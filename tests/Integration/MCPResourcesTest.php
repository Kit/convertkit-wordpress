<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP Resource primitive classes, registered via the
 * `convertkit_resources` filter and exposed by the MCP server as Resources:
 *
 * Live-state (JSON):
 * - kit/forms, kit/tags, kit/landing-pages, kit/products
 * - kit/account
 * - kit/settings
 *
 * Reference (Markdown):
 * - kit/overview
 * - kit/reference-forms
 * - kit/reference-restrict-content
 * - kit/reference-settings
 *
 * @since   3.5.0
 */
class MCPResourcesTest extends WPTestCase
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
	 * @since   3.5.0
	 *
	 * @var     \ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Live-state list resources: resource name => backing ConvertKit_Resource_*
	 * class, used to seed / clear the resource cache.
	 *
	 * @since   3.5.0
	 *
	 * @var     array<string, class-string>
	 */
	private const LIST_RESOURCES = array(
		'kit/forms'         => \ConvertKit_Resource_Forms::class,
		'kit/tags'          => \ConvertKit_Resource_Tags::class,
		'kit/landing-pages' => \ConvertKit_Resource_Landing_Pages::class,
		'kit/products'      => \ConvertKit_Resource_Products::class,
	);

	/**
	 * Reference (Markdown) resource names.
	 *
	 * @since   3.5.0
	 *
	 * @var     string[]
	 */
	private const REFERENCE_RESOURCES = array(
		'kit/overview',
		'kit/reference-forms',
		'kit/reference-restrict-content',
		'kit/reference-settings',
	);

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.5.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Store credentials, so the live-state resources can fetch data from
		// the Kit API when init() is called.
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
	 * @since   3.5.0
	 */
	public function tearDown(): void
	{
		// Delete credentials and any cached resources so each test starts clean.
		delete_option($this->settings::SETTINGS_NAME);

		foreach ( self::LIST_RESOURCES as $resource_class ) {
			$resource = new $resource_class();
			delete_option($resource->settings_name);
			delete_option($resource->settings_name . '_last_queried');
		}

		$account = new \ConvertKit_Resource_Account();
		delete_option($account->settings_name);
		delete_option($account->settings_name . '_last_queried');

		// Restore the current user.
		wp_set_current_user(0);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that all resources are registered with the `convertkit_resources`
	 * filter, so they are picked up by the Abilities API and exposed by the
	 * MCP server.
	 *
	 * @since   3.5.0
	 */
	public function testResourcesRegistered()
	{
		$resources = convertkit_get_resources();

		$expected = array(
			'kit/forms'                      => \ConvertKit_MCP_Resource_Forms::class,
			'kit/tags'                       => \ConvertKit_MCP_Resource_Tags::class,
			'kit/landing-pages'              => \ConvertKit_MCP_Resource_Landing_Pages::class,
			'kit/products'                   => \ConvertKit_MCP_Resource_Products::class,
			'kit/account'                    => \ConvertKit_MCP_Resource_Account::class,
			'kit/settings'                   => \ConvertKit_MCP_Resource_Settings::class,
			'kit/overview'                   => \ConvertKit_MCP_Resource_Overview::class,
			'kit/reference-forms'            => \ConvertKit_MCP_Resource_Forms_Reference::class,
			'kit/reference-restrict-content' => \ConvertKit_MCP_Resource_Restrict_Content_Reference::class,
			'kit/reference-settings'         => \ConvertKit_MCP_Resource_Settings_Reference::class,
		);

		foreach ( $expected as $name => $class ) {
			$this->assertArrayHasKey($name, $resources);
			$this->assertInstanceOf($class, $resources[ $name ]);
		}
	}

	/**
	 * Test that every registered resource advertises a kit:// URI and a
	 * supported MIME type.
	 *
	 * @since   3.5.0
	 */
	public function testResourcesHaveUriAndMimeType()
	{
		$resources = convertkit_get_resources();

		foreach ( $resources as $name => $resource ) {
			$this->assertStringStartsWith('kit://', $resource->get_uri());
			$this->assertContains(
				$resource->get_mime_type(),
				[ 'application/json', 'text/markdown' ]
			);
		}
	}

	/**
	 * Test that live-state and reference resources deny access to a user
	 * without the edit_posts capability.
	 *
	 * @since   3.5.0
	 */
	public function testEditPostsResourcesDenyWithoutEditPostsCapability()
	{
		// Become a Subscriber (no edit_posts capability).
		$subscriber_id = static::factory()->user->create([ 'role' => 'subscriber' ]);
		wp_set_current_user($subscriber_id);

		$resources = convertkit_get_resources();

		$names = array_merge(
			array_keys( self::LIST_RESOURCES ),
			self::REFERENCE_RESOURCES
		);

		foreach ( $names as $name ) {
			$this->assertInstanceOf(\WP_Error::class, $resources[ $name ]->permission_callback([]));
		}
	}

	/**
	 * Test that an Editor can read the live-state list and reference resources.
	 *
	 * @since   3.5.0
	 */
	public function testEditPostsResourcesPermitWithEditPostsCapability()
	{
		// Become an Editor (has edit_posts capability).
		$editor_id = static::factory()->user->create([ 'role' => 'editor' ]);
		wp_set_current_user($editor_id);

		$resources = convertkit_get_resources();

		$names = array_merge(
			array_keys( self::LIST_RESOURCES ),
			self::REFERENCE_RESOURCES
		);

		foreach ( $names as $name ) {
			$this->assertTrue($resources[ $name ]->permission_callback([]));
		}
	}

	/**
	 * Test that the account and settings resources require the manage_options
	 * capability: denied for an Editor, permitted for an Administrator.
	 *
	 * @since   3.5.0
	 */
	public function testManageOptionsResourcesEnforceCapability()
	{
		$resources = convertkit_get_resources();

		// Editor is denied.
		$editor_id = static::factory()->user->create([ 'role' => 'editor' ]);
		wp_set_current_user($editor_id);
		$this->assertInstanceOf(\WP_Error::class, $resources['kit/account']->permission_callback([]));
		$this->assertInstanceOf(\WP_Error::class, $resources['kit/settings']->permission_callback([]));

		// Administrator is permitted.
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);
		$this->assertTrue($resources['kit/account']->permission_callback([]));
		$this->assertTrue($resources['kit/settings']->permission_callback([]));
	}

	/**
	 * Test that list resources return a valid JSON { count, items } string
	 * with count 0 when nothing is cached.
	 *
	 * @since   3.5.0
	 */
	public function testListResourcesReturnEmptyJsonWhenNoCache()
	{
		$resources = convertkit_get_resources();

		foreach ( self::LIST_RESOURCES as $name => $resource_class ) {
			// Ensure the cache is empty for this resource.
			delete_option( ( new $resource_class() )->settings_name );

			$json = $resources[ $name ]->execute_callback([]);
			$this->assertIsString($json);

			$data = json_decode($json, true);
			$this->assertIsArray($data);
			$this->assertArrayHasKey('count', $data);
			$this->assertArrayHasKey('items', $data);
			$this->assertSame(0, $data['count']);
			$this->assertSame([], $data['items']);
		}
	}

	/**
	 * Test that each list resource returns exactly its backing list tool's
	 * output, JSON encoded. This proves the resource is single-sourced from
	 * the tool, independent of how many items the connected account holds.
	 *
	 * @since   3.5.0
	 */
	public function testListResourcesMatchBackingTool()
	{
		$resources = convertkit_get_resources();

		// Each list resource wraps the equivalent resource-list ability.
		$abilities = array(
			'kit/forms'         => \ConvertKit_MCP_Ability_Resource_Forms::class,
			'kit/tags'          => \ConvertKit_MCP_Ability_Resource_Tags::class,
			'kit/landing-pages' => \ConvertKit_MCP_Ability_Resource_Landing_Pages::class,
			'kit/products'      => \ConvertKit_MCP_Ability_Resource_Products::class,
		);

		foreach ( self::LIST_RESOURCES as $name => $resource_class ) {
			// Populate the resource cache from the Kit API.
			( new $resource_class() )->init();

			// The backing tool's output shape.
			$expected = ( new $abilities[ $name ]() )->execute_callback([]);
			$this->assertIsArray($expected);
			$this->assertArrayHasKey('count', $expected);
			$this->assertArrayHasKey('items', $expected);

			// The resource must return exactly that, JSON encoded.
			$this->assertSame( (string) wp_json_encode($expected), $resources[ $name ]->execute_callback([]));
		}
	}

	/**
	 * Test that the account resource returns an empty JSON object when no
	 * account is cached, and exactly the cached account data as JSON once
	 * populated (whatever shape the account API returns).
	 *
	 * @since   3.5.0
	 */
	public function testAccountResource()
	{
		$resources = convertkit_get_resources();
		$account   = new \ConvertKit_Resource_Account();

		// Empty cache returns an empty object.
		delete_option($account->settings_name);
		$this->assertSame('{}', $resources['kit/account']->execute_callback([]));

		// Populated cache returns exactly the cached account data, JSON encoded.
		$account->init();
		$stored   = $account->get();
		$expected = is_array($stored) ? (string) wp_json_encode($stored) : '{}';
		$this->assertSame($expected, $resources['kit/account']->execute_callback([]));
	}

	/**
	 * Test that the settings resource returns a JSON object keyed by settings
	 * group.
	 *
	 * @since   3.5.0
	 */
	public function testSettingsResource()
	{
		$resources = convertkit_get_resources();

		$data = json_decode($resources['kit/settings']->execute_callback([]), true);
		$this->assertIsArray($data);
		$this->assertArrayHasKey('general', $data);
		$this->assertArrayHasKey('broadcasts', $data);
		$this->assertArrayHasKey('restrict-content', $data);
	}

	/**
	 * Test that reference resources return non-empty Markdown beginning with a
	 * heading.
	 *
	 * @since   3.5.0
	 */
	public function testReferenceResourcesReturnMarkdown()
	{
		$resources = convertkit_get_resources();

		foreach ( self::REFERENCE_RESOURCES as $name ) {
			$this->assertSame('text/markdown', $resources[ $name ]->get_mime_type());

			$content = $resources[ $name ]->execute_callback([]);
			$this->assertIsString($content);
			$this->assertNotEmpty($content);
			$this->assertStringStartsWith('#', $content);
		}
	}
}
