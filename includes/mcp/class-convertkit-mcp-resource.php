<?php
/**
 * Kit MCP Resource base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Abstract base for all Kit Plugin resources exposed via the WordPress Abilities
 * API and MCP Adapter.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Resource {

	/**
	 * Returns the resource name, prefixed with `kit/` (e.g. `kit/forms`).
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	abstract public function get_name();

	/**
	 * Returns the resource URI that MCP clients read (e.g. `kit://forms`).
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	abstract public function get_uri();

	/**
	 * Returns the resource's human-readable label.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	abstract public function get_label();

	/**
	 * Returns the resource's human-readable description.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	abstract public function get_description();

	/**
	 * Returns the resource content's MIME type.
	 *
	 * Sub classes can override this to return a different MIME type.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	public function get_mime_type() {

		return 'text/markdown';

	}

	/**
	 * Returns the resource's category.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	public function get_category() {

		return 'kit';

	}

	/**
	 * Returns the resource's input JSON Schema.
	 *
	 * Resources are read by URI with no input, so this is an empty object
	 * schema. `null` is allowed as well as an object, so input validation
	 * doesn't fail on the empty read.
	 *
	 * @since   3.4.2
	 *
	 * @return  array
	 */
	public function get_input_schema() {

		return array(
			'type' => array( 'object', 'null' ),
		);

	}

	/**
	 * Returns the resource's output JSON Schema.
	 *
	 * Resource content is returned as a single string, wrapped as text content
	 * by the MCP Adapter.
	 *
	 * @since   3.4.2
	 *
	 * @return  array
	 */
	public function get_output_schema() {

		return array(
			'type' => 'string',
		);

	}

	/**
	 * Permission callback.
	 *
	 * Sub classes can override this to implement their own permission callback.
	 *
	 * @since   3.4.2
	 *
	 * @param   array $input   Resource input (unused).
	 * @return  bool|WP_Error
	 */
	public function permission_callback( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'convertkit_mcp_resource_permission_callback',
				__( 'You do not have permission for this operation.', 'convertkit' )
			);
		}

		return true;

	}

	/**
	 * Returns the arguments array passed to wp_register_ability().
	 *
	 * @since   3.4.2
	 *
	 * @return  array
	 */
	public function get_ability_args() {

		return array(
			'label'               => $this->get_label(),
			'description'         => $this->get_description(),
			'category'            => $this->get_category(),
			'input_schema'        => $this->get_input_schema(),
			'output_schema'       => $this->get_output_schema(),
			'permission_callback' => array( $this, 'permission_callback' ),
			'execute_callback'    => array( $this, 'execute_callback' ),
			'meta'                => array(
				'mcp' => array(
					'uri'      => $this->get_uri(),
					'mimeType' => $this->get_mime_type(),
				),
			),
		);

	}

	/**
	 * Execute callback for this resource, returning its content.
	 *
	 * @since   3.4.2
	 *
	 * @param   array $input   Resource input.
	 * @return  string|WP_Error
	 */
	abstract public function execute_callback( $input );

}
