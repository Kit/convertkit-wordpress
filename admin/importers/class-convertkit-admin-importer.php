<?php
/**
 * ConvertKit Admin Importer class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Import and migrate data from third party Form plugins to Kit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
abstract class ConvertKit_Admin_Importer {

	/**
	 * Holds the importer name.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $name = '';

	/**
	 * Holds the importer title.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $title = '';

	/**
	 * Holds the shortcode name for the third party Form plugin.
	 *
	 * @since   3.1.0
	 *
	 * @var     string
	 */
	public $shortcode_name = '';

	/**
	 * Holds the shortcode ID attribute name for the third party Form plugin.
	 *
	 * @since   3.1.0
	 *
	 * @var     bool|string
	 */
	public $shortcode_id_attribute = false;

	/**
	 * Holds the block name for the third party Form plugin.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $block_name = '';

	/**
	 * Holds the block ID attribute name for the third party Form plugin.
	 *
	 * @since   3.1.6
	 *
	 * @var     bool|string
	 */
	public $block_id_attribute = false;

	/**
	 * Returns an array of third party form IDs and titles.
	 *
	 * @since   3.1.0
	 *
	 * @return  array
	 */
	abstract public function get_forms();

	/**
	 * Registers the importer if third party forms exist.
	 *
	 * @since   3.1.7
	 *
	 * @param   array $importers     Importers.
	 * @return  array
	 */
	public function register( $importers ) {

		// Bail if no third party forms exist in posts.
		if ( ! $this->has_forms_in_posts() ) {
			return $importers;
		}

		// Bail if no third party forms exist for this importer.
		if ( ! $this->has_forms() ) {
			return $importers;
		}

		// Add this importer to the list of importers.
		$importers[ $this->name ] = array(
			'name'  => $this->name,
			'title' => $this->title,
			'forms' => $this->get_forms(),
		);

		return $importers;

	}

	/**
	 * Replaces third party form shortcodes and blocks with Kit form shortcodes and blocks.
	 *
	 * @since   3.1.7
	 *
	 * @param   array $mappings     Mappings.
	 */
	public function import( $mappings ) {

		// Iterate through the mappings, replacing the third party form shortcodes and blocks with the Kit form shortcodes and blocks.
		foreach ( $mappings as $third_party_form_id => $kit_form_id ) {
			// Skip empty Kit Form IDs i.e. no mapping was provided for this third party form.
			if ( empty( $kit_form_id ) ) {
				continue;
			}

			if ( $this->block_name ) {
				$this->replace_blocks_in_posts( $third_party_form_id, (int) $kit_form_id );
			}
			if ( $this->shortcode_name ) {
				$this->replace_shortcodes_in_posts( $third_party_form_id, (int) $kit_form_id );
			}
		}

	}

	/**
	 * Returns an array of post IDs that contain the third party form block or shortcode.
	 *
	 * @since   3.1.5
	 *
	 * @return  array
	 */
	public function get_forms_in_posts() {

		global $wpdb;

		// Build WHERE clauses and values.
		$post_content_clauses = array();
		$post_content_values  = array();

		if ( $this->shortcode_name ) {
			$post_content_clauses[] = 'post_content LIKE %s';
			$post_content_values[]  = '%[' . $this->shortcode_name . '%';
		}
		if ( $this->block_name ) {
			$post_content_clauses[] = 'post_content LIKE %s';
			$post_content_values[]  = '%<!-- wp:' . $this->block_name . '%';
		}

		// Bail early if nothing to search for.
		if ( empty( $post_content_clauses ) ) {
			return array();
		}

		// Prepare SQL using wpdb->prepare.
		// call_user_func_array() is used so variable length arrays can be passed to prepare().
		$query = call_user_func_array(
			array( $wpdb, 'prepare' ),
			array_merge(
				array(
					"
					SELECT ID
					FROM {$wpdb->posts}
					WHERE post_status = %s
					AND (
						" . implode( ' OR ', $post_content_clauses ) . '
					)
				',
				),
				array_merge(
					array( 'publish' ),
					$post_content_values
				)
			)
		);

		// Run query.
		$results = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $results ? $results : array();

	}

	/**
	 * Returns whether any third party forms exist.
	 *
	 * @since   3.1.0
	 *
	 * @return  bool
	 */
	public function has_forms() {

		return count( $this->get_forms() ) > 0;

	}

	/**
	 * Returns whether any third party forms exist in posts.
	 *
	 * @since   3.1.0
	 *
	 * @return  bool
	 */
	public function has_forms_in_posts() {

		return count( $this->get_forms_in_posts() ) > 0;

	}

	/**
	 * Replaces the third party form shortcode with the Kit form shortcode.
	 *
	 * @since   3.1.0
	 *
	 * @param   string|int $third_party_form_id    Third Party Form ID.
	 * @param   int        $form_id                Kit Form ID.
	 */
	public function replace_shortcodes_in_posts( $third_party_form_id, $form_id ) {

		// Get Posts that contain the third party Form Shortcode.
		$posts = $this->get_forms_in_posts();

		// Bail if no Posts contain the third party Form Shortcode.
		if ( empty( $posts ) ) {
			return;
		}

		// Iterate through Posts and replace the third party Form Shortcode with the Kit Form Shortcode.
		foreach ( $posts as $post_id ) {
			// Get Post content.
			$post_content = get_post_field( 'post_content', $post_id );

			// Replace the third party Form Shortcode with the Kit Form Shortcode.
			$post_content = $this->replace_shortcodes_in_content( $post_content, $third_party_form_id, $form_id );

			// Update the Post content.
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $post_content,
				),
				false,
				false // Don't fire after action hooks.
			);

		}

	}

	/**
	 * Replaces the third party form shortcode with the Kit form shortcode in the given string.
	 *
	 * @since   3.1.0
	 *
	 * @param   string     $content                Content containing third party Form Shortcodes.
	 * @param   string|int $third_party_form_id    Third Party Form ID.
	 * @param   int        $form_id                Kit Form ID.

	 * @return  string
	 */
	public function replace_shortcodes_in_content( $content, $third_party_form_id, $form_id ) {

		// If there's no shortcode ID attribute, match shortcodes with or without any attribute.
		if ( ! $this->shortcode_id_attribute ) {
			$pattern = '/\['                                     // Start regex with an opening square bracket.
			. preg_quote( $this->shortcode_name, '/' )       // Match the shortcode name, escaping any regex special chars.
			. '[^\]]*?\]/i';                                 // Match any other characters (non-greedy) up to the closing square bracket, case-insensitive.
		} else {
			$pattern = '/\['                                     // Start regex with an opening square bracket.
				. preg_quote( $this->shortcode_name, '/' )       // Match the shortcode name, escaping any regex special chars.
				. '[^\]]*?'                                      // Match any characters that are not a closing square bracket, non-greedy.
				. '\b' . preg_quote( $this->shortcode_id_attribute, '/' ) // Match the id attribute word boundary and escape as needed.
				. '\s*=\s*'                                      // Match optional whitespace around an equals sign.
				. '(?:"' . preg_quote( (string) $third_party_form_id, '/' ) . '"|\'' . preg_quote( (string) $third_party_form_id, '/' ) . '\'|' . preg_quote( (string) $third_party_form_id, '/' ) . ')' // Match the form ID, double quotes, single quotes or unquoted.
				. '[^\]]*?\]/i';                                 // Match any other characters (non-greedy) up to the closing square bracket, case-insensitive.
		}

		return preg_replace(
			$pattern,
			'[convertkit_form id="' . $form_id . '"]',
			$content
		);

	}

	/**
	 * Returns an array of all unique form IDs from the posts that contain the third party form shortcode.
	 *
	 * @since   3.1.5
	 *
	 * @return  array
	 */
	public function get_form_ids_in_posts() {

		// Get Post IDs that contain the third party form shortcode.
		$post_ids = $this->get_forms_in_posts();

		// If no post IDs are found, return an empty array.
		if ( ! count( $post_ids ) ) {
			return array();
		}

		// If the shortcode or block ID attribute is not set, the third party Plugin doesn't use IDs
		// and only has one form.
		if ( ! $this->shortcode_id_attribute && ! $this->block_id_attribute ) {
			return array(
				__( 'Default Form', 'convertkit' ),
			);
		}

		// Iterate through Posts, extracting the Form IDs from the third party form shortcodes.
		$form_ids = array();
		foreach ( $post_ids as $post_id ) {
			$content_form_ids = $this->get_form_ids_from_content( get_post_field( 'post_content', $post_id ) );
			$form_ids         = array_merge( $form_ids, $content_form_ids );
		}

		$form_ids = array_values( array_unique( $form_ids ) );

		return $form_ids;

	}

	/**
	 * Returns an array of form IDs within the shortcode for the third party Form plugin.
	 *
	 * @since   3.1.5
	 *
	 * @param   string $content             Content containing third party Form Shortcodes.
	 * @return  array
	 */
	public function get_form_ids_from_content( $content ) {

		// If there's no shortcode ID attribute, match shortcodes with or without any attribute and treat any match as a single "form".
		if ( ! $this->shortcode_id_attribute ) {
			$pattern = '/\['                                       // Start regex with an opening square bracket.
				. preg_quote( $this->shortcode_name, '/' )         // Match the shortcode name, escaping any regex special chars.
				. '(?:\s+[^\]]*)?'                                 // Optionally match any attributes (key/value pairs), non-greedy.
				. '[^\]]*?\]/i';                                   // Match up to closing bracket, case-insensitive.

			preg_match_all( $pattern, $content, $matches );

			// If we matched at least one occurrence, just return an array with a single 0 (default/non-ID form).
			if ( ! empty( $matches[0] ) ) {
				return array( 0 );
			}

			return array();
		}

		// Legacy: Extract where attribute is required.
		$pattern = '/\['                                       // Start regex with an opening square bracket.
			. preg_quote( $this->shortcode_name, '/' )         // Match the shortcode name, escaping any regex special chars.
			. '(?:\s+[^\]]*)?'                                 // Optionally match any attributes (key/value pairs), non-greedy.
			. preg_quote( $this->shortcode_id_attribute, '/' ) // Match the id attribute name.
			. '\s*=\s*'                                        // Optional whitespace, equals sign, optional whitespace.
			. '(?:"([^"]+)"|\'([^\']+)\'|([^\s\]]+))'          // Capture double quoted, single quoted or unquoted value.
			. '[^\]]*?\]/i';                                   // Match up to closing bracket, case-insensitive.

		preg_match_all( $pattern, $content, $matches );

		// Extract form IDs: They could be in either $matches[1] (double quoted), $matches[2] (single quoted) or $matches[3] (unquoted).
		$form_ids = array_values(
			array_filter(
				array_merge(
					isset( $matches[1] ) ? $matches[1] : array(),
					isset( $matches[2] ) ? $matches[2] : array(),
					isset( $matches[3] ) ? $matches[3] : array()
				)
			)
		);

		return $form_ids;

	}

	/**
	 * Replaces the third party form block with the Kit form block.
	 *
	 * @since   3.1.6
	 *
	 * @param   string|int $third_party_form_id    Third Party Form ID.
	 * @param   int        $form_id                Kit Form ID.
	 */
	public function replace_blocks_in_posts( $third_party_form_id, $form_id ) {

		// Get Posts that contain the third party Form Block.
		$posts = $this->get_forms_in_posts();

		// Bail if no Posts contain the third party Form Block.
		if ( empty( $posts ) ) {
			return;
		}

		// Iterate through Posts and replace the third party Form Block with the Kit Form Block.
		foreach ( $posts as $post_id ) {
			$this->replace_blocks_in_post( $post_id, $third_party_form_id, $form_id );
		}

	}

	/**
	 * Replaces the third party form block with the Kit form block in the given post.
	 *
	 * @since   3.1.6
	 *
	 * @param   int        $post_id                Post ID.
	 * @param   string|int $third_party_form_id    Third Party Form ID.
	 * @param   int        $form_id                Kit Form ID.
	 */
	public function replace_blocks_in_post( $post_id, $third_party_form_id, $form_id ) {

		// Get Post content.
		$post_content = get_post_field( 'post_content', $post_id );

		// Fetch Blocks from Content.
		$blocks = parse_blocks( $post_content );

		// If a single block was returned with blockName null, this content was not created using the block editor.
		if ( count( $blocks ) === 1 && is_null( $blocks[0]['blockName'] ) ) {
			return;
		}

		// Replace the third party Form Block with the Kit Form Block.
		$post_content = $this->replace_blocks_in_content( $blocks, $third_party_form_id, $form_id );

		// Double escape backslashes so that wp_update_post doesn't remove them.
		// When content contains a single backslash (\), wp_update_post will strip it unless we double escape it (\\).
		$post_content = str_replace( '\\', '\\\\', $post_content );

		// Update the Post content.
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $post_content,
			),
			false,
			false // Don't fire after action hooks.
		);

	}

	/**
	 * Replaces the third party form block with the Kit form block in the given string.
	 *
	 * @since   3.1.6
	 *
	 * @param   array      $blocks                 Blocks.
	 * @param   string|int $third_party_form_id    Third Party Form ID.
	 * @param   int        $form_id                Kit Form ID.
	 *
	 * @return  string
	 */
	public function replace_blocks_in_content( $blocks, $third_party_form_id, $form_id ) {

		// Recursively convert blocks.
		$blocks = $this->recursively_convert_blocks( $blocks, $third_party_form_id, $form_id );

		// Serialize blocks.
		return serialize_blocks( $blocks );

	}

	/**
	 * Recursively walks through an array of blocks and innerBlocks,
	 * converting third party form blocks to Kit form blocks.
	 *
	 * @since   3.1.6
	 *
	 * @param   array      $blocks                 Blocks.
	 * @param   string|int $third_party_form_id    Third Party Form ID.
	 * @param   int        $form_id                Kit Form ID.
	 * @return  array
	 */
	private function recursively_convert_blocks( $blocks, $third_party_form_id, $form_id ) {

		foreach ( $blocks as $index => $block ) {
			// If this block has inner blocks, walk through the inner blocks.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->recursively_convert_blocks( $block['innerBlocks'], $third_party_form_id, $form_id );
			}

			// Skip if a null block name.
			if ( is_null( $block['blockName'] ) ) {
				continue;
			}

			// Skip if not a third party form block.
			if ( strpos( $block['blockName'], $this->block_name ) === false ) {
				continue;
			}

			// If the block ID attribute is not set, the third party Plugin doesn't use IDs,
			// so there's no need to check the $third_party_form_id matches the block attribute.
			if ( $this->block_id_attribute ) {
				// Skip if the attribute doesn't exist i.e. the block was not configured.
				if ( ! array_key_exists( $this->block_id_attribute, $block['attrs'] ) ) {
					continue;
				}

				// Skip if the third party form ID doesn't exist within the third party form block's attribute.
				if ( stripos( $block['attrs'][ $this->block_id_attribute ], (string) $third_party_form_id ) === false ) {
					continue;
				}
			}

			// Replace third party form block with Kit form block.
			$blocks[ $index ] = array(
				'blockName'    => 'convertkit/form',
				'attrs'        => array(
					'form' => (string) $form_id,
				),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);
		}

		return $blocks;

	}

}
