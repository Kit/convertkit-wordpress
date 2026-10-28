<?php

namespace Tests;

/**
 * Assertions for resource classes, that don't depend on which resources exist on the Kit account.
 *
 * @since   3.4.8
 */
trait ResourceAssertionsTrait
{
	/**
	 * Asserts that the given resources are sorted by the resource class' order_by and order properties.
	 *
	 * @since   3.4.8
	 *
	 * @param   array $resources  Resources.
	 */
	private function assertResourcesSorted($resources)
	{
		// Assert every resource has the order_by key.
		$values = array_column($resources, $this->resource->order_by);
		$this->assertCount(count($resources), $values);

		// Assert resources are in the expected order, using the same comparison as the resource class.
		$sorted = $values;
		usort($sorted, 'strcmp');
		if ($this->resource->order === 'desc') {
			$sorted = array_reverse($sorted);
		}
		$this->assertEquals($sorted, $values);
	}

	/**
	 * Asserts that the given resources are in the same order as the resource class' resources.
	 *
	 * @since   3.4.8
	 *
	 * @param   array $resources  Resources.
	 */
	private function assertResourcesInOriginalOrder($resources)
	{
		$this->assertSame(
			array_keys(array_intersect_key($this->resource->resources, $resources)),
			array_keys($resources)
		);
	}
}
