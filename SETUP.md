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

Copy the `.env.example` file to `.env.testing` in the root of this repository, changing folder and database credentials as necessary:

```
TEST_SITE_DB_DSN=mysql:host=localhost;dbname=test
TEST_SITE_DB_HOST=localhost
TEST_SITE_DB_NAME=test
TEST_SITE_DB_USER=root
TEST_SITE_DB_PASSWORD=root
TEST_SITE_TABLE_PREFIX=wp_
TEST_SITE_ADMIN_USERNAME=admin
TEST_SITE_ADMIN_PASSWORD=password
TEST_SITE_WP_ADMIN_PATH=/wp-admin
WP_ROOT_FOLDER="/Users/tim/Local Sites/convertkit-github/app/public"
TEST_DB_NAME=test
TEST_DB_HOST=localhost
TEST_DB_USER=root
TEST_DB_PASSWORD=root
TEST_TABLE_PREFIX=wp_
TEST_SITE_WP_URL=http://convertkit.local
TEST_SITE_WP_DOMAIN=convertkit.local
TEST_SITE_ADMIN_EMAIL=wordpress@convertkit.local
CONVERTKIT_API_KEY_NO_DATA=
CONVERTKIT_API_SECRET_NO_DATA=
CONVERTKIT_API_KEY=
CONVERTKIT_API_SECRET=
CONVERTKIT_API_FORM_NAME="Page Form"
CONVERTKIT_API_FORM_ID="2765139"
CONVERTKIT_API_FORM_FORMAT_MODAL_NAME="Modal Form"
CONVERTKIT_API_FORM_FORMAT_SLIDE_IN_NAME="Slide In Form"
CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME="Sticky Bar Form"
CONVERTKIT_API_LANDING_PAGE_NAME="Landing Page"
CONVERTKIT_API_LANDING_PAGE_ID="2765196"
CONVERTKIT_API_LANDING_PAGE_CHARACTER_ENCODING_NAME="Character Encoding"
CONVERTKIT_API_LEGACY_FORM_NAME="Legacy Form"
CONVERTKIT_API_LEGACY_FORM_ID="470099"
CONVERTKIT_API_LEGACY_FORM_SHORTCODE="[convertkit form=5281783]"
CONVERTKIT_API_LEGACY_LANDING_PAGE_NAME="Legacy Landing Page"
CONVERTKIT_API_LEGACY_LANDING_PAGE_ID="470103"
CONVERTKIT_API_LEGACY_LANDING_PAGE_URL="https://app.kit.com/landing_pages/470103"
CONVERTKIT_API_SEQUENCE_ID="1030824"
CONVERTKIT_API_TAG_NAME="wordpress"
CONVERTKIT_API_TAG_ID="2744672"
CONVERTKIT_API_SUBSCRIBER_EMAIL="optin@n7studios.com"
CONVERTKIT_API_SUBSCRIBER_ID="1579118532"
CONVERTKIT_API_THIRD_PARTY_INTEGRATIONS_FORM_NAME="Third Party Integrations Form"
CONVERTKIT_API_THIRD_PARTY_INTEGRATIONS_FORM_ID="3003590"
```

### Codeception

Create a `codeception.yml` file in the root of the repository, with the following contents:
```yaml
params:
    - .env.testing
```

This tells Codeception to read the above `.env.testing` file when testing on the local development enviornment.

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

ChromeDriver is a headless (i.e. non-GUI) browser that our test suite uses to run Acceptance tests, interacting with the Kit
Plugin just as a user would - including full JavaScript execution, user inputs etc.

Download ChromeDriver for your Google Chrome version and OS from https://sites.google.com/chromium.org/driver/downloads?authuser=0

For Mac users, copy the unzipped executable to `/usr/local/bin`.

### Running the Test Suite

First, run the ChromeDriver in a separate Terminal window:

```bash
chromedriver --url-base=/wd/hub
```

![ChromeDriver Screenshot](/.github/docs/chromedriver.png?raw=true)

In a second Terminal window, in the Plugin's directory, build and run the tests to make sure there are no errors and that you have 
correctly setup your environment:

```bash
vendor/bin/codecept build
vendor/bin/codecept run acceptance
vendor/bin/codecept run wpunit
```

![Codeception Test Results](/.github/docs/codeception.png?raw=true)

Don't worry if you don't understand these commands; if your output looks similar to the above screenshot, and no test is prefixed with `E`, 
your environment is setup successfully.

### Running CodeSniffer

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

Refer to the [Kit Help Article](https://help.kit.com/en/articles/2502591-getting-started-the-wordpress-plugin) to get started with
using the WordPress Plugin.

## Docker

Using the Development Container, and either GitHub Codespaces or VS Code, it's quick and easy to get started:

- Install [Docker Desktop](https://www.docker.com/products/docker-desktop/), or [Docker Engine](https://docs.docker.com/engine/) if you're developing on Linux
- Install [Visual Studio Code]()
- Open Visual Studio Code, and install the [Dev Containers]() extension
- Open the Visual Studio Code Command Palette (`Ctrl + Shift + P`)
- Type `>Dev Container: Rebuild and Reopen in Container`, pressing Enter

Visual Studio Code will switch to the Dev Container, loading the preconfigured Docker image for WordPress development, with the Terminal in Visual Studio Code showing the progress:

[]

After a few minutes, your development environment should be ready.  Click on the Ports tab, and navigate to the "Application" URL:

[]

[]

### Accessing WordPress

To access the WordPress Administration interface, append `/wp-admin` to the URL, using the following credentials:
- Username: `vipgo`
- Password: `password`

Once logged in, navigating to the Plugins screen will show the repository Plugin installed and active, along with some other common third party Plugins:

[]

### Running Codesniffer

In Visual Studio Code's Terminal, navigate to `/workspaces/convertkit-wordpress`, and run the following command to run PHP_CodeSniffer, which will check the code meets WordPress' Coding Standards:

```bash
vendor/bin/phpcs ./ -v -s
```

If no Terminal instance is open, you can create a new one by clicking the `+` icon.

### Running PHPStan

In Visual Studio Code's Terminal, navigate to `/workspaces/convertkit-wordpress`, and run the following command to run PHPStan, which will perform static analysis on the code, checking it meets required
standards, that PHP DocBlocks are valid, WordPress action/filter DocBlocks are valid etc:

```bash
vendor/bin/phpstan --memory-limit=1G
```

If no Terminal instance is open, you can create a new one by clicking the `+` icon.

### Testing

Codeception testing is currently unavailable when using Dev Containers or GitHub Codespaces. This may be available in a future PR.

### Next Steps

With your development environment setup, you'll probably want to start development, which is covered in the [Development Guide](DEVELOPMENT.md)