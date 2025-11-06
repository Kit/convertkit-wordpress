<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to WordPress Caching Plugins,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   2.5.7
 */
class DiviBuilder extends \Codeception\Module
{
	/**
	 * Helper method to create a Divi Page in the WordPress Administration interface.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 * @param   string         $title  Page Title.
	 */
	public function createDiviPageInBackendEditor($I, $title)
	{
		// Activate Classic Editor Plugin.
		$I->activateThirdPartyPlugin($I, 'classic-editor');

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage($I, 'page', $title);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Scroll to Publish meta box, so its buttons are not hidden.
		$I->scrollTo('#submitdiv');

		// Wait for the Publish button to change its state from disabled (WordPress disables it for a moment when auto-saving).
		$I->waitForElementVisible('input#publish:not(:disabled)');

		// Click the Publish button twice, because Divi is flaky at best.
		$I->click('input#publish');
		$I->wait(2);
		$I->click('input#publish');

		// Wait for notice to display.
		$I->waitForElementNotVisible('.et-fb-preloader');
		$I->waitForElementVisible('.notice-success');
		$I->wait(2);

		// Remove transient set by Divi that would show the welcome modal.
		$I->dontHaveTransientInDatabase('et_builder_show_bfb_welcome_modal');

		// Click Divi Builder button.
		$I->click('#et_pb_toggle_builder');

		// Dismiss modal if displayed.
		// May have been dismissed by other tests in the suite e.g. DiviFormCest.
		try {
			$I->waitForElementVisible('.et-core-modal-action-dont-restore');
			$I->click('.et-core-modal-action-dont-restore');
		} catch ( \Facebook\WebDriver\Exception\NoSuchElementException $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// No modal exists, so nothing to dismiss.
		}

		// Click Build from scratch button.
		$I->waitForElementVisible('.et-fb-page-creation-card-build_from_scratch');
		$I->click('Start Building', '.et-fb-page-creation-card-build_from_scratch');
	}

	/**
	 * Helper method to create a Divi Page in the Frontend interface.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I                 EndToEnd Tester.
	 * @param   string         $title             Page Title.
	 * @param   bool           $configureMetaBox  Configure Plugin's Meta Box to set Form = None (set to false if running a test with no credentials).
	 * @return  string                              Page URL.
	 */
	public function createDiviPageInFrontendEditor($I, $title, $configureMetaBox = true)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', $title);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		if ($configureMetaBox) {
			$I->configureMetaboxSettings(
				$I,
				'wp-convertkit-meta-box',
				[
					'form' => [ 'select2', 'None' ],
				]
			);
		}

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Click Divi Builder button inside the Gutenberg editor.
		$I->click('Use Divi Builder');

		// Reload page to dismiss modal.
		$I->wait(5);
		$I->amOnUrl($url . '?et_fb=1&PageSpeed=off');

		// Click Build from scratch button.
		$I->waitForElementVisible('.et-fb-page-creation-card-build_from_scratch', 30);
		$I->click('Start Building', '.et-fb-page-creation-card-build_from_scratch');

		return $url;
	}

	/**
	 * Helper method to insert a given Divi module in to a page edited with either the
	 * backend or frontend editor, with the supplied configuration.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I                 EndToEnd Tester.
	 * @param   string         $name              Module Name.
	 * @param   string         $programmaticName  Programmatic Module Name.
	 * @param   bool|string    $fieldName         Field Name.
	 * @param   bool|string    $fieldValue        Field Value.
	 */
	public function insertDiviRowWithModule($I, $name, $programmaticName, $fieldName = false, $fieldValue = false)
	{
		// Insert row.
		$I->waitForElementVisible('li[data-layout="4_4"]');
		$I->click('li[data-layout="4_4"]');

		// Search for module.
		$I->waitForElementVisible('input[name="filterByTitle"]');
		$I->fillField('filterByTitle', $name);

		// Insert module.
		$I->waitForElementVisible('li.' . $programmaticName);
		$I->wait(2);
		$I->click('li.' . $programmaticName);

		// Wait for module to load.
		$I->waitForElementVisible('div.et-fb-modal');

		// Select field value.
		if ($fieldName && $fieldValue) {
			$I->waitForElementVisible('#et-fb-' . $fieldName);
			$I->click('#et-fb-' . $fieldName);
			$I->click('li[data-value="' . $fieldValue . '"]', '#et-fb-' . $fieldName);
		}
	}

	/**
	 * Helper method to save the Divi module added using insertDiviRowWithModule() in the backend editor, saving
	 * the WordPress Page and viewing it on the frontend site.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function saveDiviModuleInBackendEditorAndViewPage($I)
	{
		// Save module.
		$I->waitForElementVisible('button[data-tip="Save Changes"]');
		$I->click('button[data-tip="Save Changes"]');

		// Update page.
		$I->click('Update');

		// Wait for the save to complete.
		$I->waitForElementVisible('#message');
		$I->see('Page updated.', '#message');
		$I->waitForElementNotVisible('.et-fb-preloader');

		// Load the Page on the frontend site.
		$I->click('.notice-success a');
		$I->waitForElementVisible('body');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Helper method to save the Divi module added using insertDiviRowWithModule() in the frontend editor, saving
	 * the WordPress Page and viewing it on the frontend site.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 * @param   string         $url    Page URL.
	 */
	public function saveDiviModuleInFrontendEditorAndViewPage($I, $url)
	{
		// Save module.
		$I->click('button[data-tip="Save Changes"]');

		// Save page.
		$I->click('.et-fb-page-settings-bar__toggle-button');
		$I->waitForElementVisible('button.et-fb-button--publish');
		$I->click('button.et-fb-button--publish');
		$I->wait(3);

		// Load page without Divi frontend builder.
		$I->amOnUrl($url);

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Helper method to see specific text is displayed in a Divi module.
	 *
	 * @since   3.0.8
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 * @param   string         $title  Title of the module.
	 * @param   string         $text   Text to confirm is displayed.
	 */
	public function seeTextInDiviModule($I, $title, $text)
	{
		// Switch to Divi Builder iframe.
		$I->switchToIFrame('iframe[id="et-fb-app-frame"]');

		// Confirm the on screen message displays.
		$I->waitForElementVisible('div.convertkit-divi-module');
		$I->see($title, 'div.convertkit-divi-module');
		$I->see($text, 'div.convertkit-divi-module');

		// Switch back to main window.
		$I->switchToIFrame();
	}

	/**
	 * Create a Page in the database comprising of Divi Page Builder data
	 * containing a Kit module.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I                 Tester.
	 * @param   string         $title             Page Title.
	 * @param   string         $programmaticName  Programmatic Module Name.
	 * @param   string         $fieldName         Field Name.
	 * @param   string         $fieldValue        Field Value.
	 * @return  int                                 Page ID
	 */
	public function createPageWithDiviModuleProgrammatically($I, $title, $programmaticName, $fieldName, $fieldValue)
	{
		return $I->havePostInDatabase(
			[
				'post_title'   => $title,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '[et_pb_section fb_built="1" _builder_version="4.27.0" _module_preset="default" global_colors_info="{}"]
					[et_pb_row _builder_version="4.27.0" _module_preset="default"]
						[et_pb_column _builder_version="4.27.0" _module_preset="default" type="4_4"]
							[' . $programmaticName . ' _builder_version="4.27.0" _module_preset="default" ' . $fieldName . '="' . $fieldValue . '" hover_enabled="0" sticky_enabled="0"][/' . $programmaticName . ']
						[/et_pb_column]
					[/et_pb_row]
				[/et_pb_section]',
				'meta_input'   => [
					// Enable Divi Builder.
					'_et_pb_use_builder'         => 'on',
					'_et_pb_built_for_post_type' => 'page',

					// Configure Kit Plugin to not display a default Form,
					// as we are testing for the Form in Elementor.
					'_wp_convertkit_post_meta'   => [
						'form'         => '0',
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);
	}
}
