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
				// Output the submit button. If a spam protection provider is active
				// it renders the button plus any accompanying widget div; otherwise a
				// plain submit button is output.
				$spam          = new ConvertKit_Spam_Protection();
				$spam_provider = $spam->get_active_provider();

				if ( $spam_provider !== null ) {
					echo $spam_provider->get_submit_button_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$this->restrict_content_settings->get_by_key( 'subscribe_button_label' ),
						'convertkit_restrict_content_tag',
						array( 'wp-block-button__link', 'wp-block-button__link' )
					);
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
