<?php
/**
 * Elementor Widget: ConvertKit Member Content Login.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers the ConvertKit Member Content Login Block as an Elementor Widget.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Elementor_Widget_Login extends ConvertKit_Elementor_Widget {

	/**
	 * The module's slug. Must be different from the block's name i.e. cannot be convertkit-login.
	 *
	 * @since   3.4.4
	 *
	 * @var     string
	 */
	public $slug = 'convertkit-elementor-login';

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
					'{{WRAPPER}} #convertkit-restrict-content.convertkit-login' => 'background-color: {{VALUE}};',
				),
			),
			'text_color'       => array(
				'label'     => __( 'Text color', 'convertkit' ),
				'selectors' => array(
					'{{WRAPPER}} #convertkit-restrict-content.convertkit-login' => 'color: {{VALUE}};',
				),
			),
		);

	}

}
