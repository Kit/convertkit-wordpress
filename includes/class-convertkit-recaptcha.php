<?php
/**
 * ConvertKit reCAPTCHA class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Handles reCAPTCHA verification.
 *
 * @since   3.0.0
 */
class ConvertKit_Recaptcha {

	/**
	 * Holds the settings class.
	 *
	 * @since   3.0.0
	 *
	 * @var     bool|ConvertKit_Settings
	 */
	private $settings = false;

	/**
	 * Constructor.
	 *
	 * @since   3.0.0
	 */
	public function __construct() {

		$this->settings = new ConvertKit_Settings();

	}

	/**
	 * Enqueues the reCAPTCHA scripts if reCAPTCHA site and secret keys are set,
	 * and scripts are enabled.
	 *
	 * @since   3.0.0
	 */
	public function enqueue_scripts() {

		// Don't run if the reCAPTCHA or scripts are disabled.
		if ( ! $this->settings->has_recaptcha_site_and_secret_keys() || $this->settings->scripts_disabled() ) {
			return;
		}

		// Enqueue Google reCAPTCHA JS.
		add_filter(
			'convertkit_output_scripts_footer',
			function ( $scripts ) {

				$scripts[] = array(
					'src' => 'https://www.google.com/recaptcha/api.js?',
				);

				return $scripts;

			}
		);

	}

	/**
	 * Verifies the reCAPTCHA response, if reCAPTCHA site and secret keys are set,
	 * and scripts are enabled.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $recaptcha_response  The reCAPTCHA response.
	 * @param   string $plugin_action       The action to verify the reCAPTCHA response for.
	 * @return  bool|WP_Error
	 */
	public function verify( $recaptcha_response, $plugin_action ) {

		// Don't run if the reCAPTCHA or scripts are disabled.
		if ( ! $this->settings->has_recaptcha_site_and_secret_keys() || $this->settings->scripts_disabled() ) {
			return true;
		}

		// Check if the submission is spam.
		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body' => array(
					'secret'   => $this->settings->recaptcha_secret_key(),
					'response' => $recaptcha_response,
					'remoteip' => ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ),
				),
			)
		);

		// Bail if an error occurred.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Inspect response.
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		// If the request wasn't successful, return an error.
		if ( ! $body['success'] ) {
			return new WP_Error(
				'convertkit_recaptcha_failed',
				sprintf(
					/* translators: Error codes */
					__( 'Google reCAPTCHA failure: %s', 'convertkit' ),
					implode( ', ', $body['error-codes'] )
				)
			);
		}

		// Return if the action doesn't match the Plugin action, this might not be a reCAPTCHA request
		// for this request.
		if ( $body['action'] !== $plugin_action ) {
			return true;
		}

		// If the score is less than the required minimum score, it's likely a spam submission.
		if ( $body['score'] < $this->settings->recaptcha_minimum_score() ) {
			return new WP_Error(
				'convertkit_recaptcha_failed',
				__( 'Google reCAPTCHA failed', 'convertkit' )
			);
		}

		// If here, the submission looks genuine. Continue the request.
		return true;

	}

	/**
	 * Attaches the reCAPTCHA v3 invisible-badge attributes to the given submit
	 * button element within an existing DOM tree, so that the challenge is
	 * executed when the button is clicked and the form is submitted via the
	 * `convertKitRecaptchaFormSubmit` callback.
	 *
	 * @since   3.3.7
	 *
	 * @param   ConvertKit_HTML_Parser $parser         Parser wrapping the DOM (unused).
	 * @param   DOMElement             $button         <button> element to attach attributes to.
	 * @param   string                 $plugin_action  Plugin action string.
	 */
	public function attach_to_form_button_dom( $parser, $button, $plugin_action ) {

		unset( $parser );

		$button->setAttribute( 'data-sitekey', esc_attr( $this->settings->recaptcha_site_key() ) );
		$button->setAttribute( 'data-callback', 'convertKitRecaptchaFormSubmit' );
		$button->setAttribute( 'data-action', $plugin_action );
		$button->setAttribute( 'class', trim( $button->getAttribute( 'class' ) . ' g-recaptcha' ) );

	}

	/**
	 * Returns the HTML for a submit button with reCAPTCHA v3 invisible-badge
	 * attributes attached, used by templates that don't have a DOM parser
	 * available (e.g. the Restrict Content tag view).
	 *
	 * @since   3.3.7
	 *
	 * @param   string   $label          Button's visible label.
	 * @param   string   $plugin_action  Plugin action string.
	 * @param   string[] $css_classes    CSS classes for the button.
	 * @return  string
	 */
	public function get_submit_button_html( $label, $plugin_action, $css_classes = array() ) {

		$css_classes[] = 'g-recaptcha';

		return sprintf(
			'<input type="submit" class="%1$s" value="%2$s" data-sitekey="%3$s" data-callback="convertKitRecaptchaFormSubmit" data-action="%4$s" />',
			esc_attr( implode( ' ', $css_classes ) ),
			esc_attr( $label ),
			esc_attr( $this->settings->recaptcha_site_key() ),
			esc_attr( $plugin_action )
		);

	}

}
