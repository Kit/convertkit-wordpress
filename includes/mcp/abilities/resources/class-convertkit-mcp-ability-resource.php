<?php
/**
 * Kit MCP Ability: Resource list base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Base class for abilities to list resources (Forms, Tags, Landing Pages, Products).
 *
 * Each subclass represents a single resouce type.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Ability_Resource extends ConvertKit_MCP_Ability {

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
	 * Returns the ability name, derived from the resource slug.
	 *
	 * For example, the Forms list ability is named `kit/forms-list`.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/' . $this->get_resource() . '-list';

	}

	/**
	 * Returns the resource slug for this ability, used in the ability name
	 * and as a hint for clients (e.g. `forms`, `tags`, `landing-pages`,
	 * `products`).
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract protected function get_resource();

	/**
	 * Returns the fully-qualified class name of the ConvertKit_Resource_*
	 * implementation backing this ability.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract protected function get_resource_class();

	/**
	 * Maps a single raw resource item from the resource class' get() method
	 * into the shape exposed in this ability's output.
	 *
	 * The default implementation returns just id and name. Subclasses may
	 * override to expose additional per-item fields (e.g. Forms includes
	 * `format`) — output_schema() should be overridden to match.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $item   Raw item from the resource class' get() method.
	 * @return  array
	 */
	protected function map_item( $item ) {

		return array(
			'id'   => (int) ( $item['id'] ?? 0 ),
			'name' => (string) ( $item['name'] ?? '' ),
		);

	}

	/**
	 * Returns the JSON Schema describing a single item in the output `items`
	 * array.
	 *
	 * Subclasses may override to add per-resource fields. Keep in sync with
	 * map_item() — both describe the same shape, one in schema form and one
	 * in PHP.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	protected function get_item_schema() {

		return array(
			'type'       => 'object',
			'required'   => array( 'id', 'name' ),
			'properties' => array(
				'id'   => array(
					'type'        => 'integer',
					'description' => __( 'Numeric ID of the resource item.', 'convertkit' ),
				),
				'name' => array(
					'type'        => 'string',
					'description' => __( 'Human-readable name of the resource item.', 'convertkit' ),
				),
			),
		);

	}

	/**
	 * Permission callback for resource-list abilities.
	 *
	 * Listing available Kit resources is permitted for anyone who can edit
	 * posts — the same capability gate that allows placing a Kit element on
	 * a post, where these lists are typically used as a lookup.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input (unused).
	 * @return  bool|WP_Error
	 */
	public function permission_callback( $input ) {

		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'convertkit_mcp_cannot_list_resources',
				__( 'You do not have permission to list Kit resources.', 'convertkit' )
			);
		}

		return true;

	}

	/**
	 * Returns the ability's input JSON Schema.
	 *
	 * Resource-list abilities take no input.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_input_schema() {

		return array(
			'type'       => 'object',
			'properties' => new stdClass(),
		);

	}

	/**
	 * Returns the ability's output JSON Schema.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_output_schema() {

		return array(
			'type'       => 'object',
			'required'   => array( 'count', 'items' ),
			'properties' => array(
				'count' => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'The number of items returned.', 'convertkit' ),
				),
				'items' => array(
					'type'        => 'array',
					'description' => __( 'The resource items.', 'convertkit' ),
					'items'       => $this->get_item_schema(),
				),
			),
		);

	}

	/**
	 * Executes the ability: instantiate the backing resource class, fetch
	 * its cached items, and return them mapped to this ability's output
	 * shape.
	 *
	 * A "no items" result (e.g. the Plugin has not yet cached this resource
	 * from the Kit API) is returned as a successful empty list rather than
	 * an error, so the model can explain the absence to the user.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input (unused).
	 * @return  array|WP_Error
	 */
	public function execute_callback( $input ) {

		// Instantiate the backing resource class.
		$resource_class = $this->get_resource_class();
		if ( ! class_exists( $resource_class ) ) {
			return new WP_Error(
				'convertkit_mcp_resource_class_missing',
				sprintf(
					/* translators: %s: Resource class name */
					__( 'The resource class "%s" does not exist.', 'convertkit' ),
					$resource_class
				)
			);
		}

		$resource = new $resource_class();

		// Fetch the items from the resource cache. ConvertKit_Resource::get()
		// returns false when nothing has been cached; normalise that to an
		// empty array so the output shape is always consistent.
		$items = $resource->get();
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		// Map each raw item to the ability's output shape.
		$mapped = array();
		foreach ( $items as $item ) {
			$mapped[] = $this->map_item( $item );
		}

		return array(
			'count' => count( $mapped ),
			'items' => $mapped,
		);

	}

}
