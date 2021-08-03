<?php

namespace VNShipping;

use VNShipping\Address\District;
use VNShipping\Address\Province;
use VNShipping\Address\Ward;
use WC_Customer;

class CartShippingContext implements \JsonSerializable {
	/**
	 * @var WC_Customer
	 */
	public $customer;

	/**
	 * @var array
	 */
	public $destination = [];

	/**
	 * @var array
	 */
	public $contents = [];

	/**
	 * @var float
	 */
	public $contents_cost = 0;

	/**
	 * @var float
	 */
	public $cart_subtotal = 0;

	/**
	 * @var array
	 */
	public $rates = [];

	/**
	 * @var array
	 */
	public $applied_coupons = [];

	/**
	 * Create new shipping context from shipping package.
	 *
	 * @param array $package
	 * @return static
	 */
	public static function create_from_package( array $package ) {
		$context = new static();

		$context->customer = new WC_Customer( $package['user']['ID'] );
		$context->destination = $package['destination'];
		$context->contents = $package['contents'];
		$context->contents_cost = $package['contents_cost'];
		$context->cart_subtotal = $package['cart_subtotal'];
		$context->rates = $package['rates'];
		$context->applied_coupons = $package['applied_coupons'];

		return $context;
	}

	/**
	 * @return bool
	 */
	public function is_empty_address() {
		return empty( $this->destination['province'] ) ||
		       empty( $this->destination['district'] ) ||
		       empty( $this->destination['address'] );
	}

	/**
	 * @return Province|null
	 */
	public function get_province() {
		if ( ! isset( $this->destination['province'] ) ) {
			return null;
		}

		return Province::get_by_code( $this->destination['province'] );
	}

	/**
	 * @return District|null
	 */
	public function get_district() {
		if ( ! isset( $this->destination['district'] ) ) {
			return null;
		}

		return District::get_by_code( $this->destination['district'] );
	}

	/**
	 * @return Ward|null
	 */
	public function get_ward() {
		if ( ! isset( $this->destination['ward'] ) ) {
			return null;
		}

		return Ward::get_by_code( $this->destination['ward'] );
	}

	/**
	 * @return float
	 */
	public function get_total_weight() {
		return array_reduce( $this->contents, function ( $total, $content ) {
			if ( $content['data']->has_weight() ) {
				$total += (float) $content['data']->get_weight() * $content['quantity'];
			}

			return $total;
		}, 0 );
	}

	/**
	 * @return array|int[]
	 */
	public function get_total_dimensions() {
		return array_reduce( $this->contents, function ( array $totals, $content ) {
			$qty = $content['quantity'];

			if ( $content['data']->has_dimensions() ) {
				$totals['length'] += (float) $content['data']->get_length() * $qty;
				$totals['width'] += (float) $content['data']->get_width() * $qty;
				$totals['height'] += (float) $content['data']->get_height() * $qty;
			}

			return $totals;
		}, [ 'length' => 0, 'width' => 0, 'height' => 0 ] );
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
