<?php
/**
 * Outputs the content for the Plugin Setup Wizard > Done step.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<h1><?php esc_html_e( 'Setup complete', 'convertkit' ); ?></h1>
<p>
	<?php esc_html_e( 'The Kit for WordPress Plugin setup is complete.', 'convertkit' ); ?>
</p>

<div class="convertkit-setup-wizard-grid">
	<div>
		<a href="<?php echo esc_url( admin_url( 'index.php' ) ); ?>" class="button button-primary button-hero">
			<?php esc_html_e( 'Dashboard', 'convertkit' ); ?>
		</a>
		<span class="description"><?php esc_html_e( 'Exit the wizard and load the WordPress Dashboard screen.', 'convertkit' ); ?></span>
	</div>

	<div>
		<a href="<?php echo esc_url( convertkit_get_settings_link() ); ?>" class="button button-primary button-hero">
			<?php esc_html_e( 'Plugin Settings', 'convertkit' ); ?>
		</a>
		<span class="description"><?php esc_html_e( 'Exit the wizard and load the Plugin\'s settings screen.', 'convertkit' ); ?></span>
	</div>
</div>
