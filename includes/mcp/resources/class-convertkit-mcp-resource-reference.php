<?php
/**
 * Kit MCP Resource: reference base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Base class for static reference resources: Markdown documents that describe
 * how the Plugin works (its mental model), so the model can read them as
 * context instead of guessing.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Resource_Reference extends ConvertKit_MCP_Resource {

	/**
	 * Returns the Markdown content lines for this reference document.
	 *
	 * @since   3.5.0
	 *
	 * @return  array
	 */
	abstract protected function get_content_lines();

	/**
	 * Permission callback.
	 *
	 * Reference docs are non-sensitive and readable by anyone who can edit
	 * posts — the audience that uses the MCP tools.
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
				__( 'You do not have permission for this operation.', 'convertkit' )
			);
		}

		return true;

	}

	/**
	 * Executes the resource: return the Markdown document.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Resource input (unused).
	 * @return  string|WP_Error
	 */
	public function execute_callback( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return implode( "\n", $this->get_content_lines() );

	}

}
