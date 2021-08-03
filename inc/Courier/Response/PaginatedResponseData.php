<?php

namespace VNShipping\Courier\Response;

class PaginatedResponseData extends CollectionResponseData {
	/**
	 * @var int
	 */
	protected $currentPage;

	/**
	 * @var int
	 */
	protected $totalPages;

	/**
	 * PaginatedResponseData constructor.
	 *
	 * @param array $data
	 * @param $currentPage
	 * @param $totalPages
	 */
	public function __construct( array $data, $currentPage, $totalPages ) {
		parent::__construct( $data );

		$this->currentPage = $currentPage;
		$this->totalPages = $totalPages;
	}

	/**
	 * @return int
	 */
	public function getCurrentPage() {
		return $this->currentPage;
	}

	/**
	 * @return int
	 */
	public function getTotalPages() {
		return $this->totalPages;
	}
}
