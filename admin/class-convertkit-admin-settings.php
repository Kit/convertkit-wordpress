<?php
/**
 * ConvertKit Admin Settings class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers a screen at Settings > Kit in the WordPress Administration
 * interface, and handles saving its data.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Settings {

	/**
	 * Settings sections
	 *
	 * @var array
	 */
	public $sections = array();

	/**
	 * Holds the Settings Page Slug
	 *
	 * @var     string
	 */
	const SETTINGS_PAGE_SLUG = '_wp_convertkit_settings';

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_sections' ) );
		add_filter( 'plugin_action_links_' . CONVERTKIT_PLUGIN_FILE, array( $this, 'add_settings_page_link' ) );

	}

	/**
	 * Enqueue JavaScript in Admin
	 *
	 * @since   1.9.6
	 *
	 * @param   string $hook   Hook.
	 */
	public function enqueue_scripts( $hook ) {

		// Bail if we are not on the Settings screen.
		if ( $hook !== 'settings_page_' . self::SETTINGS_PAGE_SLUG ) {
			return;
		}

		// Get active settings section / tab that has been requested.
		$section = $this->get_active_section();

		/**
		 * Enqueue JavaScript for the Settings Screen at Settings > Kit
		 *
		 * @since   1.9.6
		 *
		 * @param   string  $section    Settings section / tab (general|tools|restrict-content).
		 */
		do_action( 'convertkit_admin_settings_enqueue_scripts', $section );

	}

	/**
	 * Enqueue CSS for the Settings Screens at Settings > Kit
	 *
	 * @since   1.9.6
	 *
	 * @param   string $hook   Hook.
	 */
	public function enqueue_styles( $hook ) {

		// Bail if we are not on the Settings screen.
		if ( $hook !== 'settings_page_' . self::SETTINGS_PAGE_SLUG ) {
			return;
		}

		// Get active settings section / tab that has been requested.
		$section = $this->get_active_section();

		// Always enqueue Settings CSS, as this is used for the UI across all settings sections.
		wp_enqueue_style( 'convertkit-admin-settings', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/settings.css', array(), CONVERTKIT_PLUGIN_VERSION );

		/**
		 * Enqueue CSS for the Settings Screen at Settings > Kit
		 *
		 * @since   1.9.6
		 *
		 * @param   string  $section    Settings section / tab (general|tools|restrict-content).
		 */
		do_action( 'convertkit_admin_settings_enqueue_styles', $section );

	}

	/**
	 * Adds the options page
	 *
	 * @since   1.9.6
	 */
	public function add_settings_page() {

		add_options_page(
			__( 'Kit', 'convertkit' ),
			__( 'Kit', 'convertkit' ),
			'manage_options',
			self::SETTINGS_PAGE_SLUG,
			array( $this, 'display_settings_page' )
		);

	}

	/**
	 * Outputs the settings screen.
	 *
	 * @since   1.9.6
	 */
	public function display_settings_page() {

		$active_section = $this->get_active_section();
		?>

		<header>
			<h1><?php esc_html_e( 'Kit', 'convertkit' ); ?></h1>

			<?php
			// Output Help link tab, if it exists.
			$documentation_url = $this->get_active_section_documentation_url( $active_section );
			if ( $documentation_url !== false ) {
				printf(
					'<a href="%s" class="convertkit-docs" target="_blank">%s</a>',
					esc_attr( $documentation_url ),
					esc_html__( 'Help', 'convertkit' )
				);
			}
			?>
		</header>

		<div class="wrap">
			<?php
			if ( count( $this->sections ) > 1 ) {
				$this->display_section_nav( $active_section );
			}

			/**
			 * Defines the settings form's method.
			 *
			 * @since   3.0.0
			 *
			 * @param   string  $form_method        The method of the form.
			 * @param   string  $active_section     The active section.
			 */
			$form_method = apply_filters( 'convertkit_admin_settings_form_method', 'post', $active_section );

			/**
			 * Defines the settings form's action URL.
			 *
			 * @since   3.0.0
			 *
			 * @param   string  $form_action_url    The URL to submit the form to.
			 * @param   string  $active_section     The active section.
			 */
			$form_action_url = apply_filters( 'convertkit_admin_settings_form_action_url', admin_url( 'options.php' ), $active_section );
			?>
			<form method="<?php echo esc_attr( $form_method ); ?>" action="<?php echo esc_url( $form_action_url ); ?>" enctype="multipart/form-data">
				<?php
				// Iterate through sections to find the active section to render.
				if ( isset( $this->sections[ $active_section ] ) ) {
					$this->sections[ $active_section ]->render();
				}
				?>
			</form>

			<p class="description">
				<?php
				// Output Help link, if it exists.
				$documentation_url = $this->get_active_section_documentation_url( $active_section );
				if ( $documentation_url !== false ) {
					printf(
						'%s <a href="%s" target="_blank">%s</a>',
						esc_html__( 'If you need help setting up the plugin please refer to the', 'convertkit' ),
						esc_attr( $documentation_url ),
						esc_html__( 'plugin documentation', 'convertkit' )
					);
				}
				?>
			</p>
		</div>
		<?php

	}

	/**
	 * Gets the active tab section that the user is viewing on the Plugin Settings screen.
	 *
	 * @since   1.9.6
	 *
	 * @return  string  Tab Name
	 */
	private function get_active_section() {

		if ( filter_has_var( INPUT_GET, 'tab' ) ) {
			return filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		}

		// First registered section will be the active section.
		return current( $this->sections )->name;

	}

	/**
	 * Define links to display below the Plugin Name on the WP_List_Table at in the Plugins screen.
	 *
	 * @param   array $links      Links.
	 * @return  array               Links
	 */
	public function add_settings_page_link( $links ) {

		// Add link to Plugin settings screen.
		$links['settings'] = sprintf(
			'<a href="%s">%s</a>',
			convertkit_get_settings_link(),
			__( 'Settings', 'convertkit' )
		);

		/**
		 * Define links to display below the Plugin Name on the WP_List_Table at Plugins > Installed Plugins.
		 *
		 * @since   2.1.2
		 *
		 * @param   array   $links  HTML Links.
		 */
		$links = apply_filters( 'convertkit_plugin_screen_action_links', $links );

		// Return.
		return $links;

	}

	/**
	 * Output tabs, one for each registered settings section.
	 *
	 * @param   string $active_section     Currently displayed/selected section.
	 */
	public function display_section_nav( $active_section ) {

		?>
		<ul class="convertkit-tabs">
			<?php
			foreach ( $this->sections as $section ) {
				printf(
					'<li><a href="%s" class="convertkit-tab %s">%s%s</a></li>',
					esc_url(
						add_query_arg(
							array(
								'page' => self::SETTINGS_PAGE_SLUG,
								'tab'  => $section->name,
							),
							admin_url( 'options-general.php' )
						)
					),
					( $active_section === $section->name ? 'convertkit-tab-active' : '' ),
					esc_html( $section->tab_text ),
					wp_kses(
						$section->is_beta ? $this->get_beta_tab() : '',
						convertkit_kses_allowed_html()
					)
				);
			}
			?>
		</ul>
		<?php
		// WordPress' JS will automatically move any .notice elements to be immediately below .wp-header-end
		// or <h2>, whichever comes first.
		// As our <h2> is inside our .metabox-holder, we output .wp-header-end first to control the notification
		// placement to be before the white background container/box.
		?>
		<hr class="wp-header-end">
		<?php

	}

	/**
	 * Returns a 'beta' tab wrapped in a span.
	 *
	 * @since   2.1.0
	 *
	 * @return  string
	 */
	private function get_beta_tab() {

		return '<span class="convertkit-beta-label">' . esc_html__( 'Beta', 'convertkit' ) . '</span>';

	}

	/**
	 * Registers settings sections at Settings > Kit.
	 *
	 * Each section has its own tab.
	 *
	 * @since   1.9.6
	 */
	public function register_sections() {

		// If no Access Token exists, register a settings section that shows a button
		// to start the OAuth authentication flow.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			// Just register the OAuth screen.
			$sections = array(
				'oauth' => new ConvertKit_Admin_Section_OAuth(),
			);

			// Assign them to this class.
			$this->sections = $sections;

			return;
		}

		// Register the General and Tools settings sections.
		$sections = array(
			'general' => new ConvertKit_Admin_Section_General(),
			'tools'   => new ConvertKit_Admin_Section_Tools(),
		);

		/**
		 * Registers settings sections at Settings > Kit.
		 *
		 * @since   1.9.6
		 *
		 * @param   array   $sections   Array of settings classes that handle individual tabs e.g. General, Tools etc.
		 */
		$sections = apply_filters( 'convertkit_admin_settings_register_sections', $sections );

		// With our sections now registered, assign them to this class.
		$this->sections = $sections;

	}

	/**
	 * Returns the documentation URL for the active settings section viewed by the user.
	 *
	 * @since   2.0.8
	 *
	 * @param   string $active_section     Currently displayed/selected section.
	 * @return  bool|string
	 */
	private function get_active_section_documentation_url( $active_section ) {

		// Bail if no sections registered.
		if ( ! $this->sections ) {
			return false;
		}

		// Bail if the active section isn't registered.
		if ( ! array_key_exists( $active_section, $this->sections ) ) {
			return false;
		}

		// Pass request to section's documentation_url() function, including UTM parameters.
		return add_query_arg(
			array(
				'utm_source'  => 'wordpress',
				'utm_term'    => get_locale(),
				'utm_content' => 'convertkit',
			),
			$this->sections[ $active_section ]->documentation_url()
		);

	}

}
