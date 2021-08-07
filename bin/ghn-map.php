<?php

namespace VNShipping\Scripts;

require_once __DIR__ . '/shared.php';

function mapping_provinces() {
	$ghn_provinces = json_decode( file_get_contents( __DIR__ . '/data/ghn-json/tinh_tp.json' ), true );

	$found = $missing = 0;

	foreach ( $ghn_provinces as $province ) {
		if ( false !== mb_stripos( $province['ProvinceName'], 'đặc biệt' ) ) {
			continue;
		}

		$row = _find_province_code( $province['ProvinceName'], $province['NameExtension'] );
		if ( $row && $row->ghn_code ) {
			continue;
		}

		if ( $row ) {
			$found++;

			_get_pdo()->prepare( "UPDATE provinces SET ghn_code = ? WHERE code = ?" )
				->execute( [ $province['ProvinceID'], $row->code ] );

			success( sprintf( "[GHN] Found: %s ~> %s(%s)", $province['ProvinceName'], $row->name, $row->code ) );
		} else {
			$missing++;

			error( '[GHN] Unable to find province for: ' . $province['ProvinceName'] );
		}
	}

	message( "=> Found: {$found}. Missing: {$missing}" );
}

function mapping_districts( $provinceCode, $dbProvince ) {
	$districts = json_decode(
		file_get_contents( __DIR__ . '/data/ghn-json/quan-huyen/' . $provinceCode . '.json' ),
		true
	);

	$found = $missing = 0;

	foreach ( $districts as $district ) {
		if ( _str_contains( $district['DistrictName'], [ 'đặc biệt', 'vật tư' ] ) ) {
			continue;
		}

		$row = _find_district_code( $dbProvince->code, $district['DistrictName'], $district['NameExtension'] ?? [] );
		if ( $row && $row->ghn_code ) {
			continue;
		}

		if ( $row ) {
			$found++;

			_get_pdo()->prepare( "UPDATE districts SET ghn_code = ? WHERE code = ?" )
				->execute( [ $district['DistrictID'], $row->code ] );

			success( sprintf( "......[GHN] Found: %s ~> %s(%s)", $district['DistrictName'], $row->name, $row->code ) );

			// _create_alt_names( 'district', $dbProvince->code, $row->code, $district['NameExtension'] ?? [] );

			// mapping_wards( $district, $row );
			// echo PHP_EOL . PHP_EOL;
		} else {
			$missing++;

			error( '......[GHN] Unable to find district for: ' . $district['DistrictName'] );
		}
	}

	message( "......=> Found: {$found}. Missing: {$missing}" );
}

function mapping_wards($districtCode, $districtRow) {
	$wards = json_decode(
		file_get_contents( __DIR__ . '/data/ghn-json/xa-phuong/' . $districtCode . '.json' ),
		true
	);

	$found = $missing = 0;

	foreach ( $wards as $ward ) {
		$row = _find_wards_code( $districtRow->code, $ward['WardName'], $ward['NameExtension'] ?? [] );
		if ( $row && $row->ghn_code ) {
			continue;
		}

		if ( $row ) {
			$found++;

			_get_pdo()->prepare( "UPDATE wards SET ghn_code = ? WHERE code = ?" )
				->execute( [ $ward['WardCode'], $row->code ] );

			success( sprintf( "............Found: %s ~> %s(%s)", $ward['WardName'], $row->name, $row->code ) );

			// _create_alt_names( 'wards', $districtRow->code, $row->code, $ward['NameExtension'] ?? [] );
		} else {
			$missing++;

			error( '............Unable to find wards for: ' . $ward['WardName'] );
		}
	}

	message( "............Found: {$found}. Missing: {$missing}" );
}

mapping_provinces();

$provinceRows = _query_rows( "SELECT * FROM provinces WHERE ghn_code IS NOT NULL" );
foreach ( $provinceRows as $province_row ) {
	success( sprintf( "[GHN] Mapping districts: %s", $province_row->name ) );

	mapping_districts( $province_row->ghn_code, $province_row );
}

$districtRows = _query_rows( "SELECT * FROM districts WHERE ghn_code IS NOT NULL" );
foreach ( $districtRows as $districtRow ) {
	success( sprintf( "[GHN] Mapping wards: %s", $districtRow->name ) );

	mapping_wards( $districtRow->ghn_code, $districtRow );
}
