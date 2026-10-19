<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Broadcasts_Exporter class.
 *
 * @since   3.4.8
 */
class BroadcastsExportTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the request body sent to the API when creating a Broadcast.
	 *
	 * @since   3.4.8
	 *
	 * @var     array
	 */
	private $request = array();

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.8
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Configure access and refresh token in Plugin settings.
		$settings = new \ConvertKit_Settings();
		$settings->save(
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		// Initialize the class we want to test.
		$this->exporter = new \ConvertKit_Broadcasts_Exporter();

		// Mock the API response when creating a Broadcast.
		add_filter( 'pre_http_request', array( $this, 'mockCreateBroadcastResponse' ), 10, 3 );
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.8
	 */
	public function tearDown(): void
	{
		remove_filter( 'pre_http_request', array( $this, 'mockCreateBroadcastResponse' ) );

		// Destroy the class we tested.
		unset($this->exporter);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that relative and root-relative URLs in links and images are converted to
	 * absolute URLs, and other URLs are unchanged.
	 *
	 * @since   3.4.8
	 */
	public function testExportConvertsRelativeURLsToAbsoluteURLs()
	{
		// Create Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Absolute URLs',
				'post_content' => '<p><a href="/book/">Root relative</a> <a href="book/">Relative</a> <a href="https://kit.com/">Absolute</a> <a href="mailto:hello@kit.com">Email</a> <a href="tel:123456">Phone</a></p><p><img src="/wp-content/uploads/image.png" alt="Root relative image"><img src="https://kit.com/image.png" alt="Absolute image"></p>',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm relative and root-relative URLs are converted to absolute URLs.
		$this->assertStringContainsString( '<a href="' . home_url( '/book/' ) . '">Root relative</a>', $this->request['content'] );
		$this->assertStringContainsString( '<a href="' . \WP_Http::make_absolute_url( 'book/', get_permalink( $post_id ) ) . '">Relative</a>', $this->request['content'] );
		$this->assertStringContainsString( 'src="' . home_url( '/wp-content/uploads/image.png' ) . '"', $this->request['content'] );

		// Confirm other URLs are unchanged.
		$this->assertStringContainsString( '<a href="https://kit.com/">Absolute</a>', $this->request['content'] );
		$this->assertStringContainsString( '<a href="mailto:hello@kit.com">Email</a>', $this->request['content'] );
		$this->assertStringContainsString( '<a href="tel:123456">Phone</a>', $this->request['content'] );
		$this->assertStringContainsString( 'src="https://kit.com/image.png"', $this->request['content'] );
	}

	/**
	 * Mocks the API response when creating a Broadcast, storing the request body.
	 *
	 * @since   3.4.8
	 *
	 * @param   mixed  $response       HTTP Response.
	 * @param   array  $parsed_args    Request arguments.
	 * @param   string $url            Request URL.
	 * @return  mixed
	 */
	public function mockCreateBroadcastResponse( $response, $parsed_args, $url )
	{
		// Only mock requests made to create a Broadcast.
		if ( strpos( $url, 'https://api.kit.com/v4/broadcasts' ) === false || $parsed_args['method'] !== 'POST' ) {
			return $response;
		}

		// Store the request body.
		$this->request = json_decode( $parsed_args['body'], true );

		return array(
			'headers'       => array(),
			'body'          => wp_json_encode(
				array(
					'broadcast' => array(
						'id'      => 123,
						'subject' => $this->request['subject'],
						'content' => $this->request['content'],
						'public'  => false,
					),
				)
			),
			'response'      => array(
				'code'    => 201,
				'message' => 'Created',
			),
			'cookies'       => array(),
			'http_response' => null,
		);
	}
}
