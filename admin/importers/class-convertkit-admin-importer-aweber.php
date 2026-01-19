<?php
/**
 * ConvertKit Admin Importer AWeber class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Import and migrate data from AWeber to Kit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Importer_AWeber extends ConvertKit_Admin_Importer {

	/**
	 * Holds the shortcode name for AWeber forms.
	 *
	 * @since   3.1.5
	 *
	 * @var     string
	 */
	public $shortcode_name = 'aweber';

	/**
	 * Holds the ID attribute name for AWeber forms.
	 *
	 * @since   3.1.5
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = 'formid';

	/**
	 * Holds the block name for AWeber forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $block_name = 'aweber-signupform-block/aweber-shortcode';

	/**
	 * Holds the ID attribute name for AWeber forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $block_id_attribute = 'selectedShortCode';

	/**
	 * Returns an array of AWeber form IDs and titles.
	 *
	 * @since   3.1.5
	 *
	 * @return  array
	 */
	public function get_forms() {

		global $aweber_webform_plugin;

		// If the AWeber Plugin is not active, fall back to showing the AWeber Form IDs found in the posts, if any.
		if ( is_null( $aweber_webform_plugin ) ) {
			return $this->get_forms_detected_in_posts();
		}

		// Fetch AWeber account, using OAuth1 or OAuth2.
		// This is how the AWeber Plugin fetches the account data, as nothing is cached in their Plugin or the database.
		$response = $aweber_webform_plugin->getAWeberAccount(
			get_option( $aweber_webform_plugin->adminOptionsName ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			get_option( $aweber_webform_plugin->oauth2TokensOptions ) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		);

		// If no account is returned, fall back to showing the AWeber Form IDs found in the posts, if any.
		if ( ! isset( $response['account'] ) ) {
			return $this->get_forms_detected_in_posts();
		}

		// Get account, which contains forms and form split tests.
		$account              = $response['account'];
		$web_forms            = $account->getWebForms();
		$web_form_split_tests = $account->getWebFormSplitTests();

		// Build array of forms.
		$forms = array();
		foreach ( $web_forms as $form ) {
			$forms[ $form->id ] = sprintf( '%s: %s', __( 'Sign Up Form', 'convertkit' ), $form->name );
		}
		foreach ( $web_form_split_tests as $form ) {
			$forms[ $form->id ] = sprintf( '%s: %s', __( 'Split Tests', 'convertkit' ), $form->name );
		}

		// Return forms.
		return $forms;

	}

	/**
	 * Returns an array of AWeber form IDs and titles found in the posts.
	 *
	 * @since   3.1.5
	 *
	 * @return  array
	 */
	private function get_forms_detected_in_posts() {

		$forms = array();

		foreach ( $this->get_form_ids_in_posts() as $form_id ) {
			$forms[ $form_id ] = sprintf( 'AWeber Form ID #%s', $form_id );
		}

		return $forms;

	}

}
