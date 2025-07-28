<?php
/**
 * Outputs confirmation that the form submitted and the visitor
 * subscribed.
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

echo esc_html( $atts['subscribed_message'] );

// Don't wrap the form in a div if it's being rendered in the block editor,
// as the block editor will add its own wrapper.
if ( ! $this->is_block_editor_request() ) {
	?>
	</div>
	<?php
}
?>
