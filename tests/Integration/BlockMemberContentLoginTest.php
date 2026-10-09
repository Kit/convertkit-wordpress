<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Member Content Login block.
 *
 * @since   3.4.5
 */
class BlockMemberContentLoginTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the Member Content Login block class.
	 *
	 * @since   3.4.5
	 *
	 * @var     ConvertKit_Block_Member_Content_Login
	 */
	private $block;

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

		// Initialize the Restrict Content class' settings.
		WP_ConvertKit()->get_class('output_restrict_content')->initialize_classes();

		// Initialize the class we want to test.
		$this->block = new \ConvertKit_Block_Member_Content_Login();

		// Append a string to the block's output using the render filter.
		add_filter(
			'convertkit_block_member_content_login_render',
			function ($html) {
				return $html . '<!-- Filtered -->';
			}
		);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.5
	 */
	public function tearDown(): void
	{
		// Remove the subscriber ID cookie and token.
		unset($_COOKIE['ck_subscriber_id']);
		WP_ConvertKit()->get_class('output_restrict_content')->token = false;

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the convertkit_block_member_content_login_render filter is applied
	 * when the login form is output.
	 *
	 * @since   3.4.5
	 */
	public function testRenderFilterAppliedToLoginForm()
	{
		$html = $this->block->render([]);
		$this->assertStringContainsString('convertkit-restrict-content-form', $html);
		$this->assertStringEndsWith('<!-- Filtered -->', $html);
	}

	/**
	 * Test that the convertkit_block_member_content_login_render filter is applied
	 * when the code form is output.
	 *
	 * @since   3.4.5
	 */
	public function testRenderFilterAppliedToCodeForm()
	{
		// Define a token, as if the subscriber submitted their email address.
		WP_ConvertKit()->get_class('output_restrict_content')->token = 'token';

		$html = $this->block->render([]);
		$this->assertStringContainsString('convertkit-subscriber-code', $html);
		$this->assertStringEndsWith('<!-- Filtered -->', $html);
	}

	/**
	 * Test that the convertkit_block_member_content_login_render filter is applied
	 * when the subscriber is logged in.
	 *
	 * @since   3.4.5
	 */
	public function testRenderFilterAppliedWhenLoggedIn()
	{
		// Define a subscriber ID, as if the subscriber is logged in.
		$_COOKIE['ck_subscriber_id'] = $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'];

		$html = $this->block->render([]);
		$this->assertStringContainsString('convertkit-restrict-content-logout', $html);
		$this->assertStringEndsWith('<!-- Filtered -->', $html);
	}
}
