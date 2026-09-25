<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests the Form setting on WordPress Pages, Posts and Custom Post Types when using the Classic Editor.
 *
 * @since   3.3.0
 */
class ClassicEditorFormCest
{
	/**
	 * Post Types to test.
	 *
	 * @since   3.3.0
	 *
	 * @var array
	 */
	private $postTypes = [
		'page',
		'post',
		'article',
	];

	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);

		// Activate Classic Editor Plugin.
		$I->activateThirdPartyPlugin($I, 'classic-editor');

		// Create Custom Post Types using the Custom Post Type UI Plugin.
		$I->registerCustomPostTypes($I);
	}

	/**
	 * Test that the Pages > Add New screen has expected a11y output, such as label[for].
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAccessibility(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Navigate to Post Type (e.g. Pages / Posts) > Add New.
			$I->amOnAdminPage('post-new.php?post_type=' . $postType);

			// Confirm that settings have label[for] attributes.
			$I->waitForElementVisible('label[for="wp-convertkit-form"]');
			$I->waitForElementVisible('label[for="wp-convertkit-tag"]');
			$I->waitForElementVisible('label[for="wp-convertkit-restrict_content"]');

			// For Pages, confirm that the Landing Page setting label is correct.
			// This isn't supported for Posts and Articles.
			if ( 'page' === $postType ) {
				$I->waitForElementVisible('label[for="wp-convertkit-landing_page"]');
			}
		}
	}

	/**
	 * Test that UTM parameters are included in links displayed in the metabox for the user to sign in to
	 * their Kit account.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testUTMParametersExist(EndToEndTester $I)
	{
		// Setup Kit plugin with no credentials or data.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Navigate to Pages > Add New.
		$I->amOnAdminPage('post-new.php?post_type=page');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the metabox is displayed.
		$I->seeElementInDOM('#wp-convertkit-meta-box');

		// Confirm that UTM parameters exist for the 'sign in to Kit' link.
		$I->seeInSource('<a href="https://app.kit.com/?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank">sign in to Kit</a>');
	}

	/**
	 * Test that the 'Default' option for the Default Form setting in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, Post or Article, and there is no Default Form specified in the Plugin
	 * settings.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostTypeUsingDefaultFormWithNoDefaultFormSpecifiedInPlugin(EndToEndTester $I)
	{
		// Setup Kit plugin with no default Forms configured.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Post Type using the Classic Editor.
			$I->addClassicEditorPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default: None'
			);

			// Publish and view the Post Type on the frontend site.
			$I->publishAndViewClassicEditorPage($I);

			// Confirm that no Kit Form is displayed.
			$I->dontSeeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, Post or Article.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostTypeUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Post Type using the Classic Editor.
			$I->addClassicEditorPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default'
			);

			// Publish and view the Post Type on the frontend site.
			$I->publishAndViewClassicEditorPage($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				isShortcode: true
			);
		}
	}



	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, Post or Article, and its position is set
	 * to after the 3rd paragraph.
	 *
	 * @since   2.6.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostTypeUsingDefaultFormAfterParagraphElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages, Posts and Articles set to be output after the 3rd paragraph of Post Type content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                           => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'                  => 'after_element',
				'page_form_position_element'          => 'p',
				'page_form_position_element_index'    => 3,
				'post_form'                           => $_ENV['CONVERTKIT_API_FORM_ID'],
				'post_form_position'                  => 'after_element',
				'post_form_position_element'          => 'p',
				'post_form_position_element_index'    => 3,
				'article_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'article_form_position'               => 'after_element',
				'article_form_position_element'       => 'p',
				'article_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Setup Post Type with placeholder content.
			$pageID = $I->addClassicEditorPageToDatabase(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default: After 3rd Paragraph Element'
			);

			// View the Post Type on the frontend site.
			$I->amOnPage('?p=' . $pageID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM after the third paragraph.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				position: 'after_element',
				element: 'p',
				elementIndex: 3,
				isShortcode: true
			);

			// Confirm character encoding is not broken due to using DOMDocument.
			$I->seeInSource('Adhaésionés altéram improbis mi pariendarum sit stulti triarium');

			// Confirm no meta tag exists within the content.
			$I->dontSeeInSource('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');

			// Confirm no extra <html>, <head> or <body> tags are output i.e. injecting the form doesn't result in DOMDocument adding tags.
			$I->seeNoExtraHtmlHeadBodyTagsOutput($I);
		}
	}





	/**
	 * Test that the Default Legacy Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, Post or Article.
	 *
	 * @since   1.9.6.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostTypeUsingDefaultLegacyForm(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'      => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'   => $_ENV['CONVERTKIT_API_SECRET'],
				'page_form'    => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
				'post_form'    => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
				'article_form' => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Post Type using the Classic Editor.
			$I->addClassicEditorPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Legacy: Default'
			);

			// Publish and view the Post Type on the frontend site.
			$I->publishAndViewClassicEditorPage($I);

			// Confirm that the Kit Default Legacy Form displays.
			$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');

			// Confirm that the Legacy Form title's character encoding is correct.
			$I->seeInSource('Vantar þinn ungling sjálfstraust í stærðfræði?');
		}
	}

	/**
	 * Test that 'None' Form specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, Post or Article.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostTypeUsingNoForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Post Type using the Classic Editor.
			$I->addClassicEditorPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: None'
			);

			// Configure metabox's Form setting = None.
			$I->configureMetaboxSettings(
				$I,
				metabox: 'wp-convertkit-meta-box',
				configuration: [
					'form' => [ 'select2', 'None' ],
				]
			);

			// Publish and view the Post Type on the frontend site.
			$I->publishAndViewClassicEditorPage($I);

			// Confirm that no Kit Form is displayed.
			$I->dontSeeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that the Form specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, Post or Article.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostTypeUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Post Type using the Classic Editor.
			$I->addClassicEditorPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME']
			);

			// Configure metabox's Form setting = Inline Form.
			$I->configureMetaboxSettings(
				$I,
				metabox: 'wp-convertkit-meta-box',
				configuration: [
					'form' => [ 'select2', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
				]
			);

			// Publish and view the Post Type on the frontend site.
			$I->publishAndViewClassicEditorPage($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				isShortcode: true
			);
		}
	}








	/**
	 * Test that the Default Form for Pages displays when an invalid Form ID is specified
	 * for a WordPress Page, Post or Article.
	 *
	 * Whilst the on screen options won't permit selecting an invalid Form ID, a Page might
	 * have an invalid Form ID because:
	 * - the form belongs to another Kit account (i.e. API credentials were changed in the Plugin, but this Page's specified Form was not changed)
	 * - the form was deleted from the Kit account.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostTypeUsingInvalidDefinedForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Create Post Type, with an invalid Form ID, as if it were created prior to API credentials being changed and/or
			// a Form being deleted in Kit.
			$pageID = $I->havePostInDatabase(
				[
					'post_type'  => $postType,
					'post_title' => 'Kit: ' . $postType . ': Form: Specific: Invalid',
					'meta_input' => [
						'_wp_convertkit_post_meta' => [
							'form'         => '11111',
							'landing_page' => '',
							'tag'          => '',
						],
					],
				]
			);

			// Load the Post Type on the frontend site.
			$I->amOnPage('/?p=' . $pageID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that the invalid Kit Form does not display.
			$I->dontSeeElementInDOM('form[data-sv-form="11111"]');

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				isShortcode: true
			);
		}
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
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->unregisterCustomPostTypes($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
