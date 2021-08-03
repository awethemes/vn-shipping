<?php

namespace VNShipping;

use VNShipping\Address\District;
use VNShipping\Address\Province;
use VNShipping\Address\Ward;
use WC_Order;

class AddressHooks {
	/**
	 * Init the address hooks.
	 */
	public function __construct() {
		add_filter( 'woocommerce_states', [ $this, 'register_vn_states' ] );
		add_filter( 'woocommerce_localisation_address_formats', [ $this, 'localisation_address_formats' ] );
		add_filter( 'woocommerce_formatted_address_replacements', [ $this, 'formatted_address_replacements' ], 10, 2 );

		// Front-end hooks.
		add_filter( 'woocommerce_cart_shipping_packages', [ $this, 'cart_shipping_packages_destination' ] );
		add_filter( 'woocommerce_get_country_locale', [ $this, 'setup_country_locale_fields' ] );

		// Admin-area hooks.
		add_filter( 'woocommerce_admin_billing_fields', [ $this, 'setup_admin_address_fields' ], 5 );
		add_filter( 'woocommerce_admin_shipping_fields', [ $this, 'setup_admin_address_fields' ], 5 );
	}

	/**
	 * Register Vietnam "provinces" as states field.
	 *
	 * @param array $states
	 * @return array
	 */
	public function register_vn_states( $states ) {
		$states['VN'] = wp_list_pluck( Province::all(), 'name_with_type', 'code' );

		return $states;
	}

	/**
	 * Define the vietnam address for display.
	 *
	 * @param array $address_formats
	 * @return array
	 */
	public function localisation_address_formats( $address_formats ) {
		$address_formats['VN'] = "{name}\n{company}\n{address_1}\n{address_2}, {city}\n{state}";

		return $address_formats;
	}

	/**
	 * Format the vietnam address replacements.
	 *
	 * @param array $replacements
	 * @param array $args
	 * @return array
	 */
	public function formatted_address_replacements( $replacements, $args ) {
		if ( $args['country'] !== 'VN' ) {
			return $replacements;
		}

		if ( $ward = Ward::get_by_code( $args['address_2'] ) ) {
			$replacements['{address_2}'] = $ward->name_with_type;
			$replacements['{address_2_upper}'] = wc_strtoupper( $replacements['{address_2}'] );
		}

		if ( $district = District::get_by_code( $args['city'] ) ) {
			$replacements['{city}'] = $district->name_with_type;
			$replacements['{city_upper}'] = wc_strtoupper( $replacements['{city}'] );
		}

		return $replacements;
	}

	/**
	 * Add province, district, ward to the shipping destination context.
	 *
	 * @param array $packages
	 * @return array
	 */
	public function cart_shipping_packages_destination( $packages ) {
		$customer = WC()->cart->get_customer();

		foreach ( $packages as &$package ) {
			if ( ( $package['destination']['country'] ?? null ) !== 'VN' ) {
				continue;
			}

			// In Vietnam, we consider "state" as "province", "city" as "district"
			// and "address_2" as "ward"
			$package['destination'] = array_merge(
				$package['destination'],
				[
					'province' => $customer->get_shipping_state(),
					'district' => $customer->get_shipping_city(),
					'ward' => $customer->get_shipping_address_2(),
				]
			);
		}

		return $packages;
	}

	/**
	 * @param array $locale
	 * @return array
	 */
	public function setup_country_locale_fields( $locale ) {
		$locale['VN'] = array_merge(
			$locale['VN'] ?? [],
			[
				'state' => [
					'label' => esc_html__( 'Province', 'vn-shipping' ),
					'placeholder' => __( 'Province', 'vn-shipping' ),
					'required' => true,
					'hidden' => false,
					'priority' => 42,
				],
				'city' => [
					'label' => esc_html__( 'District', 'vn-shipping' ),
					'placeholder' => esc_html__( 'District', 'vn-shipping' ),
					'class' => [ 'address-field' ],
					'required' => true,
					'hidden' => false,
					'priority' => 44,
				],
				'address_2' => [
					'label' => esc_html__( 'Ward', 'vn-shipping' ),
					'placeholder' => esc_html__( 'Ward', 'vn-shipping' ),
					'class' => [ 'address-field' ],
					'required' => false,
					'hidden' => false,
					'priority' => 46,
				],
			]
		);

		return $locale;
	}

	/**
	 * Custom billing and shipping fields in meta-box.
	 *
	 * @param array $fields
	 * @return array
	 *
	 * @see \WC_Meta_Box_Order_Data::init_address_fields()
	 */
	public function setup_admin_address_fields( $fields ) {
		global $theorder;

		if ( ! $theorder instanceof WC_Order ) {
			return $fields;
		}

		$_country = strpos( current_filter(), 'billing_fields' )
			? $theorder->get_billing_country( 'edit' )
			: $theorder->get_shipping_country( 'edit' );

		if ( $_country !== 'VN' ) {
			return $fields;
		}

		// We perform sorting the columns by priority.
		$priorities = [
			'first_name' => 10,
			'last_name' => 10,

			'country' => 15,
			'state' => 16, // Province code.
			'city' => 17, // District code.
			'address_2' => 18, // Ward code.
			'address_1' => 19,
			'postcode' => 20,

			'company' => 30,
		];

		foreach ( $fields as $name => $field ) {
			$fields[ $name ]['priority'] = $priorities[ $name ] ?? 10;
		}

		return wp_list_sort( $fields, 'priority', 'ASC', true );
	}
}
