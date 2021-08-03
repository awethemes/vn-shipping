<?php

namespace VNShipping\Courier\Response;

use ArrayAccess;
use JsonSerializable;

class JsonResponseData implements ArrayAccess, JsonSerializable {
	/**
	 * @var array
	 */
	protected $data;

	/**
	 * JsonResponseData constructor.
	 *
	 * @param array $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	public function get_data() {
		return $this->data;
	}

	public function to_array() {
		return $this->get_data();
	}

	public function jsonSerialize() {
		return $this->to_array();
	}

	public function offsetExists( $offset ) {
		return isset( $this->data[ $offset ] );
	}

	public function offsetGet( $offset ) {
		return $this->data[ $offset ] ?? null;
	}

	public function offsetSet( $offset, $value ) {
		// Cannot modify the response data.
	}

	public function offsetUnset( $offset ) {
		// Cannot modify the response data.
	}
}
