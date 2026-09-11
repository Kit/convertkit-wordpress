<?php
/**
 * Outputs the Member Content Login block when the subscriber is logged in,
 * displaying a message and a button to log out.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<div id="convertkit-restrict-content" class="convertkit-login <?php echo esc_attr( implode( ' ', map_deep( $css_classes, 'sanitize_html_class' ) ) ); ?>" style="<?php echo esc_attr( implode( ';', $css_styles ) ); ?>">
	<div class="convertkit-restrict-content-actions">
		<p>
			<?php echo esc_html( $atts['logged_in_text'] ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( $logout_url ); ?>" class="convertkit-restrict-content-logout wp-block-button__link wp-element-button"><?php echo esc_html( $atts['logout_button_label'] ); ?></a>
		</p>
	</div>
</div>
