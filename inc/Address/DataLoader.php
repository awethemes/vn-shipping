<?php

namespace VNShipping\Address;

class DataLoader {
	/**
	 * @var array
	 */
	public static $cached_data = [];

	/**
	 * @param string $path
	 * @return string
	 */
	public static function get_base_path( $path ) {
		return VN_SHIPPING_PLUGIN_DIR_PATH . '/resources/address/' . $path;
	}

	/**
	 * @param string $data_file
	 * @return array|null
	 */
	public static function load_json_data( $data_file ) {
		$path = self::get_base_path( 'data/' ) . $data_file;

		if ( array_key_exists( $path, static::$cached_data ) ) {
			return static::$cached_data[ $path ];
		}

		if ( file_exists( $path ) && is_readable( $path ) ) {
			$json = json_decode( file_get_contents( $path ), true );

			return static::$cached_data[ $path ] = $json;
		}

		return null;
	}

	/**
	 * @param string $php_file
	 * @return array|null
	 */
	public static function load_php_data( $php_file ) {
		$path = self::get_base_path( $php_file );

		if ( array_key_exists( $path, static::$cached_data ) ) {
			return static::$cached_data[ $path ];
		}

		return static::$cached_data[ $path ] = include $path;
	}
}
