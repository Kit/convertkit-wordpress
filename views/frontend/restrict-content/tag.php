<?php
/**
 * Outputs the restricted content tag message,
 * and a form for the subscriber to enter their
 * email address to subscribe to the tag, granting
 * them access.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<div id="convertkit-restrict-content">
	<div class="convertkit-restrict-content-actions">
		<?php
		require 'header.php';
		?>

		<form class="convertkit-restrict-content-form" action="<?php echo esc_attr( add_query_arg( array( 'convertkit_login' => 1 ), get_permalink( $post_id ) ) ); ?>#convertkit-restrict-content" method="post">
			<div id="convertkit-restrict-content-email-field" class="<?php echo sanitize_html_class( ( is_wp_error( $this->error ) ? 'convertkit-restrict-content-error' : '' ) ); ?>">
				<input type="email" name="convertkit_email" id="convertkit_email" value="" placeholder="<?php esc_attr_e( 'Email Address', 'convertkit' ); ?>" required />
				<?php
				// Output submit button, plus a Turnstile widget div if Cloudflare Turnstile
				// is the active spam protection provider. For reCAPTCHA v3 (invisible),
				// the challenge is attached to the button itself.
				$spam_provider = ConvertKit_Spam_Protection::get_active_provider();

				if ( $spam_provider instanceof ConvertKit_Cloudflare_Turnstile ) {
					?>
					<div class="cf-turnstile"
						data-sitekey="<?php echo esc_attr( $this->settings->turnstile_site_key() ); ?>"
						data-appearance="interaction-only"
						data-callback="convertKitTurnstileFormSubmit"></div>
					<input type="submit" class="wp-block-button__link wp-block-button__link" value="<?php echo esc_attr( $this->restrict_content_settings->get_by_key( 'subscribe_button_label' ) ); ?>" />
					<?php
				} elseif ( $spam_provider instanceof ConvertKit_Recaptcha ) {
					?>
					<input type="submit" class="wp-block-button__link wp-block-button__link g-recaptcha" value="<?php echo esc_attr( $this->restrict_content_settings->get_by_key( 'subscribe_button_label' ) ); ?>"
							data-sitekey="<?php echo esc_attr( $this->settings->recaptcha_site_key() ); ?>"
							data-callback="convertKitRecaptchaFormSubmit"
							data-action="convertkit_restrict_content_tag" />
					<?php
				} else {
					?>
					<input type="submit" class="wp-block-button__link wp-block-button__link" value="<?php echo esc_attr( $this->restrict_content_settings->get_by_key( 'subscribe_button_label' ) ); ?>" />
					<?php
				}
				?>
				<input type="hidden" name="convertkit_resource_type" value="<?php echo esc_attr( $this->resource_type ); ?>" />
				<input type="hidden" name="convertkit_resource_id" value="<?php echo esc_attr( $this->resource_id ); ?>" />
				<input type="hidden" name="convertkit_post_id" value="<?php echo esc_attr( $this->post_id ); ?>" />
				<?php wp_nonce_field( 'convertkit_restrict_content_login' ); ?>
			</div>
		</form>

		<?php
		// Output a login link or form if scripts are enabled. Login is always
		// required for tag-restricted content, matching form/product behaviour.
		if ( ! $this->settings->scripts_disabled() ) {
			require 'login.php';
		}

		// Output notices.
		require 'notices.php';
		?>
	</div>
</div>
