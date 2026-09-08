<?php
/**
 * Kit MCP Prompt: Add Form.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Guided workflow to show a Kit Form as a default, on a post or category, or
 * inline in content. Produces a prompt named `kit/add-form`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Prompt_Add_Form extends ConvertKit_MCP_Prompt {

	/**
	 * Returns the prompt's short name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_prompt_name() {

		return 'add-form';

	}

	/**
	 * Returns the prompt's label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Add a Kit Form', 'convertkit' );

	}

	/**
	 * Returns the prompt's description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Show a Kit Form as a site default, on a single post or page, on a category, or inline in content.', 'convertkit' );

	}

	/**
	 * Returns the prompt's arguments.
	 *
	 * @since   3.5.0
	 *
	 * @return  array
	 */
	protected function get_arguments() {

		return array(
			'form'  => array(
				'description' => __( 'The Kit Form to add, by name or numeric ID.', 'convertkit' ),
			),
			'scope' => array(
				'description' => __( 'Where to add it: default, post, category or inline.', 'convertkit' ),
			),
		);

	}

	/**
	 * Returns the prompt text.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Prompt arguments.
	 * @return  array|WP_Error
	 */
	public function execute_callback( $input ) {

		$form  = isset( $input['form'] ) ? trim( (string) $input['form'] ) : '';
		$scope = isset( $input['scope'] ) ? trim( (string) $input['scope'] ) : '';

		$provided = array();
		if ( '' !== $form ) {
			/* translators: %s: Form name or ID. */
			$provided[] = sprintf( __( '- Requested Form: %s', 'convertkit' ), $form );
		}
		if ( '' !== $scope ) {
			/* translators: %s: scope. */
			$provided[] = sprintf( __( '- Requested scope: %s', 'convertkit' ), $scope );
		}

		return $this->render(
			array(
				'# ' . __( 'Add a Kit Form', 'convertkit' ),
				__( 'Goal: show a Kit Form as a site default, on a single post or page, on a category, or inline in content.', 'convertkit' ),
				count( $provided ) ? implode( "\n", $provided ) : '',
				'## ' . __( 'Preflight', 'convertkit' ),
				__( '- Read `kit://forms` and map the requested Form to its numeric ID. If nothing matches, list the available Forms and ask the user.', 'convertkit' ),
				__( '- See `kit://reference/forms` for how the Form shown on a post is resolved (post overrides category overrides the default).', 'convertkit' ),
				'## ' . __( 'Choose the scope', 'convertkit' ),
				__( '- **Default for a post type** — `kit/settings-general-update`.', 'convertkit' ),
				__( '- **A single Page or Post** — `kit/post-settings-update`, setting `form` (needs `post_id`).', 'convertkit' ),
				__( '- **A category** — `kit/category-settings-update`.', 'convertkit' ),
				__( '- **Inline at a chosen position in the content** — `kit/form-insert`.', 'convertkit' ),
				'## ' . __( 'Steps', 'convertkit' ),
				__( '1. Confirm the Form and the scope with the user.', 'convertkit' ),
				__( '2. Apply it with the matching tool above. Confirm before writing.', 'convertkit' ),
				__( '3. Verify with the matching read tool (e.g. `kit/post-settings-get`).', 'convertkit' ),
			)
		);

	}

}
