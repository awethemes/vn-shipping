<?php

namespace VNShipping\Address;

class AddressMapper {
	/**
	 * @var string
	 */
	protected $courier;

	/**
	 * Constructor.
	 *
	 * @param string $courier
	 */
	public function __construct( $courier ) {
		$this->courier = strtolower( $courier );
	}

	/**
	 * Get province code.
	 *
	 * @param Province|string $province
	 * @return string|null
	 */
	public function get_province_code( $province ) {
		if ( $province instanceof Province ) {
			$province = $province->get_code();
		}

		$province = sprintf( '%02s', $province );

		return $this->get_maps( 'provinces' )[ $province ] ?? null;
	}

	/**
	 * Get district code.
	 *
	 * @param District|string $district
	 * @return string|null
	 */
	public function get_district_code( $district ) {
		if ( $district instanceof District ) {
			$district = $district->get_code();
		}

		$district = sprintf( '%03s', $district );

		return $this->get_maps( 'districts' )[ $district ] ?? null;
	}

	/**
	 * Get the ward code.
	 *
	 * @param Ward|string $ward
	 * @return string|null
	 */
	public function get_ward_code( $ward ) {
		if ( $ward instanceof Ward ) {
			$ward = $ward->get_code();
		}

		$ward = sprintf( '%05s', $ward );

		return $this->get_maps( 'wards' )[ $ward ] ?? null;
	}

	/**
	 * @param string $path
	 * @return array
	 */
	protected function get_maps( $path ) {
		return DataLoader::load_php_data( $this->courier . '-map.php' )[ $path ];
	}
}
