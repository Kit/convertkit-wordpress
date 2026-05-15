<?php
/**
 * Kit MCP Ability base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Abstract base for all Kit Plugin abilities exposed via the WordPress Abilities
 * API and MCP Adapter.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Ability {

	/**
	 * Sets whether the ability is readonly.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $readonly = false;

	/**
	 * Sets whether the ability is destructive.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $destructive = false;

	/**
	 * Sets whether the ability is idempotent.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $idempotent = false;

	/**
	 * Returns the ability name, prefixed with `kit/` (e.g. `kit/form-insert`).
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract public function get_name();

	/**
	 * Returns the arguments array passed to wp_register_ability().
	 *
	 * @since   3.4.0
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
				'annotations' => $this->get_annotations(),
			),
		);

	}

	/**
	 * Returns the ability's human-readable label.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract public function get_label();

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract public function get_description();

	/**
	 * Returns the ability's category.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_category() {

		return 'kit';

	}

	/**
	 * Returns the ability's input JSON Schema.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	abstract public function get_input_schema();

	/**
	 * Returns the ability's output JSON Schema.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	abstract public function get_output_schema();

	/**
	 * Define the annotations for the ability.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_annotations() {

		return array(
			'title'       => $this->get_label(),
			'readonly'    => $this->readonly,
			'destructive' => $this->destructive,
			'idempotent'  => $this->idempotent,
		);

	}

	/**
	 * Permission callback for this ability.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input.
	 * @return  bool|WP_Error
	 */
	abstract public function permission_callback( $input );

	/**
	 * Execute callback for this ability.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input.
	 * @return  array|WP_Error
	 */
	abstract public function execute_callback( $input );

}
