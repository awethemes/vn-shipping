<?php
/**
 * Plugin Name:     VNShipping for WooCommerce
 * Plugin URI:      https://github.com/awethemes/vn-shipping
 * Description:     Support shipping couriers in Vietnam like GHN, GHTK, Viettel-Post.
 * Author:          awethemes
 * Author URI:      https://awethemes.com
 * Text Domain:     vn-shipping
 * Domain Path:     /languages
 * Version:         0.1.2
 *
 * @package         VNShipping
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VN_SHIPPING_VERSION', '0.1.2' );
define( 'VN_SHIPPING_DB_VERSION', '1.0.0' );
define( 'VN_SHIPPING_PLUGIN_FILE', __FILE__ );
define( 'VN_SHIPPING_PLUGIN_DIR_PATH', plugin_dir_path( VN_SHIPPING_PLUGIN_FILE ) );
define( 'VN_SHIPPING_PLUGIN_DIR_URL', plugin_dir_url( VN_SHIPPING_PLUGIN_FILE ) );
define( 'VN_SHIPPING_ASSETS_URL', VN_SHIPPING_PLUGIN_DIR_URL . 'dist' );
define( 'VN_SHIPPING_MINIMUM_PHP_VERSION', '7.1.3' );

require __DIR__ . '/third-party/vendor/scoper-autoload.php';
require __DIR__ . '/inc/vendor/autoload.php';

// Main plugin initialization happens there so that this file is still parsable in PHP < 5.6.
require __DIR__ . '/inc/namespace.php';
