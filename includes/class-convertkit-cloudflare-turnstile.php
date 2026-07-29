<?php
/**
 * ConvertKit Cloudflare Turnstile class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Handles Cloudflare Turnstile verification.
 *
 * @since   3.4.0
 */
class ConvertKit_Cloudflare_Turnstile {

	/**
	 * The endpoint used to validate Turnstile tokens server-side.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	/**
	 * The URL of the Turnstile client-side script.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const CLIENT_SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

	/**
	 * Holds the settings class.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool|ConvertKit_Settings
	 */
	private $settings = false;

	/**
	 * Constructor.
	 *
	 * @since   3.4.0
	 */
	public function __construct() {

		$this->settings = new ConvertKit_Settings();

	}

	/**
	 * Enqueues the Turnstile client-side script if Turnstile site and secret keys are set,
	 * and scripts are enabled.
	 *
	 * @since   3.4.0
	 */
	public function enqueue_scripts() {

		// Don't run if Turnstile or scripts are disabled.
		if ( ! $this->settings->has_turnstile_site_and_secret_keys() || $this->settings->scripts_disabled() ) {
			return;
		}

		// Enqueue Cloudflare Turnstile JS.
		add_filter(
			'convertkit_output_scripts_footer',
			function ( $scripts ) {

				$scripts[] = array(
					'src'   => self::CLIENT_SCRIPT_URL,
					'async' => true,
					'defer' => true,
				);

				return $scripts;

			}
		);

	}

	/**
	 * Verifies a Turnstile response token against the Siteverify API, if Turnstile
	 * site and secret keys are set, and scripts are enabled.
	 *
	 * Mirrors the request format documented at
	 * https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
	 *
	 * @since   3.4.0
	 *
	 * @param   string $turnstile_response  The Turnstile response token from the client.
	 * @return  bool|WP_Error               True on success or when Turnstile is not
	 *                                      configured / disabled; WP_Error on failure.
	 */
	public function verify( $turnstile_response ) {

		// Don't run if Turnstile or scripts are disabled.
		if ( ! $this->settings->has_turnstile_site_and_secret_keys() || $this->settings->scripts_disabled() ) {
			return true;
		}

		// POST to Cloudflare Siteverify.
		$response = wp_remote_post(
			self::SITEVERIFY_URL,
			array(
				'body' => array(
					'secret'   => $this->settings->turnstile_secret_key(),
					'response' => $turnstile_response,
					'remoteip' => ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ),
				),
			)
		);

		// Bail if the request itself errored.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Decode response.
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		// If the response body couldn't be decoded, treat that as a failure.
		if ( ! is_array( $body ) ) {
			return new WP_Error(
				'convertkit_turnstile_failed',
				__( 'Cloudflare Turnstile failure: invalid response from Siteverify.', 'convertkit' )
			);
		}

		// Cloudflare returns { success: bool, "error-codes": [...] }.
		if ( empty( $body['success'] ) ) {
			$error_codes = ( isset( $body['error-codes'] ) && is_array( $body['error-codes'] ) )
				? $body['error-codes']
				: array( 'unknown-error' );

			return new WP_Error(
				'convertkit_turnstile_failed',
				sprintf(
					/* translators: Error codes */
					__( 'Cloudflare Turnstile failure: %s', 'convertkit' ),
					implode( ', ', $error_codes )
				)
			);
		}

		// Token verified.
		return true;

	}

}
