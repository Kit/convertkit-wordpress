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
	 * Holds the HTML block to test, which includes special characters.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	private $html_block = '<!-- wp:html --><div class="wp-block-core-html">Some content with characters !@£$%^&amp;*()_+~!@£$%^&amp;*()_+\</div><!-- /wp:html -->';

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
	 * Test that the get_form_ids_from_content() method returns ActiveCampaign form shortcode Form IDs
	 * ignoring any other shortcodes.
	 *
	 * @since   3.1.7
	 */
	public function testActiveCampaignGetFormIDsFromContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ActiveCampaign();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the content to test.
		$content = '[activecampaign form="10"] some content [activecampaign form="11"] some other content [aweber formid="12"] different shortcode to ignore';

		// Extract form IDs from content.
		$form_ids = $this->importer->get_form_ids_from_content( $content );

		// Assert the correct number of form IDs are returned.
		$this->assertEquals( 2, count( $form_ids ) );
		$this->assertEquals( 10, $form_ids[0] );
		$this->assertEquals( 11, $form_ids[1] );
	}

	/**
	 * Test that the replace_shortcodes_in_content() method replaces the ActiveCampaign form shortcode with the Kit form shortcode.
	 *
	 * @since   3.1.7
	 */
	public function testActiveCampaignReplaceShortcodesInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ActiveCampaign();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[activecampaign form="1"]',
			'[activecampaign form=1]',
			'[activecampaign form="1" css="1"]',
			'[activecampaign form=1 css=1]',
			'[activecampaign css="1" form="1"]',
			'[activecampaign css=1 form=1]',
		];

		// Test each shortcode is replaced with the Kit form shortcode.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				'[convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 1, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 1, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $content, 1, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the replace_shortcodes_in_content() method ignores non-ActiveCampaign shortcodes.
	 *
	 * @since   3.1.7
	 */
	public function testActiveCampaignReplaceShortcodesInContentIgnoringOtherShortcodes()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ActiveCampaign();

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
	 * Test that the replace_blocks_in_post() method replaces the ActiveCampaign form block with the Kit form block,
	 * and special characters are not stripped when the Post is saved.
	 *
	 * @since   3.1.7
	 */
	public function testActiveCampaignReplaceBlocksInPost()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ActiveCampaign();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Create a Post with an ActiveCampaign form block and HTML block, as if the user already created this post.
		$postID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'ActiveCampaign: Replace Blocks in Post',
				'post_content' => str_replace( '\\', '\\\\', '<!-- wp:activecampaign-form/activecampaign-form-block {"formId":1} /-->' . $this->html_block ),
			]
		);

		// Replace the blocks in the post.
		$this->importer->replace_blocks_in_post( $postID, 1, $_ENV['CONVERTKIT_API_FORM_ID'] );

		// Test the block is replaced with the Kit form block, and special characters are not stripped.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			get_post_field( 'post_content', $postID )
		);
	}

	/**
	 * Test that the replace_blocks_in_content() method replaces the ActiveCampaign form block with the Kit form block,
	 * and special characters are not stripped.
	 *
	 * @since   3.1.7
	 */
	public function testActiveCampaignReplaceBlocksInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ActiveCampaign();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the blocks to test.
		$content = '<!-- wp:activecampaign-form/activecampaign-form-block {"formId":1} /-->' . $this->html_block;

		// Test the block is replaced with the Kit form block.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			$this->importer->replace_blocks_in_content( parse_blocks( $content ), 1, $_ENV['CONVERTKIT_API_FORM_ID'] )
		);
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
	 * Test that the replace_blocks_in_post() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped when the Post is saved.
	 *
	 * @since   3.1.6
	 */
	public function testAWeberReplaceBlocksInPost()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_AWeber();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Create a Post with an AWeber form block and HTML block, as if the user already created this post.
		$postID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'AWeber: Replace Blocks in Post',
				'post_content' => str_replace(
					'\\',
					'\\\\',
					'<!-- wp:aweber-signupform-block/aweber-shortcode {"selectedShortCode":"6924484-289586845-webform"} -->
<div class="wp-block-aweber-signupform-block-aweber-shortcode">[aweber listid=6924484 formid=289586845 formtype=webform]</div>
<!-- /wp:aweber-signupform-block/aweber-shortcode -->' . $this->html_block
				),
			]
		);

		// Replace the blocks in the post.
		$this->importer->replace_blocks_in_post( $postID, 289586845, $_ENV['CONVERTKIT_API_FORM_ID'] );

		// Test the block is replaced with the Kit form block, and special characters are not stripped.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			get_post_field( 'post_content', $postID )
		);
	}

	/**
	 * Test that the replace_blocks_in_content() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped.
	 *
	 * @since   3.1.6
	 */
	public function testAWeberReplaceBlocksInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_AWeber();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the blocks to test.
		$content = '<!-- wp:aweber-signupform-block/aweber-shortcode {"selectedShortCode":"6924484-289586845-webform"} -->
<div class="wp-block-aweber-signupform-block-aweber-shortcode">[aweber listid=6924484 formid=289586845 formtype=webform]</div>
<!-- /wp:aweber-signupform-block/aweber-shortcode -->' . $this->html_block;

		// Test the block is replaced with the Kit form block.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			$this->importer->replace_blocks_in_content( parse_blocks( $content ), 289586845, $_ENV['CONVERTKIT_API_FORM_ID'] )
		);
	}

	/**
	 * Test that the get_form_ids_from_content() method returns Campaign Monitor form shortcode Form IDs
	 * ignoring any other shortcodes.
	 *
	 * @since   3.1.7
	 */
	public function testCampaignMonitorGetFormIDsFromContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_CampaignMonitor();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the content to test.
		$content = '[cm_form form_id="cm_6912dba75db2d"] some content [cm_form form_id=\'cm_6982a693a0095\'] some other content [aweber formid="12"] different shortcode to ignore';

		// Extract form IDs from content.
		$form_ids = $this->importer->get_form_ids_from_content( $content );

		// Assert the correct number of form IDs are returned.
		$this->assertEquals( 2, count( $form_ids ) );
		$this->assertEquals( 'cm_6912dba75db2d', $form_ids[0] );
		$this->assertEquals( 'cm_6982a693a0095', $form_ids[1] );
	}

	/**
	 * Test that the replace_shortcodes_in_content() method replaces the Campaign Monitor form shortcode with the Kit form shortcode.
	 *
	 * @since   3.1.7
	 */
	public function testCampaignMonitorReplaceShortcodesInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_CampaignMonitor();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[cm_form form_id="cm_6912dba75db2d"]',
			'[cm_form form_id=\'cm_6912dba75db2d\']',
			'[cm_form form_id=cm_6912dba75db2d]',
		];

		// Test each shortcode is replaced with the Kit form shortcode.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				'[convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 'cm_6912dba75db2d', $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 'cm_6912dba75db2d', $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $content, 'cm_6912dba75db2d', $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the replace_shortcodes_in_content() method ignores non-Campaign Monitor shortcodes.
	 *
	 * @since   3.1.7
	 */
	public function testCampaignMonitorReplaceShortcodesInContentIgnoringOtherShortcodes()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_CampaignMonitor();

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
				$this->importer->replace_shortcodes_in_content( $shortcode, 'cm_6912dba75db2d', $_ENV['CONVERTKIT_API_FORM_ID'] )
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

	/**
	 * Test that the replace_blocks_in_post() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped when the Post is saved.
	 *
	 * @since   3.1.6
	 */
	public function testMC4WPReplaceBlocksInPost()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_MC4WP();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Create a Post with a MC4WP form block and HTML block, as if the user already created this post.
		$postID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Mailchimp 4 WP: Replace Blocks in Post',
				'post_content' => str_replace( '\\', '\\\\', '<!-- wp:mailchimp-for-wp/form {"id":4410} /-->' . $this->html_block ),
			]
		);

		// Replace the blocks in the post.
		$this->importer->replace_blocks_in_post( $postID, 4410, $_ENV['CONVERTKIT_API_FORM_ID'] );

		// Test the block is replaced with the Kit form block, and special characters are not stripped.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			get_post_field( 'post_content', $postID )
		);
	}

	/**
	 * Test that the replace_blocks_in_content() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped.
	 *
	 * @since   3.1.6
	 */
	public function testMC4WPReplaceBlocksInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_MC4WP();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the blocks to test.
		$content = '<!-- wp:mailchimp-for-wp/form {"id":4410} /-->' . $this->html_block;

		// Test the block is replaced with the Kit form block.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			$this->importer->replace_blocks_in_content( parse_blocks( $content ), 4410, $_ENV['CONVERTKIT_API_FORM_ID'] )
		);
	}

	/**
	 * Test that the get_form_ids_from_content() method returns MailPoet form shortcode Form IDs
	 * ignoring any other shortcodes.
	 *
	 * @since   3.1.6
	 */
	public function testGetMailPoetFormIDsFromContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Mailpoet();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the content to test.
		$content = '[mailpoet_form id="10"] some content [mailpoet_form id="11"] some other content [aweber formid="12"] different shortcode to ignore';

		// Extract form IDs from content.
		$form_ids = $this->importer->get_form_ids_from_content( $content );

		// Assert the correct number of form IDs are returned.
		$this->assertEquals( 2, count( $form_ids ) );
		$this->assertEquals( 10, $form_ids[0] );
		$this->assertEquals( 11, $form_ids[1] );
	}

	/**
	 * Test that the replace_shortcodes_in_content() method replaces the MailPoet form shortcode with the Kit form shortcode.
	 *
	 * @since   3.1.6
	 */
	public function testMailPoetReplaceShortcodesInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Mailpoet();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[mailpoet_form id="10"]',
			'[mailpoet_form id=10]',
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
	 * Test that the replace_shortcodes_in_content() method ignores non-MailPoet shortcodes.
	 *
	 * @since   3.1.6
	 */
	public function testMailPoetReplaceShortcodesInContentIgnoringOtherShortcodes()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Mailpoet();

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
	 * Test that the replace_blocks_in_post() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped when the Post is saved.
	 *
	 * @since   3.1.6
	 */
	public function testMailPoetReplaceBlocksInPost()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Mailpoet();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Create a Post with a MailPoet form block and HTML block, as if the user already created this post.
		$postID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'MailPoet: Replace Blocks in Post',
				'post_content' => str_replace( '\\', '\\\\', '<!-- wp:mailpoet/subscription-form-block {"formId":4410} /-->' . $this->html_block ),
			]
		);

		// Replace the blocks in the post.
		$this->importer->replace_blocks_in_post( $postID, 4410, $_ENV['CONVERTKIT_API_FORM_ID'] );

		// Test the block is replaced with the Kit form block, and special characters are not stripped.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			get_post_field( 'post_content', $postID )
		);
	}

	/**
	 * Test that the replace_blocks_in_content() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped.
	 *
	 * @since   3.1.6
	 */
	public function testMailPoetReplaceBlocksInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Mailpoet();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the blocks to test.
		$content = '<!-- wp:mailpoet/subscription-form-block {"formId":4410} /-->' . $this->html_block;

		// Test the block is replaced with the Kit form block.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			$this->importer->replace_blocks_in_content( parse_blocks( $content ), 4410, $_ENV['CONVERTKIT_API_FORM_ID'] )
		);
	}

	/**
	 * Test that the get_form_ids_from_content() method returns a single Newsletter form shortcode,
	 * ignoring any other shortcodes.
	 *
	 * @since   3.1.6
	 */
	public function testGetNewsletterFormFromContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Newsletter();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the content to test.
		$content = '[newsletter_form] some content [newsletter_form] some other content [aweber formid="12"] different shortcode to ignore';

		// Extract forms from content.
		// Only one form should be returned as the Newsletter Plugin does not use IDs.
		$form_ids = $this->importer->get_form_ids_from_content( $content );

		// Assert the correct number of form IDs are returned.
		$this->assertEquals( 1, count( $form_ids ) );
		$this->assertEquals( 0, $form_ids[0] );
	}

	/**
	 * Test that the replace_shortcodes_in_content() method replaces the Newsletter form shortcode with the Kit form shortcode.
	 *
	 * @since   3.1.6
	 */
	public function testNewsletterReplaceShortcodesInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Newsletter();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[newsletter_form]',
		];

		// Test each shortcode is replaced with the Kit form shortcode.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				'[convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 0, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 0, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $content, 0, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the replace_shortcodes_in_content() method ignores non-MailPoet shortcodes.
	 *
	 * @since   3.1.6
	 */
	public function testNewsletterReplaceShortcodesInContentIgnoringOtherShortcodes()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Newsletter();

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
				$this->importer->replace_shortcodes_in_content( $shortcode, 0, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the replace_blocks_in_post() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped when the Post is saved.
	 *
	 * @since   3.1.6
	 */
	public function testNewsletterReplaceBlocksInPost()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Newsletter();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Create a Post with a MailPoet form block and HTML block, as if the user already created this post.
		$postID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Newsletter: Replace Blocks in Post',
				'post_content' => str_replace( '\\', '\\\\', '<!-- wp:tnp/minimal {"formtype":"full"} /-->' . $this->html_block ),
			]
		);

		// Replace the blocks in the post.
		$this->importer->replace_blocks_in_post( $postID, 0, $_ENV['CONVERTKIT_API_FORM_ID'] );

		// Test the block is replaced with the Kit form block, and special characters are not stripped.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			get_post_field( 'post_content', $postID )
		);
	}

	/**
	 * Test that the replace_blocks_in_content() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped.
	 *
	 * @since   3.1.6
	 */
	public function testNewsletterReplaceBlocksInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_Newsletter();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the blocks to test.
		$content = '<!-- wp:tnp/minimal {"formtype":"full"} /-->' . $this->html_block;

		// Test the block is replaced with the Kit form block.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			$this->importer->replace_blocks_in_content( parse_blocks( $content ), 0, $_ENV['CONVERTKIT_API_FORM_ID'] )
		);
	}
}
