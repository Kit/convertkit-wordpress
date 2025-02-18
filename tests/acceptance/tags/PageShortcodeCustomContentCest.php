<?php
/**
 * Tests for the Kit Custom Content shortcode.
 *
 * @since   1.9.6
 */
class PageShortcodeCustomContentCest
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
		$I->activateKitPlugin($I);

		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test the [convertkit_content] shortcode works using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.7.5
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testCustomContentShortcodeInVisualEditor(AcceptanceTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage($I, 'page', 'Kit: Page: Custom Content: Shortcode: Visual Editor');

		// Add shortcode to Page, setting the Tag setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			'Kit Custom Content',
			[
				'tag' => [ 'select', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			],
			'[convertkit_content tag="' . $_ENV['CONVERTKIT_API_TAG_ID'] . '"][/convertkit_content]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);
	}

	/**
	 * Test the [convertkit_content] shortcode works using the Text Editor.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testCustomContentShortcodeInTextEditor(AcceptanceTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage($I, 'page', 'Kit: Page: Custom Content: Shortcode: Text Editor');

		// Add shortcode to Page, setting the Tag setting to the value specified in the .env file.
		$I->addTextEditorShortcode(
			$I,
			'convertkit-content',
			[
				'tag' => [ 'select', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			],
			'[convertkit_content tag="' . $_ENV['CONVERTKIT_API_TAG_ID'] . '"][/convertkit_content]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);
	}

	/**
	 * Test the [convertkit_content] shortcode works when a valid Tag ID is specified,
	 * and an invalid Subscriber ID is used.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testCustomContentShortcodeWithValidTagParameterAndInvalidSubscriberID(AcceptanceTester $I)
	{
		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-custom-content-shortcode-valid-tag-param-and-invalid-subscriber-id',
				'post_content' => '[convertkit_content tag="' . $_ENV['CONVERTKIT_API_TAG_ID'] . '"]KitCustomContent[/convertkit_content]',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-custom-content-shortcode-valid-tag-param-and-invalid-subscriber-id');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Custom Content is not yet displayed.
		$I->dontSee('KitCustomContent');

		// Reload the page, this time with an invalid subscriber ID .
		$I->amOnPage('/kit-custom-content-shortcode-valid-tag-param-and-invalid-subscriber-id?ck_subscriber_id=1');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Custom Content is not yet displayed.
		$I->dontSee('KitCustomContent');
	}

	/**
	 * Test the [convertkit_content] shortcode works when a valid Tag ID is specified,
	 * and a valid Subscriber ID is used who is subscribed to the tag.
	 *
	 * @since   1.9.6
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testCustomContentShortcodeWithValidTagParameterAndValidSubscriberID(AcceptanceTester $I)
	{
		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-custom-content-shortcode-valid-tag-param-and-valid-subscriber-id',
				'post_content' => '[convertkit_content tag="' . $_ENV['CONVERTKIT_API_TAG_ID'] . '"]KitCustomContent[/convertkit_content]',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-custom-content-shortcode-valid-tag-param-and-valid-subscriber-id');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Custom Content is not yet displayed.
		$I->dontSee('KitCustomContent');

		// Reload the page, this time with a subscriber ID who is already subscribed to the tag.
		$I->amOnPage('/kit-custom-content-shortcode-valid-tag-param-and-valid-subscriber-id?ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Custom Content is now displayed.
		$I->see('KitCustomContent');
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
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
