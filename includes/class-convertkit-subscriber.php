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
			$this->set( $subscriber_id );
			return $subscriber_id;
		}

		// If the subscriber ID is in a cookie, return it.
		if ( isset( $_COOKIE[ $this->key ] ) && ! empty( $_COOKIE[ $this->key ] ) ) {
			return $this->get_subscriber_id_from_cookie();
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

		setcookie( $this->key, (string) $subscriber_id, time() + ( 365 * DAY_IN_SECONDS ), '/' );
		setcookie( 'wordpress_' . $this->key, (string) $subscriber_id, time() + ( 365 * DAY_IN_SECONDS ), '/' );

	}

	/**
	 * Deletes the `ck_subscriber_id` cookie.
	 *
	 * @since   2.0.0
	 */
	public function forget() {

		setcookie( $this->key, '', time() - ( 365 * DAY_IN_SECONDS ), '/' );
		setcookie( 'wordpress_' . $this->key, '', time() - ( 365 * DAY_IN_SECONDS ), '/' );

	}

}
