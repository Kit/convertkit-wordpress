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

		// Build CSS class(es) that might need to be added to the top level element for this block.
		$atts['_css_classes'] = array( 'convertkit-' . $this->get_name() );
		$atts['_css_styles']  = array();

		// If the block supports a text color, and a preset color was selected, add it to the
		// array of CSS classes.
		if ( $atts['textColor'] ) {
			$atts['_css_classes'][] = 'has-text-color';
			$atts['_css_classes'][] = 'has-' . $atts['textColor'] . '-color';
		}

		// If the block supports a text color, and a custom hex color was selected, add it to the
		// array of CSS inline styles.
		if ( isset( $atts['style']['color'] ) && isset( $atts['style']['color']['text'] ) ) {
			$atts['_css_classes'][]       = 'has-text-color';
			$atts['_css_styles']['color'] = 'color:' . $atts['style']['color']['text'];
		}

		// If the shortcode supports a text color, and a custom hex color was selected, add it to the
		// array of CSS inline styles.
		if ( isset( $atts['text_color'] ) && ! empty( $atts['text_color'] ) ) {
			$atts['_css_classes'][]       = 'has-text-color';
			$atts['_css_styles']['color'] = 'color:' . $atts['text_color'];
		}

		// If the block supports a background color, and a preset color was selected, add it to the
		// array of CSS classes.
		if ( $atts['backgroundColor'] ) {
			$atts['_css_classes'][] = 'has-background';
			$atts['_css_classes'][] = 'has-' . $atts['backgroundColor'] . '-background-color';
		}

		// If the block supports a background color, and a custom hex color was selected, add it to the
		// array of CSS inline styles.
		if ( isset( $atts['style']['color'] ) && isset( $atts['style']['color']['background'] ) ) {
			$atts['_css_classes'][]            = 'has-background';
			$atts['_css_styles']['background'] = 'background-color:' . $atts['style']['color']['background'];
		}

		// If the block supports a font size, and a preset font size was selected, add it to the
		// array of CSS classes.
		if ( isset( $atts['fontSize'] ) && ! empty( $atts['fontSize'] ) ) {
			$atts['_css_classes'][] = 'has-custom-font-size';
			$atts['_css_classes'][] = 'has-' . $atts['fontSize'] . '-font-size';
		}

		// If the block supports padding, and padding is set, add it to the
		// array of CSS inline styles.
		if ( isset( $atts['style']['spacing'] ) && isset( $atts['style']['spacing']['padding'] ) ) {
			foreach ( $atts['style']['spacing']['padding'] as $position => $value ) {
				$atts['_css_styles'][ 'padding-' . $position ] = 'padding-' . $position . ':' . $value;
			}
		}

		// If the shortcode supports a background color, and a custom hex color was selected, add it to the
		// array of CSS inline styles.
		if ( isset( $atts['background_color'] ) && ! empty( $atts['background_color'] ) ) {
			$atts['_css_classes'][]            = 'has-background';
			$atts['_css_styles']['background'] = 'background-color:' . $atts['background_color'];
		}

		// Remove some unused attributes, now they're declared above.
		unset( $atts['style'] );

		return $atts;

	}

	/**
	 * Removes any HTML that might be wrongly included in the shorcode attribute's values
	 * due to e.g. copy and pasting from Documentation or other examples.
	 *
	 * @since   1.9.6
	 *
	 * @param   array $atts   Block or shortcode attributes.
	 * @return  array           Block or shortcode attributes
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
			'_css_classes',
			'_css_styles',
		);

		// Define a blank string to build the data-* attributes in.
		$data = '';

		foreach ( $atts as $key => $value ) {
			// Skip built in attributes provided by Gutenberg.
			if ( in_array( $key, $skip_keys, true ) ) {
				continue;
			}

			// Append to data string, replacing underscores with hyphens in the key name.
			$data .= ' data-' . strtolower( str_replace( '_', '-', $key ) ) . '="' . esc_attr( $value ) . '"';
		}

		return trim( $data );

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
		if ( ! defined( 'REST_REQUEST' ) ) {
			return false;
		}
		if ( REST_REQUEST !== true ) {
			return false;
		}

		// Return false if the context parameter isn't edit.
		if ( ! array_key_exists( 'context', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}
		if ( sanitize_text_field( wp_unslash( $_GET['context'] ) ) !== 'edit' ) { // phpcs:ignore WordPress.Security.NonceVerification
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

}
