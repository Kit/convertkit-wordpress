<?php
/**
 * ConvertKit Cache Plugins class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * If Restrict Content is enabled, run functions for common caching plugins
 * to either:
 * - automatically configure them to disable caching when the ck_subscriber_id is present,
 * - show a notice in the WordPress Administration interface where we cannot automatically configure a caching plugin.
 *
 * Currently supported third party Plugins:
 * - Litespeed Cache
 * - WP-Optimize
 * - W3 Total Cache
 * - WP Fastest Cache
 * - WP Super Cache
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Cache_Plugins {

	/**
	 * Holds the cookie name.
	 *
	 * @since   2.2.2
	 *
	 * @var     string
	 */
	private $key = 'ck_subscriber_id';

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   2.2.2
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'maybe_configure_cache_plugins' ) );

	}

	/**
	 * If Restrict Content is enabled, run functions for common caching plugins
	 * to either configure them to disable caching when the ck_subscriber_id
	 * is present, or show a notice in the WordPress Administration interface
	 * where we cannot automatically configure a caching plugin.
	 *
	 * @since   2.2.2
	 */
	public function maybe_configure_cache_plugins() {

		// If no Pages, Posts or CPTs are configured to use Restrict Content, don't
		// configure any caching plugins.
		if ( ! $this->restrict_content_enabled() ) {
			return;
		}

		$this->litespeed_cache();
		$this->w3_total_cache();
		$this->wp_fastest_cache();
		$this->wp_optimize();
		$this->wp_rocket();
		$this->wp_super_cache();

	}

	/**
	 * Check if any Pages, Posts or CPTs are configured to use Restrict Content.
	 *
	 * @since   2.7.6
	 *
	 * @return  bool
	 */
	private function restrict_content_enabled() {

		// Check if any Pages, Posts or CPTs are configured to use Restrict Content.
		$query = new WP_Query(
			array(
				'post_type'      => convertkit_get_supported_post_types(),
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => '_wp_convertkit_post_meta',
						'value'   => '"restrict_content";',
						'compare' => 'LIKE',
					),
					array(
						'key'     => '_wp_convertkit_post_meta',
						'value'   => '"restrict_content";s:1:"0"',
						'compare' => 'NOT LIKE',
					),
				),
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return $query->have_posts();

	}

	/**
	 * Show a notice in the WordPress Administration interface if
	 * Litespeed Cache is active, its caching enabled and no rule to disable caching
	 * exists when the ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 */
	public function litespeed_cache() {

		// Bail if the Litespeed Cache plugin is not active.
		if ( ! is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
			return;
		}

		// Bail if caching isn't enabled in the Litespeed Plugin.
		$config = new \LiteSpeed\Base();
		if ( ! $config->conf( \LiteSpeed\Base::O_CACHE ) ) {
			return;
		}

		// If the exclusion rule exists, no need to modify anything.
		if ( in_array( $this->key, $config->conf( \LiteSpeed\Base::O_CACHE_EXC_COOKIES ), true ) ) {
			return;
		}

		// Litespeed Cache is active, with page caching enabled, but has not been configured to disable
		// caching when the ck_subscriber_id cookie is present.
		// Show a notice in the WordPress Administration, as we can't directly write to the
		// Litespeed Cache configuration files.
		WP_ConvertKit()->get_class( 'admin_notices' )->add( 'litespeed_cache' );

		// Define the output of the persistent notice.
		add_filter( 'convertkit_admin_notices_output_litespeed_cache', array( $this, 'litespeed_cache_notice' ) );

	}

	/**
	 * Define the notice text to display in the WordPress Administration interface
	 * when Litespeed Cache is active, its caching enabled and no rule to disable caching
	 * exists when the ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 *
	 * @param   string $notice     Notice text.
	 * @return  string              Notice text
	 */
	public function litespeed_cache_notice( $notice ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return sprintf(
			'%s %s %s %s',
			esc_html__( 'Kit: Member Content: Please add', 'convertkit' ),
			'<code>ck_subscriber_id</code>',
			sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					admin_url(
						add_query_arg(
							array(
								'page' => 'litespeed-cache#excludes',
							),
							'admin.php'
						)
					)
				),
				esc_html__( 'to Litespeed Cache\'s "Do Not Cache Cookies" setting by clicking here.', 'convertkit' )
			),
			esc_html__( 'Failing to do so will result in errors.', 'convertkit' )
		);

	}

	/**
	 * Show a notice in the WordPress Administration interface if
	 * W3 Total Cache is active, its caching enabled and no rule to disable caching
	 * exists when the ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 */
	public function w3_total_cache() {

		// Bail if the W3 Total Cache plugin is not active.
		if ( ! is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			return;
		}

		// Bail if caching isn't enabled in the W3 Total Cache Plugin.
		$config = new \W3TC\Config();
		if ( ! $config->get_boolean( 'pgcache.enabled' ) ) {
			return;
		}

		// If the exclusion rule exists, no need to modify anything.
		if ( in_array( $this->key, $config->get_array( 'pgcache.reject.cookie' ), true ) ) {
			return;
		}

		// W3 Total Cache is active, with page caching enabled, but has not been configured to disable
		// caching when the ck_subscriber_id cookie is present.
		// Show a notice in the WordPress Administration, as we can't directly write to the W3
		// Total Cache configuration files.
		WP_ConvertKit()->get_class( 'admin_notices' )->add( 'w3_total_cache' );

		// Define the output of the persistent notice.
		add_filter( 'convertkit_admin_notices_output_w3_total_cache', array( $this, 'w3_total_cache_notice' ) );

	}

	/**
	 * Define the notice text to display in the WordPress Administration interface
	 * when W3 Total Cache is active, its caching enabled and no rule to disable caching
	 * exists when the ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 *
	 * @param   string $notice     Notice text.
	 * @return  string              Notice text
	 */
	public function w3_total_cache_notice( $notice ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return sprintf(
			'%s %s %s %s',
			esc_html__( 'Kit: Member Content: Please add', 'convertkit' ),
			'<code>ck_subscriber_id</code>',
			sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					admin_url(
						add_query_arg(
							array(
								'page' => 'w3tc_pgcache#pgcache_reject_cookie',
							),
							'admin.php'
						)
					)
				),
				esc_html__( 'to W3 Total Cache\'s "Rejected Cookies" setting by clicking here.', 'convertkit' )
			),
			esc_html__( 'Failing to do so will result in errors.', 'convertkit' )
		);

	}

	/**
	 * Add a rule to WP Fastest Cache to prevent caching when
	 * the ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 */
	public function wp_fastest_cache() {

		// Bail if the WP Fastest Cache plugin is not active.
		if ( ! is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ) {
			return;
		}

		// Fetch exclusion rules.
		$exclusion_rules = get_option( 'WpFastestCacheExclude' );

		// If the exclusion exists, no need to modify anything.
		if ( strpos( $exclusion_rules, $this->key ) !== false ) {
			return;
		}

		// Define the rule.
		$rule = array(
			'prefix'  => 'contain',
			'content' => $this->key,
			'type'    => 'cookie',
		);

		// If no rules exist, add the rule.
		if ( ! $exclusion_rules ) {
			return update_option( 'WpFastestCacheExclude', wp_json_encode( array( $rule ) ) );
		}

		// Append the rule to the existing ruleset.
		$exclusion_rules   = json_decode( $exclusion_rules );
		$exclusion_rules[] = $rule;
		return update_option( 'WpFastestCacheExclude', wp_json_encode( $exclusion_rules ) );

	}

	/**
	 * Add a rule to WP-Optimize to prevent caching when
	 * the ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 */
	public function wp_optimize() {

		// Bail if the WP Fastest Cache plugin is not active.
		if ( ! is_plugin_active( 'wp-optimize/wp-optimize.php' ) ) {
			return;
		}

		// Sanity check that we can access WP-Optimize's configuration class.
		if ( ! class_exists( 'WPO_Cache_Config' ) ) {
			return;
		}

		// Fetch settings.
		$config   = new WPO_Cache_Config();
		$settings = $config->get();

		// Check the exception cookies array key exists in the settings.
		if ( ! array_key_exists( 'cache_exception_cookies', $settings ) ) {
			$settings['cache_exception_cookies'] = array();
		}

		// If the cookie exception exists, no need to modify anything.
		if ( in_array( $this->key, $config->get_option( 'cache_exception_cookies' ), true ) !== false ) {
			return;
		}

		// Add cookie to exceptions.
		$settings['cache_exception_cookies'][] = $this->key;

		// Update configuration to include excluding caching for the ck_subscriber_id cookie.
		return $config->update( $settings );

	}

	/**
	 * Add a rule to WP Rocket to prevent caching when
	 * the ck_subscriber_id cookie is present.
	 *
	 * @since   2.7.0
	 */
	public function wp_rocket() {

		// Bail if the WP Rocket plugin is not active.
		if ( ! is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) {
			return;
		}

		// Sanity check that we can access WP-Optimize's configuration class.
		if ( ! function_exists( 'update_rocket_option' ) ) {
			return;
		}

		// Fetch settings.
		$settings = get_rocket_option( 'cache_reject_cookies', array() );

		// If the cookie exception exists, no need to modify anything.
		if ( in_array( $this->key, $settings, true ) ) {
			return;
		}

		// Add cookie to exceptions.
		$settings[] = $this->key;

		// Update configuration to include excluding caching for the ck_subscriber_id cookie.
		update_rocket_option( 'cache_reject_cookies', $settings );

	}

	/**
	 * Show a notice in the WordPress Administration interface if
	 * WP Super Cache is active, its caching enabled and no rule to disable caching
	 * exists when the ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 */
	public function wp_super_cache() {

		// Bail if the WP Super Cache plugin is not active.
		if ( ! is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
			return;
		}

		// Bail if caching isn't enabled in the WP Super Cache Plugin.
		global $cache_enabled;
		if ( ! $cache_enabled ) {
			return;
		}

		// If the exclusion rule exists, no need to modify anything.
		global $wpsc_rejected_cookies;
		if ( is_array( $wpsc_rejected_cookies ) && in_array( $this->key, $wpsc_rejected_cookies, true ) ) {
			return;
		}

		// WP Super Cache is active, with caching enabled, but has not been configured to disable
		// caching when the ck_subscriber_id cookie is present.
		// Show a notice in the WordPress Administration, as we can't directly write to the WP
		// Super Cache configuration files.
		WP_ConvertKit()->get_class( 'admin_notices' )->add( 'wp_super_cache' );

		// Define the output of the persistent notice.
		add_filter( 'convertkit_admin_notices_output_wp_super_cache', array( $this, 'wp_super_cache_notice' ) );

	}

	/**
	 * Define the notice text to display in the WordPress Administration interface
	 * when WP Super Cache is active, its caching enabled and no rule to disable caching
	 * exists when the ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 *
	 * @param   string $notice     Notice text.
	 * @return  string              Notice text
	 */
	public function wp_super_cache_notice( $notice ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return sprintf(
			'%s %s %s %s',
			esc_html__( 'Kit: Member Content: Please add', 'convertkit' ),
			'<code>ck_subscriber_id</code>',
			sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					admin_url(
						add_query_arg(
							array(
								'page' => 'wpsupercache',
								'tab'  => 'settings#rejectcookies',
							),
							'options-general.php'
						)
					)
				),
				esc_html__( 'to WP Super Cache\'s "Rejected Cookies" setting by clicking here.', 'convertkit' )
			),
			esc_html__( 'Failing to do so will result in errors.', 'convertkit' )
		);

	}

}
