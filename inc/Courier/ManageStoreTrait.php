<?php

namespace VNShipping\Courier;

trait ManageStoreTrait {
	/**
	 * @var string|int
	 */
	protected $store_id;

	/**
	 * @return string|int
	 */
	public function get_store_id() {
		return $this->shop_id;
	}

	/**
	 * @param int|int $shop_id
	 * @return $this
	 */
	public function set_store_id( $shop_id ) {
		$this->shop_id = $shop_id;

		return $this;
	}
}
