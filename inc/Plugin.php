<?php

namespace VNShipping;

use VNShipping\Address\Province;
use VNShipping\Courier\Couriers;

class Plugin {
	use Traits\SingletonTrait;

	/**
	 * Initialize plugin functionality.
	 *
	 * @return void
	 */
	public function register() {
		// REST API endpoints.
		// High priority so it runs after create_initial_rest_routes().
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ], 100 );

		// Register shipping methods.
		add_filter( 'woocommerce_shipping_methods', [ $this, 'register_shipping_methods' ] );

		// Init the core address hooks.
		( new AddressHooks() );

		// Init the admin hooks.
		add_action( 'admin_init', [ $this, 'admin_init' ] );

		// Assets hooks.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

		// Database upgrade hooks.
		$plugin_upgrader = new DatabaseUpgrader();
		add_action( 'admin_init', [ $plugin_upgrader, 'init' ], 5 );
	}

	/**
	 * Init admin hooks.
	 */
	public function admin_init() {
		AdminActions::get_instance()->init();
		OrderListTable::get_instance()->init();

		add_action( 'add_meta_boxes', [ $this, 'register_meta_box' ] );
	}

	/**
	 * Register shipping methods.
	 *
	 * @param array $methods
	 * @return array
	 */
	public function register_shipping_methods( array $methods ) {
		$methods[ Couriers::GHN ] = ShippingMethod\GHNShippingMethod::class;
		$methods[ Couriers::GHTK ] = ShippingMethod\GHTKShippingMethod::class;
		$methods[ Couriers::VTP ] = ShippingMethod\VTPShippingMethod::class;

		return $methods;
	}

	/**
	 * Registers REST API routes.
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		$controllers = [
			REST\AddressController::class,
			REST\ShippingController::class,
		];

		foreach ( $controllers as $controller_class ) {
			$container = new $controller_class();
			$container->register_routes();
		}
	}

	/**
	 * Register and enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_register_script(
			'vn-shipping-checkout-js',
			VN_SHIPPING_ASSETS_URL . '/checkout.js',
			array_merge( [ 'jquery', 'wc-checkout' ], $this->get_asset_info( 'checkout', 'dependencies' ) ),
			$this->get_asset_info( 'checkout', 'version' ),
			true
		);

		if ( is_checkout() ) {
			wp_enqueue_script( 'vn-shipping-checkout-js' );
		}
	}

	/**
	 * Register and enqueue admin scripts.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		wp_register_style(
			'vn-shipping-admin-css',
			VN_SHIPPING_ASSETS_URL . '/admin.css',
			[ 'wp-components' ],
			VN_SHIPPING_VERSION
		);

		wp_register_script(
			'vn-shipping-edit-order',
			VN_SHIPPING_ASSETS_URL . '/edit-order.js',
			$this->get_asset_info( 'edit-order', 'dependencies' ),
			$this->get_asset_info( 'edit-order', 'version' ),
			true
		);

		// Enqueue scripts.
		$current_screen = get_current_screen();
		if ( $current_screen && 'shop_order' === $current_screen->id ) {
			wp_localize_script( 'vn-shipping-edit-order', '_vnsOrderData', [
				'provinces' => array_values( Province::all() ),
			] );

			wp_enqueue_style( 'vn-shipping-admin-css' );
			wp_enqueue_script( 'vn-shipping-edit-order' );
		}
	}

	/**
	 * Get asset info from extracted asset files.
	 *
	 * @param string $name      Asset name as defined in build/webpack configuration.
	 * @param string $attribute Optional attribute to get. Can be "version" or "dependencies".
	 * @return array
	 */
	public function get_asset_info( $name, $attribute = null ) {
		static $assets = [];

		if ( array_key_exists( $name, $assets ) ) {
			return $assets[ $name ];
		}

		$asset_path = untrailingslashit( VN_SHIPPING_PLUGIN_DIR_PATH ) . sprintf( '/dist/%s.asset.php', $name );

		if ( file_exists( $asset_path ) && is_readable( $asset_path ) ) {
			$info = $assets[ $name ] = include $asset_path;
		} else {
			$info = [ 'version' => VN_SHIPPING_VERSION, 'dependencies' => [] ];
		}

		if ( ! empty( $attribute ) && isset( $info[ $attribute ] ) ) {
			return $info[ $attribute ];
		}

		return $info;
	}

	/**
	 * @return void
	 */
	public function register_meta_box() {
		$renderCallback = function ( $post ) {
			global $theorder;

			if ( ! is_object( $theorder ) ) {
				$theorder = wc_get_order( $post->ID );
			}

			$orderStates = OrderHelper::get_order_states( $theorder );
			if ( empty( $orderStates['orderShippingData'] ) && ! $orderStates['canCreateShipping'] ) {
				echo sprintf( '<p>%s</p>', esc_html__( 'Không thể tạo mã vận đơn cho đơn hàng này.' ) );

				return;
			}

			wp_add_inline_script(
				'vn-shipping-edit-order',
				'window._vnShippingInitialStates = ' . wp_json_encode( $orderStates ),
				'before'
			);

			echo '<div id="VNShippingRoot"></div>';
		};

		add_meta_box(
			'vn_shipping_box',
			esc_html__( 'Shipping', 'vn-shipping' ),
			$renderCallback,
			'shop_order',
			'side',
			'high'
		);
	}
}
