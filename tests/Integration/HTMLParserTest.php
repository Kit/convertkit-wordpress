<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_HTML_Parser functions.
 *
 * @since   3.0.0
 */
class HTMLParserTest extends WPTestCase
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
	 * @since   3.0.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Configure access and refresh token in Plugin settings.
		$this->settings = new \ConvertKit_Settings();
		$this->settings->save(
			[
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
			]
		);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.0.0
	 */
	public function tearDown(): void
	{
		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the parse_html() method returns the expected HTML.
	 *
	 * @since   3.0.0
	 */
	public function testContent()
	{
		$content = '<h1>Hello World</h1><p>This is a test</p>';

		// Initialize the class we want to test and confirm initialization didn't result in an error.
		$parser = new \ConvertKit_HTML_Parser( $content );
		$this->assertNotInstanceOf(\WP_Error::class, $parser);

		$this->assertEquals( $content, $parser->get_body_html() );
	}

	/**
	 * Test that the modified content is returned.
	 *
	 * @since   3.0.0
	 */
	public function testModifiedContent()
	{
		$content  = '<h1>Hello World</h1><p>This is a test</p>';
		$expected = '<h1>Hello World</h1><p>This is a test</p><form action="https://example.com" method="post"></form>';

		// Initialize the class we want to test and confirm initialization didn't result in an error.
		$parser = new \ConvertKit_HTML_Parser( $content );
		$this->assertNotInstanceOf(\WP_Error::class, $parser);

		// Create a Form element.
		$form = $parser->html->createElement( 'form' );
		$form->setAttribute( 'action', 'https://example.com' );
		$form->setAttribute( 'method', 'post' );

		// Insert form after the last paragraph.
		$paragraphs    = $parser->xpath->query('//p');
		$lastParagraph = $paragraphs->item($paragraphs->length - 1);
		$lastParagraph->parentNode->insertBefore($form, $lastParagraph->nextSibling);

		// Confirm the modified content is returned.
		$this->assertEquals( $expected, $parser->get_body_html() );
	}

	/**
	 * Test that special characters are correctly parsed.
	 *
	 * @since   3.0.0
	 */
	public function testSpecialCharacters()
	{
		$content  = '<h1>Vantar &thorn;inn ungling sj&aacute;lfstraust &iacute; st&aelig;r&eth;fr&aelig;&eth;i?</h1><p>This is a test</p>';
		$expected = '<h1>Vantar &thorn;inn ungling sj&aacute;lfstraust &iacute; st&aelig;r&eth;fr&aelig;&eth;i?</h1><p>This is a test</p><div class="convertkit-form-builder">&THORN;a&eth; er h&aelig;gt a&eth; breyta &thorn;v&iacute;! Lausnin er ekki me&eth; &thorn;v&iacute; a&eth; reikna fleiri d&aelig;mi...</div>';

		// Initialize the class we want to test and confirm initialization didn't result in an error.
		$parser = new \ConvertKit_HTML_Parser( $content );
		$this->assertNotInstanceOf(\WP_Error::class, $parser);

		// Create a Div element.
		$div = $parser->html->createElement( 'div' );
		$div->setAttribute( 'class', 'convertkit-form-builder' );
		$div->textContent = 'Það er hægt að breyta því! Lausnin er ekki með því að reikna fleiri dæmi...';

		// Insert div after the last paragraph.
		$paragraphs    = $parser->xpath->query('//p');
		$lastParagraph = $paragraphs->item($paragraphs->length - 1);
		$lastParagraph->parentNode->insertBefore($div, $lastParagraph->nextSibling);

		// Confirm the modified content is returned.
		$this->assertEquals( $expected, $parser->get_body_html() );
	}
}
