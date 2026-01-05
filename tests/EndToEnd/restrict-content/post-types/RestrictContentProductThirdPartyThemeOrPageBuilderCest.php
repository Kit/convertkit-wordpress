<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Restrict Content by Product functionality on WordPress Pages when
 * using a third party Theme or Page Builder that the Kit Plugin supports
 * by using the `convertkit_restrict_content_register_content_filter` hook.
 *
 * @since   2.7.7
 */
class RestrictContentProductThirdPartyThemeOrPageBuilderCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.7.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit Plugin and third party Plugins.
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');

		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that restricting content by a Product specified in the Page Settings works when
	 * creating and viewing a new WordPress Page using the Impeka theme automatically
	 * adds Impeka's grve-container CSS class to the Restrict Content container,
	 * ensuring correct layout.
	 *
	 * @since   3.1.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductWithImpekaTheme(EndToEndTester $I)
	{
		// Activate theme.
		$I->useTheme('impeka');

		// Programmatically create a Page using the Visual Composer Page Builder.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => 'Kit: Page: Restrict Content: Product: Impeka Theme with Visual Composer',
				'post_content' => 'Member-only content.',

				// Don't display a Form on this Page, so we test against Restrict Content's Form.
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
				],
			]
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			urlOrPageID: $pageID,
			options: [
				'visible_content' => '',
				'member_content'  => 'Member-only content.',
				'settings'        => [
					// Test that the grve-container CSS class is added to the Restrict Content container.
					'container_css_classes' => 'grve-container',
				],
			],
		);

		// Deactivate theme and third party Plugins.
		$I->useTheme('twentytwentyfive');
	}

	/**
	 * Test that restricting content by a Product specified in the Page Settings works when
	 * creating and viewing a new WordPress Page using the Uncode theme with
	 * the Visual Composer Page Builder.
	 *
	 * @since   2.7.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductWithUncodeThemeAndVisualComposer(EndToEndTester $I)
	{
		// Activate theme and third party Plugins.
		$I->useTheme('uncode');
		$I->activateThirdPartyPlugin($I, 'uncode-core');
		$I->activateThirdPartyPlugin($I, 'uncode-wpbakery-page-builder');

		// Programmatically create a Page using the Visual Composer Page Builder.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => 'Kit: Page: Restrict Content: Product: Uncode Theme with Visual Composer',

				// Emulate Visual Composer content.
				'post_content' => '[vc_row][vc_column width="1/1"][vc_column_text uncode_shortcode_id="998876"]Member-only content.[/vc_column_text][/vc_column][/vc_row]',

				// Don't display a Form on this Page, so we test against Restrict Content's Form.
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
					'_wpb_vc_js_status'        => 'true',
				],
			]
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			urlOrPageID: $pageID,
			options: [
				'visible_content' => '',
				'member_content'  => 'Member-only content.',
			],
			// Don't check for warnings and notices, as Uncode uses deprecated functions which WordPress 6.9 warn about.
			checkNoWarningsAndNotices: false
		);

		// Deactivate theme and third party Plugins.
		$I->deactivateThirdPartyPlugin($I, 'uncode-wpbakery-page-builder');
		$I->deactivateThirdPartyPlugin($I, 'uncode-core');
		$I->useTheme('twentytwentyfive');
	}

	/**
	 * Test that restricting content by a Product specified in the Page Settings works when
	 * creating and viewing a new WordPress Page using the Uncode theme without
	 * the Visual Composer Page Builder.
	 *
	 * @since   2.7.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductWithUncodeTheme(EndToEndTester $I)
	{
		// Activate theme and third party Plugins.
		$I->useTheme('uncode');
		$I->activateThirdPartyPlugin($I, 'uncode-core');

		// Programmatically create a Page using the Visual Composer Page Builder.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => 'Kit: Page: Restrict Content: Product: Uncode Theme without Visual Composer',

				// Emulate non-Visual Composer content.
				'post_content' => 'Member-only content.',

				// Don't display a Form on this Page, so we test against Restrict Content's Form.
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
				],
			]
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			urlOrPageID: $pageID,
			options: [
				'visible_content' => '',
				'member_content'  => 'Member-only content.',
			],
			// Don't check for warnings and notices, as Uncode uses deprecated functions which WordPress 6.9 warn about.
			checkNoWarningsAndNotices: false
		);

		// Deactivate theme and third party Plugins.
		$I->deactivateThirdPartyPlugin($I, 'uncode-core');
		$I->useTheme('twentytwentyfive');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.7.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		// Deactivate Plugins.
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');

		// Deactivate and reset Kit Plugin.
		$I->clearRestrictContentCookie($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
