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
	 * creating and viewing a new WordPress Page using the Avada theme with a Layout
	 * that has a custom Header and Footer, and that the Header and Footer are not restricted.
	 *
	 * @since   3.5.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductWithAvadaTheme(EndToEndTester $I)
	{
		// Activate theme and third party Plugins.
		$I->useTheme('Avada');
		$I->activateThirdPartyPlugin($I, 'fusion-core');
		$I->activateThirdPartyPlugin($I, 'fusion-builder');

		// Create Avada Header and Footer Layout Sections.
		$headerID = $I->havePostInDatabase(
			[
				'post_type'    => 'fusion_tb_section',
				'post_title'   => 'Kit: Avada: Header',
				'post_content' => 'Avada Header Section',
			]
		);
		$footerID = $I->havePostInDatabase(
			[
				'post_type'    => 'fusion_tb_section',
				'post_title'   => 'Kit: Avada: Footer',
				'post_content' => 'Avada Footer Section',
			]
		);

		// Create an Avada Layout using the Header and Footer on all Pages.
		$I->havePostInDatabase(
			[
				'post_type'    => 'fusion_tb_layout',
				'post_title'   => 'Kit: Avada: Layout',
				'post_content' => json_encode( // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
					[
						'conditions'     => [
							'singular_page' => [
								'label'    => 'All Pages',
								'type'     => 'singular',
								'mode'     => 'include',
								'singular' => 'singular_page',
							],
						],
						'template_terms' => [
							'header' => $headerID,
							'footer' => $footerID,
						],
					]
				),
			]
		);

		// Programmatically create a Page.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => 'Kit: Page: Restrict Content: Product: Avada Theme',
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

		// View the Page.
		$I->amOnPage('?p=' . $pageID);

		// Confirm the Avada Header and Footer display, and the CTA is only output once in the Page's content.
		$I->see('Avada Header Section');
		$I->see('Avada Footer Section');
		$I->seeNumberOfElementsInDOM('#convertkit-restrict-content', 1);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			urlOrPageID: $pageID,
			options: [
				'visible_content' => '',
				'member_content'  => 'Member-only content.',
			],
		);

		// Confirm the Avada Header and Footer display when the subscriber has access.
		$I->see('Avada Header Section');
		$I->see('Avada Footer Section');

		// Revert theme and deactivate Avada Plugins, so they don't affect other tests.
		$I->useTheme('twentytwentyfive');
		$I->deactivateThirdPartyPlugin($I, 'fusion-builder');
		$I->deactivateThirdPartyPlugin($I, 'fusion-core');
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
	}

	/**
	 * Test that restricting content by a Product specified in the Page Settings works when
	 * creating and viewing a new WordPress Page using the Uncode theme both using
	 * the Visual Composer Page Builder and not using it.
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

		// Programmatically create a Page without using the Visual Composer Page Builder.
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
