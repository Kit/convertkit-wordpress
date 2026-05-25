<?php
/**
 * ConvertKit Content Post Helper class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Mechanism-agnostic helper to find, insert, update and delete a Kit element
 * (Broadcast, Form, Form Trigger, Product) within a WordPress Post's content.
 *
 * This is the entry point used by the Content MCP abilities. It decides how the
 * given Post stores its content — Gutenberg blocks, Classic editor / shortcode
 * markup — and delegates to the appropriate mechanism-specific helper:
 *
 * - ConvertKit_Block_Post_Helper     for block-based content.
 *
 * Callers pass an element name (e.g. `form`); this class applies the correct
 * prefix for the chosen mechanism (`convertkit/form` for blocks,
 * `convertkit_form` for shortcodes).
 *
 * Page builders (Elementor, etc.) store their content outside post_content and
 * are not supported; a WP_Error is returned for posts built with one.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Content_Post_Helper {

	/**
	 * Finds all occurrences of the given Kit feature in a Post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id        Post ID.
	 * @param   string $feature_name   Kit feature name (e.g. `form`), without prefix.
	 * @return  WP_Error|bool|array
	 */
	public static function find( $post_id, $feature_name ) {

		// Determine how this post stores its content.
		$mechanism = self::detect_mechanism( $post_id );
		if ( is_wp_error( $mechanism ) ) {
			return $mechanism;
		}

		// Find the element in the post, depending on the mechanism.
		// A switch is used as shortcodes and other mechanisms will be supported in the future.
		switch ( $mechanism ) {
			case 'block':
				return ConvertKit_Block_Post_Helper::find(
					$post_id,
					'convertkit/' . $feature_name
				);
		}

		return self::unsupported_mechanism_error( $mechanism );

	}

	/**
	 * Inserts a new occurrence of the given Kit feature into a Post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id        Post ID.
	 * @param   string $feature_name   Kit feature name (e.g. `form`), without prefix.
	 * @param   array  $attrs          Feature attributes.
	 * @param   string $position       One of 'prepend', 'append', 'index'.
	 * @param   int    $index          Zero-based top-level index; only used when $position is 'index'.
	 * @return  WP_Error|array
	 */
	public static function insert( $post_id, $feature_name, $attrs, $position = 'append', $index = 0 ) {

		// Determine how this post stores its content.
		$mechanism = self::detect_mechanism( $post_id );
		if ( is_wp_error( $mechanism ) ) {
			return $mechanism;
		}

		// Insert the element into the post, depending on the mechanism.
		// A switch is used as shortcodes and other mechanisms will be supported in the future.
		switch ( $mechanism ) {
			case 'block':
				return ConvertKit_Block_Post_Helper::insert(
					$post_id,
					'convertkit/' . $feature_name,
					$attrs,
					$position,
					$index
				);
		}

		return self::unsupported_mechanism_error( $mechanism );

	}

	/**
	 * Updates the attributes of an existing occurrence of the given Kit feature
	 * in a Post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id            Post ID.
	 * @param   string $feature_name       Kit feature name (e.g. `form`), without prefix.
	 * @param   int    $occurrence_index   Zero-based occurrence index to update.
	 * @param   array  $attrs              Feature attributes.
	 * @return  WP_Error|array
	 */
	public static function update( $post_id, $feature_name, $occurrence_index, $attrs ) {

		// Determine how this post stores its content.
		$mechanism = self::detect_mechanism( $post_id );
		if ( is_wp_error( $mechanism ) ) {
			return $mechanism;
		}

		// Updates the existing occurrence of the element in the post, depending on the mechanism.
		// A switch is used as shortcodes and other mechanisms will be supported in the future.
		switch ( $mechanism ) {
			case 'block':
				return ConvertKit_Block_Post_Helper::update(
					$post_id,
					'convertkit/' . $feature_name,
					$occurrence_index,
					$attrs
				);
		}

		return self::unsupported_mechanism_error( $mechanism );

	}

	/**
	 * Deletes a specific occurrence of the given Kit feature from a Post's
	 * content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id            Post ID.
	 * @param   string $feature_name       Kit feature name (e.g. `form`), without prefix.
	 * @param   int    $occurrence_index   Zero-based occurrence index to delete.
	 * @return  WP_Error|array
	 */
	public static function delete( $post_id, $feature_name, $occurrence_index ) {

		// Determine how this post stores its content.
		$mechanism = self::detect_mechanism( $post_id );
		if ( is_wp_error( $mechanism ) ) {
			return $mechanism;
		}

		// Delete the element from the post, depending on the mechanism.
		// A switch is used as shortcodes and other mechanisms will be supported in the future.
		switch ( $mechanism ) {
			case 'block':
				return ConvertKit_Block_Post_Helper::delete(
					$post_id,
					'convertkit/' . $feature_name,
					$occurrence_index
				);
		}

		return self::unsupported_mechanism_error( $mechanism );

	}

	/**
	 * Determines how the given Post stores its content.
	 *
	 * Returns one of:
	 * - 'block'      The Post uses Gutenberg blocks.
	 * - 'shortcode'  The Post uses Classic editor / shortcode markup.
	 * - WP_Error     The Post does not exist, or is built with an unsupported
	 *                page builder.
	 *
	 * Page builders are checked first, because a page builder typically leaves
	 * post_content empty (or a non-authoritative fallback) and stores its real
	 * content in post meta. Writing a block or shortcode into post_content for
	 * such a Post would have no visible effect, so we refuse rather than fail
	 * silently.
	 *
	 * @since   3.4.0
	 *
	 * @param   int $post_id   Post ID.
	 * @return  string|WP_Error
	 */
	private static function detect_mechanism( $post_id ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_content_post_helper_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Bail if the Post is built with a page builder, as these store their
		// content outside post_content.
		$page_builder = self::detect_page_builder( $post_id );
		if ( $page_builder ) {
			return new WP_Error(
				'convertkit_content_post_helper_page_builder_unsupported',
				sprintf(
					/* translators: %s: page builder name */
					__( 'This content is built with %s, which is not yet supported. Add the Kit feature using the page builder editor instead.', 'convertkit' ),
					$page_builder
				)
			);
		}

		// Block-based content if the Post contains block markup; otherwise
		// treat it as Classic editor / shortcode content. An empty Post also
		// falls through to 'shortcode' — a shortcode renders correctly in
		// both Classic and block editors, so this is a safe default.
		return has_blocks( $post->post_content ) ? 'block' : 'shortcode';

	}

	/**
	 * Returns the human-readable name of the page builder used to build the
	 * given Post, or false if no supported page builder is detected.
	 *
	 * @since   3.4.0
	 *
	 * @param   int $post_id   Post ID.
	 * @return  string|false
	 */
	private static function detect_page_builder( $post_id ) {

		// Elementor stores its content in the _elementor_data post meta key,
		// and flags edited posts via _elementor_edit_mode.
		if ( 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
			return 'Elementor';
		}

		/**
		 * Filters the detected page builder for a Post.
		 *
		 * Return a non-empty string (the page builder's name) to mark the Post
		 * as built with an unsupported page builder, causing the Content MCP
		 * abilities to return an error rather than writing to post_content.
		 *
		 * @since   3.4.0
		 *
		 * @param   string|false $page_builder   Detected page builder name, or false.
		 * @param   int          $post_id        Post ID.
		 */
		return apply_filters( 'convertkit_content_post_helper_detect_page_builder', false, $post_id );

	}

	/**
	 * Returns a WP_Error for an unrecognised content mechanism. Acts as a
	 * defensive fallback; detect_mechanism() should only ever return a known
	 * mechanism or a WP_Error.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $mechanism   The unrecognised mechanism.
	 * @return  WP_Error
	 */
	private static function unsupported_mechanism_error( $mechanism ) {

		return new WP_Error(
			'convertkit_content_post_helper_unsupported_mechanism',
			sprintf(
				/* translators: %s: mechanism identifier */
				__( 'Unsupported content mechanism: %s.', 'convertkit' ),
				$mechanism
			)
		);

	}

}
