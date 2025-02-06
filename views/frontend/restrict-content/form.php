<?php
/**
 * Outputs a form for the subscriber to enter their
 * email address to subscribe to the form, granting
 * them access.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<div id="convertkit-restrict-content-no-styles">
	<?php
	echo $form;

	// If scripts are disabled in the Plugin's settings, output the email login form now.
	if ( $this->settings->scripts_disabled() ) {
		?>
		<p>
			<?php echo esc_html( $this->restrict_content_settings->get_by_key( 'email_text' ) ); ?>
		</p>
		<?php
		require 'login-email.php';
	} else {
		// Just output the paragraph with a link to login, which will trigger the modal to display.
		?>
		<p>
			<?php echo esc_html( $this->restrict_content_settings->get_by_key( 'email_text' ) ); ?>
			<a href="#" class="convertkit-restrict-content-modal-open"><?php echo esc_attr( $this->restrict_content_settings->get_by_key( 'email_button_label' ) ); ?></a>
		</p>
		<?php
	}
	?>
</div>
