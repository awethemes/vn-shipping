<?php

namespace VNShipping\Courier;

class ShippingStatus {
	/**
	 * Returns the shipping statuses.
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return [
			'' => esc_html__( 'Không xác định', 'vn-shipping' ),
			'ready_to_pick' => esc_html__( 'Chờ lấy hàng', 'vn-shipping' ),
			'picking' => esc_html__( 'Đang lấy hàng', 'vn-shipping' ),
			'money_collect_picking' => esc_html__( 'Đang thu tiền người gửi', 'vn-shipping' ),
			'picked' => esc_html__( 'Lấy hàng thành công', 'vn-shipping' ),
			'cancel' => esc_html__( 'Đã hủy đơn hàng', 'vn-shipping' ),
			'storing' => esc_html__( 'Lưu kho', 'vn-shipping' ),
			'transporting' => esc_html__( 'Đang luân chuyển kho', 'vn-shipping' ),
			'sorting' => esc_html__( 'Đang được phân loại', 'vn-shipping' ),
			'delivering' => esc_html__( 'Đang giao hàng', 'vn-shipping' ),
			'money_collect_delivering' => esc_html__( 'Đang thu tiền người nhận', 'vn-shipping' ),
			'delivered' => esc_html__( 'Giao thành công', 'vn-shipping' ),
			'delivery_fail' => esc_html__( 'Giao hàng thất bại', 'vn-shipping' ),
			'waiting_to_return' => esc_html__( 'Đang chờ trả hàng', 'vn-shipping' ),
			'return' => esc_html__( 'Trả hàng', 'vn-shipping' ),
			'return_transporting' => esc_html__( 'Luân chuyển kho trả', 'vn-shipping' ),
			'return_sorting' => esc_html__( 'Phân loại hàng trả', 'vn-shipping' ),
			'returning' => esc_html__( 'Đang trả hàng', 'vn-shipping' ),
			'return_fail' => esc_html__( 'Trả hàng thất bại', 'vn-shipping' ),
			'returned' => esc_html__( 'Trả hàng thành công', 'vn-shipping' ),
			'exception' => esc_html__( 'Hàng ngoại lệ', 'vn-shipping' ),
			'lost' => esc_html__( 'Hàng bị mất', 'vn-shipping' ),
			'damage' => esc_html__( 'Hàng bị vỡ hoặc hư hỏng', 'vn-shipping' ),
		];
	}

	/**
	 * Get the given status for display.
	 *
	 * @param string $name
	 * @return string
	 */
	public static function get_status_name( $name ) {
		static $statuses;

		if ( ! $statuses ) {
			$statuses = self::get_statuses();
		}

		return $statuses[ $name ] ?? '';
	}
}
