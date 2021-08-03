<?php

namespace VNShipping\Courier\Response;

class ShippingOrderResponseData extends JsonResponseData {
	/**
	 * @var string
	 */
	protected $tracking_number;

	/**
	 * JsonResponseData constructor.
	 *
	 * @param string $tracking_number
	 * @param array  $data
	 */
	public function __construct( string $tracking_number, array $data ) {
		$this->tracking_number = $tracking_number;

		parent::__construct( $data );
	}

	/**
	 * Returns the tracking number.
	 *
	 * @return string
	 */
	public function get_tracking_number() {
		return $this->tracking_number;
	}

	/**
	 * {@inheritdoc}
	 */
	public function to_array() {
		return [
			'tracking_number' => $this->get_tracking_number(),
			'data' => $this->get_data(),
		];
	}
}
