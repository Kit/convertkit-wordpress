<?php
/**
 * Elementor Widget: Form Trigger Button.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers the ConvertKit Form Trigger Button Block as an Elementor Widget.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Elementor_Widget_FormTrigger extends ConvertKit_Elementor_Widget {

	/**
	 * The module's slug. Must be different from the block's name i.e. cannot be convertkit-product.
	 *
	 * @since   2.2.2
	 *
	 * @var     string
	 */
	public $slug = 'convertkit-elementor-formtrigger';

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
					'{{WRAPPER}} .convertkit-formtrigger' => 'background-color: {{VALUE}};',
				),
			),
			'text_color'       => array(
				'label'     => __( 'Text color', 'convertkit' ),
				'selectors' => array(
					'{{WRAPPER}} .convertkit-formtrigger' => 'color: {{VALUE}};',
				),
			),
		);

	}

}
