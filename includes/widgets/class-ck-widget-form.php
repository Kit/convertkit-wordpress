<?php
/**
 * ConvertKit Form Widget class
 *
 * @package ConvertKit
 * @author ConvertKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers a ConvertKit Form Widget with WordPress' widgets functionality.
 *
 * Since 1.9.7.6, the ConvertKit Form Block can be used on WordPress 5.8+ sites
 * that make use of the block editor for Widgets at Apperance > Widgets, and therefore
 * on WordPress 5.8+, this widget will appear as a 'legacy' widget in WordPress.
 *
 * It's retained as not all users may be on WordPress 5.8+, and users may already
 * have this widget configured on their site.
 *
 * @author   ConvertKit
 * @version  1.4.3
 */
class CK_Widget_Form extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {

		parent::__construct(
			'convertkit_form',
			__( 'Kit Form (Legacy Widget)', 'convertkit' ),
			array(
				'classname'                   => 'convertkit widget_convertkit_form',
				'description'                 => __( 'Display a Kit form.', 'convertkit' ),
				'customize_selective_refresh' => true,
			)
		);

	}

	/**
	 * Outputs the settings update form.
	 *
	 * @since   1.4.3
	 *
	 * @param   array $instance   Current settings.
	 * @return  string              Default return is 'noform'
	 */
	public function form( $instance ) {

		$forms = new ConvertKit_Resource_Forms( 'output_form' );

		// Bail if no Forms exist.
		if ( ! $forms->exist() ) {
			?>
			<p>
				<?php esc_html_e( 'To display a Kit Form, at least one form must be defined in your Kit Account.', 'convertkit' ); ?>
			</p>
			<?php
		}

		// If the widget's settings are not defined, set them now to avoid undefined index errors.
		if ( ! array_key_exists( 'title', $instance ) ) {
			$instance['title'] = '';
		}
		if ( ! array_key_exists( 'form', $instance ) ) {
			$instance['form'] = '';
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'convertkit' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" value="<?php esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'form' ) ); ?>"><?php esc_html_e( 'Form', 'convertkit' ); ?></label>
			<?php
			echo $forms->get_select_field_all( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_attr( $this->get_field_name( 'form' ) ),
				esc_attr( $this->get_field_id( 'form' ) ),
				array(
					'widefat',
				),
				esc_attr( $instance['form'] )
			);
			?>
		</p>
		<?php

		return '';

	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget settings.
	 */
	public function widget( $args, $instance ) {

		// Bail if no form is defined.
		if ( ! isset( $instance['form'] ) ) {
			return;
		}

		// Get Form.
		$forms = new ConvertKit_Resource_Forms( 'output_form' );
		$form  = $forms->get_html( $instance['form'] );

		// Bail if the Form has an error.
		if ( is_wp_error( $form ) ) {
			return;
		}

		// Output Form.
		// $args already escaped as supplied by WordPress, so we don't need to escape them again.
		// $form could be a script or legacy form with varying HTML, so we don't want to escape it.
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $args['before_widget'];
		if ( $instance['title'] ) {
			echo $args['before_title'];
			echo esc_attr( $instance['title'] );
			echo $args['after_title'];
		}
		echo $form;
		echo $args['after_widget'];
		// phpcs:enable

	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * @see    WP_Widget->update
	 * @param  array $new_instance Updated widget settings.
	 * @param  array $old_instance Original widget settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return array(
			'title' => sanitize_text_field( $new_instance['title'] ),
			'form'  => sanitize_text_field( $new_instance['form'] ),
		);

	}

}
