<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form widget.
 *
 * A widget area is typically defined by a Theme in a shared area, such as a sidebar or footer.
 *
 * We test both legacy widgets (registered in includes/widgets/class-ck-widget-form.php),
 * and the Gutenberg Form Block, which can be inserted into a widget area starting from
 * WordPress 5.8.
 *
 * @since   1.9.7.6
 */
class WidgetFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Activate an older WordPress Theme that supports Widgets.
		$I->useTheme('twentytwentyone');
	}

	/**
	 * Test that the legacy Form widget works when a valid Form is selected.
	 *
	 * We retain this legacy non-block widget, because it's been available since 1.4.3,
	 * and there is no smooth conversion path to making legacy widgets into widget blocks
	 * for WordPress 5.8+.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testLegacyFormWidgetWithValidFormParameter(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'page_form'  => '',
				'post_form'  => '',
			]
		);

		// Add legacy widget, setting the Form setting to the value specified in the .env file.
		$I->addLegacyWidget(
			$I,
			blockName: 'Kit Form (Legacy Widget)',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Confirm that the widget displays.
		$I->seeLegacyWidget($I, 'convertkit-form', 'form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]');
	}

	/**
	 * Test that the legacy Form widget works when a valid Legacy Form is selected.
	 *
	 * We retain this legacy non-block widget, because it's been available since 1.4.3,
	 * and there is no smooth conversion path to making legacy widgets into widget blocks
	 * for WordPress 5.8+.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testLegacyFormWidgetWithValidLegacyFormParameter(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'page_form'  => '',
				'post_form'  => '',
			]
		);

		// Add legacy widget, setting the Form setting to the value specified in the .env file.
		$I->addLegacyWidget(
			$I,
			blockName: 'Kit Form (Legacy Widget)',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] ],
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that the widget displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the Form widget works when a valid Form is selected.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBlockFormWidgetWithValidFormParameter(EndToEndTester $I)
	{
		// Add block widget, setting the Form setting to the value specified in the .env file.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Confirm that the widget displays.
		$I->seeBlockWidget($I, 'form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]');
	}

	/**
	 * Test the Form widget works when a valid Legacy Form is selected.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBlockFormBlockWithValidLegacyFormParameter(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'    => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret' => $_ENV['CONVERTKIT_API_SECRET'],
				'page_form'  => '',
				'post_form'  => '',
			]
		);

		// Add block widget, setting the Form setting to the value specified in the .env file.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] ],
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that the widget displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the Form widget displays a message explaining why the block cannot be previewed
	 * when a valid Modal Form is selected.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormWidgetWithValidModalFormParameter(EndToEndTester $I)
	{
		// Add block widget, setting the Form setting to the value specified in the .env file.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'] ],
			]
		);

		// Switch to iframe preview for the Form block.
		$I->switchToIFrame('iframe[class="components-sandbox"]');

		// Confirm that the Form block iframe sandbox preview displays that the Modal form was selected, and to view the frontend
		// site to see it (we cannot preview Modal forms in the Gutenberg editor due to Gutenberg using an iframe).
		$I->see('Modal form "' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME_ONLY'] . '" selected. View on the frontend site to see the modal form.');

		// Switch back to main window.
		$I->switchToIFrame();

		// Confirm that the widget displays.
		$I->seeBlockWidget($I, 'form[data-sv-form]');
	}

	/**
	 * Test the Form widget displays a message explaining why the block cannot be previewed
	 *  when a valid Slide In Form is selected.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBlockFormWidgetWithValidSlideInFormParameter(EndToEndTester $I)
	{
		// Add block widget, setting the Form setting to the value specified in the .env file.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_SLIDE_IN_NAME'] ],
			]
		);

		// Switch to iframe preview for the Form block.
		$I->switchToIFrame('iframe[class="components-sandbox"]');

		// Confirm that the Form block iframe sandbox preview displays that the Modal form was selected, and to view the frontend
		// site to see it (we cannot preview Modal forms in the Gutenberg editor due to Gutenberg using an iframe).
		$I->see('Slide in form "' . $_ENV['CONVERTKIT_API_FORM_FORMAT_SLIDE_IN_NAME_ONLY'] . '" selected. View on the frontend site to see the slide in form.');

		// Switch back to main window.
		$I->switchToIFrame();

		// Confirm that the widget displays.
		$I->seeBlockWidget($I, 'form[data-sv-form]');
	}

	/**
	 * Test the Form widget displays a message explaining why the block cannot be previewed
	 * when a valid Sticky Bar Form is selected.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBlockFormWidgetWithValidStickyBarFormParameter(EndToEndTester $I)
	{
		// Add block widget, setting the Form setting to the value specified in the .env file.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME'] ],
			]
		);

		// Switch to iframe preview for the Form block.
		$I->switchToIFrame('iframe[class="components-sandbox"]');

		// Confirm that the Form block iframe sandbox preview displays that the Modal form was selected, and to view the frontend
		// site to see it (we cannot preview Modal forms in the Gutenberg editor due to Gutenberg using an iframe).
		$I->see('Sticky bar form "' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME_ONLY'] . '" selected. View on the frontend site to see the sticky bar form.');

		// Switch back to main window.
		$I->switchToIFrame();

		// Confirm that the widget displays.
		$I->seeBlockWidget($I, 'form[data-sv-form]');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		// Activate the current theme.
		$I->useTheme('twentytwentytwo');
		$I->resetWidgets($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
