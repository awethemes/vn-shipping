<?php

namespace VNShipping\Scripts;

use VNShipping\Courier\ViettelPost;

require_once __DIR__ . '/shared.php';

global $ghn;
$ghn = new ViettelPost( '', '' );

const __SAVE_PATH = __DIR__ . '/data/vtp-json/';
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
		echo "  -> " . $district['DISTRICT_NAME'] . PHP_EOL;

		__save_xa_phuong( $district['DISTRICT_ID'] );
	}
}

function __save_xa_phuong( $ma_quan ) {
	global $ghn;

	$wards = $ghn->get_wards( $ma_quan );

	file_put_contents(
		__SAVE_PATH . '/xa-phuong/' . $ma_quan . '.json',
		json_encode( $wards->get_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )
	);
}

foreach ( $provinces as $province ) {
	echo "> " . $province['PROVINCE_NAME'] . PHP_EOL;
	__save_quan_huyen( $province['PROVINCE_ID'] );
}
