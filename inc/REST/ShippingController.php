<?php

namespace VNShipping\REST;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use VNShipping\Courier\Couriers;
use VNShipping\Courier\Exception\InvalidParameterException;
use VNShipping\Courier\Exception\RequestException;
use VNShipping\Courier\Factory;
use VNShipping\Courier\RequestParameters;
use VNShipping\OrderShippingContext;
use VNShipping\ShippingData;
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
	 * {@inheritdoc}
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/show',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_shipping_data' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		/*register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<order_id>[\d]+)/show',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'cancel_shipment' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
				],
				'args' => [
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);*/

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<order_id>[\d]+)/cancel',
			[
				[
					'methods' => WP_REST_Server::EDITABLE,
					'callback' => [ $this, 'cancel_shipment' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
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
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
				],
				'args' => [
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
		if ( ! wc_rest_check_post_permissions( 'shop_order', 'read' ) ) {
			return new WP_Error( 'vn_shipping_rest_cannot_view',
				__( 'Sorry, you cannot view the resources.', 'vn-shipping' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! wc_rest_check_post_permissions( 'shop_order', 'edit', $request->get_param( 'order_id' ) ) ) {
			return new WP_Error(
				'vn_shipping_rest_cannot_edit',
				__( 'Sorry, you are not allowed to update resources.', 'vn-shipping' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function get_shipping_data( WP_REST_Request $request ) {
		$order = wc_get_order( $request->get_param( 'order_id' ) );

		if ( ! $order || 'shop_order' !== $order->get_type() ) {
			return new WP_Error(
				'rest_invalid_request',
				esc_html__( 'Invalid order ID.', 'vn-shipping' ),
				[ 'status' => 404 ]
			);
		}

		return rest_ensure_response( [
			'order_id' => $order->get_id(),
			'order_number' => $order->get_order_number(),
			'shipping' => OrderShippingContext::create_from_order( $order ),
		] );
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function cancel_shipment( $request ) {
		try {
			$order = $this->resolve_order( $request );
		} catch ( RuntimeException $e ) {
			return new WP_Error( 'rest_error', $e->getMessage(), [ 'status' => $e->getCode() ] );
		}

		$shipping_data = ShippingData::get( $order->get_id() );

		if ( ! $shipping_data ) {
			return new WP_Error(
				'rest_invalid_order',
				esc_html__( 'Shipping code not created yet.', 'vn-shipping' ),
				[ 'status' => 400 ]
			);
		}

		return $this->make_response(
			function () use ( $shipping_data ) {
				$courier = Factory::create( $shipping_data->courier );

				$response = $courier->cancel_order( [
					'order_code' => $shipping_data->tracking_number,
				] );

				$shipping_data->delete();

				return $response;
			}
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function create_shipping_order( $request ) {
		try {
			$order = $this->resolve_order( $request );

			$courier = $this->resolve_courier( $request );
			$shipping_method = $this->resolve_shipping_method( $request );
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

				$order->add_order_note(
					sprintf(
						__( 'Shipping order created: %s - %s' ),
						$shipping_data->get_courier_name(),
						$shipping_data->tracking_number
					)
				);

				return rest_ensure_response( $shipping_data->to_array() );
			}
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_Error|mixed
	 */
	public function get_lead_time( $request ) {
		try {
			$courier = $this->resolve_courier( $request );
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
			$courier = $this->resolve_courier( $request );
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
			$courier = $this->resolve_courier( $request );
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
				'rest_api_request_error',
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
	 * @param WP_REST_Request $request
	 * @return \VNShipping\Courier\AbstractCourier|mixed
	 */
	protected function resolve_courier( $request ) {
		$name = $request->get_param( 'shipping_method' );

		try {
			return Factory::create( $name );
		} catch ( InvalidArgumentException $e ) {
			throw new RuntimeException( esc_html__( 'Shipping courier is invalid', 'vn-shipping' ), 400 );
		}
	}

	/**
	 * @param WP_REST_Request $request
	 * @return \WC_Shipping_Method
	 */
	protected function resolve_shipping_method( $request ) {
		$name = $request->get_param( 'shipping_method' );

		$shipping_methods = WC()->shipping()->get_shipping_methods();

		$info = Couriers::getCourier( $name );
		if ( ! $info || ! isset( $shipping_methods[ $info['id'] ] ) ) {
			throw new RuntimeException( esc_html__( 'Shipping method is invalid', 'vn-shipping' ), 400 );
		}

		return $shipping_methods[ $info['id'] ];
	}
}
