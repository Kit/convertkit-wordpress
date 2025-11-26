<?php
/**
 * ConvertKit Custom Content Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * ConvertKit Custom Content Block for Gutenberg and Shortcode.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Content extends ConvertKit_Block {

	/**
	 * Constructor
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		// Register this as a shortcode in the ConvertKit Plugin.
		add_filter( 'convertkit_shortcodes', array( $this, 'register' ) );

	}

	/**
	 * Returns this block's programmatic name, excluding the convertkit- prefix.
	 *
	 * @since   1.9.6
	 *
	 * @return  string
	 */
	public function get_name() {

		/**
		 * This will register as:
		 * - a shortcode, with the name [convertkit_content]
		 * - a Gutenberg block, with the name convertkit/content
		 */
		return 'content';

	}

	/**
	 * Returns this block's title.
	 *
	 * @since   3.1.1
	 */
	public function get_title() {

		return __( 'Kit Custom Content', 'convertkit' );

	}

	/**
	 * Returns this block's icon.
	 *
	 * @since   3.1.1
	 */
	public function get_icon() {

		return 'resources/backend/images/block-icon-content.svg';

	}

	/**
	 * Returns this block's Title, Icon, Categories, Keywords and properties.
	 *
	 * @since   1.9.6
	 *
	 * @return  array
	 */
	public function get_overview() {

		return array(
			'title'                         => $this->get_title(),
			'description'                   => __( 'Displays Kit Custom Content for a subscriber if their tag matches the Page\'s tag.', 'convertkit' ),
			'icon'                          => $this->get_icon(),
			'category'                      => 'convertkit',
			'keywords'                      => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Content', 'convertkit' ),
			),

			// TinyMCE / QuickTags Modal Width and Height.
			'modal'                         => array(
				'width'  => 500,
				'height' => 106,
			),

			'shortcode_include_closing_tag' => true,

			// Function to call when rendering the block/shortcode on the frontend web site.
			'render_callback'               => array( $this, 'render' ),
		);

	}

	/**
	 * Returns this block's Attributes
	 *
	 * @since   1.9.6.5
	 *
	 * @return  array
	 */
	public function get_attributes() {

		return array(
			'tag' => array(
				'type' => 'string',
			),
		);

	}

	/**
	 * Returns this block's Fields
	 *
	 * @since   1.9.6
	 *
	 * @return  bool|array
	 */
	public function get_fields() {

		// Get ConvertKit Tags.
		$tags            = array();
		$convertkit_tags = new ConvertKit_Resource_Tags();
		if ( $convertkit_tags->exist() ) {
			foreach ( $convertkit_tags->get() as $tag ) {
				$tags[ absint( $tag['id'] ) ] = sanitize_text_field( $tag['name'] );
			}
		}

		return array(
			'tag' => array(
				'label'  => __( 'Tag', 'convertkit' ),
				'type'   => 'select',
				'values' => $tags,
			),
		);

	}

	/**
	 * Returns this block's UI panels / sections.
	 *
	 * @since   1.9.6
	 *
	 * @return  bool|array
	 */
	public function get_panels() {

		return array(
			'general' => array(
				'label'  => __( 'General', 'convertkit' ),
				'fields' => array(
					'tag',
				),
			),
		);

	}

	/**
	 * Returns this block's Default Values
	 *
	 * @since   1.9.6
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array(
			'tag' => '',
		);

	}

	/**
	 * Returns the block's output, based on the supplied configuration attributes.
	 *
	 * @since   1.9.6
	 *
	 * @param   array  $atts       Block / Shortcode Attributes.
	 * @param   string $content    Content.
	 * @return  string              Output
	 */
	public function render( $atts, $content = '' ) {

		// Bail if the request is not for the frontend site.
		if ( is_admin() ) {
			return '';
		}

		// Parse shortcode attributes, defining fallback defaults if required.
		$atts = shortcode_atts(
			$this->get_default_values(),
			$this->sanitize_atts( $atts ),
			$this->get_name()
		);

		// Setup Settings class.
		$settings = new ConvertKit_Settings();

		// Bail if the tag isn't specified.
		if ( ! $atts['tag'] ) {
			if ( $settings->debug_enabled() ) {
				return '<!-- Kit Custom Content: No Tag Specified -->';
			}

			return '';
		}

		// Bail if there is no subscriber ID from the cookie or request.
		$subscriber    = new ConvertKit_Subscriber();
		$subscriber_id = $subscriber->get_subscriber_id();
		if ( is_wp_error( $subscriber_id ) ) {
			if ( $settings->debug_enabled() ) {
				return '<!-- Kit Custom Content: Subscriber ID Error: ' . $subscriber_id->get_error_message() . ' -->';
			}

			return '';
		}
		if ( ! $subscriber_id ) {
			if ( $settings->debug_enabled() ) {
				return '<!-- Kit Custom Content: Subscriber ID does not exist -->';
			}

			return '';
		}

		// Bail if the API hasn't been configured.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			if ( $settings->debug_enabled() ) {
				return '<!-- Kit Custom Content: No Access Token -->';
			}

			return '';
		}

		// Initialize the API.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$settings->get_access_token(),
			$settings->get_refresh_token(),
			$settings->debug_enabled(),
			'output_content'
		);

		// Get the subscriber's tags, to see if they subscribed to this tag.
		$tags = $api->get_subscriber_tags( $subscriber_id );

		// Bail if an error occured.
		if ( is_wp_error( $tags ) ) {
			if ( $settings->debug_enabled() ) {
				return '<!-- Kit Custom Content: ' . $tags->get_error_message() . ' -->';
			}

			return '';
		}

		// Bail if the subscriber has no tags.
		if ( ! count( $tags ) ) {
			if ( $settings->debug_enabled() ) {
				return '<!-- Kit Custom Content: Subscriber has no tags -->';
			}

			return '';
		}

		// Iterate through ConvertKit Tags to find a match.
		foreach ( $tags['tags'] as $tag ) {
			// Skip if this ConvertKit Tag isn't the Tag specified in the block.
			if ( absint( $tag['id'] ) !== absint( $atts['tag'] ) ) {
				continue;
			}

			/**
			 * Filters the content in the ConvertKit Custom Content block/shortcode
			 * immediately before it is output.
			 *
			 * @since   1.9.6
			 *
			 * @param   string  $content        Content
			 * @param   array   $atts           Block / Shortcode Attributes
			 * @param   int     $subscriber_id  ConvertKit Subscriber's ID
			 * @param   array   $tags           ConvertKit Subscriber's Tags
			 * @param   array   $tag            ConvertKit Subscriber's Tag that matches $atts['tag']
			 */
			$content = apply_filters( 'convertkit_block_content_render', $content, $atts, $subscriber_id, $tags, $tag );

			/**
			 * Backward compat. filter for < 1.9.6. Filters the content in the ConvertKit Custom Content block/shortcode
			 * immediately before it is output.
			 *
			 * @since   1.0.0
			 *
			 * @param   string  $content        Content
			 * @param   array   $atts           Block / Shortcode Attributes
			 */
			$content = apply_filters( 'wp_convertkit_shortcode_custom_content', $content, $atts );

			return $content;
		}

		// If here, the subscriber does not have the block's tag.
		if ( $settings->debug_enabled() ) {
			return '<!-- Kit Custom Content: Subscriber does not have tag -->';
		}

		return '';

	}

}
