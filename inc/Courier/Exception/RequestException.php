<?php

namespace VNShipping\Courier\Exception;

class RequestException extends \RuntimeException {
	/**
	 * @var string
	 */
	protected $rawBody;

	/**
	 * @return string
	 */
	public function getRawBody() {
		return $this->rawBody;
	}

	/**
	 * @param string $rawBody
	 * @return $this
	 */
	public function setRawBody( $rawBody ) {
		$this->rawBody = $rawBody;

		return $this;
	}
}
