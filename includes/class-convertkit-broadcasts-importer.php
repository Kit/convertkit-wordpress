<?php
/**
 * ConvertKit Broadcasts Importer class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Class to publish WordPress Posts based on ConvertKit Broadcasts,
 * if the Broadcasts functionality is enabled in the Plugin's settings.
 *
 * @since   2.2.9
 */
class ConvertKit_Broadcasts_Importer {

	/**
	 * Holds the Broadcasts Settings class.
	 *
	 * @since   2.2.9
	 *
	 * @var     bool|ConvertKit_Settings_Broadcasts
	 */
	private $broadcasts_settings = false;

	/**
	 * Holds the Media Library class.
	 *
	 * @since   2.6.3
	 *
	 * @var     bool|ConvertKit_Media_Library
	 */
	private $media_library = false;

	/**
	 * Holds the Settings class.
	 *
	 * @since   2.6.4
	 *
	 * @var     bool|ConvertKit_Settings
	 */
	private $settings = false;

	/**
	 * Holds the Logging class.
	 *
	 * @since   2.6.4
	 *
	 * @var     bool|ConvertKit_Log
	 */
	private $log = false;

	/**
	 * Constructor. Registers actions and filters to output ConvertKit Forms and Landing Pages
	 * on the frontend web site.
	 *
	 * @since   2.2.9
	 */
	public function __construct() {

		// Initialize required classes.
		$this->broadcasts_settings = new ConvertKit_Settings_Broadcasts();
		$this->media_library       = new ConvertKit_Media_Library();
		$this->settings            = new ConvertKit_Settings();

		// Create WordPress Posts when the ConvertKit Posts Resource is refreshed.
		add_action( 'convertkit_resource_refreshed_posts', array( $this, 'refresh' ) );

	}

	/**
	 * When the list of broadcasts is refreshed by the resource class, iterate through
	 * each Broadcast, to check if a Post exists in WordPress for it.
	 *
	 * If not, creates the WordPress Post.
	 *
	 * @since   2.2.9
	 *
	 * @param   array $broadcasts     Broadcasts.
	 */
	public function refresh( $broadcasts ) {

		// Bail if Broadcasts to Posts are disabled.
		if ( ! $this->broadcasts_settings->enabled() ) {
			return;
		}

		// Bail if no Broadcasts exist.
		if ( ! count( $broadcasts ) ) {
			return;
		}

		foreach ( $broadcasts as $broadcast_id => $broadcast ) {
			// If a WordPress Post exists for this Broadcast ID, we previously imported it - skip it.
			if ( $this->broadcast_exists_as_post( $broadcast_id ) ) {
				$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . ' already exists as a WordPress Post. Skipping...' );
				continue;
			}

			// Skip if the published_at date is older than the 'Earliest Date' setting.
			if ( strtotime( $broadcast['published_at'] ) < strtotime( $this->broadcasts_settings->published_at_min_date() ) ) {
				$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . ' published_at date is before ' . $this->broadcasts_settings->published_at_min_date() . '. Skipping...' );
				continue;
			}

			// Import the broadcast.
			$this->import_broadcast(
				$broadcast_id,
				$this->broadcasts_settings->post_status(),
				$this->broadcasts_settings->author_id(),
				$this->broadcasts_settings->category_id(),
				$this->broadcasts_settings->import_thumbnail(),
				$this->broadcasts_settings->import_images(),
				$this->broadcasts_settings->no_styles()
			);
		}

	}



	/**
	 * Imports the given Kit Broadcast ID to a new WordPress Post.
	 *
	 * @since   2.6.4
	 *
	 * @param   int      $broadcast_id   Broadcast ID.
	 * @param   string   $post_status    WordPress Post Status to save Post as (publish,draft etc).
	 * @param   int      $author_id      WordPress User ID to assign as the author of the Post.
	 * @param   bool|int $category_id    WordPress Category to assign to the Post.
	 * @param   bool     $import_thumbnail   Store Broadcast's thumbnail as the WordPress Post's Featured Image.
	 * @param   bool     $import_images      Store Broadcast's inline images in the Media Library.
	 * @param   bool     $disable_styles   Remove CSS styles and layout elements from the Broadcast content.
	 *
	 * @return  WP_Error|int
	 */
	public function import_broadcast( $broadcast_id, $post_status = 'publish', $author_id = 1, $category_id = false, $import_thumbnail = false, $import_images = false, $disable_styles = false ) {

		// Bail if the Plugin Access Token has not been configured.
		if ( ! $this->settings->has_access_and_refresh_token() ) {
			return new WP_Error(
				'convertkit_broadcasts_importer_error',
				__( 'No Access Token specified in Plugin Settings', 'convertkit' )
			);
		}

		// Initialize the API.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$this->settings->get_access_token(),
			$this->settings->get_refresh_token(),
			$this->settings->debug_enabled(),
			'broadcasts_importer'
		);

		// Check that we're using the ConvertKit WordPress Libraries 1.3.8 or higher.
		// If another ConvertKit Plugin is active and out of date, its libraries might
		// be loaded that don't have this method.
		if ( ! method_exists( $api, 'get_post' ) ) { // @phpstan-ignore-line Older WordPress Libraries won't have this function.
			return new WP_Error(
				'convertkit_broadcasts_importer_error',
				__( 'Kit WordPress Libraries 1.3.7 or older detected, missing the `get_post` method.', 'convertkit' )
			);
		}

		// Fetch Broadcast's content.
		// We need to query wordpress/posts/{id} to fetch the full Broadcast information and content.
		$broadcast = $api->get_post( $broadcast_id );

		// Unset API class.
		unset( $api );

		// Bail if an error occured fetching the Broadcast.
		if ( is_wp_error( $broadcast ) ) {
			$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . '. Error fetching from API: ' . $broadcast->get_error_message() );
			return $broadcast;
		}

		// Create Post as a draft, without content or a Featured Image.
		// This gives us a Post ID we can then use if we need to import
		// the Featured Image and/or Broadcast images to the Media Library,
		// storing them against the Post ID just created.
		$post_id = wp_insert_post(
			$this->build_post_args(
				$broadcast,
				$author_id,
				$category_id
			),
			true
		);

		// Bail if an error occured.
		if ( is_wp_error( $post_id ) ) {
			$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . '. Error on wp_insert_post(): ' . $post_id->get_error_message() );
			return $post_id;
		}

		// Parse the Broadcast's content, storing it in the Post.
		$post_id = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $this->parse_broadcast_content(
					$post_id,
					$broadcast['content'],
					$broadcast['title'],
					$import_images,
					$disable_styles
				),
			),
			true
		);

		// Bail if an error occured updating the Post.
		if ( is_wp_error( $post_id ) ) {
			$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . '. Error on wp_update_post() when adding Broadcast content: ' . $post_id->get_error_message() );
			return $post_id;
		}

		// If a Product is specified, apply it as the Restrict Content setting.
		if ( $broadcast['is_paid'] && $broadcast['product_id'] ) {
			// Fetch Post's settings.
			$convertkit_post = new ConvertKit_Post( $post_id );
			$meta            = $convertkit_post->get();

			// Define Restrict Content setting.
			$meta['restrict_content'] = 'product_' . $broadcast['product_id'];

			// Save Post's settings.
			$convertkit_post->save( $meta );
			$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . '. Set Restrict Content = ' . $broadcast['product_id'] );
		}

		// If the Import Thumbnail setting is enabled, and the Broadcast has an image, save it to the Media Library and link it to the Post.
		if ( $import_thumbnail ) {
			$result = $this->add_broadcast_image_to_post( $broadcast, $post_id );

			if ( is_wp_error( $result ) ) {
				$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . '. Error on add_broadcast_image_to_post(): ' . $result->get_error_message() );
			}
		} else {
			$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . '. Skipping thumbnail.' );
		}

		// Transition the Post to the defined Post Status in the settings, now that the image has been added to it.
		$post_id = wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => $post_status,
			),
			true
		);

		// Maybe log if an error occured updating the Post to the publish status.
		if ( is_wp_error( $post_id ) ) {
			$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . '. Error on wp_update_post() when transitioning post status from draft to publish: ' . $post_id->get_error_message() );
			return $post_id;
		}

		// Import successful.
		$this->maybe_log( 'ConvertKit_Broadcasts_Importer::refresh(): Broadcast #' . $broadcast_id . '. Added as Post ID #' . $post_id );
		return $post_id;

	}

	/**
	 * Logs the given message if debug logging is enabled
	 * in the Plugin's settings.
	 *
	 * @since   2.6.4
	 *
	 * @param   string $message    Log message.
	 */
	private function maybe_log( $message ) {

		// Don't log if debugging is not enabled.
		if ( ! $this->settings->debug_enabled() ) {
			return;
		}

		// Initialize logging class, if not yet initialized.
		if ( ! $this->log ) {
			$this->log = new ConvertKit_Log( CONVERTKIT_PLUGIN_PATH );
		}

		$this->log->add( $message );

	}

	/**
	 * Helper method to determine if the given ConvertKit Broadcast already exists
	 * as a WordPress Post.
	 *
	 * @since   2.2.7
	 *
	 * @param   int $broadcast_id   ConvertKit Broadcast ID.
	 * @return  bool                    Broadcast exists as a WordPress Post
	 */
	private function broadcast_exists_as_post( $broadcast_id ) {

		$posts = new WP_Query(
			array(
				'post_type'         => 'post',
				'post_status'       => 'any',
				'meta_query'        => array(
					array(
						'key'   => '_convertkit_broadcast_id',
						'value' => $broadcast_id,
					),
				),
				'fields'            => 'ids',
				'update_post_cache' => false,
			)
		);

		if ( ! $posts->post_count ) {
			return false;
		}

		return true;

	}

	/**
	 * Defines the wp_insert_post() compatible arguments for importing the given ConvertKit
	 * Broadcast to a new WordPress Post.
	 *
	 * @since   2.2.9
	 *
	 * @param   array    $broadcast          Broadcast.
	 * @param   int      $author_id          WordPress User to assign as the author of the Post.
	 * @param   bool|int $category_id        Category ID.
	 * @return  array                           wp_insert_post() compatible arguments.
	 */
	private function build_post_args( $broadcast, $author_id, $category_id = false ) {

		// Define array for the wp_insert_post() compatible arguments.
		$post_args = array(
			'post_type'     => 'post',
			'post_title'    => $broadcast['title'],
			'post_excerpt'  => ( ! is_null( $broadcast['description'] ) ? $broadcast['description'] : '' ),
			'post_date_gmt' => gmdate( 'Y-m-d H:i:s', strtotime( $broadcast['published_at'] ) ),
			'post_author'   => $author_id,
			'post_name'     => $this->generate_permalink( $broadcast['title'] ),
		);

		// If a Category was supplied, assign the Post to the given Category ID when created.
		if ( $category_id ) {
			$post_args['post_category'] = array( $category_id );
		}

		/**
		 * Define the wp_insert_post() compatible arguments for importing a ConvertKit Broadcast
		 * to a new WordPress Post.
		 *
		 * @since   2.2.9
		 *
		 * @param   array   $post_args  Post arguments.
		 * @param   array   $broadcast  Broadcast.
		 */
		$post_args = apply_filters( 'convertkit_broadcasts_build_post_args', $post_args, $broadcast );

		// Deliberate: force the Post Status = draft. This will be changed to scheduled or publish after
		// any image is imported to the Post's Featured Image. So many plugins wrongly publish a Post
		// and then import the Featured Image, which breaks a lot of social media sharing Plugins.
		$post_args['post_status'] = 'draft';

		// Deliberate: ensure the Broadcast ID is always defined.
		if ( ! array_key_exists( 'meta_input', $post_args ) ) {
			$post_args['meta_input'] = array();
		}
		$post_args['meta_input']['_convertkit_broadcast_id'] = $broadcast['id'];

		return $post_args;

	}

	/**
	 * Removes emojis from the given string.
	 *
	 * @since   2.8.2
	 *
	 * @param   string $title Broadcast Title.
	 * @return  string
	 */
	public function generate_permalink( $title ) {

		// Remove emojis.
		$title = preg_replace( '/[^\p{L}\p{N}\p{P}\s]+/u', '', $title );

		// Return the Permalink.
		return sanitize_title( $title );

	}

	/**
	 * Parses the given Broadcast's content, removing unnecessary HTML tags and styles.
	 *
	 * If 'Import Images' is enabled in the Plugin settings, imports images to the
	 * Media Library, replacing the <img> `src` with the WordPress Media Library
	 * Image URL.
	 *
	 * @since   2.2.9
	 *
	 * @param   int    $post_id            WordPress Post ID.
	 * @param   string $broadcast_content  Broadcast Content.
	 * @param   string $broadcast_title    Broadcast Title.
	 * @param   bool   $import_images      Import images to Media Library.
	 * @param   bool   $disable_styles     Disable CSS styles in content.
	 * @return  string                     Parsed Content.
	 */
	private function parse_broadcast_content( $post_id, $broadcast_content, $broadcast_title = '', $import_images = false, $disable_styles = false ) {

		$content = $broadcast_content;

		// Load the content into the parser.
		$parser = new ConvertKit_HTML_Parser( $content );

		// Remove certain elements and their contents, as we never want these to be included in the WordPress Post.
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		// Remove open tracking.
		foreach ( $parser->xpath->query( '//img[@src="https://preview.convertkit-mail2.com/open"]' ) as $node ) {
			$node->parentNode->removeChild( $node );
		}

		// Remove blank contenteditable table cells.
		foreach ( $parser->xpath->query( '//td[@contenteditable="false"]' ) as $node ) {
			$node->parentNode->removeChild( $node );
		}

		// Remove <style> elements and their contents.
		foreach ( $parser->xpath->query( '//style' ) as $node ) {
			$node->parentNode->removeChild( $node );
		}

		// Remove ck-hide-in-public-posts and their contents.
		// This includes the unsubscribe section.
		foreach ( $parser->xpath->query( '//div[contains(@class, "ck-hide-in-public-posts")]' ) as $node ) {
			$node->parentNode->removeChild( $node );
		}

		// Remove ck-poll, as interacting with these results in an error.
		foreach ( $parser->xpath->query( '//table[contains(@class, "ck-poll")]' ) as $node ) {
			$node->parentNode->removeChild( $node );
		}

		// If a H1 through H6 heading matches the Broadcast's title, remove it from the content.
		// The Broadcast's title will always display as the WordPress Post title.
		for ( $i = 1; $i <= 6; $i++ ) {
			foreach ( $parser->xpath->query( '//h' . $i ) as $node ) {
				if ( $node->textContent === $broadcast_title ) {
					$node->parentNode->removeChild( $node );
				}
			}
		}
		// phpcs:enable

		// If the Import Images setting is enabled, iterate through all images within the Broadcast, importing them and changing their
		// URLs to the WordPress Media Library hosted versions.
		if ( $import_images ) {
			foreach ( $parser->xpath->query( '//img' ) as $node ) {
				$image = array(
					'src' => $node->getAttribute( 'src' ), // @phpstan-ignore-line
					'alt' => $node->getAttribute( 'alt' ), // @phpstan-ignore-line
				);

				// Skip if this image isn't served from https://embed.filekitcdn.com, as it isn't
				// a user uploaded image to the Broadcast.
				if ( strpos( $image['src'], 'https://embed.filekitcdn.com' ) === false ) {
					continue;
				}

				// Import Image into the Media Library.
				$image_id = $this->media_library->import_remote_image(
					$image['src'],
					$post_id,
					$image['alt']
				);

				// If the image could not be imported, serve the original CDN version.
				if ( is_wp_error( $image_id ) ) {
					continue;
				}

				// Get image URL from Media Library.
				$image_url = wp_get_attachment_image_src(
					$image_id,
					'full'
				);

				// Replace this image's `src` attribute with the Media Library Image URL.
				$node->setAttribute( 'src', $image_url[0] ); // @phpstan-ignore-line
			}
		}

		// Save HTML to a string.
		$content = $parser->get_body_html();

		// Return content with permitted HTML tags and inline styles included/excluded, depending on the setting.
		$content = $this->get_permitted_html( $content, $disable_styles );

		/**
		 * Parses the given Broadcast's content, removing unnecessary HTML tags and styles.
		 *
		 * @since   2.2.9
		 *
		 * @param   string $content            Parsed Content.
		 * @param   int    $post_id            WordPress Post ID.
		 * @param   string $broadcast_content  Broadcast Content.
		 * @param   string $broadcast_title    Broadcast Title.
		 * @param   bool   $import_images      Import images to Media Library.
		 * @param   bool   $disable_styles     Disable CSS styles in content.
		 */
		$content = apply_filters( 'convertkit_broadcasts_parse_broadcast_content', $content, $post_id, $broadcast_content, $broadcast_title, $import_images, $disable_styles );

		return $content;

	}

	/**
	 * Returns the given content containing only the permitted HTML tags,
	 * cleans up empty div elements and removes multiple newlines.
	 *
	 * Content contained within non-permitted HTML tags is returned, without
	 * the HTML tags wrapping the content.
	 *
	 * @since   2.4.0
	 *
	 * @param   string $content         HTML Content.
	 * @param   bool   $disable_styles  Disable styles.
	 * @return  string                  HTML Content
	 */
	public function get_permitted_html( $content, $disable_styles = false ) {

		// For PHP 7.4 and lower compatibility, convert permitted HTML tags array to a string
		// for use in strip_tags().
		$permitted_html_tags_string = '<' . implode( '><', $this->permitted_html_tags( $disable_styles ) ) . '>';

		// Remove other tags, retaining inner contents.
		// For HTML broadcasts, this will remove e.g. <html>, <head> and <body> tags.
		$content = strip_tags( $content, $permitted_html_tags_string );

		// If Disable Styles is enabled, remove inline styles and class attributes from remaining HTML elements.
		if ( $disable_styles ) {
			$content = preg_replace( '/(<[^>]+) style=".*?"/i', '$1', $content );
			$content = preg_replace( '/(<[^>]+) class=".*?"/i', '$1', $content );
		}

		// Remove empty div elements, which may remain because they contained HTML comments only, which were removed above.
		$content = str_replace( '<div></div>', '', $content );

		// Remove leading and trailing newlines and spaces, so WordPress doesn't convert these to blank paragraph tags.
		$content = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content );
		$content = trim( $content );

		return $content;

	}

	/**
	 * Returns an array of permitted HTML tags to retain in the imported Broadcast.
	 *
	 * @since   2.2.9
	 *
	 * @param   bool $disable_styles  Disable styles.
	 * @return  array
	 */
	private function permitted_html_tags( $disable_styles = false ) {

		// Define HTML tags to retain in the content.
		$permitted_html_tags = array(
			'h1',
			'h2',
			'h3',
			'p',
			'ul',
			'ol',
			'li',
			'blockquote',
			'strong',
			'em',
			'u',
			's',
			'a',
			'img',
			'figure',
			'figcaption',
			'br',
			'style', // Deliberate; we'll use DOMDocument to remove inline styles and their contents.
		);

		// If Disable Styles is false, include layout tags.
		if ( ! $disable_styles ) {
			$permitted_html_tags = array_merge(
				$permitted_html_tags,
				array(
					'span',
					'div',
					'table',
					'tbody',
					'tr',
					'td',
				)
			);
		}

		/**
		 * Define the HTML tags to retain in the Broadcast Content.
		 *
		 * @since   2.2.9
		 *
		 * @param   array  $permitted_html_tags    Permitted HTML Tags.
		 */
		$permitted_html_tags = apply_filters( 'convertkit_broadcasts_parse_broadcast_content_permitted_html_tags', $permitted_html_tags );

		// Return.
		return $permitted_html_tags;

	}

	/**
	 * Imports the broadcast's thumbnail_url image to the WordPress Media Library,
	 * assigning it as the WordPress Post's featured image.
	 *
	 * @since   2.2.9
	 *
	 * @param   array $broadcast  ConvertKit Broadcast.
	 * @param   int   $post_id    Post ID.
	 * @return  WP_Error|bool|int
	 */
	private function add_broadcast_image_to_post( $broadcast, $post_id ) {

		// Bail if no image specified.
		if ( empty( $broadcast['thumbnail_url'] ) ) {
			return false;
		}

		// Import Image into the Media Library.
		$image_id = $this->media_library->import_remote_image(
			$broadcast['thumbnail_url'],
			$post_id,
			$broadcast['thumbnail_alt']
		);

		// Bail if an error occured.
		if ( is_wp_error( $image_id ) ) {
			return $image_id;
		}

		// Assign the imported Media Library image as the Post's Featured Image.
		update_post_meta( $post_id, '_thumbnail_id', $image_id );

		return $image_id;

	}

}
