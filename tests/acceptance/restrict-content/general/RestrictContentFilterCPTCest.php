<?php
/**
 * Tests the filter dropdown for Restrict Content in the CPT WP_List_Table.
 *
 * @since   2.4.3
 */
class RestrictContentFilterCPTCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
	{
		// Activate ConvertKit plugin.
		$I->activateConvertKitPlugin($I);

		// Create a public Custom Post Type called Articles, using the Custom Post Type UI Plugin.
		$I->registerCustomPostType($I, 'article', 'Articles', 'Article');

		// Create a non-public Custom Post Type called Private, using the Custom Post Type UI Plugin.
		$I->registerCustomPostType($I, 'private', 'Private', 'Private', false);
	}

	/**
	 * Test that no dropdown filter on the CPT screen is displayed when no credentials are configured.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testNoFilterDisplayedWhenNoCredentials(AcceptanceTester $I)
	{
		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check no filter is displayed, as the Plugin isn't configured.
		$I->dontSeeElementInDOM('#wp-convertkit-restrict-content-filter');
	}

	/**
	 * Test that no dropdown filter on the CPT screen is displayed when the ConvertKit
	 * account has no Forms, Tag and Products.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testNoFilterDisplayedWhenNoResources(AcceptanceTester $I)
	{
		// Setup Plugin using credentials that have no resources.
		$I->setupConvertKitPluginCredentialsNoData($I);

		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check no filter is displayed, as the ConvertKit account has no resources.
		$I->dontSeeElementInDOM('#wp-convertkit-restrict-content-filter');
	}

	/**
	 * Test that no dropdown filter on the CPT screen is displayed when the Post Type
	 * is not public.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testNoFilterOnPrivateCPT(AcceptanceTester $I)
	{
		// Setup Plugin.
		$I->setupConvertKitPlugin($I);

		// Navigate to Private CPT.
		$I->amOnAdminPage('edit.php?post_type=private');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check no filter is displayed.
		$I->dontSeeElementInDOM('#wp-convertkit-restrict-content-filter');
	}

	/**
	 * Test that filtering by Product works on the Articles screen.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testFilterByProduct(AcceptanceTester $I)
	{
		// Setup Plugin.
		$I->setupConvertKitPlugin($I);

		// Create Article, set to restrict content to a Product.
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Product: Filter Test',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);

		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Article is listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Article: Restricted Content: Product: Filter Test');
		$I->see('Kit Member Content');

		// Filter by Product.
		$I->selectOption('#wp-convertkit-restrict-content-filter', $_ENV['CONVERTKIT_API_PRODUCT_NAME']);
		$I->click('Filter');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Article is still listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Article: Restricted Content: Product: Filter Test');
		$I->see('Kit Member Content');
	}

	/**
	 * Test that filtering by Tag works on the Articles screen.
	 *
	 * @since   2.7.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testFilterByTag(AcceptanceTester $I)
	{
		// Setup Plugin.
		$I->setupConvertKitPlugin($I);

		// Create Article, set to restrict content to a Product.
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Tag: Filter Test',
				'restrict_content_setting' => 'tag_' . $_ENV['CONVERTKIT_API_TAG_ID'],
			]
		);

		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Article is listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Article: Restricted Content: Tag: Filter Test');
		$I->see('Kit Member Content');

		// Filter by Tag.
		$I->selectOption('#wp-convertkit-restrict-content-filter', $_ENV['CONVERTKIT_API_TAG_NAME']);
		$I->click('Filter');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Article is still listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Article: Restricted Content: Tag: Filter Test');
		$I->see('Kit Member Content');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->unregisterCustomPostType($I, 'article');
		$I->unregisterCustomPostType($I, 'private');
		$I->deactivateConvertKitPlugin($I);
		$I->resetConvertKitPlugin($I);
	}
}
