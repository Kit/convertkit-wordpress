<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

/**
 * Tests for the Kit MCP Server.
 *
 * @since   3.4.0
 */
class MCPTest extends WPRestApiTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.0
	 */
	public function setUp(): void
	{
		parent::setUp();
		activate_plugins('convertkit/wp-convertkit.php');
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		deactivate_plugins('convertkit/wp-convertkit.php');
		parent::tearDown();
	}

	/**
	 * Test that the /wp-json/kit-mcp/v1 REST API route returns a 401 when the user is not authorized.
	 *
	 * @since   3.4.0
	 */
	public function testWhenUnauthorized()
	{
		// Make request.
		$request  = new \WP_REST_Request( 'GET', '/kit-mcp/v1' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is unsuccessful.
		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test that the Kit MCP server is registered with the MCP Adapter and
	 * exposes its discovery endpoint at /wp-json/kit-mcp/v1.
	 *
	 * @since   3.4.0
	 */
	public function testKitMCPServerCreated()
	{
		// Create and become administrator.
		$this->actAsAdministrator();

		// Make request.
		$request = new \WP_REST_Request('POST', '/kit-mcp/v1');
		$request->set_header('Content-Type', 'application/json');
		$request->set_body(
			wp_json_encode(
				[
					'jsonrpc' => '2.0',
					'id'      => 1,
					'method'  => 'initialize',
					'params'  => [
						'protocolVersion' => '2024-11-05',
						'capabilities'    => new \stdClass(),
						'clientInfo'      => [
							'name'    => 'test',
							'version' => '1.0',
						],
					],
				]
			)
		);
		$response = rest_get_server()->dispatch($request);

		// Assert the discovery endpoint is registered and responds successfully.
		$this->assertSame(200, $response->get_status());

		// Assert the response identifies itself as the Kit MCP server.
		$data = $response->get_data();
		$this->assertSame('Kit MCP', $data['result']->serverInfo['name'] ?? null);
	}

	/**
	 * Act as an administrator user.
	 *
	 * @since   3.4.0
	 */
	private function actAsAdministrator()
	{
		$administrator_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $administrator_id );
	}
}
