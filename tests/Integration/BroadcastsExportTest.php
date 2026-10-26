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
	 * Holds the requests sent to the Broadcasts API, in the format `METHOD endpoint`.
	 *
	 * @since   3.4.8
	 *
	 * @var     array
	 */
	private $requests = array();

	/**
	 * The HTTP response code and status to return when fetching an existing Broadcast.
	 *
	 * @since   3.4.8
	 *
	 * @var     array
	 */
	private $existingBroadcast = array(
		'code'   => 200,
		'status' => 'draft',
	);

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

		// Mock the API responses when fetching and updating an existing Broadcast.
		add_filter( 'pre_http_request', array( $this, 'mockExistingBroadcastResponse' ), 10, 3 );
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.8
	 */
	public function tearDown(): void
	{
		remove_filter( 'pre_http_request', array( $this, 'mockCreateBroadcastResponse' ) );
		remove_filter( 'pre_http_request', array( $this, 'mockExistingBroadcastResponse' ) );

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
	 * Test that srcset and sizes attributes are removed from images.
	 *
	 * @since   3.4.8
	 */
	public function testExportRemovesResponsiveImageAttributes()
	{
		// Create Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Responsive Images',
				'post_content' => '<p><img src="https://kit.com/image.png" srcset="/image-300.png 300w, /image-600.png 600w" sizes="(max-width: 600px) 100vw, 600px" alt="Responsive image"></p>',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm responsive image attributes are removed, and the image remains.
		$this->assertStringContainsString( 'src="https://kit.com/image.png"', $this->request['content'] );
		$this->assertStringContainsString( 'alt="Responsive image"', $this->request['content'] );
		$this->assertStringNotContainsString( 'srcset=', $this->request['content'] );
		$this->assertStringNotContainsString( 'sizes=', $this->request['content'] );
	}

	/**
	 * Test that Broadcasts are sent to all subscribers and aren't public by default.
	 *
	 * @since   3.4.8
	 */
	public function testExportDefaultAudienceAndVisibility()
	{
		// Create Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Defaults',
				'post_content' => '<p>Post content.</p>',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the Broadcast is sent to all subscribers, and isn't public.
		$this->assertEquals( [ [ 'all' => [] ] ], $this->request['subscriber_filter'] );
		$this->assertFalse( $this->request['public'] );
		$this->assertArrayNotHasKey( 'published_at', $this->request );
	}

	/**
	 * Test that Broadcasts are sent to subscribers with the Tag defined in the Send To setting.
	 *
	 * @since   3.4.8
	 */
	public function testExportSendToTag()
	{
		// Define Send To setting.
		$this->saveBroadcastsSettings( [ 'export_send_to' => 'tag_' . $_ENV['CONVERTKIT_API_TAG_ID'] ] );

		// Create Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Send To Tag',
				'post_content' => '<p>Post content.</p>',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the Broadcast is sent to subscribers with the Tag.
		$this->assertEquals(
			[
				[
					'all' => [
						[
							'type' => 'tag',
							'ids'  => [ (int) $_ENV['CONVERTKIT_API_TAG_ID'] ],
						],
					],
				],
			],
			$this->request['subscriber_filter']
		);
	}

	/**
	 * Test that Broadcasts are sent to subscribers in the Segment defined in the Send To setting.
	 *
	 * @since   3.4.8
	 */
	public function testExportSendToSegment()
	{
		// Define Send To setting.
		$this->saveBroadcastsSettings( [ 'export_send_to' => 'segment_' . $_ENV['CONVERTKIT_API_SEGMENT_ID'] ] );

		// Create Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Send To Segment',
				'post_content' => '<p>Post content.</p>',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the Broadcast is sent to subscribers in the Segment.
		$this->assertEquals(
			[
				[
					'all' => [
						[
							'type' => 'segment',
							'ids'  => [ (int) $_ENV['CONVERTKIT_API_SEGMENT_ID'] ],
						],
					],
				],
			],
			$this->request['subscriber_filter']
		);
	}

	/**
	 * Test that Broadcasts are public, with the Post's published date, when the Make Public setting is enabled.
	 *
	 * @since   3.4.8
	 */
	public function testExportPublic()
	{
		// Enable Make Public setting.
		$this->saveBroadcastsSettings( [ 'export_public' => 'on' ] );

		// Create Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'    => 'Kit: Export: Public',
				'post_content'  => '<p>Post content.</p>',
				'post_date_gmt' => '2026-01-02 03:04:05',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the Broadcast is public, with the Post's published date.
		$this->assertTrue( $this->request['public'] );
		$this->assertEquals( '2026-01-02 03:04:05', $this->request['published_at'] );
	}

	/**
	 * Test that Broadcasts created from draft Posts are public, without a published date,
	 * when the Make Public setting is enabled.
	 *
	 * @since   3.4.8
	 */
	public function testExportPublicDraftPost()
	{
		// Enable Make Public setting.
		$this->saveBroadcastsSettings( [ 'export_public' => 'on' ] );

		// Create draft Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Public: Draft',
				'post_content' => '<p>Post content.</p>',
				'post_status'  => 'draft',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the Broadcast is public, without a published date.
		$this->assertTrue( $this->request['public'] );
		$this->assertArrayNotHasKey( 'published_at', $this->request );
	}

	/**
	 * Test that the Greeting and Sign-off settings are added to the start and end of the Broadcast,
	 * keeping Liquid tags.
	 *
	 * @since   3.4.8
	 */
	public function testExportGreetingAndSignOff()
	{
		// Define Greeting and Sign-off settings.
		$this->saveBroadcastsSettings(
			[
				'export_greeting' => 'Hi {{ subscriber.first_name | default: "there" }},',
				'export_sign_off' => '<strong>Thanks</strong>',
			]
		);

		// Create Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Greeting and Sign-off',
				'post_content' => '<p>Post content.</p>',
			]
		);

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the Greeting, content and Sign-off are in order.
		$this->assertStringStartsWith( '<p>Hi {{ subscriber.first_name | default: "there" }},</p>', $this->request['content'] );
		$this->assertStringContainsString( '<p>Post content.</p>', $this->request['content'] );
		$this->assertStringEndsWith( '<p><strong>Thanks</strong></p>' . "\n", $this->request['content'] );
	}

	/**
	 * Test that a link to read the Post on the web is added to the bottom or top of the Broadcast,
	 * with link text that can be changed using a filter.
	 *
	 * @since   3.4.8
	 */
	public function testExportWebLink()
	{
		// Create Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Web Link',
				'post_content' => '<p>Post content.</p>',
			]
		);
		$link    = '<p><a href="' . get_permalink( $post_id ) . '">Read this on the web</a></p>';

		// Confirm the link is added to the bottom.
		$this->saveBroadcastsSettings( [ 'export_web_link' => 'bottom' ] );
		$this->exporter->export_post_to_broadcast( $post_id );
		$this->assertStringEndsWith( $link, $this->request['content'] );

		// Confirm the link is added to the top.
		$this->saveBroadcastsSettings( [ 'export_web_link' => 'top' ] );
		$this->exporter->export_post_to_broadcast( $post_id );
		$this->assertStringStartsWith( $link, $this->request['content'] );

		// Confirm the link text can be changed.
		add_filter(
			'convertkit_broadcasts_exporter_web_link_text',
			function () {
				return 'View online';
			}
		);
		$this->exporter->export_post_to_broadcast( $post_id );
		$this->assertStringStartsWith( '<p><a href="' . get_permalink( $post_id ) . '">View online</a></p>', $this->request['content'] );
	}

	/**
	 * Test that a link to read the Post on the web isn't added when the Post isn't published.
	 *
	 * @since   3.4.8
	 */
	public function testExportWebLinkNotAddedToDraftPost()
	{
		// Define Read on the Web setting.
		$this->saveBroadcastsSettings( [ 'export_web_link' => 'bottom' ] );

		// Create draft Post.
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Kit: Export: Web Link: Draft',
				'post_content' => '<p>Post content.</p>',
				'post_status'  => 'draft',
			]
		);

		// Export Post.
		$this->exporter->export_post_to_broadcast( $post_id );

		// Confirm no link was added.
		$this->assertStringNotContainsString( 'Read this on the web', $this->request['content'] );
	}

	/**
	 * Test that exporting a Post creates a Broadcast, storing its ID in the Post's meta.
	 *
	 * @since   3.4.8
	 */
	public function testExportStoresBroadcastID()
	{
		// Create Post.
		$post_id = $this->factory->post->create( [ 'post_title' => 'Kit: Export: Store Broadcast ID' ] );

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm a Broadcast was created, and its ID stored.
		$this->assertEquals( [ 'POST broadcasts' ], $this->requests );
		$this->assertEquals( 123, $this->exporter->get_post_broadcast_id( $post_id ) );
	}

	/**
	 * Test that exporting a previously exported Post updates its draft Broadcast,
	 * instead of creating a new Broadcast.
	 *
	 * @since   3.4.8
	 */
	public function testExportUpdatesDraftBroadcast()
	{
		// Create Post that was previously exported.
		$post_id = $this->createExportedPost( 'Kit: Export: Update Draft Broadcast' );

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm the existing Broadcast was updated.
		$this->assertEquals( [ 'GET broadcasts/456', 'PUT broadcasts/456' ], $this->requests );
		$this->assertEquals( 'Kit: Export: Update Draft Broadcast', $this->request['subject'] );
		$this->assertEquals( 456, $this->exporter->get_post_broadcast_id( $post_id ) );
	}

	/**
	 * Test that exporting a previously exported Post returns an error if its Broadcast
	 * has been sent or scheduled, without creating or updating a Broadcast.
	 *
	 * @since   3.4.8
	 */
	public function testExportErrorWhenBroadcastSentOrScheduled()
	{
		foreach ( [ 'scheduled', 'sending', 'completed', 'aborted' ] as $status ) {
			$this->requests                    = [];
			$this->existingBroadcast['status'] = $status;

			// Create Post that was previously exported.
			$post_id = $this->createExportedPost( 'Kit: Export: Broadcast ' . $status );

			// Export Post.
			$result = $this->exporter->export_post_to_broadcast( $post_id );

			// Confirm an error is returned, and the Broadcast wasn't updated.
			$this->assertInstanceOf(\WP_Error::class, $result, $status);
			$this->assertEquals( 'This Post\'s Kit Broadcast has already been sent or scheduled, so it can\'t be updated.', $result->get_error_message() );
			$this->assertEquals( [ 'GET broadcasts/456' ], $this->requests, $status );
			$this->assertEquals( 456, $this->exporter->get_post_broadcast_id( $post_id ) );
		}
	}

	/**
	 * Test that exporting a previously exported Post creates a new Broadcast if its
	 * Broadcast was deleted in Kit.
	 *
	 * @since   3.4.8
	 */
	public function testExportCreatesBroadcastWhenDeletedInKit()
	{
		$this->existingBroadcast['code'] = 404;

		// Create Post that was previously exported.
		$post_id = $this->createExportedPost( 'Kit: Export: Broadcast Deleted' );

		// Export Post.
		$result = $this->exporter->export_post_to_broadcast( $post_id );
		$this->assertNotInstanceOf(\WP_Error::class, $result);

		// Confirm a new Broadcast was created, and its ID stored.
		$this->assertEquals( [ 'GET broadcasts/456', 'POST broadcasts' ], $this->requests );
		$this->assertEquals( 123, $this->exporter->get_post_broadcast_id( $post_id ) );
	}

	/**
	 * Creates a Post that was previously exported to Broadcast ID 456.
	 *
	 * @since   3.4.8
	 *
	 * @param   string $title  Post title.
	 * @return  int
	 */
	private function createExportedPost( $title )
	{
		return $this->factory->post->create(
			[
				'post_title'   => $title,
				'post_content' => '<p>Post content.</p>',
				'meta_input'   => [
					'_convertkit_broadcast_export_id' => 456,
				],
			]
		);
	}

	/**
	 * Saves the given Broadcasts settings, and re-initializes the exporter so it uses them.
	 *
	 * @since   3.4.8
	 *
	 * @param   array $settings   Settings.
	 */
	private function saveBroadcastsSettings( $settings )
	{
		$broadcasts_settings = new \ConvertKit_Settings_Broadcasts();
		$broadcasts_settings->save( $settings );
		$this->exporter = new \ConvertKit_Broadcasts_Exporter();
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
		$this->request    = json_decode( $parsed_args['body'], true );
		$this->requests[] = 'POST broadcasts';

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

	/**
	 * Mocks the API responses when fetching and updating an existing Broadcast, storing the request body
	 * when updating.
	 *
	 * @since   3.4.8
	 *
	 * @param   mixed  $response       HTTP Response.
	 * @param   array  $parsed_args    Request arguments.
	 * @param   string $url            Request URL.
	 * @return  mixed
	 */
	public function mockExistingBroadcastResponse( $response, $parsed_args, $url )
	{
		// Only mock requests made to fetch or update a Broadcast.
		if ( ! preg_match( '#^https://api\.kit\.com/v4/broadcasts/([0-9]+)#', $url, $matches ) || ! in_array( $parsed_args['method'], [ 'GET', 'PUT' ], true ) ) {
			return $response;
		}

		$this->requests[] = $parsed_args['method'] . ' broadcasts/' . $matches[1];

		// Return a 404 if the Broadcast should not exist.
		if ( $parsed_args['method'] === 'GET' && $this->existingBroadcast['code'] === 404 ) {
			return array(
				'headers'       => array(),
				'body'          => wp_json_encode( array( 'errors' => array( 'Not Found' ) ) ),
				'response'      => array(
					'code'    => 404,
					'message' => 'Not Found',
				),
				'cookies'       => array(),
				'http_response' => null,
			);
		}

		// Store the request body when updating.
		if ( $parsed_args['method'] === 'PUT' ) {
			$this->request = json_decode( $parsed_args['body'], true );
		}

		return array(
			'headers'       => array(),
			'body'          => wp_json_encode(
				array(
					'broadcast' => array(
						'id'      => (int) $matches[1],
						'status'  => $this->existingBroadcast['status'],
						'send_at' => ( $this->existingBroadcast['status'] === 'draft' ? null : '2026-01-01T00:00:00Z' ),
					),
				)
			),
			'response'      => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'       => array(),
			'http_response' => null,
		);
	}
}
