<?php
/**
 * ConvertKit Output class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Outputs Forms and Landing Pages on the frontend web site, based on
 * the Post and Plugin's configuration.
 *
 * @since   1.9.6
 */
class ConvertKit_Output {

	/**
	 * Holds the ConvertKit Subscriber ID.
	 *
	 * @since   2.0.6
	 *
	 * @var     int|string
	 */
	private $subscriber_id = 0;

	/**
	 * Holds the ConvertKit Plugin Settings class
	 *
	 * @since   1.9.6
	 *
	 * @var     bool|ConvertKit_Settings
	 */
	private $settings = false;

	/**
	 * Holds the ConvertKit Post Settings class
	 *
	 * @since   1.9.6
	 *
	 * @var     bool|ConvertKit_Post
	 */
	private $post_settings = false;

	/**
	 * Holds the available ConvertKit Forms
	 *
	 * @since   1.9.6
	 *
	 * @var     bool|ConvertKit_Resource_Forms
	 */
	private $forms = false;

	/**
	 * Holds the available ConvertKit Landing Pages
	 *
	 * @since   1.9.6
	 *
	 * @var     bool|ConvertKit_Resource_Landing_Pages
	 */
	private $landing_pages = false;

	/**
	 * Constructor. Registers actions and filters to output ConvertKit Forms and Landing Pages
	 * on the frontend web site.
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'get_subscriber_id_from_request' ) );
		add_action( 'wp', array( $this, 'maybe_tag_subscriber' ) );
		add_action( 'template_redirect', array( $this, 'output_form' ) );
		add_action( 'template_redirect', array( $this, 'page_takeover' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'the_content', array( $this, 'append_form_to_content' ) );
		add_filter( 'hooked_block_types', array( $this, 'maybe_register_form_block_on_category_archive' ), 10, 4 );
		add_filter( 'hooked_block_convertkit/form', array( $this, 'append_form_block_to_category_archive' ), 10, 1 );
		add_action( 'wp_footer', array( $this, 'output_global_non_inline_form' ), 1 );
		add_action( 'wp_footer', array( $this, 'output_scripts_footer' ) );

	}

	/**
	 * Tags the subscriber, if:
	 * - a subscriber ID exists in the cookie or URL,
	 * - the WordPress Page has the "Add a Tag" setting specified
	 *
	 * @since   2.4.9.1
	 */
	public function maybe_tag_subscriber() {

		// Bail if no subscriber ID detected.
		if ( ! $this->subscriber_id ) {
			return;
		}

		// Bail if not a singular Post Type supported by ConvertKit.
		if ( ! is_singular( convertkit_get_supported_post_types() ) ) {
			return;
		}

		// Get Post ID.
		$post_id = get_the_ID();

		// Bail if a Post ID couldn't be identified.
		if ( ! $post_id ) {
			return;
		}

		// Get Settings, if they have not yet been loaded.
		if ( ! $this->settings ) {
			$this->settings = new ConvertKit_Settings();
		}

		// Bail if the API hasn't been configured.
		if ( ! $this->settings->has_access_and_refresh_token() ) {
			return;
		}

		// Get ConvertKit Post's Settings, if they have not yet been loaded.
		if ( ! $this->post_settings ) {
			$this->post_settings = new ConvertKit_Post( $post_id );
		}

		// Bail if no "Add a Tag" setting specified for this Page.
		if ( ! $this->post_settings->has_tag() ) {
			return;
		}

		// Initialize the API.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$this->settings->get_access_token(),
			$this->settings->get_refresh_token(),
			$this->settings->debug_enabled(),
			'output'
		);

		// If the subscriber ID is not numeric, it's a cryptographically-signed subscriber ID, set
		// when using the Member Content functionality by Kit's API.
		// Fetch the underlying subscriber ID for the tag_subscriber() method.
		if ( ! is_numeric( $this->subscriber_id ) ) {
			$result = $api->profile( $this->subscriber_id );

			// If an error occured, the subscriber ID is invalid.
			if ( is_wp_error( $result ) ) {
				return;
			}

			// Set the subscriber ID.
			$this->subscriber_id = $result['id'];
		}

		// Tag subscriber.
		$api->tag_subscriber( $this->post_settings->get_tag(), $this->subscriber_id );

	}

	/**
	 * Runs the `convertkit_output_output_form` action for singular Post Types that don't use the_content()
	 * or apply_filters( 'the_content' ) to output a ConvertKit Form.
	 *
	 * @since   1.9.6
	 */
	public function output_form() {

		/**
		 * Outputs a ConvertKit Form on singular Post Types that don't use the_content()
		 * or apply_filters( 'the_content' ).
		 *
		 * @since   1.9.6
		 *
		 * @return  string              Post Content with Form Appended, if applicable
		 */
		do_action( 'convertkit_output_output_form' );

	}

	/**
	 * Outputs a ConvertKit Landing Page if configured, replacing all output for the singular Post Type.
	 *
	 * @since   1.9.6
	 */
	public function page_takeover() {

		$queried_object = get_queried_object();

		// Bail if the queried object cannot be inspected.
		if ( ! isset( $queried_object->post_type ) ) {
			return;
		}

		// Get Post ID.
		$post_id = $queried_object->ID;

		// Bail if the queried object isn't a supported Post Type for Landing Pages.
		if ( $queried_object->post_type !== 'page' ) {
			return;
		}

		// Get ConvertKit Post's Settings, if they have not yet been loaded.
		if ( ! $this->post_settings ) {
			$this->post_settings = new ConvertKit_Post( $post_id );
		}

		// Get Landing Page ID.
		$landing_page_id = $this->post_settings->get_landing_page();

		/**
		 * Define the ConvertKit Landing Page ID to display for the given Post ID,
		 * overriding the Post settings.
		 *
		 * Return false to not display any ConvertKit Landing Page.
		 *
		 * @since   1.9.6
		 *
		 * @param   int     $landing_page_id    Landing Page ID
		 * @param   int     $post_id            Post ID
		 */
		$landing_page_id = apply_filters( 'convertkit_output_page_takeover_landing_page_id', $landing_page_id, $post_id );

		// Bail if no Landing Page is configured to be output.
		if ( empty( $landing_page_id ) ) {
			return;
		}

		// Get available ConvertKit Landing Pages, if they have not yet been loaded.
		if ( ! $this->landing_pages ) {
			$this->landing_pages = new ConvertKit_Resource_Landing_Pages( 'output_landing_page' );
		}

		// Get Landing Page.
		$landing_page = $this->landing_pages->get_html( $this->post_settings->get_landing_page() );

		// Bail if an error occured.
		if ( is_wp_error( $landing_page ) ) {
			return;
		}

		// Replace the favicon with the WordPress site's favicon, if specified.
		$landing_page = $this->landing_pages->replace_favicon( $landing_page );

		/**
		 * Perform any actions immediately prior to outputting the Landing Page.
		 *
		 * Caching and minification Plugins may need to hook here to prevent
		 * CSS / JS minification and lazy loading images, which can interfere
		 * with Landing Pages.
		 *
		 * @since   2.4.4
		 *
		 * @param   string  $landing_page       ConvertKit Landing Page HTML.
		 * @param   int     $landing_page_id    ConvertKit Landing Page ID.
		 * @param   int     $post_id            WordPress Page ID.
		 */
		do_action( 'convertkit_output_landing_page_before', $landing_page, $landing_page_id, $post_id );

		// Output Landing Page.
		// Output is supplied from ConvertKit's API, which is already sanitized.
		echo $landing_page; // phpcs:ignore WordPress.Security.EscapeOutput
		exit;

	}

	/**
	 * Inserts a form to the singular Page, Post or Custom Post Type's Content.
	 *
	 * @param   string $content    Post Content.
	 * @return  string              Post Content with Form Appended, if applicable
	 */
	public function append_form_to_content( $content ) {

		// Bail if not a singular Post Type supported by ConvertKit.
		if ( ! is_singular( convertkit_get_supported_post_types() ) ) {
			return $content;
		}

		// Get Post ID.
		$post_id = get_the_ID();

		// Bail if Post Type is not supported by Kit.
		if ( ! in_array( get_post_type( $post_id ), convertkit_get_supported_post_types(), true ) ) {
			return $content;
		}

		// Get Form ID for the Post.
		$form_id = $this->get_post_form_id( $post_id );

		/**
		 * Define the ConvertKit Form ID to display for the given Post ID,
		 * overriding the Post, Category or Plugin settings.
		 *
		 * Return false to not display any ConvertKit Form.
		 *
		 * @since   1.9.6
		 *
		 * @param   bool|int    $form_id    Form ID
		 * @param   int         $post_id    Post ID
		 */
		$form_id = apply_filters( 'convertkit_output_append_form_to_content_form_id', $form_id, $post_id );

		// Return the Post Content, unedited, if the Form ID is false or zero.
		if ( ! $form_id ) {
			return $content;
		}

		// Get available ConvertKit Forms, if they have not yet been loaded.
		if ( ! $this->forms ) {
			$this->forms = new ConvertKit_Resource_Forms( 'output_form' );
		}

		// Get Form HTML.
		$form = $this->forms->get_html( $form_id, $post_id );

		// If an error occured, it could be because the specified Form ID for the Post either:
		// - belongs to another ConvertKit account (i.e. API credentials were changed in the Plugin, but this Post's specified Form was not changed), or
		// - the form was deleted from the ConvertKit account.
		// Attempt to fallback to the default form for this Post Type.
		if ( is_wp_error( $form ) ) {
			if ( $this->settings->debug_enabled() ) {
				$content .= '<!-- Kit append_form_to_content(): ' . $form->get_error_message() . ' Attempting fallback to Default Form. -->';
			}

			// Get Default Form ID for this Post's Type.
			$form_id = $this->settings->get_default_form( get_post_type( $post_id ) );

			// If no Default Form is specified, just return the Post Content, unedited.
			if ( ! $form_id ) {
				if ( $this->settings->debug_enabled() ) {
					$content .= '<!-- Kit append_form_to_content(): No Default Form exists as a fallback. -->';
				}

				return $content;
			}

			// Get Form HTML.
			$form = $this->forms->get_html( $form_id, $post_id );

			// If an error occured again, the default form doesn't exist in this ConvertKit account.
			// Just return the Post Content, unedited.
			if ( is_wp_error( $form ) ) {
				if ( $this->settings->debug_enabled() ) {
					$content .= '<!-- Kit append_form_to_content(): Default Form: ' . $form->get_error_message() . ' -->';
				}

				return $content;
			}
		}

		// If the Form HTML is empty, it's a modal form that has been set to load in the footer of the site.
		// We don't need to append anything to the content.
		if ( empty( $form ) ) {
			if ( $this->settings->debug_enabled() ) {
				$content .= '<!-- Kit append_form_to_content(): Form is non-inline, appended to footer. -->';
			}

			return $content;
		}

		// If here, we have a ConvertKit Form.
		// Append form to Post's Content, based on the position setting.
		$form_position = $this->settings->get_default_form_position( get_post_type( $post_id ) );

		if ( $this->settings->debug_enabled() ) {
			$content .= '<!-- Kit append_form_to_content(): Form Position: ' . esc_html( $form_position ) . ' -->';
		}

		switch ( $form_position ) {
			case 'before_after_content':
				$content = $form . $content . $form;
				break;

			case 'before_content':
				$content = $form . $content;
				break;

			case 'after_element':
				$element = $this->settings->get_default_form_position_element( get_post_type( $post_id ) );
				$index   = $this->settings->get_default_form_position_element_index( get_post_type( $post_id ) );

				// Check if DOMDocument is installed.
				// It should be installed as mosts hosts include php-dom and php-xml modules.
				// If not, fallback to using preg_match_all(), which is less reliable.
				if ( ! class_exists( 'DOMDocument' ) ) {
					$content = $this->inject_form_after_element_fallback( $content, $element, $index, $form );
					break;
				}

				// Use DOMDocument.
				$content = $this->inject_form_after_element( $content, $element, $index, $form );
				break;

			case 'after_content':
			default:
				// Default behaviour < 2.5.8 was to append the Form after the content.
				$content .= $form;
				break;
		}

		/**
		 * Filter the Post's Content, which includes a ConvertKit Form, immediately before it is output.
		 *
		 * @since   1.9.6
		 *
		 * @param   string  $content        Post Content
		 * @param   string  $form           ConvertKit Form HTML
		 * @param   int     $post_id        Post ID
		 * @param   int     $form_id        ConvertKit Form ID
		 * @param   string  $form_position  Form Position setting for the Post's Type.
		 */
		$content = apply_filters( 'convertkit_frontend_append_form', $content, $form, $post_id, $form_id, $form_position );

		return $content;

	}

	/**
	 * Injects the form after the given element and index, using DOMDocument.
	 *
	 * @since   2.6.2
	 *
	 * @param   string $content        Page / Post Content.
	 * @param   string $tag            HTML tag to insert form after.
	 * @param   int    $index          Number of $tag elements to find before inserting form.
	 * @param   string $form           Form HTML to inject.
	 * @return  string
	 */
	private function inject_form_after_element( $content, $tag, $index, $form ) {

		// If the form is empty, don't inject anything.
		if ( empty( $form ) ) {
			return $content;
		}

		// Define the meta tag.
		$meta_tag = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

		// Wrap content in <html>, <head> and <body> tags now, so we can inject the UTF-8 Content-Type meta tag.
		$modified_content = '<html><head></head><body>' . $content . '</body></html>';

		// Forcibly tell DOMDocument that this HTML uses the UTF-8 charset.
		// <meta charset="utf-8"> isn't enough, as DOMDocument still interprets the HTML as ISO-8859, which breaks character encoding
		// Use of mb_convert_encoding() with HTML-ENTITIES is deprecated in PHP 8.2, so we have to use this method.
		// If we don't, special characters render incorrectly.
		$modified_content = str_replace( '<head>', '<head>' . "\n" . $meta_tag, $modified_content );

		// Load Page / Post content into DOMDocument.
		libxml_use_internal_errors( true );
		$html = new DOMDocument();
		$html->loadHTML( $modified_content, LIBXML_HTML_NODEFDTD );

		// Find the element to append the form to.
		// item() is a zero based index.
		$element_node = $html->getElementsByTagName( $tag )->item( $index - 1 );

		// If the element could not be found, either the number of elements by tag name is less
		// than the requested position the form be inserted in, or no element exists.
		// Append the form to the original content and return.
		if ( is_null( $element_node ) ) {
			return $content . $form;
		}

		// Create new element for the Form.
		$form_node = new DOMDocument();
		$form_node->loadHTML( $form, LIBXML_HTML_NODEFDTD );

		// Append the form to the specific element.
		$element_node->parentNode->insertBefore( $html->importNode( $form_node->documentElement, true ), $element_node->nextSibling ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// Fetch HTML string.
		$modified_content = $html->saveHTML();

		// Remove some HTML tags that DOMDocument adds, returning the output.
		// We do this instead of using LIBXML_HTML_NOIMPLIED in loadHTML(), because Legacy Forms are not always contained in
		// a single root / outer element, which is required for LIBXML_HTML_NOIMPLIED to correctly work.
		$modified_content = str_replace( '<html>', '', $modified_content );
		$modified_content = str_replace( '</html>', '', $modified_content );
		$modified_content = str_replace( '<head>', '', $modified_content );
		$modified_content = str_replace( '</head>', '', $modified_content );
		$modified_content = str_replace( '<body>', '', $modified_content );
		$modified_content = str_replace( '</body>', '', $modified_content );
		$modified_content = str_replace( $meta_tag, '', $modified_content );

		return $modified_content;

	}

	/**
	 * Injects the form after the given element and index, using preg_match_all().
	 * This is less reliable than DOMDocument, and is called if DOMDocument is
	 * not installed on the server.
	 *
	 * @since   2.6.2
	 *
	 * @param   string $content        Page / Post Content.
	 * @param   string $tag            HTML tag to insert form after.
	 * @param   int    $index           Number of $tag elements to find before inserting form.
	 * @param   string $form           Form HTML to inject.
	 * @return  string
	 */
	private function inject_form_after_element_fallback( $content, $tag, $index, $form ) {

		// If the form is empty, don't inject anything.
		if ( empty( $form ) ) {
			return $content;
		}

		// Calculate tag length.
		$tag_length = ( strlen( $tag ) + 3 );

		// Find all closing elements.
		preg_match_all( '/<\/' . $tag . '>/', $content, $matches );

		// If no elements exist, just append the form.
		if ( count( $matches[0] ) === 0 ) {
			$content = $content . $form;
			return $content;
		}

		// If the number of elements is less than the index, we don't have enough elements to add the form to.
		// Just add the form after the content.
		if ( count( $matches[0] ) <= $index ) {
			$content = $content . $form;
			return $content;
		}

		// Iterate through the content to find the element at the configured index e.g. find the 4th closing paragraph.
		$offset = 0;
		foreach ( $matches[0] as $element_index => $element ) {
			$position = strpos( $content, $element, $offset );
			if ( ( $element_index + 1 ) === $index ) {
				return substr( $content, 0, $position + 4 ) . $form . substr( $content, $position + 4 );
			}

			// Increment offset.
			$offset = $position + 1;
		}

		// If here, something went wrong.
		// Just add the form after the content.
		$content = $content . $form;
		return $content;

	}

	/**
	 * Registers the ConvertKit Form block to before or after the Query Loop block, when viewing a Category archive.
	 *
	 * See append_form_block_on_category_archive() configures the block to display the applicable category's Form.
	 *
	 * @since   2.4.9.1
	 *
	 * @param   array                           $hooked_blocks              The list of hooked block types.
	 * @param   string                          $position                   The relative position of the hooked blocks.
	 * @param   string                          $anchor_block               The anchor block type.
	 * @param   WP_Block_Template|WP_Post|array $context                    The block template, template part, wp_navigation post type, or pattern that the anchor block belongs to.
	 * @return  array
	 */
	public function maybe_register_form_block_on_category_archive( $hooked_blocks, $position, $anchor_block, $context ) {

		// Don't append if we're not viewing a category archive.
		if ( ! is_category() ) {
			return $hooked_blocks;
		}

		if ( $context instanceof WP_Block_Template && $context->slug !== 'archive' ) {
			return $hooked_blocks;
		}

		// Don't append if the anchor block isn't the Query Loop block.
		if ( $anchor_block !== 'core/query' ) {
			return $hooked_blocks;
		}

		// Don't append if the Category's form position setting is not defined.
		$form_position = $this->get_term_form_position();
		if ( ! $form_position ) {
			// Unhook this function as we don't need to check again in this request, as we'll
			// never output a form on the Category archive.
			remove_filter( 'hooked_block_types', array( $this, 'maybe_register_form_block_on_category_archive' ), 10 );

			return $hooked_blocks;
		}

		// Don't append if the position doesn't match.
		if ( $form_position !== $position ) {
			return $hooked_blocks;
		}

		// Hook the ConvertKit Form block.
		$hooked_blocks[] = 'convertkit/form';

		// Unhook this function as we don't need to check again in this request, as
		// we have now appended the form.
		remove_filter( 'hooked_block_types', array( $this, 'maybe_register_form_block_on_category_archive' ), 10 );

		return $hooked_blocks;

	}

	/**
	 * Configures the ConvertKit Form block that was hooked below the Query Loop block by maybe_register_form_block_on_category_archive,
	 * defining the Form ID based on the current Category's Form ID.
	 *
	 * @since   2.4.9.1
	 *
	 * @param   array $parsed_hooked_block    The parsed block array for the given hooked block type, or null to suppress the block.
	 * @return  null|array
	 */
	public function append_form_block_to_category_archive( $parsed_hooked_block ) {

		// Sanity check that we're still viewing a Category archive.
		if ( ! is_category() ) {
			// Returning null will unregister the Form block from displaying.
			return null;
		}

		// Get Category archive being viewed.
		$category = get_category( get_query_var( 'cat' ) );

		// Bail if the Category could be found.
		if ( is_wp_error( $category ) || is_null( $category ) ) {
			// Returning null will unregister the Form block from displaying.
			return null;
		}

		// Load Term Settings.
		$term_settings = new ConvertKit_Term( $category->term_id );

		// Bail if no Form specified for the Category.
		if ( ! $term_settings->has_form() ) {
			// Returning null will unregister the Form block from displaying.
			return null;
		}

		// Define the form block attributes to display the given Form ID.
		$parsed_hooked_block['attrs'] = array(
			'id' => absint( $term_settings->get_form() ),
		);

		// Return the Form block with its attributes.
		return $parsed_hooked_block;

	}

	/**
	 * Returns the Post, Category or Plugin ConvertKit Form ID for the given Post.
	 *
	 * If the Post specifies a form to use, returns that Form ID.
	 * If the Post uses the 'Default' setting, and an assigned Category has a Form ID, uses the Category's Form ID.
	 * Otherwise falls back to the Plugin's Default Form ID (if any).
	 *
	 * @since   1.9.6
	 *
	 * @param   int $post_id    Post ID.
	 * @return  bool|string|int     false|'default'|Form ID
	 */
	private function get_post_form_id( $post_id ) {

		// Get Settings, if they have not yet been loaded.
		if ( ! $this->settings ) {
			$this->settings = new ConvertKit_Settings();
		}

		// Get ConvertKit Post's Settings, if they have not yet been loaded.
		if ( ! $this->post_settings ) {
			$this->post_settings = new ConvertKit_Post( $post_id );
		}

		// If the Post specifies a Form to use, return its ID now.
		if ( $this->post_settings->has_form() ) {
			return $this->post_settings->get_form();
		}

		// If the Post specifies that no Form should be used, return false.
		if ( $this->post_settings->uses_no_form() ) {
			return false;
		}

		// Sanity check that the Post uses the Default Form setting, which should be the case
		// because the above conditions were not met.
		if ( ! $this->post_settings->uses_default_form() ) {
			return false;
		}

		// Get Post's Categories.
		$categories = wp_get_post_categories(
			$post_id,
			array(
				'fields' => 'ids',
			)
		);

		// If no Categories exist, use the Default Form.
		if ( ! is_array( $categories ) || ! count( $categories ) ) {
			// Get Post Type.
			return $this->settings->get_default_form( get_post_type( $post_id ) );
		}

		/**
		 * Iterate through Categories in reverse order.
		 * This honors the behaviour < 1.9.6, which states that if multiple Categories each have a Form.
		 * assigned, the last Category with a Form in the wp_get_post_categories() call will be used.
		 */
		$categories = array_reverse( $categories );
		foreach ( $categories as $term_id ) {
			// Load Term Settings.
			$term_settings = new ConvertKit_Term( $term_id );

			// If a Form ID exists, return it now.
			if ( $term_settings->has_form() ) {
				return $term_settings->get_form();
			}

			// If the Term specifies that no Form should be used, return false.
			if ( $term_settings->uses_no_form() ) {
				return false;
			}
		}

		// If here, all Terms were set to display the Default Form.
		// Therefore use the Plugin's Default Form.
		return $this->settings->get_default_form( get_post_type( $post_id ) );

	}

	/**
	 * Returns the Form Position setting for the currently viewed Category.
	 *
	 * @since   2.4.9.1
	 *
	 * @return  bool|string
	 */
	private function get_term_form_position() {

		// Get Category archive being viewed.
		$category = get_category( get_query_var( 'cat' ) );

		// Bail if the Category could be found.
		if ( is_wp_error( $category ) || is_null( $category ) ) {
			return false;
		}

		// Load Term Settings.
		$term_settings = new ConvertKit_Term( $category->term_id );

		// Return false if no form position is defined i.e. we don't want to display
		// it on the Category archive.
		if ( ! $term_settings->has_form_position() ) {
			return false;
		}

		// Return form position.
		return $term_settings->get_form_position();

	}

	/**
	 * Enqueue scripts.
	 *
	 * @since   1.9.6
	 */
	public function enqueue_scripts() {

		// Get Post.
		$post = get_post();

		// Bail if no Post could be fetched.
		if ( ! $post ) {
			return;
		}

		// Get ConvertKit Settings and Post's Settings.
		$settings        = new ConvertKit_Settings();
		$convertkit_post = new ConvertKit_Post( $post->ID );

		// Register scripts that we might use.
		wp_register_script(
			'convertkit-js',
			CONVERTKIT_PLUGIN_URL . 'resources/frontend/js/convertkit.js',
			array(),
			CONVERTKIT_PLUGIN_VERSION,
			true
		);
		wp_localize_script(
			'convertkit-js',
			'convertkit',
			array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'debug'         => $settings->debug_enabled(),
				'nonce'         => wp_create_nonce( 'convertkit' ),
				'subscriber_id' => $this->subscriber_id,
			)
		);

		// Bail if the no scripts setting is enabled.
		if ( $settings->scripts_disabled() ) {
			return;
		}

		// Enqueue.
		wp_enqueue_script( 'convertkit-js' );

	}

	/**
	 * Gets the subscriber ID from the request (either the cookie or the URL).
	 *
	 * @since   1.9.6
	 */
	public function get_subscriber_id_from_request() {

		// Use ConvertKit_Subscriber class to fetch and validate the subscriber ID.
		$subscriber    = new ConvertKit_Subscriber();
		$subscriber_id = $subscriber->get_subscriber_id();

		// If an error occured, the subscriber ID in the request/cookie is not a valid subscriber.
		if ( is_wp_error( $subscriber_id ) ) {
			return;
		}

		$this->subscriber_id = $subscriber_id;

	}

	/**
	 * Outputs a non-inline forms if defined in the Plugin's settings >
	 * Default Forms (Site Wide) setting.
	 *
	 * @since   2.3.3
	 */
	public function output_global_non_inline_form() {

		// Get Settings, if they have not yet been loaded.
		if ( ! $this->settings ) {
			$this->settings = new ConvertKit_Settings();
		}

		// Bail if no non-inline form setting is specified.
		if ( ! $this->settings->has_non_inline_form() ) {
			return;
		}

		// Bail if the Page, Post or Custom Post Type's Form setting is set to 'None'
		// and the Plugin is set to honor this setting.
		if ( $this->post_settings !== false && $this->post_settings->uses_no_form() && $this->settings->non_inline_form_honor_none_setting() ) {
			return;
		}

		// Get form.
		$convertkit_forms = new ConvertKit_Resource_Forms();

		// Iterate through forms.
		foreach ( $this->settings->get_non_inline_form() as $form_id ) {
			// Get Form.
			$form = $convertkit_forms->get_by_id( (int) $form_id );

			// Bail if the Form doesn't exist (this shouldn't happen, but you never know).
			if ( ! $form ) {
				continue;
			}

			// Add the form to the scripts array so it is included in the output.
			add_filter(
				'convertkit_output_scripts_footer',
				function ( $scripts ) use ( $form ) {

					$scripts[] = array(
						'async'    => true,
						'data-uid' => $form['uid'],
						'src'      => $form['embed_js'],
					);

					return $scripts;

				}
			);
		}

	}

	/**
	 * Outputs any JS <script> tags registered with the convertkit_output_scripts_footer
	 * filter
	 *
	 * @since   2.1.4
	 */
	public function output_scripts_footer() {

		// Don't output scripts if the request is for a search page or 404.
		if ( is_search() || is_404() ) {
			return;
		}

		// Define array of scripts.
		$scripts = array();

		/**
		 * Define an array of scripts to output in the footer of the WordPress site.
		 *
		 * @since   2.1.4
		 *
		 * @param   array   $scripts    Scripts.
		 */
		$scripts = apply_filters( 'convertkit_output_scripts_footer', $scripts );

		// Bail if no scripts exist.
		if ( ! count( $scripts ) ) {
			return;
		}

		// Define array to store <script> outputs.
		$output_scripts = array();

		// Iterate through scripts, building the <script> tag for each.
		foreach ( $scripts as $script ) {
			/**
			 * Filter the form <script> key/value pairs immediately before the script is output.
			 *
			 * @since   2.4.5
			 *
			 * @param   array   $script     Form script key/value pairs to output as <script> tag.
			 */
			$script = apply_filters( 'convertkit_output_script_footer', $script );

			// Build output.
			$output = '<script';
			foreach ( $script as $attribute => $value ) {
				// If the value is true, just output the attribute.
				if ( $value === true ) {
					$output .= ' ' . esc_attr( $attribute );
					continue;
				}

				// Sanitize attribute and value.
				$attribute = esc_attr( $attribute );
				$value     = ( $attribute === 'src' ? esc_url( $value ) : esc_attr( $value ) );

				// Output the attribute and value.
				$output .= ' ' . $attribute;

				// Output the value, if it's not a blank string.
				if ( strlen( $value ) > 0 ) {
					$output .= '="' . $value . '"';
				}
			}

			$output .= '></script>';

			// Add to array.
			$output_scripts[] = $output;
		}

		// Remove duplicate scripts.
		// This prevents the same non-inline form displaying twice. For example, if a modal form is specified both
		// in the Page's settings and the Form block, the user would see the same modal form displayed twice
		// because the script would be output twice.
		$output_scripts = array_unique( $output_scripts );

		// Output scripts.
		foreach ( $output_scripts as $output_script ) {
			echo $output_script . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

	}

}
