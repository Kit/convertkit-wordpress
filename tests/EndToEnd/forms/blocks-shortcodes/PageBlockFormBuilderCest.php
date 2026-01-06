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
	 * Test the Form Builder block's conditional fields work.
	 *
	 * @since   3.0.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderBlockConditionalFields(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Conditional Fields'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder'
		);

		// Confirm conditional fields are not displayed.
		$I->dontSeeElementInDOM('#convertkit_form_builder_text_if_subscribed');

		// Disable 'Display form' and confirm the conditional field displays.
		$I->click("//label[normalize-space(text())='Display form']/preceding-sibling::span/input");
		$I->waitForElementVisible('#convertkit_form_builder_text_if_subscribed');

		// Enable 'Display form' to confirm the conditional field is hidden.
		$I->click("//label[normalize-space(text())='Display form']/preceding-sibling::span/input");
		$I->waitForElementNotVisible('#convertkit_form_builder_text_if_subscribed');

		// Publish Page, so no browser warnings are displayed about unsaved changes.
		$I->publishGutenbergPage($I);
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
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
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
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the message and form is displayed.
		$I->waitForElementVisible('.convertkit-form-builder-subscribed-message');
		$I->see('Thanks for subscribing!');
		$I->seeElementInDOM('input[name="convertkit[first_name]"]');
		$I->seeElementInDOM('input[name="convertkit[email]"]');
		$I->seeElementInDOM('button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->wait(3);
		$I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);
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

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder',
			blockConfiguration: [
				'Display form'       => [ 'toggle', false ],
				'text_if_subscribed' => [ 'text', 'Welcome to the newsletter!' ],
			]
		);

		// Change the labels of the form fields. These are added as inner blocks when the Form Builder block is added.
		$I->selectGutenbergBlockInEditor($I, 'convertkit/form-builder-field-name');
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->fillField('#convertkit_form_builder_field_name_label', 'Nafnið þitt');

		$I->selectGutenbergBlockInEditor($I, 'convertkit/form-builder-field-email');
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->fillField('#convertkit_form_builder_field_email_label', 'Netfangið þitt');

		// Wait for the changes to show in the editor.
		$I->wait(2);

		// Confirm the block template was used as the default.
		$this->seeFormBuilderBlock($I);
		$this->seeFormBuilderButtonBlock($I);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'Nafnið þitt',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
			fieldName: 'email',
			fieldID: 'email',
			label: 'Netfangið þitt',
			container: 'div[data-type="convertkit/form-builder"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Form is output in the DOM.
		$this->seeFormBuilderField(
			$I,
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'Nafnið þitt',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
			fieldName: 'email',
			fieldID: 'email',
			label: 'Netfangið þitt',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Check that the form no longer displays and the message displays.
		$I->waitForText('Welcome to the newsletter!', 10, '.convertkit-form-builder.wp-block-convertkit-form-builder');
		$I->dontSeeElementInDOM('input[name="convertkit[first_name]"]');
		$I->dontSeeElementInDOM('input[name="convertkit[email]"]');

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);
	}

	/**
	 * Test the Form Builder block works when added and a Form is specified
	 * to subscribe the subscriber to.
	 *
	 * @since   3.0.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderBlockWithFormEnabled(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Form Enabled'
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
			blockProgrammaticName: 'convertkit-form-builder',
			blockConfiguration: [
				'form_id' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Confirm the block template was used as the default.
		$this->seeFormBuilderBlock($I);
		$this->seeFormBuilderButtonBlock($I);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
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
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->waitForElementVisible('.convertkit-form-builder-subscribed-message');
		$I->wait(3);
		$subscriber = $I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);

		// Confirm that the subscriber has the form.
		$I->apiCheckSubscriberHasForm(
			$I,
			subscriberID: $subscriber['id'],
			formID: $_ENV['CONVERTKIT_API_FORM_ID'],
			referrer: $_ENV['WORDPRESS_URL'] . $I->grabFromCurrentUrl()
		);
	}

	/**
	 * Test the Form Builder block works when added and a Tag is specified
	 * to subscribe the subscriber to.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderBlockWithTaggingEnabled(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Tagging Enabled'
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
			blockProgrammaticName: 'convertkit-form-builder',
			blockConfiguration: [
				'tag_id' => [ 'select', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Confirm the block template was used as the default.
		$this->seeFormBuilderBlock($I);
		$this->seeFormBuilderButtonBlock($I);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
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
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->waitForElementVisible('.convertkit-form-builder-subscribed-message');
		$I->wait(3);
		$subscriber = $I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);

		// Confirm that the subscriber has the tag.
		$I->apiCheckSubscriberHasTag(
			$I,
			subscriberID: $subscriber['id'],
			tagID: $_ENV['CONVERTKIT_API_TAG_ID']
		);
	}

	/**
	 * Test the Form Builder block works when added and a Sequence is specified
	 * to subscribe the subscriber to.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderBlockWithSequenceEnabled(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Sequence Enabled'
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
			blockProgrammaticName: 'convertkit-form-builder',
			blockConfiguration: [
				'sequence_id' => [ 'select', $_ENV['CONVERTKIT_API_SEQUENCE_NAME'] ],
			]
		);

		// Confirm the block template was used as the default.
		$this->seeFormBuilderBlock($I);
		$this->seeFormBuilderButtonBlock($I);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
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
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->waitForElementVisible('.convertkit-form-builder-subscribed-message');
		$I->wait(3);
		$subscriber = $I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);

		// Confirm that the subscriber has the sequence.
		$I->apiCheckSubscriberHasSequence(
			$I,
			subscriberID: $subscriber['id'],
			sequenceID: $_ENV['CONVERTKIT_API_SEQUENCE_ID']
		);
	}

	/**
	 * Test the Form Builder block works when custom fields are added.
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

		// Define custom fields to add to the form.
		$customFields = [
			'last_name'    => [
				'label' => 'Last Name',
				'type'  => 'text',
				'value' => 'Last',
			],
			'phone_number' => [
				'label' => 'Phone Number',
				'type'  => 'number',
				'value' => '1234567890',
			],
			'notes'        => [
				'label' => 'Notes',
				'type'  => 'textarea',
				'value' => 'Notes',
			],
			'url'          => [
				'label' => 'URL',
				'type'  => 'url',
				'value' => 'https://kit.com',
			],
		];

		foreach ( $customFields as $field ) {
			// Focus on an inner block, so the Form Builder field blocks are available in the inserter.
			$I->selectGutenbergBlockInEditor($I, 'convertkit/form-builder-field-name');

			// Add custom field block, mapping its data to the Last Name field in Kit.
			$I->addGutenbergBlock(
				$I,
				blockName: 'Kit Form Builder: Custom Field',
				blockProgrammaticName: 'convertkit-form-builder-field-custom',
				blockConfiguration: [
					'label'        => [ 'input', $field['label'] ],
					'type'         => [ 'select', $field['type'] ],
					'custom_field' => [ 'select', $field['label'] ],
				]
			);
		}

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Form is output in the DOM.
		$this->seeFormBuilderField(
			$I,
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);
		foreach ( $customFields as $key => $field ) {
			$this->seeFormBuilderField(
				$I,
				fieldType: $field['type'],
				fieldName: 'custom_fields][' . $key,
				fieldID: 'custom_fields_' . $key,
				label: $field['label'],
				container: 'div.wp-block-convertkit-form-builder',
				switchToGutenbergEditor: false
			);
		}

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		foreach ( $customFields as $key => $field ) {
			$I->fillField('[name="convertkit[custom_fields][' . $key . ']"]', $field['value']);
		}
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->waitForElementVisible('.convertkit-form-builder-subscribed-message');
		$I->wait(3);
		$subscriber = $I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);

		// Confirm that the custom fields were added to the subscriber.
		foreach ( $customFields as $key => $field ) {
			$I->assertEquals($field['value'], $subscriber['fields'][ $key ]);
		}
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
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div[data-type="convertkit/form-builder"]'
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
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
			fieldType: 'text',
			fieldName: 'first_name',
			fieldID: 'first_name',
			label: 'First name',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);
		$this->seeFormBuilderField(
			$I,
			fieldType: 'email',
			fieldName: 'email',
			fieldID: 'email',
			label: 'Email address',
			container: 'div.wp-block-convertkit-form-builder',
			switchToGutenbergEditor: false
		);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Check we are on the redirect URL i.e. the home page.
		$I->waitForElementVisible('body.home');

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);
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
		$I->selectGutenbergBlockInEditor($I, 'convertkit/form-builder');
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
		$I->selectGutenbergBlockInEditor($I, 'convertkit/form-builder');
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');

		// Confirm some core blocks can be added to the Form Builder block.
		$I->seeGutenbergBlockAvailable($I, 'Paragraph', 'paragraph/paragraph');
		$I->seeGutenbergBlockAvailable($I, 'Heading', 'heading/heading');
		$I->seeGutenbergBlockAvailable($I, 'List', 'list');
		$I->seeGutenbergBlockAvailable($I, 'Image', 'image');
		$I->seeGutenbergBlockAvailable($I, 'Spacer', 'spacer');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);
	}

	/**
	 * Test the Form Builder block works when reCAPTCHA is enabled.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderWithRecaptchaEnabled(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin(
			$I,
			[
				'recaptcha_site_key'      => $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY'],
				'recaptcha_secret_key'    => $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY'],
				'recaptcha_minimum_score' => '0.01', // Set a low score to ensure reCAPTCHA passes the subscriber.
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Recaptcha'
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

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm the button includes the g-recaptcha class, and the script is enqueued.
		$I->seeElementInDOM('div.wp-block-convertkit-form-builder button[type="submit"][class*="g-recaptcha"]');
		$I->seeInSource('<script src="https://www.google.com/recaptcha/api.js');

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->waitForElementVisible('.convertkit-form-builder-subscribed-message');
		$I->wait(3);
		$I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);
	}

	/**
	 * Test the Form Builder block works when reCAPTCHA is enabled, and the minimum score is set to 0.99.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderWithRecaptchaEnabledAndHighMinimumScore(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin(
			$I,
			[
				'recaptcha_site_key'      => $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY'],
				'recaptcha_secret_key'    => $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY'],
				'recaptcha_minimum_score' => '0.99', // Set a high score to ensure reCAPTCHA blocks the subscriber.
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Recaptcha High Min Score'
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

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm the button includes the g-recaptcha class, and the script is enqueued.
		$I->seeElementInDOM('div.wp-block-convertkit-form-builder button[type="submit"][class*="g-recaptcha"]');
		$I->seeInSource('<script src="https://www.google.com/recaptcha/api.js');

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was not added to Kit, as reCAPTCHA score failed.
		$I->wait(3);
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);
	}

	/**
	 * Test the Form Builder block works when the Store Entries option is enabled,
	 * with custom fields, tag and sequence settings defined.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBuilderWithStoreEntriesEnabled(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form Builder: Block: Store Entries'
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
			blockProgrammaticName: 'convertkit-form-builder',
			blockConfiguration: [
				'Store form submissions' => [ 'toggle', true ],
				'sequence_id'            => [ 'select', $_ENV['CONVERTKIT_API_SEQUENCE_ID'] ],
				'tag_id'                 => [ 'select', $_ENV['CONVERTKIT_API_TAG_ID'] ],
			]
		);

		// Focus on an inner block, so the Form Builder field blocks are available in the inserter.
		$I->selectGutenbergBlockInEditor($I, 'convertkit/form-builder-field-name');

		// Add custom field block, mapping its data to the Last Name field in Kit.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Builder: Custom Field',
			blockProgrammaticName: 'convertkit-form-builder-field-custom',
			blockConfiguration: [
				'label'        => [ 'input', 'Last Name' ],
				'type'         => [ 'select', 'text' ],
				'custom_field' => [ 'select', 'last_name' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit form.
		$I->fillField('input[name="convertkit[first_name]"]', 'First');
		$I->fillField('input[name="convertkit[custom_fields][last_name]"]', 'Last');
		$I->fillField('input[name="convertkit[email]"]', $emailAddress);
		$I->click('div.wp-block-convertkit-form-builder button[type="submit"]');

		// Confirm that the email address was added to Kit.
		$I->waitForElementVisible('.convertkit-form-builder-subscribed-message');
		$I->wait(3);
		$subscriber = $I->apiCheckSubscriberExists(
			$I,
			emailAddress: $emailAddress,
			firstName: 'First'
		);

		// Confirm that the custom field was added to the subscriber.
		$I->assertEquals('Last', $subscriber['fields']['last_name']);

		// Confirm that the subscriber has the tag.
		$I->apiCheckSubscriberHasTag(
			$I,
			subscriberID: $subscriber['id'],
			tagID: $_ENV['CONVERTKIT_API_TAG_ID']
		);

		// Confirm that the subscriber has the sequence.
		$I->apiCheckSubscriberHasSequence(
			$I,
			subscriberID: $subscriber['id'],
			sequenceID: $_ENV['CONVERTKIT_API_SEQUENCE_ID']
		);

		// Confirm that the entry was stored in the database.
		$I->seeInDatabase(
			'wp_kit_form_entries',
			[
				'email'         => $emailAddress,
				'first_name'    => 'First',
				'custom_fields' => '{"last_name":"Last"}',
				'tag_id'        => $_ENV['CONVERTKIT_API_TAG_ID'],
				'sequence_id'   => $_ENV['CONVERTKIT_API_SEQUENCE_ID'],
				'api_result'    => 'success',
			]
		);
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
	 * @param   EndToEndTester $I                        Tester.
	 * @param   bool           $switchToGutenbergEditor  Switch to the Gutenberg IFrame.
	 */
	private function seeFormBuilderBlock(EndToEndTester $I, $switchToGutenbergEditor = true)
	{
		// Switch to the Gutenberg IFrame.
		if ($switchToGutenbergEditor) {
			$I->switchToGutenbergEditor($I);
		}

		$I->seeElementInDOM('div[data-type="convertkit/form-builder"]');

		// Switch back to main window.
		if ($switchToGutenbergEditor) {
			$I->switchToIFrame();
		}
	}

	/**
	 * Helper method to confirm that the Form Builder button block is output in the DOM.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I                        Tester.
	 * @param   bool           $switchToGutenbergEditor  Switch to the Gutenberg IFrame.
	 */
	private function seeFormBuilderButtonBlock(EndToEndTester $I, $switchToGutenbergEditor = true)
	{
		// Switch to the Gutenberg IFrame.
		if ($switchToGutenbergEditor) {
			$I->switchToGutenbergEditor($I);
		}

		$I->seeElementInDOM('div[data-type="convertkit/form-builder"] div[data-type="core/button"]');

		// Switch back to main window.
		if ($switchToGutenbergEditor) {
			$I->switchToIFrame();
		}
	}

	/**
	 * Helper method to confirm that the Form Builder field is output in the DOM.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   string         $fieldType  Field type.
	 * @param   string         $fieldName  Field name.
	 * @param   string         $fieldID    Field ID.
	 * @param   string         $label      Field label.
	 * @param   bool           $required   Whether the field should be marked `required`.
	 * @param   string         $container  The container the field should be in.
	 * @param   bool           $switchToGutenbergEditor  Switch to the Gutenberg IFrame.
	 */
	private function seeFormBuilderField(EndToEndTester $I, $fieldType, $fieldName, $fieldID, $label, $required = true, $container = 'div', $switchToGutenbergEditor = true)
	{
		// Switch to the Gutenberg IFrame.
		if ($switchToGutenbergEditor) {
			$I->switchToGutenbergEditor($I);
		}

		// Check field exists with correct attributes.
		switch ( $fieldType ) {
			case 'textarea':
				$I->seeElementInDOM($container . ' textarea[name="convertkit[' . $fieldName . ']"][id="' . $fieldID . '"]' . ( $required ? '[required]' : '' ) );
				break;
			default:
				$I->seeElementInDOM($container . ' input[name="convertkit[' . $fieldName . ']"][type="' . $fieldType . '"][id="' . $fieldID . '"]' . ( $required ? '[required]' : '' ) );
		}

		// Check label exists with correct text.
		$I->seeElementInDOM($container . ' label[for="' . $fieldID . '"]');
		$I->assertEquals($label . ( $required ? ' *' : '' ), $I->grabTextFrom($container . ' label[for="' . $fieldID . '"]'));

		// Check the required asterisk is displayed.
		if ($required) {
			$I->seeElementInDOM($container . ' label[for="' . $fieldID . '"] span.convertkit-form-builder-field-required');
		}

		// Switch back to main window.
		if ($switchToGutenbergEditor) {
			$I->switchToIFrame();
		}
	}

	/**
	 * Helper method to confirm that the Form Builder submit button is output in the DOM.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I       Tester.
	 * @param   string         $text    The text to check for in the submit button.
	 */
	private function seeFormBuilderSubmitButton(EndToEndTester $I, $text)
	{
		$I->seeElementInDOM('button[type="submit"]');
		$I->assertEquals($text, $I->grabTextFrom('button[type="submit"]'));
	}
}
