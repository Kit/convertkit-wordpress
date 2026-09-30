<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

/**
 * Tests for registering the Broadcasts webhook endpoint in Kit, and receiving
 * webhook deliveries, mocking Kit API requests.
 *
 * @since   3.4.6
 */
class BroadcastsWebhookTest extends WPRestApiTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Settings class.
	 *
	 * @since   3.4.6
	 *
	 * @var     ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Holds the Broadcasts Webhook class.
	 *
	 * @since   3.4.6
	 *
	 * @var     ConvertKit_Broadcasts_Webhook
	 */
	private $webhook;

	/**
	 * Holds the Kit API requests made.
	 *
	 * @since   3.4.6
	 *
	 * @var     array
	 */
	private $requests = [];

	/**
	 * Holds the Kit API response code and body to return, keyed by method and endpoint.
	 *
	 * @since   3.4.6
	 *
	 * @var     array
	 */
	private $responses = [];

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.6
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin, to include the Plugin's constants in tests.
		activate_plugins('convertkit/wp-convertkit.php');

		// Store Credentials in Plugin's settings.
		$this->settings = new \ConvertKit_Settings();
		$this->settings->save(
			array(
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
				'token_expires' => ( time() + 10000 ),
			)
		);

		// Tell WordPress that we're making REST API requests.
		// This constant isn't set by the WP_REST_Server class in tests.
		if ( ! defined( 'REST_REQUEST' ) ) {
			define( 'REST_REQUEST', true );
		}

		$this->webhook = \WP_ConvertKit()->get_class('broadcasts_webhook');

		// Define the Kit API responses.
		$this->responses = [
			'POST webhook_endpoints'       => [
				'code' => 201,
				'body' => [
					'webhook_endpoint' => [
						'id'     => 123,
						'url'    => $this->webhook->get_url(),
						'events' => [ 'post.published' ],
						'status' => 'active',
						'secret' => 'whsec_test',
					],
				],
			],
			'GET webhook_endpoints/123'    => [
				'code' => 200,
				'body' => [
					'webhook_endpoint' => [
						'id'     => 123,
						'url'    => $this->webhook->get_url(),
						'events' => [ 'post.published' ],
						'status' => 'active',
					],
				],
			],
			'DELETE webhook_endpoints/123' => [
				'code' => 204,
				'body' => null,
			],
			'GET wordpress/posts'          => [
				'code' => 200,
				'body' => [
					'posts'       => [],
					'page'        => 1,
					'total_pages' => 1,
				],
			],
		];

		// Mock Kit API requests, storing each request made.
		add_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10, 3);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.6
	 */
	public function tearDown(): void
	{
		remove_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10);

		// Delete Credentials and Broadcasts settings.
		$this->settings->delete_credentials();
		delete_option(\ConvertKit_Settings_Broadcasts::SETTINGS_NAME);
		delete_option(\ConvertKit_Broadcasts_Webhook::EVENTS_OPTION_NAME);
		delete_transient(\ConvertKit_Broadcasts_Webhook::ERROR_TRANSIENT_NAME);
		delete_transient(\ConvertKit_Broadcasts_Webhook::STATUS_TRANSIENT_NAME);

		parent::tearDown();
	}

	/**
	 * Test that the webhook endpoint is registered in Kit when the Broadcasts import is enabled.
	 *
	 * @since   3.4.6
	 */
	public function testRegisteredWhenImportEnabled()
	{
		$this->enableImport();

		// Confirm the webhook endpoint was created for the post.published event.
		$this->assertCount(1, $this->requests);
		$this->assertEquals('POST', $this->requests[0]['method']);
		$this->assertStringEndsWith('webhook_endpoints', $this->requests[0]['url']);
		$this->assertEquals($this->webhook->get_url(), $this->requests[0]['body']['url']);
		$this->assertEquals([ 'post.published' ], $this->requests[0]['body']['events']);

		// Confirm the endpoint was stored.
		$this->assertTrue($this->webhook->is_registered());
		$this->assertEquals(
			[
				'id'     => 123,
				'secret' => 'whsec_test',
				'url'    => $this->webhook->get_url(),
			],
			get_option(\ConvertKit_Broadcasts_Webhook::OPTION_NAME)
		);
	}

	/**
	 * Test that the webhook endpoint is deleted from Kit when the Broadcasts import is disabled.
	 *
	 * @since   3.4.6
	 */
	public function testDeletedWhenImportDisabled()
	{
		$this->enableImport();
		$this->requests = [];

		// Disable the import.
		$settings = new \ConvertKit_Settings_Broadcasts();
		$settings->save([ 'enabled' => '' ]);

		// Confirm the webhook endpoint was deleted.
		$this->assertCount(1, $this->requests);
		$this->assertEquals('DELETE', $this->requests[0]['method']);
		$this->assertStringEndsWith('webhook_endpoints/123', $this->requests[0]['url']);
		$this->assertFalse(get_option(\ConvertKit_Broadcasts_Webhook::OPTION_NAME));
	}

	/**
	 * Test that the registration error is stored, and registration isn't retried on every request,
	 * when Kit rejects the webhook endpoint.
	 *
	 * @since   3.4.6
	 */
	public function testRegistrationFailed()
	{
		$this->responses['POST webhook_endpoints'] = [
			'code' => 422,
			'body' => [ 'errors' => [ 'Url must be publicly accessible' ] ],
		];

		$this->enableImport();

		// Confirm the error was stored, and no endpoint was stored.
		$this->assertFalse($this->webhook->is_registered());
		$this->assertEquals('Url must be publicly accessible', $this->webhook->get_error());
		$this->assertFalse($this->webhook->get_status());

		// Confirm registration isn't retried.
		$this->requests = [];
		$this->webhook->maybe_register();
		$this->assertCount(0, $this->requests);
	}

	/**
	 * Test that re-registering the webhook endpoint deletes the existing endpoint from Kit.
	 *
	 * @since   3.4.6
	 */
	public function testRegisterReplacesExistingEndpoint()
	{
		$this->enableImport();
		$this->requests = [];

		$this->assertTrue($this->webhook->register());

		// Confirm the existing endpoint was deleted before creating the new endpoint.
		$this->assertCount(2, $this->requests);
		$this->assertEquals('DELETE', $this->requests[0]['method']);
		$this->assertStringEndsWith('webhook_endpoints/123', $this->requests[0]['url']);
		$this->assertEquals('POST', $this->requests[1]['method']);
	}

	/**
	 * Test that registering the webhook endpoint doesn't delete an existing endpoint registered
	 * for a different URL, such as when the site was cloned to a staging site.
	 *
	 * @since   3.4.6
	 */
	public function testRegisterWhenURLChanged()
	{
		update_option(
			\ConvertKit_Broadcasts_Webhook::OPTION_NAME,
			[
				'id'     => 456,
				'secret' => 'whsec_other',
				'url'    => 'https://example.com/wp-json/kit/v1/broadcasts/webhook',
			]
		);

		$this->enableImport();

		// Confirm the other site's endpoint wasn't deleted, and a new endpoint was registered.
		$this->assertCount(1, $this->requests);
		$this->assertEquals('POST', $this->requests[0]['method']);
		$this->assertEquals(123, get_option(\ConvertKit_Broadcasts_Webhook::OPTION_NAME)['id']);
	}

	/**
	 * Test that get_status() returns the webhook endpoint's status in Kit.
	 *
	 * @since   3.4.6
	 */
	public function testGetStatus()
	{
		$this->enableImport();
		$this->assertEquals('active', $this->webhook->get_status());
	}

	/**
	 * Test that get_status() returns missing when the webhook endpoint was deleted in Kit.
	 *
	 * @since   3.4.6
	 */
	public function testGetStatusWhenDeletedInKit()
	{
		$this->responses['GET webhook_endpoints/123'] = [
			'code' => 404,
			'body' => [ 'errors' => [ 'Not Found' ] ],
		];

		$this->enableImport();
		$this->assertEquals('missing', $this->webhook->get_status());
	}

	/**
	 * Test that the stored webhook endpoint is removed when the Plugin's credentials are deleted.
	 *
	 * @since   3.4.6
	 */
	public function testRemovedWhenCredentialsDeleted()
	{
		$this->enableImport();

		$this->settings->delete_credentials();

		$this->assertFalse(get_option(\ConvertKit_Broadcasts_Webhook::OPTION_NAME));
	}

	/**
	 * Test that a delivery with a post.published event refreshes the Posts resource, which imports Broadcasts.
	 *
	 * @since   3.4.6
	 */
	public function testReceivePostPublished()
	{
		$this->enableImport();

		$this->requests = [];

		$response = $this->deliver($this->payload('post.published'));

		$this->assertSame(200, $response->get_status());
		$this->assertTrue($this->postsRefreshed());
	}

	/**
	 * Test that a delivery without a post.published event doesn't refresh the Posts resource.
	 *
	 * @since   3.4.6
	 */
	public function testReceiveOtherEvent()
	{
		$this->enableImport();
		$this->requests = [];

		$response = $this->deliver($this->payload('subscriber.created'));

		$this->assertSame(200, $response->get_status());
		$this->assertFalse($this->postsRefreshed());
	}

	/**
	 * Test that a duplicate delivery of the same event doesn't refresh the Posts resource again.
	 *
	 * @since   3.4.6
	 */
	public function testReceiveDuplicateEvent()
	{
		$this->enableImport();
		$this->requests = [];

		$payload = $this->payload('post.published');
		$this->deliver($payload);
		$this->requests = [];

		$response = $this->deliver($payload);

		$this->assertSame(200, $response->get_status());
		$this->assertFalse($this->postsRefreshed());
	}

	/**
	 * Test that a delivery with an invalid signature is rejected.
	 *
	 * @since   3.4.6
	 */
	public function testReceiveInvalidSignature()
	{
		$this->enableImport();
		$this->requests = [];

		$response = $this->deliver($this->payload('post.published'), 'whsec_invalid');

		$this->assertSame(401, $response->get_status());
		$this->assertFalse($this->postsRefreshed());
	}

	/**
	 * Test that a delivery without a signature is rejected.
	 *
	 * @since   3.4.6
	 */
	public function testReceiveNoSignature()
	{
		$this->enableImport();
		$this->requests = [];

		$request = new \WP_REST_Request('POST', '/kit/v1/broadcasts/webhook');
		$request->set_body($this->payload('post.published'));
		$response = rest_get_server()->dispatch($request);

		$this->assertSame(401, $response->get_status());
		$this->assertFalse($this->postsRefreshed());
	}

	/**
	 * Test that a delivery signed more than five minutes ago is rejected.
	 *
	 * @since   3.4.6
	 */
	public function testReceiveExpiredSignature()
	{
		$this->enableImport();
		$this->requests = [];

		$response = $this->deliver($this->payload('post.published'), 'whsec_test', time() - 600);

		$this->assertSame(401, $response->get_status());
		$this->assertFalse($this->postsRefreshed());
	}

	/**
	 * Test that a delivery returns a 410 when the Broadcasts import is disabled,
	 * so Kit stops retrying it.
	 *
	 * @since   3.4.6
	 */
	public function testReceiveWhenImportDisabled()
	{
		$response = $this->deliver($this->payload('post.published'));

		$this->assertSame(410, $response->get_status());
		$this->assertFalse($this->postsRefreshed());
	}

	/**
	 * Test that the Posts resource's WordPress Cron event, used before webhooks, is removed
	 * when the Plugin is updated.
	 *
	 * @since   3.4.6
	 */
	public function testPostsCronEventRemovedOnPluginUpdate()
	{
		// Schedule the event, as earlier versions of the Plugin would.
		wp_schedule_event(time(), 'hourly', 'convertkit_resource_refresh_posts');

		// Set Plugin version number in options table to < 3.4.6.
		update_option('convertkit_version', '3.4.4');

		// Run the update action as WordPress would when updating the Plugin to a newer version.
		\WP_ConvertKit()->setup();

		// Confirm the event was removed.
		$this->assertFalse(wp_next_scheduled('convertkit_resource_refresh_posts'));
	}

	/**
	 * Enables the Broadcasts import, which registers the webhook endpoint.
	 *
	 * @since   3.4.6
	 */
	private function enableImport()
	{
		$settings = new \ConvertKit_Settings_Broadcasts();
		$settings->save([ 'enabled' => 'on' ]);
	}

	/**
	 * Returns a webhook delivery payload containing a single event of the given type.
	 *
	 * @since   3.4.6
	 *
	 * @param   string $type   Event type.
	 * @return  string
	 */
	private function payload($type)
	{
		return wp_json_encode(
			[
				'delivery_id' => wp_generate_uuid4(),
				'events'      => [
					[
						'id'      => wp_generate_uuid4(),
						'type'    => $type,
						'created' => gmdate('c'),
						'data'    => [
							'post' => [
								'id'           => 123456,
								'title'        => 'Broadcast',
								'published_at' => gmdate('c'),
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Sends the given payload to the webhook REST API route, signed with the given secret.
	 *
	 * @since   3.4.6
	 *
	 * @param   string $payload     Payload.
	 * @param   string $secret      Signing secret.
	 * @param   int    $timestamp   Timestamp.
	 * @return  WP_REST_Response
	 */
	private function deliver($payload, $secret = 'whsec_test', $timestamp = 0)
	{
		$timestamp = ( $timestamp ? $timestamp : time() );

		$request = new \WP_REST_Request('POST', '/kit/v1/broadcasts/webhook');
		$request->set_body($payload);
		$request->set_header('X-Kit-Signature', 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret));

		return rest_get_server()->dispatch($request);
	}

	/**
	 * Returns whether the Posts resource was refreshed from the Kit API.
	 *
	 * @since   3.4.6
	 *
	 * @return  bool
	 */
	private function postsRefreshed()
	{
		foreach ( $this->requests as $request ) {
			if ( $request['method'] === 'GET' && strpos($request['url'], 'https://api.kit.com/wordpress/posts?') === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Mocks Kit API requests, storing each request made and returning the defined response.
	 *
	 * @since   3.4.6
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

		$method = ( isset($args['method']) ? $args['method'] : 'GET' );

		// Store request.
		$this->requests[] = [
			'method' => $method,
			'url'    => $url,
			'body'   => ( isset($args['body']) ? json_decode($args['body'], true) : null ),
		];

		// Return the defined response.
		$key    = $method . ' ' . strtok(str_replace([ 'https://api.kit.com/v4/', 'https://api.kit.com/' ], '', $url), '?');
		$result = ( isset($this->responses[ $key ]) ? $this->responses[ $key ] : [
			'code' => 404,
			'body' => [ 'errors' => [ 'Not Found' ] ],
		] );

		return [
			'headers'  => [],
			'body'     => ( is_null($result['body']) ? '' : wp_json_encode($result['body']) ),
			'response' => [
				'code'    => $result['code'],
				'message' => '',
			],
			'cookies'  => [],
		];
	}
}
