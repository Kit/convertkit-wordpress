<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for Plugin permission checks.
 *
 * @since   3.3.9
 */
class PermissionsTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Settings class.
	 *
	 * @since   3.3.9
	 *
	 * @var     \ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.3.9
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin, to include the Plugin's constants in tests.
		activate_plugins( 'convertkit/wp-convertkit.php' );

		// Store credentials in Plugin settings.
		$this->settings = new \ConvertKit_Settings();
		$this->settings->save(
			array(
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
				'token_expires' => ( time() + 10000 ),
			)
		);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.3.9
	 */
	public function tearDown(): void
	{
		// Delete credentials from Plugin settings.
		$this->settings->delete_credentials();

		// Reset request data used by admin request handlers.
		$_POST    = array();
		$_REQUEST = array();

		parent::tearDown();
	}

	/**
	 * Test that users must be able to create and publish the requested Post Type.
	 *
	 * @since   3.3.9
	 */
	public function testUserCanCreatePublishedPostType()
	{
		// Administrators and Editors can create and publish Pages.
		$this->actAs( 'administrator' );
		$this->assertTrue( convertkit_user_can_create_published_post_type( 'page' ) );

		$this->actAs( 'editor' );
		$this->assertTrue( convertkit_user_can_create_published_post_type( 'page' ) );

		// Authors can create and publish Posts, but not Pages.
		$this->actAs( 'author' );
		$this->assertTrue( convertkit_user_can_create_published_post_type( 'post' ) );
		$this->assertFalse( convertkit_user_can_create_published_post_type( 'page' ) );

		// Contributors cannot publish Posts.
		$this->actAs( 'contributor' );
		$this->assertFalse( convertkit_user_can_create_published_post_type( 'post' ) );

		// Unknown Post Types cannot be created.
		$this->assertFalse( convertkit_user_can_create_published_post_type( 'convertkit_unknown' ) );
	}

	/**
	 * Test that users unable to publish Pages are not offered content creation buttons.
	 *
	 * @since   3.3.9
	 */
	public function testListTableButtonsNotRegisteredWhenUserCannotPublishPages()
	{
		// Create and become an Author, who cannot create or publish Pages.
		$this->actAs( 'author' );

		// Confirm no content creation buttons are registered.
		$landing_page = new \ConvertKit_Admin_Landing_Page();
		$this->assertSame( array(), $landing_page->register_add_new_button( array(), 'page' ) );

		$restrict_content = new \ConvertKit_Admin_Restrict_Content();
		$this->assertSame( array(), $restrict_content->register_add_new_button( array(), 'page' ) );
	}

	/**
	 * Test that only Administrators can access the Plugin setup wizard.
	 *
	 * @since   3.3.9
	 */
	public function testPluginSetupWizardRequiresManageOptionsCapability()
	{
		$wizard = new \ConvertKit_Admin_Setup_Wizard_Plugin();

		// Editors cannot access the Plugin setup wizard.
		$this->actAs( 'editor' );
		$this->assertFalse( $wizard->user_has_access() );

		// Administrators can access the Plugin setup wizard.
		$this->actAs( 'administrator' );
		$this->assertTrue( $wizard->user_has_access() );
	}

	/**
	 * Test that Post settings cannot be saved for a Post the user cannot edit.
	 *
	 * @since   3.3.9
	 */
	public function testPostSettingsNotSavedWhenUserCannotEditPost()
	{
		// Create a Post owned by an Administrator.
		$administrator_id = static::factory()->user->create( array( 'role' => 'administrator' ) );
		$post_id          = static::factory()->post->create(
			array(
				'post_author' => $administrator_id,
			)
		);

		// Become an Author, who cannot edit another user's Post.
		$this->actAs( 'author' );
		$_POST = array(
			'wp-convertkit-save-meta-nonce' => wp_create_nonce( 'wp-convertkit-save-meta' ),
			'wp-convertkit'                 => array(
				'form' => '123',
			),
		);

		// Attempt to save the Post's settings.
		( new \ConvertKit_Admin_Post() )->save_post_meta( $post_id );

		// Confirm no settings were saved.
		$this->assertSame( '', get_post_meta( $post_id, '_wp_convertkit_post_meta', true ) );
	}

	/**
	 * Test that Category settings cannot be saved when the user cannot edit the Category.
	 *
	 * @since   3.3.9
	 */
	public function testCategorySettingsNotSavedWhenUserCannotEditCategory()
	{
		// Create a Category.
		$term = static::factory()->term->create_and_get(
			array(
				'taxonomy' => 'category',
			)
		);

		// Become an Author, who cannot manage Categories.
		$this->actAs( 'author' );
		$_POST = array(
			'wp-convertkit-save-meta-nonce' => wp_create_nonce( 'wp-convertkit-save-meta' ),
			'wp-convertkit'                 => array(
				'form' => '123',
			),
		);

		// Attempt to save the Category's settings.
		( new \ConvertKit_Admin_Category() )->save_category_fields( $term->term_id );

		// Confirm no settings were saved.
		$this->assertSame( '', get_term_meta( $term->term_id, '_wp_convertkit_term_meta', true ) );
	}

	/**
	 * Test that exporting a Post to a Broadcast stops when the user cannot edit the Post.
	 *
	 * @since   3.3.9
	 */
	public function testBroadcastExportStopsWhenUserCannotEditPost()
	{
		// Enable Broadcast exports.
		$broadcasts_settings = new \ConvertKit_Settings_Broadcasts();
		$broadcasts_settings->save( array( 'enabled_export' => 'on' ) );

		// Create a Post owned by an Administrator.
		$administrator_id = static::factory()->user->create( array( 'role' => 'administrator' ) );
		$post_id          = static::factory()->post->create(
			array(
				'post_author' => $administrator_id,
			)
		);

		// Become an Author, who cannot edit the Administrator's Post.
		$this->actAs( 'author' );
		$_REQUEST = array(
			'nonce'              => wp_create_nonce( 'action-convertkit-broadcast-export' ),
			'convertkit-action' => 'broadcast-export',
			'id'                 => $post_id,
		);

		// Confirm the export stops before a Broadcast can be created.
		$this->expectException( \WPDieException::class );
		$this->expectExceptionMessage( 'Sorry, you are not allowed to export this Post.' );
		( new \ConvertKit_Broadcasts_Exporter() )->run_row_action();
	}

	/**
	 * Creates a User with the given role and makes them the current User.
	 *
	 * @since   3.3.9
	 *
	 * @param   string $role   User role.
	 */
	private function actAs( $role )
	{
		$user_id = static::factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
	}
}
