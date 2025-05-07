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
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	protected $namespace = 'kit';

	/**
	 * Version of the REST API.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	private $version = 'v4';

	/**
	 * Base for the REST API.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	private $base = 'media';

	/**
	 * Constructor.
	 *
	 * @since   3.0.0
	 */
	public function __construct() {

		// Get post meta fields class, as WP_REST_Attachments_Controller requires it.
		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );

		// Register REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

	}

	/**
	 * Register REST API routes for the Media Library.
	 *
	 * @since   3.0.0
	 */
	public function register_routes() {

		// Register a route at /wp-json/kit/v4/media.
		register_rest_route(
			$this->namespace . '/' . $this->version,
			$this->base,
			array(
				// The Kit Media Source Plugin will use POST requests to fetch images.
				// This Plugin supports GET so it is in line with WordPress' media endpoint.
				'methods'  => array( 'GET', 'POST' ),
				'callback' => array( $this, 'get_images' ),
				'permission_callback' => '__return_true',
			)
		);

	}

	/**
	 * Get Media Library items.
	 *
	 * @since   3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return WP_REST_Response The JSON response.
	 */
	public function get_images( WP_REST_Request $request ) {

		// The incoming request frm the Kit Media Source Plugin will use
		// a settings parameter with `query`, `label`, and `sort` keys.
		// We need to convert these to WP_Query compatible parameters.
		$params = $this->get_params( $request );

		// Set the query parameters.
		$request->set_query_params( $params );

		// Call WP_REST_Attachments_Controller get_items() method for the given REST API request.
		$response = $this->get_items( $request );

		// Restructure the image data from WP_REST_Attachments_Controller::get_items()
		// to match the structure required for the Kit Media Source Plugin.
		$data = $this->parse_image_data( $response->get_data() );

		// Return the JSON response in the structure required by the Kit Media Source Plugin.
		return new WP_REST_Response(
			array(
				// For debugging only.
				'debug'    => array(
					'request_params'       => $request->get_params(),
					'request_query_params' => $request->get_query_params(),
					'response_headers'     => $response->get_headers(),
				),
				'pagination' => array(
					'has_previous_page' => $this->has_previous_page( $request, $response ),
					'has_next_page'     => $this->has_next_page( $request, $response ),
					'start_cursor'      => $this->previous_page_id( $request, $response ),
					'end_cursor'        => $this->next_page_id( $request, $response ),
					'per_page'          => 100,
				),
				'data'       => $data,
			)
		);

	}

	private function has_previous_page( WP_REST_Request $request, WP_REST_Response $response ) {

		$headers = $response->get_headers();

		if ( ! array_key_exists( 'X-WP-Total', $headers ) ) {
			return false;
		}

		return $headers['X-WP-Total'] > 100;

	}

	private function has_next_page( WP_REST_Request $request ) {

	}
	
	

	/**
	 * Returns the settings[query] parameter from the REST API request.
	 *
	 * @since   3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return string|false The settings[query] parameter, or false if it doesn't exist.
	 */
	private function get_params( WP_REST_Request $request ) {

		// The incoming request frm the Kit Media Source Plugin will use
		// a settings parameter with `query`, `label`, and `sort` keys.
		// We need to convert these to WP_Query compatible parameters.
		$params = array(
			'per_page' => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 100,
			's'        => $this->get_search_parameter( $request ),
			'orderby'  => $this->get_orderby_parameter( $request ),
			'order'    => $this->get_order_parameter( $request ),
		);

		// Remove any parameters that are false, as they weren't included in the request.
		foreach ( $params as $key => $value ) {
			if ( $value === false ) {
				unset( $params[ $key ] );
			}
		}

		return $params;

	}

	/**
	 * Returns the settings[query] parameter from the REST API request.
	 *
	 * @since   3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return string|false The settings[query] parameter, or false if it doesn't exist.
	 */
	private function get_search_parameter( WP_REST_Request $request ) {

		$settings = $request->get_param( 'settings' );

		if ( ! is_array( $settings ) ) {
			return false;
		}

		if ( ! array_key_exists( 'query', $settings ) ) {
			return false;
		}

		if ( is_null( $settings['query'] ) ) {
			return false;
		}

		return $settings['query'];

	}

	/**
	 * Returns the settings[sort] parameter's order by value from the REST API request.
	 *
	 * @since   3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return string|false The settings[query] parameter, or false if it doesn't exist.
	 */
	private function get_orderby_parameter( WP_REST_Request $request ) {

		$result = $this->get_orderby_and_order_parameters( $request );

		if ( ! $result ) {
			return false;
		}

		return $result['orderby'];

	}

	/**
	 * Returns the settings[sort] parameter's order value from the REST API request.
	 *
	 * @since   3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return string|false The settings[query] parameter, or false if it doesn't exist.
	 */
	private function get_order_parameter( WP_REST_Request $request ) {

		$result = $this->get_orderby_and_order_parameters( $request );

		if ( ! $result ) {
			return false;
		}

		return $result['order'];

	}

	/**
	 * Returns the settings[sort] parameter's order by and order values from the REST API request.
	 *
	 * @since   3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return array|false The order by and order values, or false if it doesn't exist.
	 */
	private function get_orderby_and_order_parameters( WP_REST_Request $request ) {

		$settings = $request->get_param( 'settings' );

		if ( ! is_array( $settings ) ) {
			return false;
		}

		if ( ! array_key_exists( 'sort', $settings ) ) {
			return false;
		}

		if ( is_null( $settings['sort'] ) ) {
			return false;
		}

		list( $orderby, $order ) = explode( '_', $settings['sort'] );

		return array(
			'orderby' => $orderby,
			'order'   => $order,
		);

	}

	/**
	 * Restructures the image data from WP_REST_Attachments_Controller::get_items()
	 * to match the structure required for the Kit Media Source Plugin.
	 *
	 * @see https://developers.kit.com/v4#media-source-plugin-configuration-request-url
	 *
	 * @since   3.0.0
	 *
	 * @param array $images The images to parse.
	 */
	private function parse_image_data( $images ) {

		$data = array();

		foreach ( $images as $image ) {
			$data[] = array(
				'id'            => $image['id'],
				'type'          => $image['media_type'],
				'url'           => $image['source_url'],

				// Use the large size as the thumbnail, so it's a sufficient resolution for the Kit Media Source Plugin.
				// Sometimes an image might be so small that it does not have a 'large' size; in this case, use the source URL.
				'thumbnail_url' => isset( $image['media_details']['sizes']['large']['source_url'] ) ? $image['media_details']['sizes']['large']['source_url'] : $image['source_url'],
				'alt'           => $image['alt_text'],
				'caption'       => $image['caption']['rendered'],
				'title'         => ( ! empty( $image['title']['rendered'] ) ? $image['title']['rendered'] : $image['media_details']['sizes']['full']['file'] ),
				'attribution'   => array(
					'label' => $image['title']['rendered'],
					'href'  => $image['source_url'],
				),
			);
		}

		return $data;

	}

}

// Initialize the class and its REST API routes.
new ConvertKit_REST_Media_Controller();
