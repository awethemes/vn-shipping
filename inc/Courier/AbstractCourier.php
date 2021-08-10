<?php

namespace VNShipping\Courier;

use ReflectionClass;
use VNShipping\Courier\Exception\BadResponseException;
use VNShipping\Courier\Exception\UnsupportedMethodException;
use VNShipping\Courier\Response\CollectionResponseData;
use VNShipping\Courier\Response\JsonResponseData;
use VNShipping\Courier\Response\ShippingOrderResponseData;

abstract class AbstractCourier {
	/**
	 * @var bool
	 */
	protected $is_debug;

	/**
	 * @var string
	 */
	protected $access_token;

	/**
	 * @var string
	 */
	protected $token_header_key = 'Token';

	/**
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Return the base URL of service.
	 *
	 * @return string
	 */
	abstract public function get_base_url();

	/**
	 * @param RequestParameters|array $parameters
	 * @return CollectionResponseData
	 */
	abstract public function get_stores( $parameters );

	/**
	 * @param RequestParameters|array $parameters
	 * @return JsonResponseData
	 */
	abstract public function get_shipping_fee( $parameters );

	/**
	 * @param RequestParameters|array $parameters
	 * @return ShippingOrderResponseData
	 */
	abstract public function get_order( $parameters );

	/**
	 * @param RequestParameters|array $parameters
	 * @return ShippingOrderResponseData
	 */
	abstract public function create_order( $parameters );

	/**
	 * @param RequestParameters|array $parameters
	 * @return JsonResponseData
	 */
	abstract public function cancel_order( $parameters );

	/**
	 * @param RequestParameters|array $parameters
	 * @return JsonResponseData
	 */
	public function get_available_services( $parameters ) {
		throw new UnsupportedMethodException(
			sprintf(
				__( 'Lấy thông tin dịch vụ giao hàng không được hỗ trợ bởi %s.', 'vn-shipping' ),
				( new ReflectionClass( $this ) )->getShortName()
			)
		);
	}

	/**
	 * @param RequestParameters|array $parameters
	 * @return JsonResponseData
	 */
	public function get_lead_time( $parameters ) {
		throw new UnsupportedMethodException(
			sprintf(
				__( 'Lấy thông tin ngày giao hàng không được hỗ trợ bởi %s.', 'vn-shipping' ),
				( new ReflectionClass( $this ) )->getShortName()
			)
		);
	}

	/**
	 * @param bool $debug
	 * @return $this
	 */
	public function set_debug( $debug = true ) {
		$this->is_debug = $debug;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function is_debug() {
		return $this->is_debug;
	}

	/**
	 * @return string
	 */
	public function get_access_token() {
		return $this->access_token;
	}

	/**
	 * @param string $access_token
	 */
	public function set_access_token( $access_token ) {
		$this->access_token = $access_token;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return $this
	 */
	public function with_header( $key, $value ) {
		$this->headers[ $key ] = $value;

		return $this;
	}

	/**
	 * @return array
	 */
	public function request_headers() {
		return array_merge( $this->headers, [
			'Content-Type' => 'application/json',
		] );
	}

	/**
	 * Send request to the endpoint.
	 *
	 * @param string $endpoint
	 * @param mixed  $data
	 * @param string $method
	 * @return array
	 *
	 * @throws Exception\RequestException
	 * @throws Exception\BadResponseException
	 */
	public function request( $endpoint, $data = null, $method = 'POST' ) {
		if ( $this->token_header_key && $token = $this->get_access_token() ) {
			$this->with_header( $this->token_header_key, $token );
		}

		$endpoint = untrailingslashit( $this->get_base_url() ) . $endpoint;

		$response = wp_remote_request(
			$endpoint,
			[
				'method' => $method,
				'timeout' => 30,
				'body' => $data,
				'headers' => $this->request_headers(),
			]
		);

		// Clear the headers.
		$this->headers = [];

		if ( is_wp_error( $response ) ) {
			throw new Exception\RequestException(
				$response->get_error_message(),
				is_numeric( $response->get_error_code() ) ? $response->get_error_code() : 0
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$raw_body = wp_remote_retrieve_body( $response );

		if ( $code !== 200 ) {
			$error = json_decode( $raw_body, true );

			$exception = $code === 401
				? Exception\UnauthorizedException::class
				: Exception\RequestException::class;

			throw ( new $exception(
				$error['message'] ?? wp_remote_retrieve_response_message( $response ),
				$error['code'] ?? $code
			) )->setRawBody( $raw_body );
		}

		$body = json_decode( $raw_body, true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			throw ( new Exception\BadResponseException(
				'The data set for the response body cannot be parsed: ' . json_last_error_msg()
			) )->setRawBody( $raw_body );
		}

		return $body;
	}

	/**
	 * @param array $data
	 * @return JsonResponseData
	 */
	protected static function newJsonResponseData( array $data ) {
		return new JsonResponseData( $data );
	}

	/**
	 * @param array $data
	 * @return CollectionResponseData
	 */
	protected static function newCollectionResponseData( array $data ) {
		return new CollectionResponseData( $data );
	}

	/**
	 * @param array  $response
	 * @param string ...$keys
	 */
	protected static function assertResponseHasKey( $response, ...$keys ) {
		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $response ) || $response[ $key ] === null ) {
				throw new BadResponseException( "The `$key` key in response is missing." );
			}
		}
	}
}
