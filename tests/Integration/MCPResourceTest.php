<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP Form Resources
 *
 * @since   3.4.0
 */
class MCPResourceFormsTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Performs actions before each test.
	 *
	 * @since   1.9.7.4
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   1.9.6.9
	 */
	public function tearDown(): void
	{
		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

    /**
	 * Test that the ability returns a 401 when the user is not authorized.
	 *
	 * @since   3.4.0
	 */
	public function testWhenUnauthorized()
	{
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/blocks' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 401, $response->get_status() );
	}

    public function testWhenNoResourcesExist()
    {

    }

    public function testWhenResourcesExist()
    {

    }
}
