<?php

namespace VNShipping\Courier;

use Exception;
use VNShipping\Courier\Exception\InvalidParameterException;
use VNShipping\OptionsResolver\OptionsResolver;
use WP_REST_Request;

class RequestParameters {
	/**
	 * The raw request parameters.
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * Constructor.
	 *
	 * @param WP_REST_Request|array $parameters
	 */
	public function __construct( $parameters = [] ) {
		if ( $parameters instanceof WP_REST_Request ) {
			$parameters = $parameters->get_params();
		}

		$this->parameters = $parameters;
	}

	/**
	 * Merge the request parameters.
	 *
	 * @param array $values
	 * @return $this
	 */
	public function merge( array $values ) {
		foreach ( $values as $key => $value ) {
			$_value = $this->parameters[ $key ] ?? null;

			if ( $_value === null || $_value === '' ) {
				$this->parameters[ $key ] = $value;
			}
		}

		return $this;
	}

	/**
	 * @param array $keys
	 * @return array
	 */
	public function only( array $keys ) {
		$values = [];

		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $this->parameters ) ) {
				$values[ $key ] = $this->parameters[ $key ];
			}
		}

		return $values;
	}

	/**
	 * Get a request parameter.
	 *
	 * @param string $key
	 * @return mixed A single parameter value.
	 */
	public function get( $key ) {
		return $this->parameters[ $key ] ?? null;
	}

	/**
	 * Set a request parameter value.
	 *
	 * @param string $key   Parameter key
	 * @param mixed  $value Parameter value
	 * @return $this
	 */
	public function set( $key, $value ) {
		$this->parameters[ $key ] = $value;

		return $this;
	}

	/**
	 * Validate the request.
	 *
	 * This method is called internally by nodes to avoid wasting time with an API call
	 * when the request is clearly invalid.
	 *
	 * @param callable $configureOptions
	 * @return array
	 */
	public function validate( callable $configureOptions ) {
		$options = new OptionsResolver();

		$configureOptions( $options );

		$data = $this->only(
			$options->getDefinedOptions()
		);

		try {
			return $options->resolve( $data );
		} catch ( Exception $e ) {
			throw new InvalidParameterException( $e->getMessage(), $e->getCode(), $e );
		}
	}
}
