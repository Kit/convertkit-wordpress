<?php
/**
 * ConvertKit Broadcasts List Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * ConvertKit Broadcasts List Block for Gutenberg and Shortcode.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Broadcasts extends ConvertKit_Block {

	/**
	 * Constructor
	 *
	 * @since   1.9.7.4
	 */
	public function __construct() {

		// Register this as a shortcode in the ConvertKit Plugin.
		add_filter( 'convertkit_shortcodes', array( $this, 'register' ) );

		// Register this as a Gutenberg block in the ConvertKit Plugin.
		add_filter( 'convertkit_blocks', array( $this, 'register' ) );

		// Enqueue scripts and styles for this Gutenberg Block in the editor and frontend views.
		add_action( 'convertkit_gutenberg_enqueue_scripts_editor_and_frontend', array( $this, 'enqueue_scripts' ) );
		add_action( 'convertkit_gutenberg_enqueue_styles_editor_and_frontend', array( $this, 'enqueue_styles' ) );

		// Render Broadcasts block via AJAX.
		add_action( 'wp_ajax_nopriv_convertkit_broadcasts_render', array( $this, 'render_ajax' ) );
		add_action( 'wp_ajax_convertkit_broadcasts_render', array( $this, 'render_ajax' ) );

	}

	/**
	 * Enqueues scripts for this Gutenberg Block in the editor and frontend views.
	 *
	 * @since   1.9.7.6
	 */
	public function enqueue_scripts() {

		// Get ConvertKit Settings.
		$settings = new ConvertKit_Settings();

		wp_enqueue_script( 'convertkit-' . $this->get_name(), CONVERTKIT_PLUGIN_URL . 'resources/frontend/js/broadcasts.js', array(), CONVERTKIT_PLUGIN_VERSION, true );
		wp_localize_script(
			'convertkit-' . $this->get_name(),
			'convertkit_broadcasts',
			array(
				// WordPress AJAX URL endpoint.
				'ajax_url' => admin_url( 'admin-ajax.php' ),

				// AJAX action registered in __construct().
				'action'   => 'convertkit_broadcasts_render',

				// Whether debugging is enabled.
				'debug'    => $settings->debug_enabled(),
			)
		);
	}

	/**
	 * Enqueues styles for this Gutenberg Block in the editor and frontend views.
	 *
	 * @since   1.9.7.4
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'convertkit-' . $this->get_name(), CONVERTKIT_PLUGIN_URL . 'resources/frontend/css/broadcasts.css', array(), CONVERTKIT_PLUGIN_VERSION );

	}

	/**
	 * Returns this block's programmatic name, excluding the convertkit- prefix.
	 *
	 * @since   1.9.7.4
	 */
	public function get_name() {

		/**
		 * This will register as:
		 * - a shortcode, with the name [convertkit_broadcasts].
		 * - a Gutenberg block, with the name convertkit/broadcasts.
		 */
		return 'broadcasts';

	}

	/**
	 * Returns this block's Title, Icon, Categories, Keywords and properties.
	 *
	 * @since   1.9.7.4
	 */
	public function get_overview() {

		// Fetch Posts.
		$posts    = new ConvertKit_Resource_Posts( 'output_broadcasts' );
		$settings = new ConvertKit_Settings();

		return array(
			'title'                         => __( 'Kit Broadcasts', 'convertkit' ),
			'description'                   => __( 'Displays a list of your Kit broadcasts.', 'convertkit' ),
			'icon'                          => 'resources/backend/images/block-icon-broadcasts.svg',
			'category'                      => 'convertkit',
			'keywords'                      => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Broadcasts', 'convertkit' ),
				__( 'Posts', 'convertkit' ),
			),

			// Function to call when rendering as a block or a shortcode on the frontend web site.
			'render_callback'               => array( $this, 'render' ),

			// Shortcode: TinyMCE / QuickTags Modal Width and Height.
			'modal'                         => array(
				'width'  => 650,
				'height' => 455,
			),

			// Shortcode: Include a closing [/shortcode] tag when using TinyMCE or QuickTag Modals.
			'shortcode_include_closing_tag' => false,

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'                => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-broadcasts.svg' ),

			// Gutenberg: Example image showing how this block looks when choosing it in Gutenberg.
			'gutenberg_example_image'       => CONVERTKIT_PLUGIN_URL . 'resources/backend/images/block-example-broadcasts.png',

			// Help descriptions, displayed when no Access Token / resources exist and this block/shortcode is added.
			'no_access_token'               => array(
				'notice'           => __( 'Not connected to Kit.', 'convertkit' ),
				'link'             => convertkit_get_setup_wizard_plugin_link(),
				'link_text'        => __( 'Click here to connect your Kit account.', 'convertkit' ),
				'instruction_text' => __( 'Connect your Kit account at Settings > Kit, and then refresh this page to configure broadcasts to display.', 'convertkit' ),
			),
			'no_resources'                  => array(
				'notice'           => __( 'No broadcasts exist in Kit.', 'convertkit' ),
				'link'             => convertkit_get_new_broadcast_url(),
				'link_text'        => __( 'Click here to send your first broadcast.', 'convertkit' ),
				'instruction_text' => __( 'Add a broadcast to your Kit account, and then refresh this page to configure broadcasts to display.', 'convertkit' ),
			),

			// Whether an API Key exists in the Plugin, and are the required resources (broadcasts) available.
			// If no API Key is specified in the Plugin's settings, render the "No API Key" output.
			'has_access_token'              => $settings->has_access_and_refresh_token(),
			'has_resources'                 => $posts->exist(),
		);

	}

	/**
	 * Returns this block's Attributes
	 *
	 * @since   1.9.7.4
	 */
	public function get_attributes() {

		return array(
			// Block attributes.
			'display_grid'         => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'display_grid' ),
			),
			'display_order'        => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'display_order' ),
			),
			'date_format'          => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'date_format' ),
			),
			'display_image'        => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'display_image' ),
			),
			'display_description'  => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'display_description' ),
			),
			'display_read_more'    => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'display_read_more' ),
			),
			'read_more_label'      => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'read_more_label' ),
			),
			'limit'                => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'limit' ),
			),
			'page'                 => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'page' ),
			),
			'paginate'             => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'paginate_label_prev'  => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'paginate_label_prev' ),
			),
			'paginate_label_next'  => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'paginate_label_next' ),
			),

			// get_supports() style, color and typography attributes.
			'style'                => array(
				'type' => 'object',
			),
			'backgroundColor'      => array(
				'type' => 'string',
			),
			'textColor'            => array(
				'type' => 'string',
			),
			'fontSize'             => array(
				'type' => 'string',
			),

			// Always required for Gutenberg.
			'is_gutenberg_example' => array(
				'type'    => 'boolean',
				'default' => false,
			),
		);

	}

	/**
	 * Returns this block's supported built-in Attributes.
	 *
	 * @since   1.9.7.4
	 *
	 * @return  array   Supports
	 */
	public function get_supports() {

		return array(
			'className'  => true,
			'color'      => array(
				'link'       => true,
				'background' => true,
				'text'       => true,
			),
			'typography' => array(
				'fontSize'   => true,
				'lineHeight' => true,
			),
			'spacing'    => array(
				'margin'  => true,
				'padding' => true,
			),
		);

	}

	/**
	 * Returns this block's Fields
	 *
	 * @since   1.9.7.4
	 *
	 * @return  bool|array
	 */
	public function get_fields() {

		// Bail if the request is not for the WordPress Administration or frontend editor.
		if ( ! WP_ConvertKit()->is_admin_or_frontend_editor() ) {
			return false;
		}

		return array(
			'display_grid'        => array(
				'label'       => __( 'Display as grid', 'convertkit' ),
				'type'        => 'toggle',
				'description' => __( 'If enabled, displays broadcasts in a grid, instead of a list.', 'convertkit' ),
			),
			'display_order'       => array(
				'label'  => __( 'Display order', 'convertkit' ),
				'type'   => 'select',
				'values' => array(
					'date-broadcast' => __( 'Date, Broadcast', 'convertkit' ),
					'broadcast-date' => __( 'Broadcast, Date', 'convertkit' ),
				),
			),
			'date_format'         => array(
				'label'  => __( 'Date format', 'convertkit' ),
				'type'   => 'select',
				'values' => array(
					'F j, Y' => date_i18n( 'F j, Y', strtotime( 'now' ) ),
					'Y-m-d'  => date_i18n( 'Y-m-d', strtotime( 'now' ) ),
					'm/d/Y'  => date_i18n( 'm/d/Y', strtotime( 'now' ) ),
					'd/m/Y'  => date_i18n( 'd/m/Y', strtotime( 'now' ) ),
				),
			),
			'display_image'       => array(
				'label' => __( 'Display images', 'convertkit' ),
				'type'  => 'toggle',
			),
			'display_description' => array(
				'label' => __( 'Display descriptions', 'convertkit' ),
				'type'  => 'toggle',
			),
			'display_read_more'   => array(
				'label' => __( 'Display read more links', 'convertkit' ),
				'type'  => 'toggle',
			),
			'read_more_label'     => array(
				'label'       => __( 'Read more label', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The label to display for the "read more" link below each broadcast.', 'convertkit' ),
				'display_if'  => array(
					'key'   => 'display_read_more',
					'value' => 1,
				),
			),
			'limit'               => array(
				'label' => __( 'Number of posts', 'convertkit' ),
				'type'  => 'number',
				'min'   => 1,
				'max'   => 999,
				'step'  => 1,
			),
			'paginate'            => array(
				'label'       => __( 'Display pagination', 'convertkit' ),
				'type'        => 'toggle',
				'description' => __( 'If the number of broadcasts exceeds the "Number of posts" settings above, previous/next pagination links will be displayed.', 'convertkit' ),
			),
			'paginate_label_prev' => array(
				'label'       => __( 'Newer posts label', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The label to display for the link to newer broadcasts.', 'convertkit' ),
				'display_if'  => array(
					'key'   => 'paginate',
					'value' => 1,
				),
			),
			'paginate_label_next' => array(
				'label'       => __( 'Older posts label', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The label to display for the link to older broadcasts.', 'convertkit' ),
				'display_if'  => array(
					'key'   => 'paginate',
					'value' => 1,
				),
			),

			// These fields will only display on the shortcode, and are deliberately not registered in get_attributes(),
			// because Gutenberg will register its own color pickers for link, background and text.
			'link_color'          => array(
				'label' => __( 'Link color', 'convertkit' ),
				'type'  => 'color',
			),
			'background_color'    => array(
				'label' => __( 'Background color', 'convertkit' ),
				'type'  => 'color',
			),
			'text_color'          => array(
				'label' => __( 'Text color', 'convertkit' ),
				'type'  => 'color',
			),
		);

	}

	/**
	 * Returns this block's UI panels / sections.
	 *
	 * @since   1.9.7.4
	 *
	 * @return  bool|array
	 */
	public function get_panels() {

		// Bail if the request is not for the WordPress Administration or frontend editor.
		if ( ! WP_ConvertKit()->is_admin_or_frontend_editor() ) {
			return false;
		}

		return array(
			'general'    => array(
				'label'  => __( 'General', 'convertkit' ),
				'fields' => array(
					'display_grid',
					'display_order',
					'date_format',
					'display_image',
					'display_description',
					'display_read_more',
					'read_more_label',
				),
			),
			'pagination' => array(
				'label'  => __( 'Pagination', 'convertkit' ),
				'fields' => array(
					'limit',
					'paginate',
					'paginate_label_prev',
					'paginate_label_next',
				),
			),
			'styles'     => array(
				'label'  => __( 'Styles', 'convertkit' ),
				'fields' => array(
					'link_color',
					'background_color',
					'text_color',
				),
			),
		);

	}

	/**
	 * Returns this block's Default Values
	 *
	 * @since   1.9.7.4
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array(
			'display_grid'        => false,
			'display_order'       => 'date-broadcast',
			'date_format'         => 'F j, Y',
			'display_image'       => false,
			'display_description' => false,
			'display_read_more'   => false,
			'read_more_label'     => __( 'Read more', 'convertkit' ),
			'limit'               => 10,
			'paginate'            => false,
			'paginate_label_prev' => __( 'Previous', 'convertkit' ),
			'paginate_label_next' => __( 'Next', 'convertkit' ),
			'link_color'          => '',
			'background_color'    => '',
			'text_color'          => '',

			// Built-in Gutenberg block attributes.
			'style'               => '',
			'backgroundColor'     => '',
			'textColor'           => '',

			// Not output as a block option, but stores the page requested by the user if using pagination without JS.
			'page'                => $this->get_page(),
		);

	}

	/**
	 * Returns the block's output, based on the supplied configuration attributes.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   array $atts                 Block / Shortcode / Page Builder Module Attributes.
	 * @return  string
	 */
	public function render( $atts ) {

		// Parse attributes, defining fallback defaults if required
		// and moving some attributes (such as Gutenberg's styles), if defined.
		$atts = $this->sanitize_and_declare_atts( $atts );

		// Setup Settings class.
		$settings = new ConvertKit_Settings();

		// Fetch Posts.
		$posts = new ConvertKit_Resource_Posts( 'output_broadcasts' );

		// If this is an admin request, refresh the Posts resource now from the API,
		// as it's an inexpensive query of ~ 0.5 seconds when we're editing a Page
		// containing this block.
		if ( function_exists( 'is_admin' ) && is_admin() ) {
			$posts->refresh();
		}

		// If no Posts exist, bail.
		if ( ! $posts->exist() ) {
			if ( $settings->debug_enabled() ) {
				return '<!-- ' . __( 'No Broadcasts exist in Kit.', 'convertkit' ) . ' -->';
			}

			return '';
		}

		// Build HTML.
		if ( $this->is_block_editor_request() ) {
			// For the block editor, don't include compiled CSS classes and styles,
			// as the block editor will add these to the parent container.
			// Otherwise the block will render incorrectly with double padding, double margins etc.
			$html = $this->build_html(
				$posts,
				$atts,
				true,
				array(
					'convertkit-' . $this->get_name(),
				)
			);
		} else {
			$html = $this->build_html(
				$posts,
				$atts,
				true,
				$this->get_css_classes(),
				$this->get_css_styles( $atts )
			);
		}

		/**
		 * Filter the block's content immediately before it is output.
		 *
		 * @since   1.9.7.4
		 *
		 * @param   string  $html       ConvertKit Broadcasts HTML.
		 * @param   array   $atts       Block Attributes.
		 */
		$html = apply_filters( 'convertkit_block_broadcasts_render', $html, $atts );

		return $html;

	}

	/**
	 * Returns the block's output, based on the supplied configuration attributes,
	 * when requested via AJAX.
	 *
	 * @since   1.9.7.6
	 */
	public function render_ajax() {

		// Check nonce.
		check_ajax_referer( 'convertkit-broadcasts', 'nonce' );

		// Build attributes array.
		$atts = array(
			'date_format'         => ( isset( $_REQUEST['date_format'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['date_format'] ) ) : $this->get_default_value( 'date_format' ) ),
			'display_image'       => ( isset( $_REQUEST['display_image'] ) ? absint( $_REQUEST['display_image'] ) : $this->get_default_value( 'display_image' ) ),
			'display_description' => ( isset( $_REQUEST['display_description'] ) ? absint( $_REQUEST['display_description'] ) : $this->get_default_value( 'display_description' ) ),
			'display_read_more'   => ( isset( $_REQUEST['display_read_more'] ) ? absint( $_REQUEST['display_read_more'] ) : $this->get_default_value( 'display_read_more' ) ),
			'read_more_label'     => ( isset( $_REQUEST['read_more_label'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['read_more_label'] ) ) : $this->get_default_value( 'read_more_label' ) ),
			'limit'               => ( isset( $_REQUEST['limit'] ) ? absint( $_REQUEST['limit'] ) : $this->get_default_value( 'limit' ) ),
			'page'                => ( isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : $this->get_default_value( 'page' ) ),
			'paginate'            => ( isset( $_REQUEST['paginate'] ) ? absint( $_REQUEST['paginate'] ) : $this->get_default_value( 'paginate' ) ),
			'paginate_label_next' => ( isset( $_REQUEST['paginate_label_next'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['paginate_label_next'] ) ) : $this->get_default_value( 'paginate_label_next' ) ),
			'paginate_label_prev' => ( isset( $_REQUEST['paginate_label_prev'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['paginate_label_prev'] ) ) : $this->get_default_value( 'paginate_label_prev' ) ),
			'link_color'          => ( isset( $_REQUEST['link_color'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['link_color'] ) ) : $this->get_default_value( 'link_color' ) ),
		);

		// Parse attributes, defining fallback defaults if required
		// and moving some attributes (such as Gutenberg's styles), if defined.
		$atts = $this->sanitize_and_declare_atts( $atts );

		// Fetch Posts.
		$posts = new ConvertKit_Resource_Posts( 'output_broadcasts' );

		// Build HTML.
		$html = $this->build_html( $posts, $atts, false );

		/**
		 * Filter the block's inner content immediately before it is output by AJAX,
		 * which occurs when pagination was clicked.
		 *
		 * @since   1.9.7.6
		 *
		 * @param   string  $html   ConvertKit Broadcasts HTML.
		 * @param   array   $atts   Block Attributes.
		 */
		$html = apply_filters( 'convertkit_block_broadcasts_render_ajax', $html, $atts );

		// Send HTML as response.
		wp_send_json_success( $html );

	}

	/**
	 * Returns a HTML list of ConvertKit broadcasts, honoring the supplied
	 * attribute's current requested page and limit.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   ConvertKit_Resource_Posts $posts              ConvertKit Posts Resource class.
	 * @param   array                     $atts               Block attributes.
	 * @param   bool                      $include_container  Include container div in HTML.
	 * @param   array                     $css_classes        CSS classes to apply to block.
	 * @param   array                     $css_styles         CSS inline styles to apply to block.
	 * @return  string
	 */
	private function build_html( $posts, $atts, $include_container = true, $css_classes = array(), $css_styles = array() ) {

		// Get paginated subset of Posts.
		$broadcasts = $posts->get_paginated_subset( $atts['page'], $atts['limit'] );

		// Define a nonce to ensure requests made for paginated broadcasts are protected against e.g. CSRF attacks.
		$nonce = wp_create_nonce( 'convertkit-broadcasts' );

		// Define HTML string.
		$html = '';

		// Include container, if required.
		if ( $include_container ) {
			$html .= '<div class="' . implode( ' ', map_deep( $css_classes, 'sanitize_html_class' ) ) . '" style="' . implode( ';', map_deep( $css_styles, 'esc_attr' ) ) . '" ' . $this->get_atts_as_html_data_attributes( $atts ) . '>';
		}

		// Start list.
		$html .= '<ul class="convertkit-broadcasts-list">';

		// Iterate through broadcasts, building HTML list items.
		foreach ( $broadcasts['items'] as $count => $broadcast ) {
			// Add broadcast as list item.
			$html .= $this->build_html_list_item( $broadcast, $atts );
		}

		// End list.
		$html .= '</ul>';

		// If pagination is disabled, return the output now.
		if ( ! $atts['paginate'] ) {
			// Close container div, if required.
			if ( $include_container ) {
				$html .= '</div>';
			}

			return $html;
		}

		// If no next or previous page exists, just return the output.
		if ( ! $broadcasts['has_next_page'] && ! $broadcasts['has_prev_page'] ) {
			// Close container div, if required.
			if ( $include_container ) {
				$html .= '</div>';
			}

			return $html;
		}

		// Append pagination.
		$html .= '<ul class="convertkit-broadcasts-pagination">
			<li class="convertkit-broadcasts-pagination-prev">' . ( $broadcasts['has_prev_page'] ? $this->get_pagination_link_prev_html( $atts, $nonce ) : '' ) . '</li>
			<li class="convertkit-broadcasts-pagination-next">' . ( $broadcasts['has_next_page'] ? $this->get_pagination_link_next_html( $atts, $nonce ) : '' ) . '</li>
		</ul>';

		// Close container div, if required.
		if ( $include_container ) {
			$html .= '</div>';
		}

		/**
		 * Filter the block's content immediately before it is output.
		 *
		 * @since   2.2.3
		 *
		 * @param   string  $html       ConvertKit Broadcasts HTML.
		 * @param   array   $atts       Block Attributes.
		 */
		$html = apply_filters( 'convertkit_block_broadcasts_render', $html, $atts );

		// Return.
		return $html;

	}

	/**
	 * Defines the HTML for an individual broadcast item in the Broadcasts block.
	 *
	 * @since   2.2.3
	 *
	 * @param   array $broadcast  Broadcast.
	 * @param   array $atts       Block attributes.
	 * @return  string              HTML
	 */
	private function build_html_list_item( $broadcast, $atts ) {

		// Convert UTC date to timestamp.
		$date_timestamp = strtotime( $broadcast['published_at'] );

		// Build broadcast URL.
		$url = add_query_arg(
			array(
				'utm_source'  => 'wordpress',
				'utm_term'    => get_locale(),
				'utm_content' => 'convertkit',
			),
			$broadcast['url']
		);

		// Build HTML.
		$html = '<li class="convertkit-broadcast">';

		// Display date.
		$html .= '<time datetime="' . esc_attr( date_i18n( 'Y-m-d', $date_timestamp ) ) . '">' . esc_html( date_i18n( $atts['date_format'], $date_timestamp ) ) . '</time>';

		// Display linked title.
		$html .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="nofollow noopener"' . $this->get_link_style_tag( $atts ) . ' class="convertkit-broadcast-title">' . esc_html( $broadcast['title'] ) . '</a>';

		// Display image.
		// We check for thumbnail_url, as these were added to the API in https://github.com/ConvertKit/convertkit/pull/23938,
		// and might not immediately be available until the resources are refreshed.
		if ( $atts['display_image'] && array_key_exists( 'thumbnail_url', $broadcast ) && ! is_null( $broadcast['thumbnail_url'] ) ) {
			$html .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="nofollow noopener" class="convertkit-broadcast-image">
				<img src="' . esc_url( $broadcast['thumbnail_url'] ) . '" alt="' . esc_attr( $broadcast['thumbnail_alt'] ) . '" />
			</a>';
		}

		// Display description / read more.
		if ( $atts['display_description'] || $atts['display_read_more'] ) {
			$html .= '<span class="convertkit-broadcast-text">';

			// Display description.
			// We check for description, as these were added to the API in https://github.com/ConvertKit/convertkit/pull/23938,
			// and might not immediately be available until the resources are refreshed.
			if ( $atts['display_description'] && array_key_exists( 'description', $broadcast ) && ! is_null( $broadcast['description'] ) ) {
				$html .= '<span class="convertkit-broadcast-description">' . esc_html( $broadcast['description'] ) . '</span>';
			}

			// Display read more link.
			if ( $atts['display_read_more'] ) {
				$html .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="nofollow noopener" class="convertkit-broadcast-read-more">' . esc_html( $atts['read_more_label'] ) . '</a>';
			}

			$html .= '</span>';
		}

		// Close list item.
		$html .= '</li>';

		/**
		 * Defines the HTML for an individual broadcast item in the Broadcasts block.
		 *
		 * @since   2.2.3
		 *
		 * @param   string  $html       HTML.
		 * @param   array   $broadcast  Broadcast.
		 * @param   array   $atts       Block attributes.
		 * @return  string              HTML
		 */
		$html = apply_filters( 'convertkit_block_broadcasts_build_html_list_item', $html, $broadcast, $atts );

		return $html;

	}

	/**
	 * Returns the HTML link to paginate to the previous page, to view
	 * newer broadcasts.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   array  $atts   Block attributes.
	 * @param   string $nonce  Nonce.
	 * @return  string          HTML Link
	 */
	private function get_pagination_link_prev_html( $atts, $nonce ) {

		return '<a href="' . esc_url( $this->get_pagination_link( $atts['page'] - 1, $nonce ) ) . '" title="' . esc_attr( $atts['paginate_label_prev'] ) . '" data-page="' . esc_attr( (string) ( $atts['page'] - 1 ) ) . '" data-nonce="' . esc_attr( $nonce ) . '"' . $this->get_link_style_tag( $atts ) . '>
			' . esc_html( $atts['paginate_label_prev'] ) . '
		</a>';

	}

	/**
	 * Returns the HTML link to paginate to the next page, to view
	 * older broadcasts.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   array  $atts   Block attributes.
	 * @param   string $nonce  Nonce.
	 * @return  string          HTML Link
	 */
	private function get_pagination_link_next_html( $atts, $nonce ) {

		return '<a href="' . esc_url( $this->get_pagination_link( $atts['page'] + 1, $nonce ) ) . '" title="' . esc_attr( $atts['paginate_label_next'] ) . '" data-page="' . esc_attr( (string) ( $atts['page'] + 1 ) ) . '" data-nonce="' . esc_attr( $nonce ) . '"' . $this->get_link_style_tag( $atts ) . '>
			' . esc_html( $atts['paginate_label_next'] ) . '
		</a>';

	}

	/**
	 * Returns the link to paginate to the specified page.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   int    $page   Page Number.
	 * @param   string $nonce  Nonce.
	 * @return  string          URL
	 */
	private function get_pagination_link( $page, $nonce ) {

		global $post, $wp;

		// Determine the base Permalink, depending on whether we're viewing an individual Page/Post or not.
		if ( ! is_null( $post ) ) {
			$permalink = get_permalink( $post->ID );
		} else {
			// Fallback to WordPress' request object to identify the current slug, as we are not viewing
			// an individual Page or Post e.g. we're on the Home Page and this block is in a footer widget.
			$permalink = home_url( $wp->request );
		}

		return add_query_arg(
			array(
				'convertkit-broadcasts-page'  => absint( $page ),
				'convertkit-broadcasts-nonce' => $nonce,
			),
			$permalink
		);

	}

	/**
	 * Returns the current pagination page requested for broadcasts.
	 *
	 * @since   1.9.7.6
	 *
	 * @return  int     Page
	 */
	private function get_page() {

		// Assume we're requesting the first page.
		$page = 1;

		// Return first page number if no nonce exists.
		if ( ! array_key_exists( 'convertkit-broadcasts-nonce', $_REQUEST ) ) {
			return $page;
		}

		// Return first page number if nonce verification fails, as this means we can't reliably trust $_REQUEST['convertkit-broadcasts-page'].
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['convertkit-broadcasts-nonce'] ), 'convertkit-broadcasts' ) ) {
			return $page;
		}

		// Return first page number if no specific page was requested.
		if ( ! isset( $_REQUEST['convertkit-broadcasts-page'] ) ) {
			return $page;
		}

		// Return requested page number.
		return absint( $_REQUEST['convertkit-broadcasts-page'] );

	}

	/**
	 * If a link_color attribute exists in the given array of attributes, we're rendering a shortcode, and therefore
	 * need to include inline styling for links.
	 *
	 * The Gutenberg block doesn't need this, because WordPress generates its own inline styles when a link color is selected.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   array $atts   Block attributes.
	 * @return  string          style attribute (blank string if no styles need to be applied)
	 */
	private function get_link_style_tag( $atts ) {

		if ( ! isset( $atts['link_color'] ) ) {
			return '';
		}

		if ( empty( $atts['link_color'] ) ) {
			return '';
		}

		return ' style="color:' . esc_attr( $atts['link_color'] ) . '"';

	}

}
