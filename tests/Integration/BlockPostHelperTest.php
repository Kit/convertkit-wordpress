<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Block_Post_Helper functions.
 *
 * @since   3.4.0
 */
class BlockPostHelperTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Block Post Helper class.
	 *
	 * @since   3.4.0
	 *
	 * @var     ConvertKit_Block_Post_Helper
	 */
	private $block_post_helper;

	/**
	 * Holds the Post ID.
	 *
	 * @since   3.4.0
	 *
	 * @var     int
	 */
	private $postID;

	/**
	 * Holds the indicides of the existing Form blocks in the Post.
	 *
	 * @since   3.4.0
	 *
	 * @var     array
	 */
	private $formBlockIndices = [
		10,
		16,
	];

	private $totalBlocks = 28;

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
	 * Test that the find() method returns the correct block indicies and attributes.
	 *
	 * @since   3.4.0
	 */
	public function testFind()
	{
		// Find the block.
		$blocks = \ConvertKit_Block_Post_Helper::find( $this->postID, 'convertkit/form' );
		$this->assertIsArray( $blocks );
		$this->assertCount( 2, $blocks );

		// Assert first matching block indicies and attributes are correct.
		$this->assertEquals( $this->formBlockIndices[0], $blocks[0]['index'] );
		$this->assertEquals( 0, $blocks[0]['occurrence_index'] );
		$this->assertEquals( $_ENV['CONVERTKIT_API_FORM_ID'], $blocks[0]['attrs']['form'] );

		// Assert second matching block indicies and attributes are correct.
		$this->assertEquals( $this->formBlockIndices[1], $blocks[1]['index'] );
		$this->assertEquals( 1, $blocks[1]['occurrence_index'] );
		$this->assertEquals( $_ENV['CONVERTKIT_API_FORM_ID'], $blocks[1]['attrs']['form'] );
	}

	/**
	 * Test that the find() method returns false when no blocks match the given block name.
	 *
	 * @since   3.4.0
	 */
	public function testFindWhenNoBlocksMatch()
	{
		$this->assertFalse(\ConvertKit_Block_Post_Helper::find( $this->postID, 'fake/block' ));
	}

	/**
	 * Test that the find() method returns a WP_Error when the post does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testFindWhenPostDoesNotExist()
	{
		$this->assertInstanceOf(\WP_Error::class, \ConvertKit_Block_Post_Helper::find( 999999, 'convertkit/form' ));
	}

	/**
	 * Test that the insert() method inserts a new block at the beginning of the content
	 * when the position is set to prepend.
	 *
	 * @since   3.4.0
	 */
	public function testInsertPrepend()
	{
		$result = \ConvertKit_Block_Post_Helper::insert(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'prepend'
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( 0, $result['index'] );
	}

	/**
	 * Test that the insert() method inserts a new block at the end of the content
	 * when the position is set to append.
	 *
	 * @since   3.4.0
	 */
	public function testInsertAppend()
	{
		$result = \ConvertKit_Block_Post_Helper::insert(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'append'
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( $this->totalBlocks + 1, $result['index'] );
	}

	/**
	 * Test that the insert() method inserts a new block at the specified index position.
	 *
	 * @since   3.4.0
	 */
	public function testInsertIndex()
	{
		$result = \ConvertKit_Block_Post_Helper::insert(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'index',
			index: 1
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( 1, $result['index'] );
	}

	/**
	 * Test that the insert() method inserts a new block at end of the content when
	 * the index is out of bounds.
	 *
	 * @since   3.4.0
	 */
	public function testInsertIndexOutOfBounds()
	{
		$result = \ConvertKit_Block_Post_Helper::insert(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'index',
			index: 100
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( $this->totalBlocks + 1, $result['index'] );
	}

	/**
	 * Test that the insert() method inserts a new block at the beginning of the content when
	 * the index is negative.
	 *
	 * @since   3.4.0
	 */
	public function testInsertIndexNegative()
	{
		$result = \ConvertKit_Block_Post_Helper::insert(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'index',
			index: -1
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( 0, $result['index'] );
	}

	/**
	 * Test that the insert() method returns a WP_Error when the post does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testInsertWhenPostDoesNotExist()
	{
		$result = \ConvertKit_Block_Post_Helper::insert(
			post_id: 999999,
			block_name: 'convertkit/form',
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ],
			position: 'index',
			index: 0
		);
		$this->assertInstanceOf(\WP_Error::class, $result );
	}

	/**
	 * Test that the update() method updates the attributes of an existing block.
	 *
	 * @since   3.4.0
	 */
	public function testUpdate()
	{
		$result = \ConvertKit_Block_Post_Helper::update(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			occurrence_index: 0,
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ]
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( $this->formBlockIndices[0], $result['index'] );

		$result = \ConvertKit_Block_Post_Helper::update(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			occurrence_index: 1,
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ]
		);

		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( $this->formBlockIndices[1], $result['index'] );
	}

	/**
	 * Test that the update() method returns a WP_Error when the occurrence index is out of bounds.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateWhenOccurrenceIndexIsOutOfBounds()
	{
		$result = \ConvertKit_Block_Post_Helper::update(
			post_id: $this->postID,
			block_name: 'convertkit/form',
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
		$result = \ConvertKit_Block_Post_Helper::update(
			post_id: 999999,
			block_name: 'convertkit/form',
			occurrence_index: 0,
			attrs: [ 'form' => $_ENV['CONVERTKIT_API_FORM_ID'] ]
		);
		$this->assertInstanceOf(\WP_Error::class, $result );
	}

	/**
	 * Test that the delete() method deletes an existing block.
	 *
	 * @since   3.4.0
	 */
	public function testDelete()
	{
		$result = \ConvertKit_Block_Post_Helper::delete(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			occurrence_index: 1
		);
		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( $this->formBlockIndices[1], $result['index'] );

		$result = \ConvertKit_Block_Post_Helper::delete(
			post_id: $this->postID,
			block_name: 'convertkit/form',
			occurrence_index: 0
		);
		$this->assertIsArray( $result );
		$this->assertEquals( $this->postID, $result['post_id'] );
		$this->assertEquals( $this->formBlockIndices[0], $result['index'] );
	}

	/**
	 * Test that the delete() method returns a WP_Error when the occurrence index is out of bounds.
	 *
	 * @since   3.4.0
	 */
	public function testDeleteWhenOccurrenceIndexIsOutOfBounds()
	{
		$result = \ConvertKit_Block_Post_Helper::delete(
			post_id: $this->postID,
			block_name: 'convertkit/form',
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
		$result = \ConvertKit_Block_Post_Helper::delete(
			post_id: 999999,
			block_name: 'convertkit/form',
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
		// Create a Post with the given block.
		return $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Block Post',
				'post_content' => '<!-- wp:paragraph -->
<p>Item #1</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Item #1</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Item #2: Adhaésionés altéram improbis mi pariendarum sit stulti triarium</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":4237,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="https://placehold.co/600x400" alt="Image #1" /></figure>
<!-- /wp:image -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Item #2</h2>
<!-- /wp:heading -->

<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->

<!-- wp:paragraph -->
<p>Item #3</p>
<!-- /wp:paragraph -->

<!-- wp:image {"id":4240,"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="https://placehold.co/600x400" alt="Image #2" /></figure>
<!-- /wp:image -->

<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Item #1</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Item #4</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Item #1</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Item #5</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Item #2</h3>
<!-- /wp:heading -->

<!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Item #2</h4>
<!-- /wp:heading -->',
			]
		);
	}
}
