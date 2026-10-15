<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Broadcasts_Importer functions.
 *
 * @since   2.6.4
 */
class BroadcastsImportTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Performs actions before each test.
	 *
	 * @since   2.6.4
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Configure access and refresh token in Plugin settings.
		$this->settings = new \ConvertKit_Settings();
		$this->settings->save(
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Broadcasts_Importer();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   2.4.2
	 */
	public function tearDown(): void
	{
		// Destroy the class we tested.
		unset($this->importer);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the import_broadcast() method returns a WP_Error
	 * when no access token is specified in the Plugin's settings.
	 *
	 * @since   2.6.4
	 */
	public function testImportBroadcastWithNoAccessToken()
	{
		// Delete access and refresh token from Plugin settings.
		$this->settings->delete_credentials();

		// Re-initialize the class we want to test.
		$this->importer = new \ConvertKit_Broadcasts_Importer();

		// Assert a WP_Error is returned when attempting to import a Broadcast.
		$this->assertInstanceOf(\WP_Error::class, $this->importer->import_broadcast($_ENV['CONVERTKIT_API_BROADCAST_ID']));
	}

	/**
	 * Test that the import_broadcast() method works.
	 *
	 * @since   2.6.4
	 */
	public function testImportBroadcastWithDefaultSettings()
	{
		// Import broadcast.
		$result = $this->importer->import_broadcast($_ENV['CONVERTKIT_API_BROADCAST_ID']);

		// Assert a Post ID was returned.
		$this->assertNotInstanceOf(\WP_Error::class, $result);
		$this->assertIsInt($result);

		// Fetch Post from database.
		$post = get_post($result);

		// Assert the imported Broadcast is correct.
		$this->assertImportValid($post);
		$this->assertInlineStylesExist($post);
		$this->assertFeaturedImageDoesNotExist($post);
		$this->assertImagesNotImported($post);
		$this->assertPostStatusEquals('publish', $post);
		$this->assertPostAuthorIDEquals(1, $post);
	}

	/**
	 * Test that the import_broadcast() method works when
	 * a Post Status is defined.
	 *
	 * @since   2.6.4
	 */
	public function testImportBroadcastWithDraftPostStatus()
	{
		// Import broadcast.
		$result = $this->importer->import_broadcast(
			$_ENV['CONVERTKIT_API_BROADCAST_ID'],
			'draft'
		);

		// Assert a Post ID was returned.
		$this->assertNotInstanceOf(\WP_Error::class, $result);
		$this->assertIsInt($result);

		// Fetch Post from database.
		$post = get_post($result);

		// Assert the imported Broadcast is correct.
		$this->assertImportValid($post);
		$this->assertInlineStylesExist($post);
		$this->assertFeaturedImageDoesNotExist($post);
		$this->assertImagesNotImported($post);
		$this->assertPostStatusEquals('draft', $post);
		$this->assertPostAuthorIDEquals(1, $post);
	}

	/**
	 * Test that the import_broadcast() method works when
	 * a WordPress User ID is defined as the author.
	 *
	 * @since   2.6.4
	 */
	public function testImportBroadcastWithAuthorID()
	{
		// Import broadcast.
		$result = $this->importer->import_broadcast(
			$_ENV['CONVERTKIT_API_BROADCAST_ID'],
			'publish',
			10
		);

		// Assert a Post ID was returned.
		$this->assertNotInstanceOf(\WP_Error::class, $result);
		$this->assertIsInt($result);

		// Fetch Post from database.
		$post = get_post($result);

		// Assert the imported Broadcast is correct.
		$this->assertImportValid($post);
		$this->assertInlineStylesExist($post);
		$this->assertFeaturedImageDoesNotExist($post);
		$this->assertImagesNotImported($post);
		$this->assertPostStatusEquals('publish', $post);
		$this->assertPostAuthorIDEquals(10, $post);
	}

	/**
	 * Test that the import_broadcast() method works when
	 * a Category ID is defined.
	 *
	 * @since   2.6.4
	 */
	public function testImportBroadcastWithCategoryID()
	{
		// Create category.
		$termID = wp_create_category('Newsletter');

		// Import broadcast.
		$result = $this->importer->import_broadcast(
			$_ENV['CONVERTKIT_API_BROADCAST_ID'],
			'publish',
			1,
			$termID
		);

		// Assert a Post ID was returned.
		$this->assertNotInstanceOf(\WP_Error::class, $result);
		$this->assertIsInt($result);

		// Fetch Post from database.
		$post = get_post($result);

		// Assert the imported Broadcast is correct.
		$this->assertImportValid($post);
		$this->assertInlineStylesExist($post);
		$this->assertFeaturedImageDoesNotExist($post);
		$this->assertImagesNotImported($post);
		$this->assertPostStatusEquals('publish', $post);
		$this->assertPostAuthorIDEquals(1, $post);
		$this->assertPostHasCategory($termID, $post);
	}

	/**
	 * Test that the import_broadcast() method works when
	 * importing the Broadcast's thumbnail as the Post's
	 * Featured Image is enabled.
	 *
	 * @since   2.6.4
	 */
	public function testImportBroadcastWithImportThumbnailEnabled()
	{
		// Import broadcast.
		$result = $this->importer->import_broadcast(
			$_ENV['CONVERTKIT_API_BROADCAST_ID'],
			'publish',
			1,
			false,
			true
		);

		// Assert a Post ID was returned.
		$this->assertNotInstanceOf(\WP_Error::class, $result);
		$this->assertIsInt($result);

		// Fetch Post from database.
		$post = get_post($result);

		// Assert the imported Broadcast is correct.
		$this->assertImportValid($post);
		$this->assertInlineStylesExist($post);
		$this->assertFeaturedImageExists($post);
		$this->assertImagesNotImported($post);
		$this->assertPostStatusEquals('publish', $post);
		$this->assertPostAuthorIDEquals(1, $post);
	}

	/**
	 * Test that the import_broadcast() method works when
	 * importing images to the Media Library is enabled.
	 *
	 * @since   2.6.4
	 */
	public function testImportBroadcastWithImportImagesEnabled()
	{
		// Import broadcast.
		$result = $this->importer->import_broadcast(
			$_ENV['CONVERTKIT_API_BROADCAST_ID'],
			'publish',
			1,
			false,
			false,
			true
		);

		// Assert a Post ID was returned.
		$this->assertNotInstanceOf(\WP_Error::class, $result);
		$this->assertIsInt($result);

		// Fetch Post from database.
		$post = get_post($result);

		// Assert the imported Broadcast is correct.
		$this->assertImportValid($post);
		$this->assertInlineStylesExist($post);
		$this->assertFeaturedImageDoesNotExist($post);
		$this->assertImagesImported($post);
		$this->assertPostStatusEquals('publish', $post);
		$this->assertPostAuthorIDEquals(1, $post);
	}

	/**
	 * Test that the import_broadcast() method works when
	 * disabling styles is enabled.
	 *
	 * @since   2.6.4
	 */
	public function testImportBroadcastWithDisableStylesEnabled()
	{
		// Import broadcast.
		$result = $this->importer->import_broadcast(
			$_ENV['CONVERTKIT_API_BROADCAST_ID'],
			'publish',
			1,
			false,
			false,
			false,
			true
		);

		// Assert a Post ID was returned.
		$this->assertNotInstanceOf(\WP_Error::class, $result);
		$this->assertIsInt($result);

		// Fetch Post from database.
		$post = get_post($result);

		// Assert the imported Broadcast is correct.
		$this->assertImportValid($post);
		$this->assertInlineStylesDoNotExist($post);
		$this->assertFeaturedImageDoesNotExist($post);
		$this->assertImagesNotImported($post);
		$this->assertPostStatusEquals('publish', $post);
		$this->assertPostAuthorIDEquals(1, $post);
	}

	/**
	 * Test that the import_broadcast() method retains permitted HTML elements and removes
	 * others, with and without disabling styles, using a mocked Broadcast.
	 *
	 * @since   3.4.7
	 */
	public function testImportBroadcastHTMLElements()
	{
		// Mock the API response for the Broadcast.
		add_filter( 'pre_http_request', array( $this, 'mockBroadcastResponse' ), 10, 3 );

		// Import Broadcast with styles.
		$post = get_post( $this->importer->import_broadcast( 123 ) );

		// Assert permitted elements are retained.
		$this->assertPermittedElementsExist( $post );

		// Assert layout elements and inline styles are retained.
		$this->assertStringContainsString( '<div class="ck-inner-section" style="padding:0">', $post->post_content );
		$this->assertStringContainsString( '<span style="color:#ff0000">Span</span>', $post->post_content );
		$this->assertStringContainsString( '<table><tbody><tr><td>Cell</td></tr></tbody></table>', $post->post_content );
		$this->assertStringContainsString( '<hr style="height:2px;border:none;background-color:#e2e2e2">', $post->post_content );

		// Assert removed elements do not exist.
		$this->assertRemovedElementsDoNotExist( $post );

		// Import Broadcast with Disable Styles enabled.
		$post = get_post( $this->importer->import_broadcast( 123, 'publish', 1, false, false, false, true ) );

		// Assert permitted elements are retained.
		$this->assertPermittedElementsExist( $post );

		// Assert layout elements and inline styles are removed, retaining their contents.
		$this->assertStringNotContainsString( '<div', $post->post_content );
		$this->assertStringNotContainsString( '<span', $post->post_content );
		$this->assertStringNotContainsString( '<table', $post->post_content );
		$this->assertStringNotContainsString( 'style="', $post->post_content );
		$this->assertStringNotContainsString( 'class="', $post->post_content );
		$this->assertStringContainsString( 'Span', $post->post_content );
		$this->assertStringContainsString( 'Cell', $post->post_content );
		$this->assertStringContainsString( '<hr>', $post->post_content );

		// Assert removed elements do not exist.
		$this->assertRemovedElementsDoNotExist( $post );

		remove_filter( 'pre_http_request', array( $this, 'mockBroadcastResponse' ) );
	}

	/**
	 * Test that the generate_permalink() function strips emojis, and runs the string
	 * through the WordPress sanitize_title() function, which will convert the string
	 * to lowercase and replace spaces with hyphens.
	 *
	 * @since   2.8.2
	 */
	public function testGeneratePermalinkFunction()
	{
		$this->assertEquals('hello-world-123', $this->importer->generate_permalink('Hello World 123 🌍'));
		$this->assertEquals('hello-123-world', $this->importer->generate_permalink('Hello ❤️‍🩹 123 ❤️ World'));
		$this->assertEquals('123-hello-world', $this->importer->generate_permalink('🩹 123 👍🏿 Hello World'));
		$this->assertEquals('cafe-deja-vu', $this->importer->generate_permalink('🩹 Café déjà-vu! 👍🏿'));
		$this->assertEquals('cafe-deja-vu', $this->importer->generate_permalink('Café déjà-vu!'));
	}

	/**
	 * Mocks the API response when fetching a Broadcast, returning HTML containing
	 * elements that should be retained or removed on import.
	 *
	 * @since   3.4.7
	 *
	 * @param   false|array $response   Response.
	 * @param   array       $args       Request arguments.
	 * @param   string      $url        Request URL.
	 * @return  false|array
	 */
	public function mockBroadcastResponse( $response, $args, $url )
	{
		if ( strpos( $url, 'api.kit.com/v4/posts/123' ) === false ) {
			return $response;
		}

		$content = '<html><head><style>p { color: red; }</style></head><body>'
			. '<div class="ck-inner-section" style="padding:0">'
			. '<h1>Mocked Broadcast</h1>'
			. '<h1>Heading 1</h1><h2>Heading 2</h2><h3>Heading 3</h3><h4>Heading 4</h4><h5>Heading 5</h5><h6>Heading 6</h6>'
			. '<p><strong>Bold</strong> <em>Italic</em> <u>Underline</u> <s>Strikethrough</s> <a href="https://kit.com">Link</a><br>New line</p>'
			. '<ul><li>Unordered</li></ul><ol><li>Ordered</li></ol>'
			. '<blockquote>Quote</blockquote>'
			. '<figure><img src="https://embed.filekitcdn.com/image.png" alt="Image"><figcaption>Caption</figcaption></figure>'
			. '<hr style="height:2px;border:none;background-color:#e2e2e2">'
			. '<span style="color:#ff0000">Span</span>'
			. '<table><tbody><tr><td>Cell</td><td contenteditable="false"></td></tr></tbody></table>'
			. '<button>Button</button>'
			. '<table class="ck-poll"><tbody><tr><td>Poll</td></tr></tbody></table>'
			. '<div class="ck-hide-in-public-posts">Unsubscribe</div>'
			. '<img src="https://preview.convertkit-mail2.com/open" alt="">'
			. '</div></body></html>';

		return array(
			'headers'  => array(),
			'body'     => wp_json_encode(
				array(
					'post' => array(
						'id'               => 123,
						'title'            => 'Mocked Broadcast',
						'description'      => 'Mocked Broadcast Description',
						'meta_description' => null,
						'published_at'     => '2026-01-01T00:00:00.000Z',
						'is_paid'          => false,
						'product_id'       => null,
						'thumbnail_url'    => null,
						'thumbnail_alt'    => null,
						'content'          => $content,
					),
				)
			),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}

	/**
	 * Assert that the permitted HTML elements exist in the imported Post's content.
	 *
	 * @since   3.4.7
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertPermittedElementsExist($post)
	{
		$this->assertStringContainsString( '<h1>Heading 1</h1>', $post->post_content );
		$this->assertStringContainsString( '<h2>Heading 2</h2>', $post->post_content );
		$this->assertStringContainsString( '<h3>Heading 3</h3>', $post->post_content );
		$this->assertStringContainsString( '<h4>Heading 4</h4>', $post->post_content );
		$this->assertStringContainsString( '<h5>Heading 5</h5>', $post->post_content );
		$this->assertStringContainsString( '<h6>Heading 6</h6>', $post->post_content );
		$this->assertStringContainsString( '<strong>Bold</strong>', $post->post_content );
		$this->assertStringContainsString( '<em>Italic</em>', $post->post_content );
		$this->assertStringContainsString( '<u>Underline</u>', $post->post_content );
		$this->assertStringContainsString( '<s>Strikethrough</s>', $post->post_content );
		$this->assertStringContainsString( '<a href="https://kit.com">Link</a>', $post->post_content );
		$this->assertStringContainsString( '<br>', $post->post_content );
		$this->assertStringContainsString( '<ul><li>Unordered</li></ul>', $post->post_content );
		$this->assertStringContainsString( '<ol><li>Ordered</li></ol>', $post->post_content );
		$this->assertStringContainsString( '<blockquote>Quote</blockquote>', $post->post_content );
		$this->assertStringContainsString( '<figure><img src="https://embed.filekitcdn.com/image.png" alt="Image"><figcaption>Caption</figcaption></figure>', $post->post_content );
		$this->assertStringContainsString( '<hr', $post->post_content );
	}

	/**
	 * Assert that HTML elements which should be removed do not exist in the imported Post's content.
	 *
	 * @since   3.4.7
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertRemovedElementsDoNotExist($post)
	{
		// Wrapper and non-permitted elements are removed, retaining their contents.
		$this->assertStringNotContainsString( '<html', $post->post_content );
		$this->assertStringNotContainsString( '<body', $post->post_content );
		$this->assertStringNotContainsString( '<button', $post->post_content );
		$this->assertStringContainsString( 'Button', $post->post_content );

		// Elements are removed with their contents.
		$this->assertStringNotContainsString( '<h1>Mocked Broadcast</h1>', $post->post_content );
		$this->assertStringNotContainsString( 'color: red', $post->post_content );
		$this->assertStringNotContainsString( 'contenteditable', $post->post_content );
		$this->assertStringNotContainsString( 'Poll', $post->post_content );
		$this->assertStringNotContainsString( 'Unsubscribe', $post->post_content );
		$this->assertStringNotContainsString( 'preview.convertkit-mail2.com/open', $post->post_content );
	}

	/**
	 * Assert that the created Post's content is valid.
	 *
	 * @since   2.6.4
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertImportValid($post)
	{
		// Confirm title correct.
		$this->assertEquals($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE'], $post->post_title);

		// Confirm the title is not included in the content as a heading.
		$this->assertStringNotContainsString($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE'] . '</h1>', $post->post_content);

		// Confirm tracking image has been removed.
		$this->assertStringNotContainsString('<img src="https://preview.convertkit-mail2.com/open" alt="">', $post->post_content);

		// Confirm images included retain their <figure> and <figcaption> elements.
		$this->assertStringContainsString('<figure', $post->post_content);
		$this->assertStringContainsString('<figcaption', $post->post_content);
		$this->assertStringContainsString('A Sample Caption</figcaption>', $post->post_content);
		$this->assertStringContainsString('</figure>', $post->post_content);

		// Confirm unsubscribe link section has been removed.
		$this->assertStringNotContainsString('<div class="ck-section ck-hide-in-public-posts"', $post->post_content);

		// Confirm poll block has been removed.
		$this->assertStringNotContainsString('<table roll="presentation" class="ck-poll', $post->post_content);

		// Confirm published date matches the Broadcast.
		$date = date('Y-m-d', strtotime($_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'])) . ' ' . date('H:i:s', strtotime($_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE']));
		$this->assertEquals($date, $post->post_date_gmt);

		// Confirm SEO description is stored as the Post's excerpt.
		$this->assertEquals($_ENV['CONVERTKIT_API_BROADCAST_FIRST_DESCRIPTION'], $post->post_excerpt);
	}

	/**
	 * Assert that the created Post's post status matches the expected status.
	 *
	 * @since   2.6.4
	 *
	 * @param   string  $status Expected Post Status.
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertPostStatusEquals($status, $post)
	{
		$this->assertEquals($status, $post->post_status);
	}

	/**
	 * Assert that the created Post's inline styles exist.
	 *
	 * @since   2.6.4
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertInlineStylesExist($post)
	{
		$this->assertStringContainsString('<div class="ck-inner-section', $post->post_content);
	}

	/**
	 * Assert that the created Post's inline styles do not exist.
	 *
	 * @since   2.6.4
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertInlineStylesDoNotExist($post)
	{
		$this->assertStringNotContainsString('<div class="ck-inner-section', $post->post_content);
		$this->assertStringNotContainsString('style="', $post->post_content);
	}

	/**
	 * Assert that the created Post has a Featured Image.
	 *
	 * @since   2.6.4
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertFeaturedImageExists($post)
	{
		$this->assertGreaterThan(0, (int) get_post_meta($post->ID, '_thumbnail_id', true));
	}

	/**
	 * Assert that the created Post does not have a Featured Image.
	 *
	 * @since   2.6.4
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertFeaturedImageDoesNotExist($post)
	{
		$this->assertEmpty(get_post_meta($post->ID, '_thumbnail_id', true));
	}

	/**
	 * Assert that the created Post's images were imported from Kit
	 * and saved in the Media Library.
	 *
	 * @since   2.6.4
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertImagesImported($post)
	{
		$this->assertStringNotContainsString('embed.filekitcdn.com', $post->post_content);
		$this->assertStringContainsString($_ENV['WORDPRESS_URL'] . '/wp-content/uploads/2023/08', $post->post_content);
	}

	/**
	 * Assert that the created Post's images were not imported from Kit
	 * and saved in the Media Library.
	 *
	 * @since   2.6.4
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertImagesNotImported($post)
	{
		$this->assertStringContainsString('embed.filekitcdn.com', $post->post_content);
		$this->assertStringNotContainsString($_ENV['WORDPRESS_URL'] . '/wp-content/uploads/2023/08', $post->post_content);
	}

	/**
	 * Assert that the created Post's author matches the expected
	 * WordPress User ID.
	 *
	 * @since   2.6.4
	 *
	 * @param   int     $id     WordPress User ID.
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertPostAuthorIDEquals($id, $post)
	{
		$this->assertEquals($id, (int) $post->post_author);
	}

	/**
	 * Assert that the created Post has the expected
	 * WordPress Category assigned to it.
	 *
	 * @since   2.6.4
	 *
	 * @param   int     $id     WordPress Category ID.
	 * @param   WP_Post $post   WordPress Post.
	 */
	private function assertPostHasCategory($id, $post)
	{
		$this->assertTrue(has_category($id, $post));
	}
}
