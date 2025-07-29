<?php
/**
 * ConvertKit Caching Plugins class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Programmatically changes settings for third party caching Plugins which interfere with Forms
 * and Landing Pages loading correctly.
 *
 * @since   2.4.6
 */
class ConvertKit_Cache_Plugins {

	/**
	 * Holds external hosts to exclude from CSS and JS minification.
	 *
	 * @since   2.4.6
	 *
	 * @var     array
	 */
	public $exclude_hosts = array(
		'cdnjs.cloudflare.com',
		'pages.kit.com',
		'kit.com',
		'pages.convertkit.com',
		'convertkit.com',
	);

	/**
	 * Holds internal JS paths to exclude from JS minification.
	 * Supports regular expressions.
	 *
	 * @since   2.4.6
	 *
	 * @var     array
	 */
	public $exclude_plugin_js = array(
		'convertkit/resources/frontend/js/(.*).js',
	);

	/**
	 * Constructor
	 *
	 * @since   2.4.6
	 */
	public function __construct() {

		// Autoptimize: Exclude Forms from JS defer.
		add_filter( 'convertkit_output_script_footer', array( $this, 'autoptimize_exclude_js_defer' ) );
		add_filter( 'convertkit_resource_forms_output_script', array( $this, 'autoptimize_exclude_js_defer' ) );

		// Debloat: Exclude Forms from Delay Load JS.
		add_filter( 'debloat/defer_js_excludes', array( $this, 'exclude_hosts_from_minification' ) );
		add_filter( 'debloat/delay_js_excludes', array( $this, 'exclude_hosts_from_minification' ) );

		// Jetpack Boost: Exclude Forms from JS defer.
		add_filter( 'convertkit_output_script_footer', array( $this, 'jetpack_boost_exclude_js_defer' ) );
		add_filter( 'convertkit_resource_forms_output_script', array( $this, 'jetpack_boost_exclude_js_defer' ) );

		// LiteSpeed: Exclude Forms from JS defer.
		add_filter( 'convertkit_output_script_footer', array( $this, 'litespeed_cache_exclude_js_defer' ) );
		add_filter( 'convertkit_resource_forms_output_script', array( $this, 'litespeed_cache_exclude_js_defer' ) );

		// Perfmatters: Exclude Forms from Delay JavaScript.
		add_filter( 'convertkit_output_script_footer', array( $this, 'perfmatters_exclude_delay_js' ) );
		add_filter( 'convertkit_resource_forms_output_script', array( $this, 'perfmatters_exclude_delay_js' ) );
		add_filter( 'perfmatters_lazyload', array( $this, 'perfmatters_disable_lazy_loading_on_landing_pages' ) );

		// Siteground Speed Optimizer: Exclude Forms from JS combine.
		add_filter( 'convertkit_output_script_footer', array( $this, 'siteground_speed_optimizer_exclude_js_combine' ) );
		add_filter( 'convertkit_resource_forms_output_script', array( $this, 'siteground_speed_optimizer_exclude_js_combine' ) );

		// WP Rocket: Disable Caching and Minification on Landing Pages.
		add_action( 'convertkit_output_landing_page_before', array( $this, 'wp_rocket_disable_caching_and_minification_on_landing_pages' ) );

		// WP Rocket: Exclude Forms from JS minification and combine.
		add_filter( 'rocket_minify_excluded_external_js', array( $this, 'exclude_hosts_from_minification' ) );

		// WP Rocket: Exclude Forms from Delay JavaScript execution.
		add_filter( 'convertkit_output_script_footer', array( $this, 'wp_rocket_exclude_delay_js_execution' ) );
		add_filter( 'convertkit_resource_forms_output_script', array( $this, 'wp_rocket_exclude_delay_js_execution' ) );

	}

	/**
	 * Exclude ConvertKit scripts from Autoptimize's "Defer JS" setting.
	 *
	 * @since   2.4.9
	 *
	 * @param   array $script     Script key/value pairs to output as <script> tag.
	 * @return  array
	 */
	public function autoptimize_exclude_js_defer( $script ) {

		add_filter(
			'autoptimize_filter_js_exclude',
			function ( $exclusions ) use ( $script ) {

				$exclusions .= ', ' . $script['src'];
				return $exclusions;

			}
		);

		// Return original script.
		return $script;

	}

	/**
	 * Disable JS defer on ConvertKit scripts when the Jetpack Boost Plugin is installed, active
	 * and its "Defer Non-Essential JavaScript" setting is enabled.
	 *
	 * @since   2.4.6
	 *
	 * @param   array $script     Script key/value pairs to output as <script> tag.
	 * @return  array
	 */
	public function jetpack_boost_exclude_js_defer( $script ) {

		return array_merge(
			$script,
			array(
				'data-jetpack-boost' => 'ignore',
			)
		);

	}

	/**
	 * Disable JS defer on ConvertKit scripts when the LiteSpeed Cache Plugin is installed, active
	 * and its "Load JS Deferred" setting is enabled.
	 *
	 * @since   2.4.6
	 *
	 * @param   array $script     Script key/value pairs to output as <script> tag.
	 * @return  array
	 */
	public function litespeed_cache_exclude_js_defer( $script ) {

		return array_merge(
			$script,
			array(
				'data-no-defer' => '1',
			)
		);

	}

	/**
	 * Exclude ConvertKit scripts from Perfmatters' "Delay JS" setting.
	 *
	 * @since   2.4.7
	 *
	 * @param   array $script     Script key/value pairs to output as <script> tag.
	 * @return  array
	 */
	public function perfmatters_exclude_delay_js( $script ) {

		add_filter(
			'perfmatters_delay_js_exclusions',
			function ( $exclusions ) use ( $script ) {

				$exclusions[] = $script['src'];
				return $exclusions;

			}
		);

		// Return original script.
		return $script;

	}

	/**
	 * Disable lazy loading in Perfmatters when a WordPress Page configured to display a
	 * ConvertKit Landing Page is viewed.
	 *
	 * @since   2.5.1
	 *
	 * @param   bool $enabled    Lazy loading enabled.
	 * @return  bool
	 */
	public function perfmatters_disable_lazy_loading_on_landing_pages( $enabled ) {

		// If the request isn't for a Page, don't change lazy loading settings.
		if ( ! is_page( get_the_ID() ) ) {
			return $enabled;
		}

		// If no landing page is specified for the Post, don't change lazy loading settings.
		$post_settings = new ConvertKit_Post( get_the_ID() );
		if ( ! $post_settings->has_landing_page() ) {
			return $enabled;
		}

		// ConvertKit Landing Page is going to be displayed.
		// Disable Perfmatters Lazy Loading so that the Landing Page images display.
		return false;

	}

	/**
	 * Disable JS combining on ConvertKit scripts when the Siteground Speed Optimizer Plugin is installed, active
	 * and its "Combine JavaScript Files" setting is enabled.
	 *
	 * @since   2.4.6
	 *
	 * @param   array $script     Script key/value pairs to output as <script> tag.
	 * @return  array
	 */
	public function siteground_speed_optimizer_exclude_js_combine( $script ) {

		add_filter(
			'sgo_javascript_combine_excluded_external_paths',
			function ( $excluded_paths ) use ( $script ) {

				$excluded_paths[] = $script['src'];
				return $excluded_paths;

			}
		);

		// Return original script.
		return $script;

	}

	/**
	 * Disable caching and minification when a WordPress Page configured to display a
	 * ConvertKit Landing Page is viewed.
	 *
	 * @since   2.4.6
	 */
	public function wp_rocket_disable_caching_and_minification_on_landing_pages() {

		add_filter( 'rocket_minify_excluded_external_js', array( $this, 'exclude_hosts_from_minification' ) );
		add_filter( 'rocket_exclude_css', array( $this, 'exclude_hosts_from_minification' ) );
		add_filter( 'rocket_exclude_js', array( $this, 'exclude_local_js_from_minification' ) );
		add_filter( 'do_rocket_lazyload', '__return_false' );

	}

	/**
	 * Disable WP Rocket's "Delay JavaScript execution" on ConvertKit scripts.
	 *
	 * @since   2.4.7
	 *
	 * @see     https://docs.wp-rocket.me/article/1349-delay-javascript-execution
	 *
	 * @param   array $script     Script key/value pairs to output as <script> tag.
	 * @return  array
	 */
	public function wp_rocket_exclude_delay_js_execution( $script ) {

		return array_merge(
			$script,
			array(
				'nowprocket' => '',
			)
		);

	}

	/**
	 * Appends the $exclude_hosts property to an array of existing hosts excluded from
	 * minification.
	 *
	 * @since   2.4.6
	 *
	 * @param   array $hosts  External hosts to ignore.
	 * @return  array
	 */
	public function exclude_hosts_from_minification( $hosts ) {

		return array_merge( $hosts, $this->exclude_hosts );

	}

	/**
	 * Appends the $exclude_plugin_js property to an array of existing JS files excluded from
	 * minification.
	 *
	 * @since   2.4.6
	 *
	 * @param   array $scripts  Internal JS scripts to ignore.
	 * @return  array
	 */
	public function exclude_local_js_from_minification( $scripts ) {

		return array_merge( $scripts, $this->exclude_plugin_js );

	}

}
