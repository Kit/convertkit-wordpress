<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

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
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);

		// Create Custom Post Types using the Custom Post Type UI Plugin.
		$I->registerCustomPostTypes($I);
	}

	/**
	 * Test that no dropdown filter on the CPT screen is displayed when no credentials are configured.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoFilterDisplayedWhenNoCredentials(EndToEndTester $I)
	{
		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check no filter is displayed, as the Plugin isn't configured.
		$I->dontSeeElementInDOM('#wp-convertkit-restrict-content-filter');
	}

	/**
	 * Test that no dropdown filter on the CPT screen is displayed when the Kit
	 * account has no Forms, Tag and Products.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoFilterDisplayedWhenNoResources(EndToEndTester $I)
	{
		// Setup Plugin using credentials that have no resources.
		$I->setupKitPluginCredentialsNoData($I);

		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check no filter is displayed, as the Kit account has no resources.
		$I->dontSeeElementInDOM('#wp-convertkit-restrict-content-filter');
	}

	/**
	 * Test that no dropdown filter on the CPT screen is displayed when the Post Type
	 * is not public.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoFilterOnPrivateCPT(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

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
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFilterByProduct(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

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
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFilterByTag(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

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
	 * Test that filtering by Form works on the Articles screen.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFilterByForm(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Article, set to restrict content to a Product.
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Form: Filter Test',
				'restrict_content_setting' => 'form_' . $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);

		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Article is listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Article: Restricted Content: Form: Filter Test');
		$I->see('Kit Member Content');

		// Filter by Form.
		$I->selectOption('#wp-convertkit-restrict-content-filter', $_ENV['CONVERTKIT_API_FORM_NAME']);
		$I->click('Filter');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Article is still listed, and has the 'Kit Member Content' label.
		$I->see('Kit: Article: Restricted Content: Form: Filter Test');
		$I->see('Kit Member Content');
	}

	/**
	 * Test that filtering by 'All member-only content' works on the CPT screen.
	 *
	 * @since   2.8.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFilterByAllMemberOnlyContent(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create a mix of Posts restricted and not restricted to Forms, Tags and Products.
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Form: Filter Test',
				'restrict_content_setting' => 'form_' . $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Tag: Filter Test',
				'restrict_content_setting' => 'tag_' . $_ENV['CONVERTKIT_API_TAG_ID'],
			]
		);
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Product: Filter Test',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);
		$I->havePostInDatabase(
			[
				'post_type'  => 'article',
				'post_title' => 'Kit: Article: Standard',
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => '0',
					],
				],
			]
		);
		$I->havePostInDatabase(
			[
				'post_type'  => 'article',
				'post_title' => 'Kit: Article: Standard: No Meta',
			]
		);

		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Filter by All member-only content.
		$I->selectOption('#wp-convertkit-restrict-content-filter', 'All member-only content');
		$I->click('Filter');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Restrict Content Articles are listed.
		$I->see('Kit: Article: Restricted Content: Form: Filter Test');
		$I->see('Kit: Article: Restricted Content: Tag: Filter Test');
		$I->see('Kit: Article: Restricted Content: Product: Filter Test');

		// Confirm that no non-Restrict Content Posts are not listed.
		$I->dontSee('Kit: Article: Standard');
		$I->dontSee('Kit: Article: Standard: No Meta');
	}

	/**
	 * Test that no filtering takes place when the filter is set to All Content on the CPT screen.
	 *
	 * @since   2.8.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoFilteringWhenAllContentSelected(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create a mix of Posts restricted and not restricted to Forms, Tags and Products.
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Form: No Filter Test',
				'restrict_content_setting' => 'form_' . $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Tag: No Filter Test',
				'restrict_content_setting' => 'tag_' . $_ENV['CONVERTKIT_API_TAG_ID'],
			]
		);
		$I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restricted Content: Product: No Filter Test',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);
		$I->havePostInDatabase(
			[
				'post_type'  => 'article',
				'post_title' => 'Kit: Article: Standard',
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => '0',
					],
				],
			]
		);
		$I->havePostInDatabase(
			[
				'post_type'  => 'article',
				'post_title' => 'Kit: Article: Standard: No Meta',
			]
		);

		// Navigate to Articles.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Click the Filter button with no changes made.
		$I->click('Filter');

		// Wait for the WP_List_Table of Articles to load.
		$I->waitForElementVisible('tbody#the-list');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that all 5 Articles still display.
		$I->see('Kit: Article: Restricted Content: Form: No Filter Test');
		$I->see('Kit: Article: Restricted Content: Tag: No Filter Test');
		$I->see('Kit: Article: Restricted Content: Product: No Filter Test');
		$I->see('Kit: Article: Standard');
		$I->see('Kit: Article: Standard: No Meta');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->unregisterCustomPostTypes($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
