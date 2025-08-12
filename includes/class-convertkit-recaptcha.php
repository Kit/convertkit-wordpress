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

    public function enqueue_scripts() {

        // Setup classes.
        $this->settings = new ConvertKit_Settings();

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

    public function verify_recaptcha( $recaptcha_response, $plugin_action ) {

        // Setup classes.
        $this->settings = new ConvertKit_Settings();

        // Don't run if the reCAPTCHA or scripts are disabled.
        if ( ! $this->settings->has_recaptcha_site_and_secret_keys() || $this->settings->scripts_disabled() ) {
            return;
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

        // Bail if an error occured.
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

        // If the action doesn't match the Plugin action, this might not be a reCAPTCHA request
        // for this request.
        if ( $body['action'] !== $plugin_action ) {
            // Just silently return.
            return;
        }

        // If the score is less than the required minimum score, it's likely a spam submission.
        if ( $body['score'] < $this->settings->recaptcha_minimum_score() ) {
            return new WP_Error(
                'convertkit_recaptcha_failed',
                __( 'Google reCAPTCHA failed', 'convertkit' )
            );
            return;
        }

        // If here, the submission looks genuine. Continue the request.
        return true;

    }

}