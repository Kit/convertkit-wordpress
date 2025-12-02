<?php
/**
 * ConvertKit Admin Refresh Resources class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers scripts, which run when a Refresh button is clicked, to refresh resources
 * asynchronously whilst editing a Page, Post or Category.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Refresh_Resources {

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.9.8.0
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

	}

	/**
	 * Register REST API routes.
	 *
	 * @since   3.1.0
	 */
	public function register_routes() {

		// Register route to return all blocks registered by the Plugin.
		register_rest_route(
			'kit/v1',
			'/resources/refresh/(?P<resource>[a-zA-Z0-9-_]+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'refresh_resources' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

	}

	/**
	 * Refreshes resources (forms, landing pages or tags) from the API, returning them as a JSON string.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   WP_REST_Request $request    Request object.
	 * @return  WP_REST_Response|WP_Error               Response object.
	 */
	public function refresh_resources( $request ) {

		// Get resource type.
		$resource = $request->get_param( 'resource' );

		// Fetch resources.
		switch ( $resource ) {
			case 'forms':
				$forms   = new ConvertKit_Resource_Forms( 'user_refresh_resource' );
				$results = $forms->refresh();
				break;

			case 'landing_pages':
				$landing_pages = new ConvertKit_Resource_Landing_Pages( 'user_refresh_resource' );
				$results       = $landing_pages->refresh();
				break;

			case 'tags':
				$tags    = new ConvertKit_Resource_Tags( 'user_refresh_resource' );
				$results = $tags->refresh();
				break;

			case 'posts':
				$posts   = new ConvertKit_Resource_Posts( 'user_refresh_resource' );
				$results = $posts->refresh();
				break;

			case 'products':
				$products = new ConvertKit_Resource_Products( 'user_refresh_resource' );
				$results  = $products->refresh();
				break;

			case 'restrict_content':
				// Fetch Tags.
				$tags         = new ConvertKit_Resource_Tags( 'user_refresh_resource' );
				$results_tags = $tags->refresh();

				// Bail if an error occured.
				if ( is_wp_error( $results_tags ) ) {
					return rest_ensure_response( $results_tags );
				}

				// Fetch Products.
				$products         = new ConvertKit_Resource_Products( 'user_refresh_resource' );
				$results_products = $products->refresh();

				// Bail if an error occured.
				if ( is_wp_error( $results_products ) ) {
					return rest_ensure_response( $results_products );
				}

				// Return resources.
				return rest_ensure_response(
					array(
						'tags'     => array_values( $results_tags ),
						'products' => array_values( $results_products ),
					)
				);

			default:
				$results = new WP_Error(
					'convertkit_admin_refresh_resources_error',
					sprintf(
						'Resource type %s is not supported in ConvertKit_Admin_Refresh_Resources class.',
						$resource
					)
				);
				break;
		}

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return rest_ensure_response( $results );
		}

		// Return resources as a zero based sequential array, so that JS retains the order of resources.
		return rest_ensure_response( array_values( $results ) );

	}

	/**
	 * Enqueue JavaScript when editing a Page, Post, Custom Post Type or Category.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   string $hook   Hook.
	 */
	public function enqueue_scripts( $hook ) {

		// Bail if we are not on an Edit or Term screen.
		if ( ! in_array( $hook, array( 'edit.php', 'post-new.php', 'term.php', 'edit-tags.php', 'post.php' ), true ) ) {
			return;
		}

		// Get settings.
		$settings = new ConvertKit_Settings();

		// Bail if no Access Token is defined.
		if ( ! $settings->has_access_and_refresh_token() ) {
			return;
		}

		// Enqueue JS to perform AJAX request to refresh resources.
		wp_enqueue_script( 'convertkit-admin-refresh-resources', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/refresh-resources.js', array(), CONVERTKIT_PLUGIN_VERSION, true );
		wp_localize_script(
			'convertkit-admin-refresh-resources',
			'convertkit_admin_refresh_resources',
			array(
				'ajaxurl' => rest_url( 'kit/v1/resources/refresh/' ),
				'debug'   => $settings->debug_enabled(),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);

	}

}
