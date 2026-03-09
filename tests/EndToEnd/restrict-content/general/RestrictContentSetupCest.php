<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Restrict Content's Setup functionality.
 *
 * @since   2.1.0
 */
class RestrictContentSetupCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit Plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that the Add New Member Content button does not display on the Pages screen when no Kit
	 * account is connected.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentButtonNotDisplayedWhenNoCredentials(EndToEndTester $I)
	{
		// Navigate to Pages.
		$I->amOnAdminPage('edit.php?post_type=page');

		// Check the buttons are not displayed.
		$I->dontSeeElementInDOM('span.convertkit-action.page-title-action');
	}

	/**
	 * Test that the Add New Member Content button does not display on the Posts screen.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentButtonNotDisplayedOnPosts(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Navigate to Posts.
		$I->amOnAdminPage('edit.php?post_type=post');

		// Check the buttons are not displayed.
		$I->dontSeeElementInDOM('span.convertkit-action.page-title-action');
	}

	/**
	 * Test that the Add New Member Content button does not display on the Pages screen when the Add New Member Content button is disabled.
	 *
	 * @since   3.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentButtonNotDisplayedWhenDisabled(EndToEndTester $I)
	{
		// Setup Plugin, disabling the Add New Landing Page / Member Content button.
		$I->setupKitPlugin(
			$I,
			[
				'no_add_new_button' => 'on',
			]
		);

		// Navigate to Pages.
		$I->amOnAdminPage('edit.php?post_type=page');

		// Check the buttons are not displayed.
		$I->dontSeeElementInDOM('span.convertkit-action.page-title-action');
	}

	/**
	 * Test that the Dashboard submenu item for this wizard does not display when a
	 * third party Admin Menu editor type Plugin is installed and active.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoMemberContentWizardDashboardSubmenuItem(EndToEndTester $I)
	{
		// Activate Admin Menu Editor Plugin.
		$I->activateThirdPartyPlugin($I, 'admin-menu-editor');

		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Navigate to Admin Menu Editor's settings.
		$I->amOnAdminPage('options-general.php?page=menu_editor');

		// Wait for the Admin Menu Editor settings screen to load.
		$I->waitForElementVisible('body.settings_page_menu_editor');

		// Save settings. If hiding submenu items fails in the Plugin, this step
		// will display those submenu items on subsequent page loads.
		$I->click('Save Changes');

		// Wait for the Admin Menu Editor settings to save.
		$I->waitForElementVisible('#setting-error-settings_updated');
		$I->see('Settings saved.');

		// Navigate to Dashboard.
		$I->amOnAdminPage('index.php');

		// Confirm no Member Content Dashboard Submenu item exists.
		$I->dontSeeInSource('<a href="options.php?page=convertkit-restrict-content-setup"></a>');
	}

	/**
	 * Test that the Add New Member Content wizard displays call to actions to add a Product or Tag in Kit
	 * when the Kit account has no Tags and Products.
	 *
	 * @since   2.3.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentDisplaysCTAWhenNoResources(EndToEndTester $I)
	{
		// Setup Plugin using Kit account that has no resources.
		$I->setupKitPluginCredentialsNoData($I);

		// Navigate to Pages.
		$I->amOnAdminPage('edit.php?post_type=page');

		// Click Add New Member Content button.
		$I->moveMouseOver('span.convertkit-action');
		$I->waitForElementVisible('span.convertkit-action span.convertkit-actions a');
		$I->click('Member Content');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the expected buttons display linking to Kit.
		$I->see('Create product');
		$I->see('Create tag');
		$I->seeInSource('<a href="https://app.kit.com/products/new/?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit"');
		$I->seeInSource('<a href="https://app.kit.com/subscribers/?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit"');

		// Update the Plugin to use credentials that have resources.
		$I->setupKitPlugin($I);

		// Click the button to reload the wizard.
		$I->click('center a.button-primary');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the Download and Course buttons now display.
		$I->see('What type of content are you offering?');
		$I->see('Download');
		$I->see('Course');
	}

	/**
	 * Test that the Add New Member Content > Exit wizard link returns to the Pages screen.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentExitWizardLink(EndToEndTester $I)
	{
		// Setup Plugin and navigate to Add New Member Content screen.
		$this->_setupAndLoadAddNewMemberContentScreen($I);

		// Click Exit wizard link.
		$I->click('Exit wizard');

		// Confirm exit.
		$I->acceptPopup();

		// Wait for the WP_List_Table of Pages to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the Pages screen is displayed.
		$I->see('Pages');
		$I->moveMouseOver('span.convertkit-action');
		$I->waitForElementVisible('span.convertkit-action span.convertkit-actions a');
		$I->see('Member Content');
	}

	/**
	 * Test that the Add New Member Content > Downloads generates the expected Page
	 * and restricts content by the selected Product.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentDownloadsByProduct(EndToEndTester $I)
	{
		// Setup Plugin and navigate to Add New Member Content screen.
		$this->_setupAndLoadAddNewMemberContentScreen($I);

		// Click Downloads button.
		$I->click('Download');

		// Confirm the Configure Download screen is displayed.
		$I->see('Configure Download');

		// Enter a title and description.
		$I->fillField('title', 'Kit: Member Content: Download');
		$I->fillField('description', 'Visible content.');

		// Confirm that the limit option is not visible, as this is only for courses.
		$I->dontSee('How many lessons does this course consist of?');

		// Restrict by Product.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-restrict_content-container', $_ENV['CONVERTKIT_API_PRODUCT_NAME']);

		// Click submit button.
		$I->click('Submit');

		// Wait for the WP_List_Table of Pages to load.
		$I->waitForElementVisible('tbody#the-list');

		// Confirm that one Page is listed in the WP_List_Table.
		$I->see('Kit: Member Content: Download');
		$I->seeInSource('<span class="post-state">Kit Member Content</span>');

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr.iedit');

		// Get link to Page.
		$url = $I->grabAttributeFrom('tr.iedit span.view a', 'href');

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			$url,
			[
				'member_content' => 'The downloadable member-only content goes here.',
			]
		);
	}

	/**
	 * Test that the Add New Member Content > Course generates the expected Pages.
	 * and restricts content by the selected Product.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentCourseByProduct(EndToEndTester $I)
	{
		// Setup Plugin and navigate to Add New Member Content screen.
		$this->_setupAndLoadAddNewMemberContentScreen($I);

		// Click Course button.
		$I->click('Course');

		// Confirm the Configure Course screen is displayed.
		$I->see('Configure Course');

		// Enter a title, description and lesson count.
		$I->fillField('title', 'Kit: Member Content: Course');
		$I->fillField('description', 'Visible content.');
		$I->fillField('number_of_pages', '3');

		// Restrict by Product.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-restrict_content-container', $_ENV['CONVERTKIT_API_PRODUCT_NAME']);

		// Click submit button.
		$I->click('Submit');

		// Wait for the WP_List_Table of Pages to load.
		$I->waitForElementVisible('tbody#the-list');

		// Confirm that four Pages are listed in the WP_List_Table.
		$I->see('Kit: Member Content: Course');
		$I->see('— Kit: Member Content: Course: 1/3');
		$I->see('— Kit: Member Content: Course: 2/3');
		$I->see('— Kit: Member Content: Course: 3/3');
		$I->see('Kit Member Content | Parent Page: Kit: Member Content: Course');

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr.iedit:first-child');

		// Wait for View link to be visible.
		$I->waitForElementVisible('tr.iedit:first-child span.view a');

		// Click View link.
		$I->click('tr.iedit:first-child span.view a');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Confirm the Start Course button exists.
		$I->see('Start Course');

		// Get URL to first restricted content page.
		$url = $I->grabAttributeFrom('.wp-block-button a', 'href');

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			$url,
			[
				'visible_content' => 'Some introductory text about lesson 1',
				'member_content'  => 'Lesson 1 member-only content goes here.',
			]
		);

		// Test Next / Previous links.
		$I->click('Next Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: 2/3');
		$I->see('Some introductory text about lesson 2');
		$I->see('Lesson 2 member-only content goes here.');

		$I->click('Next Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: 3/3');
		$I->see('Some introductory text about lesson 3');
		$I->see('Lesson 3 member-only content goes here.');

		$I->click('Previous Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: 2/3');
		$I->see('Some introductory text about lesson 2');
		$I->see('Lesson 2 member-only content goes here.');

		$I->click('Previous Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: 1/3');
		$I->see('Some introductory text about lesson 1');
		$I->see('Lesson 1 member-only content goes here.');
	}

	/**
	 * Test that the Add New Member Content > Downloads generates the expected Page
	 * and restricts content by the selected Tag.
	 *
	 * @since   2.3.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentDownloadsByTag(EndToEndTester $I)
	{
		// Setup Plugin and navigate to Add New Member Content screen.
		$this->_setupAndLoadAddNewMemberContentScreen($I);

		// Click Downloads button.
		$I->click('Download');

		// Confirm the Configure Download screen is displayed.
		$I->see('Configure Download');

		// Enter a title and description.
		$I->fillField('title', 'Kit: Member Content: Download: Tag');
		$I->fillField('description', 'Visible content.');

		// Confirm that the limit option is not visible, as this is only for courses.
		$I->dontSee('How many lessons does this course consist of?');

		// Restrict by Tag.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-restrict_content-container', $_ENV['CONVERTKIT_API_TAG_NAME']);

		// Click submit button.
		$I->click('Submit');

		// Wait for the WP_List_Table of Pages to load.
		$I->waitForElementVisible('tbody#the-list');

		// Confirm that one Page is listed in the WP_List_Table.
		$I->see('Kit: Member Content: Download: Tag');
		$I->seeInSource('<span class="post-state">Kit Member Content</span>');

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr.iedit');

		// Get link to Page.
		$url = $I->grabAttributeFrom('tr.iedit span.view a', 'href');

		// Test Restrict Content functionality.
		$I->testRestrictedContentByTagOnFrontend(
			$I,
			urlOrPageID: $url,
			emailAddress: $I->generateEmailAddress(),
			options: [
				'member_content' => 'The downloadable member-only content goes here.',
			]
		);
	}

	/**
	 * Test that the Add New Member Content > Course generates the expected Pages
	 * and restricts content by the selected Tag.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentCourseByTag(EndToEndTester $I)
	{
		// Setup Plugin and navigate to Add New Member Content screen.
		$this->_setupAndLoadAddNewMemberContentScreen($I);

		// Click Course button.
		$I->click('Course');

		// Confirm the Configure Course screen is displayed.
		$I->see('Configure Course');

		// Enter a title, description and lesson count.
		$I->fillField('title', 'Kit: Member Content: Course: Tag');
		$I->fillField('description', 'Visible content.');
		$I->fillField('number_of_pages', '3');

		// Restrict by Product.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-restrict_content-container', $_ENV['CONVERTKIT_API_TAG_NAME']);

		// Click submit button.
		$I->click('Submit');

		// Wait for the WP_List_Table of Pages to load.
		$I->waitForElementVisible('tbody#the-list');

		// Confirm that four Pages are listed in the WP_List_Table.
		$I->see('Kit: Member Content: Course: Tag');
		$I->see('— Kit: Member Content: Course: Tag: 1/3');
		$I->see('— Kit: Member Content: Course: Tag: 2/3');
		$I->see('— Kit: Member Content: Course: Tag: 3/3');
		$I->see('Kit Member Content | Parent Page: Kit: Member Content: Course: Tag');

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr.iedit:first-child');

		// Wait for View link to be visible.
		$I->waitForElementVisible('tr.iedit:first-child span.view a');

		// Click View link.
		$I->click('tr.iedit:first-child span.view a');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Confirm the Start Course button exists.
		$I->see('Start Course');

		// Get URL to first restricted content page.
		$url = $I->grabAttributeFrom('.wp-block-button a', 'href');

		// Test Restrict Content functionality.
		$I->testRestrictedContentByTagOnFrontend(
			$I,
			urlOrPageID: $url,
			emailAddress: $I->generateEmailAddress(),
			options: [
				'visible_content' => 'Some introductory text about lesson 1',
				'member_content'  => 'Lesson 1 member-only content goes here.',
			]
		);

		// Test Next / Previous links.
		$I->click('Next Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: Tag: 2/3');
		$I->see('Some introductory text about lesson 2');
		$I->see('Lesson 2 member-only content goes here.');

		$I->click('Next Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: Tag: 3/3');
		$I->see('Some introductory text about lesson 3');
		$I->see('Lesson 3 member-only content goes here.');

		$I->click('Previous Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: Tag: 2/3');
		$I->see('Some introductory text about lesson 2');
		$I->see('Lesson 2 member-only content goes here.');

		$I->click('Previous Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: Tag: 1/3');
		$I->see('Some introductory text about lesson 1');
		$I->see('Lesson 1 member-only content goes here.');
	}

	/**
	 * Test that the Add New Member Content > Downloads generates the expected Page
	 * and restricts content by the selected Form.
	 *
	 * @since   2.8.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentDownloadsByForm(EndToEndTester $I)
	{
		// Setup Plugin and navigate to Add New Member Content screen.
		$this->_setupAndLoadAddNewMemberContentScreen($I);

		// Click Downloads button.
		$I->click('Download');

		// Confirm the Configure Download screen is displayed.
		$I->see('Configure Download');

		// Enter a title and description.
		$I->fillField('title', 'Kit: Member Content: Download: Form');
		$I->fillField('description', 'Visible content.');

		// Confirm that the limit option is not visible, as this is only for courses.
		$I->dontSee('How many lessons does this course consist of?');

		// Restrict by Form.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-restrict_content-container', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Click submit button.
		$I->click('Submit');

		// Wait for the WP_List_Table of Pages to load.
		$I->waitForElementVisible('tbody#the-list');

		// Confirm that one Page is listed in the WP_List_Table.
		$I->see('Kit: Member Content: Download: Form');
		$I->seeInSource('<span class="post-state">Kit Member Content</span>');

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr.iedit');

		// Get link to Page.
		$url = $I->grabAttributeFrom('tr.iedit span.view a', 'href');

		// Test Restrict Content functionality.
		$I->testRestrictedContentByFormOnFrontend(
			$I,
			urlOrPageID: $url,
			formID: $_ENV['CONVERTKIT_API_FORM_ID'],
			options: [
				'member_content' => 'The downloadable member-only content goes here.',
			]
		);
	}

	/**
	 * Test that the Add New Member Content > Course generates the expected Pages
	 * and restricts content by the selected Form.
	 *
	 * @since   2.8.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewMemberContentCourseByForm(EndToEndTester $I)
	{
		// Setup Plugin and navigate to Add New Member Content screen.
		$this->_setupAndLoadAddNewMemberContentScreen($I);

		// Click Course button.
		$I->click('Course');

		// Confirm the Configure Course screen is displayed.
		$I->see('Configure Course');

		// Enter a title, description and lesson count.
		$I->fillField('title', 'Kit: Member Content: Course: Form');
		$I->fillField('description', 'Visible content.');
		$I->fillField('number_of_pages', '3');

		// Restrict by Product.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-restrict_content-container', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Click submit button.
		$I->click('Submit');

		// Wait for the WP_List_Table of Pages to load.
		$I->waitForElementVisible('tbody#the-list');

		// Confirm that four Pages are listed in the WP_List_Table.
		$I->see('Kit: Member Content: Course: Form');
		$I->see('— Kit: Member Content: Course: Form: 1/3');
		$I->see('— Kit: Member Content: Course: Form: 2/3');
		$I->see('— Kit: Member Content: Course: Form: 3/3');
		$I->see('Kit Member Content | Parent Page: Kit: Member Content: Course: Form');

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr.iedit:first-child');

		// Wait for View link to be visible.
		$I->waitForElementVisible('tr.iedit:first-child span.view a');

		// Click View link.
		$I->click('tr.iedit:first-child span.view a');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Confirm the Start Course button exists.
		$I->see('Start Course');

		// Get URL to first restricted content page.
		$url = $I->grabAttributeFrom('.wp-block-button a', 'href');

		// Test Restrict Content functionality.
		$I->testRestrictedContentByFormOnFrontend(
			$I,
			urlOrPageID: $url,
			formID: $_ENV['CONVERTKIT_API_FORM_ID'],
			options: [
				'visible_content' => 'Some introductory text about lesson 1',
				'member_content'  => 'Lesson 1 member-only content goes here.',
			]
		);

		// Test Next / Previous links.
		$I->click('Next Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: Form: 2/3');
		$I->see('Some introductory text about lesson 2');
		$I->see('Lesson 2 member-only content goes here.');

		$I->click('Next Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: Form: 3/3');
		$I->see('Some introductory text about lesson 3');
		$I->see('Lesson 3 member-only content goes here.');

		$I->click('Previous Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: Form: 2/3');
		$I->see('Some introductory text about lesson 2');
		$I->see('Lesson 2 member-only content goes here.');

		$I->click('Previous Lesson');
		$I->waitForElementVisible('body.page-template-default');
		$I->see('Kit: Member Content: Course: Form: 1/3');
		$I->see('Some introductory text about lesson 1');
		$I->see('Lesson 1 member-only content goes here.');
	}

	/**
	 * Sets up the Kit Plugin, and starts the Setup Wizard for Member Content.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	private function _setupAndLoadAddNewMemberContentScreen(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Navigate to Pages.
		$I->amOnAdminPage('edit.php?post_type=page');

		// Click Add New Member Content button.
		$I->moveMouseOver('span.convertkit-action');
		$I->waitForElementVisible('span.convertkit-action span.convertkit-actions a');
		$I->click('Member Content');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		// Clear cookies for next request.
		$I->clearRestrictContentCookie($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
