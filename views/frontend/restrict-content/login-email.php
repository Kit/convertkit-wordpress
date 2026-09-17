<?php
/**
 * Outputs the restricted content product email field
 * for the subscriber to enter their email address if
 * they've already subscribed to the ConvertKit Product.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

// Resource type and ID aren't defined when the Member Content Login block is used.
$resource_type = ( isset( $resource_type ) ? $resource_type : '' );
$resource_id   = ( isset( $resource_id ) ? $resource_id : 0 );
?>
<form id="convertkit-restrict-content-form" class="convertkit-restrict-content-form" action="<?php echo esc_attr( add_query_arg( array( 'convertkit_login' => 1 ), get_permalink( $post_id ) ) ); ?>#convertkit-restrict-content" method="post">
	<div id="convertkit-restrict-content-email-field" class="<?php echo sanitize_html_class( ( is_wp_error( WP_ConvertKit()->get_class( 'output_restrict_content' )->error ) ? 'convertkit-restrict-content-error' : '' ) ); ?>">
		<input type="email" name="convertkit_email" id="convertkit_email" value="" placeholder="<?php esc_attr_e( 'Email Address', 'convertkit' ); ?>" required />
		<?php
		// Output the submit button. If a spam protection provider is active
		// it renders the button plus any accompanying widget div; otherwise a
		// plain submit button is output.
		$spam          = new ConvertKit_Spam_Protection();
		$spam_provider = $spam->get_active_provider();
		if ( $spam_provider !== false ) {
			echo $spam_provider->get_submit_button_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				WP_ConvertKit()->get_class( 'output_restrict_content' )->restrict_content_settings->get_by_key( 'email_button_label' ),
				'convertkit_member_content_login',
				array( 'wp-block-button__link', 'wp-block-button__link' )
			);
		} else {
			?>
			<input type="submit" class="wp-block-button__link wp-block-button__link" value="<?php echo esc_attr( WP_ConvertKit()->get_class( 'output_restrict_content' )->restrict_content_settings->get_by_key( 'email_button_label' ) ); ?>" />
			<?php
		}
		?>
		<input type="hidden" name="convertkit_resource_type" value="<?php echo esc_attr( $resource_type ); ?>" />
		<input type="hidden" name="convertkit_resource_id" value="<?php echo esc_attr( $resource_id ); ?>" />
		<input type="hidden" name="convertkit_post_id" value="<?php echo esc_attr( $post_id ); ?>" />
		<?php wp_nonce_field( 'convertkit_restrict_content_login' ); ?>
	</div>
</form>

<?php
// Output notices.
require 'notices.php';
?>

<small><?php echo esc_html( WP_ConvertKit()->get_class( 'output_restrict_content' )->restrict_content_settings->get_by_key( 'email_description_text' ) ); ?></small>
