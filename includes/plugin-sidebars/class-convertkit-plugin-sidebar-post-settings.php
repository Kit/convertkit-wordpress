<?php
/**
 * ConvertKit Plugin Sidebar Post Settings class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * ConvertKit Plugin Sidebar Post Settings definition for Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Plugin_Sidebar_Post_Settings extends ConvertKit_Plugin_Sidebar {

	/**
	 * Constructor
	 *
	 * @since   3.3.0
	 */
	public function __construct() {

		// Register this as a plugin sidebar in the ConvertKit Plugin.
		add_filter( 'convertkit_plugin_sidebars', array( $this, 'register' ) );

	}

	/**
	 * Returns this plugin sidebar's programmatic name, excluding the convertkit- prefix.
	 *
	 * @since   3.3.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'post-settings';

	}

	/**
	 * Returns this plugin sidebar's meta key.
	 *
	 * @since   3.3.0
	 *
	 * @return  string
	 */
	public function get_meta_key() {

		return '_wp_convertkit_post_meta';

	}

	/**
	 * Returns this plugin sidebar's title.
	 *
	 * @since   3.3.0
	 */
	public function get_title() {

		return __( 'Kit', 'convertkit' );

	}

	/**
	 * Returns this plugin sidebar's icon.
	 *
	 * @since   3.3.0
	 */
	public function get_icon() {

		return 'resources/backend/images/kit-logo.svg';

	}

	/**
	 * Returns this plugin sidebar's attributes
	 *
	 * @since   3.3.0
	 *
	 * @return  array
	 */
	public function get_attributes() {

		return array(
			'form'             => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'form' ),
			),
			'landing_page'     => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'landing_page' ),
			),
			'tag'              => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'tag' ),
			),
			'restrict_content' => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'restrict_content' ),
			),
		);

	}

	/**
	 * Returns this plugin sidebar's Fields
	 *
	 * @since   3.3.0
	 *
	 * @return  bool|array
	 */
	public function get_fields() {

		// Load resource classes.
		$convertkit_forms         = new ConvertKit_Resource_Forms( 'post_settings' );
		$convertkit_landing_pages = new ConvertKit_Resource_Landing_Pages( 'post_settings' );
		$convertkit_tags          = new ConvertKit_Resource_Tags( 'post_settings' );
		$convertkit_products      = new ConvertKit_Resource_Products( 'post_settings' );

		// Get Forms.
		$forms = array(
			'-1' => esc_html__( 'Default', 'convertkit' ),
			'0'  => esc_html__( 'None', 'convertkit' ),
		);
		if ( $convertkit_forms->exist() ) {
			foreach ( $convertkit_forms->get() as $form ) {
				// Legacy forms don't include a `format` key, so define them as inline.
				$forms[ absint( $form['id'] ) ] = sprintf(
					'%s [%s]',
					sanitize_text_field( $form['name'] ),
					( ! empty( $form['format'] ) ? sanitize_text_field( $form['format'] ) : 'inline' )
				);
			}
		}

		// Get Landing Pages.
		$landing_pages = array(
			'0' => esc_html__( 'None', 'convertkit' ),
		);
		if ( $convertkit_landing_pages->exist() ) {
			foreach ( $convertkit_landing_pages->get() as $landing_page ) {
				$landing_pages[ absint( $landing_page['id'] ) ] = sanitize_text_field( $landing_page['name'] );
			}
		}

		// Get Tags.
		$tags = array(
			'0' => esc_html__( 'None', 'convertkit' ),
		);
		if ( $convertkit_tags->exist() ) {
			foreach ( $convertkit_tags->get() as $tag ) {
				$tags[ absint( $tag['id'] ) ] = sanitize_text_field( $tag['name'] );
			}
		}

		// Get Products.
		$restrict_content = array(
			'0' => esc_html__( 'Do not restrict content to member-only', 'convertkit' ),
		);
		if ( $convertkit_forms->exist() ) {
			$restrict_content['forms'] = array(
				'label'  => esc_html__( 'Forms', 'convertkit' ),
				'values' => array(),
			);
			foreach ( $convertkit_forms->get() as $form ) {
				// Legacy forms don't include a `format` key, so define them as inline.
				$restrict_content['forms']['values'][ 'form_' . absint( $form['id'] ) ] = sprintf(
					'%s [%s]',
					sanitize_text_field( $form['name'] ),
					( ! empty( $form['format'] ) ? sanitize_text_field( $form['format'] ) : 'inline' )
				);
			}
		}
		if ( $convertkit_tags->exist() ) {
			$restrict_content['tags'] = array(
				'label'  => esc_html__( 'Tags', 'convertkit' ),
				'values' => array(),
			);
			foreach ( $convertkit_tags->get() as $tag ) {
				$restrict_content['tags']['values'][ 'tag_' . absint( $tag['id'] ) ] = sanitize_text_field( $tag['name'] );
			}
		}
		if ( $convertkit_products->exist() ) {
			$restrict_content['products'] = array(
				'label'  => esc_html__( 'Products', 'convertkit' ),
				'values' => array(),
			);
			foreach ( $convertkit_products->get() as $product ) {
				$restrict_content['products']['values'][ 'product_' . $product['id'] ] = sanitize_text_field( $product['name'] );
			}
		}

		return array(
			'form'             => array(
				'label'         => __( 'Form', 'convertkit' ),
				'type'          => 'select',
				'description'   => array(
					sprintf(
						'<code>%s</code>: %s <a href="%s" target="_blank">%s</a>',
						esc_html__( 'Default', 'convertkit' ),
						esc_html__( 'Uses the form specified on the', 'convertkit' ),
						esc_url( convertkit_get_settings_link() ),
						esc_html__( 'settings page', 'convertkit' )
					),
					sprintf(
						'<code>%s</code>: %s',
						esc_html__( 'None', 'convertkit' ),
						esc_html__( 'do not display a form.', 'convertkit' )
					),
					__( 'Any other option will display that form after the main content.', 'convertkit' ),
					sprintf(
						'%s <a href="%s" target="_blank">%s</a>',
						esc_html__( 'To make changes to your forms,', 'convertkit' ),
						esc_url( convertkit_get_sign_in_url() ),
						esc_html__( 'sign in to Kit', 'convertkit' )
					),
				),
				'values'        => $forms,
				'resource_type' => 'forms',
			),
			'landing_page'     => array(
				'label'         => __( 'Landing Page', 'convertkit' ),
				'type'          => 'select',
				'description'   => array(
					esc_html__( 'Select a landing page to make it appear in place of this page.', 'convertkit' ),
					sprintf(
						/* translators: Link to sign in to ConvertKit */
						esc_html__( 'To make changes to your landing pages, %s', 'convertkit' ),
						'<a href="' . esc_url( convertkit_get_sign_in_url() ) . '" target="_blank">' . esc_html__( 'sign in to Kit', 'convertkit' ) . '</a>'
					),
				),
				'values'        => $landing_pages,
				'post_type'     => 'page',
				'resource_type' => 'landing_pages',
			),
			'tag'              => array(
				'label'         => __( 'Tag', 'convertkit' ),
				'type'          => 'select',
				'description'   => array(
					esc_html__( 'Select a tag to apply to visitors of this page who are subscribed.', 'convertkit' ),
					esc_html__( 'A visitor is deemed to be subscribed if they have clicked a link in an email to this site which includes their subscriber ID, or have entered their email address in a Kit Form on this site.', 'convertkit' ),
				),
				'values'        => $tags,
				'resource_type' => 'tags',
			),
			'restrict_content' => array(
				'label'         => __( 'Restrict Content', 'convertkit' ),
				'type'          => 'select',
				'description'   => array(
					esc_html__( 'Select the Kit form, tag or product that the visitor must be subscribed to, permitting them access to view this member-only content.', 'convertkit' ),
					sprintf(
						'<code>%s</code>: %s',
						esc_html__( 'Form', 'convertkit' ),
						esc_html__( 'Displays the Kit form. On submission, the email address will be subscribed to the selected form, granting access to the member-only content. Useful to gate free content in return for an email address.', 'convertkit' )
					),
					sprintf(
						'<code>%s</code>: %s',
						esc_html__( 'Tag', 'convertkit' ),
						esc_html__( 'Displays a WordPress styled subscription form. On submission, the email address will be subscribed to the selected tag, granting access to the member-only content. Useful to gate free content in return for an email address.', 'convertkit' )
					),
					sprintf(
						'<code>%s</code>: %s',
						esc_html__( 'Product', 'convertkit' ),
						esc_html__( 'Displays a link to the Kit product, and a login form. Useful to gate content that can only be accessed by purchasing the Kit product.', 'convertkit' )
					),
				),
				'values'        => $restrict_content,
				'resource_type' => 'restrict_content',
			),
		);

	}

	/**
	 * Returns this block's Default Values
	 *
	 * @since   3.3.0
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array(
			'form'             => '-1',
			'landing_page'     => '0',
			'tag'              => '0',
			'restrict_content' => '0',
		);

	}

}
