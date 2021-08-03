<?php

namespace VNShipping;

const UNINSTALL_DATA_PREFIX = 'vn_shipping';

/**
 * Deletes options and transients.
 *
 * @return void
 */
function delete_options() {
	global $wpdb;

	$prefix = UNINSTALL_DATA_PREFIX . '\_%';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$options = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
			$prefix
		)
	);

	if ( ! empty( $options ) ) {
		array_map( 'delete_option', $options );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$transients = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_' . $prefix,
			'_transient_timeout_' . $prefix
		)
	);

	if ( ! empty( $transients ) ) {
		array_map( 'delete_option', $transients );
	}
}

/**
 * Deletes options and transients on multisite.
 *
 * @return void
 */
function delete_site_options() {
	global $wpdb;

	$prefix = UNINSTALL_DATA_PREFIX . '\_%';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$options = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $wpdb->sitemeta WHERE meta_key LIKE %s",
			$prefix
		)
	);

	if ( ! empty( $options ) ) {
		foreach ( (array) $options as $option ) {
			delete_network_option( $option->site_id, $option->meta_key );
		}
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$transients = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT meta_key FROM $wpdb->sitemeta WHERE meta_key LIKE %s OR meta_key LIKE %s",
			'_site_transient_' . $prefix,
			'_site_transient_timeout_' . $prefix
		)
	);

	if ( ! empty( $transients ) ) {
		array_map( 'delete_site_option', (array) $transients );
	}
}

/**
 * Deletes all associated post meta data.
 *
 * @return void
 */
function delete_custom_post_meta() {
	// delete_post_meta_by_key( 'some_meta_key' );
	// delete_post_meta_by_key( 'some_meta_key' );
}

/**
 * Deletes all stories & templates.
 *
 * @return void
 */
function delete_posts() {
	/*$cpt_posts = get_posts(
		[
			'fields'           => 'ids',
			'suppress_filters' => false,
			'post_type'        => [ Story_Post_Type::POST_TYPE_SLUG, Template_Post_Type::POST_TYPE_SLUG ],
			'posts_per_page'   => - 1,
		]
	);

	foreach ( $cpt_posts as $post_id ) {
		wp_delete_post( (int) $post_id, true );
	}*/
}

/**
 * Delete all data on a site.
 *
 * @return void
 */
function delete_site() {
	delete_options();
	delete_posts();
	delete_custom_post_meta();
}
