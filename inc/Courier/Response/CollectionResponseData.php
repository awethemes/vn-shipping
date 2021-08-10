<?php

namespace VNShipping\Courier\Response;

use ArrayIterator;
use Closure;
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
	 * @param Closure $callback
	 * @return mixed|null
	 */
	public function find( Closure $callback ) {
		foreach ( $this->data as $data ) {
			if ( $callback( $data ) ) {
				return $data;
			}
		}

		return null;
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
