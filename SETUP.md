# Setup Guide

This document describes how to setup your development environment, so that it is ready to run, develop and test the Kit WordPress Plugin.

Suggestions are provided for the LAMP/LEMP stack and Git client are for those who prefer the UI over a command line and/or are less familiar with 
WordPress, PHP, MySQL and Git - but you're free to use your preferred software.

## LAMP/LEMP stack

Any Apache/nginx, PHP 7.x+ and MySQL 5.8+ stack running WordPress.  For example, but not limited to:
- Local by Flywheel (recommended)
- MAMP
- WAMP
- VVV
- Docker

## Local, MAMP, WAMP, VVV

If using a non-Docker environment, follow the below steps:

### Composer

If [Composer](https://getcomposer.org) is not installed on your local environment, enter the following commands at the command line to install it:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

Confirm that installation was successful by entering the `composer` command at the command line

### Node.js + npm

If [npm](https://nodejs.org/en/download/current) is not installed on your local environment, install a package manager to then install node, such as nvm:

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
\. "$HOME/.nvm/nvm.sh"
nvm install 24
```

### Clone Repository

Using your preferred Git client or command line, clone this repository into the `wp-content/plugins/` folder of your local WordPress installation.

If you prefer to clone the repository elsewhere, and them symlink it to your local WordPress installation, that will work as well.

If you're new to this, use [GitHub Desktop](https://desktop.github.com/) or [Tower](https://www.git-tower.com/mac)

### Install Third Party Plugins

The Kit Plugin (and/or its Addons) provides integrations with the following, and therefore it's recommended to install and activate these
Plugins on your local development environment:

- [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) (Free)
- [Gravity Forms](https://www.gravityforms.com/) (Paid)
- [WishList Member](https://wishlistmember.com/) (Paid)
- [WooCommerce](https://wordpress.org/plugins/woocommerce/) (Free)

For Kit employees or contractors, licensed versions of paid Third Party Plugins can be made available to you on request.

### Create Test Database

Create a blank `test` database in MySQL, with a MySQL user who can read and write to it.

### Configure Testing Environment

Copy the `.env.example` file to `.env.testing` in the root of this repository, changing parameters to match your local development environment as necessary.

You'll also want to include Kit credentials, such as API Keys and OAuth tokens here.  `.env.testing` is excluded from Git, to ensure these sensitive credentials are not stored in version control.

### Install Packages

In the Plugin's directory, at the command line, run `composer install`.

This will install two types of packages:
- Packages used by the Plugin (i.e. shared libraries used across multiple Kit Plugins)
- Packages used in the process of development (i.e. testing, coding standards):
-- wp-browser
-- Codeception
-- PHPStan
-- PHPUnit
-- PHP_CodeSniffer

How to use these is covered later on, and in the [Testing Guide](TESTING.md)

### Install npm Packages

In the Plugin's directory, at the command line, run `npm install`.

This sets up:
- JS linting / coding standards using WordPress recommended configurations (https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/#lint-js)
- SCSS and CSS linting / coding standards using WordPress recommended configurations (https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/#lint-style)
- JS compilation and minification
- SASS compilation and minification

### Build JS and CSS

Run `npm run build` to build the frontend CSS and JS.

### Configure wp-config.php

In the root of your WordPress installation, find the `wp-config.php` file.

Change the following line from (your database name itself may vary):

```php
define( 'DB_NAME', 'local' );
```

to:

```php
if( isset( $_SERVER['HTTP_X_TEST_REQUEST'] ) && $_SERVER['HTTP_X_TEST_REQUEST'] ) {
    // WPBrowser request, performed when Codeception tests are run. Connect to test DB.
    define( 'DB_NAME', 'test' );
} elseif( isset( $_SERVER['HTTP_USER_AGENT'] ) && strpos( $_SERVER['HTTP_USER_AGENT'], 'HeadlessChrome' ) !== false ) {
    // WPWebDriver request, performed when Codeception tests are run. Connect to test DB.
    define( 'DB_NAME', 'test' );
} else {
    // Connect to local DB.
    define( 'DB_NAME', 'local' );
}
```

When Codeception tests are run, they will include either:
- The `HTTP_X_TEST_REQUEST` header for tests run using WPBrowser.
- The `HeadlessChrome` HTTP User Agent for tests run using WPWebDriver.

Our change above tells WordPress to use the test database for our test requests, whilst using the local/default database for any other requests.

### Install ChromeDriver

ChromeDriver is a headless (i.e. non-GUI) browser that our test suite uses to run End to End tests, interacting with the Kit
Plugin just as a user would - including full JavaScript execution, user inputs etc.

Download ChromeDriver for your Google Chrome version and OS from https://sites.google.com/chromium.org/driver/downloads?authuser=0

For Mac users, copy the unzipped executable to `/usr/local/bin`.

### Running the Test Suite

First, run the ChromeDriver in a separate Terminal window:

```bash
chromedriver --url-base=/wd/hub
```

![ChromeDriver Screenshot](/.github/docs/chromedriver.png?raw=true)

In a second Terminal window, in the Plugin's directory, build and run the a test to make sure there are no errors and that you have 
correctly setup your environment:

```bash
vendor/bin/codecept build
vendor/bin/codecept run EndToEnd general/other/ActivateDeactivatePluginCest --fail-fast
vendor/bin/codecept run Integration APITest:testAccessTokenRefreshedAndSavedWhenExpired --fail-fast
```

![Codeception Test Results](/.github/docs/codeception.png?raw=true)

Don't worry if you don't understand these commands; if your output looks similar to the above screenshot, and no test is prefixed with `E`, 
your environment is setup successfully. Our [Testing Guide](TESTING.md) covers this in more detail.

### Running PHP CodeSniffer

In the Plugin's directory, run the following command to run PHP_CodeSniffer, which will check the code meets WordPress' Coding Standards:

```bash
vendor/bin/phpcs ./ -v -s
```

![Coding Standards Test Results](/.github/docs/coding-standards.png?raw=true)

Again, don't worry if you don't understand these commands; if your output looks similar to the above screenshot, with no errors, your environment
is setup successfully.

### Running PHPStan

In the Plugin's directory, run the following command to run PHPStan, which will perform static analysis on the code, checking it meets required
standards, that PHP DocBlocks are valid, WordPress action/filter DocBlocks are valid etc:

```bash
vendor/bin/phpstan --memory-limit=1G
```

![PHPStan Test Results](/.github/docs/phpstan.png?raw=true)

Again, don't worry if you don't understand these commands; if your output looks similar to the above screenshot, with no errors, your environment
is setup successfully.

### Connect Plugin to Kit

Refer to the [Kit Help Article](https://help.kit.com/en/articles/2502591-how-to-set-up-the-kit-plugin-on-your-wordpress-website) to get started with using the WordPress Plugin.

## Docker

Using the Development Container, and either GitHub Codespaces or VS Code, it's quick and easy to get started:

### Clone Repository

Using your preferred Git client or command line, clone this repository to your local machine.

If you're new to this, use [GitHub Desktop](https://desktop.github.com/) or [Tower](https://www.git-tower.com/mac)

### Install Docker and Visual Studio Code

- Install [Docker Desktop](https://www.docker.com/products/docker-desktop/), or [Docker Engine](https://docs.docker.com/engine/) if you're developing on Linux
- Install [Visual Studio Code](https://code.visualstudio.com/download)

### Install and Run Dev Containers

- Open Visual Studio Code, and install the [Dev Containers]() extension
- Open the Visual Studio Code Command Palette (`Ctrl + Shift + P`)
- Type `>Dev Container: Rebuild and Reopen in Container`, pressing Enter

Visual Studio Code will switch to the Dev Container, loading the preconfigured Docker image for WordPress development, with the Terminal in Visual Studio Code showing the progress:

![Terminal](/.github/docs/dev-container.png?raw=true)

After a few minutes, your development environment should be ready. 

### Accessing WordPress

Click on the `Ports` tab, and navigate to the "Application" URL by hovering over the `Forwarded Address` and clicking the globe icon:

![Ports tab](/.github/docs/dev-container-ports.png?raw=true)

To access the WordPress Administration interface, append `/wp-admin` to the URL, using the following credentials:
- Username: `vipgo`
- Password: `password`

Once logged in, navigating to the Plugins screen will show the repository Plugin installed and active, along with some other common third party Plugins:

![Ports tab](/.github/docs/dev-container-plugins.png?raw=true)

### Running Codesniffer

In Visual Studio Code's Terminal, navigate to `/workspaces/convertkit-wordpress`, and run the following command to run PHP_CodeSniffer, which will check the code meets WordPress' Coding Standards:

```bash
vendor/bin/phpcs ./ -v -s
```

If no Terminal instance is open, you can create a new one by clicking the `+` icon.

![Terminal tab](/.github/docs/dev-container-terminal-plus.png?raw=true)

### Running PHPStan

In Visual Studio Code's Terminal, navigate to `/workspaces/convertkit-wordpress`, and run the following command to run PHPStan, which will perform static analysis on the code, checking it meets required
standards, that PHP DocBlocks are valid, WordPress action/filter DocBlocks are valid etc:

```bash
vendor/bin/phpstan --configuration phpstan-dev.neon --memory-limit=1G
```

If no Terminal instance is open, you can create a new one by clicking the `+` icon.

![Terminal tab](/.github/docs/dev-container-terminal-plus.png?raw=true)

### Testing

Codeception testing is currently unavailable when using Dev Containers or GitHub Codespaces. This may be available in a future PR.

### Next Steps

With your development environment setup, you'll probably want to start development, which is covered in the [Development Guide](DEVELOPMENT.md)