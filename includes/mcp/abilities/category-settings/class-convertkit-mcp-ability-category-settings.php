<?php
/**
 * Kit MCP Ability: Category Settings base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Base class for abilities that read or update the per-Category Kit settings
 * (form, form_position) stored in the `_wp_convertkit_term_meta` term meta key.
 *
 * Each subclass represents a single verb (get / update). Produces ability
 * names of the form `kit/category-settings-<operation>`.
 *
 * Scope is limited to the `category` taxonomy, matching the admin UI
 * (ConvertKit_Admin_Category) and the frontend read path
 * (ConvertKit_Output::get_term_form_position()).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Ability_Category_Settings extends ConvertKit_MCP_Ability {

	/**
	 * The taxonomy this ability operates on.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	const TAXONOMY = 'category';

	/**
	 * Returns the operation suffix used in the ability name (e.g. 'get',
	 * 'update').
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract protected function get_operation();

	/**
	 * Returns the ability name.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/category-settings-' . $this->get_operation();

	}

	/**
	 * Only permit an ability to be executed if the current user can edit
	 * the given category term.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input.
	 * @return  bool|WP_Error
	 */
	public function permission_callback( $input ) {

		// Get Term ID.
		$term_id = isset( $input['term_id'] ) ? absint( $input['term_id'] ) : 0;

		// Bail if no Term ID is provided.
		if ( ! $term_id ) {
			return new WP_Error(
				'convertkit_mcp_missing_term_id',
				__( 'A term_id is required.', 'convertkit' )
			);
		}

		// Bail if the current user cannot edit this term.
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return new WP_Error(
				'convertkit_mcp_cannot_edit_term',
				__( 'You do not have permission to edit this category.', 'convertkit' )
			);
		}

		return true;

	}

	/**
	 * Validates that the given term exists and is in the `category` taxonomy.
	 *
	 * Returned as a shared helper for both verb subclasses' execute_callback.
	 *
	 * @since   3.4.0
	 *
	 * @param   int $term_id    Term ID.
	 * @return  true|WP_Error
	 */
	protected function validate_term( $term_id ) {

		$term = get_term( $term_id );

		// Bail if the term does not exist.
		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error(
				'convertkit_mcp_term_not_found',
				sprintf(
					/* translators: %d: Term ID. */
					__( 'Term %d does not exist.', 'convertkit' ),
					$term_id
				)
			);
		}

		// Bail if the term is not in the `category` taxonomy.
		if ( $term->taxonomy !== self::TAXONOMY ) {
			return new WP_Error(
				'convertkit_mcp_term_wrong_taxonomy',
				sprintf(
					/* translators: 1: Term ID, 2: Actual taxonomy, 3: Expected taxonomy. */
					__( 'Term %1$d is in the "%2$s" taxonomy; this ability only supports the "%3$s" taxonomy.', 'convertkit' ),
					$term_id,
					$term->taxonomy,
					self::TAXONOMY
				)
			);
		}

		return true;

	}

	/**
	 * Returns the JSON Schema properties that describe the two Kit category
	 * settings, shared by both the input and output schemas.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	protected function get_settings_schema_properties() {

		return array(
			'form'          => array(
				'type'        => 'integer',
				'description' => __( 'Form to display for Posts assigned to this Category. `-1` = use the Plugin Default Form; `0` = display no form; any other positive integer is a specific Kit Form ID.', 'convertkit' ),
				'minimum'     => -1,
			),
			'form_position' => array(
				'type'        => 'string',
				'description' => __( 'Where the Form displays on the Category archive page. Empty string uses the Plugin default position; `before` displays it before the post list; `after` displays it after.', 'convertkit' ),
				'enum'        => array( '', 'before', 'after' ),
			),
		);

	}

	/**
	 * Returns the JSON Schema for the ability's output.
	 *
	 * Shared by get and update so a caller can chain update -> confirm.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_output_schema() {

		return array(
			'type'       => 'object',
			'required'   => array( 'term_id', 'form', 'form_position' ),
			'properties' => array_merge(
				array(
					'term_id' => array(
						'type'        => 'integer',
						'description' => __( 'The Category (term) ID.', 'convertkit' ),
					),
				),
				$this->get_settings_schema_properties()
			),
		);

	}

}
