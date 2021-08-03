<?php

namespace Tests;

/**
 * @property-read \WP_UnitTest_Factory $factory
 * @mixin \PHPUnit\Framework\Assert
 */
abstract class TestCase extends \WP_UnitTestCase_Base {
	/**
	 * Login as an user.
	 *
	 * @param int $user
	 */
	protected function loginAs( $user ) {
		wp_set_current_user( $user );
	}
}
