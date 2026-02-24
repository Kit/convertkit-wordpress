<?php
/**
 * ConvertKit Admin Landing Page class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Modifies the Pages WP_List_Table to provide:
 * - an 'Add New Landing Page' button next to the 'Add New' button
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Landing_Page {

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   2.5.5
	 */
	public function __construct() {

		// Add New Landing Page Wizard button to Pages.
		add_filter( 'convertkit_admin_post_register_add_new_buttons', array( $this, 'register_add_new_button' ), 10, 2 );

	}

	/**
	 * Registers a button in the Pages WP_List_Table linking to the the Landing Page Setup Wizard.
	 *
	 * @since   2.5.5
	 *
	 * @param   array  $buttons    Buttons.
	 * @param   string $post_type  Post Type.
	 * @return  array               Views
	 */
	public function register_add_new_button( $buttons, $post_type ) {

		// If no API credentials have been set, don't output the button.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			return $buttons;
		}

		// If the Add New Landing Page / Member Content button is disabled, don't output the button.
		if ( $settings->add_new_disabled() ) {
			return $buttons;
		}

		// Bail if the Post Type isn't supported.
		if ( $post_type !== 'page' ) {
			return $buttons;
		}

		// Register button.
		$buttons['convertkit_landing_page_setup'] = array(
			'url'   => add_query_arg(
				array(
					'page'         => 'convertkit-landing-page-setup',
					'ck_post_type' => $post_type,
				),
				admin_url( 'options.php' )
			),
			'label' => __( 'Landing Page', 'convertkit' ),
		);

		return $buttons;

	}

}
