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
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library is populated with images
	 * and a matching search term is used.
	 *
	 * @since   3.0.0
	 */
	public function testSearch()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		// Search using a partial title match.
		$response = $this->request(
			params: [
				'settings' => [
					'search' => '600x400',
				],
			]
		);
		$this->assertPaginationStructureValid($response);
		$this->assertDataStructureValid($response, 1);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library is populated with images
	 * and a non-matching search term is used.
	 *
	 * @since   3.0.0
	 */
	public function testSearchNoResults()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		// Search.
		$response = $this->request(
			params: [
				'settings' => [
					'search' => 'not a match',
				],
			]
		);
		$this->assertPaginationStructureValid($response);
		$this->assertDataStructureEmpty($response);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library is populated with images
	 * and a date filter is used that has images for that month and year.
	 *
	 * @since   3.0.0
	 */
	public function testDateFilter()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		// Search by month and year.
		$response = $this->request(
			params: [
				'settings' => [
					'month_year' => date('Y-m'),
				],
			]
		);

		$this->assertPaginationStructureValid($response);
		$this->assertDataStructureValid($response, 3);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library is populated with images
	 * and a date filter is used that has no images for that month and year.
	 *
	 * @since   3.0.0
	 */
	public function testDateFilterNoResults()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		// Search using a partial title match.
		$response = $this->request(
			params: [
				'settings' => [
					'month_year' => '2020-01',
				],
			]
		);

		$this->assertPaginationStructureValid($response);
		$this->assertDataStructureEmpty($response);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library is populated with images
	 * and an invalid date filter is used.
	 *
	 * @since   3.0.0
	 */
	public function testInvalidDateFilter()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		// Search using a partial title match.
		$response = $this->request(
			params: [
				'settings' => [
					'month_year' => 'not-a-valid-date',
				],
			]
		);

		$this->assertPaginationStructureValid($response);
		$this->assertDataStructureEmpty($response);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library is populated with images
	 * and a sort filter is used, with the sorting honored.
	 *
	 * @since   3.0.0
	 */
	public function testSortFilter()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		// Search using a partial title match.
		$response = $this->request(
			params: [
				'settings' => [
					'sort' => 'date_asc',
				],
			]
		);

		$this->assertPaginationStructureValid($response);
		$this->assertDataStructureValid($response, 3);

		// Confirm sort order is correct (date, ascending - first upload will be the first result).
		$this->assertStringContainsString('600', $response['data'][0]['title']);
		$this->assertStringContainsString('800', $response['data'][1]['title']);
		$this->assertStringContainsString('1920', $response['data'][2]['title']);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media` works when the Media Library is populated with images
	 * and an invalid sort filter is used, with sorting using the default (date_desc).
	 *
	 * @since   3.0.0
	 */
	public function testInvalidSortFilter()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		// Search using a partial title match.
		$response = $this->request(
			params: [
				'settings' => [
					'sort' => 'not_a_valid_sort',
				],
			]
		);

		$this->assertPaginationStructureValid($response);
		$this->assertDataStructureValid($response, 3);

		// Confirm sort order is original (date, descending - last upload will be the first result).
		$this->assertStringContainsString('1920', $response['data'][0]['title']);
		$this->assertStringContainsString('800', $response['data'][1]['title']);
		$this->assertStringContainsString('600', $response['data'][2]['title']);
	}

	/**
	 * Test that the pagination works correctly.
	 *
	 * @since   3.0.0
	 */
	public function testPagination()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		// Request first page.
		$response = $this->request(
			params: [
				'per_page' => 1,
			]
		);
		$this->assertPaginationStructureValid(
			response: $response,
			hasPreviousPage: false,
			hasNextPage: true,
			startCursor: '1',
			endCursor: '2',
			perPage: 1
		);
		$this->assertDataStructureValid($response, 1);

		// Request second page.
		$response = $this->request(
			params: [
				'per_page' => 1,
				'after'    => $response['pagination']['end_cursor'],
			]
		);
		$this->assertPaginationStructureValid(
			response: $response,
			hasPreviousPage: true,
			hasNextPage: true,
			startCursor: '1',
			endCursor: '3',
			perPage: 1
		);
		$this->assertDataStructureValid($response, 1);

		// Request third (final) page.
		$response = $this->request(
			params: [
				'per_page' => 1,
				'after'    => $response['pagination']['end_cursor'],
			]
		);
		$this->assertPaginationStructureValid(
			response: $response,
			hasPreviousPage: true,
			hasNextPage: false,
			startCursor: '2',
			endCursor: '3',
			perPage: 1
		);
		$this->assertDataStructureValid($response, 1);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media/date-options` returns the 'All Dates' option
	 * when no Media exists in the Media Library.
	 *
	 * @since   3.0.0
	 */
	public function testGetDateOptionsWhenNoMediaExists()
	{
		$response = $this->request(
			endpoint: '/kit/v4/media/date-options'
		);

		$this->assertArrayHasKey('options', $response);
		$this->assertCount(1, $response['options']);
		$this->assertEquals('All Dates', $response['options'][0]['label']);
		$this->assertEquals('0', $response['options'][0]['value']);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media/date-options` returns the 'All Dates' option
	 * and the current month and year option when Media exists in the Media Library.
	 *
	 * @since   3.0.0
	 */
	public function testGetDateOptions()
	{
		// Populate Media Library for the test.
		$this->populateMediaLibrary();

		$response = $this->request(
			endpoint: '/kit/v4/media/date-options'
		);

		$this->assertArrayHasKey('options', $response);
		$this->assertCount(2, $response['options']);

		$this->assertEquals('All Dates', $response['options'][0]['label']);
		$this->assertEquals('0', $response['options'][0]['value']);

		$this->assertEquals(date('F Y'), $response['options'][1]['label']);
		$this->assertEquals(date('Y-m'), $response['options'][1]['value']);
	}

	/**
	 * Test that calling `wp-json/kit/v4/media/sort-options` returns the correct
	 * sort options.
	 *
	 * @since   3.0.0
	 */
	public function testGetSortOptions()
	{
		$response = $this->request(
			endpoint: '/kit/v4/media/sort-options'
		);

		$this->assertArrayHasKey('options', $response);
		$this->assertCount(4, $response['options']);

		$this->assertEquals('Date, Ascending', $response['options'][0]['label']);
		$this->assertEquals('date_asc', $response['options'][0]['value']);

		$this->assertEquals('Date, Descending', $response['options'][1]['label']);
		$this->assertEquals('date_desc', $response['options'][1]['value']);

		$this->assertEquals('Title, Ascending', $response['options'][2]['label']);
		$this->assertEquals('title_asc', $response['options'][2]['value']);

		$this->assertEquals('Title, Descending', $response['options'][3]['label']);
		$this->assertEquals('title_desc', $response['options'][3]['value']);
	}

	/**
	 * Populates the Media Library with test images and documents.
	 *
	 * @since   3.0.0
	 */
	private function populateMediaLibrary()
	{
		// `create_upload_object` will define attachment metadata.
		// Titles will automatically be set to the filename e.g. 600x400, 800x600 etc.
		$this->tester->factory()->attachment->create_upload_object( 'https://placehold.co/600x400.jpg' );
		$this->tester->factory()->attachment->create_upload_object( 'https://placehold.co/800x600.png' );
		$this->tester->factory()->attachment->create_upload_object( 'https://placehold.co/1920x1080.gif' );
		$this->tester->factory()->attachment->create_upload_object( 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf' );
	}

	/**
	 * Assert that the pagination structure is valid.
	 *
	 * @since   3.0.0
	 *
	 * @param array  $response        The response to assert.
	 * @param bool   $hasPreviousPage Whether the previous page exists.
	 * @param bool   $hasNextPage     Whether the next page exists.
	 * @param string $startCursor     The start cursor.
	 * @param string $endCursor       The end cursor.
	 * @param int    $perPage         The number of items per page.
	 */
	private function assertPaginationStructureValid($response, $hasPreviousPage = false, $hasNextPage = false, $startCursor = '1', $endCursor = '1', $perPage = 24)
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
	 * @param array $response         The response to assert.
	 * @param int   $expectedItems    The number of items to assert.
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
	 * @param array  $params         The parameters to send with the request.
	 * @param string $endpoint       The endpoint to use.
	 * @return array
	 */
	private function request($params = [], $endpoint = '/kit/v4/media')
	{
		// Setup request.
		$request = new \WP_REST_Request( 'POST', $endpoint );
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert that the response code is correct.
		$this->assertSame( 200, $response->get_status() );

		// Return the response data.
		return $response->get_data();
	}
}
