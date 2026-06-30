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

		// Define Forms as if the Forms resource class populated them from the API.
		update_option(
			'convertkit_forms',
			[
				3059218 => [
					'id'         => 3059218,
					'name'       => 'Auto Confirm Form',
					'created_at' => '2022-03-07T15:57:51Z',
					'type'       => 'embed',
					'format'     => 'inline',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/bfac9ed794/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/bfac9ed794',
					'archived'   => false,
					'uid'        => 'bfac9ed794',
				],
				2765143 => [
					'id'         => 2765143,
					'name'       => 'Double Optin Form',
					'created_at' => '2021-11-11T15:31:28Z',
					'type'       => 'embed',
					'format'     => 'inline',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/a04b384fc6/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/a04b384fc6',
					'archived'   => false,
					'uid'        => 'a04b384fc6',
				],
				3003590 => [
					'id'         => 3003590,
					'name'       => 'Third Party Integrations Form',
					'created_at' => '2022-02-17T15:05:31.000Z',
					'type'       => 'embed',
					'format'     => 'inline',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/71cbcc4042/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/71cbcc4042',
					'archived'   => false,
					'uid'        => '71cbcc4042',
				],
				2780977 => [
					'id'         => 2780977,
					'name'       => 'Modal Form',
					'created_at' => '2021-11-17T04:22:06.000Z',
					'type'       => 'embed',
					'format'     => 'modal',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/397e876257/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/397e876257',
					'archived'   => false,
					'uid'        => '397e876257',
				],
				2780979 => [
					'id'         => 2780979,
					'name'       => 'Slide In Form',
					'created_at' => '2021-11-17T04:22:24.000Z',
					'type'       => 'embed',
					'format'     => 'slide in',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/e0d65bed9d/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/e0d65bed9d',
					'archived'   => false,
					'uid'        => 'e0d65bed9d',
				],
				2765139 => [
					'id'         => 2765139,
					'name'       => 'Page Form',
					'created_at' => '2021-11-11T15:30:40.000Z',
					'type'       => 'embed',
					'format'     => 'inline',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/85629c512d/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/85629c512d',
					'archived'   => false,
					'uid'        => '85629c512d',
				],
				470099  => [
					'id'                  => 470099,
					'name'                => 'Legacy Form',
					'created_at'          => null,
					'type'                => 'embed',
					'url'                 => 'https://app.kit.com/landing_pages/470099',
					'embed_js'            => 'https://api.kit.com/api/v3/forms/470099.js?api_key=' . $_ENV['CONVERTKIT_API_KEY'],
					'embed_url'           => 'https://api.kit.com/api/v3/forms/470099.html?api_key=' . $_ENV['CONVERTKIT_API_KEY'],
					'title'               => 'Join the newsletter',
					'description'         => '<p>Subscribe to get our latest content by email.</p>',
					'sign_up_button_text' => 'Subscribe',
					'success_message'     => 'Success! Now check your email to confirm your subscription.',
					'archived'            => false,
				],
				2780980 => [
					'id'         => 2780980,
					'name'       => 'Sticky Bar Form',
					'created_at' => '2021-11-17T04:22:42.000Z',
					'type'       => 'embed',
					'format'     => 'sticky bar',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/9f5c601482/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/9f5c601482',
					'archived'   => false,
					'uid'        => '9f5c601482',
				],
				3437554 => [
					'id'         => 3437554,
					'name'       => 'AAA Test',
					'created_at' => '2022-07-15T15:06:32.000Z',
					'type'       => 'embed',
					'format'     => 'inline',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/3bb15822a2/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/3bb15822a2',
					'archived'   => false,
					'uid'        => '3bb15822a2',
				],
				2765149 => [
					'id'         => 2765149,
					'name'       => 'WooCommerce Product Form',
					'created_at' => '2021-11-11T15:32:54.000Z',
					'type'       => 'embed',
					'format'     => 'inline',
					'embed_js'   => 'https://cheerful-architect-3237.kit.com/7e238f3920/index.js',
					'embed_url'  => 'https://cheerful-architect-3237.kit.com/7e238f3920',
					'archived'   => false,
					'uid'        => '7e238f3920',
				],
			]
		);
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

		// Delete the Forms resource.
		delete_option( 'convertkit_forms' );

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
				'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 1, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 1, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
				'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
				'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 'cm_6912dba75db2d', $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 'cm_6912dba75db2d', $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
	 * Test that the get_form_ids_from_content() method returns Kit legacy form shortcode Form IDs
	 * ignoring any other shortcodes.
	 *
	 * @since   3.3.5
	 */
	public function testGetKitLegacyFormsFormIDsFromContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the content to test.
		$content = 'Legacy Form: [convertkit_form form="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"] non-Legacy Form: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Different shortcode: [aweber formid="12"]';

		// Extract form IDs from content.
		$form_ids = $this->importer->get_form_ids_from_content( $content );

		// Assert the legacy form shortcode was detected, and the non-legacy form shortcode was ignored.
		$this->assertEquals( 1, count( $form_ids ) );
		$this->assertEquals( $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'], $form_ids[0] );
	}

	/**
	 * Test that the replace_shortcodes_in_content() method replaces the Kit legacy form shortcode with the Kit form shortcode.
	 *
	 * @since   3.1.0
	 */
	public function testKitLegacyFormsReplaceShortcodesInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"]',
			'[convertkit_form form=' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . ']',
		];

		// Test each shortcode is replaced with the Kit form shortcode.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				'[convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'], $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'], $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form id="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $content, $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'], $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the replace_shortcodes_in_content() method ignores non-Kit legacy form shortcodes.
	 *
	 * @since   3.1.0
	 */
	public function testKitLegacyFormsReplaceShortcodesInContentIgnoringOtherShortcodes()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the shortcodes to test.
		$shortcodes = [
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"]',
			'[a_random_shortcode]',
		];

		// Test each shortcode is ignored.
		foreach ( $shortcodes as $shortcode ) {
			$this->assertEquals(
				$shortcode,
				$this->importer->replace_shortcodes_in_content( $shortcode, $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'], $_ENV['CONVERTKIT_API_FORM_ID'] )
			);
		}
	}

	/**
	 * Test that the replace_blocks_in_post() method replaces the third party form block with the Kit form block,
	 * and special characters are not stripped when the Post is saved.
	 *
	 * @since   3.1.6
	 */
	public function testKitLegacyFormsReplaceBlocksInPost()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Create a Post with a MC4WP form block and HTML block, as if the user already created this post.
		$postID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Kit Legacy Forms: Replace Blocks in Post',
				'post_content' => str_replace( '\\', '\\\\', '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"} /-->' . $this->html_block ),
			]
		);

		// Replace the blocks in the post.
		$this->importer->replace_blocks_in_post( $postID, $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'], $_ENV['CONVERTKIT_API_FORM_ID'] );

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
	 * @since   3.3.5
	 */
	public function testKitLegacyFormsReplaceBlocksInContent()
	{
		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define the blocks to test.
		$content = '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"} /-->' . $this->html_block;

		// Test the block is replaced with the Kit form block.
		$this->assertEquals(
			'<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->' . $this->html_block,
			$this->importer->replace_blocks_in_content( parse_blocks( $content ), $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'], $_ENV['CONVERTKIT_API_FORM_ID'] )
		);
	}

	/**
	 * Test that get_form_ids_from_content() returns Kit legacy form block Form IDs
	 * (in addition to shortcode Form IDs), ignoring any non-legacy Kit form blocks.
	 *
	 * Exercises the block-extraction extension to the get_form_ids_from_content()
	 * override on the Kit Legacy Forms importer.
	 *
	 * @since   3.3.5
	 */
	public function testGetKitLegacyFormsFormIDsFromContentForBlocks()
	{
		// Populate the Forms resource so the importer can determine which form
		// IDs are legacy. Without this, the override has nothing to filter against.
		$this->setupKitFormsResource();

		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Define content containing both legacy and non-legacy Kit form blocks,
		// plus a legacy shortcode, to confirm both block and shortcode IDs are
		// surfaced when they reference a legacy form.
		$content = '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"} /-->'
			. '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->'
			. '[convertkit_form form="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"]';

		// Extract form IDs from content.
		$form_ids = $this->importer->get_form_ids_from_content( $content );

		// Assert only the legacy form ID is returned (deduplicated across the
		// shortcode and block references).
		$this->assertCount( 1, $form_ids );
		$this->assertEquals( (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'], $form_ids[0] );
	}

	/**
	 * Test that get_forms_in_posts() only returns post IDs whose content
	 * contains a Kit Form shortcode or block referencing a legacy form ID.
	 *
	 * Exercises the get_forms_in_posts() override on the Kit Legacy Forms
	 * importer, which narrows the parent class's broad SQL match.
	 *
	 * @since   3.3.5
	 */
	public function testGetKitLegacyFormsInPosts()
	{
		// Populate the Forms resource so the importer can determine which form
		// IDs are legacy. Without this, the override has nothing to filter against.
		$this->setupKitFormsResource();

		// Initialize the class we want to test.
		$this->importer = new \ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->importer);

		// Create a post containing a non-legacy Kit Form shortcode. This post
		// should NOT be returned by the Legacy Forms importer.
		$nonLegacyShortcodePostID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Kit Legacy Forms: Non-Legacy Shortcode Post',
				'post_content' => '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
			]
		);

		// Create a post containing a non-legacy Kit Form block. This post
		// should NOT be returned by the Legacy Forms importer.
		$nonLegacyBlockPostID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Kit Legacy Forms: Non-Legacy Block Post',
				'post_content' => '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"} /-->',
			]
		);

		// Create a post containing a legacy Kit Form shortcode. This post
		// SHOULD be returned by the Legacy Forms importer.
		$legacyShortcodePostID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Kit Legacy Forms: Legacy Shortcode Post',
				'post_content' => '[convertkit_form form="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"]',
			]
		);

		// Create a post containing a legacy Kit Form block. This post
		// SHOULD be returned by the Legacy Forms importer.
		$legacyBlockPostID = $this->factory->post->create(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Kit Legacy Forms: Legacy Block Post',
				'post_content' => '<!-- wp:convertkit/form {"form":"' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"} /-->',
			]
		);

		// Fetch the list of post IDs that contain a legacy Kit form reference.
		$post_ids = $this->importer->get_forms_in_posts();

		// Cast post IDs to ints for comparison (wpdb returns strings).
		$post_ids = array_map( 'intval', $post_ids );

		// Assert only the legacy posts are returned, and the non-legacy posts are not.
		$this->assertContains( $legacyShortcodePostID, $post_ids );
		$this->assertContains( $legacyBlockPostID, $post_ids );
		$this->assertNotContains( $nonLegacyShortcodePostID, $post_ids );
		$this->assertNotContains( $nonLegacyBlockPostID, $post_ids );
	}

	/**
	 * Populates the Kit Forms resource cache by storing credentials and
	 * fetching forms from the API. Required by the Kit Legacy Forms importer
	 * tests that exercise the legacy-filtering overrides.
	 *
	 * @since   3.3.5
	 */
	private function setupKitFormsResource()
	{
		// Store credentials in the Plugin's settings.
		$settings = new \ConvertKit_Settings();
		update_option(
			$settings::SETTINGS_NAME,
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);

		// Refresh the Forms resource so is_legacy() can determine which form IDs
		// are legacy.
		$resource = new \ConvertKit_Resource_Forms();
		$resource->refresh();
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
				'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
				'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 10, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
				'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
				$this->importer->replace_shortcodes_in_content( $shortcode, 0, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode.';
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode.',
				$this->importer->replace_shortcodes_in_content( $content, 0, $_ENV['CONVERTKIT_API_FORM_ID'] )
			);

			// Prepend and append some content and duplicate the shortcode.
			$content = 'Some content before the shortcode: ' . $shortcode . ' Some content after the shortcode: ' . $shortcode;
			$this->assertEquals(
				'Some content before the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"] Some content after the shortcode: [convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
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
