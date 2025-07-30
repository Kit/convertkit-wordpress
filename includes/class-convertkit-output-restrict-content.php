<?php
/**
 * ConvertKit Output Restrict Content class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Restricts (or displays) a single Page, Post or Custom Post Type's content
 * based on the Post's "Restrict Content" configuration.
 *
 * @since   2.1.0
 */
class ConvertKit_Output_Restrict_Content {

	/**
	 * Holds the WP_Error object if an API call / authentication failed,
	 * to display on screen as a notification.
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|WP_Error
	 */
	public $error = false;

	/**
	 * Holds the ConvertKit Plugin Settings class
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|ConvertKit_Settings
	 */
	public $settings = false;

	/**
	 * Holds the ConvertKit Restrict Content Settings class
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|ConvertKit_Settings_Restrict_Content
	 */
	public $restrict_content_settings = false;

	/**
	 * Holds the ConvertKit Post Settings class
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|ConvertKit_Post
	 */
	public $post_settings = false;

	/**
	 * Holds the Resource Type (product|tag) that must be subscribed to in order
	 * to grant access to the Post.
	 *
	 * @since   2.3.8
	 *
	 * @var     bool|string
	 */
	public $resource_type = false;

	/**
	 * Holds the Resource ID that must be subscribed to in order
	 * to grant access to the Post.
	 *
	 * @since   2.3.8
	 *
	 * @var     bool|int
	 */
	public $resource_id = false;

	/**
	 * Holds the Post ID
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|int
	 */
	public $post_id = false;

	/**
	 * Holds the ConvertKit API class
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|ConvertKit_API_V4
	 */
	public $api = false;

	/**
	 * Holds the token returned from calling the subscriber_authentication_send_code API endpoint.
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|string
	 */
	public $token = false;

	/**
	 * Constructor. Registers actions and filters to possibly limit output of a Page/Post/CPT's
	 * content on the frontend site.
	 *
	 * @since   2.1.0
	 */
	public function __construct() {

		// Initialize classes that will be used.
		$this->settings                  = new ConvertKit_Settings();
		$this->restrict_content_settings = new ConvertKit_Settings_Restrict_Content();

		// Don't register any hooks if this is an AJAX request, otherwise
		// maybe_run_subscriber_authentication() and maybe_run_subscriber_verification() will run
		// twice in an AJAX request (once here, and once when called by the ConvertKit_AJAX class).
		if ( wp_doing_ajax() ) {
			return;
		}

		add_action( 'init', array( $this, 'maybe_run_subscriber_authentication' ), 3 );
		add_action( 'wp', array( $this, 'maybe_run_subscriber_verification' ), 4 );
		add_action( 'wp', array( $this, 'register_content_filter' ), 5 );
		add_filter( 'get_previous_post_where', array( $this, 'maybe_change_previous_post_where_clause' ), 10, 5 );
		add_filter( 'get_next_post_where', array( $this, 'maybe_change_next_post_where_clause' ), 10, 5 );
		add_filter( 'get_previous_post_sort', array( $this, 'maybe_change_previous_next_post_order_by_clause' ), 10, 3 );
		add_filter( 'get_next_post_sort', array( $this, 'maybe_change_previous_next_post_order_by_clause' ), 10, 3 );

	}

	/**
	 * Checks if the request is a Restrict Content request with an email address.
	 * If so, calls the API depending on the Restrict Content resource that's required:
	 * - tag: subscribes the email address to the tag, storing the subscriber ID in a cookie and redirecting
	 * - product: calls the API to send the subscriber a magic link by email containing a code. See maybe_run_subscriber_verification()
	 * for logic once they click the link in the email or enter the code on screen.
	 *
	 * @since   2.1.0
	 */
	public function maybe_run_subscriber_authentication() {

		// Bail if no nonce was specified.
		if ( ! array_key_exists( '_wpnonce', $_REQUEST ) ) {
			return;
		}

		// Bail if the nonce failed validation.
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'convertkit_restrict_content_login' ) ) {
			return;
		}

		// Bail if the expected email, resource ID or Post ID are missing.
		if ( ! array_key_exists( 'convertkit_email', $_REQUEST ) ) {
			return;
		}
		if ( ! array_key_exists( 'convertkit_resource_type', $_REQUEST ) ) {
			return;
		}
		if ( ! array_key_exists( 'convertkit_resource_id', $_REQUEST ) ) {
			return;
		}
		if ( ! array_key_exists( 'convertkit_post_id', $_REQUEST ) ) {
			return;
		}

		// If the Plugin Access Token has not been configured, we can't get this subscriber's ID by email.
		if ( ! $this->settings->has_access_and_refresh_token() ) {
			return;
		}

		// Initialize the API.
		$this->api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$this->settings->get_access_token(),
			$this->settings->get_refresh_token(),
			$this->settings->debug_enabled(),
			'restrict_content'
		);

		// Sanitize inputs.
		$email               = sanitize_text_field( wp_unslash( $_REQUEST['convertkit_email'] ) );
		$this->resource_type = sanitize_text_field( wp_unslash( $_REQUEST['convertkit_resource_type'] ) );
		$this->resource_id   = absint( $_REQUEST['convertkit_resource_id'] );
		$this->post_id       = absint( $_REQUEST['convertkit_post_id'] );

		// Run subscriber authentication / subscription depending on the resource type.
		switch ( $this->resource_type ) {
			case 'product':
			case 'form':
				// Send email to subscriber with a link to authenticate they have access to the email address submitted.
				$result = $this->api->subscriber_authentication_send_code(
					$email,
					$this->get_url()
				);

				// Bail if an error occured.
				if ( is_wp_error( $result ) ) {
					$this->error = $result;
					return;
				}

				// Clear any existing subscriber ID cookie, as the authentication flow has started by sending the email.
				$subscriber = new ConvertKit_Subscriber();
				$subscriber->forget();

				// Store the token so it's included in the subscriber code form.
				$this->token = $result;
				break;

			case 'tag':
				// If require login is enabled, show the login screen.
				if ( $this->restrict_content_settings->require_tag_login() ) {
					// Tag the subscriber, unless this is an AJAX request.
					if ( ! wp_doing_ajax() ) {
						$result = $this->api->tag_subscribe( $this->resource_id, $email );

						// Bail if an error occured.
						if ( is_wp_error( $result ) ) {
							$this->error = $result;
							return;
						}
					}

					// Send email to subscriber with a link to authenticate they have access to the email address submitted.
					$result = $this->api->subscriber_authentication_send_code(
						$email,
						$this->get_url()
					);

					// Bail if an error occured.
					if ( is_wp_error( $result ) ) {
						$this->error = $result;
						return;
					}

					// Clear any existing subscriber ID cookie, as the authentication flow has started by sending the email.
					$subscriber = new ConvertKit_Subscriber();
					$subscriber->forget();

					// Store the token so it's included in the subscriber code form.
					$this->token = $result;
					break;
				}

				// If here, require login is disabled.
				// Check reCAPTCHA, tag subscriber and assign subscriber ID integer to cookie
				// without email link.

				// If Google reCAPTCHA is enabled, check if the submission is spam.
				if ( $this->restrict_content_settings->has_recaptcha_site_and_secret_keys() && ! $this->settings->scripts_disabled() ) {
					$response = wp_remote_post(
						'https://www.google.com/recaptcha/api/siteverify',
						array(
							'body' => array(
								'secret'   => $this->restrict_content_settings->get_recaptcha_secret_key(),
								'response' => ( isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '' ),
								'remoteip' => ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ),
							),
						)
					);

					// Bail if an error occured.
					if ( is_wp_error( $response ) ) {
						$this->error = $response;
						return;
					}

					// Inspect response.
					$body = json_decode( wp_remote_retrieve_body( $response ), true );

					// If the request wasn't successful, throw an error.
					if ( ! $body['success'] ) {
						$this->error = new WP_Error(
							'convertkit_output_restrict_content_maybe_run_subscriber_authentication_error',
							sprintf(
								/* translators: Error codes */
								__( 'Google reCAPTCHA failure: %s', 'convertkit' ),
								implode( ', ', $body['error-codes'] )
							)
						);
						return;
					}

					// If the action doesn't match the Plugin action, this might not be a reCAPTCHA request
					// for this Plugin.
					if ( $body['action'] !== 'convertkit_restrict_content_tag' ) {
						// Just silently return.
						return;
					}

					// If the score is less than 0.5 (on a scale of 0.0 to 1.0, with 0.0 being a bot, 1.0 being very good),
					// it's likely a spam submission.
					if ( $body['score'] < $this->restrict_content_settings->get_recaptcha_minimum_score() ) {
						$this->error = new WP_Error(
							'convertkit_output_restrict_content_maybe_run_subscriber_authentication_error',
							__( 'Google reCAPTCHA failed', 'convertkit' )
						);
						return;
					}

					// If here, the submission looks genuine. Continue the request.
				}

				// Tag the subscriber.
				$result = $this->api->tag_subscribe( $this->resource_id, $email );

				// Bail if an error occured.
				if ( is_wp_error( $result ) ) {
					$this->error = $result;
					return;
				}

				// Clear any existing subscriber ID cookie, as the authentication flow has started by sending the email.
				$subscriber = new ConvertKit_Subscriber();
				$subscriber->forget();

				// Fetch the subscriber ID from the result.
				$subscriber_id = $result['subscriber']['id'];

				// Store subscriber ID in cookie.
				$this->store_subscriber_id_in_cookie( $subscriber_id );

				// If this isn't an AJAX request, redirect now to reload the Post.
				if ( ! wp_doing_ajax() ) {
					$this->redirect();
				}
				break;

		}

	}

	/**
	 * Checks if the request contains a token and subscriber_code i.e. the subscriber clicked
	 * the link in the email sent by the maybe_run_subscriber_authentication() function above.
	 *
	 * This calls the API to verify the token and subscriber code, which tells us that the email
	 * address supplied truly belongs to the user, and that we can safely trust their subscriber ID
	 * to be valid.
	 *
	 * @since   2.1.0
	 */
	public function maybe_run_subscriber_verification() {

		// Bail if the expected token and subscriber code is missing.
		if ( ! array_key_exists( 'token', $_REQUEST ) ) {
			return;
		}
		if ( ! array_key_exists( 'subscriber_code', $_REQUEST ) ) {
			return;
		}

		// If a nonce was specified, validate it now.
		// It won't be provided if clicking the link in the magic link email.
		if ( array_key_exists( '_wpnonce', $_REQUEST ) ) {
			if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'convertkit_restrict_content_subscriber_code' ) ) {
				return;
			}
		}

		// If the Plugin Access Token has not been configured, we can't get this subscriber's ID by email.
		if ( ! $this->settings->has_access_and_refresh_token() ) {
			return;
		}

		// Store the token so it's included in the subscriber code form if verification fails.
		$this->token = sanitize_text_field( wp_unslash( $_REQUEST['token'] ) );

		// Store the post ID if this is an AJAX request.
		// This won't be included if clicking the link in the magic link email, so fall back to using
		// get_the_ID() to get the post ID.
		if ( array_key_exists( 'convertkit_post_id', $_REQUEST ) ) {
			$this->post_id = absint( wp_unslash( $_REQUEST['convertkit_post_id'] ) );
		} else {
			$this->post_id = get_the_ID();
		}

		// Initialize the API.
		$this->api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$this->settings->get_access_token(),
			$this->settings->get_refresh_token(),
			$this->settings->debug_enabled(),
			'restrict_content'
		);

		// Verify the token and subscriber code.
		$subscriber_id = $this->api->subscriber_authentication_verify(
			sanitize_text_field( wp_unslash( $_REQUEST['token'] ) ),
			sanitize_text_field( wp_unslash( $_REQUEST['subscriber_code'] ) )
		);

		// Bail if an error occured.
		if ( is_wp_error( $subscriber_id ) ) {
			$this->error = $subscriber_id;
			return;
		}

		// Store subscriber ID in cookie.
		$this->store_subscriber_id_in_cookie( $subscriber_id );

		// If this isn't an AJAX request, redirect now to reload the Post.
		if ( ! wp_doing_ajax() ) {
			$this->redirect();
		}

	}

	/**
	 * Registers the applicable content filter for maybe restricting content, depending
	 * on the Theme or Page Builder used.
	 *
	 * @since   2.7.7
	 */
	public function register_content_filter() {

		// Use the standard `the_content` filter, which works for most Themes
		// and Page Builders.
		add_filter( 'the_content', array( $this, 'maybe_restrict_content' ) );

		// Fetch some information about the current Page.
		$id = get_the_ID();

		/**
		 * Allow specific Themes and Page Builders to use a different filter
		 * for Restrict Content functionality.
		 *
		 * @since   2.7.7
		 */
		do_action( 'convertkit_restrict_content_register_content_filter' );

	}

	/**
	 * Displays (or hides) content on a singular Page, Post or Custom Post Type's Content,
	 * depending on whether the visitor is an authenticated ConvertKit subscriber and has
	 * subscribed to the ConvertKit Product or Tag.
	 *
	 * @since   2.1.0
	 *
	 * @param   string $content    Post Content.
	 * @return  string              Post Content with content restricted/not restricted
	 */
	public function maybe_restrict_content( $content ) {

		// Bail if the Restrict Content setting is not enabled on this Page.
		if ( ! $this->is_restricted_content() ) {
			return $content;
		}

		// Bail if the Page is being edited in a frontend Page Builder / Editor by a logged
		// in WordPress user who has the capability to edit the Page.
		// This ensures the User can view all content to edit it, instead of seeing the Restrict Content
		// view.
		if ( current_user_can( 'edit_post', get_the_ID() ) && WP_ConvertKit()->is_admin_or_frontend_editor() ) {
			return $content;
		}

		// Get resource type (Product or Tag) that the visitor must be subscribed against to access this content.
		$this->resource_type = $this->get_resource_type();

		// Return the Post Content, unedited, if the Resource Type is false.
		if ( ! $this->resource_type ) {
			return $content;
		}

		// Get resource ID (Product ID or Tag ID) that the visitor must be subscribed against to access this content.
		$this->resource_id = $this->get_resource_id();

		// Return the full Post Content, unedited, if the Resource ID is false, as this means
		// no restrict content setting has been defined for this Post.
		if ( ! $this->resource_id ) {
			return $content;
		}

		// Return the full Post Content, unedited, if the request is from a crawler.
		if ( $this->restrict_content_settings->permit_crawlers() && $this->is_crawler() ) {
			return $content;
		}

		// Return if this request is after the user entered their email address,
		// which means we're going through the authentication flow.
		if ( $this->in_authentication_flow() ) {
			return $this->restrict_content( $content );
		}

		// Get the subscriber ID, either from the request or an existing cookie.
		$subscriber_id = $this->get_subscriber_id_from_request();

		// If no subscriber ID exists, the visitor cannot view the content.
		if ( ! $subscriber_id ) {
			return $this->restrict_content( $content );
		}

		// If the subscriber is not subscribed to the product, restrict the content.
		if ( ! $this->subscriber_has_access( $subscriber_id ) ) {
			// Show an error before the call to action, to tell the subscriber why they still cannot
			// view the content.
			switch ( $this->resource_type ) {
				case 'form':
					$message = $this->restrict_content_settings->get_by_key( 'no_access_text_form' );
					break;

				case 'tag':
					$message = $this->restrict_content_settings->get_by_key( 'no_access_text_tag' );
					break;

				case 'product':
				default:
					$message = $this->restrict_content_settings->get_by_key( 'no_access_text' );
					break;
			}

			// Define error for output.
			$this->error = new WP_Error(
				'convertkit_restrict_content_subscriber_no_access',
				esc_html( $message )
			);

			return $this->restrict_content( $content );
		}

		// If here, the subscriber has subscribed to the product.
		// Show the full Post Content.
		return $content;

	}

	/**
	 * Changes how WordPress' get_adjacent_post() function queries Pages, to determine what
	 * the previous Page link is when using the Previous navigation block on a Page that
	 * has the Restrict Content setting defined.
	 *
	 * By default, get_adjacent_post() will query by post_date, which we change to menu_order.
	 *
	 * @since   2.1.0
	 *
	 * @param   string  $where          The `WHERE` clause in the SQL.
	 * @param   bool    $in_same_term   Whether post should be in a same taxonomy term.
	 * @param   array   $excluded_terms Array of excluded term IDs.
	 * @param   string  $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
	 * @param   WP_Post $post           WP_Post object.
	 * @return  string                  Modified `WHERE` clause
	 */
	public function maybe_change_previous_post_where_clause( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if the Restrict Content setting is not enabled on this Page.
		if ( ! $this->is_restricted_content() ) {
			return $where;
		}

		// Bail if the Page doesn't match the current Page being viewed, or has no parent Page.
		if ( ! $this->has_parent_page( $post ) ) {
			return $where;
		}

		// Build replacement where statement.
		$new_where = 'p.post_parent = ' . $post->post_parent . ' AND p.menu_order < ' . $post->menu_order;

		// Replace existing where statement with new statement.
		$where = 'WHERE ' . $new_where . ' ' . substr( $where, strpos( $where, 'AND' ) );

		// Return.
		return $where;

	}

	/**
	 * Changes how WordPress' get_adjacent_post() function queries Pages, to determine what
	 * the next Page link is when using the Previous navigation block on a Page that
	 * has the Restrict Content setting defined.
	 *
	 * By default, get_adjacent_post() will query by post_date, which we change to menu_order.
	 *
	 * @since   2.1.0
	 *
	 * @param   string  $where          The `WHERE` clause in the SQL.
	 * @param   bool    $in_same_term   Whether post should be in a same taxonomy term.
	 * @param   array   $excluded_terms Array of excluded term IDs.
	 * @param   string  $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
	 * @param   WP_Post $post           WP_Post object.
	 * @return  string                  Modified `WHERE` clause
	 */
	public function maybe_change_next_post_where_clause( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if the Restrict Content setting is not enabled on this Page.
		if ( ! $this->is_restricted_content() ) {
			return $where;
		}

		// Bail if the Page doesn't match the current Page being viewed, or has no parent Page.
		if ( ! $this->has_parent_page( $post ) ) {
			return $where;
		}

		// Build replacement where statement.
		$new_where = 'p.post_parent = ' . $post->post_parent . ' AND p.menu_order > ' . $post->menu_order;

		// Replace existing where statement with new statement.
		$where = 'WHERE ' . $new_where . ' ' . substr( $where, strpos( $where, 'AND' ) );

		// Return.
		return $where;

	}

	/**
	 * Changes how WordPress' get_adjacent_post() function orders Pages, to determine what
	 * the next and previous Page links are when using Previous / Next navigation blocks
	 * on a Page that has the Restrict Content setting defined.
	 *
	 * By default, get_adjacent_post() will sort by Post Date, which we change to Page Order
	 * (called menu_order in WordPress).
	 *
	 * @since   2.1.0
	 *
	 * @param   string  $order_by   SQL ORDER BY statement.
	 * @param   WP_Post $post       WordPress Post.
	 * @param   string  $order      Order.
	 * @return  string              Modified SQL ORDER BY statement.
	 */
	public function maybe_change_previous_next_post_order_by_clause( $order_by, $post, $order ) {

		// Bail if the Restrict Content setting is not enabled on this Page.
		if ( ! $this->is_restricted_content() ) {
			return $order_by;
		}

		// Bail if the Page doesn't match the current Page being viewed, or has no parent Page.
		if ( ! $this->has_parent_page( $post ) ) {
			return $order_by;
		}

		// Order by Page order (menu_order), highest to lowest, instead of post_date.
		return 'ORDER BY p.menu_order ' . $order . ' LIMIT 1';

	}

	/**
	 * Stores the given subscriber ID in the ck_subscriber_id cookie.
	 *
	 * @since   2.3.7
	 *
	 * @param   string|int $subscriber_id  Subscriber ID (int if restrict by tag, signed subscriber id string if restrict by product).
	 */
	private function store_subscriber_id_in_cookie( $subscriber_id ) {

		// Store subscriber ID in cookie.
		// We don't need to use validate_and_store_subscriber_id() as we just validated the subscriber via authentication above.
		$subscriber = new ConvertKit_Subscriber();
		$subscriber->set( $subscriber_id );

	}

	/**
	 * Redirects to the current URL, removing any query parameters (such as tokens), and appending
	 * a ck-cache-bust query parameter to beat caching plugins.
	 *
	 * @since   2.3.7
	 */
	private function redirect() {

		// Redirect to the Post, appending a query parameter to the URL to prevent caching plugins and
		// aggressive cache hosting configurations from serving a cached page, which would
		// result in maybe_restrict_content() not showing an error message or permitting
		// access to the content.
		wp_safe_redirect( $this->get_url( true ) );
		exit;

	}

	/**
	 * Returns the URL for the current request, excluding any query parameters.
	 *
	 * @since   2.1.0
	 *
	 * @param   bool $cache_bust     Include `ck-cache-bust` parameter in URL.
	 * @return  string  URL.
	 */
	public function get_url( $cache_bust = false ) {

		// Get URL of Post.
		$url = get_permalink( $this->post_id );

		// If no cache busting required, return the URL now.
		if ( ! $cache_bust ) {
			return $url;
		}

		// Append a query parameter to the URL to prevent caching plugins and
		// aggressive cache hosting configurations from serving a cached page, which would
		// result in maybe_restrict_content() not showing an error message or permitting
		// access to the content.
		return add_query_arg(
			array(
				'ck-cache-bust' => microtime(),
			),
			$url
		);

	}

	/**
	 * Determines if the request is for a WordPress Page that has the Restrict Content
	 * setting defined.
	 *
	 * @since   2.1.0
	 *
	 * @return  bool
	 */
	private function is_restricted_content() {

		// Bail if not a singular Post Type.
		if ( ! is_singular() ) {
			return false;
		}

		// If the Plugin Access Token has not been configured, we can't determine the validity of this subscriber ID
		// or which resource(s) they have access to.
		if ( ! $this->settings->has_access_and_refresh_token() ) {
			return false;
		}

		// Get Post ID.
		$this->post_id = get_the_ID();

		// Initialize Settings and Post Setting classes.
		$this->post_settings = new ConvertKit_Post( $this->post_id );

		// Return whether the Post's settings are set to restrict content.
		return $this->post_settings->restrict_content_enabled();

	}

	/**
	 * Determines if the user entered a valid email address, and need to be prompted
	 * to enter a code sent to their email address.
	 *
	 * @since   2.1.0
	 *
	 * @return  bool
	 */
	private function in_authentication_flow() {

		return ( $this->token !== false );

	}

	/**
	 * Checks if the given WordPress Page matches the Page ID viewed, and has a parent.
	 *
	 * @since   2.1.0
	 *
	 * @param   WP_Post $post   WordPress Post.
	 * @return  bool                Has parent page
	 */
	private function has_parent_page( $post ) {

		// Bail if the Page doesn't match the current Page being viewed.
		// This prevents us accidentally interfering with other previous / next link queries, which shouldn't happen
		// as we check if we're viewing a restricted content page above.
		if ( $post->ID !== $this->post_id ) {
			return false;
		}

		// Bail if the Page doesn't have a parent Page.
		// We don't want to modify the default sort behaviour in this instance.
		if ( $post->post_parent === 0 ) {
			return false;
		}

		return true;

	}

	/**
	 * Get the Post's Restricted Content resource type.
	 *
	 * @since   2.1.0
	 *
	 * @return  bool|string     Resource Type (product).
	 */
	private function get_resource_type() {

		// Initialize Post Setting classes.
		$this->post_settings = new ConvertKit_Post( $this->post_id );

		// Get resource type.
		$resource_type = $this->post_settings->get_restrict_content_type();

		/**
		 * Define the ConvertKit Resource Type that the visitor must be subscribed against
		 * to access this content, overriding the Post setting.
		 *
		 * Return false or an empty string to not restrict content.
		 *
		 * @since   2.1.0
		 *
		 * @param   string $resource_type   Resource Type (product)
		 * @param   int    $post_id         Post ID
		 */
		$resource_type = apply_filters( 'convertkit_output_restrict_content_get_resource_type', $resource_type, $this->post_id );

		// If resource type is blank, set it to false.
		if ( empty( $resource_type ) ) {
			$resource_type = false;
		}

		// Return.
		return $resource_type;

	}

	/**
	 * Get the Post's Restricted Content resource ID.
	 *
	 * @since   2.1.0
	 *
	 * @return  int             Resource ID (product ID).
	 */
	private function get_resource_id() {

		// Initialize Post Setting classes.
		$this->post_settings = new ConvertKit_Post( $this->post_id );

		// Get resource ID.
		$resource_id = $this->post_settings->get_restrict_content_id();

		/**
		 * Define the ConvertKit Resource ID that the visitor must be subscribed against
		 * to access this content, overriding the Post setting.
		 *
		 * Return 0 to not restrict content.
		 *
		 * @since   2.1.0
		 *
		 * @param   int    $resource_id     Resource ID
		 * @param   int    $post_id         Post ID
		 */
		$resource_id = apply_filters( 'convertkit_output_restrict_content_get_resource_id', $resource_id, $this->post_id );

		// Return.
		return $resource_id;

	}

	/**
	 * Queries the API to confirm whether the resource exists.
	 *
	 * @since   2.3.3
	 *
	 * @return  bool
	 */
	private function resource_exists() {

		switch ( $this->resource_type ) {

			case 'product':
				// Get Product.
				$products = new ConvertKit_Resource_Products( 'restrict_content' );
				$product  = $products->get_by_id( $this->resource_id );

				// If the Product does not exist, return false.
				if ( ! $product ) {
					return false;
				}

				// Product exists in ConvertKit.
				return true;

			case 'form':
				// Get Form.
				$forms = new ConvertKit_Resource_Forms( 'restrict_content' );
				$form  = $forms->get_by_id( $this->resource_id );

				// If the Form does not exist, return false.
				if ( ! $form ) {
					return false;
				}

				// Form exists in ConvertKit.
				return true;

			case 'tag':
				// Get Tag.
				$tags = new ConvertKit_Resource_Tags( 'restrict_content' );
				$tag  = $tags->get_by_id( $this->resource_id );

				// If the Tag does not exist, return false.
				if ( ! $tag ) {
					return false;
				}

				// Tag exists in ConvertKit.
				return true;

			default:
				return false;

		}

	}

	/**
	 * Determines if the given subscriber has an active subscription to
	 * the given resource and its ID.
	 *
	 * @since   2.1.0
	 *
	 * @param   string|int $subscriber_id  Signed Subscriber ID or Subscriber ID.
	 * @return  bool                        Can view restricted content
	 */
	private function subscriber_has_access( $subscriber_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Initialize the API.
		$this->api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$this->settings->get_access_token(),
			$this->settings->get_refresh_token(),
			$this->settings->debug_enabled(),
			'restrict_content'
		);

		// Depending on the resource type, determine if the subscriber has access to it.
		// This is deliberately a switch statement, because we will likely add in support
		// for restrict by tag and form later.
		switch ( $this->resource_type ) {
			case 'product':
				// For products, the subscriber ID has to be a signed subscriber ID string.
				return $this->subscriber_has_access_to_product_by_signed_subscriber_id( $subscriber_id, absint( $this->resource_id ) );

			case 'form':
				// For forms, the subscriber ID has to be a signed subscriber ID string.
				return $this->subscriber_has_access_to_form_by_signed_subscriber_id( $subscriber_id, absint( $this->resource_id ) );

			case 'tag':
				// If the subscriber ID is numeric, check using get_subscriber_tags().
				if ( is_numeric( $subscriber_id ) ) {
					// If require login is enabled, only a signed subscriber ID is accepted, as this is generated
					// via the subscriber verify email flow.
					if ( $this->restrict_content_settings->require_tag_login() ) {
						return false;
					}

					return $this->subscriber_has_access_to_tag_by_subscriber_id( $subscriber_id, absint( $this->resource_id ) );
				}

				// The subscriber ID is a signed subscriber ID string.
				// Check using profile().
				return $this->subscriber_has_access_to_tag_by_signed_subscriber_id( $subscriber_id, absint( $this->resource_id ) );

		}

		// If here, the subscriber does not have access.
		return false;

	}

	/**
	 * Determines if the given signed subscriber ID has an active subscription to
	 * the given product.
	 *
	 * @since   2.7.1
	 *
	 * @param   string $signed_subscriber_id   Signed Subscriber ID.
	 * @param   int    $product_id             Product ID.
	 * @return  bool                            Has access to product
	 */
	private function subscriber_has_access_to_product_by_signed_subscriber_id( $signed_subscriber_id, $product_id ) {

		// Get products that the subscriber has access to.
		$result = $this->api->profile( $signed_subscriber_id );

		// If an error occured, the subscriber ID is invalid.
		if ( is_wp_error( $result ) ) {
			return false;
		}

		// If no products exist, there's no access.
		if ( ! $result['products'] || ! count( $result['products'] ) ) {
			return false;
		}

		// Return if the subscriber is subscribed to the product or not.
		return in_array( $product_id, $result['products'], true );

	}

	/**
	 * Determines if the given signed subscriber ID has an active subscription to
	 * the given form.
	 *
	 * @since   2.7.3
	 *
	 * @param   string $signed_subscriber_id   Signed Subscriber ID.
	 * @param   int    $form_id                Form ID.
	 * @return  bool                           Has access to form
	 */
	private function subscriber_has_access_to_form_by_signed_subscriber_id( $signed_subscriber_id, $form_id ) {

		// Get products that the subscriber has access to.
		$result = $this->api->profile( $signed_subscriber_id );

		// If an error occured, the subscriber ID is invalid.
		if ( is_wp_error( $result ) ) {
			return false;
		}

		// If no forms exist, there's no access.
		if ( ! $result['forms'] || ! count( $result['forms'] ) ) {
			return false;
		}

		// Return if the subscriber is subscribed to the form or not.
		return in_array( $form_id, $result['forms'], true );

	}

	/**
	 * Determines if the given signed subscriber ID has an active subscription to
	 * the given tag.
	 *
	 * @since   2.7.1
	 *
	 * @param   string $signed_subscriber_id   Signed Subscriber ID.
	 * @param   int    $tag_id                 Tag ID.
	 * @return  bool                            Has access to tag
	 */
	private function subscriber_has_access_to_tag_by_signed_subscriber_id( $signed_subscriber_id, $tag_id ) {

		// Get products that the subscriber has access to.
		$result = $this->api->profile( $signed_subscriber_id );

		// If an error occured, the subscriber ID is invalid.
		if ( is_wp_error( $result ) ) {
			return false;
		}

		// If no tags exist, there's no access.
		if ( ! $result['tags'] || ! count( $result['tags'] ) ) {
			return false;
		}

		// Return if the subscriber is subscribed to the tag or not.
		return in_array( $tag_id, $result['tags'], true );

	}

	/**
	 * Determines if the given signed subscriber ID has an active subscription to
	 * the given tag.
	 *
	 * @since   2.7.1
	 *
	 * @param   int $subscriber_id  Subscriber ID.
	 * @param   int $tag_id         Tag ID.
	 * @return  bool                Has access to tag
	 */
	private function subscriber_has_access_to_tag_by_subscriber_id( $subscriber_id, $tag_id ) {

		// Get tags that the subscriber has been assigned.
		$tags = $this->api->get_subscriber_tags( $subscriber_id );

		// If an error occured, the subscriber ID is invalid.
		if ( is_wp_error( $tags ) ) {
			return false;
		}

		// If no tags exist, there's no access.
		if ( ! count( $tags['tags'] ) ) {
			return false;
		}

		// Iterate through the subscriber's tags to see if they have the required tag.
		foreach ( $tags['tags'] as $tag ) {
			if ( $tag['id'] === $tag_id ) {
				// Subscriber has the required tag assigned to them - grant access.
				return true;
			}
		}

		// If here, the subscriber does not have the tag.
		return false;

	}

	/**
	 * Gets the subscriber ID from the request (either the cookie or the URL).
	 *
	 * @since   2.1.0
	 *
	 * @return  int|string   Subscriber ID or Signed ID
	 */
	public function get_subscriber_id_from_request() {

		// Use ConvertKit_Subscriber class to fetch and validate the subscriber ID.
		$subscriber    = new ConvertKit_Subscriber();
		$subscriber_id = $subscriber->get_subscriber_id();

		// If an error occured, the subscriber ID in the request/cookie is not a valid subscriber.
		if ( is_wp_error( $subscriber_id ) ) {
			return 0;
		}

		return $subscriber_id;

	}

	/**
	 * Restrict the given Post Content by showing a preview of the content, and appending
	 * the call to action to subscribe or authenticate.
	 *
	 * @since   2.1.0
	 *
	 * @param   string $content        Post Content.
	 * @return  string                 Post Content preview with call to action
	 */
	private function restrict_content( $content ) {

		// Check that the resource exists before restricting the content.
		// This handles cases where e.g. a Tag or Product has been deleted in ConvertKit,
		// but the Page / Post still references the (now deleted) resource to restrict content with
		// under the 'Member Content' setting.
		if ( ! $this->resource_exists() ) {
			// Return the full Post Content, as we can't restrict it to a Product or Tag that no longer exists.
			return $content;
		}

		// Fetch the content preview.
		$content_preview = $this->get_content_preview( $content );

		/**
		 * Define the output for the content preview when the visitor is not
		 * an authenticated subscriber.
		 *
		 * @since   2.4.1
		 *
		 * @param   string  $content_preview    Content preview.
		 * @param   int     $post_id            Post ID.
		 */
		$content_preview = apply_filters( 'convertkit_output_restrict_content_content_preview', $content_preview, $this->post_id );

		// Fetch the call to action.
		$call_to_action = $this->get_call_to_action( $this->post_id );

		/**
		 * Define the output for the call to action, displayed below the content preview,
		 * when the visitor is not an authenticated subscriber.
		 *
		 * @since   2.4.1
		 *
		 * @param   string  $call_to_action     Call to Action.
		 * @param   int     $post_id            Post ID.
		 */
		$call_to_action = apply_filters( 'convertkit_output_restrict_content_call_to_action', $call_to_action, $this->post_id );

		// Return the content preview and its call to action.
		return $content_preview . $call_to_action;

	}

	/**
	 * Returns a preview of the given content for visitors that don't have access to restricted content.
	 *
	 * The preview is determined by:
	 * - A single <!--more--> tag being placed between WordPress paragraphs when using the Classic Editor.
	 * Content before the tag will be returned as the preview, unless 'noteaser' is enabled.
	 * - A single 'Read More' block being placed between WordPress blocks when using the Gutenberg Editor.
	 * Content before the Read More block will be returned as the preview, unless 'Hide the excerpt
	 * on the full content page' is enabled.
	 *
	 * If no more tag or Read More block is present, returns the Post's excerpt.
	 *
	 * @since   2.1.0
	 *
	 * @param   string $content    Post Content.
	 * @return  string              Post Content Preview.
	 */
	private function get_content_preview( $content ) {

		global $post;

		// Check if the content contains a <!--more--> tag, which the editor might have placed
		// in the content through WordPress' Classic Editor.
		$content_breakdown = get_extended( $content );

		// If the <!-- more --> tag exists, the 'extended' key will contain the restricted content.
		if ( ! empty( $content_breakdown['extended'] ) ) {
			// Return the preview content.
			return $content_breakdown['main'];
		}

		// Check if the content contains a 'Read More' block, which the editor might have placed
		// in the content through the Gutenberg Editor.
		$block_editor_tag = '<span id="more-' . $post->ID . '"></span>';
		if ( strpos( $content, $block_editor_tag ) !== false ) {
			// Split content into an array by the tag.
			$content_breakdown = explode( $block_editor_tag, $content );

			// Return the content before the tag.
			// If noteaser is enabled, this will correctly be blank.
			return $content_breakdown[0];
		}

		// If here, there is no preview content available. Use the Post's excerpt.
		return $this->get_excerpt( $post->ID );

	}

	/**
	 * Returns the excerpt for the given Post.
	 *
	 * If no excerpt is defined, generates one from the Post's content.
	 *
	 * @since   2.3.7
	 *
	 * @param   int $post_id    Post ID.
	 * @return  string              Post excerpt.
	 */
	private function get_excerpt( $post_id ) {

		// Remove 'the_content' filter, as if the Post contains no defined excerpt, WordPress
		// will invoke the Post's content to build an excerpt, resulting in an infinite loop.
		remove_filter( 'the_content', array( $this, 'maybe_restrict_content' ) );

		// Generate the Post's excerpt.
		$excerpt = get_the_excerpt( $post_id );

		// Restore filters so other functions and Plugins aren't affected.
		add_filter( 'the_content', array( $this, 'maybe_restrict_content' ) );

		// Return the excerpt.
		return wpautop( $excerpt );

	}

	/**
	 * Returns the HTML output for the call to action for visitors not subscribed to the required
	 * resource type and ID.
	 *
	 * @since   2.1.0
	 *
	 * @param   int $post_id        Post ID.
	 * @return  string                  HTML
	 */
	private function get_call_to_action( $post_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Only load styles if the Disable CSS option is off.
		if ( ! $this->settings->css_disabled() ) {
			// Enqueue styles.
			wp_enqueue_style( 'convertkit-restrict-content', CONVERTKIT_PLUGIN_URL . 'resources/frontend/css/restrict-content.css', array(), CONVERTKIT_PLUGIN_VERSION );
		}

		// Only load scripts if the Disable Scripts option is off.
		if ( ! $this->settings->scripts_disabled() ) {
			// Enqueue scripts.
			wp_enqueue_script( 'convertkit-restrict-content', CONVERTKIT_PLUGIN_URL . 'resources/frontend/js/restrict-content.js', array(), CONVERTKIT_PLUGIN_VERSION, true );
			wp_localize_script(
				'convertkit-restrict-content',
				'convertkit_restrict_content',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'debug'   => $this->settings->debug_enabled(),
				)
			);

		}

		// Output code form if this request is after the user entered their email address,
		// which means we're going through the authentication flow.
		if ( $this->in_authentication_flow() ) {
			ob_start();
			include CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/code.php';
			return trim( ob_get_clean() );
		}

		// This is deliberately a switch statement, because we will likely add in support
		// for restrict by tag and form later.
		switch ( $this->resource_type ) {
			case 'product':
				// Get header and text from settings for Products.
				$heading = $this->restrict_content_settings->get_by_key( 'subscribe_heading' );
				$text    = $this->restrict_content_settings->get_by_key( 'subscribe_text' );

				// Output product restricted message and email form.
				// Get Product.
				$products = new ConvertKit_Resource_Products( 'restrict_content' );
				$product  = $products->get_by_id( $this->resource_id );

				// Get commerce.js URL and enqueue.
				$url = $products->get_commerce_js_url();
				if ( $url ) {
					wp_enqueue_script( 'convertkit-commerce', $url, array(), CONVERTKIT_PLUGIN_VERSION, true );
				}

				// If scripts are enabled, output the email login form in a modal, which will be displayed
				// when the 'log in' link is clicked.
				if ( ! $this->settings->scripts_disabled() ) {
					add_action(
						'wp_footer',
						function () {

							include_once CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/login-modal.php';

						}
					);
				}

				// Output.
				ob_start();
				$button = $products->get_html(
					$this->resource_id,
					$this->restrict_content_settings->get_by_key( 'subscribe_button_label' ),
					array(
						'css_classes' => array( 'wp-block-button__link', 'wp-element-button' ),
					)
				);
				include CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/product.php';
				return trim( ob_get_clean() );

			case 'form':
				// Display the Form.
				$forms = new ConvertKit_Resource_Forms( 'restrict_content' );
				$form  = $forms->get_html( $this->resource_id, $post_id );

				// If scripts are enabled, output the email login form in a modal, which will be displayed
				// when the 'log in' link is clicked.
				if ( ! $this->settings->scripts_disabled() ) {
					add_action(
						'wp_footer',
						function () {

							include_once CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/login-modal.php';

						}
					);
				}

				// Output.
				ob_start();
				include CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/form.php';
				return trim( ob_get_clean() );

			case 'tag':
				// Get header and text from settings for Tags.
				$heading = $this->restrict_content_settings->get_by_key( 'subscribe_heading_tag' );
				$text    = $this->restrict_content_settings->get_by_key( 'subscribe_text_tag' );

				// If require login is enabled and scripts are enabled, output the email login form in a modal, which will be displayed
				// when the 'log in' link is clicked.
				if ( $this->restrict_content_settings->require_tag_login() && ! $this->settings->scripts_disabled() ) {
					add_action(
						'wp_footer',
						function () {

							include_once CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/login-modal.php';

						}
					);
				}

				// Enqueue Google reCAPTCHA JS if site and secret keys specified.
				if ( $this->restrict_content_settings->has_recaptcha_site_and_secret_keys() && ! $this->settings->scripts_disabled() ) {
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

				// Output.
				ob_start();
				include CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/tag.php';
				return trim( ob_get_clean() );

			default:
				return '';

		}

	}

	/**
	 * Whether this request is from a search engine crawler.
	 *
	 * @since   2.4.2
	 *
	 * @return  bool
	 */
	private function is_crawler() {

		// Define permitted user agent crawlers and their IP addresses.
		$permitted_user_agent_ip_ranges = array(
			// Google.
			// https://developers.google.com/static/search/apis/ipranges/googlebot.json.
			'Googlebot'     => array(
				'192.178.5.0/27',
				'34.100.182.96/28',
				'34.101.50.144/28',
				'34.118.254.0/28',
				'34.118.66.0/28',
				'34.126.178.96/28',
				'34.146.150.144/28',
				'34.147.110.144/28',
				'34.151.74.144/28',
				'34.152.50.64/28',
				'34.154.114.144/28',
				'34.155.98.32/28',
				'34.165.18.176/28',
				'34.175.160.64/28',
				'34.176.130.16/28',
				'34.22.85.0/27',
				'34.64.82.64/28',
				'34.65.242.112/28',
				'34.80.50.80/28',
				'34.88.194.0/28',
				'34.89.10.80/28',
				'34.89.198.80/28',
				'34.96.162.48/28',
				'35.247.243.240/28',
				'66.249.64.0/27',
				'66.249.64.128/27',
				'66.249.64.160/27',
				'66.249.64.192/27',
				'66.249.64.224/27',
				'66.249.64.32/27',
				'66.249.64.64/27',
				'66.249.64.96/27',
				'66.249.65.0/27',
				'66.249.65.160/27',
				'66.249.65.192/27',
				'66.249.65.224/27',
				'66.249.65.32/27',
				'66.249.65.64/27',
				'66.249.65.96/27',
				'66.249.66.0/27',
				'66.249.66.128/27',
				'66.249.66.160/27',
				'66.249.66.192/27',
				'66.249.66.32/27',
				'66.249.66.64/27',
				'66.249.66.96/27',
				'66.249.68.0/27',
				'66.249.68.32/27',
				'66.249.68.64/27',
				'66.249.69.0/27',
				'66.249.69.128/27',
				'66.249.69.160/27',
				'66.249.69.192/27',
				'66.249.69.224/27',
				'66.249.69.32/27',
				'66.249.69.64/27',
				'66.249.69.96/27',
				'66.249.70.0/27',
				'66.249.70.128/27',
				'66.249.70.160/27',
				'66.249.70.192/27',
				'66.249.70.224/27',
				'66.249.70.32/27',
				'66.249.70.64/27',
				'66.249.70.96/27',
				'66.249.71.0/27',
				'66.249.71.128/27',
				'66.249.71.160/27',
				'66.249.71.192/27',
				'66.249.71.224/27',
				'66.249.71.32/27',
				'66.249.71.64/27',
				'66.249.71.96/27',
				'66.249.72.0/27',
				'66.249.72.128/27',
				'66.249.72.160/27',
				'66.249.72.192/27',
				'66.249.72.224/27',
				'66.249.72.32/27',
				'66.249.72.64/27',
				'66.249.72.96/27',
				'66.249.73.0/27',
				'66.249.73.128/27',
				'66.249.73.160/27',
				'66.249.73.192/27',
				'66.249.73.224/27',
				'66.249.73.32/27',
				'66.249.73.64/27',
				'66.249.73.96/27',
				'66.249.74.0/27',
				'66.249.74.128/27',
				'66.249.74.32/27',
				'66.249.74.64/27',
				'66.249.74.96/27',
				'66.249.75.0/27',
				'66.249.75.128/27',
				'66.249.75.160/27',
				'66.249.75.192/27',
				'66.249.75.224/27',
				'66.249.75.32/27',
				'66.249.75.64/27',
				'66.249.75.96/27',
				'66.249.76.0/27',
				'66.249.76.128/27',
				'66.249.76.160/27',
				'66.249.76.192/27',
				'66.249.76.224/27',
				'66.249.76.32/27',
				'66.249.76.64/27',
				'66.249.76.96/27',
				'66.249.77.0/27',
				'66.249.77.128/27',
				'66.249.77.160/27',
				'66.249.77.192/27',
				'66.249.77.224/27',
				'66.249.77.32/27',
				'66.249.77.64/27',
				'66.249.77.96/27',
				'66.249.78.0/27',
				'66.249.78.32/27',
				'66.249.79.0/27',
				'66.249.79.128/27',
				'66.249.79.160/27',
				'66.249.79.192/27',
				'66.249.79.224/27',
				'66.249.79.32/27',
				'66.249.79.64/27',
				'66.249.79.96/27',
			),

			// Applebot.
			// http://search.developer.apple.com/applebot.json.
			'Applebot'      => array(
				'17.241.208.160/27',
				'17.241.193.160/27',
				'17.241.200.160/27',
				'17.22.237.0/24',
				'17.22.245.0/24',
				'17.22.253.0/24',
				'17.241.75.0/24',
				'17.241.219.0/24',
				'17.241.227.0/24',
				'17.246.15.0/24',
				'17.246.19.0/24',
				'17.246.23.0/24',
			),

			// Bing.
			// https://www.bing.com/toolbox/bingbot.json.
			'Bingbot'       => array(
				'157.55.39.0/24',
				'207.46.13.0/24',
				'40.77.167.0/24',
				'13.66.139.0/24',
				'13.66.144.0/24',
				'52.167.144.0/24',
				'13.67.10.16/28',
				'13.69.66.240/28',
				'13.71.172.224/28',
				'139.217.52.0/28',
				'191.233.204.224/28',
				'20.36.108.32/28',
				'20.43.120.16/28',
				'40.79.131.208/28',
				'40.79.186.176/28',
				'52.231.148.0/28',
				'20.79.107.240/28',
				'51.105.67.0/28',
				'20.125.163.80/28',
				'40.77.188.0/22',
				'65.55.210.0/24',
				'199.30.24.0/23',
				'40.77.202.0/24',
				'40.77.139.0/25',
				'20.74.197.0/28',
				'20.15.133.160/27',
				'40.77.177.0/24',
				'40.77.178.0/23',
			),

			// DuckDuckGo.
			// https://duckduckgo.com/duckduckgo-help-pages/results/duckduckbot.
			'DuckDuckBot'   => array(
				'57.152.72.128',
				'51.8.253.152',
				'40.80.242.63',
				'20.12.141.99',
				'20.49.136.28',
				'51.116.131.221',
				'51.107.40.209',
				'20.40.133.240',
				'20.50.168.91',
				'51.120.48.122',
				'20.193.45.113',
				'40.76.173.151',
				'40.76.163.7',
				'20.185.79.47',
				'52.142.26.175',
				'20.185.79.15',
				'52.142.24.149',
				'40.76.162.208',
				'40.76.163.23',
				'40.76.162.191',
				'40.76.162.247',
				'40.88.21.235',
				'20.191.45.212',
				'52.146.59.12',
				'52.146.59.156',
				'52.146.59.154',
				'52.146.58.236',
				'20.62.224.44',
				'51.104.180.53',
				'51.104.180.47',
				'51.104.180.26',
				'51.104.146.225',
				'51.104.146.235',
				'20.73.202.147',
				'20.73.132.240',
				'20.71.12.143',
				'20.56.197.58',
				'20.56.197.63',
				'20.43.150.93',
				'20.43.150.85',
				'20.44.222.1',
				'40.89.243.175',
				'13.89.106.77',
				'52.143.242.6',
				'52.143.241.111',
				'52.154.60.82',
				'20.197.209.11',
				'20.197.209.27',
				'20.226.133.105',
				'191.234.216.4',
				'191.234.216.178',
				'20.53.92.211',
				'20.53.91.2',
				'20.207.99.197',
				'20.207.97.190',
				'40.81.250.205',
				'40.64.106.11',
				'40.64.105.247',
				'20.72.242.93',
				'20.99.255.235',
				'20.113.3.121',
				'52.224.16.221',
				'52.224.21.53',
				'52.224.20.204',
				'52.224.21.19',
				'52.224.20.249',
				'52.224.20.203',
				'52.224.20.190',
				'52.224.16.229',
				'52.224.21.20',
				'52.146.63.80',
				'52.224.20.227',
				'52.224.20.193',
				'52.190.37.160',
				'52.224.21.23',
				'52.224.20.223',
				'52.224.20.181',
				'52.224.21.49',
				'52.224.21.55',
				'52.224.21.61',
				'52.224.19.152',
				'52.224.20.186',
				'52.224.21.27',
				'52.224.21.51',
				'52.224.20.174',
				'52.224.21.4',
				'51.104.164.109',
				'51.104.167.71',
				'51.104.160.177',
				'51.104.162.149',
				'51.104.167.95',
				'51.104.167.54',
				'51.104.166.111',
				'51.104.167.88',
				'51.104.161.32',
				'51.104.163.250',
				'51.104.164.189',
				'51.104.167.19',
				'51.104.160.167',
				'51.104.167.110',
				'20.191.44.119',
				'51.104.167.104',
				'20.191.44.234',
				'51.104.164.215',
				'51.104.167.52',
				'20.191.44.22',
				'51.104.167.87',
				'51.104.167.96',
				'20.191.44.16',
				'51.104.167.61',
				'51.104.164.147',
				'20.50.48.159',
				'40.114.182.172',
				'20.50.50.130',
				'20.50.50.163',
				'20.50.50.46',
				'40.114.182.153',
				'20.50.50.118',
				'20.50.49.55',
				'20.50.49.25',
				'40.114.183.251',
				'20.50.50.123',
				'20.50.49.237',
				'20.50.48.192',
				'20.50.50.134',
				'51.138.90.233',
				'40.114.183.196',
				'20.50.50.146',
				'40.114.183.88',
				'20.50.50.145',
				'20.50.50.121',
				'20.50.49.40',
				'51.138.90.206',
				'40.114.182.45',
				'51.138.90.161',
				'20.50.49.0',
				'40.119.232.215',
				'104.43.55.167',
				'40.119.232.251',
				'40.119.232.50',
				'40.119.232.146',
				'40.119.232.218',
				'104.43.54.127',
				'104.43.55.117',
				'104.43.55.116',
				'104.43.55.166',
				'52.154.169.50',
				'52.154.171.70',
				'52.154.170.229',
				'52.154.170.113',
				'52.154.171.44',
				'52.154.172.2',
				'52.143.244.81',
				'52.154.171.87',
				'52.154.171.250',
				'52.154.170.28',
				'52.154.170.122',
				'52.143.243.117',
				'52.143.247.235',
				'52.154.171.235',
				'52.154.171.196',
				'52.154.171.0',
				'52.154.170.243',
				'52.154.170.26',
				'52.154.169.200',
				'52.154.170.96',
				'52.154.170.88',
				'52.154.171.150',
				'52.154.171.205',
				'52.154.170.117',
				'52.154.170.209',
				'191.235.202.48',
				'191.233.3.202',
				'191.235.201.214',
				'191.233.3.197',
				'191.235.202.38',
				'20.53.78.144',
				'20.193.24.10',
				'20.53.78.236',
				'20.53.78.138',
				'20.53.78.123',
				'20.53.78.106',
				'20.193.27.215',
				'20.193.25.197',
				'20.193.12.126',
				'20.193.24.251',
				'20.204.242.101',
				'20.207.72.113',
				'20.204.242.19',
				'20.219.45.67',
				'20.207.72.11',
				'20.219.45.190',
				'20.204.243.55',
				'20.204.241.148',
				'20.207.72.110',
				'20.204.240.172',
				'20.207.72.21',
				'20.204.246.81',
				'20.207.107.181',
				'20.204.246.254',
				'20.219.43.246',
				'52.149.25.43',
				'52.149.61.51',
				'52.149.58.139',
				'52.149.60.38',
				'52.148.165.38',
				'52.143.95.162',
				'52.149.56.151',
				'52.149.30.45',
				'52.149.58.173',
				'52.143.95.204',
				'52.149.28.83',
				'52.149.58.69',
				'52.148.161.87',
				'52.149.58.27',
				'52.149.28.18',
				'20.79.226.26',
				'20.79.239.66',
				'20.79.238.198',
				'20.113.14.159',
				'20.75.144.152',
				'20.43.172.120',
				'20.53.134.160',
				'20.201.15.208',
				'20.93.28.24',
				'20.61.34.40',
				'52.242.224.168',
				'20.80.129.80',
				'20.195.108.47',
				'4.195.133.120',
				'4.228.76.163',
				'4.182.131.108',
				'4.209.224.56',
				'108.141.83.74',
				'4.213.46.14',
				'172.169.17.165',
				'51.8.71.117',
				'20.3.1.178',
			),

			// OpenAI Search Bot.
			// https://platform.openai.com/docs/bots/overview-of-openai-crawlers.
			// https://openai.com/searchbot.json.
			'OAI-SearchBot' => array(
				'20.42.10.176/28',
				'172.203.190.128/28',
				'104.210.140.128/28',
				'51.8.102.0/24',
				'135.234.64.0/24',
			),

			// Perplexity Search Bot.
			// https://www.perplexity.com/perplexitybot.json.
			'PerplexityBot' => array(
				'107.20.236.150/32',
				'3.224.62.45/32',
				'18.210.92.235/32',
				'3.222.232.239/32',
				'3.211.124.183/32',
				'3.231.139.107/32',
				'18.97.1.228/30',
				'18.97.9.96/29',
			),

			// YandexBot.
			// https://yandex.com/support/webmaster/en/robot-workings/check-yandex-robots.html.
			'YandexBot'     => array(
				'5.45.192.0/18',
				'5.255.192.0/18',
				'37.9.64.0/18',
				'37.140.128.0/18',
				'77.88.0.0/18',
				'84.252.160.0/19',
				'87.250.224.0/19',
				'90.156.176.0/22',
				'93.158.128.0/18',
				'95.108.128.0/17',
				'141.8.128.0/18',
				'178.154.128.0/18',
				'213.180.192.0/19',
				'185.32.187.0/24',
			),

		);

		/**
		 * Define the permitted user agents and their IP address ranges that can bypass
		 * Restrict Content to index content for search engines.
		 *
		 * @since   2.4.2
		 *
		 * @param   array   $permitted  Permitted user agent and IP address ranges.
		 */
		$permitted_user_agent_ip_ranges = apply_filters( 'convertkit_output_restrict_content_is_crawler_permitted_user_agent_ip_ranges', $permitted_user_agent_ip_ranges );

		// Not a crawler if no user agent defined or client IP address defined.
		if ( ! array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) || ! array_key_exists( 'REMOTE_ADDR', $_SERVER ) ) {
			return false;
		}

		// Iterate through permitted crawler IP addresses.
		foreach ( $permitted_user_agent_ip_ranges as $permitted_user_agent => $permitted_ip_addresses ) {
			// Skip this user agent's IP addresses if the client user agent doesn't contain this user agent.
			if ( stripos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), $permitted_user_agent ) === false ) {
				continue;
			}

			// Check IP address.
			foreach ( $permitted_ip_addresses as $permitted_ip_range ) {
				if ( ! $this->ip_in_range( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ), $permitted_ip_range ) ) {
					continue;
				}

				// The client user agent and IP address match a known crawler and its IP address.
				// This is a crawler.
				return true;
			}
		}

		// If here, the client IP address isn't from a crawler.
		return false;

	}

	/**
	 * Determines if the given IP address falls within the given CIDR range.
	 *
	 * @since   2.4.2
	 *
	 * @param   string $ip     Client IP Address (e.g. 127.0.0.1).
	 * @param   string $range  IP Address and bits (e.g. 127.0.0.1/27).
	 * @return  bool           Client IP Address matches range.
	 */
	public function ip_in_range( $ip, $range ) {

		// Return false if the IP address isn't valid.
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		// Return false if the range doesn't include the CIDR.
		if ( strpos( $range, '/' ) === false ) {
			return false;
		}

		// Get subnet and bits from range.
		list( $subnet, $bits ) = explode( '/', $range );

		// Return false if the CIDR isn't numerical.
		if ( ! is_numeric( $bits ) ) {
			return false;
		}

		// Cast CIDR to integer.
		$bits = (int) $bits;

		// Return false if the CIDR is not wihtin the permitted range.
		if ( $bits < 0 || $bits > 32 ) {
			return false;
		}

		// Convert to long representation.
		$ip     = ip2long( $ip );
		$subnet = ip2long( $subnet );
		$mask   = -1 << ( 32 - $bits );

		// If the supplied subnet wasn't correctly aligned.
		$subnet &= $mask;

		return ( $ip & $mask ) === $subnet;

	}

}
