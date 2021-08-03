<?php
// Ensure server variable is set for WP email functions.
if ( ! isset( $_SERVER['SERVER_NAME'] ) ) {
	$_SERVER['SERVER_NAME'] = 'localhost';
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Returns WooCommerce main directory.
 *
 * @return string
 */
function wc_dir() {
	return dirname( __FILE__, 3 ) . '/woocommerce';
}

/**
 * Manually load the plugin being tested.
 */
tests_add_filter( 'muplugins_loaded', function () {
	define( 'WC_TAX_ROUNDING_MODE', 'auto' );
	define( 'WC_USE_TRANSACTIONS', false );

	update_option( 'woocommerce_enable_coupons', 'yes' );
	update_option( 'woocommerce_calc_taxes', 'yes' );

	require_once wc_dir() . '/woocommerce.php';

	require dirname( __DIR__ ) . '/vietnam-shipping.php';
} );

/**
 * Install WC
 */
tests_add_filter( 'setup_theme', function () {
	// Clean existing install first.
	define( 'WP_UNINSTALL_PLUGIN', true );
	define( 'WC_REMOVE_ALL_DATA', true );

	include wc_dir() . '/uninstall.php';
	WC_Install::install();

	$GLOBALS['wp_roles'] = null;
	wp_roles();

	echo esc_html( 'Installing WooCommerce...' . PHP_EOL );
} );

echo "Start up the WP testing environment...." . PHP_EOL;

require $_tests_dir . '/includes/bootstrap.php';
