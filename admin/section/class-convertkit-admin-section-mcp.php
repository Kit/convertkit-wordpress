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
	 * The authorization header to display on screen.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool|string
	 */
	private $authorization_header = false;

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

		$this->maybe_generate_authentication_header();
		$this->maybe_revoke_application_password();

		// Register and maybe output notices for this settings screen, and the Intercom messenger.
		if ( $this->on_settings_screen( $this->name ) ) {
			add_action( 'convertkit_settings_base_render_before', array( $this, 'maybe_output_notices' ) );
		}

		// Enqueue scripts and CSS.
		add_action( 'convertkit_admin_settings_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		parent::__construct();

	}

	/**
	 * Generates the authentication header to display on screen, if the user
	 * has just created an Application Password.
	 *
	 * @since   3.4.0
	 */
	private function maybe_generate_authentication_header() {

		// Bail if we're not on the settings screen.
		if ( ! $this->on_settings_screen( $this->name ) ) {
			return;
		}

		// Bail if nonce verification fails.
		if ( ! isset( $_REQUEST['_convertkit_settings_mcp_create_application_password'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_convertkit_settings_mcp_create_application_password'] ), 'convertkit-mcp-create-application-password' ) ) {
			return;
		}

		// Bail if the user login and password are not included in the request.
		if ( ! isset( $_REQUEST['user_login'] ) || ! isset( $_REQUEST['password'] ) ) {
			return;
		}

		// Build the authorization header to display on screen.
		$user_login                 = sanitize_text_field( wp_unslash( $_REQUEST['user_login'] ) );
		$password                   = sanitize_text_field( wp_unslash( $_REQUEST['password'] ) );
		$this->authorization_header = base64_encode( $user_login . ':' . $password ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

	}

	/**
	 * Revokes the Application Password, if the user clicked the Revoke Application Password button.
	 *
	 * @since   3.4.0
	 */
	private function maybe_revoke_application_password() {

		// Bail if we're not on the settings screen.
		if ( ! $this->on_settings_screen( $this->name ) ) {
			return;
		}

		// Bail if nonce verification fails.
		if ( ! isset( $_REQUEST['_convertkit_settings_mcp_revoke_application_password'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_convertkit_settings_mcp_revoke_application_password'] ), 'convertkit-mcp-revoke-application-password' ) ) {
			return;
		}

		// Get the Application Password UUID.
		$application_password_uuid = $this->get_application_password_uuid();

		// Bail if no Application Password UUID exists.
		if ( ! $application_password_uuid ) {
			return;
		}

		// Revoke the Application Password.
		$result = WP_Application_Passwords::delete_application_password( get_current_user_id(), $application_password_uuid );
		if ( is_wp_error( $result ) ) {
			$this->output_error( $result->get_error_message() );
			return;
		}

		// Reload the settings screen.
		wp_safe_redirect( $this->get_settings_url() );
		exit();

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
		if ( $this->get_application_password_uuid() ) {
			add_settings_field(
				'connect',
				__( 'Connection', 'convertkit' ),
				array( $this, 'instructions_disconnect_callback' ),
				$this->settings_key,
				$this->name
			);
		} else {
			add_settings_field(
				'connect',
				__( 'Connection', 'convertkit' ),
				array( $this, 'connect_callback' ),
				$this->settings_key,
				$this->name
			);
		}

	}

	/**
	 * Prints help info for this section
	 *
	 * @since   3.4.0
	 */
	public function print_section_info() {

		?>
		<span class="convertkit-beta-label"><?php esc_html_e( 'Beta', 'convertkit' ); ?></span>
		<p class="description"><?php esc_html_e( 'Defines whether AI clients can connect to the Kit Plugin using MCP, and provides instructions for connecting.', 'convertkit' ); ?></p>
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
		// We don't use add_query_arg(), as rawurlencode() is needed for authorize-application.php's JS to work correctly.
		$authorize_url = admin_url( 'authorize-application.php' )
			. '?app_name=' . rawurlencode( CONVERTKIT_MCP_APP_NAME )
			. '&success_url=' . rawurlencode(
				$this->get_settings_url(
					array(
						'_convertkit_settings_mcp_create_application_password' => wp_create_nonce( 'convertkit-mcp-create-application-password' ),
					)
				)
			)
			. '&reject_url=' . rawurlencode( $this->get_settings_url() );
		?>
		<p>
			<?php esc_html_e( 'Click Create Application Password to create a password that AI clients can use to connect to this site\'s MCP server.', 'convertkit' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_attr( $authorize_url ); ?>" id="convertkit-settings-mcp-create-application-password" class="button button-primary">
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

		// Build disconnect URL.
		$disconnect_url = $this->get_settings_url( array( '_convertkit_settings_mcp_revoke_application_password' => wp_create_nonce( 'convertkit-mcp-revoke-application-password' ) ) );

		// Fetch query parameters to build the Basic auth header.
		if ( $this->authorization_header ) {
			?>
			<p>
				<strong><?php esc_html_e( 'Authorization Header:', 'convertkit' ); ?></strong>
				<code id="kit-authorization-header">Basic <?php echo esc_html( $this->authorization_header ); ?></code>
			</p>
			<p>
				<?php esc_html_e( 'Copy the above. It won\'t be displayed again. If you lose this, you\'ll need to revoke the Application Password and create a new one.', 'convertkit' ); ?>
			</p>
			<?php
		} else {
			?>
			<p>
				<?php esc_html_e( 'An Application Password was previously created for this Plugin. It is not displayed here for security.', 'convertkit' ); ?>
				<br />
				<?php esc_html_e( 'If you forgot your Application Password, you can revoke it using the Revoke Application Password button below, and then create a new one.', 'convertkit' ); ?>
			</p>
			<?php
		}
		?>
		<p>
			<a href="<?php echo esc_url( $disconnect_url ); ?>" id="convertkit-settings-mcp-revoke-application-password" class="button button-secondary"><?php esc_html_e( 'Revoke Application Password', 'convertkit' ); ?></a>
		</p>

		<?php
		// Build server URL and pre-encoded Basic auth header for use in the
		// per-client configuration snippets below.
		$server_url  = ConvertKit_MCP::get_server_url();
		$auth_header = $this->authorization_header
			? 'Basic ' . $this->authorization_header
			: __( 'Your base64 encoded username and application password', 'convertkit' );

		// Claude desktop / Cline JSON.
		//
		// The Authorization header value is inlined (not passed via the
		// `env` block + `${VAR}` substitution as the mcp-remote docs
		// suggest), because mcp-remote's variable substitution is unreliable
		// with Basic auth values that contain `$` characters in their
		// base64 payload — the substitution silently leaves the literal
		// `${KIT_AUTH}` string in place, producing 401s.
		$claude_desktop_config = wp_json_encode(
			array(
				'mcpServers' => array(
					'kit-wordpress' => array(
						'command' => 'npx',
						'args'    => array(
							'-y',
							'mcp-remote',
							$server_url,
							'--header',
							'Authorization: ' . $auth_header,
						),
					),
				),
			),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);

		// Cursor JSON.
		$cursor_config = wp_json_encode(
			array(
				'mcpServers' => array(
					'kit-wordpress' => array(
						'url'     => $server_url,
						'headers' => array(
							'Authorization' => $auth_header,
						),
					),
				),
			),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);
		?>

		<h3><?php esc_html_e( 'Claude', 'convertkit' ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: %s: Path to Claude desktop config file. */
				esc_html__( 'Add the following to your %s file, then restart Claude desktop:', 'convertkit' ),
				'<code>claude_desktop_config.json</code>'
			);
			?>
			<br />
			macOS: <code>~/Library/Application Support/Claude/claude_desktop_config.json</code>
			<br />
			Windows: <code>%APPDATA%\Claude\claude_desktop_config.json</code>
		</p>
		<pre><code><?php echo esc_html( $claude_desktop_config ); ?></code></pre>

		<h3><?php esc_html_e( 'Claude Code', 'convertkit' ); ?></h3>
		<p>
			<?php esc_html_e( 'Run the following command in your terminal.', 'convertkit' ); ?>
			<br />
			<code>
				<?php
				printf(
					'claude mcp add --transport http kit-wordpress %s --header "Authorization: %s"',
					esc_html( $server_url ),
					esc_html( $auth_header )
				);
				?>
			</code>
		</p>

		<h3><?php esc_html_e( 'Cursor', 'convertkit' ); ?></h3>
		<p>
			<?php
			printf(
				/* translators: %s: Path to Cursor MCP config file. */
				esc_html__( 'Add the following to your %s file, then restart Cursor:', 'convertkit' ),
				'<code>~/.cursor/mcp.json</code>'
			);
			?>
		</p>
		<pre><code><?php echo esc_html( $cursor_config ); ?></code></pre>

		<h3><?php esc_html_e( 'Other clients', 'convertkit' ); ?></h3>
		<p>
			<?php esc_html_e( 'For any other MCP client, provide it with the following:', 'convertkit' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Server URL:', 'convertkit' ); ?></strong>
			<code><?php echo esc_html( $server_url ); ?></code>
		</p>
		<p>
			<strong><?php esc_html_e( 'Authorization header:', 'convertkit' ); ?></strong>
			<code><?php echo esc_html( $auth_header ); ?></code>
		</p>
		<?php

	}

	/**
	 * Returns the URL for the this settings screen.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $query_args   Query arguments to add to the URL.
	 * @return  string
	 */
	private function get_settings_url( $query_args = array() ) {

		return add_query_arg(
			array_merge(
				array(
					'page' => '_wp_convertkit_settings',
					'tab'  => $this->name,
				),
				$query_args
			),
			admin_url( 'options-general.php' )
		);

	}

	/**
	 * Finds the UUID of the most recently-created Application Password for the
	 * currently logged in user
	 *
	 * @since   3.4.0
	 *
	 * @return  bool|string
	 */
	private function get_application_password_uuid() {

		// Get the user's Application Passwords.
		$passwords = WP_Application_Passwords::get_user_application_passwords( get_current_user_id() );

		// Return false if no Application Passwords exist.
		if ( empty( $passwords ) ) {
			return false;
		}

		// Iterate through the Application Passwords and return the password that matches the app name.
		foreach ( $passwords as $password ) {
			if ( $password['name'] === CONVERTKIT_MCP_APP_NAME ) {
				return $password['uuid'];
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
