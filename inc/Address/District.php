<?php

namespace VNShipping\Address;

use JsonSerializable;

class District implements JsonSerializable {
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
	 * @var Province
	 */
	protected $province;

	/**
	 * @var array|Ward[]
	 */
	protected $wards;

	/**
	 * @var array|self[]
	 */
	protected static $data = [];

	/**
	 * @param Province|string $province
	 * @return self[]|null
	 */
	public static function province( $province ) {
		if ( ! $province instanceof Province ) {
			$province = Province::get_by_code( $province );

			if ( ! $province === null ) {
				return null;
			}
		}

		if ( array_key_exists( $province->get_code(), static::$data ) ) {
			return static::$data[ $province->get_code() ];
		}

		$raw_data = DataLoader::load_json_data(
			sprintf( 'quan-huyen/%s.json', $province->get_code() )
		);

		$districts = array_map( function ( $args ) use ( $province ) {
			$district = new static( $args );
			$district->set_province( $province );

			return $district;
		}, $raw_data );

		return static::$data[ $province->get_code() ] = $districts;
	}

	/**
	 * @param string|int $code
	 * @return static|null
	 */
	public static function get_by_code( $code ) {
		$code = sprintf( '%03s', (string) $code );

		$maps = (array) DataLoader::load_php_data( 'indexed.php' );
		if ( ! array_key_exists( $code, $maps['districts'] ) ) {
			return null;
		}

		$districts = self::province( $maps['districts'][ $code ] );
		if ( ! $districts ) {
			return null;
		}

		return $districts[ $code ] ?? null;
	}

	/**
	 * District constructor.
	 *
	 * @param array $attributes
	 */
	public function __construct( array $attributes = [] ) {
		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {
			if ( array_key_exists( $key, $attributes ) ) {
				$this->{$key} = $attributes[ $key ];
			}
		}

		$this->wards = null;
		$this->province = null;
	}

	/**
	 * @return string
	 */
	public function get_code() {
		return sprintf( '%03s', $this->code ?: '0' );
	}

	/**
	 * @return Province
	 */
	public function get_province() {
		if ( ! $this->province ) {
			$this->province = Province::get_by_code( $this->parent_code );
		}

		return $this->province;
	}

	/**
	 * @param Province $province
	 * @return $this
	 */
	public function set_province( Province $province ) {
		$this->province = $province;

		return $this;
	}

	/**
	 * @return array|Ward[]
	 */
	public function get_wards() {
		if ( ! $this->wards ) {
			$this->wards = Ward::district( $this );
		}

		return $this->wards;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		$vars = get_object_vars( $this );

		unset( $vars['province'], $vars['wards'] );

		return $vars;
	}

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}
}
