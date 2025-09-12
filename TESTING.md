# Testing Guide

This document describes how to:
- create and run tests for your development work,
- ensure code meets PHP and WordPress Coding Standards, for best practices and security,
- ensure code passes static analysis, to catch potential errors that tests might miss

If you're new to creating and running tests, this guide will walk you through how to do this.

For those more experienced with creating and running tests, our tests are written in PHP using [wp-browser](https://wpbrowser.wptestkit.dev/) 
and [Codeception](https://codeception.com/docs/01-Introduction).

## Prerequisites

If you haven't yet set up your local development environment with the Kit Plugin repository installed, refer to the [Setup Guide](SETUP.md).

If you haven't yet created a branch and made any code changes to the Plugin, refer to the [Development Guide](DEVELOPMENT.md)

> **Familiar with wp-browser, Codeception, PHP Coding Standards and PHPStan?**
> 
> Write your tests 
> 
> Run tests using `composer test [folder]/[cest]` or `composer test-integration [test]`
> 
> Run PHP Coding Standards using `composer coding-standards` and `composer coding-standards-tests`
> 
> Fix PHP Coding Standards using `composer fix-coding-standards` and `composer fix-coding-standards-tests`
> 
> Run static analysis using `composer static-analysis`
> 
> [Submit a Pull Request](https://github.com/ConvertKit/convertkit-wordpress/compare).

## Write (or modify) a test

If your work creates new functionality, write a test.

If your work fixes existing functionality, check if a test exists. Either update that test, or create a new test if one doesn't exist.

Tests are written in PHP using [wp-browser](https://wpbrowser.wptestkit.dev/) and [Codeception](https://codeception.com/docs/01-Introduction).

Codeception provides an expressive test syntax.  For example:
```php
$I->click('Login');
$I->fillField('#input-username', 'John Dough');
$I->pressKey('#input-remarks', 'foo');
```

wp-browser further extends Codeception's test syntax, with functions and assertions that are *specific for WordPress*.  For example,
```php
$I->activatePlugin('convertkit');
```

## Types of Test

There are different types of tests that can be written:
- End to End Tests: Test the UI as a non-technical user in the web browser.
- Integration Tests: Test code modules in the context of a WordPress web site, and test single PHP classes or functions in isolation, with WordPress functions and classes loaded.

## Writing an End to End Test

To create a new End to End Test, at the command line in the Plugin's folder, enter the following command, replacing:
- `general` with the subfolder name to place the test within at `tests/EndToEnd`,
- `ActivatePlugin` with a meaningful name of what the test will perform.

End to End tests are placed in groups within subfolders at `tests/EndToEnd` so that they can be run in isolation, and the GitHub Action can run each folder's End to End tests in parallel for speed.

For example, to generate an `ActivatePlugin` End to End test in the `tests/EndtoEnd/general` folder:

```bash
php vendor/bin/codecept generate:cest EndToEnd general/ActivatePlugin
```
This will create a PHP test file in the `tests/EndToEnd/general` directory called `ActivatePluginCest.php`

```php
class ActivatePluginCest
{
    public function _before(EndToEndTester $I)
    {
    }

    public function tryToTest(EndToEndTester $I)
    {
    }
}
```

For common WordPress actions that do not relate to the Plugin (such as logging into the WordPress Administration interface), which need to be 
performed for every test that you write in this End to End Test, it's recommended to use the `_before()` function:

```php
class ActivatePluginCest
{
    public function _before(EndToEndTester $I)
    {
        // Login as a WordPress Administrator before performing each test.
        $I->loginAsAdmin();
    }

    public function tryToTest(EndToEndTester $I)
    {
    }
}
```

Above, the call to `loginAsAdmin()` is a [wp-browser specific testing function](https://wpbrowser.wptestkit.dev/modules/wpbrowser#loginasadmin) 
that is available to us.

Next, rename the `tryToTest` function to a descriptive function name that best describes what you are testing in a human readable format:

```php
class ActivatePluginCest
{
    public function _before(EndToEndTester $I)
    {
        // Login as a WordPress Administrator before performing each test.
        $I->loginAsAdmin();
    }

    public function testPluginActivation(EndToEndTester $I)
    {
    }
}
```

Within your test function, write the test:
```php
class ActivatePluginCest
{
    public function _before(EndToEndTester $I)
    {
        // Login as a WordPress Administrator before performing each test.
        $I->loginAsAdmin();
    }

    public function testPluginActivation(EndToEndTester $I)
    {
        // Go to the Plugins screen in the WordPress Administration interface.
        $I->amOnPluginsPage();

        // Activate the Plugin.
        $I->activatePlugin('convertkit');

        // Check that the Plugin activated successfully.
        $I->seePluginActivated('convertkit');

        // Check that the <body> class does not have a php-error class, which indicates an error in activation.
        $I->dontSeeElement('body.php-error');
    }
}
```

Additional tests can also be added that relate to this suite of tests.  For example, we might want to test that Plugin deactivation
also works:
```php
class ActivatePluginCest
{
    public function _before(EndToEndTester $I)
    {
        // Login as a WordPress Administrator before performing each test.
        $I->loginAsAdmin();
    }

    public function testPluginActivation(EndToEndTester $I)
    {
        // Go to the Plugins screen in the WordPress Administration interface.
        $I->amOnPluginsPage();

        // Activate the Plugin.
        $I->activatePlugin('convertkit');

        // Check that the Plugin activated successfully.
        $I->seePluginActivated('convertkit');

        // Check that the <body> class does not have a php-error class, which indicates an error in activation.
        $I->dontSeeElement('body.php-error');
    }

    public function testPluginDeactivation(EndToEndTester $I)
    {
        // Go to the Plugins screen in the WordPress Administration interface.
        $I->amOnPluginsPage();

        // Deactivate the Plugin.
        $I->deactivatePlugin('convertkit');

        // Check that the Plugin activated successfully.
        $I->seePluginDeactivated('convertkit');

        // Check that the <body> class does not have a php-error class, which indicates an error in activation.
        $I->dontSeeElement('body.php-error');
    }
}
```

In a Terminal window, run the ChromeDriver.  This is used by our test to mimic user behaviour, and will execute JavaScript
and other elements just as a user would see them:

```bash
chromedriver --url-base=/wd/hub
```

In a second Terminal window, run the test to confirm it works:
```bash
vendor/bin/codecept build
vendor/bin/codecept run EndToEnd general/ActivatePluginCest
```

The console will show the successful result:

![Codeception Test Results](/.github/docs/codeception.png?raw=true)

To run all End to End tests, use:
```bash
vendor/bin/codecept run EndToEnd
```

To run End to End tests in a specific folder (for example, `general`), use:
```bash
vendor/bin/codecept run EndToEnd general
```

To run a specific End to End test in a specific folder (for example, `ActivateDeactivatePluginCest` in the `general` folder), use:
```bash
vendor/bin/codecept run EndtoEnd general/ActivateDeactivatePluginCest
```

For a full list of available wp-browser and Codeception functions that can be used for testing, see:
- [wp-browser](https://wpbrowser.wptestkit.dev/modules)
- [Codeception](https://codeception.com/docs/AcceptanceTests)

## Required Test Format

Tests can be run in isolation, as part of a suite of tests, sequentially and/or in parralel across different environments.
It's therefore required that every Cest contain both `_before()` and `_passed()` functions, which handle:
- `_before()`: Performing prerequisite steps (such as Plugin activation, third party Plugin activation and setup) prior to each test,
- `_passed()`: Performing cleanup steps (such as Plugin deactivation, removal of Plugin data from the database) after each passing test.

The following test format should be used:

```php
class ExampleCest
{
    /**
     * Run common actions before running the test functions in this class.
     * 
     * @since   X.X.X
     * 
     * @param   EndToEndTester    $I  Tester
     */
    public function _before(EndToEndTester $I)
    {
        $I->activateConvertKitPlugin($I);
        $I->activateThirdPartyPlugin($I, 'third-party-plugin-slug');
        $I->setupConvertKitPlugin($I);
        $I->enableDebugLog($I);
    }

    public function testSpecificSteps(EndToEndTester $I)
    {
        // ... write a test here.
    }

    public function testAnotherSpecificSteps(EndToEndTester $I)
    {
        // ... write a test here.
    }

    // .. write further functions for tests as necessary.

    /**
     * Deactivate and reset Plugin(s) after each test, if the test passes.
     * We don't use _after, as this would provide a screenshot of the Plugin
     * deactivation and not the true test error.
     * 
     * @since   X.X.X
     * 
     * @param   EndToEndTester    $I  Tester
     */
    public function _passed(EndToEndTester $I)
    {
        $I->deactivateConvertKitPlugin($I);
        $I->deactivateThirdPartyPlugin($I, 'third-party-plugin-slug');
        $I->resetConvertKitPlugin($I);
    }
}
```

## Using Helpers

Helpers extend testing by registering functions that we might want to use across multiple tests, which are not provided by wp-browser, 
Codeception or PHPUnit.  This helps achieve the principle of DRY code (Don't Repeat Yourself).

For example, in the `tests/Support/Helper` directory, our `Xdebug.php` helper contains the `checkNoWarningsAndNoticesOnScreen()` function,
which checks that
- the <body> class does not contain the `php-error` class, which WordPress adds if a PHP error is detected
- no Xdebug errors were output
- no PHP Warnings or Notices were output

Our End to End Tests can now call `$I->checkNoWarningsAndNoticesOnScreen($I)`, instead of having to write several lines of code to perform each 
error check for every test.

Further End to End Test Helpers that are provided include:
- `activateConvertKitPlugin($I)`: Logs in to WordPress as the `admin` user, and activates the ConvertKit Plugin.
- `deactivateConvertKitPlugin($I)`: Logs in to WordPress as the `admin` user, and deactivates the ConvertKit Plugin.
- `activateThirdPartyPlugin($I, $name)`: Logs in to WordPress as the `admin` user, and activates the given third party Plugin by its slug.
- `deactivateThirdPartyPlugin($I, $name)`: Logs in to WordPress as the `admin` user, and deactivates the given third party Plugin by its slug.
- `setupConvertKitPlugin($I)`: Enters the ConvertKit API Key and Secret in the Plugin's Settings screen, saving it.

Other helpers most likely exist; refer to the [Helper](https://github.com/ConvertKit/convertkit-wordpress/blob/main/tests/Support/Helper/) folder for all available functions.

## Writing Helpers

With this methodology, if two or more of your tests perform the same checks, you should:
- add a function to the applicable file in the `tests/Support/Helper` directory (e.g. `tests/Support/Helper/Plugin.php`),
usually in the format of
```php
/**
 * Description of what this function does
 * 
 * @since   1.0.0
 */
public function yourCustomFunctionNameInHelper($I)
{
    // Your checks here
    $I->...
}
```
- in your test, call your function by using `$I->yourCustomFunctionNameInHelper($I);`
- at the command line, tell Codeception to build your custom function helpers by using `vendor/bin/codecept build`

If the function doesn't fit into any existing helper file:
- create a new file in the `tests/Support/Helper` directory
- edit the [EndToEnd.suite.yml](https://github.com/ConvertKit/convertkit-wordpress/blob/main/tests/EndToEnd.suite.yml) file, adding
the Helper's namespace and class under the `enabled` section.

Need to change how Codeception runs?  Edit the [codeception.dist.xml](codeception.dist.xml) file.

## Writing a WordPress Unit Test

WordPress Unit tests provide testing of Plugin specific functions and/or classes, typically to assert that they perform as expected
by a developer.  This is primarily useful for testing our API class, and confirming that any Plugin registered filters return
the correct data.

To create a new WordPress Unit Test, at the command line in the Plugin's folder, enter the following command, replacing `APITest`
with a meaningful name of what the test will perform:

```bash
php vendor/bin/codecept generate:wpunit Integration APITest
```

This will create a PHP test file in the `tests/Integration` directory called `APITest.php`

```php
<?php

class APITest extends WPTestCase
{
    /**
     * @var \WpunitTester
     */
    protected $tester;
    
    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    // Tests
    public function test_it_works()
    {
        $post = static::factory()->post->create_and_get();
        
        $this->assertInstanceOf(\WP_Post::class, $post);
    }
}
```

Helpers can be used for WordPress Unit Tests, the same as how they can be used for End To End tests.
To register your own helper function, add it to the `tests/Support/Helper/Wpunit.php` file.

## Run Tests

> **Quick Commands**
>  
> `composer test`: Run all End to End tests
>
> `composer test general`: Run all tests in the EndToEnd/general folder
>
> `composer test general/ActivateDeactivatePluginCest`: Run all tests in the EndToEnd/general/ActivateDeactivatePluginCest file
>
> `composer test-integration`: Run all Integration tests
>
> `composer test-integration APITest`: Run the Integration/APITest tests

Once you have written your code and test(s), run the tests to make sure there are no errors.

If ChromeDriver isn't running, open a new Terminal window and enter the following command:

```bash
chromedriver --url-base=/wd/hub
```

To run the tests, enter the following commands in a separate Terminal window:

```bash
vendor/bin/codecept build
vendor/bin/codecept run EndToEnd
vendor/bin/codecept run Integration
```
If a test fails, you can inspect the output and screenshot at `tests/_output`.

Any errors should be corrected by making applicable code or test changes.

## Run PHP CodeSniffer

> **Quick Command**  
> `composer coding-standards`: Run PHP Coding Standards on Plugin files

[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) checks that all Plugin code meets the 
[WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

In the Plugin's directory, run the following command to run PHP_CodeSniffer, which will check the code meets WordPress' Coding Standards
as defined in the `phpcs.xml` configuration:

```bash
vendor/bin/phpcs ./ --standard=phpcs.xml -v -s
```

`--standard=phpcs.tests.xml` tells PHP CodeSniffer to use the Coding Standards rules / configuration defined in `phpcs.tests.xml`.
These differ slightly from WordPress' Coding Standards, to ensure that writing tests isn't a laborious task, whilst maintaing consistency
in test coding style. 
`-v` produces verbose output
`-s` specifies the precise rule that failed
![Coding Standards Screenshot](/.github/docs/coding-standards-error.png?raw=true)

Any errors should be corrected by either:
- making applicable code changes
- (Experimental) running `vendor/bin/phpcbf ./ --standard=phpcs.xml -v -s` to automatically fix coding standards

Need to change the PHP or WordPress coding standard rules applied?  Either:
- ignore a rule in the affected code, by adding `phpcs:ignore {rule}`, where {rule} is the given rule that failed in the above output.
- edit the [phpcs.xml](phpcs.xml) file.

**Rules should be ignored with caution**, particularly when sanitizing and escaping data.

## Run PHPStan

> **Quick Command**  
> `composer static-analysis`: Run PHPStan static analysis on Plugin files

[PHPStan](https://phpstan.org) performs static analysis on the Plugin's PHP code.  This ensures:

- DocBlocks declarations are valid and uniform
- DocBlocks declarations for WordPress `do_action()` and `apply_filters()` calls are valid
- Typehinting variables and return types declared in DocBlocks are correctly cast
- Any unused functions are detected
- Unnecessary checks / code is highlighted for possible removal
- Conditions that do not evaluate can be fixed/removed as necessary

In the Plugin's directory, run the following command to run PHPStan:

```bash
vendor/bin/phpstan --memory-limit=1G
```

Any errors should be corrected by making applicable code changes.

False positives [can be excluded by configuring](https://phpstan.org/user-guide/ignoring-errors) the `phpstan.neon` file.

## Run PHP CodeSniffer for Tests

> **Quick Command**  
> `composer coding-standards-tests`: Run PHP Coding Standards on test files

In the Plugin's directory, run the following command to run PHP_CodeSniffer, which will check the code meets Coding Standards
as defined in the `phpcs.tests.xml` configuration:

```bash
vendor/bin/phpcs ./tests --standard=phpcs.tests.xml -v -s 
```

`--standard=phpcs.tests.xml` tells PHP CodeSniffer to use the Coding Standards rules / configuration defined in `phpcs.tests.xml`.
These differ slightly from WordPress' Coding Standards, to ensure that writing tests isn't a laborious task, whilst maintaing consistency
in test coding style. 
`-v` produces verbose output
`-s` specifies the precise rule that failed

Any errors should be corrected by either:
- making applicable code changes
- (Experimental) running `vendor/bin/phpcbf ./tests --standard=phpcs.tests.xml -v -s ` to automatically fix coding standards

Need to change the PHP or WordPress coding standard rules applied?  Either:
- ignore a rule in the affected code, by adding `phpcs:ignore {rule}`, where {rule} is the given rule that failed in the above output.
- edit the [phpcs.tests.xml](phpcs.tests.xml) file.

**Rules can be ignored with caution**, but it's essential that rules relating to coding style and inline code commenting / docblocks remain.

## Manual Testing

If a build of the Plugin ZIP file is required locally, perhaps to test on a different environment or specific site, you may run the `.scripts/build.sh` script.

This will create a `convertkit.zip` Plugin file, which can be installed on a WordPress web site.

Note that deployments are automated when using GitHub's release system; refer to the [Deployment Guide](DEPLOYMENT.md) for more information.

## Next Steps

Once your test(s) are written and successfully run locally, submit your branch via a new [Pull Request](https://github.com/ConvertKit/convertkit-wordpress/compare).

It's best to create a Pull Request in draft mode, as this will trigger all tests to run as a GitHub Action, allowing you to
double check all tests pass.

If the PR tests fail, you can make code changes as necessary, pushing to the same branch.  This will trigger the tests to run again.

If the PR tests pass, you can publish the PR, assigning some reviewers.

## Reviewing a PR

For reviewers, two methods are available:

### GitHub Codespaces

On the PR, click the `<> Code` option, followed by the `Codespaces` tab. The option to use an existing Codespace or create a new one will be presented.

![GitHub Codespaces](/.github/docs/github-codespaces.png?raw=true)

After a few minutes, your development environment should be ready. 

Click on the `Ports` tab, and navigate to the "Application" URL by hovering over the `Forwarded Address` and clicking the globe icon:

![Ports tab](/.github/docs/dev-container-ports.png?raw=true)

To access the WordPress Administration interface, append `/wp-admin` to the URL, using the following credentials:
- Username: `vipgo`
- Password: `password`

Once logged in, navigating to the Plugins screen will show the repository Plugin installed and active, along with some other common third party Plugins:

![Plugins](/.github/docs/dev-container-plugins.png?raw=true)

### WordPress Playground

On the PR, navigate to the comment created by the GitHub bot, and click the link to preview the PR in the Playground:

![WordPress Playground Comment](/.github/docs/wordpress-playground-comment.png?raw=true)

This will load a WordPress instance on playground.wordpress.net, ready to test the Plugin:

![WordPress Playground](/.github/docs/wordpress-playground.png?raw=true)