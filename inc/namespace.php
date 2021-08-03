<?php

namespace VNShipping;

/**
 * Get Plugin Instance.
 *
 * @return Plugin
 */
function get_plugin_instance() {
	return Plugin::get_instance();
}

/**
 * Handles plugin activation.
 *
 * Throws an error if the site is running on PHP < 7.1.3
 *
 * @param bool $network_wide Whether to activate network-wide.
 * @return void
 */
function activate( $network_wide ) {
	if ( version_compare( PHP_VERSION, VN_SHIPPING_MINIMUM_PHP_VERSION, '<' ) ) {
		/** @noinspection ForgottenDebugOutputInspection */
		wp_die(
			esc_html( sprintf( /* translators: %s: PHP version number */
				__( 'VN-Shipping plugin requires PHP %s or higher.', 'vn-shipping' ),
				VN_SHIPPING_MINIMUM_PHP_VERSION
			) ),
			esc_html__( 'Plugin could not be activated', 'vn-shipping' )
		);
	}

	// $story = new Custom_Post_Type();
	// $story->init();

	flush_rewrite_rules( false );

	do_action( 'vn_shipping_plugin_activation', $network_wide );
}

/**
 * Handles plugin deactivation.
 *
 * @param bool $network_wide Whether to deactivate network-wide.
 * @return void
 */
function deactivate( $network_wide ) {
	if ( version_compare( PHP_VERSION, VN_SHIPPING_MINIMUM_PHP_VERSION, '<' ) ) {
		return;
	}

	// unregister_post_type( Custom_Post_Type::POST_TYPE_SLUG );
	flush_rewrite_rules( false );

	do_action( 'vn_shipping_plugin_deactivation', $network_wide );
}

/**
 * Init the plugin.
 */
function __init__() {
	load_plugin_textdomain(
		'vn-shipping',
		false,
		dirname( plugin_basename( VN_SHIPPING_PLUGIN_FILE ) ) . '/languages/'
	);

	if ( class_exists( 'WooCommerce' ) ) {
		get_plugin_instance()->register();
	}
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\__init__' );

register_activation_hook( VN_SHIPPING_PLUGIN_FILE, __NAMESPACE__ . '\\activate' );
register_deactivation_hook( VN_SHIPPING_PLUGIN_FILE, __NAMESPACE__ . '\\deactivate' );
