<?php

namespace VNShipping\Courier;

use VNShipping\Address\District;
use VNShipping\Address\Province;
use VNShipping\Address\Ward;
use VNShipping\Courier\Exception\BadResponseException;
use VNShipping\Courier\Exception\InvalidAddressDataException;
use VNShipping\Courier\Exception\UnsupportedMethodException;
use VNShipping\Courier\Response\ShippingOrderResponseData;
use VNShipping\OptionsResolver\OptionConfigurator;
use VNShipping\OptionsResolver\OptionsResolver;
use VNShipping\Vendor\Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver;

class GHTK extends AbstractCourier {
	use ManageStoreTrait;

	/* Const */
	const BASE_URL = 'https://services.giaohangtietkiem.vn';
	const DEV_BASE_URL = 'https://services.ghtklab.com';

	/**
	 * GHTK constructor.
	 *
	 * @param string $access_token
	 * @param false  $is_debug
	 */
	public function __construct( $access_token, $is_debug = false ) {
		$this->access_token = $access_token;
		$this->is_debug = $is_debug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_base_url() {
		return $this->is_debug() ? self::DEV_BASE_URL : self::BASE_URL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_lead_time( $parameters ) {
		throw new UnsupportedMethodException(
			__( 'Thấy thông tin ngày giao hàng không được hỗ trợ bởi GHTK.', 'vn-shipping' )
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_stores( $parameters ) {
		$response = $this->request( '/services/shipment/list_pick_add' );

		self::assertResponseValid( $response, 'data' );

		return self::newCollectionResponseData( $response['data'] ?: [] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_shipping_fee( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'pick_address_id' )->asInt();
				$options->define( 'pick_address' )->asString();
				$options->define( 'pick_province' )->asString()->required();
				$options->define( 'pick_district' )->asString()->required();
				$options->define( 'pick_ward' )->asString();
				$options->define( 'pick_street' )->asString();

				$options->define( 'address' )->asString();
				$options->define( 'province' )->asString()->required();
				$options->define( 'district' )->asString()->required();
				$options->define( 'ward' )->asString();
				$options->define( 'street' )->asString();

				$options->define( 'weight' )->asInt()->required();
				$options->define( 'value' )->asInt();
				$options->define( 'transport' )->asString()->default( 'fly' );
				$options->define( 'deliver_option' )
					->asString()
					->allowedValues( 'xteam', 'none' )
					->default( 'none' );
			}
		);

		$response = $this->request( '/services/shipment/fee', $data, 'GET' );

		self::assertResponseValid( $response, 'fee' );

		return self::newJsonResponseData( $response['fee'] ?: [] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_order( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'order_code' )->asString()->required();
			}
		);

		$response = $this->request(
			'/services/shipment/v2/' . $data['order_code'],
			null,
			'GET'
		);

		self::assertResponseValid( $response, 'order' );

		return new ShippingOrderResponseData(
			$response['order']['label_id'],
			$response['order']
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function cancel_order( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'order_code' )->asString()->required();
			}
		);

		$response = $this->request(
			'/services/shipment/cancel/' . $data['order_code']
		);

		return self::newJsonResponseData( $response );
	}

	/**
	 * {@inheritdoc}
	 */
	public function create_order( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'products' )
					->allowedTypes( 'array' )
					->required();

				$options->setDefault(
					'order',
					function ( SymfonyOptionsResolver $orderResolver ) {
						$define = function ( $name ) use ( $orderResolver ) {
							return new OptionConfigurator( $name, $orderResolver );
						};

						$define( 'id' )->asString()->required();
						$define( 'pick_address_id' )
							->asString()
							->default( (string) $this->get_store_id() );
						$define( 'pick_address' )->asString()->default( '_' );
						$define( 'pick_province' )->asString()->default( '_' );
						$define( 'pick_district' )->asString()->default( '_' );
						$define( 'pick_ward' )->asString()->default( '' );

						$define( 'name' )->asString()->required();
						$define( 'email' )->asString()->required();
						$define( 'address' )->asString()->required();
						$define( 'province' )->asString()->required();
						$define( 'district' )->asString()->required();
						$define( 'ward' )->asString()->required();
						$define( 'street' )->asString();
						$define( 'hamlet' )->asString();
						$define( 'tel' )->asString()->required();
						$define( 'note' )->asString( true );

						$define( 'pick_money' )->asInt()->required();
						$define( 'pick_name' )->asString();
						$define( 'pick_tel' )->asString();

						$define( 'is_freeship' )->asNumeric()->allowedValues( 0, 1 );
						$define( 'value' )->asNumeric()->required();
						$define( 'total_weight' )->asNumeric()->required();
						$define( 'transport' )->required()->allowedValues( 'road', 'fly' );
						$define( 'pick_option' )->default( 'cod' )->allowedValues( 'cod', 'post' );

						$define( 'pick_work_shift' )->asNumeric()->allowedValues( 1, 2, 3 );
						$define( 'deliver_work_shift' )->asNumeric()->allowedValues( 1, 2, 3 );

						$define( 'tags' )->allowedTypes( 'array' )->default( [] );
					}
				);
			}
		);

		// Fixed some attributes.
		$data['order']['hamlet'] = 'Khác';
		$data['order']['weight_option'] = 'gram';

		$this->remap_address_name( $data['order'] );

		$response = $this->request(
			'/services/shipment/order',
			json_encode( $data )
		);

		self::assertResponseValid( $response, 'order' );

		return new ShippingOrderResponseData(
			$response['order']['label'],
			$response['order']
		);
	}

	/**
	 * @param array $data
	 */
	protected function remap_address_name( array &$data ) {
		$data['province'] = Province::get_by_code( $data['province'] )->name_with_type ?? null;
		InvalidAddressDataException::throwIf( ! $data['province'] );

		$data['district'] = District::get_by_code( $data['district'] )->name_with_type ?? null;
		InvalidAddressDataException::throwIf( ! $data['district'] );

		$data['ward'] = Ward::get_by_code( $data['ward'] )->name_with_type ?? null;
		InvalidAddressDataException::throwIf( ! $data['ward'] );
	}

	/**
	 * @param array  $response
	 * @param string ...$keys
	 */
	protected static function assertResponseValid( $response, ...$keys ) {
		// Check the response is success again since GHTK response status 200 but got success = false.
		if ( isset( $response['success'] ) && $response['success'] === false ) {
			throw ( new BadResponseException( $response['message'] ?? '' ) )->setRawBody( $response );
		}

		if ( count( $keys ) > 0 ) {
			static::assertResponseHasKey( $response, ...$keys );
		}
	}
}
