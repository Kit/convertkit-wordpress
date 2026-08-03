<?php
/**
 * ConvertKit Account class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Fetches and caches the connected Kit account (from `/account`) in the
 * WordPress options table, exposing accessors for the account's plan type.
 *
 * Refresh is deliberately not automatic — callers (the General settings screen
 * and the MCP settings screen) explicitly invoke `refresh()` so we hit Kit's
 * API only when a creator is actively in the settings UI.
 *
 * @since   3.4.0
 */
class ConvertKit_Account {

	/**
	 * The WordPress option that stores the cached account response.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const OPTION_NAME = 'convertkit_account';

	/**
	 * Returns the cached account response.
	 *
	 * @since   3.4.0
	 *
	 * @return  bool|array
	 */
	public function get() {

		$account = get_option( self::OPTION_NAME );

		if ( ! is_array( $account ) ) {
			return false;
		}

		return $account;

	}

	/**
	 * Returns the cached plan_type string.
	 *
	 * @since   3.4.0
	 *
	 * @return  string|null
	 */
	public function get_plan_type() {

		$account = $this->get();

		if ( ! $account || ! isset( $account['account']['plan_type'] ) ) {
			return null;
		}

		return (string) $account['account']['plan_type'];

	}

	/**
	 * Returns whether the cached plan is a paid Kit plan.
	 *
	 * @since   3.4.0
	 *
	 * @return  bool
	 */
	public function is_paid_plan() {

		$plan_type = $this->get_plan_type();

		if ( $plan_type === null ) {
			return false;
		}

		return $plan_type !== 'free';

	}

	/**
	 * Fetches the account from Kit and caches the response.
	 *
	 * @since   3.4.0
	 *
	 * @return  WP_Error|array
	 */
	public function refresh() {

		// Initialize the settings object.
		$settings = new ConvertKit_Settings();

		// Bail if we don't have access and refresh tokens.
		if ( ! $settings->has_access_and_refresh_token() ) {
			return new WP_Error(
				'convertkit_account_no_credentials',
				__( 'Cannot refresh account: no OAuth credentials configured.', 'convertkit' )
			);
		}

		// Initialize the API client.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$settings->get_access_token(),
			$settings->get_refresh_token(),
			$settings->debug_enabled(),
			'account'
		);

		// Fetch the account.
		$account = $api->get_account();

		// Bail if there was an error.
		if ( is_wp_error( $account ) ) {
			return $account;
		}

		// Update the cached account.
		update_option( self::OPTION_NAME, $account );

		return $account;

	}

	/**
	 * Deletes the cached account.
	 *
	 * @since   3.4.0
	 *
	 * @return  bool
	 */
	public function delete() {

		return delete_option( self::OPTION_NAME );

	}

}
