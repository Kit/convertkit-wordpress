<?php
/**
 * Tests for Kit Forms on WordPress Posts.
 *
 * @since   1.9.6
 */
class PostFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that the Posts > Add New screen has expected a11y output, such as label[for].
	 *
	 * @since   1.9.7.6
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAccessibility(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to Post Type (e.g. Pages / Posts) > Add New.
		$I->amOnAdminPage('post-new.php?post_type=post');

		// Confirm that settings have label[for] attributes.
		$I->seeInSource('<label for="wp-convertkit-form">');
		$I->seeInSource('<label for="wp-convertkit-tag">');
	}

	/**
	 * Test that the 'Default' option for the Default Form setting in the Plugin Settings works when
	 * creating and viewing a new WordPress Post, and there is no Default Form specified in the Plugin
	 * settings.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultFormWithNoDefaultFormSpecifiedInPlugin(AcceptanceTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Form: Default: None');

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
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Post.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultForm(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Form: Default');

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Post, and its position is set
	 * to after the Post content.
	 *
	 * @since   2.5.8
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultFormBeforeContent(AcceptanceTester $I)
	{
		// Setup Kit plugin with Default Form for Posts set to be output before the Post content.
		$I->setupKitPlugin(
			$I,
			[
				'post_form_position' => 'before_content',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Form: Default: Before Content');

		// Add paragraph to Post.
		$I->addGutenbergParagraphBlock($I, 'Post content');

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM after the Post content.
		// This confirms that there is only one script on the post for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID'], 'before_content');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Post, and its position is set
	 * to before and after the Post content.
	 *
	 * @since   2.5.9
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultFormBeforeAndAfterContent(AcceptanceTester $I)
	{
		// Setup Kit plugin with Default Form for Pages set to be output before and after the Post content.
		$I->setupKitPlugin(
			$I,
			[
				'post_form_position' => 'before_after_content',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Form: Default: Before and After Content');

		// Add paragraph to Post.
		$I->addGutenbergParagraphBlock($I, 'Post content');

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that two Kit Forms are output in the DOM before and after the Post content.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID'], 'before_after_content');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Post, and its position is set
	 * to after the 3rd paragraph.
	 *
	 * @since   2.6.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultFormAfterParagraphElement(AcceptanceTester $I)
	{
		// Setup Kit plugin with Default Form for Posts set to be output after the 3rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'post_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'p',
				'post_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Post with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase($I, 'post', 'Kit: Post: Form: Default: After 3rd Paragraph Element');

		// View the Post on the frontend site.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM after the third paragraph.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID'], 'after_element', 'p', 3);

		// Confirm character encoding is not broken due to using DOMDocument.
		$I->seeInSource('Adhaésionés altéram improbis mi pariendarum sit stulti triarium');

		// Confirm no meta tag exists within the content.
		$I->dontSeeInSource('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
	}

	/**
	 * Test that specifying a non-inline Form specified in the Plugin Settings does not
	 * result in a fatal error when creating and viewing a new WordPress Post, and its position is set
	 * to after the 3rd paragraph.
	 *
	 * @since   2.6.8
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultNonInlineFormAfterParagraphElement(AcceptanceTester $I)
	{
		// Setup Kit plugin with Default Form for Posts set to be output after the 3rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'post_form'                        => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'p',
				'post_form_position_element_index' => 3,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Post with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase($I, 'post', 'Kit: Post: Non-Inline Form: Default: After 3rd Paragraph Element');

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
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Post, and its position is set
	 * to after the 2nd <h2> element.
	 *
	 * @since   2.6.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultFormAfterHeadingElement(AcceptanceTester $I)
	{
		// Setup Kit plugin with Default Form for Posts set to be output after the 2nd <h2> of content.
		$I->setupKitPlugin(
			$I,
			[
				'post_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'h2',
				'post_form_position_element_index' => 2,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Post with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase($I, 'post', 'Kit: Post: Form: Default: After 2nd H2 Element');

		// View the Post on the frontend site.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM after the second <h2> element.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID'], 'after_element', 'h2', 2);

		// Confirm character encoding is not broken due to using DOMDocument.
		$I->seeInSource('Adhaésionés altéram improbis mi pariendarum sit stulti triarium');

		// Confirm no meta tag exists within the content.
		$I->dontSeeInSource('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Post, and its position is set
	 * to after the 2nd <img> element.
	 *
	 * @since   2.6.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultFormAfterImageElement(AcceptanceTester $I)
	{
		// Setup Kit plugin with Default Form for Posts set to be output after the 2nd <img> of content.
		$I->setupKitPlugin(
			$I,
			[
				'post_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'img',
				'post_form_position_element_index' => 2,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Post with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase($I, 'post', 'Kit: Post: Form: Default: After 2nd Image Element');

		// View the Post on the frontend site.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM after the second <img> element.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID'], 'after_element', 'img', 2);

		// Confirm character encoding is not broken due to using DOMDocument.
		$I->seeInSource('Adhaésionés altéram improbis mi pariendarum sit stulti triarium');

		// Confirm no meta tag exists within the content.
		$I->dontSeeInSource('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Post, and its position is set
	 * to a number greater than the number of elements in the content.
	 *
	 * @since   2.6.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultFormAfterOutOfBoundsElement(AcceptanceTester $I)
	{
		// Setup Kit plugin with Default Form for Posts set to be output after the 7rd paragraph of content.
		$I->setupKitPlugin(
			$I,
			[
				'post_form'                        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'post_form_position'               => 'after_element',
				'post_form_position_element'       => 'p',
				'post_form_position_element_index' => 9,
			]
		);
		$I->setupKitPluginResources($I);

		// Setup Post with placeholder content.
		$pageID = $I->addGutenbergPageToDatabase($I, 'post', 'Kit: Post: Form: Default: After 9th Paragraph Element');

		// View the Post on the frontend site.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM after the content, as
		// the number of paragraphs is less than the position.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID'], 'after_content');

		// Confirm character encoding is not broken due to using DOMDocument.
		$I->seeInSource('Adhaésionés altéram improbis mi pariendarum sit stulti triarium');

		// Confirm no meta tag exists within the content.
		$I->dontSeeInSource('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
	}

	/**
	 * Test that the Default Legacy Form specified in the Plugin Settings works when
	 * creating and viewing a new WordPress Post.
	 *
	 * @since   1.9.6.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefaultLegacyForm(AcceptanceTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'post_form'  => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Form: Legacy: Default');

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'Default' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Kit Default Legacy Form displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test that 'None' Form specified in the Post Settings works when
	 * creating and viewing a new WordPress Post.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingNoForm(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Form: None');

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Form specified in the Post Settings works when
	 * creating and viewing a new WordPress Post.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefinedForm(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Legacy Form specified in the Post Settings works when
	 * creating and viewing a new WordPress Post.
	 *
	 * @since   1.9.6.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingDefinedLegacyForm(AcceptanceTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'post_form'  => '',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME']);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Kit Legacy Form displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test that the Default Form for Posts displays when an invalid Form ID is specified
	 * for a Post.
	 *
	 * Whilst the on screen options won't permit selecting an invalid Form ID, a Post might
	 * have an invalid Form ID because:
	 * - the form belongs to another Kit account (i.e. API credentials were changed in the Plugin, but this Post's specified Form was not changed)
	 * - the form was deleted from the Kit account.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAddNewPostUsingInvalidDefinedForm(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Post, with an invalid Form ID, as if it were created prior to API credentials being changed and/or
		// a Form being deleted in Kit.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Post: Form: Specific: Invalid',
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'         => '11111',
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Load the Post on the frontend site.
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
	 * @since   1.9.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testQuickEditUsingDefaultForm(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Post: Form: Default: Quick Edit',
			]
		);

		// Quick Edit the Post in the Posts WP_List_Table.
		$I->quickEdit(
			$I,
			'post',
			$postID,
			[
				'form' => [ 'select', 'Default' ],
			]
		);

		// Load the Post on the frontend site.
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
	 * @since   1.9.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testQuickEditUsingDefinedForm(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Quick Edit',
			]
		);

		// Quick Edit the Post in the Posts WP_List_Table.
		$I->quickEdit(
			$I,
			'post',
			$postID,
			[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Load the Post on the frontend site.
		$I->amOnPage('/?p=' . $postID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Default Form for Posts displays when the Default option is chosen via
	 * WordPress' Bulk Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testBulkEditUsingDefaultForm(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create two Posts.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: Default: Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: Default: Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Posts in the Posts WP_List_Table.
		$I->bulkEdit(
			$I,
			'post',
			$postIDs,
			[
				'form' => [ 'select', 'Default' ],
			]
		);

		// Iterate through Posts to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load Post on the frontend site.
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
	 * @since   1.9.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testBulkEditUsingDefinedForm(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create two Posts.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Posts in the Posts WP_List_Table.
		$I->bulkEdit(
			$I,
			'post',
			$postIDs,
			[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Iterate through Posts to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load Post on the frontend site.
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
	 * @since   1.9.8.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testBulkEditWithNoChanges(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create two Posts with a defined form.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #1',
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
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #2',
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

		// Bulk Edit the Posts in the Posts WP_List_Table.
		$I->bulkEdit(
			$I,
			'post',
			$postIDs,
			[
				'form' => [ 'select', '— No Change —' ],
			]
		);

		// Iterate through Posts to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load Post on the frontend site.
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
	 * @since   1.9.8.1
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testBulkEditFieldsHiddenWhenNoPostsFound(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Emulate the user searching for Posts with a query string that yields no results.
		$I->amOnAdminPage('edit.php?post_type=post&s=nothing');

		// Confirm that the Bulk Edit fields do not display.
		$I->dontSeeElement('#convertkit-bulk-edit');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
