<?php

namespace VNShipping;

use VNShipping\Courier\Couriers;
use WC_Order;

class OrderHelper {
	/**
	 * @param WC_Order $order
	 * @return array
	 */
	public static function get_order_states( WC_Order $order ) {
		return [
			'orderId' => $order->get_id(),
			'orderShippingData' => ShippingData::get( $order->get_id() ),
			'canCreateShipping' => static::order_can_create_shipping( $order ),
			'orderShippingMethods' => static::get_order_shipping_methods( $order ),
			'availableCouriers' => array_values( Couriers::getCouriers() ),
		];
	}

	/**
	 * Get available order shipping methods, response for API.
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	public static function get_order_shipping_methods( WC_Order $order ) {
		$items = $order->get_shipping_methods();

		if ( 0 === count( $items ) ) {
			return [];
		}

		return array_filter(
			array_map(
				function ( $item ) {
					if ( ! Couriers::getCourier( $item->get_method_id() ) ) {
						return null;
					}

					return [
						'id' => $item->get_method_id(),
						'title' => $item->get_method_title() ?: $item->get_name(),
						'item_id' => $item->get_id(),
					];
				},
				array_values( $items )
			)
		);
	}

	/**
	 * @param WC_Order|int $order
	 * @return bool
	 */
	public static function order_can_create_shipping( $order ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order );
		}

		if ( false === $order ) {
			return false;
		}

		$status = [ 'trash', 'pending', 'cancelled', 'failed', 'refunded' ];

		return ! in_array( $order->get_status(), $status, true );
	}
}
