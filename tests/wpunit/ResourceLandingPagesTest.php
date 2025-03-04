<?php
/**
 * Tests for the ConvertKit_Resource_Landing_Pages class.
 *
 * @since   1.9.7.4
 */
class ResourceLandingPagesTest extends \Codeception\TestCase\WPTestCase
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
	 * @since   1.9.7.4
	 *
	 * @var     ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Holds the ConvertKit Resource class.
	 *
	 * @since   1.9.7.4
	 *
	 * @var     ConvertKit_Resource_Landing_Pages
	 */
	private $resource;

	/**
	 * Performs actions before each test.
	 *
	 * @since   1.9.7.4
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Store Credentials in Plugin's settings.
		$this->settings = new ConvertKit_Settings();
		update_option(
			$this->settings::SETTINGS_NAME,
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		// Initialize the resource class we want to test.
		$this->resource = new ConvertKit_Resource_Landing_Pages();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(WP_Error::class, $this->resource->resources);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   1.9.6.9
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
	 * @since   1.9.7.4
	 */
	public function testRefresh()
	{
		// Confirm that the data is stored in the options table and includes some expected keys.
		$result = $this->resource->refresh();
		$this->assertIsArray($result);
		$this->assertArrayHasKey('id', reset($result));
		$this->assertArrayHasKey('name', reset($result));
	}

	/**
	 * Test that the expiry timestamp is set and returns the expected value.
	 *
	 * @since   1.9.7.4
	 */
	public function testExpiry()
	{
		// Define the expected expiry date based on the resource class' $cache_duration setting.
		$expectedExpiryDate = date('Y-m-d', time() + $this->resource->cache_duration);

		// Fetch the actual expiry date set when the resource class was initialized.
		$expiryDate = date('Y-m-d', $this->resource->last_queried + $this->resource->cache_duration);

		// Confirm both dates match.
		$this->assertEquals($expectedExpiryDate, $expiryDate);
	}

	/**
	 * Tests that the get() function returns resources in alphabetical ascending order
	 * by default.
	 *
	 * @since   1.9.7.4
	 */
	public function testGet()
	{
		// Call resource class' get() function.
		$result = $this->resource->get();

		// Assert result is an array.
		$this->assertIsArray($result);

		// Assert top level array keys are preserved.
		$this->assertArrayHasKey(array_key_first($this->resource->resources), $result);
		$this->assertArrayHasKey(array_key_last($this->resource->resources), $result);

		// Assert resource within results has expected array keys.
		$this->assertArrayHasKey('id', reset($result));
		$this->assertArrayHasKey('name', reset($result));

		// Assert order of data is in ascending alphabetical order.
		$this->assertEquals('Character Encoding', reset($result)[ $this->resource->order_by ]);
		$this->assertEquals('Legacy Landing Page', end($result)[ $this->resource->order_by ]);
	}

	/**
	 * Tests that the get() function returns resources in alphabetical descending order
	 * when a valid order_by and order properties are defined.
	 *
	 * @since   2.0.8
	 */
	public function testGetWithValidOrderByAndOrder()
	{
		// Define order_by and order.
		$this->resource->order_by = 'name';
		$this->resource->order    = 'desc';

		// Call resource class' get() function.
		$result = $this->resource->get();

		// Assert result is an array.
		$this->assertIsArray($result);

		// Assert top level array keys are preserved.
		$this->assertArrayHasKey(array_key_first($this->resource->resources), $result);
		$this->assertArrayHasKey(array_key_last($this->resource->resources), $result);

		// Assert resource within results has expected array keys.
		$this->assertArrayHasKey('id', reset($result));
		$this->assertArrayHasKey('name', reset($result));

		// Assert order of data is in ascending alphabetical order.
		$this->assertEquals('Legacy Landing Page', reset($result)[ $this->resource->order_by ]);
		$this->assertEquals('Character Encoding', end($result)[ $this->resource->order_by ]);
	}

	/**
	 * Tests that the get() function returns resources in their original order
	 * when populated with Forms and an invalid order_by value is specified.
	 *
	 * @since   2.0.8
	 */
	public function testGetWithInvalidOrderBy()
	{
		// Define order_by with an invalid value (i.e. an array key that does not exist).
		$this->resource->order_by = 'invalid_key';

		// Call resource class' get() function.
		$result = $this->resource->get();

		// Assert result is an array.
		$this->assertIsArray($result);

		// Assert top level array keys are preserved.
		$this->assertArrayHasKey(array_key_first($this->resource->resources), $result);
		$this->assertArrayHasKey(array_key_last($this->resource->resources), $result);

		// Assert resource within results has expected array keys.
		$this->assertArrayHasKey('id', reset($result));
		$this->assertArrayHasKey('name', reset($result));

		// Assert order of data has not changed.
		$this->assertEquals('Character Encoding', reset($result)['name']);
		$this->assertEquals('Legacy Landing Page', end($result)['name']);
	}

	/**
	 * Test that the count() function returns the number of resources.
	 *
	 * @since   1.9.7.6
	 */
	public function testCount()
	{
		$result = $this->resource->get();
		$this->assertEquals($this->resource->count(), count($result));
	}

	/**
	 * Test that the exist() function performs as expected.
	 *
	 * @since   1.9.7.4
	 */
	public function testExist()
	{
		// Confirm that the function returns true, because resources exist.
		$result = $this->resource->exist();
		$this->assertSame($result, true);
	}

	/**
	 * Test that the get_html() function returns the expected data.
	 *
	 * @since   2.0.4
	 */
	public function testGetHTML()
	{
		$result = $this->resource->get_html($_ENV['CONVERTKIT_API_LANDING_PAGE_ID']);
		$this->assertNotInstanceOf(WP_Error::class, $result);
		$this->assertStringContainsString('<form method="POST" action="https://app.kit.com/forms/' . $_ENV['CONVERTKIT_API_LANDING_PAGE_ID'] . '/subscriptions" data-sv-form="' . $_ENV['CONVERTKIT_API_LANDING_PAGE_ID'] . '" data-uid="99f1db6843" class="formkit-form"', $result);
	}

	/**
	 * Test that the get_html() function returns the expected data for a Legacy Landing Page ID.
	 *
	 * @since   2.0.4
	 */
	public function testGetHTMLWithLegacyLandingPageID()
	{
		$result = $this->resource->get_html($_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID']);
		$this->assertNotInstanceOf(WP_Error::class, $result);
		$this->assertStringContainsString('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://app.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID'] . '/subscribe" data-remote="true">', $result);
	}
}
