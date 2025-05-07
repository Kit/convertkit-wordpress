<?php
/**
 * Kit REST API class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Returns images from the Media Library in a JSON format compatible with the Kit App
 * Media Source Plugin.
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
				// The Kit App Media Source Plugin will use POST requests to fetch images.
				// This Plugin supports GET so it is in line with WordPress' media endpoint.
				'methods'  => array( 'GET', 'POST' ),
				'callback' => array( $this, 'get_images' ),
				'permission_callback' => '__return_true',
			)
		);

		// Register a route at /wp-json/kit/v4/media/dates to return the month and year options that
		// populate the dates filter in the Kit App Media Source Plugin.
		register_rest_route(
			$this->namespace . '/' . $this->version,
			$this->base . '/dates',
			array(
				'methods'  => array( 'GET', 'POST' ),
				'callback' => array( $this, 'get_dates' ),
				'permission_callback' => '__return_true',
			)
		);

		// Register a route at /wp-json/kit/v4/media/orderby to return the order by options that
		// populate the sort filter in the Kit App Media Source Plugin.
		register_rest_route(
			$this->namespace . '/' . $this->version,
			$this->base . '/orderby',
			array(
				'methods'  => array( 'GET', 'POST' ),
				'callback' => array( $this, 'get_orderby' ),
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

		// The incoming request frm the Kit App Media Source Plugin will use
		// a settings parameter with `query`, `label`, and `sort` keys.
		// We need to convert these to WP_Query compatible parameters.
		$params = $this->get_params( $request );

		// Set the query parameters.
		$request->set_query_params( $params );

		// Call WP_REST_Attachments_Controller get_items() method for the given REST API request.
		$response = $this->get_items( $request );

		// Restructure the image data from WP_REST_Attachments_Controller::get_items()
		// to match the structure required for the Kit App Media Source Plugin.
		$data = $this->parse_image_data( $response->get_data() );

		// Return the JSON response in the structure required by the Kit App Media Source Plugin.
		return new WP_REST_Response(
			array(
				'pagination' => array(
					'has_previous_page' => (bool) $this->has_previous_page( $request, $response ),
					'has_next_page'     => (bool) $this->has_next_page( $request, $response ),
					'start_cursor'      => (string) $this->previous_page( $request, $response ),
					'end_cursor'        => (string) $this->next_page( $request, $response ),
					'per_page'          => (int) $params['per_page'],
				),
				'data'       => $data,
			)
		);

	}

	/**
	 * Returns the years and months where attachments exist, in a format compatible with the Kit App Media Source Plugin.
	 * 
	 * @since 3.0.0
	 */
	public function get_dates( WP_REST_Request $request ) {

		global $wpdb;

		// WordPress doesn't have a native function to return the years and months where attachments exist,
		// so we use the same method as found in months_dropdown().
		// @TODO Filter by images only.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
				FROM $wpdb->posts
				WHERE post_type = %s
				AND post_status != 'auto-draft' AND post_status != 'trash'
				ORDER BY post_date DESC",
				$this->post_type
			)
		);

		// Convert the results to the format required by the Kit App Media Source Plugin.
		$data = array();
		foreach ( $results as $result ) {
			$data[] = array(
				'label' => date_i18n( 'F Y', strtotime( $result->year . '-' . $result->month . '-01' ) ),
				'value' => $result->year . '-' . sprintf( '%02d', $result->month ),
			);
		}

		return new WP_REST_Response( $data );

	}

	/**
	 * Returns the order by options for the Kit App Media Source Plugin.
	 * 
	 * @since 3.0.0
	 * 
	 * @param WP_REST_Request $request The REST API request.
	 * @return WP_REST_Response The JSON response.
	 */
	public function get_orderby( WP_REST_Request $request ) {

		return new WP_REST_Response( array(
			array(
				'label' => __( 'Date, Descending', 'convertkit' ),
				'value' => 'date_desc',
			),
			array(
				'label' => __( 'Date, Ascending', 'convertkit' ),
				'value' => 'date_asc',
			),
			array(
				'label' => __( 'Title, Descending', 'convertkit' ),
				'value' => 'title_desc',
			),
			array(
				'label' => __( 'Title, Ascending', 'convertkit' ),
				'value' => 'title_asc',
			),
		) );

	}

	private function has_previous_page( WP_REST_Request $request, WP_REST_Response $response ) {

		$current_page = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;
		return ( $current_page > 1 );

	}

	private function has_next_page( WP_REST_Request $request, WP_REST_Response $response ) {

		$total_pages = $response->get_headers()['X-WP-TotalPages'];
		$current_page = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;

		return ( $current_page < $total_pages );

	}

	private function previous_page( WP_REST_Request $request, WP_REST_Response $response ) {

		$current_page = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;

		if ( $current_page === 1 ) {
			return 1;
		}

		return ( $current_page - 1 );

	}

	private function next_page( WP_REST_Request $request, WP_REST_Response $response ) {

		$total_pages = $response->get_headers()['X-WP-TotalPages'];
		$current_page = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;

		if ( $current_page === $total_pages ) {
			return $total_pages;
		}

		return ( $current_page + 1 );

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

		// The incoming request frm the Kit App's Media Source Plugin will use
        // the following parameters, which we need to convert to WP_Query compatible parameters.
        // `per_page`: Number of images to return. Defaults to 24
        // `after`: The cursor to start the search from. The value will be the previous request's `pagination.end_cursor` value.
        // @TODO Finish describing the rest of the parameters.
		// These must be compatible with https://developer.wordpress.org/rest-api/reference/media/#arguments
		$params = array(
			'per_page' => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 24,
            'page'     => $request->get_param( 'after' ) ? $request->get_param( 'after' ) : 1,

			'search'   => $this->get_search_parameter( $request ),

			'order'    => $this->get_order_parameter( $request ),
			'orderby'  => $this->get_orderby_parameter( $request ),
			
			// Force images only.
			'media_type' => 'image',
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

		if ( ! array_key_exists( 'search', $settings ) ) {
			return false;
		}

		if ( is_null( $settings['search'] ) ) {
			return false;
		}

		return sanitize_text_field( $settings['search'] );

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

		if ( ! array_key_exists( 'orderby', $settings ) ) {
			return false;
		}

		if ( is_null( $settings['orderby'] ) ) {
			return false;
		}

		list( $orderby, $order ) = explode( '_', $settings['orderby'] );

		return array(
			'orderby' => $orderby,
			'order'   => $order,
		);

	}

	/**
	 * Restructures the image data from WP_REST_Attachments_Controller::get_items()
	 * to match the structure required for the Kit App Media Source Plugin..
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
				'id'            => (string) $image['id'], // Must be a string, otherwise the Kit App Media Source Plugin will return a 502 bad gateway.
				'type'          => $image['media_type'],
				'url'           => $image['source_url'],

				// Use the large size as the thumbnail, so it's a sufficient resolution for the Kit App Media Source Plugin.
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
