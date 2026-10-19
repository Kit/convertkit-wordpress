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
	 * Test that the Post's excerpt is used as the Broadcast's preview text.
	 *
	 * @since   3.4.8
	 */
	public function testExportPreviewTextUsesExcerpt()
	{
		// Create Post with an excerpt.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Preview Text',
				'post_content' => '<p>Post content.</p>',
				'post_excerpt' => 'Post excerpt.',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the excerpt is used as the preview text.
		$this->assertEquals( 'Post excerpt.', $this->request['preview_text'] );
	}

	/**
	 * Test that no preview text is sent when the Post has no excerpt.
	 *
	 * @since   3.4.8
	 */
	public function testExportPreviewTextWhenNoExcerpt()
	{
		// Create Post without an excerpt.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Preview Text: No Excerpt',
				'post_content' => '<p>Post content.</p>',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm no preview text is sent, so Kit doesn't use an auto-generated excerpt.
		$this->assertArrayNotHasKey( 'preview_text', $this->request );
	}

	/**
	 * Test that the convertkit_broadcasts_exporter_preview_text filter defines the preview text.
	 *
	 * @since   3.4.8
	 */
	public function testExportPreviewTextFilter()
	{
		// Define preview text using the filter.
		add_filter(
			'convertkit_broadcasts_exporter_preview_text',
			function ( $preview_text, $post ) {
				return 'Filtered preview text for ' . $post->post_title;
			},
			10,
			2
		);

		// Create Post with an excerpt.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Preview Text: Filter',
				'post_content' => '<p>Post content.</p>',
				'post_excerpt' => 'Post excerpt.',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the filtered preview text is used.
		$this->assertEquals( 'Filtered preview text for Kit: Export: Preview Text: Filter', $this->request['preview_text'] );
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
