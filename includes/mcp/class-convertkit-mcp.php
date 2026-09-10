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

		return rest_url( self::SERVER_NAMESPACE . '/' . self::SERVER_ROUTE );

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

		// Register resources and prompts.
		add_action( 'wp_abilities_api_init', array( $this, 'register_resources' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_prompts' ) );

		// Register resource-list abilities (Forms, Tags, Landing Pages, Products).
		// These are owned by the Plugin (not by any single block or feature),
		// so they're added here rather than via a per-class register_abilities().
		add_filter( 'convertkit_abilities', array( $this, 'register_resource_abilities' ) );

		// Register MCP resources (live-state lists, account, settings and reference docs).
		add_filter( 'convertkit_resources', array( $this, 'register_mcp_resources' ) );

		// Register MCP prompts (guided workflows).
		add_filter( 'convertkit_prompts', array( $this, 'register_mcp_prompts' ) );

		// Register settings get / update abilities for each Plugin settings
		// These are owned by the Plugin (not by any single feature),
		// so they're added here rather than via a per-class register_abilities().
		add_filter( 'convertkit_abilities', array( $this, 'register_settings_abilities' ) );

		// Register the per-Post Kit settings get / update abilities. These
		// operate on the `_wp_convertkit_post_meta` post meta (form,
		// landing_page, tag, restrict_content) rather than any Plugin-wide
		// settings group.
		add_filter( 'convertkit_abilities', array( $this, 'register_post_settings_abilities' ) );

		// Register the per-Category Kit settings get / update abilities.
		// These operate on the `_wp_convertkit_term_meta` term meta (form,
		// form_position) for the WordPress `category` taxonomy.
		add_filter( 'convertkit_abilities', array( $this, 'register_category_settings_abilities' ) );

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
	 * Appends the per-Post Kit settings abilities to the convertkit_abilities
	 * filter, so they are registered with the Abilities API and exposed via
	 * the MCP server.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $abilities   Abilities to register.
	 * @return  array
	 */
	public function register_post_settings_abilities( $abilities ) {

		$abilities['kit/post-settings-get']    = new ConvertKit_MCP_Ability_Post_Settings_Get();
		$abilities['kit/post-settings-update'] = new ConvertKit_MCP_Ability_Post_Settings_Update();

		return $abilities;

	}

	/**
	 * Appends the per-Category Kit settings abilities to the convertkit_abilities
	 * filter, so they are registered with the Abilities API and exposed via
	 * the MCP server.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $abilities   Abilities to register.
	 * @return  array
	 */
	public function register_category_settings_abilities( $abilities ) {

		$abilities['kit/category-settings-get']    = new ConvertKit_MCP_Ability_Category_Settings_Get();
		$abilities['kit/category-settings-update'] = new ConvertKit_MCP_Ability_Category_Settings_Update();

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
	 * Appends the MCP resources (live-state lists, account, settings and
	 * reference docs) to the convertkit_resources filter, so they are
	 * registered with the Abilities API and exposed as MCP Resources.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $resources   Resources to register.
	 * @return  array
	 */
	public function register_mcp_resources( $resources ) {

		$mcp_resources = array(
			new ConvertKit_MCP_Resource_Forms(),
			new ConvertKit_MCP_Resource_Tags(),
			new ConvertKit_MCP_Resource_Landing_Pages(),
			new ConvertKit_MCP_Resource_Products(),
			new ConvertKit_MCP_Resource_Account(),
			new ConvertKit_MCP_Resource_Settings(),
			new ConvertKit_MCP_Resource_Overview(),
			new ConvertKit_MCP_Resource_Forms_Reference(),
			new ConvertKit_MCP_Resource_Restrict_Content_Reference(),
			new ConvertKit_MCP_Resource_Settings_Reference(),
		);

		foreach ( $mcp_resources as $resource ) {
			$resources[ $resource->get_name() ] = $resource;
		}

		return $resources;

	}

	/**
	 * Appends the MCP prompts (guided workflows) to the convertkit_prompts
	 * filter, so they are registered with the Abilities API and exposed as
	 * MCP Prompts.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $prompts   Prompts to register.
	 * @return  array
	 */
	public function register_mcp_prompts( $prompts ) {

		$mcp_prompts = array(
			new ConvertKit_MCP_Prompt_Setup(),
			new ConvertKit_MCP_Prompt_Add_Form(),
			new ConvertKit_MCP_Prompt_Restrict_Content(),
			new ConvertKit_MCP_Prompt_Configure_Broadcasts_Import(),
			new ConvertKit_MCP_Prompt_Audit(),
		);

		foreach ( $mcp_prompts as $prompt ) {
			$prompts[ $prompt->get_name() ] = $prompt;
		}

		return $prompts;

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
	 * Register MCP resources with the WordPress Abilities API.
	 *
	 * @since   3.4.2
	 */
	public function register_resources() {

		// Get resources.
		$resources = convertkit_get_resources();

		// Bail if no resources are available.
		if ( ! count( $resources ) ) {
			return;
		}

		// Iterate through resources, registering each as an ability.
		foreach ( $resources as $resource ) {

			// Skip if this resource is not an instance of ConvertKit_MCP_Resource.
			if ( ! ( $resource instanceof ConvertKit_MCP_Resource ) ) {
				continue;
			}

			// Register resource.
			wp_register_ability( $resource->get_name(), $resource->get_ability_args() );
		}

	}

	/**
	 * Register MCP prompts with the WordPress Abilities API.
	 *
	 * @since   3.4.2
	 */
	public function register_prompts() {

		// Get prompts.
		$prompts = convertkit_get_prompts();

		// Bail if no prompts are available.
		if ( ! count( $prompts ) ) {
			return;
		}

		// Iterate through prompts, registering each as an ability.
		foreach ( $prompts as $prompt ) {

			// Skip if this prompt is not an instance of ConvertKit_MCP_Prompt.
			if ( ! ( $prompt instanceof ConvertKit_MCP_Prompt ) ) {
				continue;
			}

			// Register prompt.
			wp_register_ability( $prompt->get_name(), $prompt->get_ability_args() );
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

		// Bail if the MCP server isn't enabled.
		$settings     = new ConvertKit_Settings();
		$mcp_settings = new ConvertKit_Settings_MCP();
		if ( ! $mcp_settings->enabled() ) {
			return;
		}

		// Get abilities.
		$abilities = convertkit_get_abilities();

		// Build array of ability names.
		$ability_names = array();
		foreach ( $abilities as $ability ) {
			$ability_names[] = $ability->get_name();
		}

		// Build array of resource names.
		$resource_names = array();
		foreach ( convertkit_get_resources() as $resource ) {
			$resource_names[] = $resource->get_name();
		}

		// Build array of prompt names.
		$prompt_names = array();
		foreach ( convertkit_get_prompts() as $prompt ) {
			$prompt_names[] = $prompt->get_name();
		}

		// Create the MCP server.
		$result = $adapter->create_server(
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
			$resource_names, // Resources.
			$prompt_names // Prompts.
		);

		// If an error occured when creating the server, log it.
		if ( is_wp_error( $result ) && $settings->debug_enabled() ) {
			$log = new ConvertKit_Log( CONVERTKIT_PLUGIN_PATH );
			$log->add( 'MCP: create_server(): Error: ' . $result->get_error_message() );
		}

	}

}
