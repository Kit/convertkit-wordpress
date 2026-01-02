<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Restrict Content by Product functionality on WordPress Posts.
 *
 * @since   2.3.2
 */
class RestrictContentProductPostCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that content is not restricted when not configured on a WordPress Post.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentWhenDisabled(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Restrict Content: Product'
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Post.
		$url = $I->publishGutenbergPage($I);

		// Confirm that all content is displayed.
		$I->amOnUrl($url);
		$I->see('Visible content.');
		$I->see('Member-only content.');
	}

	/**
	 * Test that restricting content by a Product specified in the Post Settings works when
	 * creating and viewing a new WordPress Post.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProduct(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Restrict Content: Product'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Post.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $url);
	}

	/**
	 * Test that restricting content by a Product specified in the Post Settings works when
	 * creating and viewing a new WordPress Page, and the "Add a Tag" Post setting does
	 * not result in a critical error due to the use of a signed subscriber ID.
	 *
	 * @since   2.7.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductWithAddTag(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Restrict Content: Product'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'tag'              => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Post.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $url);
	}

	/**
	 * Test that restricting content by a Product specified in the Post Settings works when
	 * creating and viewing a new WordPress Post, and that the WordPress generated Post Excerpt
	 * is displayed when no more tag exists.
	 *
	 * @since   2.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductWithGeneratedExcerpt(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Define visible content and member-only content.
		$visibleContent    = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at velit purus. Nam gravida tempor tellus, sit amet euismod arcu. Mauris sed mattis leo. Mauris viverra eget tellus sit amet vehicula. Nulla eget sapien quis felis euismod pellentesque. Quisque elementum et diam nec eleifend. Sed ornare quam eget augue consequat, in maximus quam fringilla. Morbi';
		$memberOnlyContent = 'Member-only content';

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Restrict Content: Product: Generated Excerpt'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
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
			urlOrPageID: $url,
			options: [
				'visible_content' => $visibleContent,
				'member_content'  => $memberOnlyContent,
			]
		);
	}

	/**
	 * Test that restricting content by a Product specified in the Post Settings works when
	 * creating and viewing a new WordPress Post, and that the Excerpt defined in the Post
	 * is displayed when no more tag exists.
	 *
	 * @since   2.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductWithDefinedExcerpt(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Define visible content and member-only content.
		$options = [
			'visible_content' => 'This is a defined excerpt',
			'member_content'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at velit purus. Nam gravida tempor tellus, sit amet euismod arcu. Mauris sed mattis leo. Mauris viverra eget tellus sit amet vehicula. Nulla eget sapien quis felis euismod pellentesque. Quisque elementum et diam nec eleifend. Sed ornare quam eget augue consequat, in maximus quam fringilla. Morbi',
		];

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Restrict Content: Product: Defined Excerpt'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add member content only as a block.
		// Visible content will be defined in the excerpt.
		$I->addGutenbergParagraphBlock($I, $options['member_content']);

		// Define excerpt.
		$I->addGutenbergExcerpt($I, $options['visible_content']);

		// Publish Post.
		$url = $I->publishGutenbergPage($I);

		// Navigate to the page.
		$I->amOnUrl($url);

		// Test Restrict Content functionality.
		// Check content is not displayed, and CTA displays with expected text.
		$I->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		// Excerpt should not be displayed, as its an excerpt, and we now show the member content instead.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $url);

		// The excerpt shouldn't display, so update the options.
		$options['visible_content'] = '';
		$I->testRestrictContentDisplaysContent($I, $options);

		// Assert that the excerpt is no longer displayed.
		$I->dontSee('This is a defined excerpt');
	}

	/**
	 * Test that restricting content by a Product specified in the Post Settings works when
	 * creating and viewing a new WordPress Post, and JS is enabled to allow the modal
	 * version for the authentication flow to be used.
	 *
	 * @since   2.3.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentModalByProduct(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Restrict Content: Product: Modal'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Post.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentModal($I, $url);
	}

	/**
	 * Test that restricting content by a Product specified in the Post Settings works when
	 * creating and viewing a new WordPress Post, and that the container CSS classes are applied
	 * to the content preview and call to action.
	 *
	 * @since   3.1.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentContainerCSSClasses(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Define Restrict Content settings.
		$settings = [
			'container_css_classes' => 'custom-container-css-class',
		];

		// Setup Restrict Content functionality with container CSS classes.
		$I->setupKitPluginRestrictContent($I, $settings);

		// Add the Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Restrict Content: Container CSS Classes'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			urlOrPageID: $url,
			options: [
				'settings' => $settings,
			]
		);
	}

	/**
	 * Test that restricting content by a Product that does not exist does not output
	 * a fatal error and instead displays all of the Post's content.
	 *
	 * This checks for when a Product is deleted in Kit, but is still specified
	 * as the Restrict Content setting for a Post.
	 *
	 * @since   2.3.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByInvalidProduct(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Programmatically create a Post.
		$postID = $I->createRestrictedContentPage(
			$I,
			[
				'post_type'                => 'post',
				'post_title'               => 'Kit: Post: Restrict Content: Invalid Product',
				'restrict_content_setting' => 'product_12345', // A fake Product that does not exist in Kit.
			]
		);

		// Navigate to the post.
		$I->amOnPage('?p=' . $postID);

		// Confirm all content displays, with no errors, as the Product is invalid.
		$I->testRestrictContentDisplaysContent($I);
	}

	/**
	 * Test that restricting content by a Product specified in the Post Settings works when
	 * using the Quick Edit functionality.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductUsingQuickEdit(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Programmatically create a Post.
		$postID = $I->createRestrictedContentPage(
			$I,
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Post: Restrict Content: Product: ' . $_ENV['CONVERTKIT_API_PRODUCT_NAME'] . ': Quick Edit',
			]
		);

		// Quick Edit the Post in the Posts WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'post',
			postID: $postID,
			configuration: [
				'restrict_content' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $postID);
	}

	/**
	 * Test that restricting content by a Product specified in the Post Settings works when
	 * using the Bulk Edit functionality.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductUsingBulkEdit(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Programmatically create two Posts.
		$postIDs = array(
			$I->createRestrictedContentPage(
				$I,
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Restrict Content: Product: ' . $_ENV['CONVERTKIT_API_PRODUCT_NAME'] . ': Bulk Edit #1',
				]
			),
			$I->createRestrictedContentPage(
				$I,
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Restrict Content: Product: ' . $_ENV['CONVERTKIT_API_PRODUCT_NAME'] . ': Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Posts in the Posts WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'post',
			postIDs: $postIDs,
			configuration: [
				'restrict_content' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Iterate through Posts to run frontend tests.
		foreach ($postIDs as $postID) {
			// Test Restrict Content functionality.
			$I->testRestrictedContentByProductOnFrontend($I, $postID);
			$I->clearRestrictContentCookie($I);
		}
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->clearRestrictContentCookie($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
