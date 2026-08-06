<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Resource_Account class.
 *
 * @since   3.4.0
 */
class ResourceAccountTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Settings class.
	 *
	 * @since   3.4.0
	 *
	 * @var     \ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Holds the ConvertKit Resource class.
	 *
	 * @since   3.4.0
	 *
	 * @var     ConvertKit_Resource_Account
	 */
	private $resource;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Store credentials in Plugin's settings.
		$this->settings = new \ConvertKit_Settings();
		update_option(
			$this->settings::SETTINGS_NAME,
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		// Initialize the resource class we want to test.
		$this->resource = new \ConvertKit_Resource_Account();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->resource->resources);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		// Delete Credentials and Resources from Plugin's settings.
		delete_option($this->settings::SETTINGS_NAME);
		delete_option($this->resource->settings_name);
		delete_option($this->resource->settings_name . '_last_queried');

		// Destroy the resource class we tested.
		unset($this->resource);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the refresh() function performs as expected.
	 *
	 * @since   3.4.0
	 */
	public function testRefresh()
	{
		// Confirm that the data is stored in the options table and includes some expected keys.
		$result = $this->resource->refresh();
		$this->assertIsArray($result);

		// Check user array.
		$this->assertArrayHasKey('user', $result);
		$this->assertArrayHasKey('id', $result['user']);
		$this->assertArrayHasKey('email', $result['user']);

		// Check account array.
		$this->assertArrayHasKey('account', $result);
		$this->assertArrayHasKey('id', $result['account']);
		$this->assertArrayHasKey('name', $result['account']);
		$this->assertArrayHasKey('plan_type', $result['account']);
		$this->assertArrayHasKey('primary_email_address', $result['account']);
		$this->assertArrayHasKey('created_at', $result['account']);
		$this->assertArrayHasKey('plan', $result['account']);

		// Check account plan array.
		$this->assertArrayHasKey('plan_type', $result['account']['plan']);
		$this->assertArrayHasKey('interval', $result['account']['plan']);
		$this->assertArrayHasKey('subscriber_limit', $result['account']['plan']);
		$this->assertArrayHasKey('on_trial', $result['account']['plan']);
		$this->assertArrayHasKey('trial_lapse_date', $result['account']['plan']);
		$this->assertArrayHasKey('renews_at', $result['account']['plan']);
		$this->assertArrayHasKey('cancels_at', $result['account']['plan']);

		// Call resource class' get() function.
		$result = $this->resource->get();

		// Check user array.
		$this->assertArrayHasKey('user', $result);
		$this->assertArrayHasKey('id', $result['user']);
		$this->assertArrayHasKey('email', $result['user']);

		// Check account array.
		$this->assertArrayHasKey('account', $result);
		$this->assertArrayHasKey('id', $result['account']);
		$this->assertArrayHasKey('name', $result['account']);
		$this->assertArrayHasKey('plan_type', $result['account']);
		$this->assertArrayHasKey('primary_email_address', $result['account']);
		$this->assertArrayHasKey('created_at', $result['account']);
		$this->assertArrayHasKey('plan', $result['account']);

		// Check account plan array.
		$this->assertArrayHasKey('plan_type', $result['account']['plan']);
		$this->assertArrayHasKey('interval', $result['account']['plan']);
		$this->assertArrayHasKey('subscriber_limit', $result['account']['plan']);
		$this->assertArrayHasKey('on_trial', $result['account']['plan']);
		$this->assertArrayHasKey('trial_lapse_date', $result['account']['plan']);
		$this->assertArrayHasKey('renews_at', $result['account']['plan']);
		$this->assertArrayHasKey('cancels_at', $result['account']['plan']);
	}

	/**
	 * Test that get() returns false when no cache has been written yet.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsFalseWhenNoCache()
	{
		$this->assertSame(false, $this->resource->get());
	}

	/**
	 * Test that get_plan_type() returns null when no cache has been written yet.
	 *
	 * @since   3.4.0
	 */
	public function testGetPlanTypeReturnsFalseWhenNoCache()
	{
		$this->assertSame(false, $this->resource->get_plan_type());
	}

	/**
	 * Test that is_paid_plan() fails closed when no cache has been written yet.
	 *
	 * @since   3.4.0
	 */
	public function testIsPaidPlanFailsClosedWhenNoCache()
	{
		$this->assertSame(false, $this->resource->is_paid_plan());
	}

	/**
	 * Test that is_paid_plan() returns true when the cached plan_type is not free.
	 *
	 * @since   3.4.0
	 */
	public function testIsPaidPlanTrueForPaidPlan()
	{
		update_option(
			$this->resource->settings_name,
			[
				'account' => [
					'plan_type' => 'creator_pro',
				],
			]
		);

		$this->assertSame('creator_pro', $this->resource->get_plan_type());
		$this->assertSame(true, $this->resource->is_paid_plan());
	}

	/**
	 * Test that is_paid_plan() returns false when the cached plan_type is free.
	 *
	 * @since   3.4.0
	 */
	public function testIsPaidPlanFalseForFreePlan()
	{
		update_option(
			$this->resource->settings_name,
			[
				'account' => [
					'plan_type' => 'free',
				],
			]
		);

		$this->assertSame('free', $this->resource->get_plan_type());
		$this->assertSame(false, $this->resource->is_paid_plan());
	}

	/**
	 * Test that is_paid_plan() treats an unrecognised plan_type as paid, so
	 * newly-introduced paid Kit plans don't lock creators out until we ship
	 * an update.
	 *
	 * @since   3.4.0
	 */
	public function testIsPaidPlanTrueForUnknownPlan()
	{
		update_option(
			$this->resource->settings_name,
			[
				'account' => [
					'plan_type' => 'some_new_paid_plan',
				],
			]
		);

		$this->assertSame(true, $this->resource->is_paid_plan());
	}
}
