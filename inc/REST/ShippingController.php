<?php

namespace VNShipping\REST;

use Exception;
use RuntimeException;
use VNShipping\Courier\Exception\InvalidParameterException;
use VNShipping\Courier\Exception\RequestException;
use VNShipping\Courier\Factory;
use VNShipping\Courier\RequestParameters;
use VNShipping\OrderShippingContext;
use VNShipping\ShippingData;
use VNShipping\ShippingMethod\ShippingMethodInterface;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;

class ShippingController extends WP_REST_Controller {
	/**
	 * @var string
	 */
	protected $namespace = 'awethemes/vn-shipping';

	/**
	 * @var string
	 */
	protected $rest_base = 'shipping';

	/**
	 * @var string[]
	 */
	protected $shipping_method_alias = [
		'vtp' => 'viettel_post',
		'ghn' => 'giao_hang_nhanh',
		'ghtk' => 'giao_hang_tiet_kiem',
	];

	/**
	 * {@inheritdoc}
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/show',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'show' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/cancel',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'cancel' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<shipping_method>[\w-]+)/create',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'create_shipping_order' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
					'province' => [
						'type' => 'integer',
						'description' => 'The province code.',
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<shipping_method>[\w-]+)/preview',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'preview_shipping_order' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
					'province' => [
						'type' => 'integer',
						'description' => 'The province code.',
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<shipping_method>[\w-]+)/fee',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'get_shipping_fee' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
					'shipping_method' => [
						'type' => 'string',
						'description' => __( 'An alphanumeric identifier for the courier.', 'vn-shipping' ),
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<shipping_method>[\w-]+)/lead-time',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'get_lead_time' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
					'shipping_method' => [
						'type' => 'string',
						'description' => __( 'An alphanumeric identifier for the courier.', 'vn-shipping' ),
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<shipping_method>[\w-]+)/available-services',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'get_available_services' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
					'shipping_method' => [
						'type' => 'string',
						'description' => __( 'An alphanumeric identifier for the courier.', 'vn-shipping' ),
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function show( WP_REST_Request $request ) {
		$order = wc_get_order( $request->get_param( 'order_id' ) );

		if ( ! $order || 'shop_order' !== $order->get_type() ) {
			return new WP_Error(
				'rest_invalid_request',
				esc_html__( 'Invalid order ID.', 'vn-shipping' ),
				[ 'status' => 404 ]
			);
		}

		$context = OrderShippingContext::create_from_order( $order );

		return rest_ensure_response( [
			'order_id' => $order->get_id(),
			'order_number' => $order->get_order_number(),
			'shipping' => $context->to_array(),
		] );
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function cancel( $request ) {
		try {
			$order = $this->resolve_order( $request );

			/** @var \VNShipping\Courier\AbstractCourier $courier */
			[ $courier ] = $this->resolve_courier( $request );
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'rest_error', $e->getMessage(), [ 'status' => $e->getCode() ] );
		}

		if ( ! $shipping_code = $order->get_meta( '_shipping_order_code' ) ) {
			return new WP_Error(
				'rest_invalid_order',
				esc_html__( 'Shipping code not created yet.', 'vn-shipping' ),
				[ 'status' => 400 ]
			);
		}

		$response = $courier->cancel_order( [
			'order_codes' => [ $shipping_code ],
		] );

		return $response;
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function create_shipping_order( $request ) {
		try {
			$order = $this->resolve_order( $request );

			/** @var $courier \VNShipping\Courier\AbstractCourier */
			[ $courier, $shipping_method ] = $this->resolve_courier( $request );
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'rest_invalid_request', $e->getMessage(), [ 'status' => $e->getCode() ] );
		}

		return $this->make_response(
			function () use ( $request, $order, $courier, $shipping_method ) {
				$parameter = new RequestParameters( $request );
				$shipping_method->initialize_creation( $parameter, $order );

				$response = $courier->create_order( $parameter );

				$shipping_data = ShippingData::create(
					$order->get_id(),
					[
						'courier' => $shipping_method->id,
						'tracking_number' => $response->get_tracking_number(),
					]
				);

				if ( is_wp_error( $shipping_data ) ) {
					return $shipping_data;
				}

				return rest_ensure_response( $shipping_data->to_array() );
			}
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function preview_shipping_order( $request ) {
		try {
			$order = $this->resolve_order( $request );

			[ $courier, $shipping_method ] = $this->resolve_courier( $request );
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'rest_invalid_request', $e->getMessage(), [ 'status' => $e->getCode() ] );
		}

		$parameter = new RequestParameters( $request );
		$shipping_method->initialize_creation( $parameter, $order );

		return $this->make_response(
			function () use ( $courier, $parameter ) {
				return $courier->preview_order( $parameter );
			}
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function get_lead_time( $request ) {
		try {
			/** @var \VNShipping\Courier\AbstractCourier $courier */
			[ $courier ] = $this->resolve_courier( $request );
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'rest_invalid_request', $e->getMessage(), [ 'status' => $e->getCode() ] );
		}

		return $this->make_response(
			function () use ( $courier, $request ) {
				$response = $courier->get_lead_time( $request );

				return rest_ensure_response( $response );
			}
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function get_shipping_fee( $request ) {
		try {
			/** @var \VNShipping\Courier\AbstractCourier $courier */
			[ $courier ] = $this->resolve_courier( $request );
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'rest_invalid_request', $e->getMessage(), [ 'status' => $e->getCode() ] );
		}

		return $this->make_response(
			function () use ( $courier, $request ) {
				$response = $courier->get_shipping_fee( $request );

				return rest_ensure_response( $response );
			}
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function get_available_services( $request ) {
		try {
			/** @var \VNShipping\Courier\AbstractCourier $courier */
			[ $courier ] = $this->resolve_courier( $request );
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'rest_invalid_request', $e->getMessage(), [ 'status' => $e->getCode() ] );
		}

		return $this->make_response(
			function () use ( $courier, $request ) {
				$response = $courier->get_available_services( $request );

				return rest_ensure_response( $response );
			}
		);
	}

	/**
	 * @param callable $callback
	 * @return WP_Error|mixed
	 */
	protected function make_response( $callback ) {
		try {
			return $callback();
		} catch ( InvalidParameterException $e ) {
			return new WP_Error(
				'rest_invalid_request',
				$e->getMessage(),
				[ 'status' => 412 ]
			);
		} catch ( RequestException $e ) {
			return new WP_Error(
				'rest_request_error',
				$e->getMessage(),
				[ 'status' => $e->getCode() < 500 ? 400 : 500 ]
			);
		} catch ( Exception $e ) {
			return new WP_Error(
				'rest_server_error',
				'Opp, server error!',
				[ 'status' => 500 ]
			);
		}
	}

	/**
	 * @param WP_REST_Request $request
	 * @return \WC_Order
	 */
	protected function resolve_order( $request ) {
		$order = wc_get_order( $request->get_param( 'order_id' ) );

		if ( ! $order || 'shop_order' !== $order->get_type() ) {
			throw new RuntimeException( esc_html__( 'Invalid order ID.', 'vn-shipping' ), 404 );
		}

		return $order;
	}

	/**
	 * @param $request
	 * @return array{\VNShipping\Courier\AbstractCourier, \WC_Shipping_Method}
	 */
	protected function resolve_courier( $request ) {
		$name = $request->get_param( 'shipping_method' );

		if ( array_key_exists( $name, $this->shipping_method_alias ) ) {
			$name = $this->shipping_method_alias[ $name ];
		}

		$shipping_methods = WC()->shipping()->get_shipping_methods();
		$shipping_method = $shipping_methods[ $name ] ?? null;

		if ( ! $shipping_method || ! $shipping_method instanceof ShippingMethodInterface ) {
			throw new RuntimeException( esc_html__( 'Shipping method is missing or invalid', 'vn-shipping' ), 400 );
		}

		return [
			Factory::createFromShippingMethod( $shipping_method ),
			$shipping_method,
		];
	}
}
