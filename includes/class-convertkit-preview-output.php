<?php
/**
 * ConvertKit Preview Output class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Handles:
 * - previewing a supplied Form ID in the URL when viewing a Page or Post,
 * when the user clicks a preview link in the Plugin Setup Wizard,
 * - appending an 'Edit form in ConvertKit' link when previewing a Page or Post
 * as a user who can edit the Page.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Preview_Output {

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.9.8.5
	 */
	public function __construct() {

		// Preview inline forms.
		add_filter( 'convertkit_output_append_form_to_content_form_id', array( $this, 'preview_form' ), 99999 );

		// Preview non-inline forms.
		add_filter( 'template_redirect', array( $this, 'preview_non_inline_form' ) );

		// Append edit link to inline forms.
		add_filter( 'convertkit_block_form_render', array( $this, 'maybe_append_edit_form_link_to_form_block' ), 10, 3 );
		add_filter( 'convertkit_frontend_append_form', array( $this, 'maybe_append_edit_form_link_to_form' ), 10, 4 );

	}

	/**
	 * Changes the form to display for the given Post ID if the request is
	 * from a logged in user who has clicked a preview link in the Plugin Setup Wizard.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   int $form_id    ConvertKit Form ID.
	 * @return  int                 ConvertKit Form ID
	 */
	public function preview_form( $form_id ) {

		// Bail if the user isn't logged in.
		if ( ! is_user_logged_in() ) {
			return $form_id;
		}

		// Bail if no nonce field exists.
		if ( ! isset( $_REQUEST['convertkit-preview-form-nonce'] ) ) {
			return $form_id;
		}

		// Bail if the nonce verification fails.
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['convertkit-preview-form-nonce'] ) ), 'convertkit-preview-form' ) ) {
			return $form_id;
		}

		// Determine the form to preview.
		$preview_form_id = ( isset( $_REQUEST['convertkit_form_id'] ) ? absint( wp_unslash( $_REQUEST['convertkit_form_id'] ) ) : 0 );

		// Return.
		return $preview_form_id;

	}

	/**
	 * Adds non-inline form(s) for display if the request is from a logged in user who has clicked a preview link
	 * and is viewing the home page.
	 *
	 * @since   2.3.3
	 */
	public function preview_non_inline_form() {

		// Bail if not on the home page.
		if ( ! is_home() ) {
			return;
		}

		// Bail if the user isn't logged in.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Bail if no nonce field exists.
		if ( ! isset( $_REQUEST['convertkit-preview-form-nonce'] ) ) {
			return;
		}

		// Bail if the nonce verification fails.
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['convertkit-preview-form-nonce'] ) ), 'convertkit-preview-form' ) ) {
			return;
		}

		// Bail if no form(s) to preview.
		if ( ! isset( $_REQUEST['convertkit_form_id'] ) ) {
			return;
		}

		// Determine form ID(s) to preview.
		$convertkit_forms = new ConvertKit_Resource_Forms();
		$preview_form_ids = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['convertkit_form_id'] ) ) );

		foreach ( $preview_form_ids as $preview_form_id ) {
			// Get form.
			$form = $convertkit_forms->get_by_id( (int) $preview_form_id );

			// Bail if the Form doesn't exist (this shouldn't happen, but you never know).
			if ( ! $form ) {
				continue;
			}

			// Add the form to the scripts array so it is included in the preview.
			add_filter(
				'convertkit_output_scripts_footer',
				function ( $scripts ) use ( $form ) {

					$scripts[] = array(
						'async'    => true,
						'data-uid' => $form['uid'],
						'src'      => $form['embed_js'],
					);

					return $scripts;

				}
			);
		}

	}

	/**
	 * Returns the URL for the most recent published Post based on the supplied Post Type,
	 * with a preview form nonce included in the URL.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   string $post_type  Post Type.
	 * @return  bool|string         false | URL
	 */
	public function get_preview_form_url( $post_type = 'post' ) {

		// Get most recently published Post/Page ID.
		$post_id = $this->get_most_recent( $post_type );

		// Bail if no Post/Page exists.
		if ( ! $post_id ) {
			return false;
		}

		// Return preview URL.
		return add_query_arg(
			array(
				'convertkit-preview-form-nonce' => $this->get_preview_form_nonce(),
			),
			get_permalink( $post_id )
		);

	}

	/**
	 * Returns the URL for the home page, with a preview form nonce included in the URL.
	 *
	 * @since   2.3.3
	 *
	 * @return  string
	 */
	public function get_preview_form_home_url() {

		// Return preview URL.
		return add_query_arg(
			array(
				'convertkit-preview-form-nonce' => $this->get_preview_form_nonce(),
			),
			get_home_url()
		);

	}

	/**
	 * Appends an "Edit form in ConvertKit" link to the given ConvertKit Form block,
	 * if the request is to preview a Page and the logged in WordPress user can
	 * edit the Page.
	 *
	 * @since   2.0.8
	 *
	 * @param   string $form_html  ConvertKit Form HTML.
	 * @param   array  $atts       ConvertKit Form block attributes.
	 * @param   int    $form_id    ConvertKit Form ID.
	 * @return  string
	 */
	public function maybe_append_edit_form_link_to_form_block( $form_html, $atts, $form_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return $this->maybe_append_edit_form_link( $form_html, $form_id );

	}

	/**
	 * Appends an "Edit form in ConvertKit" link to the ConvertKit Form defined in
	 * the Page's settings, if the request is to preview a Page and the
	 * logged in WordPress user can edit the Page.
	 *
	 * @since   2.0.8
	 *
	 * @param   string $content    Post Content, including ConvertKit Form HTML.
	 * @param   string $form_html  ConvertKit Form HTML.
	 * @param   int    $post_id    Post ID.
	 * @param   int    $form_id    ConvertKit Form ID.
	 */
	public function maybe_append_edit_form_link_to_form( $content, $form_html, $post_id, $form_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return $this->maybe_append_edit_form_link( $content, $form_id );

	}

	/**
	 * Appends an "Edit form in ConvertKit" link to the given ConvertKit Form HTML,
	 * if the request is to preview a Page and the logged in WordPress user can
	 * edit the Page.
	 *
	 * @since   2.0.8
	 *
	 * @param   string $form_html  ConvertKit Form HTML.
	 * @param   int    $form_id    ConvertKit Form ID.
	 * @return  string
	 */
	private function maybe_append_edit_form_link( $form_html, $form_id ) {

		// Bail if the user isn't logged in.
		if ( ! is_user_logged_in() ) {
			return $form_html;
		}

		// Bail if the request isn't to preview a Page.
		if ( ! is_preview() ) {
			return $form_html;
		}

		// Bail if the user does not have the WordPress capabilities to edit the Page / Post.
		if ( ! current_user_can( 'edit_post', get_the_ID() ) ) {
			return $form_html;
		}

		// Fetch Form.
		$convertkit_forms = new ConvertKit_Resource_Forms();
		$form             = $convertkit_forms->get_by_id( (int) $form_id );

		// Bail if the Form doesn't exist (this shouldn't happen, but you never know).
		if ( ! $form ) {
			return $form_html;
		}

		// Bail if the Form's format isn't an inline form - we don't want to show an edit link for
		// e.g. a sticky bar form.
		// Legacy Forms won't have a format array key.
		if ( array_key_exists( 'format', $form ) && $form['format'] !== 'inline' ) {
			return $form_html;
		}

		// If no format array key is specified, this is a Legacy Form, which has a different edit URL on ConvertKit.
		if ( ! array_key_exists( 'format', $form ) ) {
			// Legacy Form.
			$link = add_query_arg(
				array(
					'utm_source'  => 'wordpress',
					'utm_term'    => get_locale(),
					'utm_content' => 'convertkit',
				),
				sprintf(
					'https://app.kit.com/landing_pages/%s/edit/',
					esc_attr( (string) $form_id )
				)
			);
		} else {
			$link = add_query_arg(
				array(
					'utm_source'  => 'wordpress',
					'utm_term'    => get_locale(),
					'utm_content' => 'convertkit',
				),
				sprintf(
					'https://app.kit.com/forms/designers/%s/edit/',
					esc_attr( (string) $form_id )
				)
			);
		}

		// Append a link to edit the Form on ConvertKit.
		$form_html .= sprintf(
			'<div style="margin:0;padding:5px;text-align:right;font-size:13px;"><a href="%s" target="_blank">%s</a></div>',
			esc_url( $link ),
			esc_html__( 'Edit form in Kit', 'convertkit' )
		);

		return $form_html;

	}

	/**
	 * Returns the nonce to preview a form on a Page, Post or Custom Post Type.
	 *
	 * @since   1.9.8.5
	 *
	 * @return  string  Nonce
	 */
	private function get_preview_form_nonce() {

		return wp_create_nonce( 'convertkit-preview-form' );

	}

	/**
	 * Returns the most recent published Post ID for the given Post Type.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   string $post_type  Post Type.
	 * @return  false|int           Post ID
	 */
	private function get_most_recent( $post_type = 'post' ) {

		// Run query.
		$query = new WP_Query(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);

		// Return false if no Posts exist for the given type.
		if ( empty( $query->posts ) ) {
			return false;
		}

		// Return the Post ID.
		return $query->posts[0];

	}

}
