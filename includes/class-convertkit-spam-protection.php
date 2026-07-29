<?php
/**
 * ConvertKit Spam Protection helper.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Returns the currently active spam protection provider (reCAPTCHA or Cloudflare
 * Turnstile) based on the Plugin settings, and exposes the corresponding POST
 * field name used to carry the challenge response.
 *
 * The active provider is returned only if it has been configured with the
 * required keys; otherwise `null` is returned so callers can safely treat spam
 * protection as disabled without provider-specific branching.
 *
 * @since   3.3.7
 */
class ConvertKit_Spam_Protection {

	/**
	 * Holds the settings class.
	 *
	 * @since   3.3.7
	 *
	 * @var     bool|ConvertKit_Settings
	 */
	private $settings = false;

	/**
	 * Constructor.
	 *
	 * @since   3.3.7
	 */
	public function __construct() {

		$this->settings = new ConvertKit_Settings();

	}

	/**
	 * Returns the configured spam protection provider instance, or false if the
	 * selected provider is missing its site and secret keys (in which case the
	 * caller should behave as if spam protection is disabled).
	 *
	 * @since   3.3.7
	 *
	 * @return  ConvertKit_Recaptcha|ConvertKit_Cloudflare_Turnstile|bool
	 */
	public function get_active_provider() {

		switch ( $this->settings->spam_protection_provider() ) {
			case 'cloudflare_turnstile':
				if ( ! $this->settings->has_cloudflare_turnstile_site_and_secret_keys() ) {
					return false;
				}

				return new ConvertKit_Cloudflare_Turnstile();

			case 'recaptcha':
			default:
				if ( ! $this->settings->has_recaptcha_site_and_secret_keys() ) {
					return false;
				}

				return new ConvertKit_Recaptcha();
		}

	}

	/**
	 * Returns the POST field name the active provider uses for its challenge
	 * response.
	 *
	 * @since   3.3.7
	 *
	 * @return  string
	 */
	public function response_field_name() {

		switch ( $this->settings->spam_protection_provider() ) {
			case 'cloudflare_turnstile':
				return 'cf-turnstile-response';

			case 'recaptcha':
			default:
				return 'g-recaptcha-response';
		}

	}

	/**
	 * Reads and sanitizes the challenge response from $_POST for the active
	 * provider. Returns an empty string if the field is absent.
	 *
	 * @since   3.3.7
	 *
	 * @return  string
	 */
	public function get_response_from_post() {

		$field = $this->response_field_name();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST[ $field ] ) ) {
			return '';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		return sanitize_text_field( wp_unslash( $_POST[ $field ] ) );

	}

	/**
	 * Verifies the challenge response for the active provider.
	 *
	 * @since   3.3.7
	 *
	 * @param   string $plugin_action  Plugin action string.
	 * @return  bool|WP_Error
	 */
	public function verify( $plugin_action ) {

		$provider = $this->get_active_provider();

		// No provider configured: allow the request through.
		if ( ! $provider ) {
			return true;
		}

		$response = $this->get_response_from_post();

		return $provider->verify( $response, $plugin_action );

	}

}
