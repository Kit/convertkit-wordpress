<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Account class.
 *
 * @since   3.4.0
 */
class AccountTest extends WPTestCase
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
	 * Holds the ConvertKit Account class we're testing.
	 *
	 * @since   3.4.0
	 *
	 * @var     \ConvertKit_Account
	 */
	private $account;

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

		$this->account = new \ConvertKit_Account();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		delete_option($this->settings::SETTINGS_NAME);
		delete_option(\ConvertKit_Account::OPTION_NAME);

		unset($this->account);

		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that get() returns false when no cache has been written yet.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsFalseWhenNoCache()
	{
		$this->assertSame(false, $this->account->get());
	}

	/**
	 * Test that get_plan_type() returns null when no cache has been written yet.
	 *
	 * @since   3.4.0
	 */
	public function testGetPlanTypeReturnsFalseWhenNoCache()
	{
		$this->assertSame(false, $this->account->get_plan_type());
	}

	/**
	 * Test that is_paid_plan() fails closed when no cache has been written yet.
	 *
	 * @since   3.4.0
	 */
	public function testIsPaidPlanFailsClosedWhenNoCache()
	{
		$this->assertSame(false, $this->account->is_paid_plan());
	}

	/**
	 * Test that refresh() fetches the account from Kit and caches it.
	 *
	 * @since   3.4.0
	 */
	public function testRefreshPopulatesCache()
	{
		$result = $this->account->refresh();
		$this->assertNotInstanceOf(\WP_Error::class, $result);
		$this->assertIsArray($result);
		$this->assertArrayHasKey('account', $result);
		$this->assertArrayHasKey('plan_type', $result['account']);

		// Confirm the value was persisted.
		$cached = $this->account->get();
		$this->assertIsArray($cached);
		$this->assertSame($result['account']['plan_type'], $cached['account']['plan_type']);
	}

	/**
	 * Test that is_paid_plan() returns true when the cached plan_type is not free.
	 *
	 * @since   3.4.0
	 */
	public function testIsPaidPlanTrueForPaidPlan()
	{
		update_option(
			\ConvertKit_Account::OPTION_NAME,
			[
				'account' => [
					'plan_type' => 'creator_pro',
				],
			]
		);

		$this->assertSame('creator_pro', $this->account->get_plan_type());
		$this->assertSame(true, $this->account->is_paid_plan());
	}

	/**
	 * Test that is_paid_plan() returns false when the cached plan_type is free.
	 *
	 * @since   3.4.0
	 */
	public function testIsPaidPlanFalseForFreePlan()
	{
		update_option(
			\ConvertKit_Account::OPTION_NAME,
			[
				'account' => [
					'plan_type' => 'free',
				],
			]
		);

		$this->assertSame('free', $this->account->get_plan_type());
		$this->assertSame(false, $this->account->is_paid_plan());
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
			\ConvertKit_Account::OPTION_NAME,
			[
				'account' => [
					'plan_type' => 'some_new_paid_plan',
				],
			]
		);

		$this->assertSame(true, $this->account->is_paid_plan());
	}

	/**
	 * Test that delete() clears the cache.
	 *
	 * @since   3.4.0
	 */
	public function testDeleteClearsCache()
	{
		update_option(
			\ConvertKit_Account::OPTION_NAME,
			[
				'account' => [
					'plan_type' => 'creator',
				],
			]
		);

		$this->assertSame(true, $this->account->is_paid_plan());

		$this->account->delete();

		$this->assertSame(false, $this->account->get());
		$this->assertSame(false, $this->account->is_paid_plan());
	}

	/**
	 * Test that refresh() returns a WP_Error when no credentials are set.
	 *
	 * @since   3.4.0
	 */
	public function testRefreshReturnsErrorWithoutCredentials()
	{
		delete_option($this->settings::SETTINGS_NAME);

		$result = $this->account->refresh();
		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test that a failed refresh() does not clobber an existing cache.
	 *
	 * @since   3.4.0
	 */
	public function testRefreshFailureLeavesCacheIntact()
	{
		update_option(
			\ConvertKit_Account::OPTION_NAME,
			[
				'account' => [
					'plan_type' => 'creator_pro',
				],
			]
		);

		// Wipe credentials so refresh fails.
		delete_option($this->settings::SETTINGS_NAME);

		$result = $this->account->refresh();
		$this->assertInstanceOf(\WP_Error::class, $result);

		// Cache should still be intact.
		$cached = $this->account->get();
		$this->assertIsArray($cached);
		$this->assertSame('creator_pro', $cached['account']['plan_type']);
	}
}
