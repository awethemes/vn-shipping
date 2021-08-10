<?php

namespace VNShipping\Scripts;

use VNShipping\Courier\GHN;

require_once __DIR__ . '/shared.php';

const ACCESS_TOKEN = '64832b65-2fd3-11eb-a18f-227f832b612c';
const ACCESS_TOKEN_LIVE = '18fed602-331b-11eb-b36a-0e2790f48b9c';

global $ghn;
$ghn = new GHN( ACCESS_TOKEN_LIVE, 0, false );

const __SAVE_PATH = __DIR__ . '/data/ghn-json/';
_create_save_path( __SAVE_PATH );

$provinces = $ghn->get_province();
file_put_contents(
	__SAVE_PATH . '/tinh_tp.json',
	json_encode( $provinces->get_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )
);

function __save_quan_huyen( $ma_tinh ) {
	global $ghn;

	$districts = $ghn->get_district( $ma_tinh );

	file_put_contents(
		__SAVE_PATH . '/quan-huyen/' . $ma_tinh . '.json',
		json_encode( $districts->get_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )
	);

	foreach ( $districts as $district ) {
		echo "  -> " . $district['DistrictName'] . PHP_EOL;

		__save_xa_phuong( $district['DistrictID'] );
	}
}

function __save_xa_phuong( $ma_quan ) {
	global $ghn;

	$wards = $ghn->get_wards( $ma_quan );

	$data = $wards->get_data();

	foreach ( $data as &$datum ) {
		unset( $datum['WhiteListClient'], $datum['SupportType'], $datum['UpdatedDate'] );
	}
	unset( $datum);

	file_put_contents(
		__SAVE_PATH . '/xa-phuong/' . $ma_quan . '.json',
		json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )
	);
}

foreach ( $provinces as $province ) {
	echo "> " . $province['ProvinceName'] . PHP_EOL;
	__save_quan_huyen( $province['ProvinceID'] );
}
