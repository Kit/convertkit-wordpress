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
 * @since   3.3.7
 */
class ConvertKit_Cloudflare_Turnstile {

	/**
	 * The endpoint used to validate Turnstile tokens server-side.
	 *
	 * @since   3.3.7
	 *
	 * @var     string
	 */
	const SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	/**
	 * The URL of the Turnstile client-side script.
	 *
	 * @since   3.3.7
	 *
	 * @var     string
	 */
	const CLIENT_SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';

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
	 * Enqueues the Cloudflare Turnstile client-side script if Cloudflare Turnstile
	 * site and secret keys are set and scripts are enabled.
	 *
	 * @since   3.3.7
	 */
	public function enqueue_scripts() {

		// Don't run if Cloudflare Turnstile or scripts are disabled.
		if ( ! $this->settings->has_cloudflare_turnstile_site_and_secret_keys() || $this->settings->scripts_disabled() ) {
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
	 * Verifies a Cloudflare Turnstile response token against the Siteverify API,
	 * if Cloudflare Turnstile site and secret keys are set, and scripts are enabled.
	 *
	 * Mirrors the request format documented at
	 * https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
	 *
	 * @since   3.3.7
	 *
	 * @param   string $cloudflare_turnstile_response  Cloudflare Turnstile response token from the client.
	 * @return  bool|WP_Error
	 */
	public function verify( $cloudflare_turnstile_response ) {

		// Don't run if Turnstile or scripts are disabled.
		if ( ! $this->settings->has_cloudflare_turnstile_site_and_secret_keys() || $this->settings->scripts_disabled() ) {
			return true;
		}

		// POST to Cloudflare Siteverify.
		$response = wp_remote_post(
			self::SITEVERIFY_URL,
			array(
				'body' => array(
					'secret'   => $this->settings->cloudflare_turnstile_secret_key(),
					'response' => $cloudflare_turnstile_response,
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
				'convertkit_cloudflare_turnstile_failed',
				__( 'Cloudflare Turnstile failure: invalid response from Siteverify.', 'convertkit' )
			);
		}

		// If the token verified, return true.
		if ( $body['success'] === true ) {
			return true;
		}

		// Return an error.
		return new WP_Error(
			'convertkit_cloudflare_turnstile_failed',
			sprintf(
				/* translators: Error codes */
				__( 'Cloudflare Turnstile failure: %s', 'convertkit' ),
				implode( ', ', $body['error-codes'] )
			)
		);

	}

	/**
	 * Inserts a Cloudflare Turnstile widget div immediately before the given
	 * submit button within an existing DOM tree. `data-appearance=interaction-only`
	 * keeps the widget invisible unless Cloudflare determines a challenge is
	 * required, and the `convertKitTurnstileFormSubmit` callback submits the
	 * enclosing form once the challenge is solved.
	 *
	 * @since   3.3.7
	 *
	 * @param   ConvertKit_HTML_Parser $parser         Parser wrapping the DOM.
	 * @param   DOMElement             $button         <button> element.
	 * @param   string                 $plugin_action  Plugin action string (unused).
	 */
	public function attach_to_form_button_dom( $parser, $button, $plugin_action ) {

		unset( $plugin_action );

		$widget = $parser->html->createElement( 'div' );
		$widget->setAttribute( 'class', 'cf-turnstile' );
		$widget->setAttribute( 'data-sitekey', esc_attr( $this->settings->cloudflare_turnstile_site_key() ) );
		$widget->setAttribute( 'data-appearance', 'interaction-only' );
		$widget->setAttribute( 'data-callback', 'convertKitTurnstileFormSubmit' );
		$button->parentNode->insertBefore( $widget, $button ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	}

	/**
	 * Returns the HTML for a Cloudflare Turnstile widget div followed by a
	 * plain submit button, used by templates that don't have a DOM parser
	 * available (e.g. the Restrict Content tag view).
	 *
	 * @since   3.3.7
	 *
	 * @param   string   $label          The button's visible label.
	 * @param   string   $plugin_action  The plugin action string (unused).
	 * @param   string[] $css_classes    CSS classes for the button.
	 * @return  string
	 */
	public function get_submit_button_html( $label, $plugin_action, $css_classes = array() ) {

		unset( $plugin_action );

		return sprintf(
			'<div class="cf-turnstile" data-sitekey="%1$s" data-appearance="interaction-only" data-callback="convertKitTurnstileFormSubmit"></div><input type="submit" class="%2$s" value="%3$s" />',
			esc_attr( $this->settings->cloudflare_turnstile_site_key() ),
			esc_attr( implode( ' ', $css_classes ) ),
			esc_attr( $label )
		);

	}

}
