<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for WordPress Custom Post Types (CPTs).
 *
 * @since   2.3.5
 */
class CPTFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);

		// Setup Kit plugin .
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Custom Post Types using the Custom Post Type UI Plugin.
		$I->registerCustomPostTypes($I);
	}

	/**
	 * Test that the Articles > Add New screen has expected a11y output, such as label[for].
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAccessibility(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to Post Type (e.g. Pages / Posts) > Add New.
		$I->amOnAdminPage('post-new.php?post_type=article');

		// Confirm that settings have label[for] attributes.
		$I->seeInSource('<label for="wp-convertkit-form">');
		$I->seeInSource('<label for="wp-convertkit-tag">');
	}

	/**
	 * Tests that:
	 * - no Kit options are displayed when adding a new private Custom Post Type,
	 * - no debug output is displayed when viewing a private Custom Post Type.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoOptionsOrOutputOnPrivateCustomPostType(EndToEndTester $I)
	{
		// Add a Private CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'private',
			title: 'Kit: Private: Form: None'
		);

		// Check that the metabox is not displayed.
		$I->dontSeeElementInDOM('#wp-convertkit-meta-box');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');

		// Confirm that no debug data is output, as this isn't a supported Post Type.
		$I->dontSeeInSource('<!-- Kit append_form_to_content()');
	}

	/**
	 * Tests that no Kit options are display when quick or bulk editing in a private Custom Post Type.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoBulkOrQuickEditOptionsOnPrivateCustomPostType(EndToEndTester $I)
	{
		// Programmatically create two Private CPTs.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'private',
					'post_title' => 'Kit: Private: #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'private',
					'post_title' => 'Kit: Private: #2',
				]
			),
		);

		// Navigate to Private CPT.
		$I->amOnAdminPage('edit.php?post_type=private');

		// Confirm no Bulk or Quick Edit settings are available.
		$I->dontSeeElementInDOM('#convertkit-bulk-edit');
		$I->dontSeeElementInDOM('#convertkit-quick-edit');
	}

	/**
	 * Test that the 'Default' option for the Default Form setting in the Plugin Settings works when
	 * creating and viewing a new WordPress CPT, and there is no Default Form specified in the Plugin
	 * settings.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultFormWithNoDefaultFormSpecifiedInPlugin(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Default: None'
		);

		// Check the order of the Form resources are alphabetical, with the Default and None options prepending the Forms.
		$I->checkSelectFormOptionOrder(
			$I,
			'#wp-convertkit-form',
			[
				'Default',
				'None',
			]
		);

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the CPT on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress CPT.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Add a CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Default'
		);

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the CPT on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress CPT, and its position is set
	 * to after the CPT content.
	 *
	 * @since   2.5.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultFormBeforeContent(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for CPTs set to be output before the CPT content.
		$I->setupKitPlugin(
			$I,
			[
				'article_form'          => $_ENV['CONVERTKIT_API_FORM_ID'],
				'article_form_position' => 'before_content',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Default: Before Content'
		);

		// Add paragraph to CPT.
		$I->addGutenbergParagraphBlock($I, 'CPT content');

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the CPT on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM after the CPT content.
		// This confirms that there is only one script on the CPT for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID'], 'before_content');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress CPT, and its position is set
	 * to before and after the CPT content.
	 *
	 * @since   2.5.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultFormBeforeAndAfterContent(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for CPTs set to be output before and after the CPT content.
		$I->setupKitPlugin(
			$I,
			[
				'article_form'          => $_ENV['CONVERTKIT_API_FORM_ID'],
				'article_form_position' => 'before_after_content',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Default: Before and After Content'
		);

		// Add paragraph to CPT.
		$I->addGutenbergParagraphBlock($I, 'CPT content');

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the CPT on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that two Kit Forms are output in the DOM before and after the CPT content.
		$I->seeFormOutput(
			$I,
			formID: $_ENV['CONVERTKIT_API_FORM_ID'],
			position: 'before_after_content'
		);
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress CPT, and its position is set
	 * to after the 3rd paragraph.
	 *
	 * @since   2.6.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultFormAfterParagraphElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for CPTs set to be output after the 3rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'article_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'article_form_position'               => 'after_element',
				'article_form_position_element'       => 'p',
				'article_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Article with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Default: After 3rd Paragraph Element'
		);

		// View the CPT on the frontend site.
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
	 * result in a fatal error when creating and viewing a new WordPress CPT, and its position is set
	 * to after the 3rd paragraph.
	 *
	 * @since   2.6.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultNonInlineFormAfterParagraphElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for CPTs set to be output after the 3rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'article_form'                        => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				'article_form_position'               => 'after_element',
				'article_form_position_element'       => 'p',
				'article_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup CPT with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Non-Inline Form: Default: After 3rd Paragraph Element'
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
	 * creating and viewing a new WordPress CPT, and its position is set
	 * to after the 2nd <h2> element.
	 *
	 * @since   2.6.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultFormAfterHeadingElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for CPTs set to be output after the 2nd <h2> of content.
		$I->setupKitPlugin(
			$I,
			[
				'article_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'article_form_position'               => 'after_element',
				'article_form_position_element'       => 'h2',
				'article_form_position_element_index' => 2,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Article with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Default: After 2nd H2 Element'
		);

		// View the CPT on the frontend site.
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
	 * creating and viewing a new WordPress CPT, and its position is set
	 * to after the 2nd <img> element.
	 *
	 * @since   2.6.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultFormAfterImageElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for CPTs set to be output after the 2nd <img> of content.
		$I->setupKitPlugin(
			$I,
			[
				'article_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'article_form_position'               => 'after_element',
				'article_form_position_element'       => 'img',
				'article_form_position_element_index' => 2,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Article with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Default: After 2nd Image Element'
		);

		// View the CPT on the frontend site.
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
	 * creating and viewing a new WordPress CPT, and its position is set
	 * to a number greater than the number of elements in the content.
	 *
	 * @since   2.6.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultFormAfterOutOfBoundsElement(EndToEndTester $I)
	{
		// Setup Kit plugin with Default Form for CPTs set to be output after the 7th paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'article_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'article_form_position'               => 'after_element',
				'article_form_position_element'       => 'p',
				'article_form_position_element_index' => 9,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Article with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Default: After 9th Paragraph Element'
		);

		// View the CPT on the frontend site.
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
	 * creating and viewing a new WordPress CPT.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefaultLegacyForm(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'      => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'   => $_ENV['CONVERTKIT_API_SECRET'],
				'article_form' => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Add a CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: Legacy: Default'
		);

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the CPT on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Kit Default Legacy Form displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test that 'None' Form specified in the CPT Settings works when
	 * creating and viewing a new WordPress CPT.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingNoForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Add a CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: None'
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the CPT on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Form specified in the CPT Settings works when
	 * creating and viewing a new WordPress CPT.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Add a CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME']
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the CPT on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Legacy Form specified in the CPT Settings works when
	 * creating and viewing a new WordPress CPT.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingDefinedLegacyForm(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'      => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'   => $_ENV['CONVERTKIT_API_SECRET'],
				'article_form' => '',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a CPT using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'article',
			title: 'Kit: CPT: Form: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME']
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] ],
			]
		);

		// Publish and view the CPT on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Kit Legacy Form displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test that the Default Form for CPTs displays when an invalid Form ID is specified
	 * for a CPT.
	 *
	 * Whilst the on screen options won't permit selecting an invalid Form ID, a CPT might
	 * have an invalid Form ID because:
	 * - the form belongs to another Kit account (i.e. API credentials were changed in the Plugin, but this CPT's specified Form was not changed)
	 * - the form was deleted from the Kit account.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewCPTUsingInvalidDefinedForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Create CPT, with an invalid Form ID, as if it were created prior to API credentials being changed and/or
		// a Form being deleted in Kit.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'article',
				'post_title' => 'Kit: CPT: Form: Specific: Invalid',
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'         => '11111',
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Load the CPT on the frontend site.
		$I->amOnPage('/?p=' . $postID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the invalid Kit Form does not display.
		$I->dontSeeElementInDOM('form[data-sv-form="11111"]');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Default Form for Pages displays when the Default option is chosen via
	 * WordPress' Quick Edit functionality.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuickEditUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Programmatically create a CPT.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'article',
				'post_title' => 'Kit: CPT: Form: Default: Quick Edit',
			]
		);

		// Quick Edit the CPT in the CPTs WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'article',
			postID: $postID,
			configuration: [
				'form' => [ 'select', 'Default' ],
			]
		);

		// Load the CPT on the frontend site.
		$I->amOnPage('/?p=' . $postID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the defined form displays when chosen via
	 * WordPress' Quick Edit functionality.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuickEditUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Programmatically create a CPT.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'article',
				'post_title' => 'Kit: CPT: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Quick Edit',
			]
		);

		// Quick Edit the CPT in the CPTs WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'article',
			postID: $postID,
			configuration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Load the CPT on the frontend site.
		$I->amOnPage('/?p=' . $postID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Default Form for CPTs displays when the Default option is chosen via
	 * WordPress' Bulk Edit functionality.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Programmatically create two CPTs.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'article',
					'post_title' => 'Kit: CPT: Form: Default: Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'article',
					'post_title' => 'Kit: CPT: Form: Default: Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the CPTs in the CPTs WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'article',
			postIDs: $postIDs,
			configuration: [
				'form' => [ 'select', 'Default' ],
			]
		);

		// Iterate through CPTs to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load CPT on the frontend site.
			$I->amOnPage('/?p=' . $postID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the defined form displays when chosen via
	 * WordPress' Bulk Edit functionality.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Programmatically create two CPTs.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'article',
					'post_title' => 'Kit: CPT: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'article',
					'post_title' => 'Kit: CPT: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the CPTs in the CPTs WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'article',
			postIDs: $postIDs,
			configuration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Iterate through CPTs to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load CPT on the frontend site.
			$I->amOnPage('/?p=' . $postID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the existing settings are honored and not changed
	 * when the Bulk Edit options are set to 'No Change'.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditWithNoChanges(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Programmatically create two CPTs with a defined form.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'article',
					'post_title' => 'Kit: CPT: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #1',
					'meta_input' => [
						'_wp_convertkit_post_meta' => [
							'form'         => $_ENV['CONVERTKIT_API_FORM_ID'],
							'landing_page' => '',
							'tag'          => '',
						],
					],
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'article',
					'post_title' => 'Kit: CPT: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #2',
					'meta_input' => [
						'_wp_convertkit_post_meta' => [
							'form'         => $_ENV['CONVERTKIT_API_FORM_ID'],
							'landing_page' => '',
							'tag'          => '',
						],
					],
				]
			),
		);

		// Bulk Edit the CPTs in the CPTs WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'article',
			postIDs: $postIDs,
			configuration: [
				'form' => [ 'select', '— No Change —' ],
			]
		);

		// Iterate through CPTs to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load CPT on the frontend site.
			$I->amOnPage('/?p=' . $postID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the Bulk Edit fields do not display when a search on a WP_List_Table
	 * returns no results.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditFieldsHiddenWhenNoCPTsFound(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'article_form' => $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Emulate the user searching for CPTs with a query string that yields no results.
		$I->amOnAdminPage('edit.php?post_type=article&s=nothing');

		// Confirm that the Bulk Edit fields do not display.
		$I->dontSeeElement('#convertkit-bulk-edit');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.4.3.7
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
