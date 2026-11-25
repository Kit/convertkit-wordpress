<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Admin_Section_Tools class.
 *
 * @since   3.5.3
 */
class AdminSectionToolsTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.5.3
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Throw an exception on redirect, as the Tools screen exits after redirecting.
		add_filter('wp_redirect', array( $this, 'throwOnRedirect' ));
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.5.3
	 */
	public function tearDown(): void
	{
		remove_filter('wp_redirect', array( $this, 'throwOnRedirect' ));
		unset($_REQUEST['_convertkit_settings_tools_nonce'], $_REQUEST['convertkit-clear-debug-log']);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that an Administrator can run Tools actions, such as clearing the debug log.
	 *
	 * @since   3.5.3
	 */
	public function testAdministratorCanRunActions()
	{
		$this->clearLogAs('administrator');

		// Confirm the log was cleared.
		$this->assertStringNotContainsString('Kit test log entry', $this->readLog());
	}

	/**
	 * Test that a user without the manage_options capability can't run Tools actions,
	 * even with a valid nonce.
	 *
	 * @since   3.5.3
	 */
	public function testNonAdministratorCannotRunActions()
	{
		$this->clearLogAs('editor');

		// Confirm the log wasn't cleared.
		$this->assertStringContainsString('Kit test log entry', $this->readLog());
	}

	/**
	 * Writes a log entry, then requests the Tools screen's clear debug log action as a user with the given role,
	 * using a valid nonce for that user.
	 *
	 * @since   3.5.3
	 *
	 * @param   string $role   User role.
	 */
	private function clearLogAs($role)
	{
		$log = new \ConvertKit_Log(CONVERTKIT_PLUGIN_PATH);
		$log->add('Kit test log entry');

		wp_set_current_user(static::factory()->user->create([ 'role' => $role ]));
		$_REQUEST['_convertkit_settings_tools_nonce'] = wp_create_nonce('convertkit-settings-tools');
		$_REQUEST['convertkit-clear-debug-log']       = '1';

		try {
			new \ConvertKit_Admin_Section_Tools();
		} catch ( \Exception $e ) {
			// The Tools screen redirects after clearing the log.
			$this->assertEquals('redirect', $e->getMessage());
		}
	}

	/**
	 * Returns the debug log's contents.
	 *
	 * @since   3.5.3
	 *
	 * @return  string
	 */
	private function readLog()
	{
		$log = new \ConvertKit_Log(CONVERTKIT_PLUGIN_PATH);
		return (string) $log->read();
	}

	/**
	 * Throws an exception when redirecting, as the Tools screen exits after redirecting.
	 *
	 * @since   3.5.3
	 *
	 * @param   string $location   Redirect URL.
	 * @throws  \Exception         Redirect.
	 */
	public function throwOnRedirect($location)
	{
		throw new \Exception('redirect');
	}
}
