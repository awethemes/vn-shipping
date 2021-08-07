<?php

use VNShipping\Address\Province;

## =================================
## wp eval-file ./create-index.php
## =================================
require_once __DIR__ . '/../vendor/autoload.php';

$maps = [
	'districts' => [],
	'wards' => [],
];

$provinces = Province::all();
foreach ( $provinces as $province ) {
	foreach ( $province->get_districts() as $district ) {
		if ( array_key_exists( $district->get_code(), $maps['districts'] ) ) {
			echo ">>> Warning: District code: " . $district->get_code() . ' already mapped.' . PHP_EOL;
		} else {
			$maps['districts'][ $district->get_code() ] = $province->get_code();
		}

		foreach ( $district->get_wards() as $ward ) {
			if ( array_key_exists( $ward->get_code(), $maps['wards'] ) ) {
				echo "    >>> Warning: Ward code: " . $ward->get_code() . ' already mapped.' . PHP_EOL;
			} else {
				$maps['wards'][ $ward->get_code() ] = $district->get_code();
			}
		}
	}
}

file_put_contents(
	__DIR__ . '/../resources/address/indexed.php',
	"<?php\n\nreturn " . var_export( $maps, true ) . ';'
);
