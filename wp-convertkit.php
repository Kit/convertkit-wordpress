<?php
/**
 * Kit (formerly ConvertKit) WordPress Plugin.
 *
 * @package ConvertKit
 * @author ConvertKit
 *
 * @wordpress-plugin
 * Plugin Name: Kit (formerly ConvertKit)
 * Plugin URI: https://kit.com/
 * Description: Display Kit (formerly ConvertKit) email subscription forms, landing pages, products, broadcasts and more.
 * Version: 2.8.0
 * Author: Kit
 * Author URI: https://kit.com/
 * Text Domain: convertkit
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Bail if Kit is alread loaded.
if ( class_exists( 'WP_ConvertKit' ) ) {
	return;
}

// Define Kit Plugin paths and version number.
define( 'CONVERTKIT_PLUGIN_NAME', 'ConvertKit' ); // Used for user-agent in API class.
define( 'CONVERTKIT_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'CONVERTKIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CONVERTKIT_PLUGIN_PATH', __DIR__ );
define( 'CONVERTKIT_PLUGIN_VERSION', '2.8.0' );
define( 'CONVERTKIT_OAUTH_CLIENT_ID', 'HXZlOCj-K5r0ufuWCtyoyo3f688VmMAYSsKg1eGvw0Y' );
define( 'CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI', 'https://app.kit.com/wordpress/redirect' );

// Load shared classes, if they have not been included by another Kit Plugin.
if ( ! trait_exists( 'ConvertKit_API_Traits' ) ) {
	require_once CONVERTKIT_PLUGIN_PATH . '/vendor/convertkit/convertkit-wordpress-libraries/src/class-convertkit-api-traits.php';
}
if ( ! class_exists( 'ConvertKit_API_V4' ) ) {
	require_once CONVERTKIT_PLUGIN_PATH . '/vendor/convertkit/convertkit-wordpress-libraries/src/class-convertkit-api-v4.php';
}
if ( ! class_exists( 'ConvertKit_Log' ) ) {
	require_once CONVERTKIT_PLUGIN_PATH . '/vendor/convertkit/convertkit-wordpress-libraries/src/class-convertkit-log.php';
}
if ( ! class_exists( 'ConvertKit_Resource_V4' ) ) {
	require_once CONVERTKIT_PLUGIN_PATH . '/vendor/convertkit/convertkit-wordpress-libraries/src/class-convertkit-resource-v4.php';
}
if ( ! class_exists( 'ConvertKit_Review_Request' ) ) {
	require_once CONVERTKIT_PLUGIN_PATH . '/vendor/convertkit/convertkit-wordpress-libraries/src/class-convertkit-review-request.php';
}

// Load plugin files that are always required.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/cron-functions.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/functions.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-wp-convertkit.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-ajax.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-broadcasts-exporter.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-broadcasts-importer.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-cache-plugins.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-cron.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-gutenberg.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-media-library.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-output.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-output-broadcasts.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-output-restrict-content.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-post.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-preview-output.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-resource-creator-network-recommendations.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-resource-forms.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-resource-landing-pages.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-resource-posts.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-resource-products.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-resource-sequences.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-resource-tags.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-settings.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-settings-broadcasts.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-settings-restrict-content.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-setup.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-shortcodes.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-subscriber.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-term.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-user.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/class-convertkit-widgets.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/blocks/class-convertkit-block.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/blocks/class-convertkit-block-broadcasts.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/blocks/class-convertkit-block-content.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/blocks/class-convertkit-block-form-trigger.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/blocks/class-convertkit-block-form.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/blocks/class-convertkit-block-product.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/block-formatters/class-convertkit-block-formatter.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/block-formatters/class-convertkit-block-formatter-form-link.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/block-formatters/class-convertkit-block-formatter-product-link.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/pre-publish-actions/class-convertkit-pre-publish-action.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/pre-publish-actions/class-convertkit-pre-publish-action-broadcast-export.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/widgets/class-ck-widget-form.php';

// Admin classes.
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-bulk-edit.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-quick-edit.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-cache-plugins.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-category.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-landing-page.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-notices.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-post.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-refresh-resources.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-restrict-content.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-settings.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-tinymce.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-user.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-convertkit-admin-setup-wizard.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/class-multi-value-field-table.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/section/class-convertkit-admin-section-base.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/section/class-convertkit-admin-section-broadcasts.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/section/class-convertkit-admin-section-general.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/section/class-convertkit-admin-section-oauth.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/section/class-convertkit-admin-section-restrict-content.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/section/class-convertkit-admin-section-tools.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/setup-wizard/class-convertkit-admin-setup-wizard-plugin.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/setup-wizard/class-convertkit-admin-setup-wizard-landing-page.php';
require_once CONVERTKIT_PLUGIN_PATH . '/admin/setup-wizard/class-convertkit-admin-setup-wizard-restrict-content.php';

// Contact Form 7 Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/contactform7/class-convertkit-contactform7-admin-section.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/contactform7/class-convertkit-contactform7.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/contactform7/class-convertkit-contactform7-settings.php';

// Divi Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/divi/class-convertkit-divi.php';

// Elementor Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/elementor/class-convertkit-elementor.php';

// Forminator Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/forminator/class-convertkit-forminator-admin-section.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/forminator/class-convertkit-forminator.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/forminator/class-convertkit-forminator-settings.php';

// Uncode Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/class-convertkit-uncode.php';

// WishList Member Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/wishlist/class-convertkit-wishlist-admin-section.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/wishlist/class-convertkit-wishlist.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/wishlist/class-convertkit-wishlist-settings.php';

// WooCommerce Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/woocommerce/class-convertkit-woocommerce-product-form.php';

// Register Plugin activation and deactivation functions.
register_activation_hook( __FILE__, 'convertkit_plugin_activate' );
add_action( 'wp_insert_site', 'convertkit_plugin_activate_new_site' );
add_action( 'activate_blog', 'convertkit_plugin_activate_new_site' );
register_deactivation_hook( __FILE__, 'convertkit_plugin_deactivate' );

/**
 * Main function to return Plugin instance.
 *
 * @since   1.9.6
 */
function WP_ConvertKit() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName

	return WP_ConvertKit::get_instance();

}

// Finally, initialize the Plugin.
WP_ConvertKit();
