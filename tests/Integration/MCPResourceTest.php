<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP resource-list abilities:
 *
 * - kit/forms-list          (ConvertKit_MCP_Ability_Resource_Forms)
 * - kit/tags-list           (ConvertKit_MCP_Ability_Resource_Tags)
 * - kit/landing-pages-list  (ConvertKit_MCP_Ability_Resource_Landing_Pages)
 * - kit/products-list       (ConvertKit_MCP_Ability_Resource_Products)
 *
 * @since   3.4.0
 */
class MCPResourceTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Settings class, so we can seed credentials in
	 * setUp() and clean up in tearDown().
	 *
	 * @since   3.4.0
	 *
	 * @var     \ConvertKit_Settings
	 */
	private $settings;

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

		// Store credentials in Plugin's settings, so the resource classes
		// can fetch live data from the Kit API when init() is called.
		$this->settings = new \ConvertKit_Settings();
		update_option(
			$this->settings::SETTINGS_NAME,
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		// Delete credentials and any cached resources so each test starts clean.
		delete_option($this->settings::SETTINGS_NAME);

		foreach ( self::RESOURCE_CLASSES as $resource_class ) {
			$resource = new $resource_class();
			delete_option($resource->settings_name);
			delete_option($resource->settings_name . '_last_queried');
		}

		// Restore the current user.
		wp_set_current_user(0);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Map of resource-list ability names to the ConvertKit_Resource_* class
	 * backing them. Used by tests that need to seed / clear the resource
	 * cache alongside the ability under test.
	 *
	 * @since   3.4.0
	 *
	 * @var     array<string, class-string>
	 */
	private const RESOURCE_CLASSES = array(
		'kit/forms-list'         => \ConvertKit_Resource_Forms::class,
		'kit/tags-list'          => \ConvertKit_Resource_Tags::class,
		'kit/landing-pages-list' => \ConvertKit_Resource_Landing_Pages::class,
		'kit/products-list'      => \ConvertKit_Resource_Products::class,
	);

	/**
	 * Test that the four resource-list abilities are registered with the
	 * `convertkit_abilities` filter, so they are picked up by the Abilities
	 * API and exposed by the MCP server.
	 *
	 * @since   3.4.0
	 */
	public function testAbilitiesRegistered()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// The ability names and classes expected to be registered.
		$expected = array(
			'kit/forms-list'         => \ConvertKit_MCP_Ability_Resource_Forms::class,
			'kit/tags-list'          => \ConvertKit_MCP_Ability_Resource_Tags::class,
			'kit/landing-pages-list' => \ConvertKit_MCP_Ability_Resource_Landing_Pages::class,
			'kit/products-list'      => \ConvertKit_MCP_Ability_Resource_Products::class,
		);

		// Assert that the abilities are registered and are instances of the expected classes.
		foreach ( $expected as $name => $class ) {
			$this->assertArrayHasKey($name, $abilities);
			$this->assertInstanceOf($class, $abilities[ $name ]);
		}
	}

	/**
	 * Test that the permission_callback() rejects a user without the
	 * edit_posts capability.
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackDeniesWithoutEditPostsCapability()
	{
		// Become a Subscriber (no edit_posts capability).
		$subscriber_id = static::factory()->user->create([ 'role' => 'subscriber' ]);
		wp_set_current_user($subscriber_id);

		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Assert that the abilities are permission denied.
		foreach ( array_keys( self::RESOURCE_CLASSES ) as $name ) {
			// Execute the ability.
			$result = $abilities[ $name ]->permission_callback([]);

			// Assert that the result is a WP_Error.
			$this->assertInstanceOf(\WP_Error::class, $result);
		}
	}

	/**
	 * Test that the permission_callback() permits a user with the edit_posts
	 * capability (e.g. an Editor or Administrator).
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackPermitsWithEditPostsCapability()
	{
		// Become an Editor (has edit_posts capability).
		$editor_id = static::factory()->user->create([ 'role' => 'editor' ]);
		wp_set_current_user($editor_id);

		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Assert that the abilities are permission granted.
		foreach ( array_keys( self::RESOURCE_CLASSES ) as $name ) {
			// Execute the ability.
			$this->assertTrue($abilities[ $name ]->permission_callback([]));
		}
	}

	/**
	 * Test that the execute_callback() returns an empty (but successful) list
	 * when the resource cache is empty, rather than an error.
	 *
	 * @since   3.4.0
	 */
	public function testReturnsEmptyListWhenNoResourcesAreCached()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		foreach ( self::RESOURCE_CLASSES as $name => $resource_class ) {
			// Ensure the cache is empty for this resource.
			delete_option( ( new $resource_class() )->settings_name );

			// Execute the ability.
			$result = $abilities[ $name ]->execute_callback([]);

			$this->assertIsArray($result);
			$this->assertArrayHasKey('count', $result);
			$this->assertArrayHasKey('items', $result);
			$this->assertSame(0, $result['count']);
			$this->assertSame([], $result['items']);
		}
	}

	/**
	 * Test that execute_callback() returns the cached items, shaped as
	 * { count, items: [{ id, name, ... }] }, when the resource cache is
	 * populated.
	 *
	 * @since   3.4.0
	 */
	public function testReturnsCachedItems()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the abilities.
		foreach ( self::RESOURCE_CLASSES as $name => $resource_class ) {
			// Populate the resource cache from the Kit API.
			( new $resource_class() )->init();

			// Execute the ability.
			$result = $abilities[ $name ]->execute_callback([]);

			$this->assertIsArray($result);
			$this->assertArrayHasKey('count', $result);
			$this->assertArrayHasKey('items', $result);
			$this->assertGreaterThan(0, $result['count']);
			$this->assertCount($result['count'], $result['items']);

			// Each item must have id and name.
			foreach ($result['items'] as $item) {
				$this->assertArrayHasKey('id', $item);
				$this->assertArrayHasKey('name', $item);
				$this->assertIsInt($item['id']);
				$this->assertIsString($item['name']);
			}
		}
	}

	/**
	 * Test that the Forms ability includes the `format` field on each item,
	 * and that legacy forms (which omit `format` in the raw resource cache)
	 * fall back to 'inline'.
	 *
	 * @since   3.4.0
	 */
	public function testFormsItemsIncludeFormat()
	{
		// Populate the resource cache from the Kit API.
		( new \ConvertKit_Resource_Forms() )->init();

		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result    = $abilities['kit/forms-list']->execute_callback([]);

		// Assert that the result is an array.
		$this->assertGreaterThan(0, $result['count']);

		// Assert that the result has items.
		$allowedFormats = [ 'inline', 'modal', 'slide in', 'sticky bar' ];
		foreach ($result['items'] as $item) {
			$this->assertArrayHasKey('format', $item);
			$this->assertContains($item['format'], $allowedFormats);
		}
	}

	/**
	 * Test that the output schema returned by each ability advertises the
	 * same keys (id, name, plus format for forms) that execute_callback()
	 * actually returns. Guards against drift between map_item() and
	 * get_item_schema().
	 *
	 * @since   3.4.0
	 */
	public function testOutputSchemaMatchesExecuteShape()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the abilities.
		foreach ( self::RESOURCE_CLASSES as $name => $resource_class ) {
			// Populate the resource cache from the Kit API.
			( new $resource_class() )->init();

			// Execute the ability.
			$result = $abilities[ $name ]->execute_callback([]);

			if ($result['count'] === 0) {
				// No items to compare against; skip this ability.
				continue;
			}

			// Assert that the output schema is an object.
			$schema = $ability->get_output_schema();
			$this->assertSame('object', $schema['type']);
			$this->assertSame([ 'count', 'items' ], $schema['required']);

			// Assert that the item schema keys match the result item keys.
			$itemSchemaKeys = array_keys($schema['properties']['items']['items']['properties']);
			$itemKeys       = array_keys($result['items'][0]);

			sort($itemSchemaKeys);
			sort($itemKeys);

			$this->assertSame($itemSchemaKeys, $itemKeys);
		}
	}
}
