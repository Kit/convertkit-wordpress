<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to registering Custom Post Types in WordPress.
 *
 * @since   2.3.5
 */
class CustomPostType extends \Codeception\Module
{
	/**
	 * Registers a Custom Post Type in WordPress
	 *
	 * @since   2.3.5
	 *
	 * @param   EndToEndTester $I                   End To End Tester.
	 * @param   string         $slug              Post Type Slug.
	 * @param   string         $singularLabel     Singular Label.
	 * @param   string         $pluralLabel       Plural Label.
	 * @param   bool           $isPublicPostType  Public Post Type.
	 */
	public function registerCustomPostType($I, $slug, $singularLabel, $pluralLabel, $isPublicPostType = true)
	{
		// Activate CPT UI Plugin.
		$I->activateThirdPartyPlugin($I, 'custom-post-type-ui');

		// Navigate to the CPT UI Plugin screen.
		$I->amOnAdminPage('admin.php?page=cptui_manage_post_types');

		// Add the Post Type.
		$I->fillField('cpt_custom_post_type[name]', $slug);
		$I->fillField('cpt_custom_post_type[label]', $pluralLabel);
		$I->fillField('cpt_custom_post_type[singular_label]', $singularLabel);
		if ( ! $isPublicPostType) {
			$I->selectOption('cpt_custom_post_type[public]', 'False');
		}

		// Scroll to panel containing submit button.
		$I->scrollTo('#cptui_panel_pt_basic_settings');
		$I->click('Add Post Type');

		// Confirm the Post Type was created.
		$I->see($slug . ' has been successfully added');
	}

	/**
	 * Unregisters an existing Custom Post Type in WordPress
	 *
	 * @since   2.3.5
	 *
	 * @param   EndToEndTester $I             EndToEnd Tester.
	 * @param   string         $slug          Post Type Slug.
	 */
	public function unregisterCustomPostType($I, $slug)
	{
		// Navigate to the CPT UI Plugin screen.
		$I->amOnAdminPage('admin.php?page=cptui_manage_post_types&action=edit');

		// Select the Post Type.
		$I->selectOption('#post_type', $slug);

		// Click the Delete button.
		$I->click('Delete Post Type');

		// Confirm deletion.
		$I->waitForElementVisible('div.wp-dialog');
		$I->click('OK', 'div.wp-dialog');
	}
}
