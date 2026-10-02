<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Forminator class, mocking Kit API requests.
 *
 * @since   3.4.5
 */
class ForminatorTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit_Forminator class.
	 *
	 * @since   3.4.5
	 *
	 * @var     ConvertKit_Forminator
	 */
	private $forminator;

	/**
	 * Holds the Forminator Form ID.
	 *
	 * @since   3.4.5
	 *
	 * @var     int
	 */
	private $forminatorFormID = 123;

	/**
	 * Holds the Kit API requests made.
	 *
	 * @since   3.4.5
	 *
	 * @var     array
	 */
	private $requests = [];

	/**
	 * Holds the HTTP status code the mocked Kit API returns.
	 *
	 * @since   3.4.5
	 *
	 * @var     int
	 */
	private $responseCode = 201;

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

		// Map the Forminator Form to the Kit Form.
		update_option(
			\ConvertKit_Forminator_Settings::SETTINGS_NAME,
			[
				$this->forminatorFormID => 'form:' . $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);

		// Mock Kit API requests, storing each request made.
		add_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10, 3);

		// Initialize the class we want to test.
		$this->forminator = new \ConvertKit_Forminator();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.5
	 */
	public function tearDown(): void
	{
		remove_filter('pre_http_request', array( $this, 'mockKitAPIRequest' ), 10);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that a subscriber is created with the first name from a single Name field,
	 * and added to the mapped Kit Form.
	 *
	 * @since   3.4.5
	 */
	public function testSubscribeWithNameField()
	{
		$this->forminator->maybe_subscribe($this->createEntry(), $this->forminatorFormID, $this->getFormData('Kit Name'));

		// Confirm the subscriber was created and added to the Form.
		$this->assertCount(2, $this->requests);
		$this->assertEquals('Kit', $this->requests[0]['body']['first_name']);
		$this->assertStringContainsString('forms/' . $_ENV['CONVERTKIT_API_FORM_ID'] . '/subscribers/', $this->requests[1]['url']);
	}

	/**
	 * Test that a subscriber is created with the first name from a Name field that
	 * uses multiple fields (prefix, first, middle and last name).
	 *
	 * @since   3.4.5
	 */
	public function testSubscribeWithMultipleNameFields()
	{
		$this->forminator->maybe_subscribe(
			$this->createEntry(),
			$this->forminatorFormID,
			$this->getFormData(
				[
					'prefix'      => 'Mr',
					'first-name'  => 'Kit',
					'middle-name' => '',
					'last-name'   => 'Name',
				]
			)
		);

		// Confirm the subscriber was created and added to the Form.
		$this->assertCount(2, $this->requests);
		$this->assertEquals('Kit', $this->requests[0]['body']['first_name']);
	}

	/**
	 * Test that no attempt is made to add the subscriber to the Form when creating
	 * the subscriber fails.
	 *
	 * @since   3.4.5
	 */
	public function testSubscribeWhenCreateSubscriberFails()
	{
		// Return an error from the Kit API.
		$this->responseCode = 422;

		$this->forminator->maybe_subscribe($this->createEntry(), $this->forminatorFormID, $this->getFormData('Kit Name'));

		// Confirm only the create subscriber request was made.
		$this->assertCount(1, $this->requests);
		$this->assertStringEndsWith('subscribers', $this->requests[0]['url']);
	}

	/**
	 * Test that spam, draft and abandoned entries are not subscribed.
	 *
	 * @since   3.4.5
	 */
	public function testSpamDraftAndAbandonedEntriesAreNotSubscribed()
	{
		foreach ( [ 'spam', 'draft', 'abandoned' ] as $status ) {
			$this->forminator->maybe_subscribe($this->createEntry($status), $this->forminatorFormID, $this->getFormData('Kit Name'));
		}

		// Flag an active entry as spam, as Forminator versions before 1.48.0 only set is_spam.
		$entry          = $this->createEntry();
		$entry->is_spam = 1;
		$this->forminator->maybe_subscribe($entry, $this->forminatorFormID, $this->getFormData('Kit Name'));

		// Confirm no requests were made.
		$this->assertCount(0, $this->requests);
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

		return [
			'headers'  => [],
			'body'     => wp_json_encode(
				$this->responseCode === 201 ? [
					'subscriber' => [
						'id' => 123456,
					],
				] : [
					'errors' => [ 'Email address is invalid' ],
				]
			),
			'response' => [
				'code'    => $this->responseCode,
				'message' => '',
			],
			'cookies'  => [],
		];
	}

	/**
	 * Creates a Forminator Form Entry with the given status.
	 *
	 * @since   3.4.5
	 *
	 * @param   string $status     Entry status.
	 * @return  object
	 */
	private function createEntry($status = 'active')
	{
		$entry          = new \stdClass();
		$entry->form_id = $this->forminatorFormID;
		$entry->status  = $status;
		$entry->is_spam = false;
		return $entry;
	}

	/**
	 * Returns Forminator's submitted form data for a Name and Email field.
	 *
	 * @since   3.4.5
	 *
	 * @param   string|array $name   Name field value.
	 * @return  array
	 */
	private function getFormData($name)
	{
		return [
			[
				'name'       => 'name-1',
				'value'      => $name,
				'field_type' => 'name',
			],
			[
				'name'       => 'email-1',
				'value'      => 'kit@example.com',
				'field_type' => 'email',
			],
		];
	}
}
