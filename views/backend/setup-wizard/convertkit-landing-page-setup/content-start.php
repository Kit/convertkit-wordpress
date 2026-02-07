<?php
/**
 * Outputs the content for the Restrict Content Setup Wizard > Setup step.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<h1><?php esc_html_e( 'Landing Page', 'convertkit' ); ?></h1>
<p>
	<?php
	esc_html_e( 'This will display a Kit landing page on the URL you specify on this WordPress web site.', 'convertkit' );
	?>
</p>

<?php
// If no Landing Pages exist on the ConvertKit account, show the user how to add a Landing Page to ConvertKit,
// with an option to refresh this page so that they can then select the Landing Page.
if ( ! $this->landing_pages->exist() ) {
	?>
	<p>
		<?php
		esc_html_e( 'To get started, you first need to create a landing page in Kit. Click the button below to get started.', 'convertkit' );
		?>
	</p>

	<div class="convertkit-setup-wizard-grid">
		<div>
			<a href="<?php echo esc_url( convertkit_get_new_landing_page_url() ); ?>" target="_blank" class="button button-primary button-hero">
				<?php esc_html_e( 'Create landing page', 'convertkit' ); ?>
			</a>
		</div>

		<div>
			<a href="<?php echo esc_url( $this->current_url ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'I\'ve created a landing page in Kit', 'convertkit' ); ?>
			</a>
		</div>
	</div>		
	<?php
} else {
	?>
	<hr />
	<div>
		<label for="landing_page"><?php esc_html_e( 'Which landing page would you like to display?', 'convertkit' ); ?></label>
		<select name="landing_page" id="landing_page" class="convertkit-select2 widefat">
			<?php
			foreach ( $this->landing_pages->get() as $landing_page ) {
				if ( isset( $convertkit_landing_page['url'] ) ) {
					?>
					<option value="<?php echo esc_attr( $landing_page['url'] ); ?>"><?php echo esc_attr( $landing_page['name'] ); ?></option>
					<?php
				} else {
					?>
					<option value="<?php echo esc_attr( $landing_page['id'] ); ?>"><?php echo esc_attr( $landing_page['name'] ); ?></option>
					<?php
				}
			}
			?>
		</select>
	</div>
	<div>
		<label for="post_name"><?php esc_html_e( 'Which permalink / slug should be assigned to this landing page?', 'convertkit' ); ?></label>
		<input type="text" name="post_name" id="post_name" placeholder="<?php esc_attr_e( 'my-landing-page', 'convertkit' ); ?>" class="widefat" required />
	</div>
	
	<?php
}
