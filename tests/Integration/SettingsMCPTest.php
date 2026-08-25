<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Settings_MCP class, in particular that enabled()
 * is the single source of truth combining the toggle setting and the cached
 * account plan.
 *
 * @since   3.4.0
 */
class SettingsMCPTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.0
	 */
	public function setUp(): void
	{
		parent::setUp();
		activate_plugins('convertkit/wp-convertkit.php');
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		delete_option(\ConvertKit_Settings_MCP::SETTINGS_NAME);
		delete_option('convertkit_account');
		deactivate_plugins('convertkit/wp-convertkit.php');
		parent::tearDown();
	}

	/**
	 * Test that enabled() returns false when the MCP toggle is off, regardless
	 * of the cached plan type.
	 *
	 * @since   3.4.0
	 */
	public function testEnabledFalseWhenToggleOff()
	{
		update_option(\ConvertKit_Settings_MCP::SETTINGS_NAME, [ 'enabled' => '' ]);
		update_option(
			'convertkit_account',
			[ 'account' => [ 'plan_type' => 'creator_pro' ] ]
		);

		$settings = new \ConvertKit_Settings_MCP();
		$this->assertSame(false, $settings->enabled());
	}

	/**
	 * Test that enabled() returns false when the toggle is on but no account is
	 * cached (fail closed).
	 *
	 * @since   3.4.0
	 */
	public function testEnabledFalseWhenToggleOnAndNoAccountCache()
	{
		update_option(\ConvertKit_Settings_MCP::SETTINGS_NAME, [ 'enabled' => 'on' ]);
		delete_option('convertkit_account');

		$settings = new \ConvertKit_Settings_MCP();
		$this->assertSame(false, $settings->enabled());
	}

	/**
	 * Test that enabled() returns false when the toggle is on but the cached
	 * plan is free.
	 *
	 * @since   3.4.0
	 */
	public function testEnabledFalseWhenToggleOnAndFreePlan()
	{
		update_option(\ConvertKit_Settings_MCP::SETTINGS_NAME, [ 'enabled' => 'on' ]);
		update_option(
			'convertkit_account',
			[ 'account' => [ 'plan_type' => 'free' ] ]
		);

		$settings = new \ConvertKit_Settings_MCP();
		$this->assertSame(false, $settings->enabled());
	}

	/**
	 * Test that enabled() returns true when the toggle is on and the cached
	 * plan is a paid plan.
	 *
	 * @since   3.4.0
	 */
	public function testEnabledTrueWhenToggleOnAndPaidPlan()
	{
		update_option(\ConvertKit_Settings_MCP::SETTINGS_NAME, [ 'enabled' => 'on' ]);
		update_option(
			'convertkit_account',
			[ 'account' => [ 'plan_type' => 'creator' ] ]
		);

		$settings = new \ConvertKit_Settings_MCP();
		$this->assertSame(true, $settings->enabled());
	}

	/**
	 * Test that enabled() returns true for creator_pro plans.
	 *
	 * @since   3.4.0
	 */
	public function testEnabledTrueForCreatorProPlan()
	{
		update_option(\ConvertKit_Settings_MCP::SETTINGS_NAME, [ 'enabled' => 'on' ]);
		update_option(
			'convertkit_account',
			[ 'account' => [ 'plan_type' => 'creator_pro' ] ]
		);

		$settings = new \ConvertKit_Settings_MCP();
		$this->assertSame(true, $settings->enabled());
	}
}
