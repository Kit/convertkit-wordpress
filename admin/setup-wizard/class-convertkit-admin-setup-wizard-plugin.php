<?php
/**
 * ConvertKit Admin Setup Wizard class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Provides a UI for setting up the ConvertKit Plugin when activated for the
 * first time.
 *
 * If the Plugin has previously been configured (i.e. settings exist in the database),
 * this UI isn't triggered on activation.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Setup_Wizard_Plugin extends ConvertKit_Admin_Setup_Wizard {

	/**
	 * Holds the ConvertKit Forms resource class.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|ConvertKit_Resource_Forms
	 */
	public $forms = false;

	/**
	 * Holds the ConvertKit API class.
	 *
	 * @since   2.5.0
	 *
	 * @var     bool|ConvertKit_API_V4
	 */
	public $api = false;

	/**
	 * Holds the ConvertKit Settings class.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|ConvertKit_Settings
	 */
	public $settings = false;

	/**
	 * Holds the URL to the most recent WordPress Post, used when previewing a Form below a Post
	 * on the frontend site.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|string
	 */
	public $preview_post_url = false;

	/**
	 * Holds the URL to the most recent WordPress Page, used when previewing a Form below a Page
	 * on the frontend site.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|string
	 */
	public $preview_page_url = false;

	/**
	 * The required user capability to access the setup wizard.
	 *
	 * @since   2.3.2
	 *
	 * @var     string
	 */
	public $required_capability = 'edit_posts';

	/**
	 * The programmatic name for this wizard.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     string
	 */
	public $page_name = 'convertkit-setup';

	/**
	 * The URL to take the user to when they click the Exit link.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     string
	 */
	public $exit_url = 'options-general.php?page=_wp_convertkit_settings';

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.9.8.4
	 */
	public function __construct() {

		// Setup API and settings classes.
		$this->api      = new ConvertKit_API_V4( CONVERTKIT_OAUTH_CLIENT_ID, CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI, false, false, false, 'setup_wizard' );
		$this->settings = new ConvertKit_Settings();

		// Define details for each step in the setup process.
		$this->steps = array(
			1 => array(
				'name'        => __( 'Connect', 'convertkit' ),
				'next_button' => array(
					'label' => __( 'Connect', 'convertkit' ),
					'link'  => $this->api->get_oauth_url( admin_url( 'options.php?page=convertkit-setup&step=2' ), get_site_url() ),
				),
			),
			2 => array(
				'name'        => __( 'Configuration', 'convertkit' ),
				'next_button' => array(
					'label' => __( 'Finish Setup', 'convertkit' ),
				),
			),
			3 => array(
				'name' => __( 'Done', 'convertkit' ),
			),
		);

		// Register link to Setup Wizard below Plugin Name at Plugins > Installed Plugins.
		add_filter( 'convertkit_plugin_screen_action_links', array( $this, 'add_setup_wizard_link_on_plugins_screen' ) );

		add_action( 'admin_init', array( $this, 'maybe_redirect_to_setup_screen' ), 9999 );
		add_action( 'convertkit_admin_setup_wizard_process_form_convertkit-setup', array( $this, 'process_form' ) );
		add_action( 'convertkit_admin_setup_wizard_load_screen_data_convertkit-setup', array( $this, 'load_screen_data' ) );

		// Call parent class constructor.
		parent::__construct();

	}

	/**
	 * Add a link to the Setup Wizard below the Plugin Name on the WP_List_Table at Plugins > Installed Plugins.
	 *
	 * @since   2.1.2
	 *
	 * @param   array $links  HTML Links.
	 * @return  array           HTML Links
	 */
	public function add_setup_wizard_link_on_plugins_screen( $links ) {

		return array_merge(
			$links,
			array(
				'setup_wizard' => sprintf(
					'<a href="%s">%s</a>',
					add_query_arg(
						array(
							'page' => $this->page_name,
						),
						admin_url( 'options.php' )
					),
					__( 'Setup Wizard', 'convertkit' )
				),
			)
		);

	}

	/**
	 * Redirects to the setup screen if a transient was created on Plugin activation,
	 * and the Plugin has no API Key and Secret configured.
	 *
	 * @since   1.9.8.4
	 */
	public function maybe_redirect_to_setup_screen() {

		// If no transient was set by the Plugin's activation routine, don't redirect to the setup screen.
		// This transient will only exist for 30 seconds by design, so we don't hijack a later WordPress
		// Admin screen request.
		if ( ! get_transient( $this->page_name ) ) {
			return;
		}

		// Delete the transient, so we don't redirect again.
		delete_transient( $this->page_name );

		// Bail if the user doesn't have access.
		if ( ! $this->user_has_access() ) {
			return;
		}

		// Check if any settings exist.
		// If they do, the Plugin has already been setup, so no need to show the setup screen.
		$settings = new ConvertKit_Settings();
		if ( $settings->has_access_and_refresh_token() ) {
			return;
		}

		// Show the setup screen.
		wp_safe_redirect( admin_url( 'options.php?page=' . $this->page_name ) );
		exit;

	}

	/**
	 * Process posted data from the submitted form.
	 *
	 * @since   1.9.8.4
	 *
	 * @param   int $step   Current step.
	 */
	public function process_form( $step ) {

		// Depending on the step, process the form data.
		switch ( $step ) {
			case 2:
				// If an error occured from OAuth i.e. the user did not authorize, show it now.
				if ( array_key_exists( 'error', $_REQUEST ) && array_key_exists( 'error_description', $_REQUEST ) ) {
					// Decrement the step.
					$this->step  = ( $this->step - 1 );
					$this->error = sanitize_text_field( wp_unslash( $_REQUEST['error_description'] ) );
					return;
				}

				// Bail if no authorization code is included in the request.
				if ( ! array_key_exists( 'code', $_REQUEST ) ) {
					return;
				}

				// Sanitize token.
				$authorization_code = sanitize_text_field( wp_unslash( $_REQUEST['code'] ) );

				// Exchange the authorization code and verifier for an access token.
				$result = $this->api->get_access_token( $authorization_code );

				// Show an error message if we could not fetch the access token.
				if ( is_wp_error( $result ) ) {
					// Decrement the step.
					$this->step  = ( $this->step - 1 );
					$this->error = $result->get_error_message();
					return;
				}

				// Store Access Token, Refresh Token and expiry.
				$this->settings->save(
					array(
						'access_token'  => $result['access_token'],
						'refresh_token' => $result['refresh_token'],
						'token_expires' => ( time() + $result['expires_in'] ),
					)
				);
				break;

			case 3:
				// Run security checks.
				if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
					return;
				}
				if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), $this->page_name ) ) {
					$this->error = __( 'Invalid nonce specified.', 'convertkit' );
					return;
				}

				// Save Default Page and Post Form settings.
				$settings = new ConvertKit_Settings();
				$settings->save(
					array(
						'post_form'      => ( isset( $_POST['post_form'] ) ? sanitize_text_field( wp_unslash( $_POST['post_form'] ) ) : '0' ),
						'page_form'      => ( isset( $_POST['page_form'] ) ? sanitize_text_field( wp_unslash( $_POST['page_form'] ) ) : '0' ),
						'usage_tracking' => ( isset( $_POST['usage_tracking'] ) ? 'on' : '' ),
					)
				);
				break;
		}

	}

	/**
	 * Load any data into class variables for the given setup wizard name and current step.
	 *
	 * @since   1.9.8.4
	 *
	 * @param   int $step   Current step.
	 */
	public function load_screen_data( $step ) {

		// If this wizard is being served in a modal window, change the flow.
		if ( $this->is_modal() ) {
			switch ( $step ) {
				case 1:
					// Setup API.
					$api = new ConvertKit_API_V4( CONVERTKIT_OAUTH_CLIENT_ID, CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI );

					// Permit wp_safe_redirect to redirect to app.kit.com.
					add_filter(
						'allowed_redirect_hosts',
						function ( $hosts ) {

							return array_merge(
								$hosts,
								array(
									'app.kit.com',
								)
							);

						}
					);

					// Redirect to OAuth.
					wp_safe_redirect( $api->get_oauth_url( admin_url( 'options.php?page=convertkit-setup&step=2&convertkit-modal=1' ), get_site_url() ) );
					die();

				case 2:
					// Close modal.
					$this->maybe_close_modal();
					break;
			}
		}

		switch ( $step ) {
			case 2:
				// Re-load settings class now that the API Key and Secret has been defined.
				$this->settings = new ConvertKit_Settings();

				// Fetch Forms.
				$this->forms = new ConvertKit_Resource_Forms( 'setup_wizard' );
				$this->forms->refresh();

				// If no Forms exist in ConvertKit, change the next button label and make it a link to reload
				// the screen.
				if ( ! $this->forms->exist() ) {
					$this->steps[2]['next_button']['label'] = __( 'I\'ve created a form in Kit', 'convertkit' );
					$this->steps[2]['next_button']['link']  = add_query_arg(
						array(
							'page' => $this->page_name,
							'step' => 2,
						),
						admin_url( 'options.php' )
					);
				}

				// Fetch a Post and a Page, appending the preview nonce to their URLs.
				$this->preview_post_url = WP_ConvertKit()->get_class( 'preview_output' )->get_preview_form_url( 'post' );
				$this->preview_page_url = WP_ConvertKit()->get_class( 'preview_output' )->get_preview_form_url( 'page' );
				break;
		}

	}

}
