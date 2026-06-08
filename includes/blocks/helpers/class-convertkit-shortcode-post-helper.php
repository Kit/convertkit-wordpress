<?php
/**
 * ConvertKit Shortcode Post Helper class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Helper methods to find, insert, update and delete shortcodes within a WordPress Post's content.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Shortcode_Post_Helper {

	/**
	 * The element-level HTML tags treated as top-level element boundaries when
	 * resolving an insertion position.
	 *
	 * This is the same set WordPress' wpautop() recognises as element-level,
	 * so the segmentation matches how WordPress itself conceptualises Classic
	 * editor content.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const ELEMENT_LEVEL_TAGS = 'address|article|aside|blockquote|details|dd|div|dl|dt|' .
		'figcaption|figure|footer|form|h1|h2|h3|h4|h5|h6|header|hgroup|hr|' .
		'main|menu|nav|ol|p|pre|section|table|ul';

	/**
	 * Finds all occurrences of the given shortcode in a Post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id          Post ID.
	 * @param   string $shortcode_tag    Programmatic Shortcode Tag.
	 * @return  WP_Error|bool|array
	 */
	public static function find( $post_id, $shortcode_tag ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_shortcode_post_helper_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Match all occurrences of the shortcode in the Post's content.
		$matches = self::match_shortcodes( $post->post_content, $shortcode_tag );
		$found   = array();

		foreach ( $matches as $occurrence_index => $match ) {
			$found[] = array(
				// Zero-based index of this occurrence among occurrences of
				// this shortcode in the post.
				'occurrence_index' => (int) $occurrence_index,
				'attrs'            => self::parse_attrs( $match ),
			);
		}

		// If no shortcodes found, return false.
		if ( empty( $found ) ) {
			return false;
		}

		return $found;

	}

	/**
	 * Inserts a new shortcode into the Post's content at the specified
	 * position.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id          Post ID.
	 * @param   string $shortcode_tag    Programmatic Shortcode Tag.
	 * @param   array  $attrs            Shortcode Attributes.
	 * @param   string $position         One of 'prepend', 'append', 'index'.
	 * @param   int    $index            Zero-based top-level element index; only used when $position is 'index'.
	 * @return  WP_Error|array
	 */
	public static function insert( $post_id, $shortcode_tag, $attrs, $position = 'append', $index = 0 ) {

		// If the index is negative, bail.
		if ( $position === 'index' && (int) $index < 0 ) {
			return new WP_Error(
				'convertkit_shortcode_post_helper_invalid_index',
				sprintf(
					/* translators: %d: index */
					__( 'The supplied index (%d) must be zero or a positive integer.', 'convertkit' ),
					(int) $index
				)
			);
		}

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_shortcode_post_helper_insert_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Build the shortcode string to insert.
		$shortcode = self::build_shortcode( $shortcode_tag, $attrs );
		$content   = $post->post_content;

		// Determine the byte offset of the start of each top-level element.
		$starts = self::get_element_starts( $content );

		// Resolve $position into a concrete byte offset within the content.
		switch ( $position ) {
			case 'prepend':
				$insert_at = 0;
				break;

			case 'index':
				// Insert before the Nth top-level element. If no elements
				// exist, or the index is equal to / beyond count(), append
				// after all existing content — mirroring how array_splice()
				// treats an index equal to the array length.
				if ( empty( $starts ) || (int) $index >= count( $starts ) ) {
					$insert_at = strlen( $content );
				} else {
					$insert_at = $starts[ (int) $index ];
				}
				break;

			case 'append':
			default:
				$insert_at = strlen( $content );
				break;
		}

		// Determine the occurrence index the new shortcode will have, by
		// counting how many existing occurrences of the same shortcode start
		// before the insertion offset.
		$occurrence_index = 0;
		foreach ( self::match_shortcodes( $content, $shortcode_tag ) as $match ) {
			if ( $match['offset'] < $insert_at ) {
				++$occurrence_index;
			}
		}

		// Splice the shortcode into the content at the resolved offset,
		// wrapped in blank lines so it sits as its own top-level element.
		// All other content is left byte-for-byte unchanged.
		$snippet = self::pad_snippet( $shortcode, $content, $insert_at );
		$content = substr_replace( $content, $snippet, $insert_at, 0 );

		// Update Post.
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $content,
			),
			true
		);

		// Bail if the update failed.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return the occurrence index of the newly inserted shortcode.
		return array(
			'post_id'          => $post_id,
			'occurrence_index' => $occurrence_index,
		);

	}

	/**
	 * Updates the attributes of an existing shortcode in the Post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id            Post ID.
	 * @param   string $shortcode_tag      Programmatic Shortcode Tag.
	 * @param   int    $occurrence_index   Zero-based occurrence index to update.
	 * @param   array  $attrs              Shortcode Attributes.
	 * @return  WP_Error|array
	 */
	public static function update( $post_id, $shortcode_tag, $occurrence_index, $attrs ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_shortcode_post_helper_update_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Match all occurrences of the shortcode.
		$matches = self::match_shortcodes( $post->post_content, $shortcode_tag );

		// Bail if the requested occurrence does not exist.
		if ( ! isset( $matches[ (int) $occurrence_index ] ) ) {
			return new WP_Error(
				'convertkit_shortcode_post_helper_occurrence_not_found',
				sprintf(
					/* translators: 1: shortcode tag, 2: occurrence index, 3: post ID */
					__( 'No occurrence #%2$d of shortcode %1$s found in post %3$d.', 'convertkit' ),
					$shortcode_tag,
					(int) $occurrence_index,
					$post_id
				)
			);
		}

		// Build the replacement shortcode, merging new attributes over existing.
		$match        = $matches[ (int) $occurrence_index ];
		$merged_attrs = array_merge( self::parse_attrs( $match ), (array) $attrs );
		$replacement  = self::build_shortcode( $shortcode_tag, $merged_attrs );

		// Replace the matched shortcode text with the rebuilt shortcode.
		$content = self::replace_match( $post->post_content, $match, $replacement );

		// Update Post.
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $content,
			),
			true
		);

		// Bail if the update failed.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return the occurrence index that was updated.
		return array(
			'post_id'          => $post_id,
			'occurrence_index' => (int) $occurrence_index,
		);

	}

	/**
	 * Deletes a specific shortcode from the Post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id            Post ID.
	 * @param   string $shortcode_tag      Programmatic Shortcode Tag.
	 * @param   int    $occurrence_index   Zero-based occurrence index to delete.
	 * @return  WP_Error|array
	 */
	public static function delete( $post_id, $shortcode_tag, $occurrence_index ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_shortcode_post_helper_delete_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Match all occurrences of the shortcode.
		$matches = self::match_shortcodes( $post->post_content, $shortcode_tag );

		// Bail if the requested occurrence does not exist.
		if ( ! isset( $matches[ (int) $occurrence_index ] ) ) {
			return new WP_Error(
				'convertkit_shortcode_post_helper_occurrence_not_found',
				sprintf(
					/* translators: 1: shortcode tag, 2: occurrence index, 3: post ID */
					__( 'No occurrence #%2$d of shortcode %1$s found in post %3$d.', 'convertkit' ),
					$shortcode_tag,
					(int) $occurrence_index,
					$post_id
				)
			);
		}

		// Remove the matched shortcode text from the content.
		$content = self::replace_match( $post->post_content, $matches[ (int) $occurrence_index ], '' );

		// Update Post.
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $content,
			),
			true
		);

		// Bail if the update failed.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return the occurrence index that was deleted.
		return array(
			'post_id'          => $post_id,
			'occurrence_index' => (int) $occurrence_index,
		);

	}

	/**
	 * Returns all matches of the given shortcode tag within the content, in
	 * document order.
	 *
	 * Each match is an array of:
	 * - 'text'   The full matched shortcode string (e.g. `[convertkit_form form="1"]`).
	 * - 'offset' Its byte offset within the content.
	 * - 'atts'   The raw attribute string only (e.g. `form="1"`), suitable for
	 *            passing directly to shortcode_parse_atts().
	 *
	 * @since   3.4.0
	 *
	 * @param   string $content         Post content.
	 * @param   string $shortcode_tag   Programmatic Shortcode Tag.
	 * @return  array
	 */
	private static function match_shortcodes( $content, $shortcode_tag ) {

		// Build a shortcode regex scoped to this single tag.
		$pattern = get_shortcode_regex( array( $shortcode_tag ) );

		// Bail if there are no matches.
		if ( ! preg_match_all( '/' . $pattern . '/', $content, $matches, PREG_OFFSET_CAPTURE ) ) {
			return array();
		}

		// WordPress' shortcode regex captures the attribute string in group 3.
		// With PREG_OFFSET_CAPTURE each group is a [ value, offset ] pair.
		$found = array();
		foreach ( $matches[0] as $i => $match ) {
			$found[] = array(
				'text'   => $match[0],
				'offset' => (int) $match[1],
				'atts'   => isset( $matches[3][ $i ][0] ) ? trim( (string) $matches[3][ $i ][0] ) : '',
			);
		}

		return $found;

	}

	/**
	 * Parses the attributes of a single matched shortcode into a key/value
	 * array.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $shortcode   A match from match_shortcodes().
	 * @return  array
	 */
	private static function parse_attrs( $shortcode ) {

		// Parse the raw attribute string (e.g. `form="1"`). shortcode_parse_atts()
		// expects only the attributes, without the surrounding brackets or tag name.
		$attrs = shortcode_parse_atts( $shortcode['atts'] );

		// Discard any positional (non-string keyed) attributes, keeping only
		// named attributes.
		foreach ( array_keys( $attrs ) as $key ) {
			if ( ! is_string( $key ) ) {
				unset( $attrs[ $key ] );
			}
		}

		return $attrs;

	}

	/**
	 * Builds a self-closing shortcode string from a tag and attributes.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $shortcode_tag   Programmatic Shortcode Tag.
	 * @param   array  $attrs           Shortcode Attributes.
	 * @return  string
	 */
	private static function build_shortcode( $shortcode_tag, $attrs ) {

		$shortcode = '[' . $shortcode_tag;

		foreach ( (array) $attrs as $key => $value ) {
			// Skip empty attribute names.
			if ( ! is_string( $key ) || '' === $key ) {
				continue;
			}

			$shortcode .= sprintf( ' %s="%s"', $key, esc_attr( (string) $value ) );
		}

		$shortcode .= ']';

		return $shortcode;

	}

	/**
	 * Replaces a single matched shortcode occurrence with the replacement
	 * string, targeting it by byte offset so identical occurrences elsewhere
	 * are left untouched.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $content       Post content.
	 * @param   array  $atts          A match from match_shortcodes().
	 * @param   string $replacement   Replacement string (empty string to delete).
	 * @return  string
	 */
	private static function replace_match( $content, $atts, $replacement ) {

		return substr_replace(
			$content,
			$replacement,
			$atts['offset'],
			strlen( $atts['text'] )
		);

	}

	/**
	 * Wraps a shortcode snippet in blank-line padding so that, once inserted
	 * at the given offset, it sits as its own top-level element.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $shortcode   The shortcode string to insert.
	 * @param   string $content     The content the shortcode is being inserted into.
	 * @param   int    $offset      Byte offset within $content the shortcode will be inserted at.
	 * @return  string
	 */
	private static function pad_snippet( $shortcode, $content, $offset ) {

		// Determine the text immediately before and after the insertion point.
		$before = substr( $content, 0, $offset );
		$after  = substr( $content, $offset );

		// Add a leading blank line unless the shortcode is at the start of the
		// content, or already preceded by a blank line.
		$lead = ( '' === $before || (bool) preg_match( '/\R\R\s*$/', $before ) ) ? '' : "\n\n";

		// Add a trailing blank line unless the shortcode is at the end of the
		// content, or already followed by a blank line.
		$trail = ( '' === $after || (bool) preg_match( '/^\s*\R\R/', $after ) ) ? '' : "\n\n";

		return $lead . $shortcode . $trail;

	}

	/**
	 * Returns the byte offset of the start of each top-level element in
	 * the content, in document order.
	 *
	 * Uses WP_HTML_Tag_Processor when available (WordPress 6.2+), which is a
	 * streaming HTML parser that does not rewrite the content (unlike
	 * DOMDocument), preserving the byte-for-byte guarantee that update() /
	 * delete() rely on.
	 *
	 * Falls back to a regex for older WordPress versions, which
	 * cannot handle same-tag nesting (e.g. <div> inside <div>) — rare in
	 * Classic editor content.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $content   Post content.
	 * @return  int[]              Zero-indexed array of byte offsets.
	 */
	private static function get_element_starts( $content ) {

		// Bail with an empty array if there is no content.
		if ( trim( (string) $content ) === '' ) {
			return array();
		}

		// Prefer WP_HTML_Tag_Processor where available (WordPress 6.2+) —
		// it is a streaming HTML parser that handles nested same-name tags
		// correctly, and does not re-serialise / normalise content.
		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return self::get_element_starts_via_html_processor( $content );
		}

		// Fallback: regex-based detection of top-level element-level elements.
		// This matches the same set of tags as wpautop() treats as block-level,
		// but does not handle same-tag nesting (e.g. a <div> within a <div>).
		// Used only on WordPress versions older than 6.2.
		$pattern = '/<(' . self::ELEMENT_LEVEL_TAGS . ')\b[^>]*>.*?<\/\1>/is';
		if ( ! preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE ) ) {
			return array();
		}

		$starts = array();
		foreach ( $matches[0] as $match ) {
			$starts[] = (int) $match[1];
		}

		return $starts;

	}

	/**
	 * Returns top-level element start offsets, computed using
	 * WP_HTML_Tag_Processor.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $content   Post content.
	 * @return  int[]
	 */
	private static function get_element_starts_via_html_processor( $content ) {

		$processor = new WP_HTML_Tag_Processor( $content );

		$starts             = array();
		$depth              = 0;
		$element_level_tags = array_flip( explode( '|', strtoupper( self::ELEMENT_LEVEL_TAGS ) ) );

		while ( $processor->next_tag( array( 'tag_closers' => 'visit' ) ) ) {

			$tag = $processor->get_tag();

			// Only element-level tags participate in depth tracking.
			if ( ! isset( $element_level_tags[ $tag ] ) ) {
				continue;
			}

			if ( $processor->is_tag_closer() ) {
				if ( $depth > 0 ) {
					--$depth;
				}
				continue;
			}

			// Opening tag at depth zero: this is a top-level element.
			if ( 0 === $depth ) {
				// Bookmark the opener so we can read its absolute start byte
				// offset. WP_HTML_Tag_Processor::get_bookmark() returns an
				// object whose `->start` property is the offset of the `<`.
				$processor->set_bookmark( 'el' );
				$bookmark = $processor->get_bookmark( 'el' );

				if ( is_object( $bookmark ) && isset( $bookmark->start ) ) {
					$starts[] = (int) $bookmark->start;
				}
			}

			// Void elements (hr) do not increase depth; everything else does.
			// WP_HTML_Tag_Processor returns is_tag_closer() === false for both
			// void and non-void openers, so we approximate void-vs-non-void
			// by checking the tag name. `hr` is the only void element in our
			// ELEMENT_LEVEL_TAGS list.
			if ( 'HR' !== $tag ) {
				++$depth;
			}
		}

		return $starts;

	}

}
