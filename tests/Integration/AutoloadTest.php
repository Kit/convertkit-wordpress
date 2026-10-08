<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests the Plugin's class autoloader map at includes/autoload.php.
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
	 * Class names mapped to their files, relative to the Plugin's path.
	 *
	 * @since   3.4.7
	 *
	 * @var     array
	 */
	private $classes = [];

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.7
	 */
	public function setUp(): void
	{
		parent::setUp();
		activate_plugins('convertkit/wp-convertkit.php');
		$this->classes = require CONVERTKIT_PLUGIN_PATH . '/includes/autoload.php';
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
	 * Test that each class in the autoloader map exists in its mapped file, and can be autoloaded.
	 *
	 * @since   3.4.7
	 */
	public function testMappedClassesCanBeAutoloaded()
	{
		foreach ($this->classes as $class => $file) {
			$this->assertFileExists(CONVERTKIT_PLUGIN_PATH . $file, $class . ' is mapped to a file that does not exist.');
			$this->assertSame([ $class ], $this->getDeclaredClasses($file), $file . ' does not declare ' . $class . '.');
			$this->assertTrue(class_exists($class) || trait_exists($class) || interface_exists($class), $class . ' could not be autoloaded.');
		}
	}

	/**
	 * Test that every Plugin class is either in the autoloader map, or loaded directly.
	 *
	 * @since   3.4.7
	 */
	public function testAllClassesAreMapped()
	{
		// Files loaded directly by wp-convertkit.php, as they register hooks when loaded.
		preg_match_all("#require_once CONVERTKIT_PLUGIN_PATH \. '([^']+)'#", file_get_contents(CONVERTKIT_PLUGIN_PATH . '/wp-convertkit.php'), $matches);
		$required = $matches[1];

		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(CONVERTKIT_PLUGIN_PATH, \FilesystemIterator::SKIP_DOTS));
		foreach ($iterator as $path) {
			$file = str_replace(CONVERTKIT_PLUGIN_PATH, '', $path->getPathname());

			// Only check PHP files in the includes and admin folders.
			if ($path->getExtension() !== 'php' || ! preg_match('#^/(includes|admin)/#', $file)) {
				continue;
			}

			// Skip Divi and Elementor modules and widgets, which are loaded by their integrations.
			if (preg_match('#^/includes/integrations/(divi|elementor)/#', $file) && ! in_array($file, [ '/includes/integrations/divi/class-convertkit-divi.php', '/includes/integrations/elementor/class-convertkit-elementor.php' ], true)) {
				continue;
			}

			// Skip files loaded directly.
			if (in_array($file, $required, true)) {
				continue;
			}

			foreach ($this->getDeclaredClasses($file) as $class) {
				$this->assertArrayHasKey($class, $this->classes, $class . ' in ' . $file . ' is missing from includes/autoload.php.');
				$this->assertSame($file, $this->classes[ $class ], $class . ' is mapped to the wrong file.');
			}
		}
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
		preg_match_all('#^(?:abstract |final )?(?:class|trait|interface) (\w+)#m', file_get_contents(CONVERTKIT_PLUGIN_PATH . $file), $matches);
		return $matches[1];
	}
}
