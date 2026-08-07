<?php
/**
 * ConvertKit Account class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Reads Kit Account data from the options table, and refreshes
 * Kit Account data from the API.
 *
 * @since   3.4.0
 */
class ConvertKit_Resource_Account extends ConvertKit_Resource_V4 {

	/**
	 * Holds the Settings Key that stores account data
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	public $settings_name = 'convertkit_account';

	/**
	 * The type of resource
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	public $type = 'account';

	/**
	 * Constructor.
	 *
	 * @since   1.9.8.4
	 *
	 * @param   bool|string $context    Context.
	 */
	public function __construct( $context = false ) {

		// Initialize the API if the Access Token has been defined in the Plugin Settings.
		$settings = new ConvertKit_Settings();
		if ( $settings->has_access_and_refresh_token() ) {
			$this->api = new ConvertKit_API_V4(
				CONVERTKIT_OAUTH_CLIENT_ID,
				CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
				$settings->get_access_token(),
				$settings->get_refresh_token(),
				$settings->debug_enabled(),
				$context
			);
		}

		// Get last query time and existing resources.
		$this->last_queried = get_option( $this->settings_name . '_last_queried' );
		$this->resources    = get_option( $this->settings_name );

	}

	/**
	 * Fetches the account data from the API, storing them in the options table
	 * with a last queried timestamp.
	 *
	 * If the refresh results in a 401, removes the access and refresh tokens from the settings.
	 *
	 * @since   3.4.0
	 *
	 * @return  WP_Error|array
	 */
	public function refresh() {

		// Query API for account details.
		$results = $this->api->get_account();

		// Define and store the last query time now.
		// This prevents multiple calls to refresh() when the above returns a 401 error.
		$this->last_queried = time();
		update_option( $this->settings_name . '_last_queried', $this->last_queried );

		// If an error occurred, maybe delete credentials from the Plugin's settings
		// if the error is a 401 unauthorized.
		if ( is_wp_error( $results ) ) {
			convertkit_maybe_delete_credentials( $results, CONVERTKIT_OAUTH_CLIENT_ID );
			return $results;
		}

		// Store resources in the options table.
		// We don't use WordPress' Transients API (i.e. auto expiring options), because they're prone to being
		// flushed by some third party "optimization" Plugins. They're also not guaranteed to remain in the options
		// table for the amount of time specified; any expiry is a maximum, not a minimum.
		// We don't want to keep querying the ConvertKit API for a list of e.g. forms, tags that rarely change as
		// a result of transients not being honored, so storing them as options with a separate, persistent expiry
		// value is more reliable here.
		update_option( $this->settings_name, $results );

		// Store resources in class variable.
		$this->resources = $results;

		/**
		 * Perform any actions immediately after the resource has been refreshed.
		 *
		 * @since   3.4.0
		 *
		 * @param   array   $results    Resources
		 */
		do_action( 'convertkit_resource_refreshed_' . $this->type, $results );

		// Return resources.
		return $this->get();

	}

	/**
	 * Overrides the parent method to return account data, as there's no sorting required.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get() {

		return get_option( $this->settings_name );

	}

	/**
	 * Returns the cached plan_type string.
	 *
	 * @since   3.4.0
	 *
	 * @return  bool|string
	 */
	public function get_plan_type() {

		// Get account details from cache.
		$account = $this->get();

		// If no account details are found, or the plan type is not set, return false.
		if ( ! $account || ! isset( $account['account']['plan_type'] ) ) {
			return false;
		}

		// Return the plan type.
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

		// Get the plan type from the account details.
		$plan_type = $this->get_plan_type();

		// If no plan type is found, return false.
		if ( ! $plan_type ) {
			return false;
		}

		// Return true if the plan type is not free, false otherwise.
		return $plan_type !== 'free';

	}

}
