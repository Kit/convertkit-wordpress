<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the convertkit_is_theme_active() function.
 *
 * @since   3.1.4
 */
class ThemeDetectionTest extends WPTestCase
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
	 * @since   3.1.4
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
	 * @since   3.1.4
	 */
	public function tearDown(): void
	{
		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the convertkit_is_theme_active() function returns the expected result
	 * depending on the active theme.
	 *
	 * @since   3.1.4
	 */
	public function testIsThemeActive()
	{
		// Switch Theme.
		switch_theme('twentytwentyfive');
		$this->assertTrue(convertkit_is_theme_active( 'Twenty Twenty-Five' ));
		$this->assertFalse(convertkit_is_theme_active( 'Impeka' ));

		// Switch Theme.
		switch_theme('impeka');
		$this->assertFalse(convertkit_is_theme_active( 'Twenty Twenty-Five' ));
		$this->assertTrue(convertkit_is_theme_active( 'Impeka' ));
	}
}
