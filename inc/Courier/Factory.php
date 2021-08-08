<?php

namespace VNShipping\Courier;

use InvalidArgumentException;
use VNShipping\ShippingMethod\GHNShippingMethod;
use VNShipping\ShippingMethod\GHTKShippingMethod;
use VNShipping\ShippingMethod\VTPShippingMethod;
use WC_Shipping_Method;

class Factory {
	/**
	 * Create from name.
	 *
	 * @param string $name
	 */
	public static function create( $name ) {
		$info = Couriers::getCourier( $name );

		if ( $info === null ) {
			throw new InvalidArgumentException( 'Invalid courier' );
		}

		$shipping_methods = WC()->shipping()->get_shipping_methods();
		$shipping_method = $shipping_methods[ $info['id'] ] ?? null;

		return static::createFromShippingMethod( $shipping_method );
	}

	/**
	 * @param WC_Shipping_Method $method
	 * @return AbstractCourier
	 */
	public static function createFromShippingMethod( WC_Shipping_Method $method ) {
		switch ( true ) {
			case $method instanceof GHNShippingMethod:
				return new GHN(
					$method->get_option( 'api_token' ),
					(int) $method->get_option( 'shop_id', 0 ),
					'yes' === $method->get_option( 'is_debug', 'no' )
				);

			case $method instanceof GHTKShippingMethod:
				return ( new GHTK(
					$method->get_option( 'api_token' ),
					'yes' === $method->get_option( 'is_debug', 'no' )
				) )->set_store_id( $method->get_option( 'shop_id' ) );

			/*case $method instanceof VTPShippingMethod:
				$instance = new ViettelPost(
					$method->get_option( 'username' ),
					$method->get_option( 'password' )
				);

				if ( $access_token = $method->get_access_token() ) {
					$instance->set_access_token( $access_token );
				}

				return $instance;*/

			default:
				throw new InvalidArgumentException( 'Invalid courier' );
		}
	}
}
