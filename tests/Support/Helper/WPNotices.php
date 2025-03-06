<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to WordPress' Admin Notices,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class WPNotices extends \Codeception\Module
{
	/**
	 * Confirms that an error notification is output with the given text.
	 *
	 * @since   2.0.9
	 *
	 * @param   EndToEndTester $I              EndToEnd Tester.
	 * @param   string         $message        Message.
	 */
	public function seeErrorNotice($I, $message)
	{
		$I->see($message, 'div.notice-error');
	}

	/**
	 * Confirms that an error notification is not output with the given text.
	 *
	 * @since   2.0.9
	 *
	 * @param   EndToEndTester $I              EndToEnd Tester.
	 * @param   string         $message        Message.
	 */
	public function dontSeeErrorNotice($I, $message)
	{
		$I->dontSee($message, 'div.notice-error');
	}
}
