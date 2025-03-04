<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to PHP's Xdebug,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class Xdebug extends \Codeception\Module
{
	/**
	 * Helper method to assert that there are non PHP errors, warnings or notices output
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I Acceptance Tester.
	 */
	public function checkNoWarningsAndNoticesOnScreen($I)
	{
		// Check that no Xdebug errors exist.
		$I->dontSeeElement('.xdebug-error');
		$I->dontSeeElement('.xe-notice');
	}
}
