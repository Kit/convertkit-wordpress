<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP content abilities bound to the Broadcasts block:
 *
 * - kit/broadcasts-list    (ConvertKit_MCP_Ability_Content_List)
 * - kit/broadcasts-insert  (ConvertKit_MCP_Ability_Content_Insert)
 * - kit/broadcasts-update  (ConvertKit_MCP_Ability_Content_Update)
 * - kit/broadcasts-delete  (ConvertKit_MCP_Ability_Content_Delete)
 *
 * @since   3.4.0
 */
class MCPContentBroadcastsTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the Post ID created in setUp(), used by tests that exercise
	 * list / update / delete against a post containing two Broadcasts
	 * blocks.
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

		// Create a Post containing two Broadcasts blocks, so list / update /
		// delete have something to act on.
		$this->postID = $this->createPostWithBroadcastsBlocks();
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
	 * The ability names registered by the Broadcasts block.
	 *
	 * @since   3.4.0
	 *
	 * @var     string[]
	 */
	private const BROADCASTS_ABILITY_NAMES = array(
		'kit/broadcasts-list',
		'kit/broadcasts-insert',
		'kit/broadcasts-update',
		'kit/broadcasts-delete',
	);

	/**
	 * Test that the Broadcasts block registers all four content abilities via
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
			'kit/broadcasts-list'   => \ConvertKit_MCP_Ability_Content_List::class,
			'kit/broadcasts-insert' => \ConvertKit_MCP_Ability_Content_Insert::class,
			'kit/broadcasts-update' => \ConvertKit_MCP_Ability_Content_Update::class,
			'kit/broadcasts-delete' => \ConvertKit_MCP_Ability_Content_Delete::class,
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
		foreach ( self::BROADCASTS_ABILITY_NAMES as $name ) {
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
		foreach ( self::BROADCASTS_ABILITY_NAMES as $name ) {
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
		foreach ( self::BROADCASTS_ABILITY_NAMES as $name ) {
			// Execute the ability.
			$this->assertTrue($abilities[ $name ]->permission_callback([ 'post_id' => $this->postID ]));
		}
	}

	/**
	 * Test that kit/broadcasts-list returns every Broadcasts block occurrence
	 * in the post.
	 *
	 * @since   3.4.0
	 */
	public function testListReturnsAllBroadcastsOccurrencesInPost()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/broadcasts-list']->execute_callback([ 'post_id' => $this->postID ]);

		$this->assertIsArray($result);
		$this->assertSame($this->postID, $result['post_id']);
		$this->assertSame(2, $result['count']);
		$this->assertCount(2, $result['occurrences']);

		// Each occurrence carries an occurrence_index and an attrs object
		// holding the limit attribute from the seeded post content.
		foreach ($result['occurrences'] as $i => $occurrence) {
			$this->assertSame($i, $occurrence['occurrence_index']);
			$this->assertArrayHasKey('attrs', $occurrence);
			$this->assertSame(5, (int) $occurrence['attrs']['limit']);
		}
	}

	/**
	 * Test that kit/broadcasts-insert appends a new Broadcasts block to the
	 * post, and returns the new occurrence_index.
	 *
	 * @since   3.4.0
	 */
	public function testInsertAppendsBroadcastsBlock()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/broadcasts-insert']->execute_callback(
			array(
				'post_id'  => $this->postID,
				'attrs'    => array(
					'limit'         => 3,
					'display_image' => true,
				),
				'position' => 'append',
			)
		);

		$this->assertIsArray($result);
		$this->assertSame($this->postID, $result['post_id']);
		$this->assertSame(2, $result['occurrence_index']);

		// Confirm the post now contains three Broadcasts blocks.
		$listed = $abilities['kit/broadcasts-list']->execute_callback([ 'post_id' => $this->postID ]);
		$this->assertSame(3, $listed['count']);

		// Confirm the newly inserted block carries the attrs we passed in.
		$this->assertSame(3, (int) $listed['occurrences'][2]['attrs']['limit']);
		$this->assertTrue( (bool) $listed['occurrences'][2]['attrs']['display_image']);
	}

	/**
	 * Test that kit/broadcasts-update changes the attrs of a specific
	 * occurrence, leaving other occurrences untouched.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateModifiesSingleOccurrence()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Update the second Broadcasts block (occurrence_index 1) to a different limit.
		$result = $abilities['kit/broadcasts-update']->execute_callback(
			array(
				'post_id'          => $this->postID,
				'occurrence_index' => 1,
				'attrs'            => array( 'limit' => 25 ),
			)
		);

		$this->assertIsArray($result);
		$this->assertSame(1, $result['occurrence_index']);

		// Re-list and confirm: occurrence 0 unchanged, occurrence 1 has the new limit.
		$listed = $abilities['kit/broadcasts-list']->execute_callback([ 'post_id' => $this->postID ]);
		$this->assertSame(
			5,
			(int) $listed['occurrences'][0]['attrs']['limit'],
			'kit/broadcasts-update must not modify other occurrences.'
		);
		$this->assertSame(
			25,
			(int) $listed['occurrences'][1]['attrs']['limit'],
			'kit/broadcasts-update did not apply the new limit to the requested occurrence.'
		);
	}

	/**
	 * Test that kit/broadcasts-delete removes a specific occurrence and the
	 * post now contains one fewer Broadcasts block.
	 *
	 * @since   3.4.0
	 */
	public function testDeleteRemovesSingleOccurrence()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/broadcasts-delete']->execute_callback(
			array(
				'post_id'          => $this->postID,
				'occurrence_index' => 0,
			)
		);

		$this->assertIsArray($result);
		$this->assertSame(0, $result['occurrence_index']);

		// Confirm the post now contains a single Broadcasts block.
		$listed = $abilities['kit/broadcasts-list']->execute_callback([ 'post_id' => $this->postID ]);
		$this->assertSame(1, $listed['count']);
	}

	/**
	 * Test that kit/broadcasts-update returns a WP_Error when asked to update
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
		$result = $abilities['kit/broadcasts-update']->execute_callback(
			array(
				'post_id'          => $this->postID,
				'occurrence_index' => 99,
				'attrs'            => array( 'limit' => 5 ),
			)
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Creates a Post containing two convertkit/broadcasts blocks interleaved
	 * with non-Kit blocks, mirroring the fixture used by BlockPostHelperTest.
	 *
	 * @since   3.4.0
	 *
	 * @return  int
	 */
	private function createPostWithBroadcastsBlocks(): int
	{
		return $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Broadcasts Abilities Fixture',
				'post_content' => '<!-- wp:paragraph -->
<p>Intro paragraph.</p>
<!-- /wp:paragraph -->

<!-- wp:convertkit/broadcasts {"limit":5} /-->

<!-- wp:paragraph -->
<p>Middle paragraph.</p>
<!-- /wp:paragraph -->

<!-- wp:convertkit/broadcasts {"limit":5} /-->

<!-- wp:paragraph -->
<p>Closing paragraph.</p>
<!-- /wp:paragraph -->',
			)
		);
	}
}
