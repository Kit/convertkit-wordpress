<?php
/**
 * ConvertKit Contact Form 7 Admin Settings class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers Contact Form 7 Settings that can be edited at Settings > Kit > Contact Form 7.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_ContactForm7_Admin_Section extends ConvertKit_Admin_Section_Base {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Define the class that reads/writes settings.
		$this->settings = new ConvertKit_ContactForm7_Settings();

		// Define the settings key.
		$this->settings_key = $this->settings::SETTINGS_NAME;

		// Define the programmatic name, Title and Tab Text.
		$this->name     = 'contactform7';
		$this->title    = __( 'Contact Form 7 Integration Settings', 'convertkit' );
		$this->tab_text = __( 'Contact Form 7', 'convertkit' );

		// Define settings sections.
		$this->settings_sections = array(
			'general' => array(
				'title'    => $this->title,
				'callback' => array( $this, 'print_section_info' ),
				'wrap'     => false,
			),
		);

		parent::__construct();

	}

	/**
	 * Register fields for this section
	 */
	public function register_fields() {

		// No fields are registered, because they are output in a WP_List_Table
		// in this class' render() function.
		// This function is deliberately blank.
	}

	/**
	 * Prints help info for this section.
	 */
	public function print_section_info() {

		?>
		<p>
			<?php
			esc_html_e( 'Kit seamlessly integrates with Contact Form 7 to let you add subscribers using Contact Form 7 forms.', 'convertkit' );
			?>
		</p>
		<p>
			<?php
			printf(
				'%s <code>text*</code> %s <code>your-name</code> %s <code>email*</code> %s <code>your-email</code>%s',
				esc_html__( 'The Contact Form 7 form must have a', 'convertkit' ),
				esc_html__( 'field named', 'convertkit' ),
				esc_html__( 'and an', 'convertkit' ),
				esc_html__( 'field named', 'convertkit' ),
				esc_html__( '. These fields will be sent to Kit for the subscription.', 'convertkit' )
			);
			?>
		</p>
		<p>
			<?php esc_html_e( 'Each Contact Form 7 Form has the following Kit options:', 'convertkit' ); ?>
			<br />
			<code><?php esc_html_e( 'Do not subscribe', 'convertkit' ); ?></code>: <?php esc_html_e( 'Do not subscribe the email address to Kit', 'convertkit' ); ?>
			<br />
			<code><?php esc_html_e( 'Subscribe', 'convertkit' ); ?></code>: <?php esc_html_e( 'Subscribes the email address to Kit', 'convertkit' ); ?>
			<br />
			<code><?php esc_html_e( 'Form', 'convertkit' ); ?></code>: <?php esc_html_e( 'Subscribes the email address to Kit, and adds the subscriber to the Kit form', 'convertkit' ); ?>
			<br />
			<code><?php esc_html_e( 'Tag', 'convertkit' ); ?></code>: <?php esc_html_e( 'Subscribes the email address to Kit, tagging the subscriber', 'convertkit' ); ?>
			<br />
			<code><?php esc_html_e( 'Sequence', 'convertkit' ); ?></code>: <?php esc_html_e( 'Subscribes the email address to Kit, and adds the subscriber to the Kit sequence', 'convertkit' ); ?>
		</p>
		<?php

	}

	/**
	 * Returns the URL for the ConvertKit documentation for this setting section.
	 *
	 * @since   2.0.8
	 *
	 * @return  string  Documentation URL.
	 */
	public function documentation_url() {

		return 'https://help.kit.com/en/articles/2502591-how-to-set-up-the-kit-plugin-on-your-wordpress-website';

	}

	/**
	 * Outputs the section as a WP_List_Table of Contact Form 7 Forms, with options to choose
	 * a ConvertKit Form mapping for each.
	 *
	 * @since   1.9.6
	 */
	public function render() {

		// Render opening container.
		$this->render_container_start();

		do_settings_sections( $this->settings_key );

		// Get Contact Form 7 Forms.
		$cf7_forms = $this->get_cf7_forms();

		// Bail with an error if no Contact Form 7 Forms exist.
		if ( ! $cf7_forms ) {
			$this->output_error( __( 'No Contact Form 7 Forms exist in the Contact Form 7 Plugin.', 'convertkit' ) );
			$this->render_container_end();
			return;
		}

		// Warn the user if any Contact Form 7 Form subscribes to Kit, and Contact Form 7
		// has no spam protection enabled.
		$this->maybe_output_spam_protection_warning( $cf7_forms );

		// Get Creator Network Recommendations script.
		$creator_network_recommendations         = new ConvertKit_Resource_Creator_Network_Recommendations( 'contact_form_7' );
		$creator_network_recommendations_enabled = $creator_network_recommendations->enabled();

		// Setup WP_List_Table.
		$table = new ConvertKit_WP_List_Table();
		$table->add_column( 'title', __( 'Contact Form 7 Form', 'convertkit' ), true );
		$table->add_column( 'form', __( 'Kit', 'convertkit' ), false );
		$table->add_column( 'email', __( 'Contact Form 7 Email Field', 'convertkit' ), false );
		$table->add_column( 'name', __( 'Contact Form 7 Name Field', 'convertkit' ), false );
		$table->add_column( 'creator_network_recommendations', __( 'Enable Creator Network Recommendations', 'convertkit' ), false );

		// Iterate through Contact Form 7 Forms, building table array.
		$table_rows = array();
		foreach ( $cf7_forms as $cf7_form ) {
			// Build row.
			$table_row = array(
				'title' => $cf7_form['name'],
				'form'  => convertkit_get_subscription_dropdown_field(
					'_wp_convertkit_integration_contactform7_settings[' . $cf7_form['id'] . ']',
					(string) $this->settings->get_convertkit_subscribe_setting_by_cf7_form_id( $cf7_form['id'] ),
					'_wp_convertkit_integration_contactform7_settings_' . $cf7_form['id'],
					'widefat',
					'contact_form_7'
				),
				'email' => 'your-email',
				'name'  => 'your-name',
			);

			// Add Creator Network Recommendations table column.
			if ( $creator_network_recommendations_enabled ) {
				// Show checkbox to enable Creator Network Recommendations for this Contact Form 7 Form.
				$table_row['creator_network_recommendations'] = $this->get_checkbox_field(
					'creator_network_recommendations_' . $cf7_form['id'],
					'1',
					$this->settings->get_creator_network_recommendations_enabled_by_cf7_form_id( $cf7_form['id'] )
				);
			} else {
				// Show a link to the ConvertKit billing page, as a paid plan is required for Creator Network Recommendations.
				$table_row['creator_network_recommendations'] = sprintf(
					'%s <a href="%s" target="_blank">%s</a>',
					esc_html__( 'Creator Network Recommendations requires a', 'convertkit' ),
					convertkit_get_billing_url(),
					esc_html__( 'paid Kit Plan', 'convertkit' )
				);
			}

			// Add row to table of settings.
			$table_rows[] = $table_row;
		}

		// Sort table rows.
		$table_rows = $table->reorder( $table_rows );

		// Set items.
		$table->add_items( $table_rows );
		$table->set_total_items( count( $table_rows ) );

		// Prepare and display WP_List_Table.
		$table->prepare_items();
		$table->display();

		// Register settings field.
		settings_fields( $this->settings_key );

		// Render closing container.
		$this->render_container_end();

		// Render submit button.
		submit_button();

	}

	/**
	 * Outputs a warning naming Contact Form 7 Forms that subscribe email addresses
	 * to Kit, and have no spam protection.
	 *
	 * @since   3.4.5
	 *
	 * @param   array $cf7_forms   Contact Form 7 Forms.
	 */
	private function maybe_output_spam_protection_warning( $cf7_forms ) {

		// Build a list of Contact Form 7 Forms that subscribe to Kit, with no spam protection.
		$unprotected_forms = array();
		foreach ( $cf7_forms as $cf7_form ) {
			if ( ! $this->settings->get_convertkit_subscribe_setting_by_cf7_form_id( $cf7_form['id'] ) ) {
				continue;
			}

			if ( $this->form_has_spam_protection( $cf7_form['id'] ) ) {
				continue;
			}

			$unprotected_forms[] = $cf7_form['name'];
		}

		// Bail if all Contact Form 7 Forms subscribing to Kit have spam protection.
		if ( ! count( $unprotected_forms ) ) {
			return;
		}

		// Akismet only checks Contact Form 7 Forms whose fields define an `akismet:` option,
		// so tell the user to configure their fields instead of enabling a provider.
		if ( $this->has_akismet() ) {
			$call_to_action_url  = 'https://contactform7.com/spam-filtering-with-akismet/';
			$call_to_action_text = __( 'Akismet is enabled in Contact Form 7, but these forms do not use it. Add Akismet options to each form\'s fields.', 'convertkit' );
		} else {
			$call_to_action_url  = admin_url( 'admin.php?page=wpcf7-integration' );
			$call_to_action_text = __( 'Enable reCAPTCHA, Turnstile or Akismet in Contact Form 7.', 'convertkit' );
		}

		$this->output_warning(
			sprintf(
				'%s <strong>%s</strong>. %s <a href="%s">%s</a>',
				esc_html__( 'The following Contact Form 7 Forms subscribe email addresses to Kit, but have no spam protection:', 'convertkit' ),
				esc_html( implode( ', ', $unprotected_forms ) ),
				esc_html__( 'Bots may submit fake email addresses, which are then added to your Kit account.', 'convertkit' ),
				esc_url( $call_to_action_url ),
				esc_html( $call_to_action_text )
			),
			'convertkit-spam-protection-warning'
		);

	}

	/**
	 * Determines if the given Contact Form 7 Form has spam protection.
	 *
	 * @since   3.4.5
	 *
	 * @param   int $cf7_form_id   Contact Form 7 Form ID.
	 * @return  bool
	 */
	private function form_has_spam_protection( $cf7_form_id ) {

		// Contact Form 7 configures reCAPTCHA and Turnstile site wide, applying to
		// every Contact Form 7 Form.
		if ( $this->has_site_wide_spam_protection() ) {
			return true;
		}

		return $this->form_has_akismet( $cf7_form_id );

	}

	/**
	 * Determines if Contact Form 7 has reCAPTCHA or Turnstile configured, which
	 * apply to every Contact Form 7 Form.
	 *
	 * @since   3.4.5
	 *
	 * @return  bool
	 */
	private function has_site_wide_spam_protection() {

		if ( ! class_exists( 'WPCF7_Integration' ) ) {
			return false;
		}

		$integration = WPCF7_Integration::get_instance();

		foreach ( array( 'recaptcha', 'turnstile' ) as $name ) {
			$service = $integration->get_service( $name );
			if ( $service && $service->is_active() ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Determines if Contact Form 7's Akismet service is configured.
	 *
	 * @since   3.4.5
	 *
	 * @return  bool
	 */
	private function has_akismet() {

		if ( ! class_exists( 'WPCF7_Integration' ) ) {
			return false;
		}

		$service = WPCF7_Integration::get_instance()->get_service( 'akismet' );

		return ( $service && $service->is_active() );

	}

	/**
	 * Determines if Akismet checks the given Contact Form 7 Form.
	 *
	 * Contact Form 7 only sends a Form to Akismet when one or more of the Form's
	 * fields define an akismet: option, so an active Akismet service alone doesn't
	 * mean the Form is checked.
	 *
	 * @since   3.4.5
	 *
	 * @param   int $cf7_form_id   Contact Form 7 Form ID.
	 * @return  bool
	 */
	private function form_has_akismet( $cf7_form_id ) {

		if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
			return false;
		}

		// Bail if Akismet isn't configured.
		if ( ! $this->has_akismet() ) {
			return false;
		}

		// Bail if the Form can't be read.
		$contact_form = WPCF7_ContactForm::get_instance( $cf7_form_id );
		if ( ! $contact_form ) {
			return false;
		}

		// Determine if any of the Form's fields define an akismet: option.
		foreach ( $contact_form->scan_form_tags() as $tag ) {
			if ( $tag->get_option( 'akismet', '(author|author_email|author_url)', true ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Gets available forms from CF7
	 */
	private function get_cf7_forms() {

		$forms = array();

		$result = new WP_Query(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		if ( ! count( $result->posts ) ) {
			return false;
		}

		foreach ( $result->posts as $post ) {
			$forms[] = array(
				'id'   => $post->ID,
				'name' => $post->post_title,
			);
		}

		return $forms;

	}

}

// Register Admin Settings section.
add_filter(
	'convertkit_admin_settings_register_sections',
	/**
	 * Register WishList Member as a section at Settings > Kit.
	 *
	 * @param   array   $sections   Settings Sections.
	 * @return  array
	 */
	function ( $sections ) {

		// Bail if Contact Form 7 isn't enabled.
		if ( ! defined( 'WPCF7_VERSION' ) ) {
			return $sections;
		}

		// Register this class as a section at Settings > Kit.
		$sections['contactform7'] = new ConvertKit_ContactForm7_Admin_Section();
		return $sections;

	}
);
