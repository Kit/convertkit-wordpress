<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

/**
 * Tests for the REST API Media Controller.
 *
 * @since   3.0.0
 */
class RestMediaControllerTest extends WPRestApiTestCase
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
	 * @since   3.0.0
	 */
	public function setUp(): void
	{
		parent::setUp();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.0.0
	 */
	public function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library is empty.
	 *
	 * @since   3.0.0
	 */
	public function testEmpty()
	{
		$response = $this->request();
        $this->assertPaginationStructureValid($response);
        $this->assertDataStructureEmpty($response);
	}

    /**
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library contains
     * attachments, but no images.
	 *
	 * @since   3.0.0
	 */
	public function testEmptyWhenNoImages()
	{
        $this->tester->factory()->attachment->create_upload_object( 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf' );
		$response = $this->request();
        $this->assertPaginationStructureValid($response);
        $this->assertDataStructureEmpty($response);
	}

    /**
     * Test that calling `wp-json/kit/v4/media` works when the Media Library is populated with images.
     * 
     * @since   3.0.0
     */
    public function testSearch()
    {
        $this->populateMediaLibrary();
		$response = $this->request([
            'per_page' => 1,
        ]);
        $this->assertPaginationStructureValid(
            response: $response,
            perPage: 1
        );
        $this->assertDataStructureValid($response, 1);
    }

    /**
     * Populates the Media Library with test images and documents.
     * 
     * @since   3.0.0
     */
    private function populateMediaLibrary()
    {
        $this->tester->factory()->attachment->create_upload_object( 'https://placehold.co/600x400.jpg' );
        $this->tester->factory()->attachment->create_upload_object( 'https://placehold.co/600x400.png' );
        $this->tester->factory()->attachment->create_upload_object( 'https://placehold.co/600x400.gif' );
        $this->tester->factory()->attachment->create_upload_object( 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf' );
    }

    /**
     * Assert that the pagination structure is valid.
     *
     * @since   3.0.0
     *
     * @param array $pagination The pagination to assert.
     */
    private function assertPaginationStructureValid($response, $hasPreviousPage = false, $hasNextPage = true, $startCursor = '1', $endCursor = '2', $perPage = 24)
    {
        $this->assertArrayHasKey('pagination', $response);
        $pagination = $response['pagination'];

        // Assert expected array keys exist.
        $this->assertArrayHasKey('has_previous_page', $pagination);
        $this->assertArrayHasKey('has_next_page', $pagination);
        $this->assertArrayHasKey('start_cursor', $pagination);
        $this->assertArrayHasKey('end_cursor', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);

        // Assert expected values exist.
        $this->assertEquals($hasPreviousPage, $pagination['has_previous_page']);
        $this->assertEquals($hasNextPage, $pagination['has_next_page']);
        $this->assertEquals($startCursor, $pagination['start_cursor']);
        $this->assertEquals($endCursor, $pagination['end_cursor']);
        $this->assertEquals($perPage, $pagination['per_page']);
    }

    /**
     * Assert that the data structure is empty.
     *
     * @since   3.0.0
     *
     * @param array $response The response to assert.
     */
    private function assertDataStructureEmpty($response)
    {
        $this->assertArrayHasKey('data', $response);
        $this->assertEmpty($response['data']);
    }

    /**
     * Assert that the image structure within the `data key is valid.
     *
     * @since   3.0.0
     *
     * @param array $response The response to assert.
     */
    private function assertDataStructureValid($response, $expectedItems = 1)
    {
        $this->assertArrayHasKey('data', $response);
        $this->assertCount($expectedItems, $response['data']);

        // Fetch first image from the data array.
        $image = $response['data'][0];

        // Assert image data is as expected.
        $this->assertArrayHasKey('id', $image);
        $this->assertArrayHasKey('type', $image);
        $this->assertArrayHasKey('url', $image);
        $this->assertArrayHasKey('thumbnail_url', $image);
        $this->assertArrayHasKey('alt', $image);
        $this->assertArrayHasKey('caption', $image);
        $this->assertArrayHasKey('title', $image);
        $this->assertArrayHasKey('attribution', $image);
        $this->assertArrayHasKey('label', $image['attribution']);
        $this->assertArrayHasKey('href', $image['attribution']); 
    }

    /**
     * Make a request to the Media endpoint.
     *
     * @since   3.0.0
     *
     * @param array $params The parameters to send with the request.
     * @param string $method The HTTP method to use for the request.
     * @return array The response body.
     */
    private function request($params = [], $method = 'POST', $expectedStatus = 200)
    {
        // Setup request.
		$request = new \WP_REST_Request( $method, '/kit/v4/media' );
        foreach( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        // Send request.
		$response = rest_get_server()->dispatch( $request );

        // Assert that the response code is correct.
        $this->assertSame( $expectedStatus, $response->get_status() );

        // Return the response data.
		return $response->get_data();
    }
}
