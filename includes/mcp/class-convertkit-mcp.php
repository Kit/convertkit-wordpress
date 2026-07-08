<?php
/**
 * Kit MCP class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers Plugin abilities (tools) using the WordPress Abilities API, and exposes
 * those abilities as MCP tools via the WordPress MCP Adapter (if installed).
 *
 * The Abilities API ships with WordPress 6.9 and later.
 *
 * The WordPress MCP Adapter is a separate plugin, and not (yet) part of WordPress
 * core. If it is not active on the site, abilities are still registered and callable
 * in PHP, but nothing is exposed over the MCP protocol.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP {

	/**
	 * The ability category slug used to group all Kit abilities.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const CATEGORY_SLUG = 'kit';

	/**
	 * The MCP server ID.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const SERVER_ID = 'kit/mcp';

	/**
	 * The REST namespace used by the MCP server.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const SERVER_NAMESPACE = 'kit/mcp';

	/**
	 * The REST version number used by the MCP server.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const SERVER_ROUTE = 'v1';

	/**
	 * Returns the absolute URL that MCP clients connect to.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public static function get_server_url() {

		return rest_url( self::SERVER_NAMESPACE . '/' . self::SERVER_ROUTE . '/mcp' );

	}

	/**
	 * Constructor.
	 *
	 * @since   3.4.0
	 */
	public function __construct() {

		// Register the ability category.
		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_abilities_category' ) );

		// Register abilities.
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );

		// Register resource-list abilities (Forms, Tags, Landing Pages, Products).
		// These are owned by the Plugin (not by any single block or feature),
		// so they're added here rather than via a per-class register_abilities().
		add_filter( 'convertkit_abilities', array( $this, 'register_resource_abilities' ) );

		// Register settings get / update abilities for each Plugin settings
		// These are owned by the Plugin (not by any single feature),
		// so they're added here rather than via a per-class register_abilities().
		add_filter( 'convertkit_abilities', array( $this, 'register_settings_abilities' ) );

		// Register the MCP server.
		add_action( 'mcp_adapter_init', array( $this, 'register_mcp_server' ) );

	}

	/**
	 * Appends the settings get / update abilities for each Plugin settings
	 * group to the convertkit_abilities filter, so they are registered with
	 * the Abilities API and exposed via the MCP server.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $abilities   Abilities to register.
	 * @return  array
	 */
	public function register_settings_abilities( $abilities ) {

		// Settings instances to register with MCP.
		$groups = array(
			new ConvertKit_Settings(),
			new ConvertKit_Settings_Broadcasts(),
			new ConvertKit_Settings_Restrict_Content(),
		);

		// Iterate through settings groups, registering the get and update abilities.
		foreach ( $groups as $settings ) {
			$get    = new ConvertKit_MCP_Ability_Settings_Get( $settings );
			$update = new ConvertKit_MCP_Ability_Settings_Update( $settings );

			$abilities[ $get->get_name() ]    = $get;
			$abilities[ $update->get_name() ] = $update;
		}

		return $abilities;

	}

	/**
	 * Appends the resource-list abilities (Forms, Tags, Landing Pages,
	 * Products) to the convertkit_abilities filter, so they are registered
	 * with the Abilities API and exposed via the MCP server.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $abilities   Abilities to register.
	 * @return  array
	 */
	public function register_resource_abilities( $abilities ) {

		return array_merge(
			$abilities,
			array(
				'kit/forms-list'         => new ConvertKit_MCP_Ability_Resource_Forms(),
				'kit/tags-list'          => new ConvertKit_MCP_Ability_Resource_Tags(),
				'kit/landing-pages-list' => new ConvertKit_MCP_Ability_Resource_Landing_Pages(),
				'kit/products-list'      => new ConvertKit_MCP_Ability_Resource_Products(),
			)
		);

	}

	/**
	 * Register the 'kit' ability category.
	 *
	 * @since   3.4.0
	 */
	public function register_abilities_category() {

		wp_register_ability_category(
			self::CATEGORY_SLUG,
			array(
				'label'       => __( 'Kit', 'convertkit' ),
				'description' => __( 'Abilities exposed by the Kit Plugin.', 'convertkit' ),
			)
		);

	}

	/**
	 * Register abilities with the WordPress Abilities API.
	 *
	 * @since   3.4.0
	 */
	public function register_abilities() {

		// Get abilities.
		$abilities = convertkit_get_abilities();

		// Bail if no abilities are available.
		if ( ! count( $abilities ) ) {
			return;
		}

		// Iterate through abilities, registering them.
		foreach ( $abilities as $ability ) {

			// Skip if this ability is not an instance of ConvertKit_MCP_Ability.
			if ( ! ( $ability instanceof ConvertKit_MCP_Ability ) ) {
				continue;
			}

			// Register ability.
			wp_register_ability( $ability->get_name(), $ability->get_ability_args() );
		}

	}

	/**
	 * Register an MCP server that exposes Kit abilities as MCP tools.
	 *
	 * @since   3.4.0
	 *
	 * @param   object $adapter    The MCP Adapter instance.
	 * @return  void
	 */
	public function register_mcp_server( $adapter ) {

		// Get abilities.
		$abilities = convertkit_get_abilities();

		// Build array of ability names.
		$ability_names = array();
		foreach ( $abilities as $ability ) {
			$ability_names[] = $ability->get_name();
		}

		// Create the MCP server.
		$adapter->create_server(
			self::SERVER_ID,
			self::SERVER_NAMESPACE,
			self::SERVER_ROUTE,
			__( 'Kit WordPress Plugin MCP', 'convertkit' ),
			__( 'Exposes Kit Plugin abilities over the Model Context Protocol.', 'convertkit' ),
			'1.0.0',
			array( 'WP\\MCP\\Transport\\HttpTransport' ),
			'WP\\MCP\\Infrastructure\\ErrorHandling\\ErrorLogMcpErrorHandler',
			'WP\\MCP\\Infrastructure\\Observability\\NullMcpObservabilityHandler',
			$ability_names, // Abilities (Tools).
			array(), // Resources.
			array()  // Prompts.
		);

	}

}
