<?php
/**
 * ConvertKit AJAX class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers AJAX actions for the Plugin.
 *
 * @since   1.9.6
 */
class ConvertKit_AJAX {

	/**
	 * Constructor.
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		add_action( 'wp_ajax_nopriv_convertkit_store_subscriber_email_as_id_in_cookie', array( $this, 'store_subscriber_email_as_id_in_cookie' ) );
		add_action( 'wp_ajax_convertkit_store_subscriber_email_as_id_in_cookie', array( $this, 'store_subscriber_email_as_id_in_cookie' ) );

	}

	/**
	 * Stores the ConvertKit Subscriber Email's ID in a cookie.
	 *
	 * Typically performed when the user subscribes via a ConvertKit Form on the web site
	 * and the Plugin's JavaScript is not disabled, permitting convertkit.js to run.
	 *
	 * @since   1.9.6
	 */
	public function store_subscriber_email_as_id_in_cookie() {

		// Check nonce.
		check_ajax_referer( 'convertkit', 'convertkit_nonce' );

		// Bail if required request parameters not submitted.
		if ( ! isset( $_REQUEST['email'] ) ) {
			wp_send_json_error( __( 'Kit: Required parameter `email` not included in AJAX request.', 'convertkit' ) );
		}
		$email = sanitize_text_field( wp_unslash( $_REQUEST['email'] ) );

		// Bail if the email address is empty.
		if ( empty( $email ) ) {
			wp_send_json_error( __( 'Kit: Required parameter `email` is empty.', 'convertkit' ) );
		}

		// Bail if the email address isn't a valid email address.
		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			wp_send_json_error( __( 'Kit: Required parameter `email` is not an email address.', 'convertkit' ) );
		}

		// Get subscriber ID.
		$subscriber    = new ConvertKit_Subscriber();
		$subscriber_id = $subscriber->validate_and_store_subscriber_email( $email );

		// Bail if an error occured i.e. API hasn't been configured, subscriber ID does not exist in ConvertKit etc.
		if ( is_wp_error( $subscriber_id ) ) {
			wp_send_json_error( $subscriber_id->get_error_message() );
		}

		// Return the subscriber ID.
		wp_send_json_success(
			array(
				'id' => $subscriber_id,
			)
		);

	}

}
