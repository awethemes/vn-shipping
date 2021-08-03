<?php

namespace VNShipping;

class DatabaseUpgrader {
	/**
	 * @var string
	 */
	public const OPTION = 'vn_shipping_db_version';

	/**
	 * @var string
	 */
	public const PREVIOUS_OPTION = 'vn_shipping_previous_db_version';

	/**
	 * Hooked into admin_init and walks through an array of upgrade methods.
	 *
	 * @return void
	 */
	public function init() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		$routines = [
			'1.0.0' => 'initial',
		];

		$version = get_option( self::OPTION, '0.0.0' );
		if ( version_compare( VN_SHIPPING_DB_VERSION, $version, '=' ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		array_walk( $routines, [ $this, 'run_upgrade_routine' ], $version );

		$this->finish_up( $version );
	}

	/**
	 * Runs the upgrade routine.
	 *
	 * @param string $routine         The method to call.
	 * @param string $version         The new version.
	 * @param string $current_version The current set version.
	 *
	 * @return void
	 */
	protected function run_upgrade_routine( $routine, $version, $current_version ) {
		if ( version_compare( $current_version, $version, '<' ) ) {
			$this->$routine( $current_version );
		}
	}

	/**
	 * First database migration.
	 *
	 * @return void
	 */
	protected function initial() {
		global $wpdb;

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$schema = <<<SQL
CREATE TABLE {$wpdb->prefix}vn_shipping_data (
    id                     BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id               BIGINT(20) UNSIGNED NOT NULL,
    courier                VARCHAR(40) NOT NULL,
    tracking_number        VARCHAR(40) NOT NULL,
    status                 VARCHAR(20) NOT NULL,
    PRIMARY KEY (id),
    KEY order_id(order_id),
    KEY courier(courier, status),
    UNIQUE KEY tracking_number(courier, tracking_number)
) $collate;
SQL;

		dbDelta( $schema );
	}

	/**
	 * Flush rewrites.
	 *
	 * @return void
	 */
	protected function rewrite_flush() {
		flush_rewrite_rules( false );
	}

	/**
	 * Runs the needed cleanup after an update, setting the DB version to latest version, flushing caches etc.
	 *
	 * @param string $previous_version The previous version.
	 *
	 * @return void
	 */
	protected function finish_up( $previous_version ) {
		update_option( self::PREVIOUS_OPTION, $previous_version );
		update_option( self::OPTION, VN_SHIPPING_DB_VERSION );
	}
}
