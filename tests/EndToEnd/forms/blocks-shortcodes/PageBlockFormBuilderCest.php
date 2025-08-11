<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form Builder Gutenberg Block.
 *
 * @since   3.0.0
 */
class PageBlockFormBuilderCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
	}

	/**
	 * Test the Form Builder block works when added with no changes to its configuration.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderBlockWithDefaultConfiguration(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Default'
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
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder'
		);

		// Confirm the block template was used as the default.
		$this->seeFormBuilderBlock($I);
		$this->seeFormBuilderButtonBlock($I);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div[data-type="convertkit/form-builder"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Form is output in the DOM.
		$this->seeFormBuilderField(
			$I,
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder'
		);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder'
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'Kit');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->waitForElementVisible('body.page');
		$I->wait(3);
		$I->apiCheckSubscriberExists($I, $emailAddress);
	}

	/**
	 * Test the Form Builder block works when added with changes made to the:
	 * - Display form option,
	 * - Thanks for subscribing text.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderBlockWithTextCustomization(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Text Customization'
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
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder',
			blockConfiguration: [
				'#inspector-toggle-control-0' => [ 'toggle', false ],
				'text_if_subscribed'          => [ 'text', 'Welcome to the newsletter!' ],
			]
		);

		// Change the labels of the form fields. These are added as inner blocks when the Form Builder block is added.
		$I->click('div[data-type="convertkit/form-builder-field-name"]');
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->fillField('#convertkit_form_builder_field_name_label', 'Your name');

		$I->click('div[data-type="convertkit/form-builder-field-email"]');
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->fillField('#convertkit_form_builder_field_email_label', 'Your email');

		// Wait for the changes to show in the editor.
		$I->wait(2);

		// Confirm the block template was used as the default.
		$this->seeFormBuilderBlock($I);
		$this->seeFormBuilderButtonBlock($I);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'Your name',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'email',
			fieldID: 'email',
			label: 'Your email',
			container: 'div[data-type="convertkit/form-builder"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Form is output in the DOM.
		$this->seeFormBuilderField(
			$I,
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'Your name',
			container: 'div.wp-block-convertkit-form-builder'
		);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'email',
			fieldID: 'email',
			label: 'Your email',
			container: 'div.wp-block-convertkit-form-builder'
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'Kit');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');
		$I->waitForElementVisible('body.page');

		// Check that the form no longer displays and the message displays.
		$I->dontSeeElementInDOM('input[name="convertkit[first_name]"]');
		$I->dontSeeElementInDOM('input[name="convertkit[email]"]');
		$I->see('Welcome to the newsletter!');

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists($I, $emailAddress);
	}

	/**
	 * Test the Form Builder block works when a custom field is added.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderBlockWithCustomField(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Custom Field'
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
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder'
		);

		// Focus on an inner block, so the Form Builder field blocks are available in the inserter.
		$I->click('div[data-type="convertkit/form-builder-field-name"]');

		// Add custom field block, mapping its data to the Last Name field in Kit.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Builder: Custom Field',
			blockProgrammaticName: 'convertkit-form-builder-field-custom',
			blockConfiguration: [
				'label' => [ 'input', 'Last name' ],
				'custom_field' => [ 'select', 'Last Name' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Form is output in the DOM.
		$this->seeFormBuilderField(
			$I,
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder'
		);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'custom_fields][last_name',
			fieldID: 'custom_fields_last_name',
			label: 'Last name',
			container: 'div.wp-block-convertkit-form-builder'
		);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder'
		);
		
		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[custom_fields][last_name]"]', 'Last');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->waitForElementVisible('body.page');
		$I->wait(3);
		$subscriber = $I->apiCheckSubscriberExists($I, $emailAddress);

		// Confirm that the custom field was added to the subscriber.
		// @TODO.
		//$I->apiCheckSubscriberHasCustomField($I, $subscriber['id'], 'last_name', 'Last');
	}

	/**
	 * Test the Form Builder block works when the redirect URL is set.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderBlockWithRedirectOption(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Redirect URL'
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
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder',
			blockConfiguration: [
				'redirect' => [ 'text', $_ENV['WORDPRESS_URL'] ],
			]
		);

		// Confirm the block template was used as the default.
		$this->seeFormBuilderBlock($I);
		$this->seeFormBuilderButtonBlock($I);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div[data-type="convertkit/form-builder"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Form is output in the DOM.
		$this->seeFormBuilderField(
			$I,
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder'
		);
		$this->seeFormBuilderField(
			$I,
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder'
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'Kit');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Check we are on the redirect URL i.e. the home page.
		$I->waitForElementVisible('body.home');

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists($I, $emailAddress);
	}

	/**
	 * Test the Form Builder block supports various styling options
	 * provided by the block editor.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderSupportsStyles(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create a Page as if it were create in Gutenberg with the Form Builder block
		// and various styling options.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => 'Kit: Page: Form Builder: Block: Styles',
				'post_content' => '<!-- wp:convertkit/form-builder {"align":"left","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"},"margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30","left":"var:preset|spacing|30","right":"var:preset|spacing|30"}}},"backgroundColor":"accent-5","fontSize":"small"} -->
<div class="wp-block-convertkit-form-builder alignleft has-accent-5-background-color has-background has-small-font-size" style="padding: var(--wp--preset--spacing--30); margin: var(--wp--preset--spacing--30);"><!-- wp:convertkit/form-builder-field-name {"label":"First name"} /-->

<!-- wp:convertkit/form-builder-field-email {"label":"Email address"} /-->

<!-- wp:button {"lock":{"move":true,"remove":true},"className":"convertkit-form-builder-submit-button"} -->
<div class="wp-block-button convertkit-form-builder-submit-button"><a class="wp-block-button__link wp-element-button">Subscribe</a></div>
<!-- /wp:button --></div>
<!-- /wp:convertkit/form-builder -->',
				'meta_input'   => [
					// Configure Kit Plugin to not display a default Form.
					'_wp_convertkit_post_meta' => [
						'form'         => '0',
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('?p=' . $pageID);

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the chosen styles are applied.
		$I->seeInSource('<div class="wp-block-convertkit-form-builder alignleft has-accent-5-background-color has-background has-small-font-size" style="padding: var(--wp--preset--spacing--30); margin: var(--wp--preset--spacing--30);">');
	}

	/**
	 * Test the Form Builder Fields (Name, Email etc.) can only be inserted to the Kit Form Builder
	 * block, and not another block in the editor.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderFieldsCanOnlyBeInsertedToFormBuilderBlock(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Form Fields'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Confirm the Form Field blocks cannot be added, as we are not within the Form Builder block.
		$I->dontSeeGutenbergBlockAvailable($I, 'Kit Form Field Name', 'convertkit-form-builder-field-name');
		$I->dontSeeGutenbergBlockAvailable($I, 'Kit Form Field Email', 'convertkit-form-builder-field-email');
		$I->dontSeeGutenbergBlockAvailable($I, 'Kit Form Field Custom', 'convertkit-form-builder-field-custom');

		// Add the Form Builder block.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder'
		);

		// Click an inner block within the Form Builder block.
		$I->click('div[data-type="convertkit/form-builder"]');
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');

		// Confirm the Form Field blocks can be added to the Form Builder block.
		$I->seeGutenbergBlockAvailable($I, 'Kit Form Field Name', 'convertkit-form-builder-field-name');
		$I->seeGutenbergBlockAvailable($I, 'Kit Form Field Email', 'convertkit-form-builder-field-email');
		$I->seeGutenbergBlockAvailable($I, 'Kit Form Field Custom', 'convertkit-form-builder-field-custom');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);
	}

	/**
	 * Test the Form Builder block supports the core blocks.
	 * - Paragraph
	 * - Heading
	 * - List
	 * - Image
	 * - Button
	 * - Spacer
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderSupportsCoreBlocks(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Core Blocks'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add the Form Builder block.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder'
		);

		// Click an inner block within the Form Builder block.
		$I->click('div[data-type="convertkit/form-builder"]');
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');

		// Confirm some core blocks can be added to the Form Builder block.
		$I->seeGutenbergBlockAvailable($I, 'Paragraph', 'paragraph');
		$I->seeGutenbergBlockAvailable($I, 'Heading', 'heading');
		$I->seeGutenbergBlockAvailable($I, 'List', 'list');
		$I->seeGutenbergBlockAvailable($I, 'Image', 'image');
		$I->seeGutenbergBlockAvailable($I, 'Spacer', 'spacer');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}

	/**
	 * Helper method to confirm that the Form Builder block is output in the DOM.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	private function seeFormBuilderBlock(EndToEndTester $I)
	{
		$I->seeElementInDOM('div[data-type="convertkit/form-builder"]');
	}

	/**
	 * Helper method to confirm that the Form Builder button block is output in the DOM.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	private function seeFormBuilderButtonBlock(EndToEndTester $I)
	{
		$I->seeElementInDOM('div[data-type="convertkit/form-builder"] div[data-type="core/button"]');
	}

	/**
	 * Helper method to confirm that the Form Builder field is output in the DOM.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   string         $fieldName  Field name.
	 * @param   string         $fieldID    Field ID.
	 * @param   string         $label      Field label.
	 * @param   bool           $required   Whether the field should be marked `required`.
	 * @param   string         $container  The container the field should be in.
	 */
	private function seeFormBuilderField(EndToEndTester $I, $fieldName, $fieldID, $label, $required = true, $container = 'div')
	{
		$I->seeElementInDOM($container . ' label[for="' . $fieldID . '"]');
		$I->seeElementInDOM($container . ' input[name="convertkit[' . $fieldName . ']"][id="' . $fieldID . '"]' . $required ? '[required]' : '');
		$I->assertEquals($label, $I->grabTextFrom($container . ' label[for="' . $fieldID . '"]'));
	}

	/**
	 * Helper method to confirm that the Form Builder submit button is output in the DOM.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I     Tester.
	 * @param   string         $text  The text to check for in the submit button.
	 */
	private function seeFormBuilderSubmitButton(EndToEndTester $I, $text)
	{
		$I->seeElementInDOM('button[type="submit"]');
		$I->assertEquals($text, $I->grabTextFrom('button[type="submit"]'));
	}
}
