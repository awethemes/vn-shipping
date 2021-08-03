<?php

namespace Tests\Courier;

use Tests\TestCase;
use VNShipping\Address\Province;
use VNShipping\Courier\GHN;
use VNShipping\Courier\Request\ShippingFeeParameters;

class GHNTest extends TestCase {
	public function test_get_province() {
		$ghn = new GHN( '64832b65-2fd3-11eb-a18f-227f832b612c', 76300, true );

		$param = new ShippingFeeParameters( [
			'shop_id' => 76300,
			'service_type_id' => GHN::SERVICE_TYPE_STANDARD,

			'from_district' => 1461,
			'to_ward' => 20314,
			'to_district' => 1444,

			'width' => 15, // (cm)
			'height' => 15, // (cm)
			'length' => 15, // (cm)
			'weight' => 900, // (gram)
		] );

		$a = Province::get_by_code( 11 );
		dd( $a );

		// dd( $ghn->get_province() );

		dump(
			$ghn->get_available_services( [
				'from_district' => 1461,
				'to_district' => 1444,
			] )
		);
	}
}
