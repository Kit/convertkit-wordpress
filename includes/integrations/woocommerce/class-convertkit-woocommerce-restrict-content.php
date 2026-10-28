<?php
/**
 * ConvertKit WooCommerce Restrict Content class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Hides a WooCommerce Product's price, short description and add to cart form,
 * and prevents it being added to the cart, when the Product's Member Content
 * setting is enabled and the visitor doesn't have access.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_WooCommerce_Restrict_Content {

	/**
	 * The restricted Product ID being viewed, that the visitor cannot access.
	 *
	 * @since   3.4.7
	 *
	 * @var     int
	 */
	private $product_id = 0;

	/**
	 * Constructor.
	 *
	 * @since   3.4.7
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'maybe_restrict_product' ), 6 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 10, 2 );

		// Restrict the Product's description and short description wherever WooCommerce outputs them,
		// such as the Store API and Product blocks.
		add_filter( 'woocommerce_product_get_description', array( $this, 'maybe_restrict_description' ), 10, 2 );
		add_filter( 'woocommerce_product_variation_get_description', array( $this, 'maybe_restrict_description' ), 10, 2 );
		add_filter( 'woocommerce_product_get_short_description', array( $this, 'maybe_restrict_short_description' ), 10, 2 );

	}

	/**
	 * Hides the Product's price, short description and add to cart form if the visitor
	 * cannot access the Product. The Product's description is restricted by
	 * ConvertKit_Output_Restrict_Content.
	 *
	 * @since   3.4.7
	 */
	public function maybe_restrict_product() {

		// Bail if WooCommerce isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if not a singular Product.
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		// Bail if the visitor can view the Product.
		if ( WP_ConvertKit()->get_class( 'output_restrict_content' )->can_view_post( get_queried_object_id() ) ) {
			return;
		}

		$this->product_id = get_queried_object_id();

		// Hide the price and short description.
		add_filter( 'woocommerce_get_price_html', array( $this, 'hide_price' ), 10, 2 );
		add_filter( 'woocommerce_short_description', array( $this, 'hide_short_description' ) );
		add_filter( 'render_block_core/post-excerpt', array( $this, 'hide_block' ), 10, 3 );

		// Mark the Product as not purchasable, for themes that check this before showing an add to cart button.
		add_filter( 'woocommerce_is_purchasable', array( $this, 'is_purchasable' ), 10, 2 );

		// Hide the add to cart form for each Product type. These actions are used by WooCommerce's
		// templates, its Add to Cart Form block and page builders.
		remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
		remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
		remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
		remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
		add_filter( 'render_block_woocommerce/add-to-cart-with-options', array( $this, 'hide_block' ), 10, 3 );

		// Remove the price and description from the Product's structured data.
		add_filter( 'woocommerce_structured_data_product', array( $this, 'hide_structured_data' ), 10, 2 );

		// Always show the Description tab, as it's where the Member Content call to action is output.
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_description_tab' ) );

	}

	/**
	 * Hides the restricted Product's price.
	 *
	 * @since   3.4.7
	 *
	 * @param   string     $price      Price HTML.
	 * @param   WC_Product $product    Product.
	 * @return  string
	 */
	public function hide_price( $price, $product ) {

		if ( ! $this->is_restricted_product( $product ) ) {
			return $price;
		}

		return '';

	}

	/**
	 * Hides the restricted Product's short description.
	 *
	 * @since   3.4.7
	 *
	 * @param   string $short_description  Short description.
	 * @return  string
	 */
	public function hide_short_description( $short_description ) {

		if ( get_the_ID() !== $this->product_id ) {
			return $short_description;
		}

		return '';

	}

	/**
	 * Hides a block's output when it's for the restricted Product.
	 *
	 * @since   3.4.7
	 *
	 * @param   string   $block_content  Block HTML.
	 * @param   array    $block          Block.
	 * @param   WP_Block $instance       Block instance.
	 * @return  string
	 */
	public function hide_block( $block_content, $block, $instance ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		$post_id = isset( $instance->context['postId'] ) ? absint( $instance->context['postId'] ) : get_the_ID();

		if ( $post_id !== $this->product_id ) {
			return $block_content;
		}

		return '';

	}

	/**
	 * Removes the restricted Product's price and description from its structured data.
	 *
	 * @since   3.4.7
	 *
	 * @param   array      $markup     Structured data.
	 * @param   WC_Product $product    Product.
	 * @return  array
	 */
	public function hide_structured_data( $markup, $product ) {

		if ( ! $this->is_restricted_product( $product ) ) {
			return $markup;
		}

		unset( $markup['offers'], $markup['description'] );
		return $markup;

	}

	/**
	 * Adds the Description tab if WooCommerce didn't add it because the Product has no description.
	 *
	 * @since   3.4.7
	 *
	 * @param   array $tabs   Product tabs.
	 * @return  array
	 */
	public function add_description_tab( $tabs ) {

		if ( isset( $tabs['description'] ) ) {
			return $tabs;
		}

		$tabs['description'] = array(
			'title'    => __( 'Description', 'convertkit' ),
			'priority' => 10,
			'callback' => 'woocommerce_product_description_tab',
		);

		return $tabs;

	}

	/**
	 * Marks the restricted Product as not purchasable.
	 *
	 * @since   3.4.7
	 *
	 * @param   bool       $purchasable    Is purchasable.
	 * @param   WC_Product $product        Product.
	 * @return  bool
	 */
	public function is_purchasable( $purchasable, $product ) {

		if ( ! $this->is_restricted_product( $product ) ) {
			return $purchasable;
		}

		return false;

	}

	/**
	 * Prevents a restricted Product being added to the cart by a visitor who cannot access it,
	 * including when added by URL, AJAX or WooCommerce's Store API.
	 *
	 * @since   3.4.7
	 *
	 * @param   bool $passed       Passed validation.
	 * @param   int  $product_id   Product ID.
	 * @return  bool
	 */
	public function validate_add_to_cart( $passed, $product_id ) {

		// Bail if validation has already failed.
		if ( ! $passed ) {
			return $passed;
		}

		// Bail if the visitor can view the Product.
		if ( WP_ConvertKit()->get_class( 'output_restrict_content' )->can_view_post( absint( $product_id ) ) ) {
			return $passed;
		}

		wc_add_notice( __( 'Sorry, this product is only available to members.', 'convertkit' ), 'error' );
		return false;

	}

	/**
	 * Replaces the Product's description with the content preview if the visitor cannot access the Product.
	 * A variation's description is hidden.
	 *
	 * @since   3.4.8
	 *
	 * @param   string     $description    Description.
	 * @param   WC_Product $product        Product.
	 * @return  string
	 */
	public function maybe_restrict_description( $description, $product ) {

		global $post;

		// Bail if the visitor can view the Product.
		if ( $this->can_view_product( $product ) ) {
			return $description;
		}

		// Hide a variation's description.
		if ( $product->get_parent_id() ) {
			return '';
		}

		// Setup the Product as the global Post, which the content preview requires.
		$original_post = $post;
		$post          = get_post( $product->get_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );

		$description = WP_ConvertKit()->get_class( 'output_restrict_content' )->maybe_restrict_content_preview( $description );

		// Restore the original global Post.
		$post = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( $post ) {
			setup_postdata( $post );
		}

		return $description;

	}

	/**
	 * Hides the Product's short description if the visitor cannot access the Product.
	 *
	 * @since   3.4.8
	 *
	 * @param   string     $short_description  Short description.
	 * @param   WC_Product $product            Product.
	 * @return  string
	 */
	public function maybe_restrict_short_description( $short_description, $product ) {

		// Bail if the visitor can view the Product.
		if ( $this->can_view_product( $product ) ) {
			return $short_description;
		}

		return '';

	}

	/**
	 * Determines if the visitor can view the given Product, or its parent if it's a variation.
	 *
	 * @since   3.4.8
	 *
	 * @param   WC_Product $product    Product.
	 * @return  bool
	 */
	private function can_view_product( $product ) {

		$product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

		// Users who can edit the Product can view it, so the WooCommerce REST API and other tools can access its content.
		if ( current_user_can( 'edit_post', $product_id ) ) {
			return true;
		}

		return WP_ConvertKit()->get_class( 'output_restrict_content' )->can_view_post( $product_id );

	}

	/**
	 * Determines if the given Product, or its parent if it's a variation, is the restricted Product.
	 *
	 * @since   3.4.7
	 *
	 * @param   WC_Product $product    Product.
	 * @return  bool
	 */
	private function is_restricted_product( $product ) {

		return in_array( $this->product_id, array( $product->get_id(), $product->get_parent_id() ), true );

	}

	/**
	 * Determines if the WooCommerce Plugin is active.
	 *
	 * @since   3.4.7
	 *
	 * @return  bool    Plugin Active.
	 */
	public function is_active() {

		return defined( 'WC_PLUGIN_FILE' );

	}

}

// Bootstrap.
add_action(
	'convertkit_initialize_global',
	function () {

		new ConvertKit_WooCommerce_Restrict_Content();

	}
);
