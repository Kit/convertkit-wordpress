<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP Prompt classes, registered via the `convertkit_prompts`
 * filter and exposed by the MCP server as Prompts:
 *
 * - kit/setup
 * - kit/add-form
 * - kit/restrict-content
 * - kit/configure-broadcasts-import
 * - kit/audit
 *
 * @since   3.5.0
 */
class MCPPromptsTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Expected prompt name => class.
	 *
	 * @since   3.5.0
	 *
	 * @var     array<string, class-string>
	 */
	private const PROMPTS = array(
		'kit/setup'                       => \ConvertKit_MCP_Prompt_Setup::class,
		'kit/add-form'                    => \ConvertKit_MCP_Prompt_Add_Form::class,
		'kit/restrict-content'            => \ConvertKit_MCP_Prompt_Restrict_Content::class,
		'kit/configure-broadcasts-import' => \ConvertKit_MCP_Prompt_Configure_Broadcasts_Import::class,
		'kit/audit'                       => \ConvertKit_MCP_Prompt_Audit::class,
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
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.5.0
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
	 * Test that all prompts are registered with the `convertkit_prompts`
	 * filter, so they are picked up by the Abilities API and exposed by the
	 * MCP server.
	 *
	 * @since   3.5.0
	 */
	public function testPromptsRegistered()
	{
		$prompts = convertkit_get_prompts();

		foreach ( self::PROMPTS as $name => $class ) {
			$this->assertArrayHasKey($name, $prompts);
			$this->assertInstanceOf($class, $prompts[ $name ]);
		}
	}

	/**
	 * Test that each prompt advertises itself to the MCP Adapter as a prompt.
	 *
	 * @since   3.5.0
	 */
	public function testPromptsAreMarkedAsPrompts()
	{
		$prompts = convertkit_get_prompts();

		foreach ( self::PROMPTS as $name => $class ) {
			$args = $prompts[ $name ]->get_ability_args();
			$this->assertSame('prompt', $args['meta']['mcp']['type']);
			$this->assertTrue($args['meta']['mcp']['public']);
		}
	}

	/**
	 * Test that each prompt returns non-empty text beginning with a heading.
	 *
	 * @since   3.5.0
	 */
	public function testPromptsReturnText()
	{
		$prompts = convertkit_get_prompts();

		foreach ( self::PROMPTS as $name => $class ) {
			$result = $prompts[ $name ]->execute_callback([]);
			$this->assertIsArray($result);
			$this->assertArrayHasKey('text', $result);
			$this->assertNotEmpty($result['text']);
			$this->assertStringStartsWith('#', $result['text']);
		}
	}

	/**
	 * Test that the prompt permission callback requires the manage_options
	 * capability: denied for an Editor, permitted for an Administrator.
	 *
	 * @since   3.5.0
	 */
	public function testPromptsRequireManageOptions()
	{
		$prompts = convertkit_get_prompts();

		// Editor is denied.
		$editor_id = static::factory()->user->create([ 'role' => 'editor' ]);
		wp_set_current_user($editor_id);
		foreach ( array_keys( self::PROMPTS ) as $name ) {
			$this->assertInstanceOf(\WP_Error::class, $prompts[ $name ]->permission_callback([]));
		}

		// Administrator is permitted.
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);
		foreach ( array_keys( self::PROMPTS ) as $name ) {
			$this->assertTrue($prompts[ $name ]->permission_callback([]));
		}
	}

	/**
	 * Test that prompts declaring arguments expose them in their input schema.
	 *
	 * @since   3.5.0
	 */
	public function testPromptArgumentsInInputSchema()
	{
		$prompts = convertkit_get_prompts();

		$addFormSchema = $prompts['kit/add-form']->get_input_schema();
		$this->assertArrayHasKey('form', $addFormSchema['properties']);
		$this->assertArrayHasKey('scope', $addFormSchema['properties']);

		$restrictSchema = $prompts['kit/restrict-content']->get_input_schema();
		$this->assertArrayHasKey('post_id', $restrictSchema['properties']);
		$this->assertArrayHasKey('gate', $restrictSchema['properties']);
	}

	/**
	 * Test that provided argument values are woven into the prompt text.
	 *
	 * @since   3.5.0
	 */
	public function testProvidedArgumentsAppearInPromptText()
	{
		$prompts = convertkit_get_prompts();

		$addForm = $prompts['kit/add-form']->execute_callback(
			[
				'form'  => 'Weekly Newsletter',
				'scope' => 'category',
			]
		);
		$this->assertStringContainsString('Weekly Newsletter', $addForm['text']);
		$this->assertStringContainsString('Requested scope: category', $addForm['text']);

		$restrict = $prompts['kit/restrict-content']->execute_callback(
			[
				'post_id' => '12345',
				'gate'    => 'Premium Membership',
			]
		);
		$this->assertStringContainsString('12345', $restrict['text']);
		$this->assertStringContainsString('Premium Membership', $restrict['text']);
	}
}
