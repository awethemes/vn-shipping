<?php

namespace VNShipping\Scripts;

require_once __DIR__ . '/shared.php';

function mapping_provinces() {
	$provinces = json_decode( file_get_contents( __DIR__ . '/data/vtp-json/tinh_tp.json' ), true );

	$found = $missing = 0;

	foreach ( $provinces as $province ) {
		$row = _find_province_code( $province['PROVINCE_NAME'], [] );

		if ( $row ) {
			$found++;

			_get_pdo()->prepare( "UPDATE provinces SET vtp_code = ? WHERE code = ?" )
				->execute( [ $province['PROVINCE_ID'], $row->code ] );

			success( sprintf( "[VTP] Found: %s ~> %s(%s)", $province['PROVINCE_NAME'], $row->name, $row->code ) );

			mapping_districts( $province, $row );
		} else {
			$missing++;

			error( '[VTP] Unable to find province for: ' . $province['PROVINCE_NAME'] );
		}
	}

	message( "=> Found: {$found}. Missing: {$missing}" );
}

function mapping_districts( $provinceInfo, $dbProvince ) {
	$districts = json_decode(
		file_get_contents( __DIR__ . '/data/vtp-json/quan-huyen/' . $provinceInfo['PROVINCE_ID'] . '.json' ),
		true
	);

	$found = $missing = 0;

	foreach ( $districts as $district ) {
		$row = _find_district_code( $dbProvince->code, $district['DISTRICT_NAME'] );

		if ( $row ) {
			$found++;

			_get_pdo()->prepare( "UPDATE districts SET vtp_code = ? WHERE code = ?" )
				->execute( [ $district['DISTRICT_ID'], $row->code ] );

			success( sprintf( "......Found: %s ~> %s(%s)", $district['DISTRICT_NAME'], $row->name, $row->code ) );

			// mapping_wards( $district, $row );
			echo PHP_EOL . PHP_EOL;
		} else {
			$missing++;

			error( '......Unable to find district for: ' . $district['DISTRICT_NAME'] );
		}
	}

	message( "......=> Found: {$found}. Missing: {$missing}" );

	if ( $missing > 2 ) {
		exit( $missing );
	}
}

function mapping_wards($districtInfo, $districtRow) {
	$wards = json_decode(
		file_get_contents( __DIR__ . '/data/vtp-json/xa-phuong/' . $districtInfo['DISTRICT_ID'] . '.json' ),
		true
	);

	$found = $missing = 0;

	foreach ( $wards as $province ) {
		$row = _find_wards_code( $districtRow->code, $province['WARDS_NAME'], [] );

		if ( $row ) {
			$found++;

			_get_pdo()->prepare( "UPDATE wards SET vtp_code = ? WHERE code = ?" )
				->execute( [ $province['WARDS_ID'], $row->code ] );

			success( sprintf( "............Found: %s ~> %s(%s)", $province['WARDS_NAME'], $row->name, $row->code ) );
		} else {
			$missing++;

			error( '............Unable to find wards for: ' . $province['WARDS_NAME'] );
		}
	}

	message( "............Found: {$found}. Missing: {$missing}" );
}

mapping_provinces();
