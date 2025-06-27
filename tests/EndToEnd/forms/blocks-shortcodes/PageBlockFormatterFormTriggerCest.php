<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form Trigger Gutenberg Block Formatter.
 *
 * @since   2.2.0
 */
class PageBlockFormatterFormTriggerCest
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
	 * Test the Form Trigger formatter works when selecting a modal form.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerFormatterWithModalForm(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger Formatter: Modal Form'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Subscribe');

		// Select text.
		$I->selectAllText($I, '.wp-block-post-content p[data-empty="false"]');

		// Apply formatter to link the selected text.
		$I->applyGutenbergFormatter(
			$I,
			formatterName: 'Kit Form Trigger',
			formatterProgrammaticName: 'convertkit-form-link',
			formatterConfiguration: [
				// Form.
				'data-id' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the link displays and works when clicked.
		$I->seeFormTriggerLinkOutput(
			$I,
			formURL: $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'],
			text: 'Subscribe'
		);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]', 1);
	}

	/**
	 * Test the Form Trigger formatter is applied and removed when selecting a modal form, and then
	 * selecting the 'None' option.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerFormatterToggleFormSelection(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger Formatter: Modal Form Toggle'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Subscribe');

		// Select text.
		$I->selectAllText($I, '.wp-block-post-content p[data-empty="false"]');

		// Apply formatter to link the selected text.
		$I->applyGutenbergFormatter(
			$I,
			formatterName: 'Kit Form Trigger',
			formatterProgrammaticName: 'convertkit-form-link',
			formatterConfiguration: [
				// Form.
				'data-id' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Select text and apply the formatter again, this time selecting the 'None' option.
		$I->applyGutenbergFormatter(
			$I,
			formatterName: 'Kit Form Trigger',
			formatterProgrammaticName: 'convertkit-form-link',
			formatterConfiguration: [
				// Form.
				'data-id' => [ 'select', 'None' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the link does not display, as no form was selected.
		$I->dontSeeFormTriggerLinkOutput($I);
	}

	/**
	 * Test the Form Trigger formatter works when no form is selected.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerFormatterWithNoForm(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger Formatter: No Form'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Subscribe');

		// Select text.
		$I->selectAllText($I, '.wp-block-post-content p[data-empty="false"]');

		// Apply formatter to link the selected text.
		$I->applyGutenbergFormatter(
			$I,
			formatterName: 'Kit Form Trigger',
			formatterProgrammaticName: 'convertkit-form-link',
			formatterConfiguration: [
				// Form.
				'data-id' => [ 'select', 'None' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the link does not display, as no form was selected.
		$I->dontSeeFormTriggerLinkOutput($I);
	}

	/**
	 * Test the Form Trigger formatter is not available when no forms exist in Kit.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerFormatterNotRegisteredWhenNoFormsExist(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Trigger Formatter: No Forms Exist'
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Subscribe');

		// Select text.
		$I->selectAllText($I, '.wp-block-post-content p[data-empty="false"]');

		// Confirm the formatter is not registered.
		$I->dontSeeGutenbergFormatter($I, 'Kit Form Trigger');

		// Publish the page, to avoid an alert when navigating away.
		$I->publishGutenbergPage($I);
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
