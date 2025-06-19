<?php
/**
 * Outputs the content for the Restrict Content Setup Wizard > Setup step.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<h1><?php esc_html_e( 'Member Content', 'convertkit' ); ?></h1>
<p>
	<?php
	printf(
		/* translators: %1$s: Link to ConvertKit Products, %2$s: ConvertKit Tag */
		esc_html__( 'This will generate content that visitors can access once they purchase a %1$s or subscribe to a %2$s.', 'convertkit' ),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_attr( 'https://app.kit.com/products' ),
			esc_html__( 'Kit product', 'convertkit' )
		),
		esc_html__( 'Kit tag', 'convertkit' )
	);
	?>
</p>

<?php
// If no Products and Tags exist on the ConvertKit account, show the user how to add a Product to ConvertKit,
// with an option to refresh this page so that they can then select the Product to restrict content with.
if ( ! $this->products->exist() && ! $this->tags->exist() ) {
	?>
	<p>
		<?php
		esc_html_e( 'To get started, you first need to create a Product or Tag in Kit. Click the button below to get started.', 'convertkit' );
		?>
	</p>

	<div class="convertkit-setup-wizard-grid">
		<div>
			<a href="<?php echo esc_url( convertkit_get_new_product_url() ); ?>" target="_blank" class="button button-primary button-hero">
				<?php esc_html_e( 'Create product', 'convertkit' ); ?>
			</a>
			<span class="description">
				<?php esc_html_e( 'Require visitors to purchase a Kit product to access your content.', 'convertkit' ); ?>
			</span>
		</div>

		<div>
			<a href="<?php echo esc_url( convertkit_get_new_tag_url() ); ?>" target="_blank" class="button button-primary button-hero">
				<?php esc_html_e( 'Create tag', 'convertkit' ); ?>
			</a>
			<span class="description">
				<?php esc_html_e( 'Require visitors to enter their email address, subscribing them to a Kit tag to access your content.', 'convertkit' ); ?>
			</span>
		</div>
	</div>

	<center>
		<a href="<?php echo esc_url( $this->current_url ); ?>" class="button button-primary button-hero">
			<?php esc_html_e( 'I\'ve created a Product or Tag in Kit', 'convertkit' ); ?>
		</a>
	</center>			
	<?php
} else {
	?>
	<h2><?php esc_html_e( 'What type of content are you offering?', 'convertkit' ); ?></h2>

	<div class="convertkit-setup-wizard-grid">
		<div>
			<a href="<?php echo esc_url( $this->download_url ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'Download', 'convertkit' ); ?>
			</a>
			<span class="description">
				<?php esc_html_e( 'Require visitors to purchase a Kit product, or subscribe to a Kit tag or form, granting access to a single Page\'s content, which includes downloadable assets.', 'convertkit' ); ?>
			</span>
		</div>

		<div>
			<a href="<?php echo esc_url( $this->course_url ); ?>" class="button button-primary button-hero">
				<?php esc_html_e( 'Course', 'convertkit' ); ?>
			</a>
			<span class="description">
				<?php esc_html_e( 'Require visitors to purchase a Kit product, or subscribe to a Kit tag or form, granting access to a sequential series of Pages, such as a course, lessons or tutorials.', 'convertkit' ); ?>
			</span>
		</div>
	</div>
	<?php
}
