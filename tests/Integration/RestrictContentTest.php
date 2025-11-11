<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for Restrict Content functionality.
 *
 * @since   2.4.2
 */
class RestrictContentTest extends WPTestCase
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
	 * @since   2.4.2
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Initialize the class we want to test.
		$this->resource = new \ConvertKit_Output_Restrict_Content();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->resource);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   2.4.2
	 */
	public function tearDown(): void
	{
		// Destroy the class we tested.
		unset($this->resource);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the Restrict Content enabled option is set to true when a published WordPress
	 * Post has the Restrict Content setting defined on the Post's creation.
	 *
	 * @since   3.0.4
	 */
	public function testRestrictContentEnabledOptionTrueWhenPublishedPostIsRestrictedOnCreation()
	{
		// Initialise class.
		$class = new \ConvertKit_Admin_Restrict_Content();

		// Delete any existing Restrict Content enabled option from the options table.
		delete_option($class->restrict_content_enabled_key);

		// Create a Post, restricting to a Kit Product.
		$post_id = wp_insert_post(
			[
				'post_type'   => 'page',
				'post_title'  => 'Restrict Content',
				'post_status' => 'publish',
				'meta_input'  => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
				],
			]
		);

		// Check that the Restrict Content enabled option is set to true.
		$this->assertTrue($class->restrict_content_enabled());

		// Trash the Post.
		wp_trash_post($post_id);

		// Check that the Restrict Content enabled option is set to false.
		$this->assertFalse($class->restrict_content_enabled());
	}

	/**
	 * Test that the Restrict Content enabled option is set to true when a published WordPress
	 * Post has the Restrict Content setting defined on the Post's update.
	 *
	 * @since   3.0.4
	 */
	public function testRestrictContentEnabledOptionTrueWhenPublishedPostIsRestrictedOnUpdate()
	{
		// Initialise class.
		$class = new \ConvertKit_Admin_Restrict_Content();

		// Delete any existing Restrict Content enabled option from the options table.
		delete_option($class->restrict_content_enabled_key);

		// Create a Post.
		$post_id = wp_insert_post(
			[
				'post_type'   => 'page',
				'post_title'  => 'Restrict Content',
				'post_status' => 'publish',
			]
		);

		// Check that the Restrict Content enabled option is set to false.
		$this->assertFalse($class->restrict_content_enabled());

		// Update the Post, restricting to a Kit Product.
		wp_update_post(
			[
				'ID'         => $post_id,
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
				],
			]
		);

		// Check that the Restrict Content enabled option is set to true.
		$this->assertTrue($class->restrict_content_enabled());

		// Delete the Post.
		wp_delete_post($post_id, true);

		// Check that the Restrict Content enabled option is set to false.
		$this->assertFalse($class->restrict_content_enabled());
	}

	/**
	 * Test that the Restrict Content enabled option is set to true when a published WordPress
	 * Post has the Restrict Content setting removed on the Post's update.
	 *
	 * @since   3.0.4
	 */
	public function testRestrictContentEnabledOptionFalseWhenPublishedPostIsUnrestrictedOnUpdate()
	{
		// Initialise class.
		$class = new \ConvertKit_Admin_Restrict_Content();

		// Delete any existing Restrict Content enabled option from the options table.
		delete_option($class->restrict_content_enabled_key);

		// Create a Post, restricting to a Kit Product.
		$post_id = wp_insert_post(
			[
				'post_type'   => 'page',
				'post_title'  => 'Restrict Content',
				'post_status' => 'publish',
				'meta_input'  => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
				],
			]
		);

		// Check that the Restrict Content enabled option is set to true.
		$this->assertTrue($class->restrict_content_enabled());

		// Update the Post, removing the Restrict Content setting.
		wp_update_post(
			[
				'ID'         => $post_id,
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => '',
					],
				],
			]
		);

		// Check that the Restrict Content enabled option is set to false.
		$this->assertFalse($class->restrict_content_enabled());

		// Trash the Post.
		wp_trash_post($post_id);

		// Check that the Restrict Content enabled option is set to false.
		$this->assertFalse($class->restrict_content_enabled());
	}

	/**
	 * Test that IP addresses 34.100.182.96 through .111 (i.e. in the CIDR range /28)
	 * are returned as true by the ip_in_range() function.
	 *
	 * @since   2.4.2
	 */
	public function testIPAddressInRange()
	{
		for ($i = 96; $i <= 111; $i++) {
			$this->assertTrue($this->resource->ip_in_range('34.100.182.' . $i, '34.100.182.96/28'));
		}
	}

	/**
	 * Test that IP address 34.100.182.112 in the range 34.100.182.96/28
	 * are returned as false by the ip_in_range() function.
	 *
	 * @since   2.4.2
	 */
	public function testIPAddressOutsideRange()
	{
		$this->assertFalse($this->resource->ip_in_range('34.100.182.112', '34.100.182.96/28'));
	}

	/**
	 * Test that invalid IP addresses are returned as false by the ip_in_range() function.
	 *
	 * @since   2.4.2
	 */
	public function testInvalidIPAddresses()
	{
		$this->assertFalse($this->resource->ip_in_range('0.0.0.0', '34.100.182.96/28'));
		$this->assertFalse($this->resource->ip_in_range('999.999.999.999', '34.100.182.96/28'));
		$this->assertFalse($this->resource->ip_in_range('not-an-ip-address', '34.100.182.96/28'));
	}

	/**
	 * Test that invalid ranges return false by the ip_in_range() function.
	 *
	 * @since   2.4.2
	 */
	public function testInvalidRanges()
	{
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '34.100.182.96'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '34.100.182.96/999'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '34.100.182.96/not-a-range'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '0.0.0.0'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', 'not-an-ip-address'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '999.999.999.999/999'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '999.999.999.999/not-a-range'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', 'not-an-ip-address/not-a-range'));
	}
}
