<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the 'Edit form in Kit' link when a Form is previewed.
 *
 * @since   2.0.8
 */
class EditFormLinkCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);

		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that the 'Edit form on Kit' link displays when a Form is specified
	 * in the Page Settings, and the user previews the WordPress Page.
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditFormLinkOnPage(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Form: Default: Edit Link');

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Edit Form link is displayed, because we did not preview the Page.
		$I->dontSee('Edit form in Kit');

		// View the Page as if we clicked Preview from the editor.
		$I->amOnUrl($_ENV['WORDPRESS_URL'] . $I->grabFromCurrentUrl() . '?preview=true');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Edit Form link is displayed.
		$I->seeInSource('<a href="https://app.kit.com/forms/designers/' . $_ENV['CONVERTKIT_API_FORM_ID'] . '/edit/?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank">Edit form in Kit</a>');
	}

	/**
	 * Test that the 'Edit form on Kit' link does not display when no
	 * Form is specified in the Page Settings, and the user previews the WordPress Page.
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditFormLinkOnPageWithNoForm(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Form: None: Edit Link');

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Edit Form link is displayed, because we did not preview the Page.
		$I->dontSee('Edit form in Kit');

		// View the Page as if we clicked Preview from the editor.
		$I->amOnUrl($_ENV['WORDPRESS_URL'] . $I->grabFromCurrentUrl() . '?preview=true');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Edit Form link is displayed, because there is no Form specified on this Page.
		$I->dontSee('Edit form in Kit');
	}

	/**
	 * Test that the 'Edit form on Kit' link does not display when an invalid
	 * Form is specified in the Page Settings, and the user previews the WordPress Page.
	 *
	 * Whilst the on screen options won't permit selecting an invalid Form ID, a Page might
	 * have an invalid Form ID because:
	 * - the form belongs to another Kit account (i.e. API credentials were changed in the Plugin, but this Page's specified Form was not changed)
	 * - the form was deleted from the Kit account.
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditFormLinkOnPageWithInvalidForm(EndToEndTester $I)
	{
		// Create Page, with an invalid Form ID, as if it were created prior to API credentials being changed and/or
		// a Form being deleted in Kit.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'  => 'page',
				'post_title' => 'Kit: Page: Form: Specific: Invalid: Edit Link',
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'         => '11111',
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Edit Form link is displayed, because we did not preview the Page.
		$I->dontSee('Edit form in Kit');

		// View the Page as if we clicked Preview from the editor.
		$I->amOnPage('/?p=' . $pageID . '&preview=true');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Edit Form link is displayed, because the form Form specified on this Page is invalid.
		$I->dontSee('Edit form in Kit');
	}

	/**
	 * Test that the 'Edit form on Kit' link displays when a Legacy Form is specified
	 * in the Page Settings, and the user previews the WordPress Page.
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditFormLinkOnPageWithLegacyForm(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Form: Legacy: Edit Link');

		// Configure metabox's Form setting = Legacy.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Edit Form link is displayed, because we did not preview the Page.
		$I->dontSee('Edit form in Kit');

		// View the Page as if we clicked Preview from the editor.
		$I->amOnUrl($_ENV['WORDPRESS_URL'] . $I->grabFromCurrentUrl() . '?preview=true');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Edit Form link is displayed.
		$I->seeInSource('<a href="https://app.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/edit/?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank">Edit form in Kit</a>');
	}

	/**
	 * Test that the 'Edit form on Kit' link displays when the Kit Form
	 * block exists in the Page, and the user previews the WordPress Page.
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditFormLinkOnPageWithFormBlock(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Form: Block: Edit Link');

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			'Kit Form',
			'convertkit-form',
			[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Edit Form link is displayed, because we did not preview the Page.
		$I->dontSee('Edit form in Kit');

		// View the Page as if we clicked Preview from the editor.
		$I->amOnUrl($_ENV['WORDPRESS_URL'] . $I->grabFromCurrentUrl() . '?preview=true');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Edit Form link is displayed.
		$I->seeInSource('<a href="https://app.kit.com/forms/designers/' . $_ENV['CONVERTKIT_API_FORM_ID'] . '/edit/?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank">Edit form in Kit</a>');
	}

	/**
	 * Test that the 'Edit form on Kit' link does not display when the Kit Form
	 * block exists in the Page and is configured to display a form format that is not inline
	 * (i.e. Modal, Sticky Bar or Slide In).
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditFormLinkOnPageWithFormBlockSpecifyingNonInlineForm(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Form: Block: Non Inline: Edit Link');

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			'Kit Form',
			'convertkit-form',
			[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Edit Form link is displayed, because we did not preview the Page.
		$I->dontSee('Edit form in Kit');

		// View the Page as if we clicked Preview from the editor.
		$I->amOnUrl($_ENV['WORDPRESS_URL'] . $I->grabFromCurrentUrl() . '?preview=true');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Edit Form link is displayed, because the form isn't an inline format.
		$I->dontSee('Edit form in Kit');
	}

	/**
	 * Test that the 'Edit form on Kit' link displays when the Kit Form
	 * shortcode exists in the Page, and the user previews the WordPress Page.
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditFormLinkOnPageWithFormShortcode(EndToEndTester $I)
	{
		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-edit-link',
				'post_content' => '[convertkit form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'         => '0', // Don't show the Plugin's default form for Pages.
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-edit-link');

		// Confirm that no Edit Form link is displayed, because we did not preview the Page.
		$I->dontSee('Edit form in Kit');

		// View the Page as if we clicked Preview from the editor.
		$I->amOnUrl($_ENV['WORDPRESS_URL'] . $I->grabFromCurrentUrl() . '?preview=true');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Edit Form link is displayed.
		$I->seeInSource('<a href="https://app.kit.com/forms/designers/' . $_ENV['CONVERTKIT_API_FORM_ID'] . '/edit/?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank">Edit form in Kit</a>');
	}

	/**
	 * Test that the 'Edit form on Kit' link does not display when the Kit Form
	 * shortcode exists in the Page and is configured to display a form format that is not inline
	 * (i.e. Modal, Sticky Bar or Slide In).
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditFormLinkOnPageWithFormShortcodeSpecifyingNonInlineForm(EndToEndTester $I)
	{
		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-non-inline-form-edit-link',
				'post_content' => '[convertkit form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]',
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'         => '0', // Don't show the Plugin's default form for Pages.
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-non-inline-form-edit-link');

		// Confirm that no Edit Form link is displayed, because we did not preview the Page.
		$I->dontSee('Edit form in Kit');

		// View the Page as if we clicked Preview from the editor.
		$I->amOnUrl($_ENV['WORDPRESS_URL'] . $I->grabFromCurrentUrl() . '?preview=true');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Edit Form link is displayed, because the form isn't an inline format.
		$I->dontSee('Edit form in Kit');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.0.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
