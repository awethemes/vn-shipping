<?php

namespace VNShipping;

use Exception;
use VNShipping\Traits\SingletonTrait;

class AdminActions {
	use SingletonTrait;

	/**
	 * Init the actions.
	 */
	public function init() {
		$actions = [
			'get-access-token' => 'get_access_token',
		];

		foreach ( $actions as $action => $method ) {
			add_action( 'admin_action_vnshipping-' . $action, [ $this, $method ] );
		}
	}

	/**
	 * Handle get access token.
	 */
	public function get_access_token() {
		header( 'Content-type: text/html; charset=utf-8' );
		header( 'X-Accel-Buffering: no' );

		ob_end_flush();
		ob_implicit_flush();

		$string_length = ini_get( 'output_buffering' );
		if ( ! is_numeric( $string_length ) ) {
			$string_length = 4096;
		}

		echo str_repeat( ' ', $string_length ); // Print 4096 empty string first.

		$shipping_method_name = sanitize_text_field( $_REQUEST['shipping-method'] ?? null );
		$shipping_methods = WC()->shipping()->get_shipping_methods();

		$shipping_method = $shipping_methods[ $shipping_method_name ] ?? null;
		$client = $shipping_method->get_courier();

		echo '<pre style="color: #2c4b4c">Info: Request access token...</pre>';
		ob_flush();

		try {
			$client->request_access_token( true );

			if ( $client->get_access_token() ) {
				$shipping_method->update_option( 'access_token', $client->get_access_token() );

				echo '<pre style="color: #308430">Info: Update access token successfully!</pre>';
			} else {
				echo '<pre>Unable to request access token!</pre>';
			}
		} catch ( Exception $e ) {
			$shipping_method->update_option( 'access_token', null );

			echo sprintf( '<pre style="color: #a51515;">Error: %s<pre>', esc_html( $e->getMessage() ) );
			ob_flush();
		}
	}
}
