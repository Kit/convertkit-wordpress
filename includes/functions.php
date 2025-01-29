<?php
/**
 * ConvertKit general plugin functions.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Runs the activation and update routines when the plugin is activated.
 *
 * @since   1.9.7.4
 *
 * @param   bool $network_wide   Is network wide activation.
 */
function convertkit_plugin_activate( $network_wide ) {

	// Initialise Plugin.
	$convertkit = WP_ConvertKit();

	// Check if we are on a multisite install, activating network wide, or a single install.
	if ( ! is_multisite() || ! $network_wide ) {
		// Single Site activation.
		$convertkit->get_class( 'setup' )->activate();

		// Set a transient for 30 seconds to redirect to the setup screen on activation.
		set_transient( 'convertkit-setup', true, 30 );
	} else {
		// Multisite network wide activation.
		$sites = get_sites(
			array(
				'number' => 0,
			)
		);
		foreach ( $sites as $site ) {
			switch_to_blog( (int) $site->blog_id );
			$convertkit->get_class( 'setup' )->activate();
			restore_current_blog();
		}
	}

}

/**
 * Runs the activation and update routines when the plugin is activated
 * on a WordPress multisite setup.
 *
 * @since   1.9.7.4
 *
 * @param   WP_Site|int $site_or_blog_id    WP_Site or Blog ID.
 */
function convertkit_plugin_activate_new_site( $site_or_blog_id ) {

	// Check if $site_or_blog_id is a WP_Site or a blog ID.
	if ( is_a( $site_or_blog_id, 'WP_Site' ) ) {
		$site_or_blog_id = $site_or_blog_id->blog_id;
	}

	// Initialise Plugin.
	$convertkit = WP_ConvertKit();

	// Run installation routine.
	switch_to_blog( $site_or_blog_id );
	$convertkit->get_class( 'setup' )->activate();
	restore_current_blog();

}

/**
 * Runs the deactivation routine when the plugin is deactivated.
 *
 * @since   1.9.7.4
 *
 * @param   bool $network_wide   Is network wide deactivation.
 */
function convertkit_plugin_deactivate( $network_wide ) {

	// Initialise Plugin.
	$convertkit = WP_ConvertKit();

	// Check if we are on a multisite install, activating network wide, or a single install.
	if ( ! is_multisite() || ! $network_wide ) {
		// Single Site activation.
		$convertkit->get_class( 'setup' )->deactivate();
	} else {
		// Multisite network wide activation.
		$sites = get_sites(
			array(
				'number' => 0,
			)
		);
		foreach ( $sites as $site ) {
			switch_to_blog( (int) $site->blog_id );
			$convertkit->get_class( 'setup' )->deactivate();
			restore_current_blog();
		}
	}

}

/**
 * Helper method to get supported Post Types.
 *
 * @since   1.9.6
 *
 * @return  array   Post Types
 */
function convertkit_get_supported_post_types() {

	// Define supported Post Types.
	$post_types = array(
		'page',
		'post',
	);

	// If public Custom Post Types can be fetched, include them now.
	if ( function_exists( 'get_post_types' ) ) {
		// Get public Custom Post Types.
		$custom_post_types = (array) get_post_types(
			array(
				'public'   => true,

				// Don't include WordPress' built in Post Types, such as attachment, revisino and nav_menu_item.
				'_builtin' => false,
			)
		);

		$post_types = array_merge(
			$post_types,
			array_keys( $custom_post_types )
		);
	}

	/**
	 * Defines the Post Types that support ConvertKit Forms.
	 *
	 * @since   1.9.6
	 *
	 * @param   array   $post_types     Post Types
	 */
	$post_types = apply_filters( 'convertkit_get_supported_post_types', $post_types );

	return $post_types;

}

/**
 * Helper method to get supported Post Types for Restricted Content (Member's Content)
 *
 * @since   2.1.0
 *
 * @deprecated 2.4.3 No longer used by internal code and not recommended. Use `convertkit_get_supported_post_types` instead.
 *
 * @return  array   Post Types
 */
function convertkit_get_supported_restrict_content_post_types() {

	return convertkit_get_supported_post_types();

}

/**
 * Helper method to get registered Shortcodes.
 *
 * @since   1.9.6.5
 *
 * @return  array   Shortcodes
 */
function convertkit_get_shortcodes() {

	$shortcodes = array();

	/**
	 * Registers shortcodes for the ConvertKit Plugin.
	 *
	 * @since   1.9.6.5
	 *
	 * @param   array   $shortcodes     Shortcodes
	 */
	$shortcodes = apply_filters( 'convertkit_shortcodes', $shortcodes );

	return $shortcodes;

}

/**
 * Helper method to get registered Blocks.
 *
 * @since   1.9.6
 *
 * @return  array   Blocks
 */
function convertkit_get_blocks() {

	$blocks = array();

	/**
	 * Registers blocks for the ConvertKit Plugin.
	 *
	 * @since   1.9.6
	 *
	 * @param   array   $blocks     Blocks
	 */
	$blocks = apply_filters( 'convertkit_blocks', $blocks );

	return $blocks;

}

/**
 * Helper method to get registered Block formatters for Gutenberg.
 *
 * @since   2.2.0
 *
 * @return  array   Block formatters
 */
function convertkit_get_block_formatters() {

	$block_formatters = array();

	/**
	 * Registers block formatters in Gutenberg for the ConvertKit Plugin.
	 *
	 * @since   2.2.0
	 *
	 * @param   array   $block_formatters     Block formatters.
	 */
	$block_formatters = apply_filters( 'convertkit_get_block_formatters', $block_formatters );

	return $block_formatters;

}

/**
 * Helper method to get registered pre-publish actions.
 *
 * @since   2.4.0
 *
 * @return  array   Pre-publish actions
 */
function convertkit_get_pre_publish_actions() {

	$pre_publish_actions = array();

	/**
	 * Registers pre-publish actions for the ConvertKit Plugin.
	 *
	 * @since   2.4.0
	 *
	 * @param   array   $pre_publish_panels     Pre-publish actions.
	 */
	$pre_publish_actions = apply_filters( 'convertkit_get_pre_publish_actions', $pre_publish_actions );

	return $pre_publish_actions;

}

/**
 * Helper method to return the Plugin Settings Link
 *
 * @since   1.9.6
 *
 * @param   array $query_args     Optional Query Args.
 * @return  string                  Settings Link
 */
function convertkit_get_settings_link( $query_args = array() ) {

	$query_args = array_merge(
		$query_args,
		array(
			'page' => '_wp_convertkit_settings',
		)
	);

	return add_query_arg( $query_args, admin_url( 'options-general.php' ) );

}

/**
 * Helper method to return the Plugin Settings Link
 *
 * @since   2.2.4
 *
 * @param   array $query_args     Optional Query Args.
 * @return  string                  Settings Link
 */
function convertkit_get_setup_wizard_plugin_link( $query_args = array() ) {

	$query_args = array_merge(
		$query_args,
		array(
			'page' => 'convertkit-setup',
		)
	);

	return add_query_arg( $query_args, admin_url( 'options.php' ) );

}

/**
 * Helper method to return the URL the user needs to visit to register a ConvertKit account.
 *
 * @since   1.9.8.4
 *
 * @return  string  ConvertKit Registration URL.
 */
function convertkit_get_registration_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/users/signup'
	);

}

/**
 * Helper method to return the URL the user needs to visit to sign in to their ConvertKit account.
 *
 * @since   1.9.6.1
 *
 * @return  string  ConvertKit Login URL.
 */
function convertkit_get_sign_in_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/'
	);

}

/**
 * Helper method to return the URL the user needs to visit to manage thier billing.
 *
 * @since   2.2.7
 *
 * @return  string  ConvertKit Billing URL.
 */
function convertkit_get_billing_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/account_settings/billing/'
	);

}

/**
 * Helper method to return the URL the user needs to visit on the ConvertKit app to create a new Form or Landing Page.
 *
 * @since   2.2.3
 *
 * @return  string              ConvertKit App URL
 */
function convertkit_get_new_form_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/forms/new/'
	);

}

/**
 * Helper method to return the URL the user needs to visit to edit ConvertKit forms.
 *
 * @since   2.2.3
 *
 * @return  string  ConvertKit Form Editor URL.
 */
function convertkit_get_form_editor_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/forms'
	);

}

/**
 * Helper method to return the URL the user needs to visit on the ConvertKit app to create a new Landing Page.
 *
 * @since   2.5.5
 *
 * @return  string              ConvertKit App URL
 */
function convertkit_get_new_landing_page_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/pages/new/'
	);

}

/**
 * Helper method to return the URL the user needs to visit on the ConvertKit app to create a new Tag.
 *
 * @since   2.3.3
 *
 * @return  string  ConvertKit App URL.
 */
function convertkit_get_new_tag_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/subscribers/'
	);

}

/**
 * Helper method to return the URL the user needs to visit on the ConvertKit app to create a new Broadcast.
 *
 * @since   2.2.6
 *
 * @return  string  ConvertKit App URL.
 */
function convertkit_get_new_broadcast_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/campaigns/'
	);

}

/**
 * Helper method to return the URL the user needs to visit on the ConvertKit app to edit a draft Broadcast.
 *
 * @since   2.4.0
 *
 * @param   int $broadcast_id   ConvertKit Broadcast ID.
 * @return  string                  ConvertKit App URL.
 */
function convertkit_get_edit_broadcast_url( $broadcast_id ) {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		sprintf(
			'https://app.kit.com/campaigns/%s/draft',
			$broadcast_id
		)
	);

}

/**
 * Helper method to return the URL the user needs to visit on the ConvertKit app to create a new Product.
 *
 * @since   2.2.3
 *
 * @return  string  ConvertKit App URL.
 */
function convertkit_get_new_product_url() {

	return add_query_arg(
		array(
			'utm_source'  => 'wordpress',
			'utm_term'    => get_locale(),
			'utm_content' => 'convertkit',
		),
		'https://app.kit.com/products/new/'
	);

}

/**
 * Helper method to enqueue Select2 scripts for use within the ConvertKit Plugin.
 *
 * @since   1.9.6.4
 */
function convertkit_select2_enqueue_scripts() {

	wp_enqueue_script( 'convertkit-select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), CONVERTKIT_PLUGIN_VERSION, false );
	wp_enqueue_script( 'convertkit-admin-select2', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/select2.js', array( 'convertkit-select2' ), CONVERTKIT_PLUGIN_VERSION, false );

}

/**
 * Helper method to enqueue Select2 stylesheets for use within the ConvertKit Plugin.
 *
 * @since   1.9.6.4
 */
function convertkit_select2_enqueue_styles() {

	wp_enqueue_style( 'convertkit-select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), CONVERTKIT_PLUGIN_VERSION );
	wp_enqueue_style( 'convertkit-admin-select2', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/select2.css', array(), CONVERTKIT_PLUGIN_VERSION );

}

/**
 * Return the contents of the given local file.
 *
 * @since   2.2.2
 *
 * @param   string $local_file     Local file, including path.
 * @return  string                  File contents.
 */
function convertkit_get_file_contents( $local_file ) {

	// Bail if the file doesn't exist.
	if ( ! file_exists( $local_file ) ) {
		return '';
	}

	// Read file.
	$contents = file_get_contents( $local_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	// Return an empty string if the contents of the file could not be read.
	if ( ! $contents ) {
		return '';
	}

	// Return file's contents.
	return $contents;

}

/**
 * Returns a dropdown field commonly used for settings, comprising of:
 * - Do not subscribe
 * - Subscribe
 * - Subscribe to Form
 *
 * @since   2.5.2
 *
 * @param   string     $name                Field name.
 * @param   string     $value               Field value.
 * @param   string     $id                  Field ID attribute.
 * @param   string     $css_class           Field CSS class(es).
 * @param   string     $context             Resource context.
 * @param   bool|array $additional_options  Additional <option> key/value pairs.
 */
function convertkit_get_subscription_dropdown_field( $name, $value, $id, $css_class = '', $context = '', $additional_options = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

	// Load resource classes.
	$forms     = new ConvertKit_Resource_Forms( $context );
	$tags      = new ConvertKit_Resource_Tags( $context );
	$sequences = new ConvertKit_Resource_Sequences( $context );

	ob_start();
	include CONVERTKIT_PLUGIN_PATH . '/views/backend/subscription-dropdown-field.php';
	$output = trim( ob_get_clean() );

	// Return output.
	return $output;

}

/**
 * Helper method to safely call get_current_screen(), returning false
 * if the function is not available or returns null.
 *
 * Otherwise returns the given WP_Screen property.
 *
 * @since   2.5.9
 *
 * @param   string $property   WP_Screen property to return.
 * @return  bool|string
 */
function convertkit_get_current_screen( $property ) {

	// Bail if we cannot determine the screen.
	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	// Get screen.
	$screen = get_current_screen();

	// Bail if the screen couldn't be determined.
	if ( is_null( $screen ) ) {
		return false;
	}

	// Return property.
	return $screen->$property;

}

/**
 * Outputs the Intercom help widget script.
 *
 * @since   2.7.2
 */
function convertkit_output_intercom_messenger() {

	?>
	<script>
		const KIT_INTERCOM_APP_ID = 'e4n3xtxz';
		window.intercomSettings = {
		api_base: 'https://api-iam.intercom.io',
		app_id: KIT_INTERCOM_APP_ID
		};
	</script>

	<script>
	(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/' + KIT_INTERCOM_APP_ID;var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);};if(document.readyState==='complete'){l();}else if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
	</script>
	<?php

}
