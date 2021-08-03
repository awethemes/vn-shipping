<?php

namespace VNShipping\Courier\Response;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class CollectionResponseData extends JsonResponseData implements Countable, IteratorAggregate {
	/**
	 * Count elements of the collection.
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->data );
	}

	/**
	 * @return ArrayIterator
	 */
	public function getIterator() {
		return new ArrayIterator( $this->data );
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->data;
	}
}
