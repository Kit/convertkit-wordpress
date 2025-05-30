<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form Trigger Gutenberg Block.
 *
 * @since   2.2.0
 */
class PageBlockFormTriggerCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
	}

	/**
	 * Test the Form Trigger block works when using a valid Form parameter.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockWithValidFormParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger: Valid Form Param'
		);

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
			blockName: 'Kit Form Trigger',
			blockProgrammaticName: 'convertkit-formtrigger',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe');
	}

	/**
	 * Test that multiple Form Trigger blocks work when using a valid Form parameter.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlocksWithValidFormParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger: Valid Form Param, Multiple Blocks'
		);

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
			blockName: 'Kit Form Trigger',
			blockProgrammaticName: 'convertkit-formtrigger',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Add the same block again.
		$I->addGutenbergBlock(
			$I,
			'Kit Form Trigger',
			'convertkit-formtrigger',
			[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]', 1);
	}

	/**
	 * Test the Form Trigger block works when not defining a Form parameter.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockWithNoFormParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger: No Form Param'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Trigger',
			blockProgrammaticName: 'convertkit-formtrigger'
		);

		// Confirm that the Form block displays instructions to the user on how to select a Form.
		$I->seeBlockHasNoContentMessage($I, 'Select a Form using the Form option in the Gutenberg sidebar.');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Form trigger button is displayed.
		$I->dontSeeFormTriggerOutput($I);
	}

	/**
	 * Test the Form Trigger block's text parameter works.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockWithTextParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger: Text Param'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Trigger',
			blockProgrammaticName: 'convertkit-formtrigger',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
				'text' => [ 'text', 'Sign up' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Sign up');
	}

	/**
	 * Test the Form Trigger block's default text value is output when the text parameter is blank.
	 *
	 * @since   2.0.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockWithBlankTextParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger: Blank Text Param'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Trigger',
			blockProgrammaticName: 'convertkit-formtrigger',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
				'text' => [ 'text', '' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe');
	}

	/**
	 * Test the Form Trigger block's theme color parameters works.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockWithThemeColorParameters(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Define colors.
		$backgroundColor = 'white';
		$textColor       = 'purple';

		// It's tricky to interact with Gutenberg's color picker, so we programmatically create the Page
		// instead to then confirm the color settings apply on the output.
		// We don't need to test the color picker itself, as it's a Gutenberg supplied component, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-form-trigger-block-theme-color-params',
				'post_content' => '<!-- wp:convertkit/formtrigger {"form":"' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '","backgroundColor":"' . $backgroundColor . '","textColor":"' . $textColor . '"} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-form-trigger-block-theme-color-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL']);

		// Confirm that the chosen colors are applied as CSS styles.
		$I->seeInSource('class="wp-block-button__link convertkit-formtrigger has-text-color has-' . $textColor . '-color has-background has-' . $backgroundColor . '-background-color');
	}

	/**
	 * Test the Form Trigger block's hex color parameters works.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockWithHexColorParameters(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Define colors.
		$backgroundColor = '#ee1616';
		$textColor       = '#1212c0';

		// It's tricky to interact with Gutenberg's color picker, so we programmatically create the Page
		// instead to then confirm the color settings apply on the output.
		// We don't need to test the color picker itself, as it's a Gutenberg supplied component, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-form-trigger-block-hex-color-params',
				'post_content' => '<!-- wp:convertkit/formtrigger {"form":"' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '","style":{"color":{"text":"' . $textColor . '","background":"' . $backgroundColor . '"}}} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-form-trigger-block-hex-color-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe', $textColor, $backgroundColor);
	}

	/**
	 * Test the Form Trigger block's parameters are correctly escaped on output,
	 * to prevent XSS.
	 *
	 * @since   2.0.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockParameterEscaping(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Define a 'bad' block.  This is difficult to do in Gutenberg, but let's assume it's possible.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-form-trigger-block-parameter-escaping',
				'post_content' => '<!-- wp:convertkit/formtrigger {"form":"' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '","style":{"color":{"text":"red\" onmouseover=\"alert(1)\""}}} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-form-trigger-block-parameter-escaping');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the output is escaped.
		$I->seeInSource('style="color:red&quot; onmouseover=&quot;alert(1)&quot;"');
		$I->dontSeeInSource('style="color:red" onmouseover="alert(1)""');

		// Confirm that the Kit Form Trigger is displayed.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe');
	}

	/**
	 * Test the Form Trigger block displays a message with a link to the Plugin's
	 * settings screen, when the Plugin has no credentials specified.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockWhenNoCredentials(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger: Block: No Credentials'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Trigger',
			blockProgrammaticName: 'convertkit-formtrigger'
		);

		// Test that the popup window works.
		$I->testBlockNoCredentialsPopupWindow(
			$I,
			blockName: 'convertkit-formtrigger',
			expectedMessage: 'Select a Form using the Form option in the Gutenberg sidebar.'
		);

		// Save page to avoid alert box when _passed() runs to deactivate the Plugin.
		$I->publishGutenbergPage($I);
	}

	/**
	 * Test the Form Trigger block displays a message with a link to the Plugin's
	 * settings screen, when the Kit account has no forms.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockWhenNoForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger: Block: No Forms'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Trigger',
			blockProgrammaticName: 'convertkit-formtrigger'
		);

		// Confirm that the Form block displays instructions to the user on how to add a Form in Kit.
		$I->seeBlockHasNoContentMessage($I, 'No modal, sticky bar or slide in forms exist in Kit.');

		// Click the link to confirm it loads Kit.
		$I->clickLinkInBlockAndAssertKitLoginScreen($I, 'Click here to create a form.');

		// Save page to avoid alert box when _passed() runs to deactivate the Plugin.
		$I->publishGutenbergPage($I);
	}

	/**
	 * Test the Form Trigger block's refresh button works.
	 *
	 * @since   2.2.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerBlockRefreshButton(EndToEndTester $I)
	{
		// Setup Plugin with Kit Account that has no Forms.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger: Refresh Button'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Trigger',
			blockProgrammaticName: 'convertkit-formtrigger'
		);

		// Setup Plugin with a valid API Key and resources, as if the user performed the necessary steps to authenticate
		// and create a form.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Click the refresh button.
		$I->clickBlockRefreshButton($I);

		// Confirm that the Form Trigger block displays instructions to the user on how to select a Form.
		$I->seeBlockHasNoContentMessage($I, 'Select a Form using the Form option in the Gutenberg sidebar.');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
