# Deployment Guide

This document describes the workflow for deploying a Plugin update from GitHub to wordpress.org.

## Merge Pull Requests

Merge the approved Pull Request(s) to the `main` branch.

An *approved* Pull Request is when a PR passes all tests **and** has been approved by **one or more** reviewers.

## Generate Localization File and Action/Filter Documentation

On your local machine, switch to the `main` branch.

Run the `.scripts/build.sh` script, which will:

- Generate the `languages/convertkit.pot` file
- Generate the [ACTIONS-FILTERS.md](ACTIONS-FILTERS.md) file
- Produce a `convertkit.zip` Plugin file, which may be used for any manual testing on other environments

## Update the Plugin's Version Number

We follow [Semantic Versioning](https://semver.org/).

- In `wp-convertkit.php`, change the Version header to the new version number.
- In `wp-convertkit.php`, change the `CONVERTKIT_PLUGIN_VERSION` constant to the new version number.

## Update the Plugin's readme.txt Changelog

Provide meaningful, verbose updates to the Changelog, in the following format:

```
### x.x.x yyyy-mm-dd
* Added: Text Editor: Quicktag Buttons for inserting Kit Forms and Custom Content
* Fix: Integration: Contact Form 7: If Contact Form 7 Form is mapped to a Kit Form, send the data to Kit if form validation passes but Contact Form 7 could or could not send an email
```

Generic changelog items such as `Fix: Various bugfixes` or `Several edge-case bug fixes` should be avoided.  They don't tell users (or us, as developers)
what took place in this version.

Each line in the changelog should start with `Added` or `Fix`.

## Commit Changes

Commit the updated files, which should comprise of:

- `languages/convertkit.pot`
- `readme.txt`
- `wp-convertkit.php`
- `ACTIONS-FILTERS.md`

## Create a New Release

[Create a New Release](https://github.com/ConvertKit/convertkit-wordpress/releases/new), completing the following:

- Choose a tag: Click this button and enter the new version number (e.g. `1.9.6`)
- Release title: The version number (e.g. `1.9.6`)
- Describe this release: The changelog entered in the `readme.txt` file for this new version:

![New Release Screen](/.github/docs/new-release.png?raw=true)

## Publish the Release

When you're happy with the above, click `Publish Release`.

This will then trigger the [deploy.yml](.github/workflows/deploy.yml) workflow, which will upload this new version to the wordpress.org
repository, making it available to download / update for WordPress users.

Any Composer packages included in the `composer.json`'s `require` section will be included in the deployment.
Packages in the `require-dev` section are **not** included.

The release will also be available to view on the [Releases](https://github.com/ConvertKit/convertkit-wordpress/releases) section of this GitHub repository.