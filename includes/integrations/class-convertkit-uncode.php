<?php
/**
 * ConvertKit Uncode Theme Integration.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Adds compatibility with the Uncode theme by using
 * Uncode's specific hooks for outputting Forms and
 * Restrict Content functionality.
 *
 * @since   2.7.7
 */
class ConvertKit_Uncode {

	/**
	 * Constructor. Registers actions and filters to possibly limit output of a Page/Post/CPT's
	 * content on the frontend site.
	 *
	 * @since   2.7.7
	 */
	public function __construct() {

		add_action( 'convertkit_restrict_content_register_content_filter', array( $this, 'maybe_register_restrict_content_filter' ) );

	}

	/**
	 * Registers the content filter if the Uncode theme is active
	 * and the Page is using the Visual Composer Page Builder.
	 *
	 * @since   2.7.7
	 */
	public function maybe_register_restrict_content_filter() {

		// Don't register a different filter if the Uncode theme is not active.
		if ( ! function_exists( 'uncode_the_content' ) ) {
			return;
		}
		if ( ! function_exists( 'vc_is_page_editable' ) ) {
			return;
		}

		// Don't register a different filter if the Page is not using the Visual Composer Page Builder.
		$the_content = uncode_get_the_content(); // @phpstan-ignore-line
		if ( strpos( $the_content, '[vc_row' ) === false ) {
			return;
		}

		// If here, the Page is using the Visual Composer Page Builder, so we need to use a different content filter
		// to correctly restrict content.
		add_filter( 'uncode_single_content_final_output', array( WP_ConvertKit()->get_class( 'output_restrict_content' ), 'maybe_restrict_content' ) );
		remove_filter( 'the_content', array( WP_ConvertKit()->get_class( 'output_restrict_content' ), 'maybe_restrict_content' ) );

	}

}

new ConvertKit_Uncode();
