<?php
/**
 * Kit REST API class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Outputs Forms and Landing Pages on the frontend web site, based on
 * the Post and Plugin's configuration.
 *
 * @since   3.0.0
 */
class ConvertKit_REST_Media_Controller extends WP_REST_Attachments_Controller {

	/**
	 * Post type.
	 *
	 * @since 3.0.0
	 * 
	 * @var string
	 */
	protected $post_type = 'attachment';

	/**
	 * Namespace for the REST API.
	 * 
	 * @since 	3.0.0
	 * 
	 * @var 	string
	 */
	protected $namespace = 'convertkit';

	/**
	 * Version of the REST API.
	 * 
	 * @since 	3.0.0
	 * 
	 * @var 	string
	 */
	private $version = 'v4';

	/**
	 * Base for the REST API.
	 * 
	 * @since 	3.0.0
	 * 
	 * @var 	string
	 */
	private $base = 'media';

	/**
	 * Constructor.
	 * 
	 * @since 	3.0.0
	 */
	public function __construct() {

		// Get post meta fields class, as WP_REST_Attachments_Controller requires it.
		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );

		// Register REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// $args       = apply_filters( "rest_{$this->post_type}_query", $args, $request );

	}

	/**
	 * Register REST API routes for the Media Library.
	 * 
	 * @since 	3.0.0
	 */
	public function register_routes() {

		register_rest_route( $this->namespace . '/' . $this->version, $this->base, array(
            'methods' => 'GET', // @TODO Change to POST.
            'callback' => array( $this, 'get_images' ),
        ) );

	}

	/**
	 * Get Media Library items.
	 * 
	 * @since 	3.0.0
	 */
	public function get_images( WP_REST_Request $request ) {

		// The incoming request frm the Kit Media Source Plugin will use
		// a settings parameter with `query`, `label`, and `sort` keys.
		// We need to convert these to WP_Query compatible parameters.
		$request->set_query_params( array(
			'per_page' => 1,
		) );

		// Call WP_REST_Attachments_Controller get_items() method for the given REST API request.
		$results = $this->get_items( $request );

		// Restructure the image data from WP_REST_Attachments_Controller::get_items()
		// to match the structure required for the Kit Media Source Plugin.
		$data = $this->parse_image_data( $results->get_data() );

		// Return the JSON response.
		return new WP_REST_Response( array(
			// For debugging only.
			'request' => array(
				'params' => $request->get_params(),
				'query_params' => $request->get_query_params(),
				'headers' => $results->get_headers(),
			),

			// Required by Kit Media Source Plugin.
			'pagination' => array(
				'has_previous_page' => false,
				'has_next_page' => true,
				'start_cursor' => 'WzEzXQ==', // previous page id.
				'end_cursor' => 'WzE0XQ==', // next page id.
				'per_page' => 100,
			),
			'data' => $data,
		) );

    }

	/**
	 * Restructures the image data from WP_REST_Attachments_Controller::get_items()
	 * to match the structure required for the Kit Media Source Plugin.
	 * 
	 * @see https://developers.kit.com/v4#media-source-plugin-configuration-request-url
	 * 
	 * @since 	3.0.0
	 * 
	 * @param array $images The images to parse.
	 */
	public function parse_image_data( $images ) {

		$data = array();

		foreach ( $images as $image ) {
			$data[] = array(
				'id' => $image['id'],
				'type' => $image['media_type'],
				'url' => $image['source_url'],
				'thumbnail_url' => $image['media_details']['sizes']['thumbnail']['source_url'],
				'alt' => $image['alt_text'],
				'caption' => $image['caption']['rendered'],
				'title' => $image['title']['rendered'],
				//'notify_download_url' => $image['guid']['rendered'],
				//'hotlink' => true,
				'attribution' => array(
					'label' => $image['title']['rendered'],
					'href' => $image['source_url'],
				),
			);
		}

		return $data;

	}

}

new ConvertKit_REST_Media_Controller();