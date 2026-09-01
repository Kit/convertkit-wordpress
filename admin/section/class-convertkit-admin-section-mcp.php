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
	 * Whether the Kit account is on a paid plan, once queried.
	 *
	 * @since   3.4.1
	 *
	 * @var     bool|null
	 */
	private $is_paid_plan = null;

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
			'connect' => array(
				'title'    => __( 'Connect an AI client', 'convertkit' ),
				'callback' => array( $this, 'print_section_info_connect' ),
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
		wp_enqueue_script( 'convertkit-admin-settings-mcp', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/settings-mcp.js', array(), CONVERTKIT_PLUGIN_VERSION, true );

		// Localize the strings displayed when copying a configuration snippet to the clipboard.
		wp_localize_script(
			'convertkit-admin-settings-mcp',
			'convertkit_admin_settings_mcp',
			array(
				'copy'   => __( 'Copy', 'convertkit' ),
				'copied' => __( 'Copied', 'convertkit' ),
				'failed' => __( 'Press Ctrl/Cmd + C to copy', 'convertkit' ),
			)
		);

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
	 * Renders the upgrade CTA when the connected Kit account is on
	 * the free plan.
	 *
	 * @since   3.4.0
	 */
	public function output_upgrade_required_message() {

		?>
		<p>
			<?php esc_html_e( 'The Kit WordPress MCP is available on paid Kit plans. Upgrade your Kit account to connect AI clients to your WordPress site.', 'convertkit' ); ?>
		</p>
		<p>
			<a href="https://app.kit.com/account_settings/billing" class="button button-primary" target="_blank">
				<?php esc_html_e( 'Upgrade Kit Account', 'convertkit' ); ?>
			</a>
		</p>
		<?php

	}

	/**
	 * Renders the input for the Enable setting.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $args   Setting field arguments (name,description).
	 */
	public function enabled_callback( $args ) {

		// If the user doesn't have a paid plan, show the upgrade required message.
		if ( ! $this->is_paid_plan() ) {
			// Disable saving settings.
			$this->save_disabled = true;

			$this->output_upgrade_required_message();
			return;
		}

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
	 * Renders the connection instructions, comprising of the steps the user needs
	 * to complete to connect an AI client to this site's MCP server.
	 *
	 * @since   3.4.1
	 */
	public function print_section_info_connect() {

		// Don't output anything if the Kit account isn't on a paid plan, as the
		// upgrade message is displayed in the section above.
		if ( ! $this->is_paid_plan() ) {
			return;
		}

		// The output is wrapped in its own container, so that the settings screen's
		// styles for a beta section's immediate children aren't applied to it.
		echo '<div class="convertkit-mcp">';

		if ( ! $this->settings->enabled() ) {
			// The MCP server isn't enabled; tell the user how to enable it.
			?>
			<p class="description">
				<?php esc_html_e( 'Enable the MCP server above and click Save Changes, to then connect an AI client to this site.', 'convertkit' ); ?>
			</p>
			<?php
		} elseif ( $this->application_passwords_available() ) {
			// Get the Application Password for this Plugin, if one exists.
			$application_password = $this->get_application_password();
			?>

			<ol class="convertkit-mcp-steps">
				<li>
					<h3><?php esc_html_e( 'Enable the MCP server', 'convertkit' ); ?></h3>
					<p class="description">
						<?php
						printf(
							/* translators: %s: MCP server URL. */
							esc_html__( 'Done. AI clients connect to %s', 'convertkit' ),
							'<code>' . esc_url( ConvertKit_MCP::get_server_url() ) . '</code>'
						);
						?>
					</p>
				</li>

				<li>
					<h3><?php esc_html_e( 'Create an Application Password', 'convertkit' ); ?></h3>
					<?php
					if ( is_array( $application_password ) ) {
						$this->output_application_password( $application_password );
					} else {
						$this->output_create_application_password();
					}
					?>
				</li>

				<li>
					<h3><?php esc_html_e( 'Connect your AI client', 'convertkit' ); ?></h3>
					<?php
					if ( ! is_array( $application_password ) ) {
						?>
						<p class="description">
							<?php esc_html_e( 'Create an Application Password above to display the configuration for your AI client.', 'convertkit' ); ?>
						</p>
						<?php
					} else {
						$this->output_environment_notices();
						$this->output_client_instructions();
					}
					?>
				</li>
			</ol>

			<?php
			// Output a summary of what the AI client can do once connected.
			$this->output_capabilities_summary();
		}

		echo '</div>';

	}

	/**
	 * Returns whether WordPress' Application Passwords feature is available for
	 * the site and the current user, outputting an error message if it isn't.
	 *
	 * WordPress disables Application Passwords when the site isn't served over
	 * HTTPS and isn't a local environment, meaning no AI client can authenticate.
	 *
	 * @since   3.4.1
	 *
	 * @return  bool    Application Passwords are available.
	 */
	private function application_passwords_available() {

		// Application Passwords are disabled for the site.
		if ( ! wp_is_application_passwords_available() ) {
			$this->output_error(
				sprintf(
					/* translators: %1$s: Site URL, %2$s: WP_ENVIRONMENT_TYPE constant. */
					__( 'Application Passwords are disabled on this site, so AI clients cannot authenticate. WordPress disables Application Passwords when a site is not served over HTTPS. Serve %1$s over HTTPS, or set %2$s to local on a development site, and then reload this screen.', 'convertkit' ),
					home_url(),
					'WP_ENVIRONMENT_TYPE'
				)
			);
			return false;
		}

		// Application Passwords are disabled for this user.
		if ( ! wp_is_application_passwords_available_for_user( get_current_user_id() ) ) {
			$this->output_error( __( 'Application Passwords are disabled for your WordPress user, so you cannot create the password an AI client needs. Ask an administrator to enable Application Passwords for your user.', 'convertkit' ) );
			return false;
		}

		return true;

	}

	/**
	 * Renders the Create Application Password button, which sends the user to
	 * WordPress' authorize-application.php screen.
	 *
	 * @since   3.4.1
	 */
	private function output_create_application_password() {

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
		<p class="description">
			<?php
			printf(
				/* translators: %s: WordPress user's display name. */
				esc_html__( 'An AI client signs in to this site using an Application Password. The client will act as %s, and can only do what that user can do.', 'convertkit' ),
				'<strong>' . esc_html( wp_get_current_user()->display_name ) . '</strong>'
			);
			?>
		</p>
		<p>
			<a href="<?php echo esc_attr( $authorize_url ); ?>" id="convertkit-settings-mcp-create-application-password" class="button button-primary">
				<?php esc_html_e( 'Create Application Password', 'convertkit' ); ?>
			</a>
		</p>
		<?php

	}

	/**
	 * Renders the Application Password's details, the authorization header (if the
	 * password was just created), and the Revoke Application Password button.
	 *
	 * @since   3.4.1
	 *
	 * @param   array $application_password   Application Password.
	 */
	private function output_application_password( $application_password ) {

		// Build disconnect URL.
		$disconnect_url = $this->get_settings_url( array( '_convertkit_settings_mcp_revoke_application_password' => wp_create_nonce( 'convertkit-mcp-revoke-application-password' ) ) );

		// Define the date and time format used for the Application Password's dates.
		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		?>
		<p class="description">
			<?php
			printf(
				/* translators: %1$s: WordPress user's display name, %2$s: Date and time the Application Password was created. */
				esc_html__( 'AI clients using this Application Password act as %1$s, and can only do what that user can do. Created %2$s.', 'convertkit' ),
				'<strong>' . esc_html( wp_get_current_user()->display_name ) . '</strong>',
				esc_html( (string) wp_date( $date_format, $application_password['created'] ) )
			);

			if ( ! empty( $application_password['last_used'] ) ) {
				echo ' ';
				printf(
					/* translators: %s: Date and time the Application Password was last used. */
					esc_html__( 'Last used %s.', 'convertkit' ),
					esc_html( (string) wp_date( $date_format, $application_password['last_used'] ) )
				);
			} else {
				echo ' ';
				esc_html_e( 'Not yet used by an AI client.', 'convertkit' );
			}
			?>
		</p>

		<?php
		if ( $this->authorization_header ) {
			?>
			<p>
				<strong><?php esc_html_e( 'Authorization header:', 'convertkit' ); ?></strong>
			</p>
			<?php
			$this->output_code_block( 'Basic ' . $this->authorization_header, 'kit-authorization-header' );
			?>
			<p class="description">
				<?php esc_html_e( 'Copy the above now. It won\'t be displayed again. If you lose it, revoke the Application Password and create a new one.', 'convertkit' ); ?>
			</p>
			<?php
		} else {
			?>
			<p class="description">
				<?php
				printf(
					/* translators: %s: Placeholder text displayed in the configuration snippets in place of the authorization header. */
					esc_html__( 'For security, WordPress only displays an Application Password once, at the point it is created. The configuration below therefore shows %s in place of your Application Password. If you no longer have it, revoke the Application Password and create a new one.', 'convertkit' ),
					'<code>BASE64_ENCODED_USERNAME_AND_APPLICATION_PASSWORD</code>'
				);
				?>
			</p>
			<?php
		}
		?>

		<p>
			<a href="<?php echo esc_url( $disconnect_url ); ?>" id="convertkit-settings-mcp-revoke-application-password" class="button button-secondary"><?php esc_html_e( 'Revoke Application Password', 'convertkit' ); ?></a>
		</p>
		<?php

	}

	/**
	 * Renders notices when this site's URL means that some AI clients won't be able
	 * to connect to the MCP server.
	 *
	 * @since   3.4.1
	 */
	private function output_environment_notices() {

		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );

		// Determine if this site is only reachable from this computer or local network,
		// meaning AI clients that run in the cloud cannot connect to it.
		$is_local = (
			in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true )
			|| (bool) preg_match( '/\.(local|localhost|test|dev|invalid|example)$/', $host )
		);

		if ( ! $is_local ) {
			return;
		}
		?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				printf(
					/* translators: %s: Site URL. */
					esc_html__( '%s is a local address. AI clients that run on this computer, such as Claude Desktop, Claude Code, Cursor and Codex, will connect. AI clients that run in the cloud, such as ChatGPT, cannot reach this site.', 'convertkit' ),
					'<code>' . esc_html( home_url() ) . '</code>'
				);
				?>
			</p>
		</div>
		<?php

	}

	/**
	 * Renders the configuration for each supported AI client, in a tabbed interface.
	 *
	 * @since   3.4.1
	 */
	private function output_client_instructions() {

		// Build the server URL and authorization header used in each client's configuration.
		// When the Application Password isn't available to display, a placeholder is used, so
		// that the configuration is still valid and shows where the header value belongs.
		$server_url  = ConvertKit_MCP::get_server_url();
		$auth_header = 'Basic ' . ( $this->authorization_header ? $this->authorization_header : 'BASE64_ENCODED_USERNAME_AND_APPLICATION_PASSWORD' );

		// Claude Desktop JSON.
		// Claude Desktop only supports remote MCP servers that authenticate using OAuth, so
		// mcp-remote is used to proxy requests to the MCP server, adding the authorization header.
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

		// Claude Code command.
		$claude_code_command = sprintf(
			'claude mcp add --transport http kit-wordpress %s --header "Authorization: %s"',
			$server_url,
			$auth_header
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

		// Codex TOML.
		$codex_config = '[mcp_servers.kit_wordpress]' . "\n"
			. 'url = "' . $server_url . '"' . "\n"
			. 'http_headers = { "Authorization" = "' . $auth_header . '" }';

		// Define the clients to display, in the order they should be displayed.
		$clients = array(
			'claude-desktop' => __( 'Claude Desktop', 'convertkit' ),
			'claude-code'    => __( 'Claude Code', 'convertkit' ),
			'cursor'         => __( 'Cursor', 'convertkit' ),
			'codex'          => __( 'Codex', 'convertkit' ),
			'other'          => __( 'Other clients', 'convertkit' ),
		);
		?>
		<div class="convertkit-mcp-clients">
			<ul class="convertkit-mcp-client-tabs">
				<?php
				$first_client = true;
				foreach ( $clients as $client => $label ) {
					?>
					<li>
						<button type="button" class="convertkit-mcp-client-tab<?php echo ( $first_client ? ' is-active' : '' ); ?>" data-client="<?php echo esc_attr( $client ); ?>">
							<?php echo esc_html( $label ); ?>
						</button>
					</li>
					<?php
					$first_client = false;
				}
				?>
			</ul>

			<div class="convertkit-mcp-client-panel is-active" data-client="claude-desktop">
				<p>
					<?php
					printf(
						/* translators: %s: Claude Desktop configuration file name. */
						esc_html__( 'Add the following to your %s file, then restart Claude Desktop:', 'convertkit' ),
						'<code>claude_desktop_config.json</code>'
					);
					?>
					<br />
					macOS: <code>~/Library/Application Support/Claude/claude_desktop_config.json</code>
					<br />
					Windows: <code>%APPDATA%\Claude\claude_desktop_config.json</code>
				</p>
				<?php $this->output_code_block( (string) $claude_desktop_config ); ?>
				<p class="description">
					<?php
					printf(
						/* translators: %1$s: mcp-remote, %2$s: Node.js. */
						esc_html__( 'Claude Desktop only connects to remote MCP servers that use OAuth, so %1$s is used to connect to this site. This requires %2$s to be installed on your computer.', 'convertkit' ),
						'<code>mcp-remote</code>',
						'<a href="https://nodejs.org/" target="_blank">Node.js</a>'
					);
					?>
				</p>
			</div>

			<div class="convertkit-mcp-client-panel" data-client="claude-code">
				<p>
					<?php esc_html_e( 'Run the following command in your terminal:', 'convertkit' ); ?>
				</p>
				<?php $this->output_code_block( $claude_code_command ); ?>
			</div>

			<div class="convertkit-mcp-client-panel" data-client="cursor">
				<p>
					<?php
					printf(
						/* translators: %s: Cursor configuration file name. */
						esc_html__( 'Add the following to your %s file, then restart Cursor:', 'convertkit' ),
						'<code>~/.cursor/mcp.json</code>'
					);
					?>
				</p>
				<?php $this->output_code_block( (string) $cursor_config ); ?>
			</div>

			<div class="convertkit-mcp-client-panel" data-client="codex">
				<p>
					<?php
					printf(
						/* translators: %s: Codex configuration file name. */
						esc_html__( 'Add the following to your %s file, then restart Codex:', 'convertkit' ),
						'<code>~/.codex/config.toml</code>'
					);
					?>
				</p>
				<?php $this->output_code_block( $codex_config ); ?>
				<p class="description">
					<?php
					printf(
						/* translators: %s: Codex configuration option. */
						esc_html__( 'Older versions of Codex require %s at the top of the configuration file to connect to remote MCP servers.', 'convertkit' ),
						'<code>experimental_use_rmcp_client = true</code>'
					);
					?>
				</p>
			</div>

			<div class="convertkit-mcp-client-panel" data-client="other">
				<p>
					<?php esc_html_e( 'For any other MCP client, use the following. The server uses the streamable HTTP transport, and authenticates using HTTP Basic authentication.', 'convertkit' ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Server URL:', 'convertkit' ); ?></strong>
				</p>
				<?php $this->output_code_block( $server_url ); ?>
				<p>
					<strong><?php esc_html_e( 'Authorization header:', 'convertkit' ); ?></strong>
				</p>
				<?php $this->output_code_block( $auth_header ); ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Renders a summary of what an AI client can do once connected.
	 *
	 * @since   3.4.1
	 */
	private function output_capabilities_summary() {

		?>
		<h3><?php esc_html_e( 'What your AI client can do', 'convertkit' ); ?></h3>
		<p class="description">
			<?php esc_html_e( 'Describe what you want in your own words; your AI client works out which tools to use.', 'convertkit' ); ?>
		</p>
		<ul class="convertkit-mcp-capabilities">
			<li><?php esc_html_e( 'Look up the Forms, Landing Pages, Products and Tags in your Kit account.', 'convertkit' ); ?></li>
			<li><?php esc_html_e( 'Add, list, change and remove Kit Forms, Form Triggers, Products and Broadcasts within a post or page\'s content.', 'convertkit' ); ?></li>
			<li><?php esc_html_e( 'Read and change a post or page\'s Kit settings, such as its Form, Landing Page, Tag and Member Content.', 'convertkit' ); ?></li>
			<li><?php esc_html_e( 'Read and change a category\'s Kit Form and Form Position.', 'convertkit' ); ?></li>
			<li><?php esc_html_e( 'Read and change this Plugin\'s General, Broadcasts and Member Content settings.', 'convertkit' ); ?></li>
		</ul>
		<p class="description">
			<?php esc_html_e( 'Your Kit account credentials, and your Form Entries, are never exposed to AI clients.', 'convertkit' ); ?>
		</p>
		<?php

		// Output a link to the documentation, if it's defined.
		if ( $this->documentation_url() === '#' ) {
			return;
		}
		?>
		<p>
			<a href="<?php echo esc_url( $this->documentation_url() ); ?>" target="_blank">
				<?php esc_html_e( 'Read the MCP documentation', 'convertkit' ); ?>
			</a>
		</p>
		<?php

	}

	/**
	 * Renders the given code in a code block, with a button to copy the code
	 * to the clipboard.
	 *
	 * @since   3.4.1
	 *
	 * @param   string $code   Code to display.
	 * @param   string $id     Optional ID attribute to assign to the code element.
	 */
	private function output_code_block( $code, $id = '' ) {

		?>
		<div class="convertkit-mcp-code">
			<pre><code<?php echo ( ! empty( $id ) ? ' id="' . esc_attr( $id ) . '"' : '' ); ?>><?php echo esc_html( $code ); ?></code></pre>
			<button type="button" class="button button-secondary convertkit-mcp-copy"><?php esc_html_e( 'Copy', 'convertkit' ); ?></button>
		</div>
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
	 * Returns whether the Kit account is on a paid plan.
	 *
	 * @since   3.4.1
	 *
	 * @return  bool
	 */
	private function is_paid_plan() {

		// If the result has already been fetched for this request, return it.
		if ( ! is_null( $this->is_paid_plan ) ) {
			return $this->is_paid_plan;
		}

		// Fetch the account resource and return the result.
		$account            = new ConvertKit_Resource_Account();
		$this->is_paid_plan = $account->is_paid_plan();
		return $this->is_paid_plan;

	}

	/**
	 * Finds the most recently-created Application Password for this Plugin, belonging
	 * to the currently logged in user.
	 *
	 * @since   3.4.1
	 *
	 * @return  bool|array
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
			if ( $password['name'] === CONVERTKIT_MCP_APP_NAME ) {
				return $password;
			}
		}

		return false;

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

		// Get the Application Password for this Plugin.
		$password = $this->get_application_password();

		// Return false if no Application Password exists.
		if ( ! is_array( $password ) ) {
			return false;
		}

		return $password['uuid'];

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
