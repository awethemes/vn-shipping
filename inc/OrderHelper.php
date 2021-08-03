<?php

namespace VNShipping;

use VNShipping\Courier\Couriers;
use WC_Order;

class OrderHelper {
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
}
