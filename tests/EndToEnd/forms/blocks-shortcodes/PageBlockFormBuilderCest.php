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
            label: 'First name',
            container: 'div[data-type="convertkit/form-builder"]'
        );
        $this->seeFormBuilderField(
            $I,
            fieldName: 'email',
            label: 'Email address',
            container: 'div[data-type="convertkit/form-builder"]'
        );

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

        $I->see('XXX');

		// Confirm that the Form is output in the DOM.
        $this->seeFormBuilderBlock($I);
        $this->seeFormBuilderField(
            $I,
            fieldName: 'first_name',
            label: 'First name'
        );
        $this->seeFormBuilderField(
            $I,
            fieldName: 'email',
            label: 'Email address'
        );
        

        // Submit form.
        // @TODO.

        // Confirm subscription.
        // @TODO.
        
	}

    private function seeFormBuilderBlock(EndToEndTester $I)
    {
        $I->seeElementInDOM('div[data-type="convertkit/form-builder"]');
    }

    private function seeFormBuilderButtonBlock(EndToEndTester $I)
    {
        $I->seeElementInDOM('div[data-type="convertkit/form-builder"] div[data-type="core/button"]');
    }

    private function seeFormBuilderField(EndToEndTester $I, $fieldName, $label, $required = true, $container = 'div')
    {
        $I->seeElementInDOM($container . ' label[for="' . $fieldName . '"]');
        $I->seeElementInDOM($container . ' input[name="convertkit[' . $fieldName . ']"]' . $required ? '[required]' : '');
        $I->assertEquals($label, $I->grabTextFrom($container . ' label[for="' . $fieldName . '"]'));
    }

    private function seeFormBuilderSubmitButton(EndToEndTester $I, $text)
    {
        $I->seeElementInDOM('button[type="submit"]');
        $I->assertEquals($text, $I->grabTextFrom('button[type="submit"]'));
    }

    /**
	 * Test the Form Builder block works when added with changes made to the:
     * - Submit button text,
     * - Redirect URL,
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

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form Builder',
			blockProgrammaticName: 'convertkit-form-builder',
            blockConfiguration: [
                'submit_button_text' => 'Subscribe',
                'redirect_url' => 'https://example.com',
                'display_form' => 'true',
                'thanks_for_subscribing_text' => 'Thanks for subscribing!',
            ]
		);

        // Confirm the block template was used as the default.
        // @TODO.

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

        $I->see('XXX');

		// Confirm that the Form is output in the DOM.
        // @TODO Use function for this.

        // Submit.

        // Confirm subscription.
        
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
        // @TODO.
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
        // @TODO.
    }

    public function testFormBuilderSupportsStyles(EndToEndTester $I)
    {
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Define theme color.
		$backgroundColor = 'accent-5';

		// Create a Page as if it were create in Gutenberg with the Form block
		// set to display an inline form.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => 'Kit: Page: Form: Block: Theme Color',
				'post_content' => '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '","style":{"color":{"background":"' . $backgroundColor . '"}}} /-->',
				'post_content' => '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '","style":{"color":{"background":"' . $backgroundColor . '"}}} /-->',
				'post_content' => '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '","style":{"spacing":{"padding":{"top":"var:preset|spacing|30"},"margin":{"top":"var:preset|spacing|30"}}}} /-->',
				
                
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

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);

		// Confirm that the chosen colors are applied as CSS styles.
		$I->seeInSource('<div class="convertkit-form wp-block-convertkit-form has-background" style="background-color:' . $backgroundColor . '"');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
