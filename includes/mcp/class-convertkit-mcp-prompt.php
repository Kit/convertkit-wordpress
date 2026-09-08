<?php
/**
 * Kit MCP Prompt base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Abstract base for all Kit Plugin prompts exposed via the WordPress Abilities
 * API and MCP Adapter.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Prompt {

	/**
	 * Returns the prompt's short name (e.g. `setup`), without the `kit/` prefix.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	abstract protected function get_prompt_name();

	/**
	 * Returns the fully qualified prompt name, prefixed with `kit/`
	 * (e.g. `kit/setup`).
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/' . $this->get_prompt_name();

	}

	/**
	 * Returns the prompt's human-readable label / title.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	abstract public function get_label();

	/**
	 * Returns the prompt's human-readable description.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	abstract public function get_description();

	/**
	 * Returns the prompt's arguments, keyed by argument name. Each value is an
	 * array with a `description` and an optional `required` flag. MCP prompt
	 * arguments are always strings.
	 *
	 * @since   3.4.2
	 *
	 * @return  array
	 */
	protected function get_arguments() {

		return array();

	}

	/**
	 * Returns the prompt's category.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	public function get_category() {

		return 'kit';

	}

	/**
	 * Returns the prompt's input JSON Schema, built from get_arguments(). The MCP
	 * Adapter converts each property to a prompt argument.
	 *
	 * @since   3.4.2
	 *
	 * @return  array
	 */
	public function get_input_schema() {

		$properties = array();
		$required   = array();

		foreach ( $this->get_arguments() as $name => $argument ) {
			$properties[ $name ] = array(
				'type'        => 'string',
				'description' => isset( $argument['description'] ) ? $argument['description'] : '',
			);

			if ( ! empty( $argument['required'] ) ) {
				$required[] = $name;
			}
		}

		$schema = array(
			'type'       => 'object',
			'properties' => $properties,
		);

		if ( count( $required ) ) {
			$schema['required'] = $required;
		}

		return $schema;

	}

	/**
	 * Returns the prompt's output JSON Schema. Prompt content is returned as a
	 * text string, wrapped as a user message by the MCP Adapter.
	 *
	 * @since   3.4.2
	 *
	 * @return  array
	 */
	public function get_output_schema() {

		return array(
			'type' => 'object',
		);

	}

	/**
	 * Permission callback.
	 *
	 * @since   3.4.2
	 *
	 * @param   array $input   Prompt input (unused).
	 * @return  bool|WP_Error
	 */
	public function permission_callback( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'convertkit_mcp_prompt_permission_callback',
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
					'type'   => 'prompt',
					'public' => true,
				),
			),
		);

	}

	/**
	 * Execute callback for this prompt, returning its prompt text.
	 *
	 * Sub classes build their prompt and return $this->render( array( ...sections... ) ).
	 *
	 * @since   3.4.2
	 *
	 * @param   array $input   Prompt arguments.
	 * @return  array|WP_Error
	 */
	abstract public function execute_callback( $input );

	/**
	 * Assembles the given text sections into a single prompt message, skipping
	 * empty sections.
	 *
	 * @since   3.4.2
	 *
	 * @param   array $sections   Text sections.
	 * @return  array               Prompt result ( text ).
	 */
	protected function render( $sections ) {

		$sections = array_filter( array_map( 'trim', (array) $sections ), 'strlen' );

		return array(
			'text' => implode( "\n\n", $sections ),
		);

	}

}
