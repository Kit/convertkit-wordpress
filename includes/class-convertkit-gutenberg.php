<?php
/**
 * ConvertKit Gutenberg class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers blocks defined in the `convertkit_blocks` filter in Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Gutenberg {

	/**
	 * Constructor
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		// Register Gutenberg Block Categories and Blocks.
		if ( get_bloginfo( 'version' ) >= 5.8 ) {
			// Filter changed in 5.8.
			add_filter( 'block_categories_all', array( $this, 'add_block_categories' ), 10, 2 );
		} else {
			add_filter( 'block_categories', array( $this, 'add_block_categories' ), 10, 2 );
		}

		// Register Gutenberg Blocks.
		add_action( 'init', array( $this, 'add_blocks' ) );

		// Register Gutenberg Plugin Sidebars.
		add_action( 'init', array( $this, 'add_plugin_sidebars' ) );

		// Register REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Delete duplicate Post Meta rows before WordPress updates them.
		add_filter( 'update_post_metadata', array( $this, 'maybe_delete_duplicate_post_meta' ), 10, 5 );

	}

	/**
	 * Deletes duplicate Post Meta rows for the Plugin's Post Meta key, before WordPress updates them,
	 * which have been created by third party Plugins, migrations, or other code.
	 *
	 * @since   3.4.5
	 *
	 * @param   null   $check       Whether to short circuit the update.
	 * @param   int    $post_id     Post ID.
	 * @param   string $meta_key    Meta key.
	 * @param   mixed  $meta_value  Meta value.
	 * @param   mixed  $prev_value  Previous meta value to update.
	 * @return  null|bool             Whether to short circuit the update.
	 */
	public function maybe_delete_duplicate_post_meta( $check, $post_id, $meta_key, $meta_value, $prev_value ) {

		// Bail if this isn't the Plugin's Post Meta key.
		if ( $meta_key !== ConvertKit_Post::POST_META_KEY ) {
			return $check;
		}

		// Bail if a previous value is specified, as WordPress will only update matching rows.
		if ( ! empty( $prev_value ) ) {
			return $check;
		}

		// Bail if no duplicate rows exist.
		$convertkit_post = new ConvertKit_Post( $post_id );
		$rows            = $convertkit_post->get_meta_rows();
		if ( count( $rows ) < 2 ) {
			return $check;
		}

		// Delete all but the most recent row.
		$row = array_pop( $rows );
		foreach ( $rows as $duplicate_row ) {
			delete_metadata_by_mid( 'post', $duplicate_row->meta_id );
		}

		// Restore the Post in the Member Content cache, as deleting the above rows removed it.
		$value = maybe_unserialize( $row->meta_value );
		WP_ConvertKit()->get_class( 'restrict_content_cache' )->on_meta_change( 0, $post_id, $meta_key, $value );

		// If the remaining row already holds the value WordPress is about to store, tell WordPress
		// the update succeeded, as there's nothing to change. Without this, WordPress' own check for
		// a single unchanged row returns false, and the block editor still cannot save the Post.
		if ( $value === $meta_value ) {
			return true;
		}

		return $check;

	}

	/**
	 * Register REST API routes.
	 *
	 * @since   3.1.0
	 */
	public function register_routes() {

		// Register route to refresh resources andreturn all blocks registered by the Plugin,
		// when the user clicks the refresh button in the Gutenberg editor.
		register_rest_route(
			'kit/v1',
			'/blocks',
			array(
				'methods'             => WP_REST_Server::READABLE,

				// Return blocks.
				'callback'            => function () {
					// Refresh Forms.
					$forms = new ConvertKit_Resource_Forms( 'block_edit' );
					$result = $forms->refresh();
					if ( is_wp_error( $result ) ) {
						// Return blocks without refreshing other resources.
						return rest_ensure_response( convertkit_get_blocks() );
					}

					// Refresh Posts.
					$posts = new ConvertKit_Resource_Posts( 'block_edit' );
					$result = $posts->refresh();
					if ( is_wp_error( $result ) ) {
						// Return blocks without refreshing other resources.
						return rest_ensure_response( convertkit_get_blocks() );
					}

					// Refresh Products.
					$products = new ConvertKit_Resource_Products( 'block_edit' );
					$result = $products->refresh();
					if ( is_wp_error( $result ) ) {
						// Return blocks without refreshing other resources.
						return rest_ensure_response( convertkit_get_blocks() );
					}

					// Return blocks, which will now include the refreshed resources.
					return rest_ensure_response( convertkit_get_blocks() );
				},

				// Only refresh resources for users who can edit posts.
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

	}

	/**
	 * Registers the ConvertKit Block Category.
	 *
	 * @since   1.9.6
	 *
	 * @param   array   $categories     Block Categories.
	 * @param   WP_Post $post           WordPress Post.
	 * @return  array                   Block Categories
	 */
	public function add_block_categories( $categories, $post ) {

		// Define block categories.
		$categories = array_merge(
			$categories,
			array(
				array(
					'slug'  => 'convertkit',
					'title' => 'Kit',
				),
			)
		);

		/**
		 * Adds block categories to the default Gutenberg Block Categories
		 *
		 * @since   1.9.6
		 *
		 * @param   array   $categories     Block Categories
		 * @param   WP_Post  $post           WordPress Post
		 */
		$categories = apply_filters( 'convertkit_admin_gutenberg_add_block_categories', $categories, $post );

		// Return filtered results.
		return $categories;

	}

	/**
	 * Registers Blocks, so that they can be used in the Gutenberg Editor
	 *
	 * @since   1.9.6
	 */
	public function add_blocks() {

		// Bail if Gutenberg isn't available.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Get blocks.
		$blocks = convertkit_get_blocks();

		// Bail if no blocks are available.
		if ( ! count( $blocks ) ) {
			return;
		}

		// Determine the block API version to use for registering blocks.
		$block_api_version = $this->get_block_api_version();

		// Get registered blocks.
		$registered_blocks = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );

		// Iterate through blocks, registering them.
		foreach ( $blocks as $block => $properties ) {

			// Skip if this block has already been registered.
			if ( in_array( 'convertkit/' . $block, $registered_blocks, true ) ) {
				continue;
			}

			// Register block.
			register_block_type(
				CONVERTKIT_PLUGIN_PATH . '/includes/blocks/v' . (string) $block_api_version . '/' . $block,
				array(
					'attributes'      => $properties['attributes'],
					'editor_script'   => 'convertkit-gutenberg',
					'render_callback' => array(
						$properties['render_callback'][0],
						$properties['render_callback'][1],
					),
				)
			);

		}

		// Enqueue block scripts and styles in the editor view.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_styles' ) );

		// Enqueue block scripts and styles in the editor and frontend views.
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_scripts_editor_and_frontend' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_styles_editor_and_frontend' ) );

	}

	/**
	 * Registers post meta for any registered plugin sidebars using register_post_meta(),
	 * so data is saved when using the Gutenberg editor.
	 *
	 * @since   3.3.0
	 */
	public function add_plugin_sidebars() {

		// Get plugin sidebars.
		$plugin_sidebars = convertkit_get_plugin_sidebars();

		// Bail if no plugin sidebars are available.
		if ( ! count( $plugin_sidebars ) ) {
			return;
		}

		foreach ( $plugin_sidebars as $plugin_sidebar ) {
			register_post_meta(
				'',
				$plugin_sidebar['meta_key'],
				array(
					'show_in_rest'      => array(
						'schema' => array(
							'type'       => 'object',
							'properties' => $plugin_sidebar['attributes'],
						),
					),
					'single'            => true,
					'type'              => 'object',
					'default'           => $plugin_sidebar['default_values'],
					'sanitize_callback' => function ( $meta ) use ( $plugin_sidebar ) {

						// If the value is not an array, return the default values.
						if ( ! is_array( $meta ) ) {
							return $plugin_sidebar['default_values'];
						}

						// Iterate through the attributes and sanitize the meta.
						foreach ( $plugin_sidebar['attributes'] as $key => $attribute ) {
							$meta[ $key ] = sanitize_text_field( $meta[ $key ] ?? $attribute['default'] );
						}

						// If a Form or Landing Page was specified, request a review.
						// This can safely be called multiple times, as the review request
						// class will ensure once a review request is dismissed by the user,
						// it is never displayed again.
						if ( $meta['form'] || $meta['landing_page'] ) {
							WP_ConvertKit()->get_class( 'review_request' )->request_review();
						}

						// Return the sanitized meta.
						return $meta;

					},
					'auth_callback'     => function () use ( $plugin_sidebar ) {

						return current_user_can( $plugin_sidebar['minimum_capability'] );

					},
				)
			);
		}

	}

	/**
	 * Determines the block API version to use for registering blocks.
	 *
	 * @since   3.2.0
	 *
	 * @return  int    Block API version.
	 */
	public function get_block_api_version() {

		// Determine the block API version to use for registering blocks.
		// WordPress supports Version 3 from WordPress 6.3:
		// https://developer.wordpress.org/block-editor/reference-guides/block-api/block-api-versions/.
		$block_api_version = ( version_compare( get_bloginfo( 'version' ), '6.3', '>=' ) ? 3 : 2 );

		/**
		 * Determine the block API version to use for registering blocks.
		 *
		 * @since   3.1.4
		 *
		 * @param   int  $block_api_version    Block API version.
		 * @return  int                        Block API version.
		 */
		$block_api_version = apply_filters( 'convertkit_gutenberg_block_api_version', $block_api_version );

		return absint( $block_api_version );

	}

	/**
	 * Enqueues scripts for Gutenberg blocks in the editor view.
	 *
	 * @since   1.9.6
	 */
	public function enqueue_scripts() {

		// Bail if request isn't for the Admin or a Frontend Editor.
		if ( ! WP_ConvertKit()->is_admin_or_frontend_editor() ) {
			return;
		}

		// Get settings.
		$settings = new ConvertKit_Settings();

		// Get blocks and block toolbar buttons.
		$blocks              = convertkit_get_blocks();
		$block_formatters    = convertkit_get_block_formatters();
		$pre_publish_actions = convertkit_get_pre_publish_actions();
		$plugin_sidebars     = convertkit_get_plugin_sidebars();

		// Enqueue Gutenberg Javascript, and set the blocks data.
		wp_enqueue_script( 'convertkit-gutenberg', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/gutenberg.js', array( 'jquery' ), CONVERTKIT_PLUGIN_VERSION, true );
		wp_localize_script( 'convertkit-gutenberg', 'convertkit_blocks', $blocks );

		// If pre-publish actions are available, set the data.
		if ( count( $pre_publish_actions ) ) {
			wp_localize_script( 'convertkit-gutenberg', 'convertkit_pre_publish_actions', $pre_publish_actions );
		}

		// If plugin sidebars are available, set the data.
		if ( count( $plugin_sidebars ) ) {
			wp_localize_script( 'convertkit-gutenberg', 'convertkit_plugin_sidebars', $plugin_sidebars );
		}

		// Set the Gutenberg data.
		wp_localize_script(
			'convertkit-gutenberg',
			'convertkit_gutenberg',
			array(
				'ajaxurl'                 => rest_url( 'kit/v1/blocks' ),
				'block_api_version'       => $this->get_block_api_version(),
				'get_blocks_nonce'        => wp_create_nonce( 'wp_rest' ),
				'refresh_resources_url'   => rest_url( 'kit/v1/resources/refresh/' ),
				'refresh_resources_nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);

		// Enqueue Gutenberg Block Toolbar Javascript, and set the block toolbar buttons data.
		wp_enqueue_script( 'convertkit-gutenberg-block-formatters', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/gutenberg-block-formatters.js', array( 'jquery' ), CONVERTKIT_PLUGIN_VERSION, true );
		wp_localize_script( 'convertkit-gutenberg-block-formatters', 'convertkit_block_formatters', $block_formatters );

		/**
		 * Enqueue any additional scripts for Gutenberg blocks that have been registered.
		 *
		 * @since   1.9.6.5
		 *
		 * @param   array   $blocks             ConvertKit Blocks.
		 * @param   array   $block_formatters   ConvertKit Block Formatters.
		 */
		do_action( 'convertkit_gutenberg_enqueue_scripts', $blocks, $block_formatters );

	}

	/**
	 * Enqueues styles for Gutenberg blocks in the editor view.
	 *
	 * Use wp_enqueue_style() hooked to the enqueue_block_assets hook for frontend styles:
	 * https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/applying-styles-with-stylesheets/
	 *
	 * @since   1.9.6.9
	 */
	public function enqueue_styles() {

		// Bail if request isn't for the Admin.
		if ( ! is_admin() ) {
			return;
		}

		/**
		 * Enqueue styles for Gutenberg blocks that have been registered.
		 *
		 * @since   1.9.6.9
		 */
		do_action( 'convertkit_gutenberg_enqueue_styles' );

	}

	/**
	 * Enqueues scripts for Gutenberg blocks in both the editor and frontend view,
	 * if the Plugin's Disable JavaScript option is not enabled.
	 *
	 * @since   1.9.7.6
	 */
	public function enqueue_scripts_editor_and_frontend() {

		// Don't load scripts if the Disable JS option is on.
		$settings = new ConvertKit_Settings();
		if ( $settings->scripts_disabled() ) {
			return;
		}

		/**
		 * Enqueues scripts for Gutenberg blocks in both the editor and frontend view,
		 * if the Plugin's Disable JavaScript option is not enabled.
		 *
		 * @since   1.9.7.6
		 */
		do_action( 'convertkit_gutenberg_enqueue_scripts_editor_and_frontend' );

	}

	/**
	 * Enqueues styles for Gutenberg blocks in both the editor and frontend view,
	 * if the Plugin's Disable CSS option is not enabled.
	 *
	 * @since   1.9.7.6
	 */
	public function enqueue_styles_editor_and_frontend() {

		// Don't load styles if the Disable CSS option is on.
		$settings = new ConvertKit_Settings();
		if ( $settings->css_disabled() ) {
			return;
		}

		/**
		 * Enqueues styles for Gutenberg blocks in both the editor and frontend view,
		 * if the Plugin's Disable CSS option is not enabled.
		 *
		 * @since   1.9.7.6
		 */
		do_action( 'convertkit_gutenberg_enqueue_styles_editor_and_frontend' );

	}

}
