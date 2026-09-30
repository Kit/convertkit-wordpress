<?php
/**
 * ConvertKit Shortcodes class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers shortcodes defined in the `convertkit_shortcodes` filter as WordPress Shortcodes.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Shortcodes {

	/**
	 * Constructor
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ), 10, 1 );

	}

	/**
	 * Register ConvertKit shortcodes.
	 *
	 * @since   1.9.6
	 */
	public function init() {

		// Get shortcodes.
		$shortcodes = convertkit_get_shortcodes();

		// Bail if no shortcodes are available.
		if ( ! count( $shortcodes ) ) {
			return;
		}

		// Iterate through shortcodes, registering them as shortcodes.
		foreach ( $shortcodes as $shortcode => $properties ) {
			// Register shortcode.
			add_shortcode(
				'convertkit_' . $shortcode,
				array(
					$properties['render_callback'][0],
					$properties['render_callback'][1],
				)
			);

			// Register the shortcode using the kit_ prefix.
			add_shortcode(
				'kit_' . $shortcode,
				array(
					$properties['render_callback'][0],
					$properties['render_callback'][1],
				)
			);

			// For the Form shortcode, register the [convertkit] shortcode for backward compatibility.
			if ( $shortcode === 'form' ) {
				add_shortcode(
					'convertkit',
					array(
						$properties['render_callback'][0],
						$properties['render_callback'][1],
					)
				);

				// For the Form shortcode, register the [kit] shortcode.
				add_shortcode(
					'kit',
					array(
						$properties['render_callback'][0],
						$properties['render_callback'][1],
					)
				);
			}
		}

	}

}
