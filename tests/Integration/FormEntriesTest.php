<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Form_Entries functions.
 *
 * @since   3.0.0
 */
class FormEntriesTest extends WPTestCase
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

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Initialize the class we want to test.
		$this->entries = new \ConvertKit_Form_Entries();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->entries);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.0.0
	 */
	public function tearDown(): void
	{
		// Destroy the class we tested.
		unset($this->entries);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

    public function testAddEntry()
    {
        $data = [
            'post_id' => 1,
            'email' => 'test@example.com',
            'first_name' => 'Test',
        ];
        $this->entries->add( $data );
        $this->seeInDatabase( $this->entries->table, $data );
    }

}
