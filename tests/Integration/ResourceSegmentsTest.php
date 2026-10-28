<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Resource_Segments class.
 *
 * @since   3.4.8
 */
class ResourceSegmentsTest extends WPTestCase
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
	 * @since   3.4.8
	 *
	 * @var     ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Holds the ConvertKit Resource class.
	 *
	 * @since   3.4.8
	 *
	 * @var     ConvertKit_Resource_Segments
	 */
	private $resource;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.8
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Store Credentials in Plugin's settings.
		$this->settings = new \ConvertKit_Settings();
		update_option(
			$this->settings::SETTINGS_NAME,
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		// Initialize the resource class we want to test.
		$this->resource = new \ConvertKit_Resource_Segments();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->resource->resources);

		// Initialize the resource class, fetching resources from the API and caching them in the options table.
		$result = $this->resource->init();

		// Confirm calling init() didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.8
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
	 * Test that the refresh() function returns Segments keyed by ID, and stores them in the options table.
	 *
	 * @since   3.4.8
	 */
	public function testRefresh()
	{
		// Confirm that the data is returned and includes some expected keys.
		$result = $this->resource->refresh();
		$this->assertIsArray($result);
		$this->assertArrayHasKey($_ENV['CONVERTKIT_API_SEGMENT_ID'], $result);
		$this->assertArrayHasKey('id', reset($result));
		$this->assertArrayHasKey('name', reset($result));

		// Confirm that the data is stored in the options table.
		$this->assertArrayHasKey($_ENV['CONVERTKIT_API_SEGMENT_ID'], get_option($this->resource->settings_name));
	}

	/**
	 * Test that the get_by_id() function returns the expected Segment.
	 *
	 * @since   3.4.8
	 */
	public function testGetByID()
	{
		$result = $this->resource->get_by_id( (int) $_ENV['CONVERTKIT_API_SEGMENT_ID'] );
		$this->assertIsArray($result);
		$this->assertEquals($_ENV['CONVERTKIT_API_SEGMENT_NAME'], $result['name']);
	}

	/**
	 * Test that the exist() function returns true when Segments exist.
	 *
	 * @since   3.4.8
	 */
	public function testExist()
	{
		$this->assertTrue($this->resource->exist());
	}
}
