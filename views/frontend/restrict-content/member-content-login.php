<?php
/**
 * Outputs the Member Content Login block's login form.
 *
 * The heading and text displayed above the login form on Member Content aren't
 * output here, as they refer to reading the Member Content that the subscriber
 * is logging in to view.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<div id="convertkit-restrict-content" class="convertkit-login <?php echo esc_attr( implode( ' ', map_deep( $css_classes, 'sanitize_html_class' ) ) ); ?>" style="<?php echo esc_attr( implode( ';', $css_styles ) ); ?>">
	<div class="convertkit-restrict-content-actions convertkit-restrict-content-content">
		<?php
		require 'login-email.php';
		?>
	</div>
</div>
