<?php

namespace VNShipping\Scripts;

use PDO;
use RuntimeException;
use VNShipping\Address\Province;

define( 'WP_USE_THEMES', false );
if ( ! isset( $_SERVER['REQUEST_METHOD'] ) ) {
	$_SERVER['REQUEST_METHOD'] = 'GET';
}

require_once __DIR__ . '/../../../../wp-blog-header.php';
require_once __DIR__ . '/../vn-shipping.php';

global $pdo;
$pdo = new PDO( 'mysql:host=localhost;dbname=address', 'root', 'secret' );
$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

function message( $message ) {
	echo "\e[33m" . $message . "\e[0m" . PHP_EOL;
}

function success( $message ) {
	echo "\e[32m" . $message . "\e[0m" . PHP_EOL;
}

function error( $message ) {
	echo "\e[31m" . $message . "\e[0m" . PHP_EOL;
}

function _get_pdo() {
	global $pdo;

	return $pdo;
}

function _query_row( $sql, $bindings = [] ) {
	$sth = _get_pdo()->prepare( $sql );

	if ( ! is_array( $bindings ) ) {
		$bindings = [ $bindings ];
	}

	if ( false === $sth->execute( $bindings ) ) {
		throw new RuntimeException( '==> Query Error: ' . $sql );
	}

	return $sth->fetch( PDO::FETCH_OBJ );
}

function _query_rows( $sql, $bindings = [] ) {
	$sth = _get_pdo()->prepare( $sql );

	if ( false === $sth->execute( $bindings ) ) {
		throw new RuntimeException( '==> Query Error: ' . $sql );
	}

	return $sth->fetchAll( PDO::FETCH_OBJ );
}

function _create_address_data() {
	global $pdo;

	$_create_sql_bindings = function ( array $data, $hasParent = false ) {
		if ( $hasParent ) {
			$placeholders = '(?,?,?)' . str_repeat( ',(?,?,?)', count( $data ) - 1 );
		} else {
			$placeholders = '(?,?)' . str_repeat( ',(?,?)', count( $data ) - 1 );
		}

		$bindings = [];
		foreach ( $data as $_data ) {
			$bindings[] = $_data->get_code();
			$bindings[] = _normalize_name( $_data->name_with_type );

			if ( $hasParent ) {
				$bindings[] = $_data->parent_code;
			}
		}

		return [ $placeholders, $bindings ];
	};

	$provinces = Province::all();

	message( "Drop data..." );

	$pdo->prepare( 'delete from provinces' )->execute();
	$pdo->prepare( 'delete from districts' )->execute();
	$pdo->prepare( 'delete from wards' )->execute();

	message( "Insert provinces..." );

	[ $placeholders, $bindings ] = $_create_sql_bindings( $provinces );
	$pdo->prepare( "INSERT INTO `provinces`(code, name) VALUES " . $placeholders )
		->execute( $bindings );

	foreach ( $provinces as $province ) {
		$districts = $province->get_districts();

		message( "-> Insert districts: " . $province->name_with_type );

		[ $placeholders, $bindings ] = $_create_sql_bindings( $districts, true );
		$pdo->prepare( "INSERT INTO `districts`(code, name, parent_code) VALUES " . $placeholders )
			->execute( $bindings );

		foreach ( $districts as $district ) {
			$wards = $district->get_wards();

			if ( empty( $wards ) ) {
				continue;
			}

			message( "---> Insert wards: " . $district->name_with_type );

			[ $placeholders, $bindings ] = $_create_sql_bindings( $wards, true );
			$pdo->prepare( "INSERT INTO `wards`(code, name, parent_code) VALUES " . $placeholders )
				->execute( $bindings );
		}
	}
}

function _normalize_name( $string ) {
	$string = mb_strtolower( normalize_whitespace( $string ) );

	$replaces = [
		'ð' => 'đ',

		'òa' => 'oà',
		'óa' => 'oá',
		'ỏa' => 'oả',
		'õa' => 'oã',
		'ọa' => 'oạ',

		'òe' => 'oè',
		'óe' => 'oé',
		'ỏe' => 'oẻ',
		'õe' => 'oẽ',
		'ọe' => 'oẹ',

		'ùy' => 'uỳ',
		'úy' => 'uý',
		'ủy' => 'uỷ',
		'ũy' => 'uỹ',
		'ụy' => 'uỵ',
	];

	foreach ( $replaces as $search => $replace ) {
		if ( false !== mb_stripos( $string, $search ) ) {
			$string = str_replace( $search, $replace, $string );
		}
	}

	return $string;
}

function _find_province_code( $name, $altNames = [] ) {
	$name = _normalize_name( $name );
	$altNames = array_map( __NAMESPACE__ . '\\_normalize_name', $altNames );

	$possibleNames = [];

	if ( strpos( $name, ' - ' ) !== false ) {
		$possibleNames[] = str_replace( ' - ', ' ', $name );
	}

	$containsTinh = 0 === mb_stripos( $name, 'tỉnh' );
	$containsThanhPho = 0 === mb_stripos( $name, 'thành phố' );

	if ( ! $containsTinh || ! $containsThanhPho ) {
		if ( ! $containsThanhPho && ! $containsTinh ) {
			$possibleNames[] = 'thành phố ' . $name;
		}

		if ( ! $containsTinh && ! $containsThanhPho ) {
			$possibleNames[] = 'tỉnh ' . $name;
		}
	}

	$possibleNames[] = $name;
	$possibleNames = array_unique( array_merge( $possibleNames, $altNames ) );

	$possibleNames = array_map( function ( $name ) {
		return iconv( 'utf-8', 'utf-8', $name );
	}, $possibleNames );

	foreach ( $possibleNames as $possibleName ) {
		$row = _query_row(
			"SELECT * FROM provinces WHERE name = :name LIMIT 1",
			[ 'name' => $possibleName ]
		);

		if ( $row ) {
			return $row;
		}
	}

	foreach ( $possibleNames as $possibleName ) {
		$row = _query_row(
			"SELECT * FROM provinces WHERE name LIKE :name LIMIT 1",
			[ 'name' => '%' . $possibleName ]
		);

		if ( $row ) {
			return $row;
		}
	}

	return null;
}

function _find_district_code( $province, $name, $altNames = [] ) {
	$name = _normalize_name( $name );
	$altNames = array_map( __NAMESPACE__ . '\\_normalize_name', $altNames );

	$extNames = [
		'kỳ' => 'kì',
		' quí' => ' quý',
		' qui' => ' quy',
	];

	$possibleNames[] = $name;
	if ( strpos( $name, ' - ' ) !== false ) {
		$possibleNames[] = str_replace( ' - ', ' ', $name );
	}

	foreach ( $extNames as $_key => $_val ) {
		if ( false !== mb_stripos( $name, $_key ) ) {
			$possibleNames[] = str_replace( $_key, $_val, $name );
		} elseif ( false !== mb_stripos( $name, $_val ) ) {
			$possibleNames[] = str_replace( $_val, $_key, $name );
		}
	}

	$possibleNames = array_unique( array_merge( $possibleNames, $altNames ) );
	$possibleNames = array_map( function ( $name ) {
		return iconv( 'utf-8', 'utf-8', $name );
	}, $possibleNames );

	foreach ( $possibleNames as $possibleName ) {
		$row = _query_row(
			"SELECT * FROM districts WHERE parent_code = ? AND name = ? LIMIT 1",
			[ $province, $possibleName ]
		);

		if ( $row ) {
			return $row;
		}
	}

	foreach ( $possibleNames as $possibleName ) {
		$row = _query_row(
			"SELECT * FROM districts WHERE parent_code = ? AND name LIKE ? LIMIT 1",
			[ $province, '%' . $possibleName ]
		);

		if ( $row ) {
			return $row;
		}
	}

	foreach ( $possibleNames as $_possibleName ) {
		$possibleName = trim( preg_replace( '/^(thành phố|quận|huyện đảo|huyện|thị xã|tp|tx|t\.x)\s/u', '',
			$_possibleName ) );

		$row = _query_row(
			"SELECT * FROM districts WHERE parent_code = ? AND name LIKE ? LIMIT 1",
			[ $province, '%' . $possibleName . '%' ]
		);

		if ( $row ) {
			return $row;
		}

		if ( $row = _search_alt_name( 'district', $province, $_possibleName ) ) {
			return $row;
		}

		if ( $row = _search_alt_name( 'district', $province, $possibleName ) ) {
			return $row;
		}
	}

	return null;
}

function _find_wards_code( $district, $name, $altNames = [] ) {
	$name = _normalize_name( $name );
	$altNames = array_map( __NAMESPACE__ . '\\_normalize_name', $altNames );

	$extNames = [
		'kỳ' => 'kì',
		' quí' => ' quý',
		' qui' => ' quy',
	];

	$possibleNames[] = $name;
	if ( strpos( $name, ' - ' ) !== false ) {
		$possibleNames[] = str_replace( ' - ', ' ', $name );
	}

	foreach ( $extNames as $_key => $_val ) {
		if ( false !== mb_stripos( $name, $_key ) ) {
			$possibleNames[] = str_replace( $_key, $_val, $name );
		} elseif ( false !== mb_stripos( $name, $_val ) ) {
			$possibleNames[] = str_replace( $_val, $_key, $name );
		}
	}

	$possibleNames = array_unique( array_merge( $possibleNames, $altNames ) );
	$possibleNames = array_map( function ( $name ) {
		return iconv( 'utf-8', 'utf-8', $name );
	}, $possibleNames );

	foreach ( $possibleNames as $possibleName ) {
		$row = _query_row(
			"SELECT * FROM wards WHERE parent_code = ? AND name = ? LIMIT 1",
			[ $district, $possibleName ]
		);

		if ( $row ) {
			return $row;
		}
	}

	foreach ( $possibleNames as $possibleName ) {
		$row = _query_row(
			"SELECT * FROM wards WHERE parent_code = ? AND name LIKE ? LIMIT 1",
			[ $district, '%' . $possibleName ]
		);

		if ( $row ) {
			return $row;
		}
	}

	foreach ( $possibleNames as $_possibleName ) {
		$possibleName = trim( preg_replace( '/^(xã|phường|thị trấn|tt)\s/u', '', $_possibleName ) );

		$row = _query_row(
			"SELECT * FROM wards WHERE parent_code = ? AND name LIKE ? LIMIT 1",
			[ $district, '%' . $possibleName . '%' ]
		);

		if ( $row ) {
			return $row;
		}

		if ( $row = _search_alt_name( 'wards', $district, $_possibleName ) ) {
			return $row;
		}

		if ( $row = _search_alt_name( 'wards', $district, $possibleName ) ) {
			return $row;
		}
	}

	return null;
}

function _str_contains( $string, $contains ) {
	foreach ( (array) $contains as $str ) {
		if ( false !== mb_stripos( $string, $str ) ) {
			return true;
		}
	}

	return false;
}

function _create_alt_names( $type, $parent_code, $code, array $alt_names ) {
	if ( empty( $alt_names ) ) {
		return;
	}

	$rows = _query_rows(
		"SELECT `name`, `id` FROM `alt_names` WHERE `type` = ? AND `parent_code` = ? AND `code` = ?",
		[ $type, $parent_code, $code ]
	);

	$names = wp_list_pluck( $rows, 'name' );

	$in = str_repeat( '?,', count( $alt_names ) - 1 ) . '?';
	_get_pdo()->prepare( "DELETE FROM `alt_names` WHERE `id` IN (SELECT `id` FROM `alt_names` WHERE `type` = ? AND `parent_code` = ? AND `code` = ? AND `name` NOT IN (" . $in . "))" )
		->execute( array_values( array_merge( [ $type, $parent_code, $code ], $alt_names ) ) );

	foreach ( $alt_names as $alt_name ) {
		if ( in_array( $alt_name, $names, true ) ) {
			continue;
		}

		_get_pdo()->prepare( "INSERT INTO `alt_names`(type, parent_code, code, name) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = name" )
			->execute( [ $type, $parent_code, $code, $alt_name ] );
	}
}

function _search_alt_name( $type, $parent_code, $name ) {
	$row = _query_row(
		"SELECT `code` FROM `alt_names` WHERE `type` = ? AND `parent_code` = ? AND `name` LIKE ? LIMIT 1",
		[ $type, $parent_code, $name ]
	);

	if ( ! $row ) {
		return null;
	}

	if ( $type === 'district' ) {
		return _query_row( "SELECT * FROM `districts` WHERE `code` = ? LIMIT 1", [ $row->code ] );
	}

	if ( $type === 'wards' ) {
		return _query_row( "SELECT * FROM `wards` WHERE `code` = ? LIMIT 1", [ $row->code ] );
	}

	return null;
}

function _create_save_path( $__SAVE_PATH ) {
	$paths = [
		$__SAVE_PATH,
		$__SAVE_PATH . '/quan-huyen',
		$__SAVE_PATH . '/xa-phuong',
	];

	foreach ( $paths as $dir ) {
		if ( is_dir( $dir ) ) {
			continue;
		}

		if ( ! mkdir( $dir, 0755 ) && ! is_dir( $dir ) ) {
			throw new \RuntimeException( sprintf( 'Directory "%s" was not created', $dir ) );
		}
	}
}

function _export_indexes( $name = 'ghn' ) {
	$maps = [];

	$column = $name === 'ghn' ? 'ghn_code' : 'vtp_code';

	$provinceRows = _query_rows( "SELECT * FROM provinces" );
	foreach ( $provinceRows as $province_row ) {
		$maps['provinces'][ $province_row->code ] = $province_row->{$column} ?? null;
	}

	$districtRows = _query_rows( "SELECT * FROM districts" );
	foreach ( $districtRows as $districtRow ) {
		$maps['districts'][ $districtRow->code ] = $districtRow->{$column} ?? null;
	}

	$wardRows = _query_rows( "SELECT * FROM wards" );
	foreach ( $wardRows as $ward_row ) {
		$maps['wards'][ $ward_row->code ] = $ward_row->{$column} ?? null;
	}

	file_put_contents(
		dirname( __DIR__ ) . '/resources/address/' . $name . '-map.php',
		"<?php\n\nreturn " . var_export( $maps, true ) . ';'
	);
}

// _export_indexes('vtp');
