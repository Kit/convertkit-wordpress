<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Kit Landing Pages on WordPress Posts.
 *
 * @since   1.9.6.4
 */
class PostLandingPageCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);

		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that no Landing Page option is displayed in the Plugin Settings when
	 * creating and viewing a new WordPress Post, and that no attempt to check
	 * for a Landing Page is made when viewing a Post.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostDoesNotDisplayLandingPageOption(EndToEndTester $I)
	{
		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Landing Page');

		// Check that the metabox is displayed.
		$I->seeElementInDOM('#wp-convertkit-meta-box');

		// Check that no Landing Page option is displayed.
		$I->dontSeeElementInDOM('#wp-convertkit-landing_page');

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

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
