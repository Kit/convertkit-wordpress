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
	 * Test that permitted crawlers can access restricted content when the Permit Crawlers
	 * setting is enabled.
	 *
	 * @since   3.4.5
	 */
	public function testPermittedCrawlersCanAccessRestrictedContent()
	{
		// Store Credentials in Plugin's settings.
		$settings = new \ConvertKit_Settings();
		$settings->save(
			array(
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
				'token_expires' => ( time() + 10000 ),
			)
		);

		// Enable the Permit Crawlers setting.
		$restrict_content_settings = new \ConvertKit_Settings_Restrict_Content();
		$restrict_content_settings->save(
			array(
				'permit_crawlers' => 'on',
			)
		);

		// Create a Post, restricting to a Kit Product.
		$post_id = static::factory()->post->create(
			[
				'post_title' => 'Restrict Content: Crawlers',
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

		// Load the Post.
		$this->go_to(get_permalink($post_id));
		the_post();

		// Initialize the class' settings.
		$this->resource->initialize_classes();

		// Define crawler user agents and an IP address within each crawler's permitted IP addresses.
		$crawlers = [
			'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' => '34.100.182.96',
			'DuckDuckBot/1.1; (+http://duckduckgo.com/duckduckbot.html)' => '57.152.72.128',
		];

		foreach ($crawlers as $user_agent => $ip_address) {
			$_SERVER['HTTP_USER_AGENT'] = $user_agent;
			$_SERVER['REMOTE_ADDR']     = $ip_address;

			// Confirm the content is not restricted.
			$this->assertEquals('Member-only content.', $this->resource->maybe_restrict_content('Member-only content.'), $user_agent);
		}
	}

	/**
	 * Test that restricted content is not displayed when the Plugin has no credentials
	 * e.g. the access token was revoked and the credentials deleted.
	 *
	 * @since   3.5.0
	 */
	public function testRestrictedContentNotDisplayedWhenNoCredentials()
	{
		// Confirm the content is restricted.
		$this->assertContentRestricted($this->createRestrictedPostAndLoad());
	}

	/**
	 * Test that restricted content is not displayed when the Plugin's resources couldn't be
	 * fetched from Kit e.g. an API error.
	 *
	 * @since   3.5.0
	 */
	public function testRestrictedContentNotDisplayedWhenResourcesUnavailable()
	{
		// Store Credentials in Plugin's settings.
		$this->storeCredentials();

		// Remove the cached Products, as if they couldn't be fetched from Kit.
		delete_option('convertkit_products');
		update_option('convertkit_products_last_queried', time());

		// Confirm the content is restricted.
		$this->assertContentRestricted($this->createRestrictedPostAndLoad());
	}

	/**
	 * Test that restricted content is displayed when the Product it's restricted to
	 * no longer exists in Kit.
	 *
	 * @since   3.5.0
	 */
	public function testRestrictedContentDisplayedWhenResourceDeleted()
	{
		// Store Credentials in Plugin's settings.
		$this->storeCredentials();

		// Store no Products, as if the Product was deleted in Kit.
		update_option('convertkit_products', []);
		update_option('convertkit_products_last_queried', time());

		// Confirm the content is not restricted.
		$content = $this->createRestrictedPostAndLoad();
		$this->assertEquals($content, $this->resource->maybe_restrict_content($content));
	}

	/**
	 * Test that restricted content is not displayed when output in an AJAX request
	 * e.g. a Theme's "load more" button.
	 *
	 * @since   3.5.0
	 */
	public function testRestrictedContentNotDisplayedInAJAXRequest()
	{
		// Store Credentials and the Product in Plugin's settings.
		$this->storeCredentials();
		$this->storeProduct();

		// Create a restricted Post with no teaser or excerpt, longer than the default excerpt length.
		$post_id = static::factory()->post->create(
			[
				'post_content' => '<p>' . str_repeat('Preview ', 55) . 'Member-only content.</p>',
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
				],
			]
		);

		// Register the content filter as if this were an AJAX request.
		add_filter('wp_doing_ajax', '__return_true');
		$this->resource->initialize_classes();
		$this->resource->register_ajax_content_filter();

		// Output the Post's content, as a Theme would in an AJAX request.
		$query = new \WP_Query([ 'p' => $post_id ]);
		$query->the_post();
		$output = apply_filters('the_content', get_the_content());
		wp_reset_postdata();

		// Confirm the content is restricted.
		$this->assertStringContainsString('Preview', $output);
		$this->assertStringNotContainsString('Member-only content.', $output);

		// Remove the content filter and AJAX override, so other tests aren't affected.
		remove_filter('the_content', [ $this->resource, 'maybe_restrict_content' ]);
		remove_filter('wp_doing_ajax', '__return_true');
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

	/**
	 * Stores credentials in the Plugin's settings.
	 *
	 * @since   3.5.0
	 */
	private function storeCredentials()
	{
		$settings = new \ConvertKit_Settings();
		$settings->save(
			array(
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
				'token_expires' => ( time() + 10000 ),
			)
		);
	}

	/**
	 * Stores the Product that Posts are restricted to, so it exists without querying the API.
	 *
	 * @since   3.5.0
	 */
	private function storeProduct()
	{
		update_option(
			'convertkit_products',
			[
				$_ENV['CONVERTKIT_API_PRODUCT_ID'] => [
					'id'        => (int) $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					'name'      => 'Product',
					'url'       => 'https://example.com',
					'published' => true,
				],
			]
		);
		update_option('convertkit_products_last_queried', time());
	}

	/**
	 * Creates a Post restricted to a Kit Product, loads it and returns its content.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	private function createRestrictedPostAndLoad()
	{
		$content = '<p>Teaser content.</p><!--more--><p>Member-only content.</p>';
		$post_id = static::factory()->post->create(
			[
				'post_content' => $content,
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
				],
			]
		);

		// Load the Post.
		$this->go_to(get_permalink($post_id));
		the_post();

		// Initialize the class' settings.
		$this->resource->initialize_classes();

		return $content;
	}

	/**
	 * Asserts that the given content is restricted, displaying the teaser and not the member-only content.
	 *
	 * @since   3.5.0
	 *
	 * @param   string $content    Post content.
	 */
	private function assertContentRestricted($content)
	{
		$output = $this->resource->maybe_restrict_content($content);
		$this->assertStringContainsString('Teaser content.', $output);
		$this->assertStringNotContainsString('Member-only content.', $output);
	}
}
