<?php
/**
 * Outputs the restricted content product code field
 * for the subscriber to enter their code if
 * they've already subscribed to the ConvertKit Product,
 * displayed within a modal.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<h4><?php echo esc_html( WP_ConvertKit()->get_class( 'output_restrict_content' )->restrict_content_settings->get_by_key( 'email_check_heading' ) ); ?></h4>
<p>
	<?php echo esc_html( WP_ConvertKit()->get_class( 'output_restrict_content' )->restrict_content_settings->get_by_key( 'email_check_text' ) ); ?>
</p>

<div class="convertkit-restrict-content-actions">
	<form id="convertkit-restrict-content-form" class="convertkit-restrict-content-form" action="<?php echo esc_attr( add_query_arg( array( 'convertkit_login' => 1 ), get_permalink( WP_ConvertKit()->get_class( 'output_restrict_content' )->post_id ) ) ); ?>" method="post">
		<div id="convertkit-subscriber-code-container" class="<?php echo sanitize_html_class( ( is_wp_error( WP_ConvertKit()->get_class( 'output_restrict_content' )->error ) ? 'convertkit-restrict-content-error' : '' ) ); ?>">
			<input type="text" name="subscriber_code" id="convertkit_subscriber_code" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="one-time-code" value="" required />
		</div>

		<?php
		require 'notices.php';
		?>

		<div>
			<input type="hidden" name="token" value="<?php echo esc_attr( WP_ConvertKit()->get_class( 'output_restrict_content' )->token ); ?>" />
			<input type="hidden" name="convertkit_post_id" value="<?php echo esc_attr( WP_ConvertKit()->get_class( 'output_restrict_content' )->post_id ); ?>" />
			<?php wp_nonce_field( 'convertkit_restrict_content_subscriber_code' ); ?>
		</div>
	</form>
</div>