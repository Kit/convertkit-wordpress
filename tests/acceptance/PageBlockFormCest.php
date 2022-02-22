<?php
/**
 * Tests for the ConvertKit Form's Gutenberg Block.
 * 
 * @since 	1.9.6
 */
class PageBlockFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->activateConvertKitPlugin($I);
		$I->setupConvertKitPlugin($I);
		$I->enableDebugLog($I);
		$I->wait(2);

		// Navigate to Pages > Add New
		$I->amOnAdminPage('post-new.php?post_type=page');

		// Close the Gutenberg "Welcome to the block editor" dialog if it's displayed
		$I->maybeCloseGutenbergWelcomeModal($I);

		// Change Form to None, so that no Plugin level Form is displayed, ensuring we only
		// test the Form block in Gutenberg.
		$I->selectOption('#wp-convertkit-form', 'None');
	}

	/**
	 * Test the Form block works when a valid Form is selected.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testFormBlockWithValidFormParameter(AcceptanceTester $I)
	{
		// Define a Page Title.
		$I->fillField('.editor-post-title__input', 'ConvertKit: Form: Block: Valid Form Param');

		// Click Add Block Button.
		$I->click('button.edit-post-header-toolbar__inserter-toggle');

		// When the Blocks sidebar appears, search for the ConvertKit Form block.
		$I->waitForElementVisible('.interface-interface-skeleton__secondary-sidebar[aria-label="Block library"]');
		$I->fillField('.block-editor-inserter__content input[type=search]', 'ConvertKit Form');
		$I->seeElementInDOM('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');
		$I->click('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');

		// When the sidebar appears, select the Form.
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->selectOption('#convertkit_form_form', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');
		
		// When the pre-publish panel displays, click Publish again.
		$I->performOn('.editor-post-publish-panel__prepublish', function($I) {
			$I->click('.editor-post-publish-panel__header-publish-button .editor-post-publish-button__button');	
		});

		// Wait for confirmation that the Page published.
		$I->waitForElementVisible('.post-publish-panel__postpublish-buttons a.components-button');

		// Load the Page on the frontend site.
		$I->click('.post-publish-panel__postpublish-buttons a.components-button');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the ConvertKit Form is displayed.
		$I->seeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test the Form block works when a valid Legacy Form is selected.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testFormBlockWithValidLegacyFormParameter(AcceptanceTester $I)
	{
		// Define a Page Title.
		$I->fillField('.editor-post-title__input', 'ConvertKit: Legacy Form: Block: Valid Form Param');

		// Click Add Block Button.
		$I->click('button.edit-post-header-toolbar__inserter-toggle');

		// When the Blocks sidebar appears, search for the ConvertKit Form block.
		$I->waitForElementVisible('.interface-interface-skeleton__secondary-sidebar[aria-label="Block library"]');
		$I->fillField('.block-editor-inserter__content input[type=search]', 'ConvertKit Form');
		$I->seeElementInDOM('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');
		$I->click('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');

		// When the sidebar appears, select the Form.
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->selectOption('#convertkit_form_form', $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME']);

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');
		
		// When the pre-publish panel displays, click Publish again.
		$I->performOn('.editor-post-publish-panel__prepublish', function($I) {
			$I->click('.editor-post-publish-panel__header-publish-button .editor-post-publish-button__button');	
		});

		// Wait for confirmation that the Page published.
		$I->waitForElementVisible('.post-publish-panel__postpublish-buttons a.components-button');

		// Load the Page on the frontend site.
		$I->click('.post-publish-panel__postpublish-buttons a.components-button');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the ConvertKit Form is displayed.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.convertkit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the Form block displays a message explaining why the block cannot be previewed
	 * in the Gutenberg editor when a valid Modal Form is selected.
	 * 
	 * @since 	1.9.6.9
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testFormBlockWithValidModalFormParameter(AcceptanceTester $I)
	{
		// Define a Page Title.
		$I->fillField('.editor-post-title__input', 'ConvertKit: Form: Block: Valid Modal Form Param');

		// Click Add Block Button.
		$I->click('button.edit-post-header-toolbar__inserter-toggle');

		// When the Blocks sidebar appears, search for the ConvertKit Form block.
		$I->waitForElementVisible('.interface-interface-skeleton__secondary-sidebar[aria-label="Block library"]');
		$I->fillField('.block-editor-inserter__content input[type=search]', 'ConvertKit Form');
		$I->seeElementInDOM('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');
		$I->click('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');

		// When the sidebar appears, select the Form.
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->selectOption('#convertkit_form_form', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME']);

		// Switch to iframe preview for the Form block.
		$I->switchToIFrame('iframe[class="components-sandbox"]');

		// Confirm that the Form block iframe sandbox preview displays that the Modal form was selected, and to view the frontend
		// site to see it (we cannot preview Modal forms in the Gutenberg editor due to Gutenberg using an iframe).
		$I->see('Modal form "' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] . '" selected. View on the frontend site to see the modal form.');

		// Switch back to main window.
		$I->switchToIFrame();

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');
		
		// When the pre-publish panel displays, click Publish again.
		$I->performOn('.editor-post-publish-panel__prepublish', function($I) {
			$I->click('.editor-post-publish-panel__header-publish-button .editor-post-publish-button__button');	
		});

		// Wait for confirmation that the Page published.
		$I->waitForElementVisible('.post-publish-panel__postpublish-buttons a.components-button');

		// Load the Page on the frontend site.
		$I->click('.post-publish-panel__postpublish-buttons a.components-button');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the ConvertKit Form is displayed.
		$I->seeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test the Form block displays a message explaining why the block cannot be previewed
	 * in the Gutenberg editor when a valid Slide In Form is selected.
	 * 
	 * @since 	1.9.6.9
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testFormBlockWithValidSlideInFormParameter(AcceptanceTester $I)
	{
		// Define a Page Title.
		$I->fillField('.editor-post-title__input', 'ConvertKit: Form: Block: Valid Slide In Form Param');

		// Click Add Block Button.
		$I->click('button.edit-post-header-toolbar__inserter-toggle');

		// When the Blocks sidebar appears, search for the ConvertKit Form block.
		$I->waitForElementVisible('.interface-interface-skeleton__secondary-sidebar[aria-label="Block library"]');
		$I->fillField('.block-editor-inserter__content input[type=search]', 'ConvertKit Form');
		$I->seeElementInDOM('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');
		$I->click('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');

		// When the sidebar appears, select the Form.
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->selectOption('#convertkit_form_form', $_ENV['CONVERTKIT_API_FORM_FORMAT_SLIDE_IN_NAME']);

		// Switch to iframe preview for the Form block.
		$I->switchToIFrame('iframe[class="components-sandbox"]');

		// Confirm that the Form block iframe sandbox preview displays that the Modal form was selected, and to view the frontend
		// site to see it (we cannot preview Modal forms in the Gutenberg editor due to Gutenberg using an iframe).
		$I->see('Slide in form "' . $_ENV['CONVERTKIT_API_FORM_FORMAT_SLIDE_IN_NAME'] . '" selected. View on the frontend site to see the slide in form.');

		// Switch back to main window.
		$I->switchToIFrame();

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');
		
		// When the pre-publish panel displays, click Publish again.
		$I->performOn('.editor-post-publish-panel__prepublish', function($I) {
			$I->click('.editor-post-publish-panel__header-publish-button .editor-post-publish-button__button');	
		});

		// Wait for confirmation that the Page published.
		$I->waitForElementVisible('.post-publish-panel__postpublish-buttons a.components-button');

		// Load the Page on the frontend site.
		$I->click('.post-publish-panel__postpublish-buttons a.components-button');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the ConvertKit Form is displayed.
		$I->seeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test the Form block displays a message explaining why the block cannot be previewed
	 * in the Gutenberg editor when a valid Sticky Bar Form is selected.
	 * 
	 * @since 	1.9.6.9
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testFormBlockWithValidStickyBarFormParameter(AcceptanceTester $I)
	{
		// Define a Page Title.
		$I->fillField('.editor-post-title__input', 'ConvertKit: Form: Block: Valid Sticky Bar Form Param');

		// Click Add Block Button.
		$I->click('button.edit-post-header-toolbar__inserter-toggle');

		// When the Blocks sidebar appears, search for the ConvertKit Form block.
		$I->waitForElementVisible('.interface-interface-skeleton__secondary-sidebar[aria-label="Block library"]');
		$I->fillField('.block-editor-inserter__content input[type=search]', 'ConvertKit Form');
		$I->seeElementInDOM('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');
		$I->click('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');

		// When the sidebar appears, select the Form.
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->selectOption('#convertkit_form_form', $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME']);

		// Switch to iframe preview for the Form block.
		$I->switchToIFrame('iframe[class="components-sandbox"]');

		// Confirm that the Form block iframe sandbox preview displays that the Modal form was selected, and to view the frontend
		// site to see it (we cannot preview Modal forms in the Gutenberg editor due to Gutenberg using an iframe).
		$I->see('Sticky bar form "' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME'] . '" selected. View on the frontend site to see the sticky bar form.');

		// Switch back to main window.
		$I->switchToIFrame();

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');
		
		// When the pre-publish panel displays, click Publish again.
		$I->performOn('.editor-post-publish-panel__prepublish', function($I) {
			$I->click('.editor-post-publish-panel__header-publish-button .editor-post-publish-button__button');	
		});

		// Wait for confirmation that the Page published.
		$I->waitForElementVisible('.post-publish-panel__postpublish-buttons a.components-button');

		// Load the Page on the frontend site.
		$I->click('.post-publish-panel__postpublish-buttons a.components-button');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the ConvertKit Form is displayed.
		$I->seeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test the Form block works when no Form is selected.
	 * 
	 * @since 	1.9.6
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function testFormBlockWithNoFormParameter(AcceptanceTester $I)
	{
		// Define a Page Title.
		$I->fillField('.editor-post-title__input', 'ConvertKit: Form: Block: No Form Param');

		// Click Add Block Button.
		$I->click('button.edit-post-header-toolbar__inserter-toggle');

		// When the Blocks sidebar appears, search for the ConvertKit Form block.
		$I->waitForElementVisible('.interface-interface-skeleton__secondary-sidebar[aria-label="Block library"]');
		$I->fillField('.block-editor-inserter__content input[type=search]', 'ConvertKit Form');
		$I->seeElementInDOM('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');
		$I->click('.block-editor-inserter__panel-content button.editor-block-list-item-convertkit-form');

		// Confirm that the Form block displays instructions to the user on how to select a Form.
		$I->see('Select a Form using the Form option in the Gutenberg sidebar.', [
			'css' => '.convertkit-form-no-content',
		]);

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');
		
		// When the pre-publish panel displays, click Publish again.
		$I->performOn('.editor-post-publish-panel__prepublish', function($I) {
			$I->click('.editor-post-publish-panel__header-publish-button .editor-post-publish-button__button');	
		});

		// Wait for confirmation that the Page published.
		$I->waitForElementVisible('.post-publish-panel__postpublish-buttons a.components-button');

		// Load the Page on the frontend site.
		$I->click('.post-publish-panel__postpublish-buttons a.components-button');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no ConvertKit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 * 
	 * @since 	1.9.6.7
	 * 
	 * @param 	AcceptanceTester 	$I 	Tester
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateConvertKitPlugin($I);
		$I->resetConvertKitPlugin($I);
	}
}