<?php

namespace VNShipping\REST;

use VNShipping\Address\District;
use VNShipping\Address\Province;
use WP_REST_Controller;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class AddressController extends WP_REST_Controller {
	/**
	 * @var string
	 */
	protected $namespace = 'awethemes/vn-shipping';

	/**
	 * @var string
	 */
	protected $rest_base = 'address';

	/**
	 * {@inheritdoc}
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_provinces' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<province>[\d-]+)',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_districts' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
					'province' => [
						'type' => 'integer',
						'description' => 'The province code.',
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<province>[\d-]+)/(?P<district>[\d-]+)',
			[
				[
					'methods' => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_wards' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'args' => [
					'province' => [
						'type' => 'integer',
						'description' => 'The province code.',
					],
					'district' => [
						'type' => 'integer',
						'description' => 'The district code.',
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_item_permissions_check( $request ) {
		return true;
	}

	/**
	 * @return WP_REST_Response
	 */
	public function get_provinces() {
		return rest_ensure_response(
			array_values( Province::all() )
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_districts( $request ) {
		$province = Province::get_by_code( $request->get_param( 'province' ) );

		if ( ! $province ) {
			return new WP_Error(
				'error',
				'Invalid or missing state/province code (VN).',
				[ 'status' => 400 ]
			);
		}

		return rest_ensure_response(
			array_values( $province->get_districts() )
		);
	}

	/**
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_wards( $request ) {
		$district = District::get_by_code( $request->get_param( 'district' ) );

		if ( ! $district ) {
			return new WP_Error(
				'error',
				'Invalid or missing district or province code (VN).',
				[ 'status' => 400 ]
			);
		}

		return rest_ensure_response(
			array_values( $district->get_wards() )
		);
	}
}
