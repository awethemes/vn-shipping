<?php

namespace VNShipping\Courier;

use Exception;
use VNShipping\Address\AddressMapper;
use VNShipping\Courier\Exception\BadResponseException;
use VNShipping\Courier\Exception\InvalidAddressDataException;
use VNShipping\Courier\Response\CollectionResponseData;
use VNShipping\Courier\Response\ShippingOrderResponseData;
use VNShipping\OptionsResolver\OptionsResolver;

class ViettelPost extends AbstractCourier {
	use ManageStoreTrait;

	/* Const */
	const BASE_URL = 'https://partner.viettelpost.vn';

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * Constructor.
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function __construct( $username, $password ) {
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_base_url() {
		return self::BASE_URL;
	}

	/**
	 * Login to get the access token.
	 *
	 * @param bool $force
	 * @return Response\JsonResponseData|null
	 */
	public function request_access_token( $force = false ) {
		if ( $this->access_token && $force === false ) {
			return null;
		}

		$response = $this->request(
			'/v2/user/Login',
			json_encode( [
				'USERNAME' => $this->username,
				'PASSWORD' => $this->password,
			] )
		);

		if ( isset( $response['error'] ) && $response['error'] === true ) {
			throw new BadResponseException( $response['message'] ?? '', $response['status'] );
		}

		self::assertResponseHasKey( $response, 'data' );

		if ( isset( $response['data']['token'] ) ) {
			$this->set_access_token( $response['data']['token'] );
		}

		return self::newJsonResponseData( $response['data'] );
	}

	/**
	 * @return CollectionResponseData
	 */
	public function get_stores( $parameters ) {
		$response = $this->request( '/v2/user/listInventory', [], 'GET' );

		self::assertResponseHasKey( $response, 'data' );

		return self::newCollectionResponseData( $response['data'] ?: [] );
	}

	/**
	 * @return CollectionResponseData
	 */
	public function get_all_services() {
		$response = $this->request(
			'/v2/categories/listService',
			json_encode( [ 'TYPE' => 2 ] )
		);

		self::assertResponseHasKey( $response, 'data' );
		$services = $response['data'] ?: [];

		foreach ( $services as &$service ) {
			try {
				$service['EXTRA_SERVICES'] = $this->request(
					'/v2/categories/listServiceExtra?serviceCode=' . $service['SERVICE_CODE'],
					null,
					'GET'
				);
			} catch ( Exception $e ) {
				continue;
			}
		}

		unset( $service );

		$services = self::newCollectionResponseData( $services );

		return self::newCollectionResponseData( $services['data'] ?: [] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_available_services( $parameters ) {
		if ( ! $parameters instanceof RequestParameters ) {
			$parameters = new RequestParameters( $parameters );
		}

		$data = $parameters->validate(
			function ( OptionsResolver $options ) {
				$options->define( 'PRODUCT_PRICE' )->asInt()->required();
				$options->define( 'MONEY_COLLECTION' )->asInt()->required();
				$options->define( 'PRODUCT_WEIGHT' )->asInt()->required(); // gram unit.

				$options->define( 'SENDER_PROVINCE' )->asInt()->required(); // TODO: Check this later.
				$options->define( 'SENDER_DISTRICT' )->asInt()->required();

				$options->define( 'RECEIVER_PROVINCE' )->asInt()->required();
				$options->define( 'RECEIVER_DISTRICT' )->asInt()->required();
			}
		);

		$data['TYPE'] = 1;
		$data['PRODUCT_TYPE'] = 'HH';

		$this->remap_address_code( $data );

		$response = $this->request( '/v2/order/getPriceAll', json_encode( $data ) );

		self::assertResponseValid( $response );

		return self::newCollectionResponseData( $response );
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

			}
		);

		$data['TYPE'] = 1;
		$data['PRODUCT_TYPE'] = 'HH';

		$response = $this->request(
			'/v2/order/getPriceAll',
			json_encode( $data )
		);

		return self::newCollectionResponseData( $response );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_order( $parameters ) {

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
				$options->define( 'ORDER_NUMBER' )->asString()->required();
				$options->define( 'GROUPADDRESS_ID' )->asInt()->default( $this->get_store_id() );
				$options->define( 'CUS_ID' )->asInt()->nullable();

				$options->define( 'DELIVERY_DATE' )->asString()->nullable(); // dd/MM/yyyy H:m:s

				$options->define( 'SENDER_FULLNAME' )->asString()->nullable();
				$options->define( 'SENDER_ADDRESS' )->asString()->nullable();
				$options->define( 'SENDER_PHONE' )->asString()->nullable();
				$options->define( 'SENDER_EMAIL' )->asString()->default( '' );
				$options->define( 'SENDER_WARD' )->asNumeric()->nullable();
				$options->define( 'SENDER_DISTRICT' )->asNumeric()->nullable();
				$options->define( 'SENDER_PROVINCE' )->asNumeric()->nullable();
				$options->define( 'SENDER_LATITUDE' )->asNumeric()->default( 0 );
				$options->define( 'SENDER_LONGITUDE' )->asNumeric()->default( 0 );

				$options->define( 'RECEIVER_FULLNAME' )->asString()->nullable();
				$options->define( 'RECEIVER_ADDRESS' )->asString()->nullable();
				$options->define( 'RECEIVER_PHONE' )->asString()->nullable();
				$options->define( 'RECEIVER_EMAIL' )->asString()->nullable();
				$options->define( 'RECEIVER_WARD' )->asNumeric()->required();
				$options->define( 'RECEIVER_DISTRICT' )->asNumeric()->required();
				$options->define( 'RECEIVER_PROVINCE' )->asNumeric()->required();
				$options->define( 'RECEIVER_LATITUDE' )->asNumeric()->default( 0 );
				$options->define( 'RECEIVER_LONGITUDE' )->asNumeric()->default( 0 );

				$options->define( 'PRODUCT_NAME' )->asString()->default( '' );
				$options->define( 'PRODUCT_DESCRIPTION' )->asString()->default( '' );
				$options->define( 'PRODUCT_QUANTITY' )->asInt()->default( 1 );
				$options->define( 'PRODUCT_PRICE' )->asInt()->required();
				$options->define( 'PRODUCT_WEIGHT' )->asInt()->required();
				$options->define( 'PRODUCT_LENGTH' )->asInt()->required();
				$options->define( 'PRODUCT_WIDTH' )->asInt()->required();
				$options->define( 'PRODUCT_HEIGHT' )->asInt()->required();

				$options->define( 'ORDER_PAYMENT' )->asInt()->default( 1 ); // 1-4
				$options->define( 'ORDER_SERVICE' )->asString()->required();
				$options->define( 'ORDER_SERVICE_ADD' )->asString()->default( '' );
				$options->define( 'ORDER_VOUCHER' )->asString()->default( '' );
				$options->define( 'ORDER_NOTE' )->asString()->nullable();

				$options->define( 'MONEY_COLLECTION' )->asInt()->required();
				$options->define( 'MONEY_TOTALFEE' )->asInt()->default( 0 );
				$options->define( 'MONEY_FEECOD' )->asInt()->default( 0 );
				$options->define( 'MONEY_FEEVAS' )->asInt()->default( 0 );
				$options->define( 'MONEY_FEEINSURANCE' )->asInt()->default( 0 );
				$options->define( 'MONEY_FEE' )->asInt()->default( 0 );
				$options->define( 'MONEY_FEEOTHER' )->asInt()->default( 0 );
				$options->define( 'MONEY_TOTALVAT' )->asInt()->default( 0 );
				$options->define( 'MONEY_TOTAL' )->asInt()->default( 0 );

				$options->define( 'LIST_ITEM' )
					->required()
					->allowedTypes( 'array' );
			}
		);

		$data['PRODUCT_TYPE'] = 'HH';
		$this->remap_address_code( $data );

		$response = $this->request( '/v2/order/createOrder', json_encode( $data ) );

		self::assertResponseValid( $response, 'data' );

		return new ShippingOrderResponseData(
			$response['data']['ORDER_NUMBER'],
			$response['data']
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
				$options->define( 'order_code' )->asString()->nullable();
				$options->define( 'ORDER_NUMBER' )->asString()->nullable();
			}
		);

		$code = $data['ORDER_NUMBER'] ?? $data['order_code'];

		$response = $this->request(
			'/v2/order/UpdateOrder',
			json_encode( [
				'TYPE' => 4, // Cancel order code.
				'ORDER_NUMBER' => $code,
			] )
		);

		self::assertResponseValid( $response );

		return self::newJsonResponseData( [ 'message' => $response['message'] ?? '' ] );
	}

	/**
	 * @return CollectionResponseData
	 */
	public function get_province() {
		$response = $this->request(
			'/v2/categories/listProvinceById?provinceId=-1',
			[],
			'GET'
		);

		self::assertResponseHasKey( $response, 'data' );

		return self::newCollectionResponseData( $response['data'] ?: [] );
	}

	/**
	 * @param int $province
	 * @return CollectionResponseData
	 */
	public function get_district( $province ) {
		$response = $this->request(
			'/v2/categories/listDistrict?provinceId=' . $province,
			[],
			'GET'
		);

		self::assertResponseHasKey( $response, 'data' );

		return self::newCollectionResponseData( $response['data'] ?: [] );
	}

	/**
	 * @param int $district
	 * @return CollectionResponseData
	 */
	public function get_wards( $district ) {
		$response = $this->request(
			'/v2/categories/listWards?districtId=' . $district,
			[],
			'GET'
		);

		self::assertResponseHasKey( $response, 'data' );

		return self::newCollectionResponseData( $response['data'] ?: [] );
	}

	/**
	 * @param array $data
	 */
	protected function remap_address_code( array &$data ) {
		$addressMapper = new AddressMapper( 'vtp' );

		/*if ( $data['SENDER_PROVINCE'] ?? null ) {
			$data['SENDER_PROVINCE'] = (int) $addressMapper->get_province_code( $data['SENDER_PROVINCE'] );
			InvalidAddressDataException::throwIf( ! $data['SENDER_PROVINCE'] );

			$data['SENDER_DISTRICT'] = (int) $addressMapper->get_district_code( $data['SENDER_DISTRICT'] );
			InvalidAddressDataException::throwIf( ! $data['SENDER_DISTRICT'] );
		}*/

		if ( $data['RECEIVER_PROVINCE'] ?? null ) {
			$data['RECEIVER_PROVINCE'] = (int) $addressMapper->get_province_code( $data['RECEIVER_PROVINCE'] );
			InvalidAddressDataException::throwIf( ! $data['RECEIVER_PROVINCE'] );

			$data['RECEIVER_DISTRICT'] = (int) $addressMapper->get_district_code( $data['RECEIVER_DISTRICT'] );
			InvalidAddressDataException::throwIf( ! $data['RECEIVER_DISTRICT'] );

			if ( ! empty( $data['RECEIVER_WARD'] ) ) {
				$data['RECEIVER_WARD'] = (int) $addressMapper->get_ward_code( $data['RECEIVER_WARD'] );
				InvalidAddressDataException::throwIf( ! $data['RECEIVER_WARD'] );
			}
		}
	}

	/**
	 * @param array  $response
	 * @param string ...$keys
	 */
	protected static function assertResponseValid( $response, ...$keys ) {
		// Check the response is success again since GHTK response status 200 but got success = false.
		if ( isset( $response['error'] ) && $response['error'] === true ) {
			throw ( new BadResponseException( $response['message'] ?? '' ) )->setRawBody( $response );
		}

		if ( count( $keys ) > 0 ) {
			static::assertResponseHasKey( $response, ...$keys );
		}
	}
}
