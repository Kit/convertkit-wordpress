<?php
/**
 * ConvertKit Broadcasts Webhook class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers a Kit webhook endpoint for the post.published event, so Kit notifies
 * this site when a Broadcast is published.
 *
 * @since   3.4.6
 */
class ConvertKit_Broadcasts_Webhook {

	/**
	 * Holds the option name storing the registered webhook endpoint's ID, secret and URL.
	 *
	 * @since   3.4.6
	 *
	 * @var     string
	 */
	const OPTION_NAME = 'convertkit_broadcasts_webhook';

	/**
	 * Holds the option name storing the most recently received event IDs.
	 *
	 * @since   3.4.6
	 *
	 * @var     string
	 */
	const EVENTS_OPTION_NAME = 'convertkit_broadcasts_webhook_events';

	/**
	 * Holds the transient name storing the last registration error.
	 *
	 * @since   3.4.6
	 *
	 * @var     string
	 */
	const ERROR_TRANSIENT_NAME = 'convertkit_broadcasts_webhook_error';

	/**
	 * Holds the transient name storing the webhook endpoint's status in Kit.
	 *
	 * @since   3.4.6
	 *
	 * @var     string
	 */
	const STATUS_TRANSIENT_NAME = 'convertkit_broadcasts_webhook_status';

	/**
	 * The number of received event IDs to store, to ignore duplicate deliveries.
	 *
	 * @since   3.4.6
	 *
	 * @var     int
	 */
	const EVENTS_LIMIT = 100;

	/**
	 * Constructor.
	 *
	 * @since   3.4.6
	 */
	public function __construct() {

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'admin_init', array( $this, 'maybe_register' ) );

		// Register or delete the webhook endpoint when the Broadcasts settings change.
		add_action( 'add_option_' . ConvertKit_Settings_Broadcasts::SETTINGS_NAME, array( $this, 'settings_updated' ) );
		add_action( 'update_option_' . ConvertKit_Settings_Broadcasts::SETTINGS_NAME, array( $this, 'settings_updated' ) );

	}

	/**
	 * Registers the REST API route that receives webhook deliveries from Kit.
	 *
	 * @since   3.4.6
	 */
	public function register_routes() {

		register_rest_route(
			'kit/v1',
			'/broadcasts/webhook',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'receive' ),

				// Deliveries are authenticated by verifying their signature.
				'permission_callback' => '__return_true',
			)
		);

	}

	/**
	 * Receives a webhook delivery from Kit, scheduling the Broadcasts import if a
	 * post.published event is included.
	 *
	 * @since   3.4.6
	 *
	 * @param   WP_REST_Request $request    Request.
	 * @return  WP_REST_Response
	 */
	public function receive( $request ) {

		$settings = new ConvertKit_Settings_Broadcasts();
		$secret   = $this->get_secret();

		// Tell Kit to stop retrying this delivery if the import is disabled or no endpoint is registered.
		if ( ! $settings->enabled() || ! $secret ) {
			return new WP_REST_Response( null, 410 );
		}

		// Check that we're using the Kit WordPress Libraries 2.8.0 or higher.
		$api = new ConvertKit_API_V4( CONVERTKIT_OAUTH_CLIENT_ID, CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI );
		if ( ! method_exists( $api, 'verify_webhook_signature' ) ) { // @phpstan-ignore-line Older WordPress Libraries won't have this function.
			return new WP_REST_Response( null, 503 );
		}

		// Bail if the signature is invalid.
		if ( ! $api->verify_webhook_signature( $request->get_body(), (string) $request->get_header( 'x_kit_signature' ), $secret ) ) {
			return new WP_REST_Response( null, 401 );
		}

		// Bail if the payload is invalid.
		$payload = json_decode( $request->get_body(), true );
		if ( ! is_array( $payload ) || ! array_key_exists( 'events', $payload ) || ! is_array( $payload['events'] ) ) {
			return new WP_REST_Response( null, 400 );
		}

		// Iterate through events, ignoring any we've already received.
		$received_events = get_option( self::EVENTS_OPTION_NAME, array() );
		$import          = false;
		foreach ( $payload['events'] as $event ) {
			if ( ! isset( $event['id'], $event['type'] ) || in_array( $event['id'], $received_events, true ) ) {
				continue;
			}

			$received_events[] = $event['id'];

			if ( $event['type'] === 'post.published' ) {
				$import = true;
			}
		}

		// Store the most recently received event IDs before importing, so a retry from Kit
		// after a slow import doesn't import again.
		update_option( self::EVENTS_OPTION_NAME, array_slice( $received_events, -self::EVENTS_LIMIT ), false );

		// Refresh the Posts resource, which imports new Broadcasts.
		if ( $import ) {
			$posts = new ConvertKit_Resource_Posts( 'broadcasts_webhook' );
			$posts->refresh();
		}

		return new WP_REST_Response( null, 200 );

	}

	/**
	 * Registers or deletes the webhook endpoint when the Broadcasts settings are saved.
	 *
	 * @since   3.4.6
	 */
	public function settings_updated() {

		$settings = new ConvertKit_Settings_Broadcasts();

		if ( $settings->enabled() ) {
			$this->maybe_register();
			return;
		}

		// Delete the webhook endpoint if the import was disabled.
		if ( get_option( self::OPTION_NAME ) ) {
			$this->delete();
		}

	}

	/**
	 * Registers the webhook endpoint if the Broadcasts import is enabled and no
	 * endpoint is registered for this site.
	 *
	 * @since   3.4.6
	 */
	public function maybe_register() {

		$settings = new ConvertKit_Settings_Broadcasts();

		// Bail if the import is disabled, or the endpoint is already registered.
		if ( ! $settings->enabled() || $this->is_registered() ) {
			return;
		}

		// Bail if registration recently failed, so we don't request the API on every request.
		if ( get_transient( self::ERROR_TRANSIENT_NAME ) ) {
			return;
		}

		$this->register();

	}

	/**
	 * Registers the webhook endpoint in Kit, replacing any existing endpoint registered by this site.
	 *
	 * @since   3.4.6
	 *
	 * @return  WP_Error|bool
	 */
	public function register() {

		$api = $this->get_api();
		if ( is_wp_error( $api ) ) {
			return $this->registration_failed( $api );
		}

		// Delete the existing endpoint, so this site doesn't receive duplicate deliveries.
		// Skip if the URL changed (e.g. a cloned staging site), as the endpoint belongs to the other site.
		$webhook = get_option( self::OPTION_NAME );
		if ( is_array( $webhook ) && $webhook['url'] === $this->get_url() ) {
			$api->delete_webhook_endpoint( (int) $webhook['id'] );
		}

		// Create the endpoint.
		$result = $api->create_webhook_endpoint(
			$this->get_url(),
			array( 'post.published' ),
			sprintf( 'Kit WordPress Plugin: %s', home_url() )
		);
		if ( is_wp_error( $result ) ) {
			return $this->registration_failed( $result );
		}

		// Store the endpoint's ID and secret. The secret is only returned by Kit when creating the endpoint.
		update_option(
			self::OPTION_NAME,
			array(
				'id'     => $result['webhook_endpoint']['id'],
				'secret' => $result['webhook_endpoint']['secret'],
				'url'    => $this->get_url(),
			),
			false
		);
		delete_transient( self::ERROR_TRANSIENT_NAME );
		delete_transient( self::STATUS_TRANSIENT_NAME );

		return true;

	}

	/**
	 * Deletes the webhook endpoint from Kit, and the stored endpoint from this site.
	 *
	 * @since   3.4.6
	 */
	public function delete() {

		// Delete the endpoint from Kit, unless the URL changed, as the endpoint belongs to the other site.
		$webhook = get_option( self::OPTION_NAME );
		if ( is_array( $webhook ) && $webhook['url'] === $this->get_url() ) {
			$api = $this->get_api();
			if ( ! is_wp_error( $api ) ) {
				$api->delete_webhook_endpoint( (int) $webhook['id'] );
			}
		}

		delete_option( self::OPTION_NAME );
		delete_transient( self::ERROR_TRANSIENT_NAME );
		delete_transient( self::STATUS_TRANSIENT_NAME );

	}

	/**
	 * Returns the webhook endpoint's status in Kit (active, disabled, missing or unknown),
	 * or false if no endpoint is registered.
	 *
	 * @since   3.4.6
	 *
	 * @return  bool|string
	 */
	public function get_status() {

		if ( ! $this->is_registered() ) {
			return false;
		}

		// Return cached status, so we don't request the API on every load of the settings screen.
		$status = get_transient( self::STATUS_TRANSIENT_NAME );
		if ( $status ) {
			return $status;
		}

		$api = $this->get_api();
		if ( is_wp_error( $api ) ) {
			return 'unknown';
		}

		$webhook = get_option( self::OPTION_NAME );
		$result  = $api->get_webhook_endpoint( (int) $webhook['id'] );
		if ( is_wp_error( $result ) ) {
			// Only a 404 means the endpoint was deleted in Kit; other errors may be temporary.
			$status = ( $result->get_error_data( 'convertkit_api_error' ) === 404 ? 'missing' : 'unknown' );
		} else {
			$status = $result['webhook_endpoint']['status'];
		}

		set_transient( self::STATUS_TRANSIENT_NAME, $status, HOUR_IN_SECONDS );

		return $status;

	}

	/**
	 * Returns the last registration error, if any.
	 *
	 * @since   3.4.6
	 *
	 * @return  bool|string
	 */
	public function get_error() {

		return get_transient( self::ERROR_TRANSIENT_NAME );

	}

	/**
	 * Returns whether a webhook endpoint is registered for this site's URL.
	 *
	 * @since   3.4.6
	 *
	 * @return  bool
	 */
	public function is_registered() {

		$webhook = get_option( self::OPTION_NAME );

		return ( is_array( $webhook ) && $webhook['url'] === $this->get_url() );

	}

	/**
	 * Returns the URL Kit sends webhook deliveries to.
	 *
	 * @since   3.4.6
	 *
	 * @return  string
	 */
	public function get_url() {

		return rest_url( 'kit/v1/broadcasts/webhook' );

	}

	/**
	 * Returns the signing secret for the registered webhook endpoint.
	 *
	 * @since   3.4.6
	 *
	 * @return  bool|string
	 */
	private function get_secret() {

		if ( ! $this->is_registered() ) {
			return false;
		}

		$webhook = get_option( self::OPTION_NAME );
		return $webhook['secret'];

	}

	/**
	 * Stores the registration error, and removes any stored endpoint.
	 *
	 * @since   3.4.6
	 *
	 * @param   WP_Error $error  Error.
	 * @return  WP_Error
	 */
	private function registration_failed( $error ) {

		set_transient( self::ERROR_TRANSIENT_NAME, $error->get_error_message(), HOUR_IN_SECONDS );
		delete_option( self::OPTION_NAME );
		delete_transient( self::STATUS_TRANSIENT_NAME );

		return $error;

	}

	/**
	 * Returns the API class, if the Plugin has an access token and the Kit WordPress Libraries
	 * support webhook endpoints.
	 *
	 * @since   3.4.6
	 *
	 * @return  WP_Error|ConvertKit_API_V4
	 */
	private function get_api() {

		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			return new WP_Error(
				'convertkit_broadcasts_webhook_error',
				__( 'No Access Token specified in Plugin Settings', 'convertkit' )
			);
		}

		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$settings->get_access_token(),
			$settings->get_refresh_token(),
			$settings->debug_enabled(),
			'broadcasts_webhook'
		);

		// Check that we're using the Kit WordPress Libraries 2.8.0 or higher.
		// If another Kit Plugin is active and out of date, its libraries might
		// be loaded that don't have this method.
		if ( ! method_exists( $api, 'create_webhook_endpoint' ) ) { // @phpstan-ignore-line Older WordPress Libraries won't have this function.
			return new WP_Error(
				'convertkit_broadcasts_webhook_error',
				__( 'Kit WordPress Libraries 2.7.0 or older detected, missing the `create_webhook_endpoint` method.', 'convertkit' )
			);
		}

		return $api;

	}

}
