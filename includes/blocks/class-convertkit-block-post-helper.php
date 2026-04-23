<?php
/**
 * ConvertKit Block Post Helper class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Helper methods to find, insert, update and delete blocks within a WordPress Post's content.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Post_Helper {

	/**
	 * Finds all top-level occurrences of the given block in a post's content.
	 *
	 * Returns an array of occurrences in document order, each of the form:
	 *   [ 'index' => <top-level block-array index>, 'attrs' => <block attrs> ]
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id     Post ID.
	 * @param   string $block_name  Full block name, e.g. "convertkit/form".
	 * @return  array|WP_Error      Array of occurrences, or WP_Error if the post is missing.
	 */
	static public function find( $post_id, $block_name ) {

		// Get post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_helper_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Parse blocks.
		$blocks = parse_blocks( $post->post_content );
		$found  = array();

		foreach ( $blocks as $idx => $block ) {
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $block_name ) {
				continue;
			}

			$found[] = array(
				'index' => (int) $idx,
				'attrs' => isset( $block['attrs'] ) ? (array) $block['attrs'] : array(),
			);
		}

		return $found;

	}

	/**
	 * Inserts a new occurrence of the given block into a post's content at the
	 * specified position.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id     Post ID.
	 * @param   string $block_name  Programmatic Block Name.
	 * @param   array  $attrs       Block Attributes.
	 * @param   int    $index       Position to insert block.
	 * @return  int|WP_Error
	 */
	static public function insert( $post_id, $block_name, $attrs, $index = 0 ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_helper_insert_block_post_not_found',
				/* translators: %d: Post ID */
				sprintf( __( 'No Post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Parse blocks.
		$blocks = parse_blocks( $post->post_content );

		// Build the new block to insert.
		$new_block = array(
			'blockName'    => $block_name,
			'attrs'        => (array) $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);

		// Determine where the new block will be inserted.
		$insert_at = max( 0, min( (int) $index, count( $blocks ) ) );
		array_splice( $blocks, $insert_at, 0, array( $new_block ) );

		// Count how many matching occurrences precede the insertion point —
		// that's the new block's zero-based occurrence index.
		$occurrence_index = 0;
		for ( $i = 0; $i < $insert_at; $i++ ) {
			if ( isset( $blocks[ $i ]['blockName'] ) && $blocks[ $i ]['blockName'] === $block_name ) {
				++$occurrence_index;
			}
		}

		// Update Post.
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

		// Bail if an error occurred.
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		// Return the occurrence index.
		return $occurrence_index;

	}

	/**
	 * Updates the attributes of a specific top-level occurrence of the given
	 * block in a post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id           Post ID.
	 * @param   string $block_name        Programmatic Block Name.
	 * @param   int    $occurrence_index  Position to update block.
	 * @param   array  $attrs             Block Attributes.
	 * @return  array|WP_Error
	 */
	static public function update( $post_id, $block_name, $occurrence_index, $attrs ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_helper_update_block_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No Post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Parse blocks.
		$blocks      = parse_blocks( $post->post_content );
		$occurrence  = 0;
		$matched     = false;
		$final_attrs = array();

		foreach ( $blocks as $key => $block ) {
			// Skip if the block name does not match.
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $block_name ) {
				continue;
			}

			// Update the block if the occurrence index matches.
			if ( $occurrence === (int) $occurrence_index ) {
				$existing                = isset( $block['attrs'] ) ? (array) $block['attrs'] : array();
				$final_attrs             = $merge ? array_merge( $existing, (array) $attrs ) : (array) $attrs;
				$blocks[ $key ]['attrs'] = $final_attrs;
				$matched                 = true;
				break;
			}

			++$occurrence;
		}

		// Bail if the block was not found.
		if ( ! $matched ) {
			return new WP_Error(
				'convertkit_block_post_helper_occurrence_not_found',
				/* translators: 1: block name, 2: occurrence index, 3: post ID */
				sprintf( __( 'No occurrence #%2$d of block %1$s found in post %3$d.', 'convertkit' ), $block_name, (int) $occurrence_index, $post_id )
			);
		}

		// Update Post.
		return wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

	}

	/**
	 * Deletes a specific top-level occurrence of the given block from a post's
	 * content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id           Post ID.
	 * @param   string $block_name        Programmatic Block Name.
	 * @param   int    $occurrence_index  Zero-based index among this block's occurrences in the post.
	 * @return  array|WP_Error
	 */
	static public function delete( $post_id, $block_name, $occurrence_index ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_helper_delete_block_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No Post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Parse blocks.
		$blocks     = parse_blocks( $post->post_content );
		$occurrence = 0;
		$matched    = false;

		foreach ( $blocks as $key => $block ) {
			// Skip if the block name does not match.
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $block_name ) {
				continue;
			}

			// Delete the block if the occurrence index matches.
			if ( $occurrence === (int) $occurrence_index ) {
				unset( $blocks[ $key ] );
				$blocks  = array_values( $blocks );
				$matched = true;
				break;
			}

			++$occurrence;
		}

		// Bail if the block was not found.
		if ( ! $matched ) {
			return new WP_Error(
				'convertkit_block_post_helper_delete_block_occurrence_not_found',
				/* translators: 1: block name, 2: occurrence index, 3: post ID */
				sprintf( __( 'No occurrence #%2$d of block %1$s found in Post %3$d.', 'convertkit' ), $block_name, (int) $occurrence_index, $post_id )
			);
		}

		// Update Post.
		return wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

	}

}
