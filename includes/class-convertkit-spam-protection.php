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
 * @since   3.4.0
 */
class ConvertKit_Spam_Protection {

	/**
	 * Returns the configured spam protection provider instance, or null if
	 * either no provider is selected or the selected provider is missing its
	 * site and secret keys.
	 *
	 * @since   3.4.0
	 *
	 * @return  ConvertKit_Recaptcha|ConvertKit_Cloudflare_Turnstile|null
	 */
	public static function get_active_provider() {

		$settings = new ConvertKit_Settings();

		switch ( $settings->spam_protection_provider() ) {
			case 'turnstile':
				if ( ! $settings->has_turnstile_site_and_secret_keys() ) {
					return null;
				}
				return new ConvertKit_Cloudflare_Turnstile();

			case 'recaptcha':
				if ( ! $settings->has_recaptcha_site_and_secret_keys() ) {
					return null;
				}
				return new ConvertKit_Recaptcha();

			case 'none':
			default:
				return null;
		}

	}

	/**
	 * Returns the POST field name the active provider uses for its challenge
	 * response, or an empty string if no provider is active.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public static function response_field_name() {

		$settings = new ConvertKit_Settings();

		switch ( $settings->spam_protection_provider() ) {
			case 'turnstile':
				return 'cf-turnstile-response';

			case 'recaptcha':
				return 'g-recaptcha-response';

			case 'none':
			default:
				return '';
		}

	}

	/**
	 * Reads and sanitizes the challenge response from $_POST for the active
	 * provider. Returns an empty string if no provider is active or the field
	 * is absent.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public static function get_response_from_post() {

		$field = self::response_field_name();

		if ( $field === '' ) {
			return '';
		}

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
	 * Returns true when spam protection is disabled or not configured, so
	 * existing sites without keys continue to work exactly as before. Returns
	 * a WP_Error when verification fails.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $plugin_action  The plugin action (used by reCAPTCHA's
	 *                                 action check; ignored by Turnstile).
	 * @return  bool|WP_Error
	 */
	public static function verify( $plugin_action ) {

		$provider = self::get_active_provider();

		// No provider configured: nothing to check, allow the request through
		// (matches the pre-3.4.0 behaviour when reCAPTCHA keys were absent).
		if ( $provider === null ) {
			return true;
		}

		$response = self::get_response_from_post();

		if ( $provider instanceof ConvertKit_Cloudflare_Turnstile ) {
			return $provider->verify( $response );
		}

		// reCAPTCHA verify method requires a plugin action string.
		return $provider->verify_recaptcha( $response, $plugin_action );

	}

}
