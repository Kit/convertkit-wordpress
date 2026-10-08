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
 * Version: 3.4.6
 * Author: Kit
 * Author URI: https://kit.com/
 * Text Domain: convertkit
 * License:     GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// Bail if Kit is already loaded.
if ( class_exists( 'WP_ConvertKit' ) ) {
	return;
}

// Define Kit Plugin paths and version number.
define( 'CONVERTKIT_PLUGIN_NAME', 'ConvertKit' ); // Used for user-agent in API class.
define( 'CONVERTKIT_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'CONVERTKIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CONVERTKIT_PLUGIN_PATH', __DIR__ );
define( 'CONVERTKIT_PLUGIN_VERSION', '3.4.6' );
define( 'CONVERTKIT_OAUTH_CLIENT_ID', 'HXZlOCj-K5r0ufuWCtyoyo3f688VmMAYSsKg1eGvw0Y' );
define( 'CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI', 'https://app.kit.com/wordpress/redirect' );
define( 'CONVERTKIT_NONCE_ACTION_OAUTH_CONNECT', 'convertkit-oauth-connect' );
define( 'CONVERTKIT_NONCE_ACTION_OAUTH_DISCONNECT', 'convertkit-oauth-disconnect' );
define( 'CONVERTKIT_MCP_APP_NAME', 'Kit WordPress Plugin: MCP Server' );

// Load shared classes, if they have not been included by another Kit Plugin.
if ( ! trait_exists( 'ConvertKit_API_Traits' ) && ! trait_exists( 'ConvertKit_API\ConvertKit_API_Traits' ) ) {
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

/**
 * Loads a Plugin class's file when the class is first used.
 *
 * @since   3.4.7
 *
 * @param   string $class_name     The class to load.
 */
function convertkit_autoloader( $class_name ) {

	// Bail if the class doesn't belong to this Plugin.
	if ( ! preg_match( '/^(ConvertKit_|CK_|WP_ConvertKit$)/i', $class_name ) ) {
		return;
	}

	// Define the file name e.g. ConvertKit_Admin_Notices = class-convertkit-admin-notices.php.
	$file_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

	// Define the folders to search, most used on the frontend first.
	$folders = array(
		'/includes/',
		'/includes/blocks/',
		'/includes/blocks/helpers/',
		'/includes/block-formatters/',
		'/includes/pre-publish-actions/',
		'/includes/plugin-sidebars/',
		'/includes/widgets/',
		'/includes/integrations/contactform7/',
		'/includes/integrations/divi/',
		'/includes/integrations/elementor/',
		'/includes/integrations/forminator/',
		'/includes/integrations/wishlist/',
		'/includes/mcp/',
		'/includes/mcp/abilities/category-settings/',
		'/includes/mcp/abilities/content/',
		'/includes/mcp/abilities/post-settings/',
		'/includes/mcp/abilities/resources/',
		'/includes/mcp/abilities/settings/',
		'/includes/mcp/prompts/',
		'/includes/mcp/resources/',
		'/admin/',
		'/admin/section/',
		'/admin/setup-wizard/',
		'/admin/importers/',
	);

	// Load the file from the first folder it's found in.
	foreach ( $folders as $folder ) {
		if ( file_exists( CONVERTKIT_PLUGIN_PATH . $folder . $file_name ) ) {
			require_once CONVERTKIT_PLUGIN_PATH . $folder . $file_name;
			return;
		}
	}

}
spl_autoload_register( 'convertkit_autoloader' );

// Load plugin files that are always required.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/cron-functions.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/functions.php';

// Contact Form 7 Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/contactform7/class-convertkit-contactform7.php';

// Forminator Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/forminator/class-convertkit-forminator.php';

// Impeka Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/class-convertkit-impeka.php';

// Uncode Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/class-convertkit-uncode.php';

// WishList Member Integration.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/wishlist/class-convertkit-wishlist.php';

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
