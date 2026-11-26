<?php
/**
 * Elementor Widget: ConvertKit Broadcasts.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers the ConvertKit Broadcasts Block as an Elementor Widget.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Elementor_Widget_Broadcasts extends ConvertKit_Elementor_Widget {

	/**
	 * The module's slug. Must be different from the block's name i.e. cannot be convertkit-broadcasts.
	 *
	 * @since   1.9.7.8
	 *
	 * @var     string
	 */
	public $slug = 'convertkit-elementor-broadcasts';

	/**
	 * Returns the widget's color controls.
	 *
	 * @since   3.5.4
	 *
	 * @return  array
	 */
	protected function get_color_controls() {

		return array(
			'link_color'       => array(
				'label'     => __( 'Link color', 'convertkit' ),
				'selectors' => array(
					'{{WRAPPER}} .convertkit-broadcasts a' => 'color: {{VALUE}};',
				),
			),
			'background_color' => array(
				'label'     => __( 'Background color', 'convertkit' ),
				'selectors' => array(
					'{{WRAPPER}} .convertkit-broadcasts' => 'background-color: {{VALUE}};',
				),
			),
			'text_color'       => array(
				'label'     => __( 'Text color', 'convertkit' ),
				'selectors' => array(
					'{{WRAPPER}} .convertkit-broadcasts' => 'color: {{VALUE}};',
				),
			),
		);

	}

}
