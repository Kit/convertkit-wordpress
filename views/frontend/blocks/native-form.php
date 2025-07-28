<?php
/**
 * Outputs a form for the subscriber to enter their
 * email address to subscribe.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

// Don't wrap the form in a div if it's being rendered in the block editor,
// as the block editor will add its own wrapper.
if ( ! $this->is_block_editor_request() ) {
	?>
	<div class="<?php echo implode( ' ', map_deep( $css_classes, 'sanitize_html_class' ) ); ?>" style="<?php echo implode( ';', map_deep( $css_styles, 'esc_attr' ) ); ?>">
	<?php
}
?>
	<form action="<?php echo esc_url( get_permalink( $post_id ) ); ?>" method="post">
		<?php
		if ( $atts['display_name_field'] ) {
			?>
			<div>
				<?php
				if ( $atts['display_labels'] ) {
					?>
					<label for="convertkit_name"><?php esc_attr_e( 'Name', 'convertkit' ); ?></label>
					<?php
				}
				?>
				<input type="text" name="convertkit_name" id="convertkit_name" value="" placeholder="<?php echo $atts['display_labels'] ? '' : esc_attr__( 'Name', 'convertkit' ); ?>" required />
			</div>
			<?php
		}
		?>
		
		<div>
			<?php
			if ( $atts['display_labels'] ) {
				?>
				<label for="convertkit_email"><?php esc_attr_e( 'Email Address', 'convertkit' ); ?></label>
				<?php
			}
			?>
			<input type="email" name="convertkit_email" id="convertkit_email" value="" placeholder="<?php echo $atts['display_labels'] ? '' : esc_attr__( 'Email Address', 'convertkit' ); ?>" required />
		</div>
		<div>
			<input type="submit" class="wp-block-button__link wp-block-button__link" value="<?php echo esc_attr( $atts['text'] ); ?>" />
			<input type="hidden" name="convertkit_post_id" value="<?php echo esc_attr( $post_id ); ?>" />
			<?php wp_nonce_field( 'convertkit_native_form' ); ?>
		</div>
	</form>

<?php
// Don't wrap the form in a div if it's being rendered in the block editor,
// as the block editor will add its own wrapper.
if ( ! $this->is_block_editor_request() ) {
	?>
	</div>
	<?php
}
?>
