<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for any output errors on a clean installation and activation,
 * with no Plugin configuration.
 *
 * @since   1.9.6
 */
class AnyErrorsOnBlankInstallCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
	}

	/**
	 * Check that no PHP errors or notices are displayed on the Plugin's Settings > General screen when the Plugin is activated
	 * and not configured.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testSettingsGeneralScreen(EndToEndTester $I)
	{
		// Go to the Plugin's Settings > General Screen.
		$I->loadKitSettingsGeneralScreen($I);
	}

	/**
	 * Check that no PHP errors or notices are displayed on the Plugin's Setting > Tools screen when the Plugin is activated
	 * and not configured.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testSettingsToolsScreen(EndToEndTester $I)
	{
		// Go to the Plugin's Settings > Tools Screen.
		$I->loadKitSettingsToolsScreen($I);
	}

	/**
	 * Check that no errors are displayed on Pages > Add New, when the Plugin is activated
	 * and not configured.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPage(EndToEndTester $I)
	{
		// Navigate to Pages > Add New.
		$I->amOnAdminPage('post-new.php?post_type=page');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Check that no errors are displayed on Posts > Add New, when the Plugin is activated
	 * and not configured.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPost(EndToEndTester $I)
	{
		// Navigate to Pages > Add New.
		$I->amOnAdminPage('post-new.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Check that no errors are displayed on Posts > Categories > Edit Uncategorized, when the Plugin is activated
	 * and not configured.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEditCategory(EndToEndTester $I)
	{
		// Navigate to Posts > Categories > Edit Uncategorized.
		$I->amOnAdminPage('term.php?taxonomy=category&tag_ID=1');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
