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
	 * Returns an array of post IDs that contain the AWeber form shortcode.
	 *
	 * @since   3.1.5
	 *
	 * @return  array
	 */
	public function get_forms_in_posts() {

		global $wpdb;

		// Search post_content for [aweber] shortcode and return array of post IDs.
		$results = $wpdb->get_col(
			$wpdb->prepare(
				"
            SELECT ID
            FROM {$wpdb->posts}
            WHERE post_status = %s
            AND post_content LIKE %s
            ",
				'publish',
				'%[' . $this->shortcode_name . '%'
			)
		);

		return $results ? $results : array();

	}

	/**
	 * Returns an array of AWeber form IDs and titles.
	 *
	 * @since   3.1.5
	 *
	 * @return  array
	 */
	public function get_forms() {

		global $aweber_webform_plugin;

		// Bail if the AWeber Plugin is not active, as the only way to fetch forms is via their API.
		// There is no cache of form data.
		if ( is_null( $aweber_webform_plugin ) ) {
			return array();
		}

		// Fetch Aweber account, using OAuth1 or OAuth2.
		// This is how the AWeber Plugin fetches the account data.
		$response = $aweber_webform_plugin->getAWeberAccount(
			get_option( $aweber_webform_plugin->adminOptionsName ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			get_option( $aweber_webform_plugin->oauth2TokensOptions ) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		);

		// If no account is returned, return an empty array.
		if ( ! isset( $response['account'] ) ) {
			return array();
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

}
