<?php

namespace VNShipping\ShippingMethod;

use Exception;
use VNShipping\CartShippingContext;
use VNShipping\Courier\Exception\RequestException;
use VNShipping\Courier\Factory;
use VNShipping\Courier\RequestParameters;
use WC_Order;
use WC_Shipping_Method;

class GHNShippingMethod extends WC_Shipping_Method implements ShippingMethodInterface {
	/* Const */
	const METHOD_NAME = 'giao_hang_nhanh';

	/**
	 * Constructor.
	 *
	 * @param int $instance_id
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id = self::METHOD_NAME;
		$this->method_title = __( 'Giao Hàng Nhanh', 'vn-shipping' );
		$this->method_description = __(
			'Giao hàng qua đơn vị Giao Hàng Nhanh',
			'vn-shipping'
		);

		$this->supports = [
			'settings',
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		];

		$this->setup_setting_fields();
		$this->setup_instance_fields();

		$this->init_settings();
		$this->init_instance_settings();
		$this->init();

		add_action(
			'woocommerce_update_options_shipping_' . $this->id,
			[ $this, 'process_admin_options' ]
		);
	}

	/**
	 * Initialize local pickup.
	 */
	public function init() {
		// Define user set variables.
		$this->title = $this->get_instance_option( 'title', 'Giao Hàng Nhanh' );
		$this->tax_status = $this->get_option( 'tax_status' );
	}

	/**
	 * Calculate local pickup shipping.
	 *
	 * @param array $package Package information.
	 */
	public function calculate_shipping( $package = [] ) {
		if ( $package['destination']['country'] !== 'VN' ) {
			return;
		}

		$context = CartShippingContext::create_from_package( $package );
		if ( $context->is_empty_address() ) {
			return;
		}

		$province = $context->get_province();
		$district = $context->get_district();
		$ward = $context->get_ward();

		// Bail if the province or district address is not valid.
		if ( $province === null || $district === null || $ward === null ) {
			return;
		}

		// Bail if the weight is not provided.
		if ( ( $weight = $context->get_total_weight() ) <= 0 ) {
			return;
		}

		$ghn = Factory::createFromShippingMethod( $this );
		$debug_mode = 'yes' === get_option( 'woocommerce_shipping_debug_mode', 'no' );

		try {
			$available_services = $ghn->get_available_services(
				[ 'to_district' => $district->get_code() ]
			);
		} catch ( Exception $e ) {
			return;
		}

		$dimensions = $context->get_total_dimensions();

		$parameters = [
			'to_district_id' => $district->get_code(),
			'to_ward_code' => $ward->get_code(),

			'weight' => wc_get_weight( $context->get_total_weight(), 'g' ) ?: 1000,
			'length' => wc_get_dimension( $dimensions['length'], 'cm' ) ?: 10,
			'width' => wc_get_dimension( $dimensions['width'], 'cm' ) ?: 10,
			'height' => wc_get_dimension( $dimensions['height'], 'cm' ) ?: 10,

			'insurance_value' => 0, // Check this!
			'coupon' => $this->get_option( 'coupon_code' ),
		];

		foreach ( $available_services as $service ) {
			try {
				$rate = $ghn->get_shipping_fee(
					array_merge( $parameters, [
						'service_id' => $service['service_id'],
						'service_type_id' => $service['service_type_id'],
					] )
				);

				$this->add_rate( [
					'id' => $this->get_rate_id( $service['service_id'] ),
					'label' => sprintf( '%s (%s)', $this->title, $service['short_name'] ),
					'cost' => $rate['total'],
					'package' => $package,
				] );
			} catch ( RequestException $e ) {
				if ( $debug_mode ) {
					wc_add_notice( $e->getMessage() );

					return;
				}
			}
		}
	}

	/**
	 * Initialise settings form fields.
	 *
	 * @return void
	 */
	public function setup_setting_fields() {
		$form_fields = [];

		$form_fields['api_token'] = [
			'type' => 'password',
			'title' => esc_html__( 'Token API', 'vn-shipping' ),
			'description' => esc_html__(
				'Token API do Giao Hàng Nhanh cung cấp. Truy cập **GHN - Thông tin tài khoản**',
				'vn-shipping'
			),
		];

		$form_fields['is_debug'] = [
			'type' => 'checkbox',
			'title' => esc_html__( 'Debug mode', 'vn-shipping' ),
			'label' => esc_html__( 'Is API under debug mode?', 'vn-shipping' ),
			'default' => 'yes',
		];

		if ( $this->get_option( 'api_token' ) ) {
			$form_fields['shop_id'] = [
				'type' => 'text',
				'title' => esc_html__( 'Store ID from GHN', 'vn-shipping' ),
				'description' => esc_html__(
					'',
					'vn-shipping'
				),
			];
		}

		$this->form_fields = $form_fields;
	}

	/**
	 * Initialise instance form fields.
	 *
	 * @return void
	 */
	public function setup_instance_fields() {
		$this->instance_form_fields = [
			'title' => [
				'title' => __( 'Method title', 'woocommerce' ),
				'type' => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default' => __( 'Giao Hàng Nhanh', 'vn-shipping' ),
				'desc_tip' => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function initialize_creation( RequestParameters $parameters, WC_Order $order ) {
		$parameters->set(
			'client_order_code',
			$order->get_order_number()
		);

		$parameters->set(
			'items',
			array_map(
				function ( $item ) {
					$product = $item->get_product();

					return [
						'name' => $item->get_name(),
						'code' => $product->get_sku() ?: 'id_' . $item->get_product_id(),
						'quantity' => $item->get_quantity(),
					];
				},
				array_values( $order->get_items() )
			)
		);
	}
}
