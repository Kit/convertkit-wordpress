#!/usr/bin/env bash

# Install Free Third Party WordPress Plugins 
wp plugin install admin-menu-editor autoptimize beaver-builder-lite-version block-visibility contact-form-7 classic-editor custom-post-type-ui elementor forminator jetpack-boost woocommerce wordpress-seo wpforms-lite litespeed-cache wp-crontrol wp-super-cache w3-total-cache wp-fastest-cache wp-optimize sg-cachepress

# Install Free Third Party WordPress Specific Version Plugins
wp plugin install https://downloads.wordpress.org/plugin/convertkit-for-woocommerce.1.6.4.zip http://cktestplugins.wpengine.com/wp-content/uploads/2024/01/convertkit-action-filter-tests.zip http://cktestplugins.wpengine.com/wp-content/uploads/2024/11/disable-doing-it-wrong-notices.zip

# Install Default WordPress Theme
# Install 2021 Theme, which provides support for widgets.
# 2021 Theme isn't included in WordPress 6.4 or higher, but is required for our widget tests.
wp theme install twentytwentyfive twentytwentyone

# WP_DEBUG is enabled in the image. No need to set it here.

# FS_METHOD = direct is required for WP_Filesystem to operate without suppressed PHP fopen() errors that trip up tests.
wp config set FS_METHOD direct

# DISALLOW_FILE_MODS = true is required to disable the block directory's "Available to Install" suggestions, which trips up
# tests that search and wait for block results.
wp config set DISALLOW_FILE_MODS true --raw

# Copy Plugin
cp -r /workspaces/convertkit /wp/wp-content/plugins/convertkit

# Run Composer in Plugin Directory to build
cd /wp/wp-content/plugins/convertkit
composer update --require-dev

# Activate Plugin
wp plugin activate convertkit