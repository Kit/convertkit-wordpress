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
	 * The search key name sent by the Kit App Media Source Plugin
	 * within the settings parameter, when the user performs a search
	 * from the Kit App Media Source Plugin.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	private $search_parameter = 'search';

	/**
	 * The date key name sent by the Kit App Media Source Plugin
	 * within the settings parameter, when the user performs a search
	 * from the Kit App Media Source Plugin.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	private $date_parameter = 'month_year';

	/**
	 * The orderby key name sent by the Kit App Media Source Plugin
	 * within the settings parameter, when the user performs a search
	 * from the Kit App Media Source Plugin.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	private $sort_parameter = 'sort';

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
				'methods'             => array( 'GET', 'POST' ),
				'callback'            => array( $this, 'get_images' ),
				'permission_callback' => '__return_true',
			)
		);

		// Register a route at /wp-json/kit/v4/media/dates to return the month and year options that
		// populate the dates filter in the Kit App Media Source Plugin.
		register_rest_route(
			$this->namespace . '/' . $this->version,
			$this->base . '/date-options',
			array(
				'methods'             => array( 'GET', 'POST' ),
				'callback'            => array( $this, 'get_date_options' ),
				'permission_callback' => '__return_true',
			)
		);

		// Register a route at /wp-json/kit/v4/media/orderby to return the order by options that
		// populate the sort filter in the Kit App Media Source Plugin.
		register_rest_route(
			$this->namespace . '/' . $this->version,
			$this->base . '/sort-options',
			array(
				'methods'             => array( 'GET', 'POST' ),
				'callback'            => array( $this, 'get_sort_options' ),
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

		$params_before = $request->get_params();

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
				'debug'      => array(
					'request_params'             => $params_before,
					'rest_api_compatible_params' => $params,
				),
				'pagination' => array(
					'has_previous_page' => (bool) $this->has_previous_page( $request ),
					'has_next_page'     => (bool) $this->has_next_page( $request, $response ),
					'start_cursor'      => (string) $this->previous_page( $request ),
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
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return WP_REST_Response The JSON response.
	 */
	public function get_date_options( WP_REST_Request $request ) {

		global $wpdb;

		// WordPress doesn't have a native function to return the years and months where attachments exist,
		// so we use the same method as found in months_dropdown(), to build an array of years and months
		// where images exist in the Media Library.
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
				FROM $wpdb->posts
				WHERE post_type = %s
				AND post_status != 'auto-draft'
				AND post_status != 'trash'
				AND post_mime_type LIKE %s
				ORDER BY post_date DESC",
				$this->post_type,
				'image/%'
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
	public function get_sort_options( WP_REST_Request $request ) {

		return new WP_REST_Response(
			array(
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
			)
		);

	}

	/**
	 * Whether the given request has a previous page.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return bool
	 */
	private function has_previous_page( WP_REST_Request $request ) {

		$current_page = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;
		return ( $current_page > 1 );

	}

	/**
	 * Whether the given request and response has a next page.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request  $request The REST API request.
	 * @param WP_REST_Response $response The REST API response.
	 * @return bool
	 */
	private function has_next_page( WP_REST_Request $request, WP_REST_Response $response ) {

		$total_pages  = $response->get_headers()['X-WP-TotalPages'];
		$current_page = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;

		return ( $current_page < $total_pages );

	}

	/**
	 * Returns the previous page number for the given request.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return int
	 */
	private function previous_page( WP_REST_Request $request ) {

		$current_page = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;

		if ( $current_page === 1 ) {
			return 1;
		}

		return ( $current_page - 1 );

	}

	/**
	 * Returns the next page number for the given request and response.
	 *
	 * @since 3.0.0
	 *
	 * @param WP_REST_Request  $request The REST API request.
	 * @param WP_REST_Response $response The REST API response.
	 * @return int
	 */
	private function next_page( WP_REST_Request $request, WP_REST_Response $response ) {

		$total_pages  = $response->get_headers()['X-WP-TotalPages'];
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
		// the following parameters, which we need to convert to REST API compatible parameters:
		// https://developer.wordpress.org/rest-api/reference/media/#arguments
		// `per_page`: Number of images to return. Defaults to 24
		// `after`: The cursor to start the search from. The value will be the previous request's `pagination.end_cursor` value.
		$params = array(
			'per_page'   => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 24,
			'page'       => $request->get_param( 'after' ) ? $request->get_param( 'after' ) : 1,

			'search'     => $this->get_search_parameter( $request ),

			'order'      => $this->get_order_parameter( $request ),
			'orderby'    => $this->get_orderby_parameter( $request ),

			'before'     => $this->get_date_before_parameter( $request ),
			'after'      => $this->get_date_after_parameter( $request ),

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

		if ( ! array_key_exists( $this->search_parameter, $settings ) ) {
			return false;
		}

		if ( is_null( $settings[ $this->search_parameter ] ) ) {
			return false;
		}

		return sanitize_text_field( $settings[ $this->search_parameter ] );

	}

	/**
	 * Returns the settings[date] parameter from the REST API request, returning
	 * an ISO8601 compliant date for the `before` parameter.
	 *
	 * @since   3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return string|false The settings[date] parameter, or false if it doesn't exist.
	 */
	private function get_date_before_parameter( WP_REST_Request $request ) {

		$settings = $request->get_param( 'settings' );

		if ( ! is_array( $settings ) ) {
			return false;
		}

		if ( ! array_key_exists( $this->date_parameter, $settings ) ) {
			return false;
		}

		if ( is_null( $settings[ $this->date_parameter ] ) ) {
			return false;
		}

		return sanitize_text_field( $settings[ $this->date_parameter ] ) . '-31';

	}

	/**
	 * Returns the settings[date] parameter from the REST API request, returning
	 * an ISO8601 compliant date for the `after` parameter.
	 *
	 * @since   3.0.0
	 *
	 * @param WP_REST_Request $request The REST API request.
	 * @return string|false The settings[date] parameter, or false if it doesn't exist.
	 */
	private function get_date_after_parameter( WP_REST_Request $request ) {

		$settings = $request->get_param( 'settings' );

		if ( ! is_array( $settings ) ) {
			return false;
		}

		if ( ! array_key_exists( $this->date_parameter, $settings ) ) {
			return false;
		}

		if ( is_null( $settings[ $this->date_parameter ] ) ) {
			return false;
		}

		return sanitize_text_field( $settings[ $this->date_parameter ] ) . '-01';

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

		if ( ! array_key_exists( $this->sort_parameter, $settings ) ) {
			return false;
		}

		if ( is_null( $settings[ $this->sort_parameter ] ) ) {
			return false;
		}

		list( $orderby, $order ) = explode( '_', $settings[ $this->sort_parameter ] );

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
