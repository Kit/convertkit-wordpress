<?php
/**
 * Tests Restrict Content by Product functionality on WordPress Custom Post Types.
 *
 * @since   2.4.3
 */
class RestrictContentProductCPTCest
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
		// Activate Kit plugin.
		$I->activateKitPlugin($I);

		// Create a public Custom Post Type called Articles, using the Custom Post Type UI Plugin.
		$I->registerCustomPostType($I, 'article', 'Articles', 'Article');

		// Create a non-public Custom Post Type called Private, using the Custom Post Type UI Plugin.
		$I->registerCustomPostType($I, 'private', 'Private', 'Private', false);
	}

	/**
	 * Test that content is not restricted when not configured on a WordPress CPT.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentWhenDisabled(AcceptanceTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Add the CPT using the Gutenberg editor.
		$I->addGutenbergPage($I, 'article', 'Kit: Article: Restrict Content: Product');

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock($I, 'More', 'more');
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Article.
		$url = $I->publishGutenbergPage($I);

		// Confirm that all content is displayed.
		$I->amOnUrl($url);
		$I->see('Visible content.');
		$I->see('Member-only content.');
	}

	/**
	 * Test that no Restrict Content options are displayed when the Post Type
	 * is not public.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testNoRestrictContentOnPrivateCPT(AcceptanceTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Add the CPT using the Gutenberg editor.
		$I->addGutenbergPage($I, 'private', 'Kit: Private: Restrict Content');

		// Check that the metabox is not displayed.
		$I->dontSeeElementInDOM('#wp-convertkit-meta-box');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);
	}

	/**
	 * Test that restricting content by a Product specified in the CPT Settings works when
	 * creating and viewing a new WordPress CPT.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentByProduct(AcceptanceTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Add the CPT using the Gutenberg editor.
		$I->addGutenbergPage($I, 'article', 'Kit: Article: Restrict Content: Product');

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock($I, 'More', 'more');
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Article.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $url);
	}

	/**
	 * Test that restricting content by a Product specified in the CPT Settings works when
	 * creating and viewing a new WordPress CPT, and that the WordPress generated CPT Excerpt
	 * is displayed when no more tag exists.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentByProductWithGeneratedExcerpt(AcceptanceTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Define visible content and member-only content.
		$visibleContent    = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at velit purus. Nam gravida tempor tellus, sit amet euismod arcu. Mauris sed mattis leo. Mauris viverra eget tellus sit amet vehicula. Nulla eget sapien quis felis euismod pellentesque. Quisque elementum et diam nec eleifend. Sed ornare quam eget augue consequat, in maximus quam fringilla. Morbi';
		$memberOnlyContent = 'Member-only content';

		// Add the CPT using the Gutenberg editor.
		$I->addGutenbergPage($I, 'article', 'Kit: Article: Restrict Content: Product: Generated Excerpt');

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, $visibleContent);
		$I->addGutenbergParagraphBlock($I, $memberOnlyContent);

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			$url,
			[
				'visible_content' => $visibleContent,
				'member_content'  => $memberOnlyContent,
			]
		);
	}

	/**
	 * Test that restricting content by a Product specified in the CPT Settings works when
	 * creating and viewing a new WordPress CPT, and JS is enabled to allow the modal
	 * version for the authentication flow to be used.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentModalByProduct(AcceptanceTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);

		// Add the CPT using the Gutenberg editor.
		$I->addGutenbergPage($I, 'article', 'Kit: Article: Restrict Content: Product: Modal');

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock($I, 'More', 'more');
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Article.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentModal($I, $url);
	}

	/**
	 * Test that restricting content by a Product that does not exist does not output
	 * a fatal error and instead displays all of the CPT's content.
	 *
	 * This checks for when a Product is deleted in Kit, but is still specified
	 * as the Restrict Content setting for the CPT.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentByInvalidProduct(AcceptanceTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Programmatically create the CPT.
		$postID = $I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'article',
				'post_title'               => 'Kit: Article: Restrict Content: Invalid Product',
				'restrict_content_setting' => 'product_12345', // A fake Product that does not exist in Kit.
			]
		);

		// Navigate to the article.
		$I->amOnPage('?p=' . $postID);

		// Confirm all content displays, with no errors, as the Product is invalid.
		$I->testRestrictContentDisplaysContent($I);
	}

	/**
	 * Test that restricting content by a Product specified in the CPT Settings works when
	 * using the Quick Edit functionality.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentByProductUsingQuickEdit(AcceptanceTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Programmatically create the CPT.
		$postID = $I->createRestrictedContentPage(
			$I,
			[
				'post_type'  => 'article',
				'post_title' => 'Kit: Article: Restrict Content: Product: ' . $_ENV['CONVERTKIT_API_PRODUCT_NAME'] . ': Quick Edit',
			]
		);

		// Quick Edit the CPT in the CPTs WP_List_Table.
		$I->quickEdit(
			$I,
			'article',
			$postID,
			[
				'restrict_content' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $postID);
	}

	/**
	 * Test that restricting content by a Product specified in the CPT Settings works when
	 * using the Bulk Edit functionality.
	 *
	 * @since   2.4.3
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentByProductUsingBulkEdit(AcceptanceTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Programmatically create two CPTs.
		$postIDs = array(
			$I->createRestrictedContentPage(
				$I,
				[
					'post_type'  => 'article',
					'post_title' => 'Kit: Article: Restrict Content: Product: ' . $_ENV['CONVERTKIT_API_PRODUCT_NAME'] . ': Bulk Edit #1',
				]
			),
			$I->createRestrictedContentPage(
				$I,
				[
					'post_type'  => 'article',
					'post_title' => 'Kit: Article: Restrict Content: Product: ' . $_ENV['CONVERTKIT_API_PRODUCT_NAME'] . ': Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the CPTs in the CPTs WP_List_Table.
		$I->bulkEdit(
			$I,
			'article',
			$postIDs,
			[
				'restrict_content' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Iterate through Articles to run frontend tests.
		foreach ($postIDs as $postID) {
			// Test Restrict Content functionality.
			$I->testRestrictedContentByProductOnFrontend($I, $postID);
			$I->resetCookie('ck_subscriber_id');
		}
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
		$I->resetCookie('ck_subscriber_id');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
