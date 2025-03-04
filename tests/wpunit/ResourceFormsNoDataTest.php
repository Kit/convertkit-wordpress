<?php
/**
 * Tests for the ConvertKit_Resource_Forms class when no data is present in the API.
 *
 * @since   1.9.7.8
 */
class ResourceFormsNoDataTest extends \Codeception\TestCase\WPTestCase
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
	 * @since   1.9.7.8
	 *
	 * @var     ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Holds the ConvertKit Resource class.
	 *
	 * @since   1.9.7.8
	 *
	 * @var     ConvertKit_Resource_Forms
	 */
	private $resource;

	/**
	 * Performs actions before each test.
	 *
	 * @since   1.9.7.8
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Store credentials in Plugin's settings.
		$this->settings = new ConvertKit_Settings();
		update_option(
			$this->settings::SETTINGS_NAME,
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN_NO_DATA'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN_NO_DATA'],
			]
		);

		// Initialize the resource class we want to test.
		$this->resource = new ConvertKit_Resource_Forms();

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
	 * Test that the refresh() function performs as expected, storing data in the options table.
	 *
	 * @since   1.9.7.8
	 */
	public function testRefresh()
	{
		// Confirm that the data is stored in the options table and includes some expected keys.
		$result = $this->resource->refresh();
		$this->assertNotInstanceOf(WP_Error::class, $result);
		$this->assertIsArray($result);
		$this->assertCount(0, $result);
	}

	/**
	 * Test that the expiry timestamp is set and returns the expected value.
	 *
	 * @since   1.9.7.8
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
	 * Test that the get() function performs as expected.
	 *
	 * @since   1.9.7.8
	 */
	public function testGet()
	{
		// Confirm that the data is fetched from the options table when using get(), and includes some expected keys.
		$result = $this->resource->get();
		$this->assertNotInstanceOf(WP_Error::class, $result);
		$this->assertIsArray($result);
		$this->assertCount(0, $result);
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
	 * @since   1.9.7.8
	 */
	public function testExist()
	{
		// Confirm that the function returns true, because resources exist.
		$result = $this->resource->exist();
		$this->assertSame($result, false);
	}

	/**
	 * Test that the non_inline_exist() function performs as expected.
	 *
	 * @since   2.2.4
	 */
	public function testNonInlineExist()
	{
		// Confirm that the function returns true, because resources exist.
		$result = $this->resource->non_inline_exist();
		$this->assertSame($result, false);
	}
}
