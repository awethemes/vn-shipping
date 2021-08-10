<?php

namespace VNShipping\Courier;

use Exception;
use VNShipping\Address\AddressMapper;
use VNShipping\Courier\Exception\BadResponseException;
use VNShipping\Courier\Exception\InvalidAddressDataException;
use VNShipping\Courier\Exception\UnsupportedMethodException;
use VNShipping\Courier\Response\CollectionResponseData;
use VNShipping\OptionsResolver\OptionsResolver;

class ViettelPost extends AbstractCourier {
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

		dd( $services );


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

		$response = $this->request(
			'/v2/order/getPriceAll',
			json_encode( $data )
		);

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
		// TODO: Implement create_order() method.
	}

	/**
	 * {@inheritdoc}
	 */
	public function cancel_order( $parameters ) {
		$response = $this->request(
			'/v2/order/UpdateOrder',
			json_encode( [
				'TYPE' => 4, // Cancel order.
				'ORDER_NUMBER' => $parameters->get( 'ORDER_NUMBER' ),
			] )
		);
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
		}
	}
}
