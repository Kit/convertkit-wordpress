<?php
/**
 * ConvertKit Settings MCP Settings class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers MCP Settings that can be edited at Settings > Kit > MCP.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Section_MCP extends ConvertKit_Admin_Section_Base {

	/**
	 * Constructor.
	 *
	 * @since   3.4.0
	 */
	public function __construct() {

		// Define the class that reads/writes settings.
		$this->settings = new ConvertKit_Settings_MCP();

		// Define the settings key.
		$this->settings_key = $this->settings::SETTINGS_NAME;

		// Define the programmatic name, Title and Tab Text.
		$this->name     = 'mcp';
		$this->title    = __( 'MCP', 'convertkit' );
		$this->tab_text = __( 'MCP', 'convertkit' );

		// Identify that this is beta functionality.
		$this->is_beta = true;

		// Define settings sections.
		$this->settings_sections = array(
			'general' => array(
				'title'    => $this->title,
				'callback' => array( $this, 'print_section_info' ),
				'wrap'     => true,
			),
		);

		// Register and maybe output notices for this settings screen, and the Intercom messenger.
		if ( $this->on_settings_screen( $this->name ) ) {
			add_action( 'convertkit_settings_base_render_before', array( $this, 'maybe_output_notices' ) );
		}

		// Enqueue scripts and CSS.
		add_action( 'convertkit_admin_settings_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		parent::__construct();

	}

	/**
	 * Enqueues scripts for the Settings > MCP screen.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $section    Settings section / tab (general|tools|restrict-content|broadcasts|mcp).
	 */
	public function enqueue_scripts( $section ) {

		// Bail if we're not on the MCP section.
		if ( $section !== $this->name ) {
			return;
		}

		// Enqueue JS.
		wp_enqueue_script( 'convertkit-admin-settings-conditional-display', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/settings-conditional-display.js', array( 'jquery' ), CONVERTKIT_PLUGIN_VERSION, true );

	}

	/**
	 * Registers settings fields for this section.
	 *
	 * @since   3.4.0
	 */
	public function register_fields() {

		// Enable.
		add_settings_field(
			'enabled',
			__( 'Enable MCP Server', 'convertkit' ),
			array( $this, 'enabled_callback' ),
			$this->settings_key,
			$this->name,
			array(
				'name'        => 'enabled',
				'label_for'   => 'enabled',
				'label'       => __( 'When enabled, allows AI clients to connect to the Kit Plugin using MCP.', 'convertkit' ),
				'description' => sprintf(
					'%s<br /><code>%s</code>',
					__( 'MCP server URL:', 'convertkit' ),
					esc_url( ConvertKit_MCP::get_server_url() )
				),
			)
		);

		// Bail if MCP is not enabled — none of the connect UI applies.
		if ( ! $this->settings->enabled() ) {
			return;
		}

		// If an Application Password exists for this Plugin, display the instructions and revoke section.
		add_settings_field(
			'connect',
			__( 'Connect a client', 'convertkit' ),
			array( $this, $this->get_application_password() === false ? 'connect_callback' : 'instructions_disconnect_callback' ),
			$this->settings_key,
			$this->name
		);

	}

	/**
	 * Prints help info for this section
	 *
	 * @since   3.4.0
	 */
	public function print_section_info() {

		?>
		<span class="convertkit-beta-label"><?php esc_html_e( 'Beta', 'convertkit' ); ?></span>
		<p class="description"><?php esc_html_e( 'Defines whether AI clients can connect to the Kit Plugin using MCP.', 'convertkit' ); ?></p>
		<?php

	}


	/**
	 * Returns the URL for the ConvertKit documentation for this setting section.
	 *
	 * @since   3.4.0
	 *
	 * @return  string  Documentation URL.
	 */
	public function documentation_url() {

		return '#';

	}

	/**
	 * Renders the input for the Enable setting.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $args   Setting field arguments (name,description).
	 */
	public function enabled_callback( $args ) {

		// Output field.
		$this->output_checkbox_field(
			$args['name'],
			'on',
			$this->settings->enabled(),
			$args['label'],
			$args['description'],
			array( 'convertkit-conditional-display' )
		);

	}

	/**
	 * Renders the Connect a client setting, to allow the user to generate an Application Password
	 * for this Plugin which is used to connect AI clients to the MCP Server.
	 *
	 * @since   3.4.0
	 */
	public function connect_callback() {

		// Build the WordPress authorize-application.php URL.
		// See: https://developer.wordpress.org/advanced-administration/security/application-passwords/.
		$authorize_url = add_query_arg(
			array(
				'app_name'    => CONVERTKIT_MCP_APP_NAME,
				'success_url' => $this->get_settings_url( array( 'application_password' => 1 ) ),
				'reject_url'  => $this->get_settings_url(),
			),
			admin_url( 'authorize-application.php' )
		);
		?>
		<p>
			<?php esc_html_e( 'Click Create Application Password to create a password that AI clients can use to connect to this site\'s MCP server.', 'convertkit' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( $authorize_url ); ?>" class="button button-primary">
				<?php esc_html_e( 'Create Application Password', 'convertkit' ); ?>
			</a>
		</p>
		<?php

	}

	/**
	 * Renders the instructions and Disconnect section.
	 *
	 * @since   3.4.0
	 */
	public function instructions_disconnect_callback() {

		// Fetch query parameters to build the Basic auth header.
		if ( array_key_exists( 'user_login', $_REQUEST ) && array_key_exists( 'password', $_REQUEST ) ) {
			$user_login = $_REQUEST['user_login'];
			$password = $_REQUEST['password'];
			$basic = base64_encode( $user_login . ':' . $password );
		}
		?>
		<p>
			<?php esc_html_e( 'Copy the configuration for your AI client below now — you will not see the password again.', 'convertkit' ); ?>

			<?php
			$application_password = $this->get_application_password();
			var_dump( $application_password );
			?>
		</p>
		<?php

	}

	/**
	 * Finds the UUID of the most recently-created Application Password for the
	 * currently logged in user
	 *
	 * @since   3.4.0
	 *
	 * @return  bool|string
	 */
	private function get_application_password() {

		// Get the user's Application Passwords.
		$passwords = WP_Application_Passwords::get_user_application_passwords( get_current_user_id() );

		// Return false if no Application Passwords exist.
		if ( empty( $passwords ) ) {
			return false;
		}

		// Iterate through the Application Passwords and return the password that matches the app name.
		foreach ( $passwords as $password ) {
			if ( $password['name'] === self::APP_NAME ) {
				return $password;
			}
		}

		return false;

	}

}

// Bootstrap.
add_filter(
	'convertkit_admin_settings_register_sections',
	function ( $sections ) {

		// Don't register the MCP section if the Abilities API is not available (WordPress < 6.9).
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return $sections;
		}

		// Don't register the MCP section if PHP 7.4+ is not installed.
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			return $sections;
		}

		$sections['mcp'] = new ConvertKit_Admin_Section_MCP();
		return $sections;

	}
);
