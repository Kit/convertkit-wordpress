<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Form Builder block's subscribe functionality, mocking Kit API requests.
 *
 * @since   3.4.5
 */
class BlockFormBuilderTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the Form Builder block class.
	 *
	 * @since   3.4.5
	 *
	 * @var     ConvertKit_Block_Form_Builder
	 */
	private $block;

	/**
	 * Holds the Kit API requests made.
	 *
	 * @since   3.4.5
	 *
	 * @var     array
	 */
	private $requests = [];

	/**
	 * Holds the Kit API endpoints that should return an error.
	 *
	 * @since   3.4.5
	 *
	 * @var     array
	 */
	private $errorEndpoints = [];

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.5
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

		// Define Forms as if the Forms resource class populated them from the API.
		update_option(
			'convertkit_forms',
			[
				$_ENV['CONVERTKIT_API_FORM_ID'] => [
					'id'     => $_ENV['CONVERTKIT_API_FORM_ID'],
					'name'   => $_ENV['CONVERTKIT_API_FORM_NAME'],
					'format' => 'inline',
				],
			]
		);
		update_option('convertkit_forms_last_queried', time());

		// Mock Kit API requests, storing each request made.
		add_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10, 3);

		// Throw an exception on redirect, as the block exits after redirecting.
		add_filter('wp_redirect', array( $this, 'throwOnRedirect' ));

		// Initialize the class we want to test.
		$this->block = new \ConvertKit_Block_Form_Builder();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.5
	 */
	public function tearDown(): void
	{
		remove_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10);
		remove_filter('wp_redirect', array( $this, 'throwOnRedirect' ));
		unset($_REQUEST['_wpnonce'], $_REQUEST['convertkit']);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the subscriber is created as inactive when a Form is specified,
	 * so the Form's double optin setting is honored.
	 *
	 * @since   3.4.5
	 */
	public function testSubscriberInactiveWhenFormSpecified()
	{
		$this->submit(form_id: $_ENV['CONVERTKIT_API_FORM_ID']);

		// Confirm the subscriber was created as inactive, and added to the Form.
		$this->assertEquals('inactive', $this->requests[0]['body']['state']);
		$this->assertStringContainsString('forms/' . $_ENV['CONVERTKIT_API_FORM_ID'] . '/subscribers/123456', $this->requests[1]['url']);
	}

	/**
	 * Test that the subscriber is created as active when only a Tag is specified.
	 *
	 * @since   3.4.5
	 */
	public function testSubscriberActiveWhenTagSpecified()
	{
		$this->submit(tag_id: $_ENV['CONVERTKIT_API_TAG_ID']);

		// Confirm the subscriber was created as active, and tagged.
		$this->assertCount(2, $this->requests);
		$this->assertEquals('active', $this->requests[0]['body']['state']);
		$this->assertStringContainsString('tags/' . $_ENV['CONVERTKIT_API_TAG_ID'] . '/subscribers/123456', $this->requests[1]['url']);
	}

	/**
	 * Test that the subscriber is created as active when only a Sequence is specified.
	 *
	 * @since   3.4.5
	 */
	public function testSubscriberActiveWhenSequenceSpecified()
	{
		$this->submit(sequence_id: $_ENV['CONVERTKIT_API_SEQUENCE_ID']);

		// Confirm the subscriber was created as active, and added to the Sequence.
		$this->assertCount(2, $this->requests);
		$this->assertEquals('active', $this->requests[0]['body']['state']);
		$this->assertStringContainsString('sequences/' . $_ENV['CONVERTKIT_API_SEQUENCE_ID'] . '/subscribers/123456', $this->requests[1]['url']);
	}

	/**
	 * Test that the subscriber is still tagged and added to the Sequence when adding
	 * the subscriber to the Form fails.
	 *
	 * @since   3.4.5
	 */
	public function testTagAndSequenceWhenAddingSubscriberToFormFails()
	{
		// Return an error when adding the subscriber to the Form.
		$this->errorEndpoints = [ 'forms/' . $_ENV['CONVERTKIT_API_FORM_ID'] . '/subscribers/123456' ];

		$this->submit(
			form_id: $_ENV['CONVERTKIT_API_FORM_ID'],
			tag_id: $_ENV['CONVERTKIT_API_TAG_ID'],
			sequence_id: $_ENV['CONVERTKIT_API_SEQUENCE_ID']
		);

		// Confirm the subscriber was tagged and added to the Sequence.
		$this->assertCount(4, $this->requests);
		$this->assertStringContainsString('tags/' . $_ENV['CONVERTKIT_API_TAG_ID'] . '/subscribers/123456', $this->requests[2]['url']);
		$this->assertStringContainsString('sequences/' . $_ENV['CONVERTKIT_API_SEQUENCE_ID'] . '/subscribers/123456', $this->requests[3]['url']);
	}

	/**
	 * Test that the subscriber is created and the entry stored when the Name field
	 * was removed from the form.
	 *
	 * @since   3.4.5
	 */
	public function testSubscribeWhenNameFieldRemoved()
	{
		$this->submit(form_id: $_ENV['CONVERTKIT_API_FORM_ID'], include_name: false);

		// Confirm the subscriber was created without a first name.
		$this->assertArrayNotHasKey('first_name', $this->requests[0]['body']);

		// Confirm the entry was stored.
		$entries = new \ConvertKit_Form_Entries();
		$this->assertEquals(1, $entries->total());
	}

	/**
	 * Test that the Email field is always required, even if the block's required
	 * attribute is false.
	 *
	 * @since   3.4.5
	 */
	public function testEmailFieldAlwaysRequired()
	{
		$field = new \ConvertKit_Block_Form_Builder_Field_Email();
		$this->assertStringContainsString(' required', $field->render([ 'required' => false ]));
	}

	/**
	 * Submits the Form Builder block with the given Form, Tag and Sequence IDs.
	 *
	 * @since   3.4.5
	 *
	 * @param   int  $form_id        Form ID.
	 * @param   int  $tag_id         Tag ID.
	 * @param   int  $sequence_id    Sequence ID.
	 * @param   bool $include_name   Include the Name field.
	 */
	private function submit($form_id = 0, $tag_id = 0, $sequence_id = 0, $include_name = true)
	{
		$post_id = static::factory()->post->create();

		// Build the request, matching the hidden fields output by the block.
		$_REQUEST['_wpnonce']   = wp_create_nonce('convertkit_block_form_builder');
		$_REQUEST['convertkit'] = [
			'email'         => 'kit@example.com',
			'post_id'       => (string) $post_id,
			'store_entries' => '1',
			'form_id'       => (string) $form_id,
			'tag_id'        => (string) $tag_id,
			'sequence_id'   => (string) $sequence_id,
		];
		if ( $include_name ) {
			$_REQUEST['convertkit']['first_name'] = 'First';
		}

		try {
			$this->block->maybe_subscribe();
		} catch ( \Exception $e ) {
			// Confirm the block redirected, meaning no error occurred.
			$this->assertEquals('redirect', $e->getMessage());
			return;
		}

		$this->fail('The block did not redirect after subscribing.');
	}

	/**
	 * Throws an exception when the block redirects, as the block exits after redirecting.
	 *
	 * @since   3.4.5
	 *
	 * @param   string $location   Redirect URL.
	 * @throws  \Exception         Redirect.
	 */
	public function throwOnRedirect($location)
	{
		throw new \Exception('redirect');
	}

	/**
	 * Mocks Kit API requests, storing each request made and returning a response.
	 *
	 * @since   3.4.5
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

		// Return an error if this endpoint should fail.
		foreach ( $this->errorEndpoints as $endpoint ) {
			if ( strpos($url, $endpoint) !== false ) {
				return [
					'headers'  => [],
					'body'     => wp_json_encode([ 'errors' => [ 'Not Found' ] ]),
					'response' => [
						'code'    => 404,
						'message' => '',
					],
					'cookies'  => [],
				];
			}
		}

		return [
			'headers'  => [],
			'body'     => wp_json_encode(
				[
					'subscriber' => [
						'id' => 123456,
					],
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
