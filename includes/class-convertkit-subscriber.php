<?php
/**
 * ConvertKit Subscriber class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Class to confirm a ConvertKit Subscriber ID exists, writing/reading
 * it from cookie storage.
 *
 * @since   2.0.0
 */
class ConvertKit_Subscriber {

	/**
	 * Holds the key to check on requests and store as a cookie.
	 *
	 * @since   2.0.0
	 *
	 * @var     string
	 */
	private $key = 'ck_subscriber_id';

	/**
	 * Holds the key of the cookie storing the numeric subscriber ID's signature.
	 *
	 * @since   3.4.7
	 *
	 * @var     string
	 */
	private $signature_key = 'ck_subscriber_id_signature';

	/**
	 * Holds the results of verifying numeric subscriber IDs against their hashed email
	 * address in this request, to avoid repeat API calls.
	 *
	 * @since   3.4.7
	 *
	 * @var     array
	 */
	private static $verified = array();

	/**
	 * Gets the subscriber ID from either the request's `ck_subscriber_id` parameter,
	 * or the existing `ck_subscriber_id` cookie.
	 *
	 * @since   2.0.0
	 *
	 * @return  WP_Error|bool|int|string    Error | false | Subscriber ID | Signed Subscriber ID
	 */
	public function get_subscriber_id() {

		// If the subscriber ID is in the request URI, use it.
		if ( filter_has_var( INPUT_GET, $this->key ) ) {
			$subscriber_id = filter_input( INPUT_GET, $this->key, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			// Ignore a numeric subscriber ID that doesn't match the hashed email address in the request.
			if ( is_numeric( $subscriber_id ) && ! $this->verify_hashed_email( $subscriber_id, (string) filter_input( INPUT_GET, 'sh_kit', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
				return $this->get_unverified_subscriber_id( $subscriber_id );
			}

			$this->set( $subscriber_id );
			return $subscriber_id;
		}

		// If the subscriber ID is in a cookie, return it.
		if ( isset( $_COOKIE[ $this->key ] ) && ! empty( $_COOKIE[ $this->key ] ) ) {
			$subscriber_id = $this->get_subscriber_id_from_cookie();

			// Ignore a numeric subscriber ID that wasn't signed by this site.
			if ( is_numeric( $subscriber_id ) && ! $this->verify_signature( $subscriber_id ) ) {
				return $this->get_unverified_subscriber_id( $subscriber_id );
			}

			return $subscriber_id;
		}

		// If here, no subscriber ID exists.
		return false;

	}

	/**
	 * Gets the subscriber ID from the `ck_subscriber_id` cookie.
	 *
	 * @since   2.0.0
	 *
	 * @return  string
	 */
	private function get_subscriber_id_from_cookie() {

		if ( ! isset( $_COOKIE[ $this->key ] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_COOKIE[ $this->key ] ) );

	}

	/**
	 * Stores the given subscriber ID in the `ck_subscriber_id` cookie
	 * and a prefixed `wordpress_ck_subscriber_id` cookie.
	 *
	 * @since   2.0.0
	 *
	 * @param   int|string $subscriber_id  Subscriber ID.
	 */
	public function set( $subscriber_id ) {

		$this->set_cookie( $this->key, (string) $subscriber_id, time() + ( 365 * DAY_IN_SECONDS ) );
		$this->set_cookie( 'wordpress_' . $this->key, (string) $subscriber_id, time() + ( 365 * DAY_IN_SECONDS ) );

		// Sign numeric subscriber IDs, so they can't be changed in the cookie.
		$signature = ( is_numeric( $subscriber_id ) ? $this->get_signature( $subscriber_id ) : '' );
		$this->set_cookie( $this->signature_key, $signature, time() + ( 365 * DAY_IN_SECONDS ) );
		$_COOKIE[ $this->signature_key ] = $signature;

	}

	/**
	 * Deletes the `ck_subscriber_id` cookie.
	 *
	 * @since   2.0.0
	 */
	public function forget() {

		$this->set_cookie( $this->key, '', time() - ( 365 * DAY_IN_SECONDS ) );
		$this->set_cookie( 'wordpress_' . $this->key, '', time() - ( 365 * DAY_IN_SECONDS ) );
		$this->set_cookie( $this->signature_key, '', time() - ( 365 * DAY_IN_SECONDS ) );

	}

	/**
	 * Sets a cookie, using the Secure and SameSite attributes.
	 *
	 * @since   3.4.7
	 *
	 * @param   string $name       Cookie name.
	 * @param   string $value      Cookie value.
	 * @param   int    $expires    Cookie expiry timestamp.
	 */
	private function set_cookie( $name, $value, $expires ) {

		// PHP 7.3+ supports the SameSite attribute using the options array.
		if ( PHP_VERSION_ID >= 70300 ) {
			setcookie(
				$name,
				$value,
				array(
					'expires'  => $expires,
					'path'     => '/',
					'secure'   => is_ssl(),
					'samesite' => 'Lax',
				)
			);
			return;
		}

		// Older PHP versions require the SameSite attribute to be appended to the path.
		setcookie( $name, $value, $expires, '/; samesite=Lax', '', is_ssl() );

	}

	/**
	 * Determines if the given numeric subscriber ID's email address matches the hashed
	 * email address that Kit appends to email links as the `sh_kit` parameter.
	 *
	 * @since   3.4.7
	 *
	 * @param   string $subscriber_id  Subscriber ID.
	 * @param   string $hashed_email   SHA-256 hash of the subscriber's email address.
	 * @return  bool
	 */
	private function verify_hashed_email( $subscriber_id, $hashed_email ) {

		// Bail if no hashed email address was provided.
		if ( empty( $hashed_email ) ) {
			return false;
		}

		// Return the result of an earlier check in this request.
		$hashed_email = strtolower( $hashed_email );
		$cache_key    = absint( $subscriber_id ) . '_' . $hashed_email;
		if ( array_key_exists( $cache_key, self::$verified ) ) {
			return self::$verified[ $cache_key ];
		}

		self::$verified[ $cache_key ] = false;

		// Bail if the Plugin isn't connected to Kit.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			return false;
		}

		// Get the subscriber's email address.
		$api    = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$settings->get_access_token(),
			$settings->get_refresh_token(),
			$settings->debug_enabled(),
			'subscriber'
		);
		$result = $api->get_subscriber( absint( $subscriber_id ) );

		// Bail if the subscriber doesn't exist.
		if ( is_wp_error( $result ) || ! isset( $result['subscriber']['email_address'] ) ) {
			return false;
		}

		// Compare the hash of the email address as stored, and lowercased.
		$email_address = trim( $result['subscriber']['email_address'] );
		foreach ( array( $email_address, strtolower( $email_address ) ) as $email ) {
			if ( hash_equals( hash( 'sha256', $email ), $hashed_email ) ) {
				self::$verified[ $cache_key ] = true;
				break;
			}
		}

		return self::$verified[ $cache_key ];

	}

	/**
	 * Determines if the signature cookie matches the given numeric subscriber ID.
	 *
	 * @since   3.4.7
	 *
	 * @param   string $subscriber_id  Subscriber ID.
	 * @return  bool
	 */
	private function verify_signature( $subscriber_id ) {

		if ( ! isset( $_COOKIE[ $this->signature_key ] ) ) {
			return false;
		}

		return hash_equals( $this->get_signature( $subscriber_id ), sanitize_text_field( wp_unslash( $_COOKIE[ $this->signature_key ] ) ) );

	}

	/**
	 * Returns the signature for the given numeric subscriber ID, using this site's salt.
	 *
	 * @since   3.4.7
	 *
	 * @param   string $subscriber_id  Subscriber ID.
	 * @return  string
	 */
	private function get_signature( $subscriber_id ) {

		return hash_hmac( 'sha256', 'ck_subscriber_id_' . absint( $subscriber_id ), wp_salt() );

	}

	/**
	 * Returns the given numeric subscriber ID that couldn't be verified, if permitted
	 * by the `convertkit_subscriber_allow_unverified_id` filter.
	 *
	 * @since   3.4.7
	 *
	 * @param   string $subscriber_id  Subscriber ID.
	 * @return  bool|string
	 */
	private function get_unverified_subscriber_id( $subscriber_id ) {

		/**
		 * Whether to use a numeric subscriber ID that couldn't be verified, either from a request
		 * without a matching `sh_kit` parameter, or from a cookie without a valid signature.
		 *
		 * @since   3.4.7
		 *
		 * @param   bool    $allow          Allow unverified subscriber ID.
		 * @param   string  $subscriber_id  Subscriber ID.
		 */
		if ( ! apply_filters( 'convertkit_subscriber_allow_unverified_id', false, $subscriber_id ) ) {
			return false;
		}

		return $subscriber_id;

	}

}
