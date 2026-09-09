<?php
/**
 * Kit MCP Resource: list base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Base class for live-state resources that expose an existing resource-list
 * ability's data (Forms, Tags, Landing Pages, Products) as an MCP Resource,
 * so clients can attach the current id/name mappings as read-only context.
 *
 * The data is single-sourced from the equivalent `kit/*-list` tool, so the
 * resource and tool never drift apart.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Resource_List extends ConvertKit_MCP_Resource {

	/**
	 * Returns the class name of the ConvertKit_MCP_Ability_Resource_*
	 * ability backing this resource.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	abstract protected function get_ability_class();

	/**
	 * Returns the resource content's MIME type.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_mime_type() {

		return 'application/json';

	}

	/**
	 * Permission callback.
	 *
	 * Matches the backing list tool's `edit_posts` gate, so the same users who
	 * can look up a Form / Tag when placing a Kit element can read this resource.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Resource input (unused).
	 * @return  bool|WP_Error
	 */
	public function permission_callback( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'convertkit_mcp_resource_permission_callback',
				__( 'You do not have permission to list Kit resources.', 'convertkit' )
			);
		}

		return true;

	}

	/**
	 * Executes the resource: run the backing list ability and return its result
	 * as a JSON string.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Resource input.
	 * @return  string|WP_Error
	 */
	public function execute_callback( $input ) {

		$ability_class = $this->get_ability_class();
		if ( ! class_exists( $ability_class ) ) {
			return new WP_Error(
				'convertkit_mcp_resource_ability_missing',
				sprintf(
					/* translators: %s: Ability class name */
					__( 'The ability class "%s" does not exist.', 'convertkit' ),
					$ability_class
				)
			);
		}

		$ability = new $ability_class();
		$result  = $ability->execute_callback( $input );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return (string) wp_json_encode( $result );

	}

}
