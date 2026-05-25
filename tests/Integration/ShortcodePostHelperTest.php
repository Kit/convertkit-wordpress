<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Shortcode_Post_Helper functions.
 *
 * @since   3.4.0
 */
class ShortcodePostHelperTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Shortcode Post Helper class.
	 *
	 * @since   3.4.0
	 *
	 * @var     ConvertKit_Shortcode_Post_Helper
	 */
	private $shortcode_post_helper;

	/**
	 * Holds the Post ID.
	 *
	 * @since   3.4.0
	 *
	 * @var     int
	 */
	private $postID;

	/**
	 * Holds the indicides of the existing Form shortcodes in the Post.
	 *
	 * @since   3.4.0
	 *
	 * @var     array
	 */
	private $formShortcodeIndices = [
		10,
		16,
	];

	/**
	 * Holds the total number of elements in the Post.
	 *
	 * @since   3.4.0
	 *
	 * @var     int
	 */
	private $totalElements = 28;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Create Post.
		$this->postID = $this->createPost();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the find() method returns the correct shortcode indicies and attributes.
	 *
	 * @since   3.4.0
	 */
	public function testFind()
	{
		// Find the shortcode.
		$shortcodes = \ConvertKit_Shortcode_Post_Helper::find( $this->postID, 'convertkit_form' );

		$this->assertIsArray( $shortcodes );
		$this->assertCount( 2, $shortcodes );

		// Assert first matching shortcode indicies and attributes are correct.
		$this->assertEquals( 0, $shortcodes[0]['occurrence_index'] );
		$this->assertEquals( $_ENV['CONVERTKIT_API_FORM_ID'], $shortcodes[0]['attrs']['form'] );

		// Assert second matching shortcode indicies and attributes are correct.
		$this->assertEquals( 1, $shortcodes[1]['occurrence_index'] );
		$this->assertEquals( $_ENV['CONVERTKIT_API_FORM_ID'], $shortcodes[1]['attrs']['form'] );
	}

	/**
	 * Test that the find() method returns false when no shortcodes match the given shortcode tag.
	 *
	 * @since   3.4.0
	 */
	public function testFindWhenNoShortcodesMatch()
	{
		$this->assertFalse(\ConvertKit_Shortcode_Post_Helper::find( $this->postID, 'fake_shortcode' ));
	}

	/**
	 * Test that the find() method returns a WP_Error when the post does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testFindWhenPostDoesNotExist()
	{
		$this->assertInstanceOf(\WP_Error::class, \ConvertKit_Shortcode_Post_Helper::find( 999999, 'convertkit_form' ));
	}

	/**
	 * Test that the insert() method inserts a new shortcode at the beginning of the content
	 * when the position is set to prepend.
	 *
	 * @since   3.4.0
	 */
	public function testInsertPrepend()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::insert(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'prepend'
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
	}

	/**
	 * Test that the insert() method inserts a new shortcode at the end of the content
	 * when the position is set to append.
	 *
	 * @since   3.4.0
	 */
	public function testInsertAppend()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::insert(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'append'
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
	}

	/**
	 * Test that the insert() method inserts a new shortcode at the specified index position.
	 *
	 * @since   3.4.0
	 */
	public function testInsertIndex()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::insert(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'index',
			index: 1
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
	}

	/**
	 * Test that the insert() method inserts a new shortcode at end of the content when
	 * the index is out of bounds.
	 *
	 * @since   3.4.0
	 */
	public function testInsertIndexOutOfBounds()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::insert(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'index',
			index: 100
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
	}

	/**
	 * Test that the insert() method inserts a new shortcode at the beginning of the content when
	 * the index is negative.
	 *
	 * @since   3.4.0
	 */
	public function testInsertIndexNegative()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::insert(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'index',
			index: -1
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
	}

	/**
	 * Test that the insert() method returns a WP_Error when the post does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testInsertWhenPostDoesNotExist()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::insert(
			post_id: 999999,
			shortcode_tag: 'convertkit_form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'index',
			index: 0
		);
		$this->assertInstanceOf(\WP_Error::class, $result );
	}

	/**
	 * Test that the update() method updates the attributes of an existing shortcode.
	 *
	 * @since   3.4.0
	 */
	public function testUpdate()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::update(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			occurrence_index: 0,
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ]
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );

		$result = \ConvertKit_Shortcode_Post_Helper::update(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			occurrence_index: 1,
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ]
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
	}

	/**
	 * Test that the update() method returns a WP_Error when the occurrence index is out of bounds.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateWhenOccurrenceIndexIsOutOfBounds()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::update(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			occurrence_index: 999,
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ]
		);
		$this->assertInstanceOf(\WP_Error::class, $result );
	}

	/**
	 * Test that the update() method returns a WP_Error when the post does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateWhenPostDoesNotExist()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::update(
			post_id: 999999,
			shortcode_tag: 'convertkit_form',
			occurrence_index: 0,
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ]
		);
		$this->assertInstanceOf(\WP_Error::class, $result );
	}

	/**
	 * Test that the delete() method deletes an existing shortcode.
	 *
	 * @since   3.4.0
	 */
	public function testDelete()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::delete(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			occurrence_index: 1
		);
		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );

		$result = \ConvertKit_Shortcode_Post_Helper::delete(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			occurrence_index: 0
		);
		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
	}

	/**
	 * Test that the delete() method returns a WP_Error when the occurrence index is out of bounds.
	 *
	 * @since   3.4.0
	 */
	public function testDeleteWhenOccurrenceIndexIsOutOfBounds()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::delete(
			post_id: $this->postID,
			shortcode_tag: 'convertkit_form',
			occurrence_index: 999
		);
		$this->assertInstanceOf(\WP_Error::class, $result );
	}

	/**
	 * Test that the delete() method returns a WP_Error when the post does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testDeleteWhenPostDoesNotExist()
	{
		$result = \ConvertKit_Shortcode_Post_Helper::delete(
			post_id: 999999,
			shortcode_tag: 'convertkit_form',
			occurrence_index: 0
		);
		$this->assertInstanceOf(\WP_Error::class, $result );
	}

	/**
	 * Mocks a post for testing.
	 *
	 * @since   3.4.0
	 * @return  int
	 */
	private function createPost()
	{
		// Create a Post with the given shortcode.
		return $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Shortcode Post',
				'post_content' => 'Item #1

<h2>Item #1</h2>

Item #2: Adhaésionés altéram improbis mi pariendarum sit stulti triarium

<figure class="size-large"><img src="https://placehold.co/600x400" alt="Image #1" /></figure>

<h2>Item #2</h2>

[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]

Item #3

<figure class="size-full"><img src="https://placehold.co/600x400" alt="Image #2" /></figure>

[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]

<h3>Item #1</h3>

Item #4

<h4>Item #1</h4>

Item #5

<h3>Item #2</h3>

<h4>Item #2</h4>',
			]
		);
	}
}
