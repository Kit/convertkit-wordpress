<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests Google reCAPTCHA and Cloudflare Turnstile verification, mocking the siteverify responses.
 *
 * @since   3.4.6
 */
class SpamProtectionTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the siteverify response body to return.
	 *
	 * @since   3.4.6
	 *
	 * @var     string
	 */
	private $siteverifyResponse = '';

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.6
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Store reCAPTCHA and Cloudflare Turnstile keys in the Plugin's settings.
		$settings = new \ConvertKit_Settings();
		$settings->save(
			array(
				'recaptcha_site_key'              => 'fakeRecaptchaSiteKey',
				'recaptcha_secret_key'            => 'fakeRecaptchaSecretKey',
				'recaptcha_minimum_score'         => 0.5,
				'cloudflare_turnstile_site_key'   => 'fakeTurnstileSiteKey',
				'cloudflare_turnstile_secret_key' => 'fakeTurnstileSecretKey',
			)
		);

		// Mock siteverify requests.
		add_filter('pre_http_request', array( $this, 'mockSiteverifyRequest' ), 10, 3);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.6
	 */
	public function tearDown(): void
	{
		remove_filter('pre_http_request', array( $this, 'mockSiteverifyRequest' ), 10);
		delete_option(\ConvertKit_Settings::SETTINGS_NAME);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that a reCAPTCHA token for the expected action with a score above the minimum score passes.
	 *
	 * @since   3.4.6
	 */
	public function testRecaptchaValidToken()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'success' => true,
				'action'  => 'convertkit_form_builder',
				'score'   => 0.9,
			]
		);

		$this->assertTrue($this->verifyRecaptcha());
	}

	/**
	 * Test that a reCAPTCHA token with a score below the minimum score fails.
	 *
	 * @since   3.4.6
	 */
	public function testRecaptchaLowScore()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'success' => true,
				'action'  => 'convertkit_form_builder',
				'score'   => 0.1,
			]
		);

		$this->assertRecaptchaFailed($this->verifyRecaptcha(), 'Google reCAPTCHA failed');
	}

	/**
	 * Test that a reCAPTCHA token generated for a different action fails, even with a high score.
	 *
	 * @since   3.4.6
	 */
	public function testRecaptchaActionMismatch()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'success' => true,
				'action'  => 'another_action',
				'score'   => 0.9,
			]
		);

		$this->assertRecaptchaFailed($this->verifyRecaptcha(), 'Google reCAPTCHA failed');
	}

	/**
	 * Test that a reCAPTCHA token with no action fails.
	 *
	 * @since   3.4.6
	 */
	public function testRecaptchaNoAction()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'success' => true,
				'score'   => 0.9,
			]
		);

		$this->assertRecaptchaFailed($this->verifyRecaptcha(), 'Google reCAPTCHA failed');
	}

	/**
	 * Test that an unsuccessful reCAPTCHA response returns the error codes.
	 *
	 * @since   3.4.6
	 */
	public function testRecaptchaFailureWithErrorCodes()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'success'     => false,
				'error-codes' => [ 'invalid-input-response' ],
			]
		);

		$this->assertRecaptchaFailed($this->verifyRecaptcha(), 'Google reCAPTCHA failure: invalid-input-response');
	}

	/**
	 * Test that an unsuccessful reCAPTCHA response with no error codes fails without a PHP error.
	 *
	 * @since   3.4.6
	 */
	public function testRecaptchaFailureWithNoErrorCodes()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'success' => false,
			]
		);

		$this->assertRecaptchaFailed($this->verifyRecaptcha(), 'Google reCAPTCHA failure: ');
	}

	/**
	 * Test that an invalid reCAPTCHA response body fails without a PHP error.
	 *
	 * @since   3.4.6
	 */
	public function testRecaptchaInvalidResponse()
	{
		$this->siteverifyResponse = 'not json';

		$this->assertRecaptchaFailed($this->verifyRecaptcha(), 'Google reCAPTCHA failure: invalid response from siteverify.');
	}

	/**
	 * Test that a successful Cloudflare Turnstile response passes.
	 *
	 * @since   3.4.6
	 */
	public function testCloudflareTurnstileValidToken()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'success' => true,
			]
		);

		$this->assertTrue($this->verifyCloudflareTurnstile());
	}

	/**
	 * Test that an unsuccessful Cloudflare Turnstile response with no error codes fails without a PHP error.
	 *
	 * @since   3.4.6
	 */
	public function testCloudflareTurnstileFailureWithNoErrorCodes()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'success' => false,
			]
		);

		$result = $this->verifyCloudflareTurnstile();
		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('Cloudflare Turnstile failure: ', $result->get_error_message());
	}

	/**
	 * Test that a Cloudflare Turnstile response with no success key fails without a PHP error.
	 *
	 * @since   3.4.6
	 */
	public function testCloudflareTurnstileNoSuccess()
	{
		$this->siteverifyResponse = wp_json_encode(
			[
				'error-codes' => [ 'internal-error' ],
			]
		);

		$result = $this->verifyCloudflareTurnstile();
		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('Cloudflare Turnstile failure: internal-error', $result->get_error_message());
	}

	/**
	 * Verifies a reCAPTCHA token for the Form Builder block's action.
	 *
	 * @since   3.4.6
	 *
	 * @return  bool|WP_Error
	 */
	private function verifyRecaptcha()
	{
		$recaptcha = new \ConvertKit_Recaptcha();
		return $recaptcha->verify('fakeToken', 'convertkit_form_builder');
	}

	/**
	 * Verifies a Cloudflare Turnstile token.
	 *
	 * @since   3.4.6
	 *
	 * @return  bool|WP_Error
	 */
	private function verifyCloudflareTurnstile()
	{
		$turnstile = new \ConvertKit_Cloudflare_Turnstile();
		return $turnstile->verify('fakeToken', 'convertkit_form_builder');
	}

	/**
	 * Asserts that the given result is a reCAPTCHA WP_Error with the given message.
	 *
	 * @since   3.4.6
	 *
	 * @param   bool|WP_Error $result     Result.
	 * @param   string        $message    Expected error message.
	 */
	private function assertRecaptchaFailed($result, $message)
	{
		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertEquals('convertkit_recaptcha_failed', $result->get_error_code());
		$this->assertEquals($message, $result->get_error_message());
	}

	/**
	 * Mocks Google reCAPTCHA and Cloudflare Turnstile siteverify requests.
	 *
	 * @since   3.4.6
	 *
	 * @param   false|array $response   Response.
	 * @param   array       $args       Request arguments.
	 * @param   string      $url        Request URL.
	 * @return  false|array
	 */
	public function mockSiteverifyRequest($response, $args, $url)
	{
		// Don't mock requests to other services.
		if ( strpos($url, 'https://www.google.com/recaptcha/api/siteverify') !== 0 && strpos($url, 'https://challenges.cloudflare.com/turnstile/v0/siteverify') !== 0 ) {
			return $response;
		}

		return [
			'headers'  => [],
			'body'     => $this->siteverifyResponse,
			'response' => [
				'code'    => 200,
				'message' => '',
			],
			'cookies'  => [],
		];
	}
}
