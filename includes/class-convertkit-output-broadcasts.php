<?php
/**
 * ConvertKit Output Broadcasts class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Applies a body CSS class to Posts, Pages and Custom Posts
 * that were imported from Kit's Broadcasts, to allow
 * CSS to target common themes that may override some
 * broadcast styling.
 *
 * @since   2.7.4
 */
class ConvertKit_Output_Broadcasts {

	/**
	 * Constructor.
	 *
	 * @since   2.7.4
	 */
	public function __construct() {

		add_filter( 'body_class', array( $this, 'maybe_add_body_class' ) );

	}

	/**
	 * Adds a .convertkit-broadcast CSS class to Posts, Pages and Custom Posts
	 * <body> tag where the content was imported from a Kit Broadcast.
	 *
	 * @since   2.7.4
	 *
	 * @param   array $css_classes    CSS classes for body tag.
	 * @return  array
	 */
	public function maybe_add_body_class( $css_classes ) {

		// Don't add a CSS class if the request isn't for an imported Broadcast.
		if ( ! is_singular() ) {
			return $css_classes;
		}

		if ( empty( get_post_meta( get_the_ID(), '_convertkit_broadcast_id', true ) ) ) {
			return $css_classes;
		}

		$css_classes[] = 'convertkit-broadcast';
		return $css_classes;

	}

}
