<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests the Member Content subscriber verification request, when the token and subscriber code
 * are in the request e.g. clicking the link in the email.
 *
 * @since   3.5.5
 */
class RestrictContentSubscriberVerificationTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the number of times the test shortcode ran.
	 *
	 * @since   3.5.5
	 *
	 * @var     int
	 */
	private $shortcodeCalls = 0;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.5.5
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

		// Store the Product that Posts are restricted to, so it exists without querying the API.
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

		// Mock Kit API requests, returning an error as if the token and subscriber code are invalid.
		add_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10, 3);

		// Register a shortcode, to check whether it runs.
		$this->shortcodeCalls = 0;
		add_shortcode(
			'kit_test_shortcode',
			function () {
				++$this->shortcodeCalls;
				return '';
			}
		);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.5.5
	 */
	public function tearDown(): void
	{
		remove_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10);
		remove_shortcode('kit_test_shortcode');
		unset($_REQUEST['token'], $_REQUEST['subscriber_code']);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that a shortcode in the request's token isn't run when the subscriber code form
	 * is displayed in the restricted Post's content.
	 *
	 * @since   3.5.5
	 */
	public function testShortcodeInTokenNotRunInContent()
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

		// Load the Post with a shortcode in the token.
		$this->go_to(get_permalink($post_id));
		the_post();
		$_REQUEST['token']           = '[kit_test_shortcode]';
		$_REQUEST['subscriber_code'] = '1';

		// Run subscriber verification, which fails.
		$output_restrict_content = WP_ConvertKit()->get_class('output_restrict_content');
		$output_restrict_content->initialize_classes();
		$output_restrict_content->maybe_run_subscriber_verification();

		// Restrict the content, then run shortcodes, as WordPress does on the_content.
		$output = do_shortcode($output_restrict_content->maybe_restrict_content($content));

		// Confirm the subscriber code form displays, the token has no square brackets and the shortcode didn't run.
		$this->assertStringContainsString('name="token" value="kit_test_shortcode"', $output);
		$this->assertEquals(0, $this->shortcodeCalls);
	}

	/**
	 * Test that a shortcode in the token is removed from the subscriber code form returned
	 * by the REST API subscriber verification route.
	 *
	 * @since   3.5.5
	 */
	public function testShortcodeInTokenRemovedInRESTAPI()
	{
		$request = new \WP_REST_Request('POST', '/kit/v1/restrict-content/subscriber-verification');
		$request->set_body_params(
			[
				'token'           => '[kit_test_shortcode]',
				'subscriber_code' => '123456',
			]
		);
		$data = rest_do_request($request)->get_data();

		// Confirm the subscriber code form displays, and the token has no square brackets.
		$this->assertFalse($data['success']);
		$this->assertStringContainsString('name="token" value="kit_test_shortcode"', $data['data']);
	}

	/**
	 * Mocks Kit API requests, returning an error as if the token and subscriber code are invalid.
	 *
	 * @since   3.5.5
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

		return [
			'headers'  => [],
			'body'     => wp_json_encode(
				[
					'errors' => [ 'The entered code is invalid.' ],
				]
			),
			'response' => [
				'code'    => 422,
				'message' => '',
			],
			'cookies'  => [],
		];
	}
}
