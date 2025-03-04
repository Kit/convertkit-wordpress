<?php  //[STAMP] 5c3c481ca435a007ffc770976a275fa6
// phpcs:ignoreFile
namespace Tests\Support\_generated;

// This class was automatically generated by build task
// You should not change it manually as it will be overwritten on next build

trait IntegrationTesterActions
{
    /**
     * @return \Codeception\Scenario
     */
    abstract protected function getScenario();

    
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Get the absolute path to the mu-plugins directory.
     *
     * The value will first look at the `WPMU_PLUGIN_DIR` constant, then the `WP_CONTENT_DIR` configuration parameter,
     * and will, finally, look in the default path from the WordPress root directory.
     *
     * @param string $path
     *
     * @return string
     * @since TBD
     * @see \lucatume\WPBrowser\Module\WPLoader::getMuPluginsFolder()
     */
    public function getMuPluginsFolder(string $path = ""): string {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('getMuPluginsFolder', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Returns the absolute path to the WordPress root folder or a path within it..
     *
     * @param string|null $path The path to append to the WordPress root folder.
     *
     * @return string The absolute path to the WordPress root folder or a path within it.
     * @see \lucatume\WPBrowser\Module\WPLoader::getWpRootFolder()
     */
    public function getWpRootFolder(?string $path = NULL): string {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('getWpRootFolder', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Returns the absolute path to the plugins directory.
     *
     * The value will first look at the `WP_PLUGIN_DIR` constant, then the `pluginsFolder` configuration parameter,
     * then the `WP_CONTENT_DIR` configuration parameter, and will, finally, look in the default path from the
     * WordPress root directory.
     *
     * @example
     * ```php
     * $plugins = $this->getPluginsFolder();
     * $hello = $this->getPluginsFolder('hello.php');
     * ```
     *
     * @param string $path A relative path to append to te plugins directory absolute path.
     *
     * @return string The absolute path to the `pluginsFolder` path or the same with a relative path appended if `$path`
     *                is provided.
     * @see \lucatume\WPBrowser\Module\WPLoader::getPluginsFolder()
     */
    public function getPluginsFolder(string $path = ""): string {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('getPluginsFolder', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Returns the absolute path to the themes' directory.
     *
     * @example
     * ```php
     * $themes = $this->getThemesFolder();
     * $twentytwenty = $this->getThemesFolder('/twentytwenty');
     * ```
     *
     * @param string $path A relative path to append to te themes directory absolute path.
     *
     * @return string The absolute path to the `themesFolder` path or the same with a relative path appended if `$path`
     *                is provided.
     * @see \lucatume\WPBrowser\Module\WPLoader::getThemesFolder()
     */
    public function getThemesFolder(string $path = ""): string {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('getThemesFolder', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Accessor method to get the object storing the factories for things.
     * This method gives access to the same factories provided by the
     * [Core test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/).
     *
     * @example
     * ```php
     * $postId = $I->factory()->post->create();
     * $userId = $I->factory()->user->create(['role' => 'administrator']);
     * ```
     *
     * @return FactoryStore A factory store, proxy to get hold of the Core suite object
     *                                                     factories.
     *
     * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/
     * @see \lucatume\WPBrowser\Module\WPLoader::factory()
     */
    public function factory(): \lucatume\WPBrowser\Module\WPLoader\FactoryStore {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('factory', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Returns the absolute path to the WordPress content directory.
     *
     * The value will first look at the `WP_CONTENT_DIR` configuration parameter, and will, finally, look in the
     * default path from the WordPress root directory.
     *
     * @example
     * ```php
     * $content = $this->getContentFolder();
     * $themes = $this->getContentFolder('themes');
     * $twentytwenty = $this->getContentFolder('themes/twentytwenty');
     * ```
     *
     * @param string $path An optional path to append to the content directory absolute path.
     *
     * @return string The content directory absolute path, or a path in it.
     * @see \lucatume\WPBrowser\Module\WPLoader::getContentFolder()
     */
    public function getContentFolder(string $path = ""): string {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('getContentFolder', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     *
     * @see \lucatume\WPBrowser\Module\WPLoader::getInstallation()
     */
    public function getInstallation(): \lucatume\WPBrowser\WordPress\Installation {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('getInstallation', func_get_args()));
    }
}
