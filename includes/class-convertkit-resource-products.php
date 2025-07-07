<?php
/**
 * ConvertKit Products Resource class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Reads ConvertKit Products from the options table, and refreshes
 * ConvertKit Products data stored locally from the API.
 *
 * @since   2.0.0
 */
class ConvertKit_Resource_Products extends ConvertKit_Resource_V4 {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = 'convertkit_products';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = 'products';

	/**
	 * Constructor.
	 *
	 * @since   2.0.0
	 *
	 * @param   bool|string $context    Context.
	 */
	public function __construct( $context = false ) {

		// Initialize the API if the Access Token has been defined in the Plugin Settings.
		$settings = new ConvertKit_Settings();
		if ( $settings->has_access_and_refresh_token() ) {
			$this->api = new ConvertKit_API_V4(
				CONVERTKIT_OAUTH_CLIENT_ID,
				CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
				$settings->get_access_token(),
				$settings->get_refresh_token(),
				$settings->debug_enabled(),
				$context
			);
		}

		// Call parent initialization function.
		parent::init();

	}

	/**
	 * Returns the commerce.js URL based on the account's ConvertKit Domain.
	 *
	 * @since   2.0.0
	 *
	 * @return  bool|string     false (if no products) | URL.
	 */
	public function get_commerce_js_url() {

		// Bail if no Products exist in this resource.
		if ( ! $this->exist() ) {
			return false;
		}

		// Fetch the first Product.
		$products = $this->get();
		$product  = reset( $products );

		// Parse the URL.
		$parsed_url = wp_parse_url( $product['url'] );

		// Bail if parsing the URL failed.
		if ( ! $parsed_url ) {
			return false;
		}

		// Bail if the scheme and host could not be obtained from the URL.
		if ( ! array_key_exists( 'scheme', $parsed_url ) || ! array_key_exists( 'host', $parsed_url ) ) {
			return false;
		}

		// Return commerce.js URL.
		return $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/commerce.js';

	}

	/**
	 * Returns the HTML button markup for the given Product ID.
	 *
	 * @since   2.0.0
	 *
	 * @param   int        $id             Product ID.
	 * @param   string     $button_text    Button Text.
	 * @param   array      $css_classes    CSS classes to apply to link (typically included when using Gutenberg).
	 * @param   array      $css_styles     CSS inline styles to apply to link (typically included when using Shortcode or third party page builder module / widget).
	 * @param   bool|array $options {
	 *     Optional. An array of settings.
	 *
	 *     @type bool           $disable_modal      If true, the button's link will open in a new browser window/tab, instead of a modal
	 *     @type bool|string    $discount_code      Discount Code to include.
	 *     @type bool           $checkout           If true, the button's link will open the checkout step.
	 * }
	 * @return  WP_Error|string                 Button HTML
	 */
	public function get_html( $id, $button_text, $css_classes = array(), $css_styles = array(), $options = false ) {

		// Define default options for the button.
		$defaults = array(
			'disable_modal' => false,
			'discount_code' => false,
			'checkout'      => false,
		);

		// If option are supplied, merge with defaults.
		$options = ( ! $options ? $defaults : array_merge( $defaults, $options ) );

		// Cast ID to integer.
		$id = absint( $id );

		// Bail if the resources are a WP_Error.
		if ( is_wp_error( $this->resources ) ) {
			return $this->resources;
		}

		// Bail if the resource doesn't exist.
		if ( ! isset( $this->resources[ $id ] ) ) {
			return new WP_Error(
				'convertkit_resource_products_get_html',
				sprintf(
					/* translators: Kit Product ID */
					__( 'Kit Product ID %s does not exist on Kit.', 'convertkit' ),
					$id
				)
			);
		}

		// Build product URL.
		$product_url = $this->resources[ $id ]['url'];

		// If a discount code is specified, add it to the URL now.
		if ( $options['discount_code'] ) {
			$product_url = add_query_arg(
				array(
					'promo' => $options['discount_code'],
				),
				$product_url
			);
		}

		// If the URL should directly load the checkout step, add it to the URL now.
		if ( $options['checkout'] ) {
			$product_url = add_query_arg(
				array(
					'step' => 'checkout',
				),
				$product_url
			);
		}

		// Build button HTML.
		$html = '<div class="convertkit-button">';

		// If the request is for the block editor, return a span with no styles, as the block
		// edit will apply the styles to an outer element.
		if ( $this->is_block_editor_request() ) {
			$html .= '<span';
		} else {
			$html .= sprintf(
				'<a href="%s" class="%s" style="%s"%s>',
				esc_url( $product_url ),
				implode( ' ', map_deep( $css_classes, 'sanitize_html_class' ) ),
				implode( ';', map_deep( $css_styles, 'esc_attr' ) ),
				( ! $options['disable_modal'] ? ' data-commerce' : '' )
			);
		}

		$html .= esc_html( $button_text );

		if ( $this->is_block_editor_request() ) {
			$html .= '</span>';
		} else {
			$html .= '</a>';
		}

		$html .= '</div>';

		// Return.
		return $html;

	}

}
