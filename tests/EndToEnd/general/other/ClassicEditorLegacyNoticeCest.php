<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests the non-dismissible legacy-resource warning notice output by
 * ConvertKit_Admin_Legacy_Resource_Notice on the Classic editor's post
 * edit screen.
 *
 * @since   3.3.7
 */
class ClassicEditorLegacyNoticeCest
{
	/**
	 * The CSS selector matching the warning notice on the classic editor.
	 *
	 * @since   3.3.7
	 *
	 * @var     string
	 */
	private $noticeSelector = 'div.notice.notice-warning';

	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'classic-editor');
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that a page with a legacy Form assignment displays the warning
	 * notice on the classic editor, listing the legacy form by name.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoticeDisplaysForLegacyForm(EndToEndTester $I)
	{
		// Create a page with a Legacy Form assignment.
		$pageID = $I->havePageInDatabase(
			[
				'post_status' => 'publish',
				'post_title'  => 'Kit: Legacy Notice: Form',
				'meta_input'  => [
					'_wp_convertkit_post_meta' => [
						'form'         => (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Navigate to the page and wait for the notice to appear.
		$I->amOnAdminPage('post.php?post=' . $pageID . '&action=edit');
		$I->seeElementInDOM($this->noticeSelector);

		// Assert the notice displays the legacy form by name.
		$I->see('Form: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'], $this->noticeSelector);

		// Assert the notice does not have a dismiss button.
		$I->dontSeeElementInDOM($this->noticeSelector . ' button.notice-dismiss');
	}

	/**
	 * Test that a page with a legacy Landing Page assignment displays the
	 * warning notice on the classic editor, listing the legacy landing
	 * page by name.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoticeDisplaysForLegacyLandingPage(EndToEndTester $I)
	{
		// Create a page with a Legacy Landing Page assignment.
		$pageID = $I->havePageInDatabase(
			[
				'post_status' => 'publish',
				'post_title'  => 'Kit: Legacy Notice: Landing Page',
				'meta_input'  => [
					'_wp_convertkit_post_meta' => [
						'form'         => '0',
						'landing_page' => (string) $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID'],
						'tag'          => '',
					],
				],
			]
		);

		// Navigate to the page and wait for the notice to appear.
		$I->amOnAdminPage('post.php?post=' . $pageID . '&action=edit');
		$I->seeElementInDOM($this->noticeSelector);

		// Assert the notice displays the legacy landing page by name.
		$I->see('Landing Page: ' . $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_NAME'], $this->noticeSelector);

		// Assert the notice does not have a dismiss button.
		$I->dontSeeElementInDOM($this->noticeSelector . ' button.notice-dismiss');
	}

	/**
	 * Test that a page with a Restrict Content assignment referencing a
	 * legacy Kit form displays the warning notice on the classic editor,
	 * scoped to Member Content.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoticeDisplaysForLegacyRestrictContentForm(EndToEndTester $I)
	{
		// Create a page with a Restrict Content assignment referencing a
		// legacy Kit form.
		$pageID = $I->havePageInDatabase(
			[
				'post_status' => 'publish',
				'post_title'  => 'Kit: Legacy Notice: Restrict Content',
				'meta_input'  => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'form_' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
					],
				],
			]
		);

		// Navigate to the page and wait for the notice to appear.
		$I->amOnAdminPage('post.php?post=' . $pageID . '&action=edit');
		$I->seeElementInDOM($this->noticeSelector);

		// Assert the notice displays the legacy form by name.
		$I->see('Member Content: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'], $this->noticeSelector);

		// Assert the notice does not have a dismiss button.
		$I->dontSeeElementInDOM($this->noticeSelector . ' button.notice-dismiss');
	}

	/**
	 * Test that a page assigning only v4 Kit resources does not display
	 * the legacy warning notice.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoticeDoesNotDisplayForV4Resources(EndToEndTester $I)
	{
		// Create a page assigning only v4 Kit resources.
		$pageID = $I->havePageInDatabase(
			[
				'post_status' => 'publish',
				'post_title'  => 'Kit: Legacy Notice: V4 Only',
				'meta_input'  => [
					'_wp_convertkit_post_meta' => [
						'form'         => (string) $_ENV['CONVERTKIT_API_FORM_ID'],
						'landing_page' => (string) $_ENV['CONVERTKIT_API_LANDING_PAGE_ID'],
						'tag'          => '',
					],
				],
			]
		);

		// Navigate to the page and wait for the notice to appear.
		$I->amOnAdminPage('post.php?post=' . $pageID . '&action=edit');

		// Assert the notice is not present.
		$I->dontSee('The Kit settings for this post reference legacy resources');
	}

	/**
	 * Test that a page with legacy form + legacy landing page + legacy
	 * Member Content assignments displays a single consolidated warning
	 * notice with all three items listed.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testConsolidatedNoticeWhenMultipleLegacyResources(EndToEndTester $I)
	{
		// Create a page with legacy form + legacy landing page + legacy
		// Member Content assignments.
		$pageID = $I->havePageInDatabase(
			[
				'post_status' => 'publish',
				'post_title'  => 'Kit: Legacy Notice: Consolidated',
				'meta_input'  => [
					'_wp_convertkit_post_meta' => [
						'form'             => (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
						'landing_page'     => (string) $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID'],
						'tag'              => '',
						'restrict_content' => 'form_' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
					],
				],
			]
		);

		// Navigate to the page and wait for the notice to appear.
		$I->amOnAdminPage('post.php?post=' . $pageID . '&action=edit');

		// Assert the notice displays all three legacy resources.
		$I->seeNumberOfElementsInDOM($this->noticeSelector, 1);
		$I->see('Form: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'], $this->noticeSelector);

		// Assert the notice displays the legacy landing page by name.
		$I->see('Landing Page: ' . $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_NAME'], $this->noticeSelector);
		$I->see('Member Content: ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'], $this->noticeSelector);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
