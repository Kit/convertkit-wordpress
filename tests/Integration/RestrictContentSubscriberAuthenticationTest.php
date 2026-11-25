<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests the Member Content subscriber authentication request, when JavaScript is disabled.
 *
 * @since   3.5.3
 */
class RestrictContentSubscriberAuthenticationTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the Kit API requests made.
	 *
	 * @since   3.5.3
	 *
	 * @var     array
	 */
	private $requests = [];

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

		// Store Credentials in Plugin's settings.
		$settings = new \ConvertKit_Settings();
		$settings->save(
			array(
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
				'token_expires' => ( time() + 10000 ),
			)
		);

		// Mock Kit API requests, storing each request made.
		add_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10, 3);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.5.3
	 */
	public function tearDown(): void
	{
		remove_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10);
		unset($_REQUEST['_wpnonce'], $_REQUEST['convertkit_email'], $_REQUEST['convertkit_post_id'], $_REQUEST['convertkit_resource_type'], $_REQUEST['convertkit_resource_id']);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the subscriber is tagged with the Post's Tag, not a Tag specified in the request.
	 *
	 * @since   3.5.3
	 */
	public function testSubscriberTaggedWithPostTag()
	{
		$this->authenticate('tag_' . $_ENV['CONVERTKIT_API_TAG_ID'], 'tag', 999);

		// Confirm the subscriber was tagged with the Post's Tag, and the code sent.
		$urls = array_column($this->requests, 'url');
		$this->assertCount(1, preg_grep('/tags\/' . $_ENV['CONVERTKIT_API_TAG_ID'] . '\/subscribers/', $urls));
		$this->assertCount(0, preg_grep('/tags\/999/', $urls));
		$this->assertStringContainsString('subscriber_authentication/send_code', end($urls));
	}

	/**
	 * Test that the subscriber isn't tagged when the Post is restricted by Product,
	 * even if the request specifies a Tag.
	 *
	 * @since   3.5.3
	 */
	public function testSubscriberNotTaggedWhenPostRestrictedByProduct()
	{
		$this->authenticate('product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'], 'tag', 999);

		// Confirm the subscriber wasn't tagged, and the code was sent.
		$urls = array_column($this->requests, 'url');
		$this->assertCount(0, preg_grep('/tags\//', $urls));
		$this->assertStringContainsString('subscriber_authentication/send_code', end($urls));
	}

	/**
	 * Test that the subscriber isn't tagged when the Post isn't restricted e.g. the Member Content
	 * Login block, even if the request specifies a Tag.
	 *
	 * @since   3.5.3
	 */
	public function testSubscriberNotTaggedWhenPostNotRestricted()
	{
		$this->authenticate('', 'tag', 999);

		// Confirm the subscriber wasn't tagged, and the code was sent.
		$urls = array_column($this->requests, 'url');
		$this->assertCount(0, preg_grep('/tags\//', $urls));
		$this->assertStringContainsString('subscriber_authentication/send_code', end($urls));
	}

	/**
	 * Submits the subscriber authentication request for a Post with the given Member Content setting,
	 * and the given resource type and ID in the request.
	 *
	 * @since   3.5.3
	 *
	 * @param   string $restrict_content   Post's Member Content setting.
	 * @param   string $resource_type      Resource type in the request.
	 * @param   int    $resource_id        Resource ID in the request.
	 */
	private function authenticate($restrict_content, $resource_type, $resource_id)
	{
		$post_id = static::factory()->post->create(
			[
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => $restrict_content,
					],
				],
			]
		);

		$_REQUEST['_wpnonce']                 = wp_create_nonce('convertkit_restrict_content_login');
		$_REQUEST['convertkit_email']         = 'kit@example.com';
		$_REQUEST['convertkit_post_id']       = (string) $post_id;
		$_REQUEST['convertkit_resource_type'] = $resource_type;
		$_REQUEST['convertkit_resource_id']   = (string) $resource_id;

		$output_restrict_content = new \ConvertKit_Output_Restrict_Content();
		$output_restrict_content->initialize_classes();
		$output_restrict_content->maybe_run_subscriber_authentication();
	}

	/**
	 * Mocks Kit API requests, storing each request made and returning a response.
	 *
	 * @since   3.5.3
	 *
	 * @param   false|array $response   Response.
	 * @param   array       $args       Request arguments.
	 * @param   string      $url        Request URL.
	 * @return  false|array
	 */
	public function mockKitAPIRequest($response, $args, $url)
	{
		// Don't mock requests to other services.
		if ( strpos($url, 'https://api.kit.com/') !== 0 ) {
			return $response;
		}

		// Store request.
		$this->requests[] = [
			'url'  => $url,
			'body' => json_decode($args['body'], true),
		];

		return [
			'headers'  => [],
			'body'     => wp_json_encode(
				[
					'subscriber' => [
						'id' => 123456,
					],
					'token'      => 'token',
				]
			),
			'response' => [
				'code'    => 201,
				'message' => '',
			],
			'cookies'  => [],
		];
	}
}
