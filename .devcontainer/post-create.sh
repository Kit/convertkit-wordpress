#!/usr/bin/env bash

# Install Free Third Party WordPress Plugins 
wp plugin install classic-editor custom-post-type-ui elementor forminator woocommerce

# Install Default WordPress Theme
wp theme install twentytwentyfive

# Symlink Plugin
ln -s /workspaces/convertkit-wordpress /wp/wp-content/plugins/convertkit

# Run Composer in Plugin Directory to build
cd /wp/wp-content/plugins/convertkit
composer update

# Activate Plugin
wp plugin activate convertkit