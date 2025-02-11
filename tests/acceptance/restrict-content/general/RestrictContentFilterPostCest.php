<?php
/**
 * Tests the filter dropdown for Restrict Content in the Posts WP_List_Table.
 *
 * @since   2.3.2
 */
class RestrictContentFilterPostCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.3.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
	{
		// Activate ConvertKit plugin.
		$I->activateConvertKitPlugin($I);
	}

	/**
	 * Test that no dropdown filter on the Posts screen is displayed when no credentials are configured.
	 *
	 * @since   2.3.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testNoFilterDisplayedWhenNoCredentials(AcceptanceTester $I)
	{
		// Navigate to Posts.
		$I->amOnAdminPage('edit.php?post_type=post');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check no filter is displayed, as the Plugin isn't configured.
		$I->dontSeeElementInDOM('#wp-convertkit-restrict-content-filter');
	}

	/**
	 * Test that no dropdown filter on the Posts screen is displayed when the ConvertKit
	 * account has no Forms, Tag and Products.
	 *
	 * @since   2.3.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testNoFilterDisplayedWhenNoResources(AcceptanceTester $I)
	{
		// Setup Plugin using credentials that have no resources.
		$I->setupConvertKitPluginCredentialsNoData($I);

		// Navigate to Posts.
		$I->amOnAdminPage('edit.php?post_type=post');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check no filter is displayed, as the ConvertKit account has no resources.
		$I->dontSeeElementInDOM('#wp-convertkit-restrict-content-filter');
	}

	/**
	 * Test that filtering by Product works on the Posts screen.
	 *
	 * @since   2.3.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testFilterByProduct(AcceptanceTester $I)
	{
		// Setup Plugin.
		$I->setupConvertKitPlugin($I);

		// Create Post, set to restrict content to a Product.
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'post',
				'post_title'               => 'Kit: Post: Restricted Content: Product: Filter Test',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);

		// Navigate to Posts.
		$I->amOnAdminPage('edit.php?post_type=post');

		// Wait for the WP_List_Table of Posts to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Post is listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Post: Restricted Content: Product: Filter Test');
		$I->see('Kit Member Content');

		// Filter by Product.
		$I->selectOption('#wp-convertkit-restrict-content-filter', $_ENV['CONVERTKIT_API_PRODUCT_NAME']);
		$I->click('Filter');

		// Wait for the WP_List_Table of Posts to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Post is still listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Post: Restricted Content: Product: Filter Test');
		$I->see('Kit Member Content');
	}

	/**
	 * Test that filtering by Form works on the Posts screen.
	 *
	 * @since   2.7.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testFilterByForm(AcceptanceTester $I)
	{
		// Setup Plugin.
		$I->setupConvertKitPlugin($I);

		// Create Post, set to restrict content to a Form.
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'post',
				'post_title'               => 'Kit: Post: Restricted Content: Form: Filter Test',
				'restrict_content_setting' => 'form_' . $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);

		// Navigate to Posts.
		$I->amOnAdminPage('edit.php?post_type=post');

		// Wait for the WP_List_Table of Posts to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Post is listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Post: Restricted Content: Form: Filter Test');
		$I->see('Kit Member Content');

		// Filter by Form.
		$I->selectOption('#wp-convertkit-restrict-content-filter', $_ENV['CONVERTKIT_API_FORM_NAME']);
		$I->click('Filter');

		// Wait for the WP_List_Table of Posts to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Post is still listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Post: Restricted Content: Form: Filter Test');
		$I->see('Kit Member Content');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.3.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateConvertKitPlugin($I);
		$I->resetConvertKitPlugin($I);
	}
}
