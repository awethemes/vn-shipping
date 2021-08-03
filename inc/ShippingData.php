<?php

namespace VNShipping;

use JsonSerializable;
use VNShipping\Courier\Couriers;
use VNShipping\Courier\ShippingStatus;
use WP_Error;

class ShippingData implements JsonSerializable {
	/**
	 * The shipping data ID.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * The order ID.
	 *
	 * @var int
	 */
	public $order_id;

	/**
	 * The courier ID.
	 *
	 * @var string
	 */
	public $courier;

	/**
	 * The tracking number.
	 *
	 * @var string
	 */
	public $tracking_number;

	/**
	 * @var string
	 */
	public $status;

	/**
	 * Retrieve ShippingData instance.
	 *
	 * @param int $order_id
	 * @return self|null
	 */
	public static function get( $order_id ) {
		global $wpdb;

		$order_id = (int) $order_id;
		if ( ! $order_id ) {
			return null;
		}

		$_data = wp_cache_get( $order_id, 'vn_shipping_data' );

		if ( ! $_data ) {
			$_data = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM `{$wpdb->prefix}vn_shipping_data` WHERE `order_id` = %d LIMIT 1",
					$order_id
				)
			);

			if ( ! $_data ) {
				return null;
			}

			wp_cache_add( $_data->id, $_data, 'vn_shipping_data' );
		}

		return new static( $_data );
	}

	/**
	 * @param int $order_id
	 * @return bool
	 */
	public static function exists( $order_id ) {
		global $wpdb;

		return (bool) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(`id`) as count FROM `{$wpdb->prefix}vn_shipping_data` WHERE `order_id` = %d LIMIT 1",
				absint( $order_id )
			)
		);
	}

	/**
	 * @param int   $order_id
	 * @param array $data
	 * @return self|WP_Error
	 */
	public static function create( $order_id, array $data ) {
		global $wpdb;

		$data = wp_parse_args( $data, [
			'courier' => '',
			'tracking_number' => '',
		] );

		if ( empty( $data['courier'] ) || empty( $data['tracking_number'] ) ) {
			return new WP_Error( 'missing_data', 'Missing required data' );
		}

		unset( $data['order_id'] );
		$exists = self::exists( $order_id );

		if ( $exists ) {
			$wpdb->update(
				$wpdb->prefix . 'vn_shipping_data',
				$data,
				[ 'order_id' => $order_id ]
			);

			wp_cache_delete( $order_id, 'vn_shipping_data' );

			return static::get( $order_id );
		}

		$data['order_id'] = $order_id;
		$data['status'] = 'ready_to_pick';

		if ( false === $wpdb->insert( $wpdb->prefix . 'vn_shipping_data', $data ) ) {
			return new WP_Error( 'db_insert_error', __( 'Could not insert post into the database.' ) );
		}

		return self::get( $order_id );
	}

	/**
	 * Constructor.
	 *
	 * @param static|object $instance
	 */
	public function __construct( $instance ) {
		$int_keys = [ 'id', 'order_id' ];

		foreach ( get_object_vars( $instance ) as $key => $value ) {
			if ( in_array( $key, $int_keys, true ) ) {
				$value = absint( $value );
			}

			$this->$key = $value;
		}
	}

	/**
	 * @return string
	 */
	public function get_status_name() {
		return ShippingStatus::get_status_name( $this->status ?: '' );
	}

	/**
	 * @return string
	 */
	public function get_courier_name() {
		return Couriers::getCourier( $this->courier )['name'] ?? '';
	}

	/**
	 * Convert object to array.
	 *
	 * @return array Object as array.
	 */
	public function to_array() {
		$vars = get_object_vars( $this );

		$vars['courier_name'] = $this->get_courier_name();
		$vars['status_name'] = $this->get_status_name();

		return $vars;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->to_array();
	}
}
