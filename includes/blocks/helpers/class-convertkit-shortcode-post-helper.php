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

		// Determine the byte offset immediately after each top-level element.
		$offsets = self::get_element_offsets( $content );

		// Resolve $position into a concrete byte offset within the content.
		switch ( $position ) {
			case 'prepend':
				$insert_at = 0;
				break;

			case 'index':
				// Insert after the Nth top-level element. If no elements
				// exist, or the index is beyond the last element, fall back
				// to appending after all existing content.
				if ( empty( $offsets ) || (int) $index >= count( $offsets ) ) {
					$insert_at = strlen( $content );
				} else {
					$insert_at = $offsets[ max( 0, (int) $index ) ];
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

		// shortcode_parse_atts() returns an empty string when there are no
		// attributes; normalise that to an array.
		if ( ! is_array( $attrs ) ) {
			return array();
		}

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
	 * Returns the byte offset immediately after each top-level element in the
	 * content, in document order.
	 *
	 * A top-level element is a single top-level element-level HTML element
	 * (e.g. a whole `<p>...</p>`). These are the Classic content analogue of
	 * the top-level blocks that ConvertKit_Block_Post_Helper works against:
	 * counting them lets a caller-supplied `index` mean the same kind of unit
	 * in both mechanisms.
	 *
	 * The returned offsets are the points *after* each element, suitable for
	 * use as a `substr_replace()` insertion point.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $content   Post content.
	 * @return  int[]              Zero-indexed array of byte offsets.
	 */
	private static function get_element_offsets( $content ) {

		// Bail with an empty array if there is no content.
		if ( '' === trim( (string) $content ) ) {
			return array();
		}

		// Match each top-level element-level element in document order. The
		// 's' flag lets the element span multiple lines; the lazy quantifier
		// and \1 backreference keep the match scoped to a single element.
		//
		// Note: this does not handle an element-level tag nested inside
		// another of the same name (e.g. a <div> within a <div>). Such
		// nesting is rare in Classic editor content, and parsing it correctly
		// requires a full HTML parser, which is avoided here to keep the
		// content modification surgical (see insert()).
		$pattern = '/<(' . self::ELEMENT_LEVEL_TAGS . ')\b[^>]*>.*?<\/\1>/is';

		// Bail with an empty array if no top-level elements are found.
		if ( ! preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE ) ) {
			return array();
		}

		// Record the byte offset immediately after each matched element.
		$offsets = array();
		foreach ( $matches[0] as $match ) {
			$offsets[] = (int) $match[1] + strlen( $match[0] );
		}

		return $offsets;

	}

}
