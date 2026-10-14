<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests the Plugin's class autoloader.
 *
 * @since   3.4.7
 */
class AutoloadTest extends WPTestCase
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
	 * @since   3.4.7
	 */
	public function setUp(): void
	{
		parent::setUp();
		activate_plugins('convertkit/wp-convertkit.php');
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.7
	 */
	public function tearDown(): void
	{
		deactivate_plugins('convertkit/wp-convertkit.php');
		parent::tearDown();
	}

	/**
	 * Test that every Plugin class can be autoloaded from its file.
	 *
	 * @since   3.4.7
	 */
	public function testAllClassesCanBeAutoloaded()
	{
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(CONVERTKIT_PLUGIN_PATH, \FilesystemIterator::SKIP_DOTS));
		foreach ($iterator as $path) {
			$file = str_replace(CONVERTKIT_PLUGIN_PATH, '', $path->getPathname());

			// Only check PHP files in the includes and admin folders.
			if ($path->getExtension() !== 'php' || ! preg_match('#^/(includes|admin)/#', $file)) {
				continue;
			}

			// Skip Divi and Elementor modules and widgets, which extend classes that only exist when Divi or Elementor are active.
			if (preg_match('#^/includes/integrations/(divi|elementor)/#', $file) && ! in_array($file, [ '/includes/integrations/divi/class-convertkit-divi.php', '/includes/integrations/elementor/class-convertkit-elementor.php' ], true)) {
				continue;
			}

			foreach ($this->getDeclaredClasses($file) as $class) {
				$this->assertTrue(class_exists($class) || trait_exists($class) || interface_exists($class), $class . ' in ' . $file . ' could not be autoloaded.');
				$reflection = new \ReflectionClass($class);
				$this->assertSame(CONVERTKIT_PLUGIN_PATH . $file, $reflection->getFileName(), $class . ' was loaded from the wrong file.');
			}
		}
	}

	/**
	 * Test that the autoloader ignores classes that don't belong to the Plugin.
	 *
	 * @since   3.4.7
	 */
	public function testNonPluginClassesAreIgnored()
	{
		$this->assertFalse(class_exists('ConvertKit_Class_That_Does_Not_Exist'));
		$this->assertFalse(class_exists('Some_Other_Plugin_Class'));
	}

	/**
	 * Returns the class, trait and interface names declared in the given file.
	 *
	 * @since   3.4.7
	 *
	 * @param   string $file   File, relative to the Plugin's path.
	 * @return  array
	 */
	private function getDeclaredClasses($file)
	{
		preg_match_all('#^(?:abstract |final )?(?:class|trait|interface) (\w+)#m', file_get_contents(CONVERTKIT_PLUGIN_PATH . $file), $matches); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return $matches[1];
	}
}
