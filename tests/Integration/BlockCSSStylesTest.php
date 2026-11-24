<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Block::get_css_styles() function.
 *
 * @since   3.5.4
 */
class BlockCSSStylesTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the Form Trigger block class.
	 *
	 * @since   3.5.4
	 *
	 * @var     \ConvertKit_Block_Form_Trigger
	 */
	private $block;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.5.4
	 */
	public function setUp(): void
	{
		parent::setUp();

		$this->block = new \ConvertKit_Block_Form_Trigger();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.5.4
	 */
	public function tearDown(): void
	{
		\WP_Block_Supports::$block_to_render = null;

		parent::tearDown();
	}

	/**
	 * Test that the text and background colors are returned when no block is being rendered,
	 * such as a shortcode or page builder widget.
	 *
	 * @since   3.5.4
	 */
	public function testColorsWhenNoBlockRendering()
	{
		\WP_Block_Supports::$block_to_render = null;

		$this->assertEquals(
			[
				'color:#000000',
				'background-color:#e0ffff',
			],
			$this->block->get_css_styles(
				[
					'text_color'       => '#000000',
					'background_color' => '#e0ffff',
				]
			)
		);
	}

	/**
	 * Test that the text and background colors are returned, and another block's styles are not,
	 * when output within another block, such as Post Content in a block theme.
	 *
	 * @since   3.5.4
	 */
	public function testColorsWhenOtherBlockRendering()
	{
		$this->setBlockToRender(
			'core/post-content',
			[
				'style' => [
					'spacing' => [
						'padding' => [
							'top' => '20px',
						],
					],
				],
			]
		);

		$this->assertEquals(
			[
				'color:#000000',
				'background-color:#e0ffff',
			],
			$this->block->get_css_styles(
				[
					'text_color'       => '#000000',
					'background_color' => '#e0ffff',
				]
			)
		);
	}

	/**
	 * Test that Elementor Global Color CSS variables are returned when output within another block.
	 *
	 * @since   3.5.4
	 */
	public function testCSSVariableColorsWhenOtherBlockRendering()
	{
		$this->setBlockToRender(
			'core/post-content',
			[
				'style' => [
					'spacing' => [
						'padding' => [
							'top' => '20px',
						],
					],
				],
			]
		);

		$this->assertEquals(
			[
				'color:var(--e-global-color-text)',
				'background-color:var(--e-global-color-primary)',
			],
			$this->block->get_css_styles(
				[
					'text_color'       => 'var(--e-global-color-text)',
					'background_color' => 'var(--e-global-color-primary)',
				]
			)
		);
	}

	/**
	 * Test that the block's own styles are returned when the block is being rendered.
	 *
	 * @since   3.5.4
	 */
	public function testBlockStylesWhenBlockRendering()
	{
		$this->setBlockToRender(
			'convertkit/formtrigger',
			[
				'style' => [
					'spacing' => [
						'padding' => [
							'top' => '11px',
						],
					],
				],
			]
		);

		$this->assertEquals(
			[
				'padding-top:11px',
			],
			$this->block->get_css_styles( [] )
		);
	}

	/**
	 * Sets the block WordPress is rendering.
	 *
	 * @since   3.5.4
	 *
	 * @param   string $block_name Block name.
	 * @param   array  $attrs      Block attributes.
	 */
	private function setBlockToRender($block_name, $attrs)
	{
		\WP_Block_Supports::$block_to_render = [
			'blockName'    => $block_name,
			'attrs'        => $attrs,
			'innerBlocks'  => [],
			'innerHTML'    => '',
			'innerContent' => [],
		];
	}
}
