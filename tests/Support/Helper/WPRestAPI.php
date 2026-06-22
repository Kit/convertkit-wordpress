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
