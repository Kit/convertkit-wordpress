<?php
/**
 * ConvertKit Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * ConvertKit Block definition for Gutenberg and Shortcode.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block {

	/**
	 * Registers this block with the ConvertKit Plugin.
	 *
	 * @since   1.9.6
	 *
	 * @param   array $blocks     Blocks to Register.
	 * @return  array               Blocks to Register
	 */
	public function register( $blocks ) {

		// If the request is for the frontend, return the minimum block definition required
		// to register and render the block on the frontend site using register_block_type().
		if ( ! $this->is_admin_frontend_editor_or_admin_rest_request() ) {
			$blocks[ $this->get_name() ] = array(
				'title'           => $this->get_title(),
				'icon'            => $this->get_icon(),
				'attributes'      => $this->get_attributes(),
				'render_callback' => array( $this, 'render' ),
			);

			return $blocks;
		}

		// Request is for the WordPress Administration, frontend editor or REST API request.
		// Register the full block definition, including fields, panels, default values and supports.
		$blocks[ $this->get_name() ] = array_merge(
			$this->get_overview(),
			array(
				'name'           => $this->get_name(),
				'fields'         => $this->get_fields(),
				'attributes'     => $this->get_attributes(),
				'supports'       => $this->get_supports(),
				'panels'         => $this->get_panels(),
				'default_values' => $this->get_default_values(),
			)
		);

		return $blocks;

	}

	/**
	 * Returns this block's programmatic name, excluding the convertkit- prefix.
	 *
	 * @since   1.9.6
	 */
	public function get_name() {

		/**
		 * This will register as:
		 * - a shortcode, with the name [convertkit_form].
		 * - a shortcode, with the name [convertkit], for backward compat.
		 * - a Gutenberg block, with the name convertkit/form.
		 */
		return '';

	}

	/**
	 * Returns this block's title.
	 *
	 * @since   3.1.1
	 */
	public function get_title() {

		return '';

	}

	/**
	 * Returns this block's icon.
	 *
	 * @since   3.1.1
	 */
	public function get_icon() {

		return '';

	}

	/**
	 * Returns this block's Title, Icon, Categories, Keywords and properties.
	 *
	 * @since   1.9.6
	 *
	 * @return  array
	 */
	public function get_overview() {

		return array();

	}

	/**
	 * Returns this block's Attributes
	 *
	 * @since   1.9.6.5
	 *
	 * @return  array
	 */
	public function get_attributes() {

		return array();

	}

	/**
	 * Gutenberg: Returns supported built in attributes, such as
	 * className, color etc.
	 *
	 * @since   1.9.7.4
	 *
	 * @return  array   Supports
	 */
	public function get_supports() {

		return array(
			'className' => true,
		);

	}

	/**
	 * Returns this block's Fields
	 *
	 * @since   1.9.6
	 *
	 * @return  array
	 */
	public function get_fields() {

		return array();

	}

	/**
	 * Returns this block's UI panels / sections.
	 *
	 * @since   1.9.6
	 *
	 * @return  array
	 */
	public function get_panels() {

		return array();

	}

	/**
	 * Returns this block's Default Values
	 *
	 * @since   1.9.6
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array();

	}

	/**
	 * Returns the given block's field's Default Value
	 *
	 * @since   1.9.6
	 *
	 * @param   string $field Field Name.
	 * @return  string
	 */
	public function get_default_value( $field ) {

		$defaults = $this->get_default_values();
		if ( isset( $defaults[ $field ] ) ) {
			return $defaults[ $field ];
		}

		return '';

	}

	/**
	 * Performs several transformation on a block's attributes, including:
	 * - sanitization
	 * - adding attributes with default values are missing but registered by the block
	 * - cast attribute values based on their defined type
	 *
	 * These steps are performed because the attributes may be defined by a shortcode,
	 * block or third party widget/page builder's block, each of which handle attributes
	 * slightly differently.
	 *
	 * Returns a standardised attributes array.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   array $atts   Declared attributes.
	 * @return  array           All attributes, standardised.
	 */
	public function sanitize_and_declare_atts( $atts ) {

		// Sanitize attributes, merging with default values so that the array
		// of attributes contains all expected keys for this block.
		$atts = shortcode_atts(
			$this->get_default_values(),
			$this->sanitize_atts( $atts ),
			$this->get_name()
		);

		// Fetch attribute definitions.
		$atts_definitions = $this->get_attributes();

		// Iterate through attributes, casting them based on their attribute definition.
		foreach ( $atts as $att => $value ) {
			// Skip if no definition exists for this attribute.
			if ( ! array_key_exists( $att, $atts_definitions ) ) {
				continue;
			}

			// Skip if no type exists for this attribute.
			if ( ! array_key_exists( 'type', $atts_definitions[ $att ] ) ) {
				continue;
			}

			// Cast, depending on the attribute type.
			switch ( $atts_definitions[ $att ]['type'] ) {
				case 'number':
					$atts[ $att ] = (int) $value;
					break;

				case 'boolean':
					$atts[ $att ] = (bool) $value;
					break;

				case 'string':
					// If the attribute's value is empty, check if the default attribute has a value.
					// If so, apply it now.
					// shortcode_atts() will only do this if the attribute key isn't specified.
					if ( empty( $value ) && ! empty( $this->get_default_value( $att ) ) ) {
						$atts[ $att ] = $this->get_default_value( $att );
					}
					break;
			}
		}

		// Remove some unused attributes, now they're declared above.
		unset( $atts['style'], $atts['backgroundColor'], $atts['textColor'], $atts['className'] );

		return $atts;

	}

	/**
	 * Removes any HTML that might be wrongly included in the shorcode attribute's values
	 * due to e.g. copy and pasting from Documentation or other examples.
	 *
	 * @since   1.9.6
	 *
	 * @param   array $atts   Block or shortcode attributes.
	 * @return  array
	 */
	public function sanitize_atts( $atts ) {

		foreach ( $atts as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			$atts[ $key ] = wp_strip_all_tags( $value );
		}

		return $atts;

	}

	/**
	 * Builds CSS class(es) that might need to be added to the top level element's `class` attribute
	 * when using Gutenberg, to honor the block's styles and layout settings.
	 *
	 * @since   2.8.3
	 *
	 * @param   array $additional_classes   Additional classes to add to the block.
	 * @return  array
	 */
	public function get_css_classes( $additional_classes = array() ) {

		// To avoid errors in get_block_wrapper_attributes() in non-block themes using the shortcode,
		// tell WordPress that a block is being rendered.
		// The attributes don't matter, as we send them to the render() function.
		if ( class_exists( 'WP_Block_Supports' ) && is_null( WP_Block_Supports::$block_to_render ) ) { // @phpstan-ignore-line
			WP_Block_Supports::$block_to_render = array(
				'blockName'    => 'convertkit/' . $this->get_name(),
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);
		}

		// Get the block wrapper attributes string.
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => implode(
					' ',
					array_merge(
						array(
							'convertkit-' . $this->get_name(),
						),
						$additional_classes
					)
				),
			)
		);

		// Extract the class attribute from the wrapper attributes string, returning as an array.
		// Extract just the class attribute value from the wrapper attributes string.
		$classes = array();
		if ( preg_match( '/class="([^"]*)"/', $wrapper_attributes, $matches ) ) {
			$classes = explode( ' ', $matches[1] );
		} else {
			$classes = array(
				'convertkit-' . $this->get_name(),
			);
		}

		// Remove some classes WordPress adds that we don't want, as they break the layout.
		$classes = array_diff( $classes, array( 'alignfull', 'wp-block-post-content' ) );

		return $classes;

	}

	/**
	 * Builds inline CSS style(s) that might need to be added to the top level element's `style` attribute
	 * when using Gutenberg, a shortcode or third party page builder module / widget.
	 *
	 * @since   2.8.3
	 *
	 * @param   array $atts   Block or shortcode attributes.
	 * @return  array
	 */
	public function get_css_styles( $atts ) {

		// To avoid errors in get_block_wrapper_attributes() in non-block themes using the shortcode,
		// tell WordPress that a block is being rendered.
		// The attributes don't matter, as we send them to the render() function.
		if ( class_exists( 'WP_Block_Supports' ) && is_null( WP_Block_Supports::$block_to_render ) ) { // @phpstan-ignore-line
			WP_Block_Supports::$block_to_render = array(
				'blockName'    => 'convertkit/' . $this->get_name(),
				'attrs'        => array(),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);
		}

		$styles = array();

		// Get the block wrapper attributes string, extracting any styles that the block has set,
		// such as margin, padding or block spacing.
		$wrapper_attributes = get_block_wrapper_attributes();
		if ( preg_match( '/style="([^"]*)"/', $wrapper_attributes, $matches ) ) {
			return array_filter( explode( ';', $matches[1] ) );
		}

		// If here, no block styles were found.
		// This might be a shortcode or third party page builder module / widget that has
		// specific attributes set.
		if ( isset( $atts['text_color'] ) && ! empty( $atts['text_color'] ) ) {
			$styles[] = 'color:' . $atts['text_color'];
		}
		if ( isset( $atts['background_color'] ) && ! empty( $atts['background_color'] ) ) {
			$styles[] = 'background-color:' . $atts['background_color'];
		}

		return $styles;

	}

	/**
	 * Returns the given block / shortcode attributes array as HTML data-* attributes, which can be output
	 * in a block's container.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   array $atts   Block or shortcode attributes.
	 * @return  string        Block or shortcode attributes
	 */
	public function get_atts_as_html_data_attributes( $atts ) {

		// Define attributes provided by Gutenberg, which will be skipped, such as
		// styling.
		$skip_keys = array(
			'backgroundColor',
			'textColor',
			'_css_styles',
		);

		// Define a blank string to build the data-* attributes in.
		$data = '';

		foreach ( $atts as $key => $value ) {
			// Skip built in attributes provided by Gutenberg.
			if ( in_array( $key, $skip_keys, true ) ) {
				continue;
			}

			// Skip empty values.
			if ( empty( $value ) ) {
				continue;
			}

			// Append to data string, replacing underscores with hyphens in the key name.
			$data .= ' data-' . strtolower( str_replace( '_', '-', $key ) ) . '="' . esc_attr( $value ) . '"';
		}

		return trim( $data );

	}

	/**
	 * Determines if the request is a WordPress REST API request
	 * made by a logged in WordPress user who has the capability to edit posts.
	 *
	 * @since   3.1.0
	 *
	 * @return  bool
	 */
	public function is_admin_rest_request() {

		return defined( 'REST_REQUEST' ) && REST_REQUEST && current_user_can( 'edit_posts' );

	}

	/**
	 * Determines if the request is for the WordPress Administration, frontend editor or REST API request.
	 *
	 * @since   3.1.0
	 *
	 * @return  bool
	 */
	public function is_admin_frontend_editor_or_admin_rest_request() {

		return WP_ConvertKit()->is_admin_or_frontend_editor() || $this->is_admin_rest_request();

	}

	/**
	 * Determines if the request for the block is from the block editor or the frontend site.
	 *
	 * @since   1.9.8.5
	 *
	 * @return  bool
	 */
	public function is_block_editor_request() {

		// Return false if not a WordPress REST API request, which Gutenberg uses.
		if ( ! $this->is_admin_rest_request() ) {
			return false;
		}

		// Return false if the context parameter isn't edit.
		if ( ! filter_has_var( INPUT_GET, 'context' ) ) {
			return false;
		}
		if ( filter_input( INPUT_GET, 'context', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) !== 'edit' ) {
			return false;
		}

		// Request is for the block editor.
		return true;

	}

	/**
	 * If the Block Visiblity Plugin is active, run the block through its conditions now.
	 * We don't wait for Block Visibility to do this, as it performs this on the
	 * `render_block` filter, by which time the code in this method has fully executed,
	 * meaning any non-inline Forms will have had their scripts added to the
	 * `convertkit_output_scripts_footer` hook.
	 * As a result, the non-inline Form will always display, regardless of whether
	 * Block Visibility's conditions are met.
	 * We deliberately don't output non-inline Forms in their block, instead deferring
	 * to the `convertkit_output_scripts_footer` hook, to ensure the non-inline Forms
	 * styling are not constrained by the Theme's width, layout or other properties.
	 *
	 * @since   2.6.6
	 *
	 * @param   array $atts   Block Attributes.
	 * @return  bool            Display Block
	 */
	public function is_block_visible( $atts ) {

		// Display the block if the Block Visibility Plugin isn't active.
		if ( ! function_exists( '\BlockVisibility\Frontend\render_with_visibility' ) ) {
			return true;
		}

		// Determine whether the block should display.
		$display_block = \BlockVisibility\Frontend\render_with_visibility(
			'block',
			array(
				'blockName' => 'convertkit-' . $this->get_name(),
				'attrs'     => $atts,
			)
		);

		// If the content returned is a blank string, conditions on this block set
		// by the user in the Block Visibility Plugin resulted in the block not displaying.
		// Don't display it.
		if ( empty( $display_block ) ) {
			return false;
		}

		// If here, the block can be displayed.
		return true;

	}

	/**
	 * Returns the block's full Gutenberg name (e.g. `convertkit/form`).
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_full_block_name() {

		return 'convertkit/' . $this->get_name();

	}

	/**
	 * Returns JSON Schema properties derived from this block's get_attributes()
	 * and get_fields(), suitable for use as the `attrs` object in an Abilities
	 * API input schema.
	 *
	 * Structural/styling attributes injected by Gutenberg (align, style,
	 * backgroundColor, textColor, className, is_gutenberg_example) are excluded
	 * so the agent-facing schema only covers block-specific attributes.
	 *
	 * Where possible, the schema is enriched using get_fields(): the field's
	 * `label` becomes the property description, and `resource`-type fields
	 * become an enum of valid IDs drawn from the corresponding resource class.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_input_schema_properties() {

		$properties = array();
		$fields     = is_array( $this->get_fields() ) ? $this->get_fields() : array();

		// JSON Schema type for each Gutenberg attribute type.
		$type_map = array(
			'string'  => 'string',
			'number'  => 'integer',
			'boolean' => 'boolean',
			'object'  => 'object',
			'array'   => 'array',
		);

		// Attributes that are either provided by Gutenberg's own block supports
		// or are internal-only. These should not appear in the agent-facing schema.
		$skip_attrs = array(
			'align',
			'style',
			'backgroundColor',
			'textColor',
			'className',
			'is_gutenberg_example',
		);

		foreach ( $this->get_attributes() as $name => $definition ) {
			if ( in_array( $name, $skip_attrs, true ) ) {
				continue;
			}

			$type                = isset( $definition['type'] ) ? $definition['type'] : 'string';
			$json_type           = isset( $type_map[ $type ] ) ? $type_map[ $type ] : 'string';
			$properties[ $name ] = array( 'type' => $json_type );

			// Enrich from the field definition, if one exists.
			if ( ! isset( $fields[ $name ] ) ) {
				continue;
			}

			$field = $fields[ $name ];

			if ( ! empty( $field['label'] ) ) {
				$properties[ $name ]['description'] = (string) $field['label'];
			}

			// For resource-type fields, narrow the schema to a concrete list of
			// valid IDs. This prevents agents from passing IDs that don't exist.
			if ( isset( $field['type'] ) && $field['type'] === 'resource' && ! empty( $field['values'] ) && is_array( $field['values'] ) ) {
				$ids = array_keys( $field['values'] );
				if ( ! empty( $ids ) ) {
					// The attribute is typed as string in Gutenberg, but IDs are
					// naturally integers. Preserve whatever the attribute's
					// declared type is, and just cast enum values to match.
					$properties[ $name ]['enum'] = array_map(
						function ( $id ) use ( $json_type ) {
							return $json_type === 'string' ? (string) $id : (int) $id;
						},
						$ids
					);
				}
			}
		}

		return $properties;

	}

	/**
	 * Finds all top-level occurrences of this block in the given post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int $post_id    Post ID.
	 * @return  array|WP_Error  Array of ['index' => int, 'attrs' => array] entries, or WP_Error if the post is missing.
	 */
	public function find_blocks_in_post( $post_id ) {

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		$blocks    = parse_blocks( $post->post_content );
		$full_name = $this->get_full_block_name();
		$found     = array();

		foreach ( $blocks as $index => $block ) {
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $full_name ) {
				continue;
			}

			$found[] = array(
				'index' => (int) $index,
				'attrs' => isset( $block['attrs'] ) ? (array) $block['attrs'] : array(),
			);
		}

		return $found;

	}

	/**
	 * Inserts this block into the given post's content at the specified position.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id    Post ID.
	 * @param   array  $attrs      Block attributes to set on the inserted block.
	 * @param   string $position   One of 'append', 'prepend', 'at_index'.
	 * @param   int    $index      Zero-based index when $position is 'at_index'.
	 * @return  array|WP_Error     ['block_count' => int, 'position_used' => string] on success.
	 */
	public function insert_into_post( $post_id, $attrs, $position = 'append', $index = 0 ) {

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		$blocks = parse_blocks( $post->post_content );

		$new_block = array(
			'blockName'    => $this->get_full_block_name(),
			'attrs'        => (array) $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);

		switch ( $position ) {
			case 'prepend':
				array_unshift( $blocks, $new_block );
				break;

			case 'at_index':
				$index = max( 0, min( (int) $index, count( $blocks ) ) );
				array_splice( $blocks, $index, 0, array( $new_block ) );
				break;

			case 'append':
			default:
				$blocks[] = $new_block;
				$position = 'append';
				break;
		}

		$updated = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		return array(
			'block_count'   => count( $blocks ),
			'position_used' => $position,
		);

	}

	/**
	 * Replaces the attributes of a specific top-level occurrence of this block
	 * in the given post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int   $post_id        Post ID.
	 * @param   int   $target_index   Zero-based index among this block's occurrences in the post (not the block-array index).
	 * @param   array $attrs          New attributes to apply.
	 * @param   bool  $merge          If true, merge $attrs into the existing block attrs. If false, replace entirely.
	 * @return  array|WP_Error
	 */
	public function replace_in_post( $post_id, $target_index, $attrs, $merge = true ) {

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		$blocks      = parse_blocks( $post->post_content );
		$full_name   = $this->get_full_block_name();
		$occurrence  = 0;
		$matched     = false;
		$final_attrs = array();

		foreach ( $blocks as $key => $block ) {
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $full_name ) {
				continue;
			}

			if ( $occurrence === (int) $target_index ) {
				$existing                = isset( $block['attrs'] ) ? (array) $block['attrs'] : array();
				$final_attrs             = $merge ? array_merge( $existing, (array) $attrs ) : (array) $attrs;
				$blocks[ $key ]['attrs'] = $final_attrs;
				$matched                 = true;
				break;
			}

			++$occurrence;
		}

		if ( ! $matched ) {
			return new WP_Error(
				'convertkit_block_occurrence_not_found',
				/* translators: 1: block name, 2: target index, 3: post ID */
				sprintf( __( 'No occurrence #%2$d of block %1$s found in post %3$d.', 'convertkit' ), $full_name, (int) $target_index, $post_id )
			);
		}

		$updated = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		return array(
			'attrs' => $final_attrs,
		);

	}

	/**
	 * Deletes a specific top-level occurrence of this block from the given
	 * post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int $post_id       Post ID.
	 * @param   int $target_index  Zero-based index among this block's occurrences in the post.
	 * @return  array|WP_Error
	 */
	public function delete_from_post( $post_id, $target_index ) {

		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		$blocks     = parse_blocks( $post->post_content );
		$full_name  = $this->get_full_block_name();
		$occurrence = 0;
		$matched    = false;

		foreach ( $blocks as $key => $block ) {
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $full_name ) {
				continue;
			}

			if ( $occurrence === (int) $target_index ) {
				unset( $blocks[ $key ] );
				$blocks  = array_values( $blocks );
				$matched = true;
				break;
			}

			++$occurrence;
		}

		if ( ! $matched ) {
			return new WP_Error(
				'convertkit_block_occurrence_not_found',
				/* translators: 1: block name, 2: target index, 3: post ID */
				sprintf( __( 'No occurrence #%2$d of block %1$s found in post %3$d.', 'convertkit' ), $full_name, (int) $target_index, $post_id )
			);
		}

		$updated = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		return array(
			'block_count' => count( $blocks ),
		);

	}

}
