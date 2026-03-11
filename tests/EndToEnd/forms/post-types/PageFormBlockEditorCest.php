<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests the Form setting on WordPress Pages when using the block editor.
 *
 * @since   3.2.2
 */
class PageFormBlockEditorCest
{
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
	public function testAddNewPageUsingDefaultFormWithNoDefaultFormSpecifiedInPlugin(EndToEndTester $I)
	{
		// Setup Kit plugin with no default Forms configured.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Default: None'
		);

		// Configure Plugin Sidebar's Form setting = Default.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'Default'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Default'
		);

		// Configure Plugin Sidebar's Form setting = Default.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'Default'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
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
	public function testAddNewPageUsingDefaultFormBeforeContent(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output before the Page content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form_position' => 'before_content',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Default: Before Content'
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Page content');

		// Configure Plugin Sidebar's Form setting = Default.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'Default'
		);

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

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to before and after the Page content.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefaultFormBeforeAndAfterContent(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output before and after the Page content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form_position' => 'before_after_content',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Default: Before and After Content'
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Page content');

		// Configure Plugin Sidebar's Form setting = Default.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'Default'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that two Kit Forms are output in the DOM before and after the Page content.
		$I->seeFormOutput(
			$I,
			formID: $_ENV['CONVERTKIT_API_FORM_ID'],
			position: 'before_after_content'
		);
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
	public function testAddNewPageUsingDefaultFormAfterParagraphElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output after the 3rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'p',
				'page_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Page with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			title: 'Kit: Page: Form: Default: After 3rd Paragraph Element'
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

	/**
	 * Test that specifying a non-inline Form specified in the Plugin Settings does not
	 * result in a fatal error when creating and viewing a new WordPress Page, and its position is set
	 * to after the 3rd paragraph.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefaultNonInlineFormAfterParagraphElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output after the 3rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'p',
				'page_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Page with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			title: 'Kit: Page: Non-Inline Form: Default: After 3rd Paragraph Element'
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

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to after the 2nd <h2> element.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefaultFormAfterHeadingElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output after the 2nd <h2> of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'h2',
				'page_form_position_element_index' => 2,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Page with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			title: 'Kit: Page: Form: Default: After 2nd H2 Element'
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

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to after the 2nd <img> element.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefaultFormAfterImageElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Posts set to be output after the 2nd <img> of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'img',
				'page_form_position_element_index' => 2,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Page with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			title: 'Kit: Page: Form: Default: After 2nd Image Element'
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

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page, and its position is set
	 * to a number greater than the number of elements in the content.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefaultFormAfterOutOfBoundsElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output after the 7rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'               => 'after_element',
				'page_form_position_element'       => 'p',
				'page_form_position_element_index' => 9,
			]
		);
		$I->setupKitPluginResources($I);

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

	/**
	 * Test that the Default Legacy Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefaultLegacyForm(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'page_form'  => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Legacy: Default'
		);

		// Configure Plugin Sidebar's Form setting = Default.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'Default'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Kit Default Legacy Form displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');

		// Confirm that the Legacy Form title's character encoding is correct.
		$I->seeInSource('Vantar þinn ungling sjálfstraust í stærðfræði?');
	}

	/**
	 * Test that 'None' Form specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingNoForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: None'
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

	/**
	 * Test that the Form specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME']
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

	/**
	 * Test that the Legacy Form specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   3.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedLegacyForm(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'page_form'  => '',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME']
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
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
