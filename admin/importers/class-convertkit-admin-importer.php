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
	 * Holds the shortcode name for the third party Form plugin.
	 *
	 * @since   3.1.0
	 *
	 * @var     string
	 */
	public $shortcode_name = '';

	/**
	 * Holds the ID attribute name for the third party Form plugin.
	 *
	 * @since   3.1.0
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = '';

	/**
	 * Returns an array of third party form IDs and titles.
	 *
	 * @since   3.1.0
	 *
	 * @return  array
	 */
	abstract public function get_forms();

	/**
	 * Returns an array of post IDs that contain the third party form shortcode.
	 *
	 * @since   3.1.0
	 *
	 * @return  array
	 */
	abstract public function get_forms_in_posts();

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
	 * @param   int $third_party_form_id    The ID of the third party form.
	 * @param   int $form_id                The ID of the Kit form.
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
	 * @param   string $content             Content containing third party Form Shortcodes.
	 * @param   int    $third_party_form_id    Third Party Form ID.
	 * @param   int    $form_id                Kit Form ID.

	 * @return  string
	 */
	public function replace_shortcodes_in_content( $content, $third_party_form_id, $form_id ) {

		$pattern = '/\['                                     // Start regex with an opening square bracket.
			. preg_quote( $this->shortcode_name, '/' )       // Match the shortcode name, escaping any regex special chars.
			. '[^\]]*?'                                      // Match any characters that are not a closing square bracket, non-greedy.
			. '\b' . preg_quote( $this->shortcode_id_attribute, '/' ) // Match the id attribute word boundary and escape as needed.
			. '\s*=\s*'                                      // Match optional whitespace around an equals sign.
			. '(?:"' . preg_quote( (string) $third_party_form_id, '/' ) . '"|' . preg_quote( (string) $third_party_form_id, '/' ) . ')' // Match the form ID, quoted or unquoted.
			. '[^\]]*?\]/i';                                 // Match any other characters (non-greedy) up to the closing square bracket, case-insensitive.

		return preg_replace(
			$pattern,
			'[convertkit_form id="' . $form_id . '"]',
			$content
		);

	}

}
