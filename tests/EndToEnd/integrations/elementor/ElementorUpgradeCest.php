<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Plugin's upgrade routines when Elementor is active.
 *
 * @since   3.5.4
 */
class ElementorUpgradeCest
{
	/**
	 * Tests that Elementor's CSS cache is cleared when upgrading from a version older than 3.5.4,
	 * so Elementor outputs the colors set in Kit widgets as CSS.
	 *
	 * @since   3.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testElementorCSSCacheClearedForWidgetColors(EndToEndTester $I)
	{
		// Activate Elementor.
		$I->activateThirdPartyPlugin($I, 'elementor');

		// Setup the Kit Plugin, with an installation version older than 3.5.4.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
		$I->haveOptionInDatabase('convertkit_version', '3.5.2');

		// Define the Form Trigger widget's settings.
		$settings = [
			'form'             => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
			'text'             => 'Subscribe',
			'background_color' => '#ee1616',
			'text_color'       => '#1212c0',
		];

		// Create a Page with a Form Trigger widget in Elementor, with Elementor's CSS cache
		// generated before 3.5.4, when the widget's colors were output as inline styles.
		$pageID = $I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Page: Elementor CSS Cache: 3.5.2',
				'post_type'   => 'page',
				'post_status' => 'publish',
				'meta_input'  => [
					'_elementor_data'          => [
						0 => [
							'id'       => '39bb59e',
							'elType'   => 'section',
							'settings' => [],
							'elements' => [
								[
									'id'       => 'b7e0e58',
									'elType'   => 'column',
									'settings' => [
										'_column_size' => 100,
										'_inline_size' => null,
									],
									'elements' => [
										[
											'id'         => 'a73a906',
											'elType'     => 'widget',
											'settings'   => $settings,
											'widgetType' => 'convertkit-elementor-formtrigger',
										],
									],
								],
							],
						],
					],
					'_elementor_version'       => '3.6.1',
					'_elementor_edit_mode'     => 'builder',
					'_elementor_template_type' => 'wp-page',
					'_elementor_css'           => [
						'time'                 => time(),
						'fonts'                => [],
						'icons'                => [],
						'dynamic_elements_ids' => [],
						'status'               => 'empty',
						'css'                  => '',
					],
					'_wp_convertkit_post_meta' => [
						'form'         => '0',
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Activate the Plugin, which runs the upgrade routine.
		$I->activateKitPlugin($I, false);

		// Confirm Elementor's CSS cache was cleared for the Page.
		$I->dontSeePostMetaInDatabase(
			[
				'post_id'  => $pageID,
				'meta_key' => '_elementor_css',
			]
		);

		// Load the Page, and confirm Elementor applies the colors.
		$I->amOnPage('?p=' . $pageID);
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->seeElementorWidgetColors(
			$I,
			selector: 'a.convertkit-formtrigger',
			textColor: '#1212c0',
			backgroundColor: '#ee1616'
		);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'elementor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
