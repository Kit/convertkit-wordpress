<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Admin_Legacy_Resource_Notice class.
 *
 * @since   3.3.7
 */
class AdminLegacyResourceNoticeTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the notice class.
	 *
	 * @since   3.3.7
	 *
	 * @var     \ConvertKit_Admin_Legacy_Resource_Notice
	 */
	private $notice;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.3.7
	 */
	public function setUp(): void
	{
		parent::setUp();

		activate_plugins('convertkit/wp-convertkit.php');

		// Store credentials in Plugin's settings.
		$settings = new \ConvertKit_Settings();
		update_option(
			$settings::SETTINGS_NAME,
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		// Refresh Forms and Landing Pages resources so is_legacy() calls resolve
		// correctly against the API-backed cache.
		$forms = new \ConvertKit_Resource_Forms();
		$forms->refresh();

		$landing_pages = new \ConvertKit_Resource_Landing_Pages();
		$landing_pages->refresh();

		$this->notice = new \ConvertKit_Admin_Legacy_Resource_Notice();
	}

	/**
	 * Test that a settings array with only v4 resource assignments returns
	 * no warnings.
	 *
	 * @since   3.3.7
	 */
	public function testNoWarningsForV4Resources()
	{
		$warnings = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'form'             => $_ENV['CONVERTKIT_API_FORM_ID'],
				'landing_page'     => $_ENV['CONVERTKIT_API_LANDING_PAGE_ID'],
				'restrict_content' => 'form_' . $_ENV['CONVERTKIT_API_FORM_ID'],
			]
		);

		$this->assertSame([], $warnings);
	}

	/**
	 * Test that a legacy form assignment produces a form-scoped warning
	 * containing the form's name.
	 *
	 * @since   3.3.7
	 */
	public function testWarningForLegacyForm()
	{
		$warnings = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'form' => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);

		$this->assertCount(1, $warnings);
		$this->assertStringContainsString('Form:', $warnings[0]);
		$this->assertStringContainsString($_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'], $warnings[0]);
	}

	/**
	 * Test that a legacy landing page assignment (by numeric ID) produces
	 * a landing-page-scoped warning containing the landing page's name.
	 *
	 * @since   3.3.7
	 */
	public function testWarningForLegacyLandingPageByID()
	{
		$warnings = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'landing_page' => $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID'],
			]
		);

		$this->assertCount(1, $warnings);
		$this->assertStringContainsString('Landing Page:', $warnings[0]);
		$this->assertStringContainsString($_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_NAME'], $warnings[0]);
	}

	/**
	 * Test that a legacy landing page assignment stored as a URL string
	 * (pre-1.9.6 storage) produces a landing-page-scoped warning using the
	 * fallback label since we can't resolve a name from the URL alone.
	 *
	 * @since   3.3.7
	 */
	public function testWarningForLegacyLandingPageByURL()
	{
		$warnings = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'landing_page' => $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_URL'],
			]
		);

		$this->assertCount(1, $warnings);
		$this->assertStringContainsString('Landing Page:', $warnings[0]);
		$this->assertStringContainsString('a Legacy Landing Page', $warnings[0]);
	}

	/**
	 * Test that a restrict_content value of `form_<legacy_id>` produces a
	 * Member Content warning.
	 *
	 * @since   3.3.7
	 */
	public function testWarningForLegacyRestrictContentForm()
	{
		$warnings = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'restrict_content' => 'form_' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);

		$this->assertCount(1, $warnings);
		$this->assertStringContainsString('Member Content:', $warnings[0]);
		$this->assertStringContainsString($_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'], $warnings[0]);
	}

	/**
	 * Test that restrict_content values of `tag_<id>` and `product_<id>`
	 * never trigger a warning, because tags and products have no legacy
	 * variant.
	 *
	 * @since   3.3.7
	 */
	public function testNoWarningForLegacyRestrictContentTagOrProduct()
	{
		$tag     = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'restrict_content' => 'tag_' . $_ENV['CONVERTKIT_API_TAG_ID'],
			]
		);
		$product = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'restrict_content' => 'product_1',
			]
		);

		$this->assertSame([], $tag);
		$this->assertSame([], $product);
	}

	/**
	 * Test that a settings array with legacy form + legacy landing page +
	 * legacy restrict_content form produces three warnings in a single
	 * returned array.
	 *
	 * @since   3.3.7
	 */
	public function testWarningsForMultipleLegacyResources()
	{
		$warnings = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'form'             => $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
				'landing_page'     => $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID'],
				'restrict_content' => 'form_' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);

		$this->assertCount(3, $warnings);
	}

	/**
	 * Test that when a legacy form ID doesn't resolve to any cached resource
	 * (e.g. the form was deleted from the Kit account), the warning falls
	 * back to "a Legacy Form" rather than omitting the entry.
	 *
	 * @since   3.3.7
	 */
	public function testWarningWithMissingResourceFallsBackToLabel()
	{
		// Wipe the Forms cache so is_legacy() can no longer resolve any
		// numeric ID to a real resource. is_legacy() will therefore return
		// false for numeric IDs — but a URL-based landing page assignment
		// still resolves as legacy by the URL-string check, exercising the
		// name-fallback path.
		delete_option('convertkit_forms');
		delete_option('convertkit_landing_pages');

		$warnings = $this->notice->get_legacy_warnings_for_post_settings(
			[
				'landing_page' => $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_URL'],
			]
		);

		$this->assertCount(1, $warnings);
		$this->assertStringContainsString('a Legacy Landing Page', $warnings[0]);
	}
}
