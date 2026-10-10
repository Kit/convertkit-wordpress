<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests the ConvertKit_Subscriber class.
 *
 * @since   3.4.7
 */
class SubscriberTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Subscriber class.
	 *
	 * @since   3.4.7
	 *
	 * @var     \ConvertKit_Subscriber
	 */
	private $subscriber;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.7
	 */
	public function setUp(): void
	{
		parent::setUp();
		activate_plugins('convertkit/wp-convertkit.php');

		// Store Credentials in Plugin's settings.
		update_option(
			\ConvertKit_Settings::SETTINGS_NAME,
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		$this->subscriber = new \ConvertKit_Subscriber();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.7
	 */
	public function tearDown(): void
	{
		unset($_COOKIE['ck_subscriber_id'], $_COOKIE['ck_subscriber_id_signature']);
		remove_all_filters('convertkit_subscriber_allow_unverified_id');
		deactivate_plugins('convertkit/wp-convertkit.php');
		parent::tearDown();
	}

	/**
	 * Test that a numeric subscriber ID in the cookie is returned when it has a valid signature.
	 *
	 * @since   3.4.7
	 */
	public function testNumericSubscriberIDWithValidSignature()
	{
		$_COOKIE['ck_subscriber_id']           = '123';
		$_COOKIE['ck_subscriber_id_signature'] = hash_hmac('sha256', 'ck_subscriber_id_123', wp_salt());
		$this->assertEquals('123', $this->subscriber->get_subscriber_id());
	}

	/**
	 * Test that a numeric subscriber ID in the cookie is ignored when it has no signature.
	 *
	 * @since   3.4.7
	 */
	public function testNumericSubscriberIDWithoutSignature()
	{
		$_COOKIE['ck_subscriber_id'] = '123';
		$this->assertFalse($this->subscriber->get_subscriber_id());
	}

	/**
	 * Test that a numeric subscriber ID in the cookie is ignored when its signature is for a different subscriber ID.
	 *
	 * @since   3.4.7
	 */
	public function testNumericSubscriberIDWithInvalidSignature()
	{
		$_COOKIE['ck_subscriber_id']           = '124';
		$_COOKIE['ck_subscriber_id_signature'] = hash_hmac('sha256', 'ck_subscriber_id_123', wp_salt());
		$this->assertFalse($this->subscriber->get_subscriber_id());
	}

	/**
	 * Test that a signed subscriber ID in the cookie is returned, as it's verified by the API when used.
	 *
	 * @since   3.4.7
	 */
	public function testSignedSubscriberID()
	{
		$_COOKIE['ck_subscriber_id'] = $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'];
		$this->assertEquals($_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $this->subscriber->get_subscriber_id());
	}

	/**
	 * Test that the convertkit_subscriber_allow_unverified_id filter permits a numeric subscriber ID without a signature.
	 *
	 * @since   3.4.7
	 */
	public function testAllowUnverifiedIDFilter()
	{
		add_filter('convertkit_subscriber_allow_unverified_id', '__return_true');
		$_COOKIE['ck_subscriber_id'] = '123';
		$this->assertEquals('123', $this->subscriber->get_subscriber_id());
	}

	/**
	 * Test that the subscriber's hashed email address is verified against the subscriber ID.
	 *
	 * @since   3.4.7
	 */
	public function testVerifyHashedEmail()
	{
		// Get the subscriber's email address.
		$api        = new \ConvertKit_API_V4(
			$_ENV['CONVERTKIT_OAUTH_CLIENT_ID'],
			$_ENV['KIT_OAUTH_REDIRECT_URI'],
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN']
		);
		$subscriber = $api->get_subscriber( (int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
		$email      = $subscriber['subscriber']['email_address'];

		// Call the private verify_hashed_email() method.
		$method = new \ReflectionMethod(\ConvertKit_Subscriber::class, 'verify_hashed_email');
		$method->setAccessible(true);

		$this->assertTrue($method->invoke($this->subscriber, $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], hash('sha256', $email)));
		$this->assertTrue($method->invoke($this->subscriber, $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], strtoupper(hash('sha256', $email))));
		$this->assertFalse($method->invoke($this->subscriber, $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], hash('sha256', 'not-the-subscriber@kit.com')));
		$this->assertFalse($method->invoke($this->subscriber, $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], ''));
		$this->assertFalse($method->invoke($this->subscriber, '1', hash('sha256', $email)));
	}
}
