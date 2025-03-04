<?php
/**
 * Tests for the ConvertKit_Broadcasts_Importer functions.
 *
 * @since   2.6.4
 */
class BroadcastsImportTest extends \Codeception\TestCase\WPTestCase
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
		$this->settings = new ConvertKit_Settings();
		$this->settings->save(
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		// Initialize the class we want to test.
		$this->importer = new ConvertKit_Broadcasts_Importer();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(WP_Error::class, $this->importer);
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
		$this->importer = new ConvertKit_Broadcasts_Importer();

		// Assert a WP_Error is returned when attempting to import a Broadcast.
		$this->assertInstanceOf(WP_Error::class, $this->importer->import_broadcast($_ENV['CONVERTKIT_API_BROADCAST_ID']));
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
		$this->assertNotInstanceOf(WP_Error::class, $result);
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
		$this->assertNotInstanceOf(WP_Error::class, $result);
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
		$this->assertNotInstanceOf(WP_Error::class, $result);
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
		$this->assertNotInstanceOf(WP_Error::class, $result);
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
		$this->assertNotInstanceOf(WP_Error::class, $result);
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
		$this->assertNotInstanceOf(WP_Error::class, $result);
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
		$this->assertNotInstanceOf(WP_Error::class, $result);
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
		$this->assertStringContainsString($_ENV['TEST_SITE_WP_URL'] . '/wp-content/uploads/2023/08', $post->post_content);
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
		$this->assertStringNotContainsString($_ENV['TEST_SITE_WP_URL'] . '/wp-content/uploads/2023/08', $post->post_content);
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
