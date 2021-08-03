<?php

namespace VNShipping\Address;

use JsonSerializable;

class Province implements JsonSerializable {
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
	 * @var array|District[]
	 */
	protected $districts;

	/**
	 * @var array|self[]
	 */
	protected static $data;

	/**
	 * @return array|static[]
	 */
	public static function all() {
		if ( ! static::$data ) {
			$data = (array) DataLoader::load_json_data( 'tinh_tp.json' );

			static::$data = array_map( function ( $args ) {
				return new static( $args );
			}, $data );
		}

		return static::$data;
	}

	/**
	 * @param string|int $code
	 * @return static|null
	 */
	public static function get_by_code( $code ) {
		$code = sprintf( '%02s', (string) $code );

		return static::all()[ $code ] ?? null;
	}

	/**
	 * Constructor.
	 *
	 * @param array $attributes
	 */
	public function __construct( array $attributes = [] ) {
		foreach ( array_keys( get_object_vars( $this ) ) as $key ) {
			if ( array_key_exists( $key, $attributes ) ) {
				$this->{$key} = $attributes[ $key ];
			}
		}

		$this->districts = null;
	}

	/**
	 * @return string
	 */
	public function get_code() {
		return sprintf( '%02s', $this->code ?: 0 );
	}

	/**
	 * @return array|District[]
	 */
	public function get_districts() {
		if ( ! $this->districts ) {
			$this->districts = District::province( $this );
		}

		return $this->districts;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		$vars = get_object_vars( $this );

		unset( $vars['districts'] );

		return $vars;
	}

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}
}
