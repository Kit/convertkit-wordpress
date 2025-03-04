<?php
/**
 * Tests for the ConvertKit_Output_Restrict_Content::ip_in_range() function.
 *
 * @since   2.4.2
 */
class RestrictContentIPInRangeTest extends \Codeception\TestCase\WPTestCase
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
	 * @since   2.4.2
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Initialize the class we want to test.
		$this->resource = new ConvertKit_Output_Restrict_Content();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(WP_Error::class, $this->resource);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   2.4.2
	 */
	public function tearDown(): void
	{
		// Destroy the class we tested.
		unset($this->resource);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that IP addresses 34.100.182.96 through .111 (i.e. in the CIDR range /28)
	 * are returned as true by the ip_in_range() function.
	 *
	 * @since   2.4.2
	 */
	public function testIPAddressInRange()
	{
		for ($i = 96; $i <= 111; $i++) {
			$this->assertTrue($this->resource->ip_in_range('34.100.182.' . $i, '34.100.182.96/28'));
		}
	}

	/**
	 * Test that IP address 34.100.182.112 in the range 34.100.182.96/28
	 * are returned as false by the ip_in_range() function.
	 *
	 * @since   2.4.2
	 */
	public function testIPAddressOutsideRange()
	{
		$this->assertFalse($this->resource->ip_in_range('34.100.182.112', '34.100.182.96/28'));
	}

	/**
	 * Test that invalid IP addresses are returned as false by the ip_in_range() function.
	 *
	 * @since   2.4.2
	 */
	public function testInvalidIPAddresses()
	{
		$this->assertFalse($this->resource->ip_in_range('0.0.0.0', '34.100.182.96/28'));
		$this->assertFalse($this->resource->ip_in_range('999.999.999.999', '34.100.182.96/28'));
		$this->assertFalse($this->resource->ip_in_range('not-an-ip-address', '34.100.182.96/28'));
	}

	/**
	 * Test that invalid ranges return false by the ip_in_range() function.
	 *
	 * @since   2.4.2
	 */
	public function testInvalidRanges()
	{
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '34.100.182.96'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '34.100.182.96/999'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '34.100.182.96/not-a-range'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '0.0.0.0'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', 'not-an-ip-address'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '999.999.999.999/999'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', '999.999.999.999/not-a-range'));
		$this->assertFalse($this->resource->ip_in_range('34.100.182.96', 'not-an-ip-address/not-a-range'));
	}
}
