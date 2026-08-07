<?php
/**
 * Kit MCP Ability: List Forms.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that lists every Kit Form cached from the connected Kit account,
 * returning each form's ID, name and format (inline / modal / slide in /
 * sticky bar).
 *
 * Produces an ability named `kit/forms-list`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Resource_Forms extends ConvertKit_MCP_Ability_Resource {

	/**
	 * Returns the resource slug for this ability.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_resource() {

		return 'forms';

	}

	/**
	 * Returns the class name of the ConvertKit_Resource_* implementation
	 * backing this ability.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_resource_class() {

		return 'ConvertKit_Resource_Forms';

	}

	/**
	 * Returns the ability's human-readable label.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'List Kit Forms', 'convertkit' );

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * Phrased as guidance to the model: state what the ability returns and
	 * (importantly) when to chain it ahead of insert / update / settings
	 * abilities so a user can refer to a Form by name rather than ID.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Lists every Kit Form configured on the connected Kit account, returning each form\'s numeric ID, name and format (inline, modal, slide in or sticky bar). Use this before kit/form-insert, kit/form-update, kit/post-settings-update or kit/settings-update when the user refers to a Form by name rather than ID, to look up the corresponding numeric Form ID.', 'convertkit' );

	}

	/**
	 * Maps a single raw Form item from the resource cache into the output
	 * shape, adding `format` so the model can distinguish inline forms (which
	 * render in-place) from non-inline forms (modal / slide in / sticky bar,
	 * which trigger from elsewhere). Legacy forms omit the `format` key in
	 * the cached data; they are treated as inline to match the rest of the
	 * Plugin\'s behaviour.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $item   Raw Form item.
	 * @return  array
	 */
	protected function map_item( $item ) {

		return array(
			'id'     => (int) ( $item['id'] ?? 0 ),
			'name'   => (string) ( $item['name'] ?? '' ),
			'format' => isset( $item['format'] ) && $item['format'] !== '' ? (string) $item['format'] : 'inline',
		);

	}

	/**
	 * Returns the JSON Schema for a single Form item, including the `format`
	 * field added by map_item().
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	protected function get_item_schema() {

		return array(
			'type'       => 'object',
			'required'   => array( 'id', 'name', 'format' ),
			'properties' => array(
				'id'     => array(
					'type'        => 'integer',
					'description' => __( 'Numeric ID of the Kit Form.', 'convertkit' ),
				),
				'name'   => array(
					'type'        => 'string',
					'description' => __( 'Human-readable name of the Kit Form.', 'convertkit' ),
				),
				'format' => array(
					'type'        => 'string',
					'enum'        => array( 'inline', 'modal', 'slide in', 'sticky bar' ),
					'description' => __( 'Where and how the Form is displayed. Inline forms render in post content; modal / slide in / sticky bar forms are site-wide overlays triggered elsewhere.', 'convertkit' ),
				),
			),
		);

	}

}
