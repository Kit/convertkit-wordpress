<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests the Form Builder block when multiple Form Builder blocks are displayed on the same page.
 *
 * @since   3.4.6
 */
class BlockFormBuilderMultipleFormsTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the Form Builder block's content, as output by the block editor.
	 *
	 * @since   3.4.6
	 *
	 * @var     string
	 */
	private $content = '<div class="wp-block-convertkit-form-builder"><input type="email" name="convertkit[email]" /></div>';

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.6
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
	 * @since   3.4.6
	 */
	public function tearDown(): void
	{
		unset($_REQUEST['_wpnonce'], $_REQUEST['convertkit']);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that each block includes its index as a hidden field.
	 *
	 * @since   3.4.6
	 */
	public function testBlockIndexHiddenField()
	{
		$block = new \ConvertKit_Block_Form_Builder();

		$this->assertStringContainsString('name="convertkit[block_index]" value="1"', $block->render([], $this->content));
		$this->assertStringContainsString('name="convertkit[block_index]" value="2"', $block->render([], $this->content));
	}

	/**
	 * Test that the error notice is only displayed on the submitted block, and that
	 * block's email field is focused.
	 *
	 * @since   3.4.6
	 */
	public function testErrorDisplayedOnSubmittedBlockOnly()
	{
		// Submit the second block with an invalid email address.
		$block = $this->submit(2);

		// Render both blocks.
		$first  = $block->render([], $this->content);
		$second = $block->render([], $this->content);

		// Confirm the first block doesn't display the error.
		$this->assertStringNotContainsString('convertkit-form-builder-notice-error', $first);
		$this->assertStringNotContainsString('autofocus', $first);
		$this->assertStringNotContainsString('aria-invalid', $first);

		// Confirm the second block displays the error, and its email field is focused.
		$this->assertStringContainsString('id="convertkit-form-builder-error-2"', $second);
		$this->assertStringContainsString('Please enter a valid email address.', $second);
		$this->assertStringContainsString('aria-invalid="true"', $second);
		$this->assertStringContainsString('aria-describedby="convertkit-form-builder-error-2"', $second);
		$this->assertStringContainsString('autofocus', $second);
	}

	/**
	 * Test that the error notice is displayed on all blocks if no block index was submitted,
	 * such as a cached page from an earlier version of the Plugin.
	 *
	 * @since   3.4.6
	 */
	public function testErrorDisplayedOnAllBlocksWhenNoBlockIndex()
	{
		// Submit with an invalid email address and no block index.
		$block = $this->submit(false);

		// Confirm both blocks display the error.
		$this->assertStringContainsString('id="convertkit-form-builder-error-1"', $block->render([], $this->content));
		$this->assertStringContainsString('id="convertkit-form-builder-error-2"', $block->render([], $this->content));
	}

	/**
	 * Submits the Form Builder block with an invalid email address, from the given block index.
	 *
	 * @since   3.4.6
	 *
	 * @param   bool|int $block_index    Block index (false to not include it).
	 * @return  \ConvertKit_Block_Form_Builder
	 */
	private function submit($block_index)
	{
		$_REQUEST['_wpnonce']   = wp_create_nonce('convertkit_block_form_builder');
		$_REQUEST['convertkit'] = [
			'email'   => 'not-an-email-address',
			'post_id' => '1',
		];
		if ( $block_index ) {
			$_REQUEST['convertkit']['block_index'] = (string) $block_index;
		}

		$block = new \ConvertKit_Block_Form_Builder();
		$block->maybe_subscribe();

		// Confirm the submission failed validation.
		$this->assertInstanceOf(\WP_Error::class, $block->error);

		return $block;
	}
}
