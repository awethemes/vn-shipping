<?php

namespace VNShipping;

use JsonSerializable;
use VNShipping\Address\District;
use VNShipping\Address\Province;
use VNShipping\Address\Ward;
use WC_Order;

class OrderShippingContext implements JsonSerializable {
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $phone;

	/**
	 * @var string
	 */
	public $address;

	/**
	 * @var array
	 */
	public $address_data = [];

	/**
	 * @var string
	 */
	public $cod;

	/**
	 * @var int|float
	 */
	public $insurance;

	/**
	 * @var int
	 */
	public $weight;

	/**
	 * @var int
	 */
	public $length;

	/**
	 * @var int
	 */
	public $width;

	/**
	 * @var int
	 */
	public $height;

	/**
	 * @var string
	 */
	public $note;

	/**
	 * Create new shipping context from order.
	 *
	 * @param WC_Order $order
	 * @return self
	 */
	public static function create_from_order( WC_Order $order ) {
		$context = new static();

		$context->name = $order->get_formatted_shipping_full_name();
		$context->address = $order->get_shipping_address_1();
		$context->phone = ( $order->get_address( 'shipping' )['phone'] ?? null ) ?: $order->get_billing_phone();

		$isShipToVietnam = $order->get_shipping_country() === 'VN';

		$province = $isShipToVietnam ? Province::get_by_code( $order->get_shipping_state() ) : null;
		$district = $isShipToVietnam ? District::get_by_code( $order->get_shipping_city() ) : null;
		$ward = $isShipToVietnam ? Ward::get_by_code( $order->get_shipping_address_2() ) : null;

		$context->address_data = [
			'province' => $province ? $province->get_code() : null,
			'district' => $district ? $district->get_code() : null,
			'ward' => $ward ? $ward->get_code() : null,
		];

		$context->cod = 0;
		$context->insurance = $order->get_subtotal();
		$context->note = $order->get_customer_note( 'edit' );

		$data = array_reduce(
			$order->get_items(),
			function ( array $arr, $item ) {
				$qty = $item->get_quantity();
				$product = $item->get_product();

				if ( $product === false ) {
					return $arr;
				}

				if ( $product->has_weight() ) {
					$arr['weight'] += (float) $product->get_weight() * $qty;
				}

				if ( $product->has_dimensions() ) {
					$arr['length'] += (float) $product->get_length() * $qty;
					$arr['width'] += (float) $product->get_width() * $qty;
					$arr['height'] += (float) $product->get_height() * $qty;
				}

				return $arr;
			},
			[ 'length' => 0, 'width' => 0, 'height' => 0, 'weight' => 0 ]
		);

		$context->weight = wc_get_weight( $data['weight'], 'g' ) ?: 1000;
		$context->length = wc_get_dimension( $data['length'], 'cm' ) ?: 10;
		$context->width = wc_get_dimension( $data['width'], 'cm' ) ?: 10;
		$context->height = wc_get_dimension( $data['height'], 'cm' ) ?: 10;

		return $context;
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * {@inheritdoc}
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}
}
