<?php
/**
 * Plugin activation, update and deactivation class.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */

/**
 * Runs any steps required on plugin activation, update and deactivation.
 *
 * @package ConvertKit
 * @author  ConvertKit
 * @version 1.9.7.4
 */
class ConvertKit_Setup {

	/**
	 * Runs routines when the Plugin is activated.
	 *
	 * @since   1.9.7.4
	 */
	public function activate() {

		// Call any functions to e.g. schedule WordPress Cron events now.
		$this->schedule_cron_events();

		// Install entries database table.
		$entries = new ConvertKit_Form_Entries();
		$entries->create_database_table();

	}

	/**
	 * Runs routines on every Plugin request e.g.
	 * ensuring WordPress Cron events are scheduled.
	 *
	 * @since   2.6.6
	 */
	public function initialize() {

		// Call any functions to e.g. schedule WordPress Cron events now.
		$this->schedule_cron_events();

	}

	/**
	 * Runs routines if the Plugin version has been updated.
	 *
	 * @since   1.9.7.4
	 */
	public function update() {

		// Get installed Plugin version.
		$current_version = get_option( 'convertkit_version' );

		// If the version number matches the plugin version, no update routines
		// need to run.
		if ( $current_version === CONVERTKIT_PLUGIN_VERSION ) {
			return;
		}

		/**
		 * 3.0.0: Migrate reCAPTCHA settings from Restrict Content to General settings.
		 * Install entries database table.
		 */
		if ( version_compare( $current_version, '3.0.0', '<' ) ) {
			$this->migrate_recaptcha_settings();

			// Install entries database table.
			$entries = new ConvertKit_Form_Entries();
			$entries->create_database_table();
		}

		/**
		 * 2.6.6: Migrate 'Default' Form value in _wp_convertkit_term_meta[form] from 0 to -1,
		 * to match Posts.
		 */
		if ( version_compare( $current_version, '2.6.6', '<' ) ) {
			$this->migrate_term_default_form_settings();
		}

		/**
		 * 2.5.4: Migrate WishList Member to ConvertKit Form Mappings
		 */
		if ( ! $current_version || version_compare( $current_version, '2.5.4', '<' ) ) {
			$this->migrate_wlm_none_setting();
			$this->migrate_wlm_form_tag_mapping_settings();
		}

		/**
		 * 2.5.3: Migrate Third Party Form integrations' 'None' option values from `default` to blank.
		 */
		if ( ! $current_version || version_compare( $current_version, '2.5.3', '<' ) ) {
			$this->migrate_contact_form_7_none_setting();
			$this->migrate_forminator_none_setting();
			$this->migrate_wlm_none_setting();
		}

		/**
		 * 2.5.2: Migrate Forminator to ConvertKit Form Mappings
		 */
		if ( ! $current_version || version_compare( $current_version, '2.5.2', '<' ) ) {
			$this->migrate_forminator_form_mapping_settings();
		}

		/**
		 * 2.5.2: Migrate Contact Form 7 to ConvertKit Form Mappings
		 */
		if ( ! $current_version || version_compare( $current_version, '2.5.2', '<' ) ) {
			$this->migrate_contact_form_7_form_mapping_settings();
		}

		/**
		 * 2.5.0: Get Access token for API version 4.0 using a v3 API Key and Secret.
		 */
		if ( ! $current_version || version_compare( $current_version, '2.5.0', '<' ) ) {
			$this->maybe_get_access_token_by_api_key_and_secret();
		}

		/**
		 * 2.4.9.1+: Migrate ck_default_form to _wp_convertkit_term_meta[form], as Term settings
		 * support multiple options (form, position etc).
		 */
		if ( version_compare( $current_version, '2.4.9.1', '<' ) ) {
			$this->migrate_term_form_settings();
		}

		/**
		 * 1.6.1+: Refresh Forms, Landing Pages and Tags data stored in settings,
		 * to get new Forms Builder Settings.
		 */
		if ( version_compare( $current_version, '1.6.1', '<' ) ) {
			$this->refresh_resources();
		}

		/**
		 * 1.9.6+: Migrate _wp_convertkit_settings[default_form] to _wp_convertkit_settings[page_form] and
		 * _wp_convertkit_settings[post_form], now that each Post Type has its own Default Form setting
		 * in Settings > Kit > General.
		 */
		if ( version_compare( $current_version, '1.9.6', '<' ) ) {
			$this->migrate_default_form_settings();
		}

		/**
		 * 1.9.7.4+: Schedule Post Resources' Cron event to refresh Posts cache hourly,
		 * as the activate() routine won't pick this up for existing active installations.
		 */
		if ( version_compare( $current_version, '1.9.7.4', '<' ) ) {
			$posts = new ConvertKit_Resource_Posts( 'cron' );
			$posts->schedule_cron_event();
		}

		// Update the installed version number in the options table.
		update_option( 'convertkit_version', CONVERTKIT_PLUGIN_VERSION );

	}

	/**
	 * 3.0.0: Migrate reCAPTCHA settings from Restrict Content settings to General settings.
	 *
	 * @since   3.0.0
	 */
	private function migrate_recaptcha_settings() {

		// Read Restrict Content settings directly from options table,
		// as Convertkit_Settings_Restrict_Content won't have helper
		// methods to return the specific settings.
		$settings = get_option( '_wp_convertkit_settings_restrict_content' );

		// If no reCAPTCHA settings exist, bail.
		if ( ! isset( $settings['recaptcha_site_key'] ) || ! isset( $settings['recaptcha_secret_key'] ) || ! isset( $settings['recaptcha_minimum_score'] ) ) {
			return;
		}

		// Load settings class, saving the reCAPTCHA settings to the General settings.
		$convertkit_settings = new ConvertKit_Settings();
		$convertkit_settings->save(
			array(
				'recaptcha_site_key'      => $settings['recaptcha_site_key'],
				'recaptcha_secret_key'    => $settings['recaptcha_secret_key'],
				'recaptcha_minimum_score' => $settings['recaptcha_minimum_score'],
			)
		);

		// Remove reCAPTCHA settings from Restrict Content settings.
		unset( $settings['recaptcha_site_key'] );
		unset( $settings['recaptcha_secret_key'] );
		unset( $settings['recaptcha_minimum_score'] );
		update_option( '_wp_convertkit_settings_restrict_content', $settings );

	}

	/**
	 * Change the Default value of 0 to -1 in wp_convertkit_term_meta[form], to
	 * match how the Default value is stored in Posts.
	 *
	 * @since   2.6.6
	 */
	private function migrate_term_default_form_settings() {

		// Get all Terms that have ConvertKit settings defined.
		$query = new WP_Term_Query(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'key'        => '_wp_convertkit_term_meta',
						'comparison' => 'EXISTS',
					),
				),
			)
		);

		// Bail if no Terms exist.
		if ( ! $query->terms ) {
			return;
		}

		// Iterate through Terms, mapping settings.
		foreach ( $query->terms as $term_id ) {
			$term_settings = new ConvertKit_Term( $term_id );

			// If the Form setting is Default i.e. it does not have a formchange it from 0 to -1.
			if ( ! $term_settings->has_form() ) {
				$term_settings->save(
					array(
						'form' => -1,
					)
				);
			}
		}

	}

	/**
	 * 2.5.4: Migrate WLM settings:
	 * - Prefix any WishList Member to ConvertKit Form ID mappings with `form:`,
	 * - Prefix any WishList Member to ConvertKit Tag ID mappings with `tag:`,
	 * - Standardise the settings keys to the format {wlm_level_id}_subscribe
	 * and {wlm_level_id}_unsubscribe.
	 *
	 * @since   2.5.4
	 */
	private function migrate_wlm_form_tag_mapping_settings() {

		$convertkit_wlm_settings = new ConvertKit_Wishlist_Settings();

		// Bail if no settings exist.
		if ( ! $convertkit_wlm_settings->has_settings() ) {
			return;
		}

		// Define new array for settings.
		$settings = array();

		// Iterate through settings.
		foreach ( $convertkit_wlm_settings->get() as $key => $convertkit_form_or_tag_id ) {
			// Split the settings key.
			list( $wlm_level_id, $type ) = explode( '_', $key );

			switch ( $type ) {
				case 'form':
					// This is the action to perform when the user is added to the WLM Level.
					// Use a new name for the setting key to reflect this.
					// < 2.5.4, forms were the only option here, so prefix the resource ID with `form:`.
					$settings[ $wlm_level_id . '_add' ] = ( empty( $convertkit_form_or_tag_id ) ? '' : 'form:' . $convertkit_form_or_tag_id );
					break;

				case 'unsubscribe':
					// This is the action to perform when the user is removed from the WLM Level.
					// Use a new name for the setting key to reflect this.
					// < 2.5.4, tags were the only option here, so prefix the resource ID with `tag:`.
					$settings[ $wlm_level_id . '_remove' ] = ( empty( $convertkit_form_or_tag_id ) ? '' : 'tag:' . $convertkit_form_or_tag_id );
					break;
			}
		}

		// Update settings.
		update_option( $convertkit_wlm_settings::SETTINGS_NAME, $settings );

	}

	/**
	 * 2.5.3: Migrate Third Party Form integrations' 'None' option values from `default` to blank.
	 *
	 * 2.4.9 changed the 'None' label's value from `default` to a blank string, as the v4 API's
	 * `add_subscriber_to_form()` method introduces type declarations, which would result in
	 * an uncaught TypeError when passing a non integer value.
	 *
	 * The PR for that (https://github.com/ConvertKit/convertkit-wordpress/pull/655) didn't include
	 * any tests or upgrade/migration routines to change any existing saved settings where the 'None'
	 * label's value was stored as `default`.
	 */
	private function migrate_contact_form_7_none_setting() {

		$convertkit_contact_form_7_settings = new ConvertKit_ContactForm7_Settings();

		// Bail if no settings exist.
		if ( ! $convertkit_contact_form_7_settings->has_settings() ) {
			return;
		}

		// Get settings.
		$settings = $convertkit_contact_form_7_settings->get();

		// Iterate through settings.
		foreach ( $settings as $contact_form_7_form_id => $convertkit_form_id ) {
			// Skip keys that are non-numeric e.g. `creator_network_recommendations_*`.
			if ( ! is_numeric( $contact_form_7_form_id ) ) {
				continue;
			}

			// Change 'default' to a blank string.
			if ( $convertkit_form_id === 'default' ) {
				$settings[ $contact_form_7_form_id ] = '';
			}
		}

		// Update settings.
		update_option( $convertkit_contact_form_7_settings::SETTINGS_NAME, $settings );

	}

	/**
	 * 2.5.3: Migrate Third Party Form integrations' 'None' option values from `default` to blank.
	 *
	 * 2.4.9 changed the 'None' label's value from `default` to a blank string, as the v4 API's
	 * `add_subscriber_to_form()` method introduces type declarations, which would result in
	 * an uncaught TypeError when passing a non integer value.
	 *
	 * The PR for that (https://github.com/ConvertKit/convertkit-wordpress/pull/655) didn't include
	 * any tests or upgrade/migration routines to change any existing saved settings where the 'None'
	 * label's value was stored as `default`.
	 */
	private function migrate_forminator_none_setting() {

		$convertkit_forminator_settings = new ConvertKit_Forminator_Settings();

		// Bail if no settings exist.
		if ( ! $convertkit_forminator_settings->has_settings() ) {
			return;
		}

		// Get settings.
		$settings = $convertkit_forminator_settings->get();

		// Iterate through settings.
		foreach ( $settings as $forminator_form_id => $convertkit_form_id ) {
			// Skip keys that are non-numeric e.g. `creator_network_recommendations_*`.
			if ( ! is_numeric( $forminator_form_id ) ) {
				continue;
			}

			// Change 'default' to a blank string.
			if ( $convertkit_form_id === 'default' ) {
				$settings[ $forminator_form_id ] = '';
			}
		}

		// Update settings.
		update_option( $convertkit_forminator_settings::SETTINGS_NAME, $settings );

	}

	/**
	 * 2.5.3: Migrate Third Party Form integrations' 'None' option values from `default` to blank.
	 *
	 * 2.4.9 changed the 'None' label's value from `default` to a blank string, as the v4 API's
	 * `add_subscriber_to_form()` method introduces type declarations, which would result in
	 * an uncaught TypeError when passing a non integer value.
	 *
	 * The PR for that (https://github.com/ConvertKit/convertkit-wordpress/pull/655) didn't include
	 * any tests or upgrade/migration routines to change any existing saved settings where the 'None'
	 * label's value was stored as `default`.
	 */
	private function migrate_wlm_none_setting() {

		$convertkit_wlm_settings = new ConvertKit_Wishlist_Settings();

		// Bail if no settings exist.
		if ( ! $convertkit_wlm_settings->has_settings() ) {
			return;
		}

		// Get settings.
		$settings = $convertkit_wlm_settings->get();

		// Iterate through settings.
		foreach ( $settings as $wlm_level_id => $value ) {
			// Change 'default' to a blank string.
			if ( $value === 'default' ) {
				$settings[ $wlm_level_id ] = '';
			}
		}

		// Update settings.
		update_option( $convertkit_wlm_settings::SETTINGS_NAME, $settings );

	}

	/**
	 * 2.5.2: Prefix any Forminator to ConvertKit Form ID mappings with `form:`, now that
	 * the Plugin supports adding a subscriber to a Form, Tag or Sequence.
	 *
	 * @since   2.5.2
	 */
	private function migrate_forminator_form_mapping_settings() {

		$convertkit_forminator_settings = new ConvertKit_Forminator_Settings();

		// Bail if no settings exist.
		if ( ! $convertkit_forminator_settings->has_settings() ) {
			return;
		}

		// Get settings.
		$settings = $convertkit_forminator_settings->get();

		// Iterate through settings.
		foreach ( $settings as $forminator_form_id => $convertkit_form_id ) {
			// Skip keys that are non-numeric e.g. `creator_network_recommendations_*`.
			if ( ! is_numeric( $forminator_form_id ) ) {
				continue;
			}

			// Skip values that are blank i.e. no ConvertKit Form ID specified.
			if ( empty( $convertkit_form_id ) ) {
				continue;
			}

			// Skip values that are non-numeric i.e. the `form_` prefix was already added.
			// This should never happen as this routine runs once, but this is a sanity check.
			if ( ! is_numeric( $convertkit_form_id ) ) {
				continue;
			}

			// Prefix the ConvertKit Form ID with `form_`.
			$settings[ $forminator_form_id ] = 'form:' . $convertkit_form_id;
		}

		// Update settings.
		update_option( $convertkit_forminator_settings::SETTINGS_NAME, $settings );

	}

	/**
	 * 2.5.2: Prefix any Contact Form 7 to ConvertKit Form ID mappings with `form:`, now that
	 * the Plugin supports adding a subscriber to a Form, Tag or Sequence.
	 *
	 * @since   2.5.2
	 */
	private function migrate_contact_form_7_form_mapping_settings() {

		$convertkit_contact_form_7_settings = new ConvertKit_ContactForm7_Settings();

		// Bail if no settings exist.
		if ( ! $convertkit_contact_form_7_settings->has_settings() ) {
			return;
		}

		// Get settings.
		$settings = $convertkit_contact_form_7_settings->get();

		// Iterate through settings.
		foreach ( $settings as $contact_form_7_form_id => $convertkit_form_id ) {
			// Skip keys that are non-numeric e.g. `creator_network_recommendations_*`.
			if ( ! is_numeric( $contact_form_7_form_id ) ) {
				continue;
			}

			// Skip values that are blank i.e. no ConvertKit Form ID specified.
			if ( empty( $convertkit_form_id ) ) {
				continue;
			}

			// Skip values that are non-numeric i.e. the `form_` prefix was already added.
			// This should never happen as this routine runs once, but this is a sanity check.
			if ( ! is_numeric( $convertkit_form_id ) ) {
				continue;
			}

			// Prefix the ConvertKit Form ID with `form_`.
			$settings[ $contact_form_7_form_id ] = 'form:' . $convertkit_form_id;
		}

		// Update settings.
		update_option( $convertkit_contact_form_7_settings::SETTINGS_NAME, $settings );

	}

	/**
	 * 2.5.0: Fetch an Access Token, Refresh Token and Expiry for v4 API use
	 * based on the Plugin setting's v3 API Key and Secret.
	 *
	 * @since   2.5.0
	 */
	private function maybe_get_access_token_by_api_key_and_secret() {

		$convertkit_settings = new ConvertKit_Settings();

		// Bail if an Access Token exists; we don't need to fetch another one.
		if ( $convertkit_settings->has_access_token() ) {
			return;
		}

		// Bail if no API Key or Secret.
		if ( empty( $convertkit_settings->get_api_key() ) ) {
			return;
		}
		if ( empty( $convertkit_settings->get_api_secret() ) ) {
			return;
		}

		// Get Access Token by API Key and Secret.
		$api    = new ConvertKit_API_V4( CONVERTKIT_OAUTH_CLIENT_ID, CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI );
		$result = $api->get_access_token_by_api_key_and_secret(
			$convertkit_settings->get_api_key(),
			$convertkit_settings->get_api_secret(),
			get_site_url()
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return;
		}

		// Store the new credentials.
		// We don't use update_credentials(), because the response
		// includes an `expires_at`, not a `created_at` and `expires_in`.
		$convertkit_settings->save(
			array(
				'access_token'  => $result['oauth']['access_token'],
				'refresh_token' => $result['oauth']['refresh_token'],
				'token_expires' => $result['oauth']['expires_at'],
			)
		);

	}

	/**
	 * Migrate ck_default_form to _wp_convertkit_term_meta[form], as Term settings
	 * support multiple options (form, position etc).
	 *
	 * @since   2.4.9.1
	 */
	private function migrate_term_form_settings() {

		// Get all Terms that have ConvertKit settings defined.
		$query = new WP_Term_Query(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'key'        => 'ck_default_form',
						'comparison' => 'EXISTS',
					),
				),
			)
		);

		// Bail if no Terms exist.
		if ( ! $query->terms ) {
			return;
		}

		// Iterate through Terms, mapping settings.
		foreach ( $query->terms as $term_id ) {
			$term_settings = new ConvertKit_Term( $term_id );
			$term_settings->save(
				array(
					'form'          => get_term_meta( $term_id, 'ck_default_form', true ), // Fetch form setting from old meta key.
					'form_position' => '', // Default to no position.
				)
			);

			// Delete old Term meta.
			delete_term_meta( $term_id, 'ck_default_form' );
		}

	}

	/**
	 * 1.9.6+: Migrate _wp_convertkit_settings[default_form] to _wp_convertkit_settings[page_form] and
	 * _wp_convertkit_settings[post_form], now that each Post Type has its own Default Form setting
	 * in Settings > Kit > General.
	 */
	private function migrate_default_form_settings() {

		$convertkit_settings = new ConvertKit_Settings();

		// Bail if no default_form setting exists.
		$settings = get_option( $convertkit_settings::SETTINGS_NAME );
		if ( ! $settings ) {
			return;
		}
		if ( ! array_key_exists( 'default_form', $settings ) ) {
			return;
		}

		// Restructure settings.
		$settings['page_form'] = $settings['default_form'];
		$settings['post_form'] = $settings['default_form'];

		// Remove obsolete default_form setting.
		unset( $settings['default_form'] );

		// Update.
		update_option( $convertkit_settings::SETTINGS_NAME, $settings );

	}

	/**
	 * 1.6.1: Refresh Forms, Landing Pages and Tags data stored in settings,
	 * to get new Forms Builder Settings.
	 */
	private function refresh_resources() {

		$forms         = new ConvertKit_Resource_Forms( 'setup' );
		$landing_pages = new ConvertKit_Resource_Landing_Pages( 'setup' );
		$tags          = new ConvertKit_Resource_Tags( 'setup' );

		$forms->refresh();
		$landing_pages->refresh();
		$tags->refresh();

	}

	/**
	 * Runs routines when the Plugin is deactivated.
	 *
	 * @since   1.9.7.4
	 */
	public function deactivate() {

		// Call any functions to e.g. unschedule WordPress Cron events now.
		$this->unschedule_cron_events();

	}

	/**
	 * Schedules any Plugin specific CRON events, if they do not already exist.
	 *
	 * @since   2.6.6
	 */
	private function schedule_cron_events() {

		$posts = new ConvertKit_Resource_Posts( 'cron' );
		$posts->schedule_cron_event();

	}

	/**
	 * Unschedules any Plugin specific CRON events, if they exist.
	 *
	 * @since   2.6.6
	 */
	private function unschedule_cron_events() {

		$posts = new ConvertKit_Resource_Posts( 'cron' );
		$posts->unschedule_cron_event();

	}

}
