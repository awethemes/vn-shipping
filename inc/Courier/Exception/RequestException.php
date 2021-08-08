<?php

namespace VNShipping\Courier\Exception;

class RequestException extends \RuntimeException {
	/**
	 * @var mixed
	 */
	protected $rawBody;

	/**
	 * @return mixed
	 */
	public function getRawBody() {
		return $this->rawBody;
	}

	/**
	 * @param mixed $rawBody
	 * @return $this
	 */
	public function setRawBody( $rawBody ) {
		$this->rawBody = $rawBody;

		return $this;
	}
}
