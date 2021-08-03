<?php

namespace VNShipping\Address;

use JsonSerializable;

class Ward implements JsonSerializable {
	/**
	 * @var string
	 */
	public $code = '';

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $type = '';

	/**
	 * @var string
	 */
	public $name_with_type = '';

	/**
	 * @var string
	 */
	public $parent_code = '';

	/**
	 * @var District
	 */
	protected $district;

	/**
	 * @var array|self[]
	 */
	protected static $data = [];

	/**
	 * @param District|string $district
	 * @return array|static[]
	 */
	public static function district( $district ) {
		if ( ! $district instanceof District ) {
			$district = District::get_by_code( $district );

			if ( $district === null ) {
				return null;
			}
		}

		if ( array_key_exists( $district->get_code(), static::$data ) ) {
			return static::$data[ $district->get_code() ];
		}

		$raw_data = DataLoader::load_json_data(
			sprintf( 'xa-phuong/%s.json', $district->get_code() )
		);

		$wards = array_map( function ( $args ) use ( $district ) {
			$ward = new static( $args );
			$ward->set_district( $district );

			return $ward;
		}, $raw_data );

		return static::$data[ $district->get_code() ] = $wards;
	}

	/**
	 * @param string|int $code
	 * @return static|null
	 */
	public static function get_by_code( $code ) {
		$code = sprintf( '%05s', $code );

		$maps = (array) DataLoader::load_php_data( 'indexed.php' );
		if ( ! array_key_exists( $code, $maps['wards'] ) ) {
			return null;
		}

		$wars = static::district( $maps['wards'][ $code ] );
		if ( ! $wars ) {
			return null;
		}

		return $wars[ $code ] ?? null;
	}

	/**
	 * Ward constructor.
	 *
	 * @param array $attributes
	 */
	public function __construct( array $attributes = [] ) {
		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {
			if ( array_key_exists( $key, $attributes ) ) {
				$this->{$key} = $attributes[ $key ];
			}
		}

		$this->district = null;
	}

	/**
	 * @return string
	 */
	public function get_code() {
		return sprintf( '%05s', $this->code ?: '0' );
	}

	/**
	 * @return District
	 */
	public function get_district() {
		if ( ! $this->district ) {
			$this->district = District::get_by_code( $this->parent_code );
		}

		return $this->district;
	}

	/**
	 * @param District $district
	 * @return $this
	 */
	public function set_district( District $district ) {
		$this->district = $district;

		return $this;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		$vars = get_object_vars( $this );

		unset( $vars['district'] );

		return $vars;
	}

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}
}
