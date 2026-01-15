<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Admin_Importer class.
 *
 * @since   3.1.0
 */
class ImporterTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.1.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.1.0
	 */
	public function tearDown(): void
	{
		// Destroy the class we tested.
		unset($this->importer);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the get_form_ids_from_content() method returns AWeber form shortcode Form IDs
	 * ignoring any other shortcodes.
	 *
	 * @since   3.1.5
	 */
	public function testGetAWeberFormIDsFromContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_AWeber();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the content to test.
		$content = '[aweber formid="10"] some content [aweber formid="11"] some other content [mc4wp_form id="12"] different shortcode to ignore';

		// Extract form IDs from content.
		$form_ids = $this->importer->get_form_ids_from_content( $content );

		// Assert the correct number of form IDs are returned.
		$this->assertEquals( 2, count( $form_ids ) );
		$this->assertEquals( 10, $form_ids[0] );
		$this->assertEquals( 11, $form_ids[1] );
	}

	/**
	 * Test that the replace_shortcodes_in_content() method replaces the third party form shortcode with the Kit form shortcode.
	 *
	 * @since   3.1.5
	 */
	public function testAWeberReplaceShortcodesInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_AWeber();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[aweber formid=10]',
			'[aweber formid="10"]',
			'[aweber formid=10 listid=11]',
			'[aweber formid="10" listid="11"]',
			'[aweber formid=10 listid=11 formtype=webform]',
			'[aweber formid="10" listid="11" formtype="webform"]',
			'[aweber listid=11 formid=10]',
			'[aweber listid="11" formid="10"]',
			'[aweber listid=11 formid=10 formtype=webform]',
			'[aweber listid="11" formid="10" formtype="webform"]',
			'[aweber formtype=webform listid=11 formid=10]',
			'[aweber formtype="webform" listid="11" formid="10"]',
		];

		// Test each shortcode is replaced with the Kit form shortcode.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				'[convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $content, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the replace_shortcodes_in_content() method ignores non-AWeber shortcodes.
	 *
	 * @since   3.1.5
	 */
	public function testAWeberReplaceShortcodesInContentIgnoringOtherShortcodes()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_AWeber();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
			'[a_random_shortcode]',
		];

		// Test each shortcode is ignored.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				$shortcode,
				$this->importer->replace_shortcodes_in_content( $shortcode, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the get_form_ids_from_content() method returns MC4WP form shortcode Form IDs
	 * ignoring any other shortcodes.
	 *
	 * @since   3.1.5
	 */
	public function testGetMC4WPFormIDsFromContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_MC4WP();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the content to test.
		$content = '[mc4wp_form id="10"] some content [mc4wp_form id="11"] some other content [aweber formid="12"] different shortcode to ignore';

		// Extract form IDs from content.
		$form_ids = $this->importer->get_form_ids_from_content( $content );

		// Assert the correct number of form IDs are returned.
		$this->assertEquals( 2, count( $form_ids ) );
		$this->assertEquals( 10, $form_ids[0] );
		$this->assertEquals( 11, $form_ids[1] );
	}

	/**
	 * Test that the replace_shortcodes_in_content() method replaces the third party form shortcode with the Kit form shortcode.
	 *
	 * @since   3.1.0
	 */
	public function testMC4WPReplaceShortcodesInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_MC4WP();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test based on https://www.mc4wp.com/kb/arguments-in-form-shortcode/.
		$shortcodes = [
			'[mc4wp_form id="10"]',
			'[mc4wp_form id=10]',
			'[mc4wp_form id="10" element_id="custom-id"]',
			'[mc4wp_form id=10 element_id=custom-id]',
			'[mc4wp_form id="10" lists="a-list-id,another-list-id"]',
			'[mc4wp_form id=10 lists=a-list-id,another-list-id]',
			'[mc4wp_form element_id="custom-id" id="10"]',
			'[mc4wp_form element_id=custom-id id=10]',
			'[mc4wp_form lists="a-list-id,another-list-id" id="10"]',
			'[mc4wp_form lists=a-list-id,another-list-id id=10]',
		];

		// Test each shortcode is replaced with the Kit form shortcode.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				'[convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $content, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the replace_shortcodes_in_content() method ignores non-MC4WP shortcodes.
	 *
	 * @since   3.1.0
	 */
	public function testMC4WPReplaceShortcodesInContentIgnoringOtherShortcodes()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_MC4WP();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
			'[a_random_shortcode]',
		];

		// Test each shortcode is ignored.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				$shortcode,
				$this->importer->replace_shortcodes_in_content( $shortcode, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}
}
