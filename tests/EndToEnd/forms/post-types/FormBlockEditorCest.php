<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests the Form setting on WordPress Pages, Posts and Custom Post Types when using the block editor.
 *
 * @since   3.2.2
 */
class FormBlockEditorCest
{
	/**
	 * Post Types to test.
	 *
	 * @since   3.2.2
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
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);

		// Create Custom Post Types using the Custom Post Type UI Plugin.
		$I->registerCustomPostTypes($I);
	}

	/**
	 * Test that the 'Default' option for the Default Form setting in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and there is no Default Form specified in the Plugin
	 * settings.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultFormWithNoDefaultFormSpecifiedInPlugin(EndToEndTester $I)
	{
		// Setup Kit plugin with no default Forms configured.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Page using the Gutenberg editor.
			$I->addGutenbergPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default: None'
			);

			// Publish and view the Page on the frontend site.
			$I->publishAndViewGutenbergPage($I);

			// Confirm that no Kit Form is displayed.
			$I->dontSeeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Page using the Gutenberg editor.
			$I->addGutenbergPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default'
			);

			// Publish and view the Page on the frontend site.
			$I->publishAndViewGutenbergPage($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to after the Page content.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultFormBeforeContent(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output before the Page content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form_position' => 'before_content',
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Page using the Gutenberg editor.
			$I->addGutenbergPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default: Before Content'
			);

			// Add paragraph to Page.
			$I->addGutenbergParagraphBlock($I, $postType . ' content');

			// Publish and view the Page on the frontend site.
			$I->publishAndViewGutenbergPage($I);

			// Confirm that one Kit Form is output in the DOM after the Page content.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				position: 'before_content'
			);
		}
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to before and after the Page content.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultFormBeforeAndAfterContent(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output before and after the Page content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form_position' => 'before_after_content',
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Page using the Gutenberg editor.
			$I->addGutenbergPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default: Before and After Content'
			);

			// Add paragraph to Page.
			$I->addGutenbergParagraphBlock($I, $postType . ' content');

			// Publish and view the Page on the frontend site.
			$I->publishAndViewGutenbergPage($I);

			// Confirm that two Kit Forms are output in the DOM before and after the Page content.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				position: 'before_after_content'
			);
		}
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to after the 3rd paragraph.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultFormAfterParagraphElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output after the 3rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'p',
				'page_form_position_element_index' => 3,
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'p',
				'post_form_position_element_index' => 3,
				'article_form_position'            => 'after_element',
				'article_form_position_element'    => 'p',
				'article_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Setup Page with placeholder content.
			$pageID = $I->addGutenbergPageToDatabase(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default: After 3rd Paragraph Element'
			);

			// View the Page on the frontend site.
			$I->amOnPage('?p=' . $pageID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM after the third paragraph.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				position: 'after_element',
				element: 'p',
				elementIndex: 3
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
	 * Test that specifying a non-inline Form specified in the Plugin Settings does not
	 * result in a fatal error when creating and viewing a new WordPress Page, and its position is set
	 * to after the 3rd paragraph.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultNonInlineFormAfterParagraphElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output after the 3rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'p',
				'page_form_position_element_index' => 3,
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'p',
				'post_form_position_element_index' => 3,
				'article_form_position'            => 'after_element',
				'article_form_position_element'    => 'p',
				'article_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Setup Page with placeholder content.
			$pageID = $I->addGutenbergPageToDatabase(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Non-Inline Form: Default: After 3rd Paragraph Element'
			);

			// View the Page on the frontend site.
			$I->amOnPage('?p=' . $pageID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]', 1);

			// Confirm character encoding is not broken due to using DOMDocument.
			$I->seeInSource('Adhaésionés altéram improbis mi pariendarum sit stulti triarium');

			// Confirm no meta tag exists within the content.
			$I->dontSeeInSource('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');

			// Confirm no extra <html>, <head> or <body> tags are output i.e. injecting the form doesn't result in DOMDocument adding tags.
			$I->seeNoExtraHtmlHeadBodyTagsOutput($I);
		}
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to after the 2nd <h2> element.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultFormAfterHeadingElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output after the 2nd <h2> of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'h2',
				'page_form_position_element_index' => 2,
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'h2',
				'post_form_position_element_index' => 2,
				'article_form_position'            => 'after_element',
				'article_form_position_element'    => 'h2',
				'article_form_position_element_index' => 2,
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Setup Page with placeholder content.
			$pageID = $I->addGutenbergPageToDatabase(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default: After 2nd H2 Element'
			);

			// View the Page on the frontend site.
			$I->amOnPage('?p=' . $pageID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM after the second <h2> element.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				position: 'after_element',
				element: 'h2',
				elementIndex: 2
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
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to after the 2nd <img> element.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultFormAfterImageElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Posts set to be output after the 2nd <img> of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'img',
				'page_form_position_element_index' => 2,
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'img',
				'post_form_position_element_index' => 2,
				'article_form_position'            => 'after_element',
				'article_form_position_element'    => 'img',
				'article_form_position_element_index' => 2,
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Setup Page with placeholder content.
			$pageID = $I->addGutenbergPageToDatabase(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Default: After 2nd Image Element'
			);

			// View the Post on the frontend site.
			$I->amOnPage('?p=' . $pageID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM after the second <img> element.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				position: 'after_element',
				element: 'img',
				elementIndex: 2
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
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to a number greater than the number of elements in the content.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultFormAfterOutOfBoundsElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output after the 7rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'p',
				'page_form_position_element_index' => 9,
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'p',
				'post_form_position_element_index' => 9,
				'article_form_position'            => 'after_element',
				'article_form_position_element'    => 'p',
				'article_form_position_element_index' => 9,
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Setup Page with placeholder content.
			$pageID = $I->addGutenbergPageToDatabase(
				$I,
				title: 'Kit: Page: Form: Default: After 9th Paragraph Element'
			);

			// View the Page on the frontend site.
			$I->amOnPage('?p=' . $pageID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM after the content, as
			// the number of paragraphs is less than the position.
			$I->seeFormOutput(
				$I,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				position: 'after_content'
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
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefaultLegacyForm(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'page_form'  => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
				'post_form'  => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
				'article_form'  => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Page using the Gutenberg editor.
			$I->addGutenbergPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Legacy: Default'
			);

			// Publish and view the Page on the frontend site.
			$I->publishAndViewGutenbergPage($I);

			// Confirm that the Kit Default Legacy Form displays.
			$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');

			// Confirm that the Legacy Form title's character encoding is correct.
			$I->seeInSource('Vantar þinn ungling sjálfstraust í stærðfræði?');
		}
	}

	/**
	 * Test that 'None' Form specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingNoForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Page using the Gutenberg editor.
			$I->addGutenbergPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: None'
			);

			// Configure Plugin Sidebar's Form setting = None.
			$I->configurePluginSidebarSettings(
				$I,
				form: 'None'
			);

			// Publish and view the Page on the frontend site.
			$I->publishAndViewGutenbergPage($I);

			// Confirm that no Kit Form is displayed.
			$I->dontSeeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that the Form specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Page using the Gutenberg editor.
			$I->addGutenbergPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME']
			);

			// Configure Plugin Sidebar's Form setting = Inline Form.
			$I->configurePluginSidebarSettings(
				$I,
				form: $_ENV['CONVERTKIT_API_FORM_NAME']
			);

			// Publish and view the Page on the frontend site.
			$I->publishAndViewGutenbergPage($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the Legacy Form specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewUsingDefinedLegacyForm(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'page_form'  => '',
				'post_form'  => '',
				'article_form'  => '',
			]
		);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Add a Page using the Gutenberg editor.
			$I->addGutenbergPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME']
			);

			// Configure Plugin Sidebar's Form setting = Legacy Form.
			$I->configurePluginSidebarSettings(
				$I,
				form: $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME']
			);

			// Publish and view the Page on the frontend site.
			$I->publishAndViewGutenbergPage($I);

			// Confirm that the Kit Legacy Form displays.
			$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');

			// Confirm that the Legacy Form title's character encoding is correct.
			$I->seeInSource('Vantar þinn ungling sjálfstraust í stærðfræði?');
		}
	}

	/**
	 * Test that the Form Settings are preserved when switching between the Classic Editor
	 * and Gutenberg.
	 *
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormSettingsPreservedWhenSwitchingEditors(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Activate Classic Editor Plugin.
			$I->activateThirdPartyPlugin($I, 'classic-editor');

			// Add a Page using the Classic Editor.
			$I->addClassicEditorPage(
				$I,
				postType: $postType,
				title: 'Kit: ' . $postType . ': Form: Editor Switching: ' . $_ENV['CONVERTKIT_API_FORM_NAME']
			);

			// Configure metabox's Form setting = Inline Form.
			$I->configureMetaboxSettings(
				$I,
				metabox: 'wp-convertkit-meta-box',
				configuration: [
					'form' => [ 'select2', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
				]
			);

			// Publish and view the Page on the frontend site.
			$I->publishAndViewClassicEditorPage($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);

			// Grab the edit page URL.
			$editPageURL = $I->grabAttributeFrom('#wp-admin-bar-edit a', 'href');

			// Deactivate Classic Editor Plugin.
			$I->deactivateThirdPartyPlugin($I, 'classic-editor');

			$I->wait(2);

			// Edit the page in the Gutenberg editor.
			$I->amOnUrl($editPageURL);

			// Confirm the Form setting is set to Inline Form.
			$I->seePluginSidebarSetting($I, 'form', $_ENV['CONVERTKIT_API_FORM_NAME']);

			// Add a paragraph, so the Save button can be used.
			$I->addGutenbergParagraphBlock($I, 'This is a test paragraph.');

			// Save (update) and view the Page on the frontend site.
			$I->saveAndViewGutenbergPage($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);

			// Activate Classic Editor Plugin.
			$I->activateThirdPartyPlugin($I, 'classic-editor');

			// Edit the page in the Classic Editor.
			$I->amOnUrl($editPageURL);

			// Add a paragraph, so the Save button can be used.
			$I->addClassicEditorParagraph($I, 'This is a test paragraph.');

			// Save (update) and view the Page on the frontend site.
			$I->publishAndViewClassicEditorPage($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);

			// Deactivate Classic Editor Plugin.
			$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		}
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->unregisterCustomPostTypes($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
