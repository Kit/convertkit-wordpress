<?php
/**
 * ConvertKit Impeka Theme Integration.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Adds compatibility with the Impeka theme by adding a CSS class
 * to the Restrict Content container if the Impeka theme is active.
 *
 * @since   3.1.4
 */
class ConvertKit_Impeka {

	/**
	 * Constructor.
	 *
	 * @since   3.1.4
	 */
	public function __construct() {

		add_filter( 'convertkit_output_restrict_content_container_css_classes', array( $this, 'maybe_add_restrict_content_container_css_classes' ) );

	}

	/**
	 * Adds a CSS class to the Restrict Content container if the Impeka theme is active.
	 *
	 * @since   3.1.4
	 *
	 * @param   array $css_classes    CSS classes for Restrict Content container.
	 * @return  array
	 */
	public function maybe_add_restrict_content_container_css_classes( $css_classes ) {

		// Don't add a CSS class if the Impeka theme is not active.
		if ( ! convertkit_is_theme_active( 'Impeka' ) ) {
			return $css_classes;
		}

		$css_classes[] = 'grve-container';
		return $css_classes;

	}

}

new ConvertKit_Impeka();
