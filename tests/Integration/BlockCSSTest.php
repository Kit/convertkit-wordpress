<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Block::get_css_classes() and ConvertKit_Block::get_css_styles() functions.
 *
 * @since   3.5.4
 */
class BlockCSSTest extends WPTestCase
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
	 * Test that another block's classes, such as preset color classes, are not returned when output
	 * within another block, and that the block being rendered is restored.
	 *
	 * @since   3.5.4
	 */
	public function testClassesWhenOtherBlockRendering()
	{
		$this->setBlockToRender(
			'core/post-content',
			[
				'backgroundColor' => 'accent-5',
				'textColor'       => 'contrast',
			]
		);

		$this->assertEquals(
			[
				'convertkit-formtrigger',
				'wp-block-button__link',
				'wp-block-convertkit-formtrigger',
			],
			array_values( $this->block->get_css_classes( [ 'wp-block-button__link' ] ) )
		);

		// Confirm the block being rendered was restored.
		$this->assertEquals( 'core/post-content', \WP_Block_Supports::$block_to_render['blockName'] );
	}

	/**
	 * Test that the block's own classes, such as preset color classes, are returned when the block is being rendered.
	 *
	 * @since   3.5.4
	 */
	public function testBlockClassesWhenBlockRendering()
	{
		$this->setBlockToRender(
			'convertkit/formtrigger',
			[
				'backgroundColor' => 'accent-5',
			]
		);

		$classes = $this->block->get_css_classes();
		$this->assertContains( 'convertkit-formtrigger', $classes );
		$this->assertContains( 'has-background', $classes );
		$this->assertContains( 'has-accent-5-background-color', $classes );
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
