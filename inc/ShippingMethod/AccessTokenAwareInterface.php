<?php

namespace VNShipping\ShippingMethod;

interface AccessTokenAwareInterface {
	/**
	 * @return string|null
	 */
	public function get_access_token();
}
