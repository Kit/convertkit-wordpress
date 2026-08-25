<?php
/**
 * Kit MCP Ability: Get Category Settings.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that returns the current Kit settings for a Category.
 *
 * Produces an ability named `kit/category-settings-get`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Category_Settings_Get extends ConvertKit_MCP_Ability_Category_Settings {

	/**
	 * Sets whether the ability is readonly.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $readonly = true; // @phpstan-ignore-line

	/**
	 * Sets whether the ability is idempotent.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $idempotent = true; // @phpstan-ignore-line

	/**
	 * Returns the operation suffix used in the ability name.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_operation() {

		return 'get';

	}

	/**
	 * Returns the ability's human-readable label.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Get Kit Category Settings', 'convertkit' );

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Returns the current Kit settings (form, form_position) for the given Category (WordPress `category` taxonomy term).', 'convertkit' );

	}

	/**
	 * Returns the ability's input JSON Schema.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_input_schema() {

		return array(
			'type'       => 'object',
			'required'   => array( 'term_id' ),
			'properties' => array(
				'term_id' => array(
					'type'        => 'integer',
					'description' => __( 'The Category (term) ID to read Kit settings for.', 'convertkit' ),
					'minimum'     => 1,
				),
			),
		);

	}

	/**
	 * Executes the ability.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input.
	 * @return  array|WP_Error
	 */
	public function execute_callback( $input ) {

		$term_id = isset( $input['term_id'] ) ? absint( $input['term_id'] ) : 0;

		// Bail if the term does not exist or is not a Category.
		$valid = $this->validate_term( $term_id );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		// Load the Category's settings.
		$term_settings = new ConvertKit_Term( $term_id );
		$settings      = $term_settings->get();

		// Cast `form` to int and `form_position` to string so the output
		// exactly matches the declared schema, regardless of how the value
		// was stored (defaults may be '' for form, but the schema wants int).
		$form          = isset( $settings['form'] ) && $settings['form'] !== '' ? (int) $settings['form'] : 0;
		$form_position = isset( $settings['form_position'] ) ? (string) $settings['form_position'] : '';

		return array(
			'term_id'       => $term_id,
			'form'          => $form,
			'form_position' => $form_position,
		);

	}

}
