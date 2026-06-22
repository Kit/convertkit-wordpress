<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP content abilities bound to the Product block:
 *
 * - kit/product-list    (ConvertKit_MCP_Ability_Content_List)
 * - kit/product-insert  (ConvertKit_MCP_Ability_Content_Insert)
 * - kit/product-update  (ConvertKit_MCP_Ability_Content_Update)
 * - kit/product-delete  (ConvertKit_MCP_Ability_Content_Delete)
 *
 * @since   3.4.0
 */
class MCPContentProductTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the Post ID created in setUp(), used by tests that exercise
	 * list / update / delete against a post containing two Product blocks.
	 *
	 * @since   3.4.0
	 *
	 * @var     int
	 */
	private $postID;

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

		// Create a Post containing two Product blocks, so list / update /
		// delete have something to act on.
		$this->postID = $this->createPostWithProductBlocks();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		// Restore the current user.
		wp_set_current_user(0);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * The ability names registered by the Product block.
	 *
	 * @since   3.4.0
	 *
	 * @var     string[]
	 */
	private const PRODUCT_ABILITY_NAMES = array(
		'kit/product-list',
		'kit/product-insert',
		'kit/product-update',
		'kit/product-delete',
	);

	/**
	 * Test that the Product block registers all four content abilities via
	 * the convertkit_abilities filter with the expected names.
	 *
	 * @since   3.4.0
	 */
	public function testAbilitiesRegistered()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// The ability names and classes expected to be registered.
		$expected = array(
			'kit/product-list'   => \ConvertKit_MCP_Ability_Content_List::class,
			'kit/product-insert' => \ConvertKit_MCP_Ability_Content_Insert::class,
			'kit/product-update' => \ConvertKit_MCP_Ability_Content_Update::class,
			'kit/product-delete' => \ConvertKit_MCP_Ability_Content_Delete::class,
		);

		// Assert that the abilities are registered and are instances of the expected classes.
		foreach ( $expected as $name => $class ) {
			$this->assertArrayHasKey($name, $abilities);
			$this->assertInstanceOf($class, $abilities[ $name ]);
		}
	}

	/**
	 * Test that the permission_callback() rejects a user who cannot edit the
	 * given post.
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackDeniesWithoutEditPostCapability()
	{
		// Become a Subscriber (no edit_post capability).
		$subscriber_id = static::factory()->user->create([ 'role' => 'subscriber' ]);
		wp_set_current_user($subscriber_id);

		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Assert that the abilities are permission denied.
		foreach ( self::PRODUCT_ABILITY_NAMES as $name ) {
			// Execute the ability.
			$result = $abilities[ $name ]->permission_callback([ 'post_id' => $this->postID ]);

			// Assert that the result is a WP_Error.
			$this->assertInstanceOf(\WP_Error::class, $result);
		}
	}

	/**
	 * Test that the permission_callback() rejects a request with no post_id,
	 * with a clear error code.
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackDeniesWithoutPostId()
	{
		// Become an Administrator (has every capability, so the only thing
		// that can fail here is the missing post_id check).
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Assert that the abilities are permission denied.
		foreach ( self::PRODUCT_ABILITY_NAMES as $name ) {
			// Execute the ability.
			$result = $abilities[ $name ]->permission_callback([]);

			// Assert that the result is a WP_Error.
			$this->assertInstanceOf(\WP_Error::class, $result);
		}
	}

	/**
	 * Test that the permission_callback() permits an Administrator on a
	 * valid post_id.
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackPermitsAdministrator()
	{
		// Become an Administrator.
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Assert that the abilities are permission granted.
		foreach ( self::PRODUCT_ABILITY_NAMES as $name ) {
			// Execute the ability.
			$this->assertTrue($abilities[ $name ]->permission_callback([ 'post_id' => $this->postID ]));
		}
	}

	/**
	 * Test that kit/product-list returns every Product block occurrence in
	 * the post.
	 *
	 * @since   3.4.0
	 */
	public function testListReturnsAllProductOccurrencesInPost()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/product-list']->execute_callback([ 'post_id' => $this->postID ]);

		$this->assertIsArray($result);
		$this->assertSame($this->postID, $result['post_id']);
		$this->assertSame(2, $result['count']);
		$this->assertCount(2, $result['occurrences']);

		// Each occurrence carries an occurrence_index and an attrs object
		// holding the product ID from the seeded post content.
		foreach ($result['occurrences'] as $i => $occurrence) {
			$this->assertSame($i, $occurrence['occurrence_index']);
			$this->assertArrayHasKey('attrs', $occurrence);
			$this->assertSame(
				(string) $_ENV['CONVERTKIT_API_PRODUCT_ID'],
				(string) $occurrence['attrs']['product']
			);
		}
	}

	/**
	 * Test that kit/product-insert appends a new Product block to the post,
	 * and returns the new occurrence_index. Exercises all four primary
	 * Product attributes so each round-trips through the block helper.
	 *
	 * @since   3.4.0
	 */
	public function testInsertAppendsProductBlock()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/product-insert']->execute_callback(
			array(
				'post_id'  => $this->postID,
				'attrs'    => array(
					'product'       => $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					'text'          => 'Buy this product',
					'discount_code' => $_ENV['CONVERTKIT_API_PRODUCT_DISCOUNT_CODE'],
					'checkout'      => true,
				),
				'position' => 'append',
			)
		);

		$this->assertIsArray($result);
		$this->assertSame($this->postID, $result['post_id']);
		// Two Product blocks existed in setUp(); the newly inserted one is the third.
		$this->assertSame(2, $result['occurrence_index']);

		// Confirm the post now contains three Product blocks.
		$listed = $abilities['kit/product-list']->execute_callback([ 'post_id' => $this->postID ]);
		$this->assertSame(3, $listed['count']);

		// Confirm the newly inserted block carries the attrs we passed in.
		$this->assertSame(
			(string) $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			(string) $listed['occurrences'][2]['attrs']['product']
		);
		$this->assertSame('Buy this product', $listed['occurrences'][2]['attrs']['text']);
		$this->assertSame(
			(string) $_ENV['CONVERTKIT_API_PRODUCT_DISCOUNT_CODE'],
			(string) $listed['occurrences'][2]['attrs']['discount_code']
		);
		$this->assertTrue( (bool) $listed['occurrences'][2]['attrs']['checkout']);
	}

	/**
	 * Test that kit/product-update changes the attrs of a specific
	 * occurrence, leaving other occurrences untouched.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateModifiesSingleOccurrence()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Update the second Product block (occurrence_index 1) to a different
		// product ID and text.
		$new_product_id = (string) ( (int) $_ENV['CONVERTKIT_API_PRODUCT_ID'] + 1 );
		$result         = $abilities['kit/product-update']->execute_callback(
			array(
				'post_id'          => $this->postID,
				'occurrence_index' => 1,
				'attrs'            => array(
					'product' => $new_product_id,
					'text'    => 'Updated CTA',
				),
			)
		);

		$this->assertIsArray($result);
		$this->assertSame(1, $result['occurrence_index']);

		// Re-list and confirm: occurrence 0 unchanged, occurrence 1 has the
		// new product ID and text.
		$listed = $abilities['kit/product-list']->execute_callback([ 'post_id' => $this->postID ]);
		$this->assertSame(
			(string) $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			(string) $listed['occurrences'][0]['attrs']['product'],
			'kit/product-update must not modify other occurrences.'
		);
		$this->assertSame(
			$new_product_id,
			(string) $listed['occurrences'][1]['attrs']['product'],
			'kit/product-update did not apply the new product ID to the requested occurrence.'
		);
		$this->assertSame(
			'Updated CTA',
			$listed['occurrences'][1]['attrs']['text'],
			'kit/product-update did not apply the new text to the requested occurrence.'
		);
	}

	/**
	 * Test that kit/product-delete removes a specific occurrence and the
	 * post now contains one fewer Product block.
	 *
	 * @since   3.4.0
	 */
	public function testDeleteRemovesSingleOccurrence()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/product-delete']->execute_callback(
			array(
				'post_id'          => $this->postID,
				'occurrence_index' => 0,
			)
		);

		$this->assertIsArray($result);
		$this->assertSame(0, $result['occurrence_index']);

		// Confirm the post now contains a single Product block.
		$listed = $abilities['kit/product-list']->execute_callback([ 'post_id' => $this->postID ]);
		$this->assertSame(1, $listed['count']);
	}

	/**
	 * Test that kit/product-update returns a WP_Error when asked to update
	 * an occurrence that does not exist, rather than silently mutating
	 * something else.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateOnMissingOccurrenceReturnsError()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/product-update']->execute_callback(
			array(
				'post_id'          => $this->postID,
				'occurrence_index' => 99,
				'attrs'            => array( 'product' => $_ENV['CONVERTKIT_API_PRODUCT_ID'] ),
			)
		);

		// Assert that the result is a WP_Error.
		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Creates a Post containing two convertkit/product blocks interleaved
	 * with non-Kit blocks, mirroring the fixture used by BlockPostHelperTest.
	 *
	 * @since   3.4.0
	 *
	 * @return  int
	 */
	private function createPostWithProductBlocks(): int
	{
		return $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Product Abilities Fixture',
				'post_content' => '<!-- wp:paragraph -->
<p>Intro paragraph.</p>
<!-- /wp:paragraph -->

<!-- wp:convertkit/product {"product":"' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '"} /-->

<!-- wp:paragraph -->
<p>Middle paragraph.</p>
<!-- /wp:paragraph -->

<!-- wp:convertkit/product {"product":"' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '"} /-->

<!-- wp:paragraph -->
<p>Closing paragraph.</p>
<!-- /wp:paragraph -->',
			)
		);
	}
}
