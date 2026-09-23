<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Member Content Login Elementor Widget.
 *
 * @since   3.4.4
 */
class ElementorMemberContentLoginCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'elementor');
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test the Member Content Login widget is registered in Elementor.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginWidgetIsRegistered(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Member Content Login: Elementor: Registered'
		);

		// Click Edit with Elementor button.
		$I->click('#elementor-switch-mode-button');

		// Wait for Elementor to load, as its loading overlay covers the widget panel,
		// which makes the panel's search field not interactable.
		$I->waitForElementVisible('#elementor-preview-iframe');
		$I->waitForElementNotVisible('#elementor-loading');

		// Search for the Kit Member Content Login block.
		$I->waitForElementClickable('#elementor-panel-elements-search-input');
		$I->fillField('#elementor-panel-elements-search-input', 'Kit Member Content Login');

		// Confirm that the Member Content Login widget is displayed as an option.
		$I->seeElementInDOM('#elementor-panel-elements .elementor-element');
	}

	/**
	 * Test the Member Content Login widget displays the login form, and the logged in
	 * text and log out button once the subscriber is logged in.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginWidget(EndToEndTester $I)
	{
		// Create Page with Member Content Login widget in Elementor.
		$pageID = $this->_createPageWithMemberContentLoginWidget(
			$I,
			title: 'Kit: Page: Member Content Login: Elementor Widget',
			settings: [
				'logged_in_text'      => 'You are signed in',
				'logout_button_label' => 'Sign out',
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the login form is displayed.
		$I->seeElementInDOM('input#convertkit_email');
		$I->dontSeeElementInDOM('#convertkit-restrict-content-modal');

		// Log in as a Kit subscriber, as if we entered the code sent in the email.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);
		$I->reloadPage();

		// Confirm the logged in text and log out button are displayed.
		$I->waitForElementVisible('a.convertkit-restrict-content-logout');
		$I->see('You are signed in');
		$I->see('Sign out', 'a.convertkit-restrict-content-logout');

		// Log out.
		$I->click('a.convertkit-restrict-content-logout');

		// Confirm the login form is displayed, and the subscriber is logged out.
		$I->waitForElementVisible('input#convertkit_email');
		$I->dontSee('You are signed in');
		$I->dontSeeCookie('ck_subscriber_id');
	}

	/**
	 * Create a Page in the database comprising of Elementor Page Builder data
	 * containing a Kit Member Content Login widget.
	 *
	 * Codeception's dragAndDrop() method doesn't support dropping an element into an iframe, which is
	 * how Elementor works for adding widgets to a Page.
	 *
	 * Therefore, we directly create a Page in the database, with Elementor's data structure
	 * as if we added the Member Content Login widget to a Page edited in Elementor.
	 *
	 * testMemberContentLoginWidgetIsRegistered() above is a sanity check that the widget is registered
	 * and available to users in Elementor.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I          Tester.
	 * @param   string         $title      Page Title.
	 * @param   array          $settings   Widget settings.
	 * @return  int                             Page ID
	 */
	private function _createPageWithMemberContentLoginWidget(EndToEndTester $I, $title, $settings)
	{
		return $I->havePostInDatabase(
			[
				'post_title'  => $title,
				'post_type'   => 'page',
				'post_status' => 'publish',
				'meta_input'  => [
					// Elementor.
					'_elementor_data'          => [
						0 => [
							'id'       => '39bb59d',
							'elType'   => 'section',
							'settings' => [],
							'elements' => [
								[
									'id'       => 'b7e0e57',
									'elType'   => 'column',
									'settings' => [
										'_column_size' => 100,
										'_inline_size' => null,
									],
									'elements' => [
										[
											'id'         => 'a73a905',
											'elType'     => 'widget',
											'settings'   => $settings,
											'widgetType' => 'convertkit-elementor-login',
										],
									],
								],
							],
						],
					],
					'_elementor_version'       => '3.6.1',
					'_elementor_edit_mode'     => 'builder',
					'_elementor_template_type' => 'wp-page',

					// Configure Kit Plugin to not display a default Form,
					// as we are testing for the Member Content Login widget in Elementor.
					'_wp_convertkit_post_meta' => [
						'form'         => '0',
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->clearRestrictContentCookie($I);
		$I->deactivateThirdPartyPlugin($I, 'elementor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
