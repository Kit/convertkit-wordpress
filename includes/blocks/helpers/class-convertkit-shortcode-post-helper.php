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
				// Shortcodes have no top-level block array, so index and
				// occurrence_index are the same value, keeping the return
				// shape identical to ConvertKit_Block_Post_Helper::find().
				'index'            => (int) $occurrence_index,
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
	 * @param   int    $index            Zero-based paragraph index; only used when $position is 'index'.
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

		// Split content into top-level paragraphs.
		$paragraphs = self::split_paragraphs( $post->post_content );

		// Resolve $position into a concrete zero-based splice point.
		switch ( $position ) {
			case 'prepend':
				$insert_at = 0;
				break;

			case 'index':
				$insert_at = max( 0, min( (int) $index + 1, count( $paragraphs ) ) );
				break;

			case 'append':
			default:
				$insert_at = count( $paragraphs );
				break;
		}

		// Splice in the new shortcode as its own paragraph.
		array_splice( $paragraphs, $insert_at, 0, array( $shortcode ) );

		// Update Post.
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => self::join_paragraphs( $paragraphs ),
			),
			true
		);

		// Bail if the update failed.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return the index the shortcode was inserted at.
		return array(
			'post_id' => $post_id,
			'index'   => $insert_at,
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
			'post_id' => $post_id,
			'index'   => (int) $occurrence_index,
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
			'post_id' => $post_id,
			'index'   => (int) $occurrence_index,
		);

	}

	/**
	 * Returns all matches of the given shortcode tag within the content, in
	 * document order.
	 *
	 * Each match is an array: 'text' (the full matched shortcode string) and
	 * 'offset' (its byte offset within the content). The offset is used by
	 * replace_match() to target a specific occurrence even when several
	 * occurrences have identical text.
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

		$found = array();
		foreach ( $matches[0] as $match ) {
			$found[] = array(
				'text'   => $match[0],
				'offset' => (int) $match[1],
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
	 * @param   array $match   A match from match_shortcodes().
	 * @return  array
	 */
	private static function parse_attrs( $match ) {

		$attrs = shortcode_parse_atts( $match['text'] );

		// shortcode_parse_atts() returns a string for an empty shortcode, and
		// the parsed array includes the tag name itself as element 0; strip
		// any non-string keys so only named attributes remain.
		if ( ! is_array( $attrs ) ) {
			return array();
		}

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
	 * @param   array  $match         A match from match_shortcodes().
	 * @param   string $replacement   Replacement string (empty string to delete).
	 * @return  string
	 */
	private static function replace_match( $content, $match, $replacement ) {

		return substr_replace(
			$content,
			$replacement,
			$match['offset'],
			strlen( $match['text'] )
		);

	}

	/**
	 * Splits Post content into top-level paragraphs (text blocks separated by
	 * one or more blank lines), discarding empty fragments.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $content   Post content.
	 * @return  array
	 */
	private static function split_paragraphs( $content ) {

		// Bail with an empty array if there is no content.
		if ( '' === trim( (string) $content ) ) {
			return array();
		}

		$paragraphs = preg_split( '/\R{2,}/', trim( (string) $content ) );

		return is_array( $paragraphs ) ? array_values( array_filter( $paragraphs, 'strlen' ) ) : array();

	}

	/**
	 * Joins paragraphs back into Post content, separated by blank lines.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $paragraphs   Paragraphs.
	 * @return  string
	 */
	private static function join_paragraphs( $paragraphs ) {

		return implode( "\n\n", $paragraphs );

	}

}
