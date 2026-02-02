<?php
/**
 * Outputs the content for the Landing Page Wizard > Done step.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<h1><?php esc_html_e( 'Setup complete', 'convertkit' ); ?></h1>
<p>
	<?php
	printf(
		'%s <code>%s</code>',
		esc_html__( 'You can access the landing page at', 'convertkit' ),
		esc_url( get_permalink( $this->result ) )
	);
	?>
</p>

<div class="convertkit-setup-wizard-grid">
	<div>
		<a href="<?php echo esc_url( get_permalink( $this->result ) ); ?>" class="button button-primary button-hero">
			<?php esc_html_e( 'View landing page', 'convertkit' ); ?>
		</a>
		<span class="description"><?php esc_html_e( 'Exit the wizard and view the landing page.', 'convertkit' ); ?></span>
	</div>

	<div>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="button button-primary button-hero">
			<?php esc_html_e( 'Exit', 'convertkit' ); ?>
		</a>
		<span class="description"><?php esc_html_e( 'Exit the wizard and load the WordPress Pages screen.', 'convertkit' ); ?></span>
	</div>
</div>
