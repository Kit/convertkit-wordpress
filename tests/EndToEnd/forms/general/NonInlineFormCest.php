<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for non-inline Kit Forms.
 *
 * @since   2.3.9
 */
class NonInlineFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that no forms are displayed for the Default Form (Site Wide) option
	 * when no non-inline forms exist.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testSettingsWhenNoNonInlineForms(EndToEndTester $I)
	{
		// Setup Plugin with Kit account that has no non-inline forms.
		$I->setupKitPluginCredentialsNoData($I);
	}

	/**
	 * Test that the defined default non-inline form displays site wide.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineForm(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form (Site Wide).
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array( $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] ),
			]
		);
		$I->setupKitPluginResources($I);

		// Create a Page in the database.
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Default Non Inline Global',
				'post_name'   => 'kit-default-non-inline-global',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// View Page.
		$I->amOnPage('/kit-default-non-inline-global');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// View a search page.
		$I->amOnPage('/?s=test');

		// Confirm that no Kit Form is output in the DOM.
		$I->dontSeeElementInDOM('form[data-sv-form]');

		// View a 404 page.
		$I->amOnPage('/non-existent-page');

		// Confirm that no Kit Form is output in the DOM.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the defined default non-inline forms displays site wide
	 * when more than one form is specified.
	 *
	 * @since   2.6.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineForms(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form (Site Wide).
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
					$_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				),
			]
		);
		$I->setupKitPluginResources($I);

		// Create a Page in the database.
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Default Non Inline Global Forms',
				'post_name'   => 'kit-default-non-inline-global-forms',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that two Kit Forms are output in the DOM.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]', 1);

		// View Page.
		$I->amOnPage('/kit-default-non-inline-global-forms');

		// Confirm that two Kit Forms are output in the DOM.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]', 1);

		// View a search page.
		$I->amOnPage('/?s=test');

		// Confirm that no Kit Form is output in the DOM.
		$I->dontSeeElementInDOM('form[data-sv-form]');

		// View a 404 page.
		$I->amOnPage('/non-existent-page');

		// Confirm that no Kit Form is output in the DOM.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that no non-inline form displays site wide when not selected in the Plugin's settings.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoDefaultNonInlineForm(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form (Site Wide).
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that no Kit Form is output in the DOM.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the non-inline form defined as the Default Form for Pages overrides
	 * the non-inline form defined in the Default Forms (Site Wide) setting
	 * when a Page is viewed.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormIgnoredWhenDefaultPageNonInlineFormDefined(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for both Pages and Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'page_form'       => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Non-Inline Form: Default');

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the modal form displays, and no sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]');
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the non-inline form defined on a Page overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Page is viewed.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormIgnoredWhenPageNonInlineFormDefined(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Non-Inline Form: Specific'
		);

		// Configure metabox's Form setting = Modal Form.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the modal form displays, and no sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]');
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the None option defined on a Page overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Page is viewed.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPageLevelNoneSettingIgnored(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Non-Inline Form: None: Ignored'
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the None option defined on a Page overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Page is viewed.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPageLevelNoneSettingHonored(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide,
		// and set to honor the None setting at Page / Post level.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form'                    => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
				'non_inline_form_honor_none_setting' => 'on',
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Non-Inline Form: None: Honored'
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no sticky bar form displays.
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the non-inline form output using the Form Block overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Page is viewed.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormIgnoredWhenPageNonInlineFormDefinedInBlock(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Non-Inline Form: Block'
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add Form block to the Page set to the Modal Form.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the modal form displays, and no sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]');
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the non-inline form output using the Form shortcode overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Page is viewed.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormIgnoredWhenPageNonInlineFormDefinedInShortcode(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Non-Inline Form: Shortcode'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the modal form displays, and no sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]');
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the non-inline form defined as the Default Form for Posts overrides
	 * the non-inline form defined in the Default Forms (Site Wide) setting
	 * when a Post is viewed.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormIgnoredWhenDefaultPostNonInlineFormDefined(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for both Posts and Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'post_form'       => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Non-Inline Form: Default'
		);

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the modal form displays, and no sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]');
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the non-inline form defined on a Post overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Post is viewed.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormIgnoredWhenPostNonInlineFormDefined(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Non-Inline Form: Specific'
		);

		// Configure metabox's Form setting = Modal Form.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the modal form displays, and no sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]');
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the non-inline form defined on a Category overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Post assigned to the Category is viewed.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormIgnoredWhenPostCategoryNonInlineFormDefined(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Create Category.
		$termID = $I->haveTermInDatabase(
			'Kit: Non Inline Form',
			'category',
			[
				'meta' => [
					'_wp_convertkit_term_meta' => [
						'form' => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
					],
				],
			]
		);
		$termID = $termID[0];

		// Create Post, assigned to Category.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Non Inline Form: Category',
				'tax_input'  => [
					[ 'category' => $termID ],
				],
			]
		);

		// Load the Post on the frontend site.
		$I->amOnPage('/?p=' . $postID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the modal form displays, and no sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]');
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the None option defined on a Post overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Post is viewed.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPostLevelNoneSettingIgnored(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Non-Inline Form: None: Ignored'
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the None option defined on a Post overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Post is viewed.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPostLevelNoneSettingHonored(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide,
		// and set to honor the None setting at Page / Post level.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form'                    => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
				'non_inline_form_honor_none_setting' => 'on',
			]
		);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Non-Inline Form: None: Honored'
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no sticky bar form displays.
		$I->dontSeeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	public function testNonInlineFormLimitPerSession(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide,
		// and set to limit the display of non-inline forms per session.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
				'non_inline_form_limit_per_session' => 'on',
			]
		);
		
		// Create a Page in the database that uses a different non-inline form.
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Non Inline Form: Limit Per Session',
				'post_name'   => 'kit-non-inline-form-limit-per-session',
				'post_type'   => 'page',
				'post_status' => 'publish',
				'meta'        => [
					'_wp_convertkit_post_meta' => [
						'form' => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
					],
				],
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// View Page.
		$I->amOnPage('/kit-non-inline-form-limit-per-session');
		die();

		// Confirm that no Kit Form is output in the DOM, and the cookie is set, because a non-inline form was output in the previous request.
		$I->dontSeeElementInDOM('form[data-sv-form]');
		$I->seeCookie('convertkit_non_inline_form_output');

		// View the home page.
		$I->amOnPage('/');

		// Confirm that no Kit Form is output in the DOM, and the cookie is set, because a non-inline form was output in the previous request.
		$I->dontSeeElementInDOM('form[data-sv-form]');
		$I->seeCookie('convertkit_non_inline_form_output');
	}

	/**
	 * Test that the defined default non-inline form displays site wide
	 * when stored as a string in the Plugin settings from older
	 * Plugin versions < 2.6.9.
	 *
	 * @since   2.6.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormOnUpgrade(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form (Site Wide).
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);
		$I->setupKitPluginResources($I);

		// Create a Page in the database.
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Default Non Inline Global Upgrade',
				'post_name'   => 'kit-default-non-inline-global-upgrade',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// View Page.
		$I->amOnPage('/kit-default-non-inline-global-upgrade');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
