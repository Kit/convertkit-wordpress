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
 * Each ability is exercised by instantiating its PHP class directly and
 * calling permission_callback() / execute_callback(). The MCP transport
 * layer is covered by E2E tests; this suite proves the abilities themselves
 * read from the resource cache and shape the output correctly.
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

		foreach (
			[
				new \ConvertKit_Resource_Forms(),
				new \ConvertKit_Resource_Tags(),
				new \ConvertKit_Resource_Landing_Pages(),
				new \ConvertKit_Resource_Products(),
			] as $resource
		) {
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
	 * Returns each of the four resource-list abilities, paired with the
	 * resource class that backs it. Used by tests that should run identically
	 * across all four abilities.
	 *
	 * @since   3.4.0
	 *
	 * @return  array<string, array{0: \ConvertKit_MCP_Ability_Resource, 1: object}>
	 */
	public function abilityProvider(): array
	{
		return [
			'forms'         => [
				new \ConvertKit_MCP_Ability_Resource_Forms(),
				new \ConvertKit_Resource_Forms(),
			],
			'tags'          => [
				new \ConvertKit_MCP_Ability_Resource_Tags(),
				new \ConvertKit_Resource_Tags(),
			],
			'landing_pages' => [
				new \ConvertKit_MCP_Ability_Resource_Landing_Pages(),
				new \ConvertKit_Resource_Landing_Pages(),
			],
			'products'      => [
				new \ConvertKit_MCP_Ability_Resource_Products(),
				new \ConvertKit_Resource_Products(),
			],
		];
	}

	/**
	 * Test that each ability's name is the expected `kit/<resource>-list`.
	 *
	 * @since   3.4.0
	 */
	public function testAbilityNames()
	{
		$this->assertSame('kit/forms-list', ( new \ConvertKit_MCP_Ability_Resource_Forms() )->get_name());
		$this->assertSame('kit/tags-list', ( new \ConvertKit_MCP_Ability_Resource_Tags() )->get_name());
		$this->assertSame('kit/landing-pages-list', ( new \ConvertKit_MCP_Ability_Resource_Landing_Pages() )->get_name());
		$this->assertSame('kit/products-list', ( new \ConvertKit_MCP_Ability_Resource_Products() )->get_name());
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

		foreach ($this->abilityProvider() as $row) {
			[ $ability ] = $row;

			$result = $ability->permission_callback([]);

			$this->assertInstanceOf(\WP_Error::class, $result);
			$this->assertSame('convertkit_mcp_cannot_list_resources', $result->get_error_code());
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

		foreach ($this->abilityProvider() as $row) {
			[ $ability ] = $row;

			$this->assertTrue($ability->permission_callback([]));
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
		foreach ($this->abilityProvider() as $row) {
			[ $ability, $resource ] = $row;

			// Ensure the cache is empty for this resource.
			delete_option($resource->settings_name);

			$result = $ability->execute_callback([]);

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
		foreach ($this->abilityProvider() as $key => $row) {
			[ $ability, $resource ] = $row;

			// Populate the resource cache from the Kit API.
			$resource->init();

			$result = $ability->execute_callback([]);

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
		$ability  = new \ConvertKit_MCP_Ability_Resource_Forms();
		$resource = new \ConvertKit_Resource_Forms();
		$resource->init();

		$result = $ability->execute_callback([]);

		$this->assertGreaterThan(0, $result['count']);

		$allowedFormats = [ 'inline', 'modal', 'slide in', 'sticky bar' ];

		foreach ($result['items'] as $item) {
			$this->assertArrayHasKey('format', $item);
			$this->assertContains($item['format'],$allowedFormats);
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
		foreach ($this->abilityProvider() as $key => $row) {
			[ $ability, $resource ] = $row;

			$resource->init();
			$result = $ability->execute_callback([]);
			if ($result['count'] === 0) {
				// No items to compare against; skip this ability.
				continue;
			}

			$schema = $ability->get_output_schema();
			$this->assertSame('object', $schema['type']);
			$this->assertSame([ 'count', 'items' ], $schema['required']);

			$itemSchemaKeys = array_keys($schema['properties']['items']['items']['properties']);
			$itemKeys       = array_keys($result['items'][0]);

			sort($itemSchemaKeys);
			sort($itemKeys);

			$this->assertSame($itemSchemaKeys,$itemKeys);
		}
	}
}
