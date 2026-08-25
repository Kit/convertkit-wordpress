<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to the REST API,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   3.4.0
 */
class WPRestAPI extends \Codeception\Module
{
	/**
	 * Check that the given route is registered in the REST API.
	 *
	 * @since   3.4.0
	 *
	 * @param   EndToEndTester $I     EndToEndTester.
	 * @param   string         $route   Route.
	 */
	public function hasRoute($I, $route)
	{
		$I->assertTrue( in_array( $route, $this->getRoutes(), true ) );
	}

	/**
	 * Check that the given route is not registered in the REST API.
	 *
	 * @since   3.4.0
	 *
	 * @param   EndToEndTester $I     EndToEndTester.
	 * @param   string         $route   Route.
	 */
	public function doesNotHaveRoute($I, $route)
	{
		$I->assertFalse( in_array( $route, $this->getRoutes(), true ) );
	}

	/**
	 * Call a REST API endpoint.
	 *
	 * @since   3.4.0
	 *
	 * @param   string $endpoint            Endpoint.
	 * @param   string $authorizationHeader Authorization Header.
	 * @param   string $method              Method.
	 * @param   array  $body                Body.
	 * @return  array
	 */
	public function callRestEndpoint( $endpoint, $authorizationHeader, $method = 'GET', $body = null ) {
		$url = $_ENV['WORDPRESS_URL'] . '/wp-json' . $endpoint;

		$args = [
			'method'  => $method,
			'headers' => [
				'Authorization' => $authorizationHeader,
				'Content-Type'  => 'application/json',
			],
			'timeout' => 10,
		];

		// Only attach a body when there's something to send. WP's HTTP layer
		// calls http_build_query() on `body` when the method is GET, which
		// fails if we've already JSON-encoded the value to a string.
		if ( ! empty( $body ) ) {
			$args['body'] = wp_json_encode( $body );
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			return [
				'status' => 0,
				'body'   => $response->get_error_message(),
			];
		}

		return [
			'status' => wp_remote_retrieve_response_code( $response ),
			'body'   => json_decode( wp_remote_retrieve_body( $response ), true ),
		];
	}

	/**
	 * Get the routes registered in the REST API.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	private function getRoutes()
	{
		$response = wp_remote_get( rest_url() );
		$body     = json_decode( wp_remote_retrieve_body( $response ), true );
		return array_keys( $body['routes'] ?? [] );
	}
}
