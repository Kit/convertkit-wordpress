<?php
/**
 * Elementor Widget: ConvertKit Product.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers the ConvertKit Product Block as an Elementor Widget.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Elementor_Widget_Product extends ConvertKit_Elementor_Widget {

	/**
	 * The module's slug. Must be different from the block's name i.e. cannot be convertkit-product.
	 *
	 * @since   2.0.0
	 *
	 * @var     string
	 */
	public $slug = 'convertkit-elementor-product';

	/**
	 * Returns the widget's color controls.
	 *
	 * @since   3.5.4
	 *
	 * @return  array
	 */
	protected function get_color_controls() {

		return array(
			'background_color' => array(
				'label'     => __( 'Background color', 'convertkit' ),
				'selectors' => array(
					'{{WRAPPER}} .convertkit-product' => 'background-color: {{VALUE}};',
				),
			),
			'text_color'       => array(
				'label'     => __( 'Text color', 'convertkit' ),
				'selectors' => array(
					'{{WRAPPER}} .convertkit-product' => 'color: {{VALUE}};',
				),
			),
		);

	}

}
