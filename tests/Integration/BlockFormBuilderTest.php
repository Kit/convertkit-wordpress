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
	 * Holds the URL the block redirected to.
	 *
	 * @since   3.5.3
	 *
	 * @var     string
	 */
	private $redirect = '';

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
		unset($_REQUEST['_wpnonce'], $_REQUEST['convertkit'], $_COOKIE['ck_subscriber_id'], $_COOKIE['ck_subscriber_id_signature']);

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
	 * Test that subscribing doesn't store the subscriber ID in a signed cookie, as the email
	 * address hasn't been verified, and anyone could submit another subscriber's email address.
	 *
	 * @since   3.5.3
	 */
	public function testSubscriberIDNotStoredInCookie()
	{
		$this->submit(tag_id: $_ENV['CONVERTKIT_API_TAG_ID']);

		// Confirm no signed subscriber ID was stored.
		$this->assertArrayNotHasKey('ck_subscriber_id_signature', $_COOKIE);

		// Confirm the visitor isn't identified as the subscriber.
		$subscriber = new \ConvertKit_Subscriber();
		$this->assertFalse($subscriber->get_subscriber_id());
	}

	/**
	 * Test that subscribing redirects to the current page with the submitted block's index,
	 * so the subscribed message is displayed.
	 *
	 * @since   3.5.3
	 */
	public function testRedirectIncludesSubscribedBlockIndex()
	{
		$this->submit(tag_id: $_ENV['CONVERTKIT_API_TAG_ID'], block_index: 2);

		$this->assertStringContainsString('convertkit_form_builder_subscribed=2', $this->redirect);
		$this->assertStringNotContainsString( (string) 123456, $this->redirect);
	}

	/**
	 * Test that the subscribed message is only displayed on the submitted block after redirecting.
	 *
	 * @since   3.5.3
	 */
	public function testSubscribedMessageDisplayedOnSubmittedBlock()
	{
		$content = '<div class="wp-block-convertkit-form-builder"><input type="email" name="convertkit[email]" /></div>';

		$_GET['convertkit_form_builder_subscribed'] = '2';
		$first                                      = $this->block->render([], $content);
		$second                                     = $this->block->render([], $content);
		unset($_GET['convertkit_form_builder_subscribed']);

		$this->assertStringNotContainsString('convertkit-form-builder-subscribed-message', $first);
		$this->assertStringContainsString('convertkit-form-builder-subscribed-message', $second);
	}

	/**
	 * Test that the form is hidden after redirecting when the block is configured to not display the form if subscribed.
	 *
	 * @since   3.5.3
	 */
	public function testFormHiddenAfterSubscribingWhenDisplayFormIfSubscribedDisabled()
	{
		$content = '<div class="wp-block-convertkit-form-builder"><input type="email" name="convertkit[email]" /></div>';

		$_GET['convertkit_form_builder_subscribed'] = '1';
		$html                                       = $this->block->render(
			[
				'display_form_if_subscribed' => false,
				'text_if_subscribed'         => 'Welcome to the newsletter!',
			],
			$content
		);
		unset($_GET['convertkit_form_builder_subscribed']);

		$this->assertStringContainsString('Welcome to the newsletter!', $html);
		$this->assertStringNotContainsString('name="convertkit[email]"', $html);
	}

	/**
	 * Test that the subscriber isn't created when the Tag, Form, Sequence or other block settings
	 * are changed in the request, as they don't match the block's signature.
	 *
	 * @since   3.5.3
	 */
	public function testSubscriberNotCreatedWhenBlockSettingsChanged()
	{
		$changes = [
			'form_id'           => '999',
			'tag_id'            => '999',
			'sequence_id'       => '999',
			'store_entries'     => '0',
			'redirect'          => 'https://example.com',
			'post_id'           => '999',
			'custom_field_keys' => 'last_name,secret',
		];

		foreach ( $changes as $key => $value ) {
			$this->requests = [];
			$this->submit(tag_id: $_ENV['CONVERTKIT_API_TAG_ID'], changes: [ $key => $value ], expect_redirect: false);

			// Confirm no requests were made to Kit, and the error is displayed.
			$this->assertCount(0, $this->requests, $key);
			$this->assertInstanceOf(\WP_Error::class, $this->block->error, $key);
			$this->assertEquals('convertkit_block_form_builder_invalid_signature', $this->block->error->get_error_code(), $key);
		}
	}

	/**
	 * Test that the subscriber isn't created when the signature is missing, such as a cached page
	 * from an older version of the Plugin.
	 *
	 * @since   3.5.3
	 */
	public function testSubscriberNotCreatedWhenSignatureMissing()
	{
		$this->submit(tag_id: $_ENV['CONVERTKIT_API_TAG_ID'], changes: [ 'signature' => null ], expect_redirect: false);

		$this->assertCount(0, $this->requests);
		$this->assertEquals('convertkit_block_form_builder_invalid_signature', $this->block->error->get_error_code());
	}

	/**
	 * Test that only custom fields included in the block are sent to Kit.
	 *
	 * @since   3.5.3
	 */
	public function testOnlyBlockCustomFieldsSubscribed()
	{
		$this->submit(
			tag_id: $_ENV['CONVERTKIT_API_TAG_ID'],
			changes: [
				'custom_fields' => [
					'last_name' => 'Last',
					'secret'    => 'Changed',
				],
			]
		);

		// Confirm only the block's custom field was sent when creating the subscriber.
		$this->assertEquals([ 'last_name' => 'Last' ], $this->requests[0]['body']['fields']);
	}

	/**
	 * Test that the signature matches when the block's redirect URL includes a query string.
	 *
	 * @since   3.5.3
	 */
	public function testSubscribeWithRedirectURL()
	{
		$this->submit(tag_id: $_ENV['CONVERTKIT_API_TAG_ID'], redirect: 'https://example.com/thanks/?a=1&b=2');

		$this->assertStringStartsWith('https://example.com/thanks/', $this->redirect);
	}

	/**
	 * Submits the Form Builder block with the given Form, Tag and Sequence IDs, using the
	 * hidden fields output by the block.
	 *
	 * @since   3.4.5
	 *
	 * @param   int   $form_id          Form ID.
	 * @param   int   $tag_id           Tag ID.
	 * @param   int   $sequence_id      Sequence ID.
	 * @param   bool  $include_name     Include the Name field.
	 * @param   int   $block_index      Block index.
	 * @param   array $changes          Changes to make to the submitted fields. A null value removes the field.
	 * @param   bool   $expect_redirect  Whether the block should redirect after subscribing.
	 * @param   string $redirect         Redirect URL block setting.
	 */
	private function submit($form_id = 0, $tag_id = 0, $sequence_id = 0, $include_name = true, $block_index = 0, $changes = [], $expect_redirect = true, $redirect = '')
	{
		$post_id = static::factory()->post->create();

		// Build the request from the block's hidden fields.
		$_REQUEST['_wpnonce']   = wp_create_nonce('convertkit_block_form_builder');
		$_REQUEST['convertkit'] = array_merge(
			$this->getHiddenFields(
				$post_id,
				[
					'form_id'       => (int) $form_id,
					'tag_id'        => (int) $tag_id,
					'sequence_id'   => (int) $sequence_id,
					'store_entries' => true,
					'redirect'      => $redirect,
				]
			),
			[
				'email' => 'kit@example.com',
			]
		);
		if ( $include_name ) {
			$_REQUEST['convertkit']['first_name'] = 'First';
		}
		if ( $block_index ) {
			$_REQUEST['convertkit']['block_index'] = (string) $block_index;
		}
		foreach ( $changes as $key => $value ) {
			if ( is_null($value) ) {
				unset($_REQUEST['convertkit'][ $key ]);
				continue;
			}
			$_REQUEST['convertkit'][ $key ] = $value;
		}

		try {
			$this->block->maybe_subscribe();
		} catch ( \Exception $e ) {
			// Confirm the block redirected, meaning no error occurred.
			$this->assertEquals('redirect', $e->getMessage());
			$this->assertTrue($expect_redirect, 'The block redirected after subscribing.');
			return;
		}

		if ( $expect_redirect ) {
			$this->fail('The block did not redirect after subscribing.');
		}
	}

	/**
	 * Renders the Form Builder block for the given Post, returning the hidden fields
	 * as they'd be submitted.
	 *
	 * @since   3.5.3
	 *
	 * @param   int   $post_id    Post ID.
	 * @param   array $atts       Block attributes.
	 * @return  array
	 */
	private function getHiddenFields($post_id, $atts)
	{
		global $post;
		$post = get_post($post_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		$block = new \ConvertKit_Block_Form_Builder();
		$html  = $block->render($atts, '<div class="wp-block-convertkit-form-builder"><input type="email" name="convertkit[email]" /><input type="text" name="convertkit[custom_fields][last_name]" /></div>');

		$fields = [];
		preg_match_all('/<input type="hidden" name="convertkit\[([a-z_]+)\]" value="([^"]*)"/', $html, $matches, PREG_SET_ORDER);
		foreach ( $matches as $match ) {
			$fields[ $match[1] ] = html_entity_decode($match[2]);
		}

		return $fields;
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
		$this->redirect = $location;
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
