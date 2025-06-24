<?php
/**
 * Outputs the content for the Restrict Content Setup Wizard > Configure step.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<h1>
	<?php
	printf(
		/* translators: Type of content (download, course) */
		esc_html__( 'Configure %s', 'convertkit' ),
		esc_html( $this->type_label )
	);
	?>
</h1>

<hr />

<div>
	<label for="title">
		<?php esc_html_e( 'What is the name of the content?', 'convertkit' ); ?>
	</label>
	<input type="text" name="title" id="title" class="widefat" placeholder="<?php esc_attr_e( 'e.g. Free PDF, Macro Photography Course', 'convertkit' ); ?>" required />
	<input type="hidden" name="type" value="<?php echo esc_attr( $this->type ); ?>" />
</div>

<div>
	<label for="description">
		<?php esc_html_e( 'Describe the content for non-members.', 'convertkit' ); ?>
	</label>
	<textarea name="description" id="description" class="widefat" required></textarea>
	<p class="description"><?php esc_html_e( 'This will be displayed above product\'s call to action button.', 'convertkit' ); ?></p>
</div>

<?php
if ( $this->type === 'course' ) {
	?>
	<div class="course">
		<div>
			<label for="number_of_pages">
				<?php esc_html_e( 'How many lessons does this course consist of?', 'convertkit' ); ?>
			</label>
			<input type="number" name="number_of_pages" min="1" max="99" step="1" id="number_of_pages" value="3" required />
		</div>
	</div>
	<?php
}
?>

<div>
	<label for="wp-convertkit-restrict_content">
		<?php esc_html_e( 'The Kit Product, Tag or Form the visitor must subscribe to, in order to see the content', 'convertkit' ); ?>
	</label>

	<div class="convertkit-select2-container">
		<select name="restrict_content" id="wp-convertkit-restrict_content" class="convertkit-select2 widefat">
			<optgroup label="<?php esc_attr_e( 'Forms', 'convertkit' ); ?>" data-resource="forms">
				<?php
				// Forms.
				if ( $this->forms->inline_exist() ) {
					foreach ( $this->forms->get_inline() as $convertkit_form ) {
						printf(
							'<option value="form_%s">%s [%s]</option>',
							esc_attr( $convertkit_form['id'] ),
							esc_attr( $convertkit_form['name'] ),
							( ! empty( $convertkit_form['format'] ) ? esc_attr( $convertkit_form['format'] ) : 'inline' )
						);
					}
				}
				?>
			</optgroup>

			<optgroup label="<?php esc_attr_e( 'Tags', 'convertkit' ); ?>" data-resource="tags">
				<?php
				// Tags.
				if ( $this->tags->exist() ) {
					foreach ( $this->tags->get() as $convertkit_tag ) {
						?>
						<option value="tag_<?php echo esc_attr( $convertkit_tag['id'] ); ?>"><?php echo esc_attr( $convertkit_tag['name'] ); ?></option>
						<?php
					}
				}
				?>
			</optgroup>

			<optgroup label="<?php esc_attr_e( 'Products', 'convertkit' ); ?>" data-resource="products">
				<?php
				// Products.
				if ( $this->products->exist() ) {
					foreach ( $this->products->get() as $product ) {
						?>
						<option value="product_<?php echo esc_attr( $product['id'] ); ?>"><?php echo esc_attr( $product['name'] ); ?></option>
						<?php
					}
				}
				?>
			</optgroup>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select the Kit form, tag or product that the visitor must be subscribed to, permitting them access to view this member-only content.', 'convertkit' ); ?>
			<br />
			<code><?php esc_html_e( 'Form', 'convertkit' ); ?></code>
			<?php esc_html_e( ': Displays the Kit form. On submission, the email address will be subscribed to the selected form, granting access to the member-only content. Useful to gate free content in return for an email address.', 'convertkit' ); ?>
			<br />
			<code><?php esc_html_e( 'Tag', 'convertkit' ); ?></code>
			<?php esc_html_e( ': Displays a WordPress styled subscription form. On submission, the email address will be subscribed to the selected tag, granting access to the member-only content. Useful to gate free content in return for an email address.', 'convertkit' ); ?>
			<br />
			<code><?php esc_html_e( 'Product', 'convertkit' ); ?></code>
			<?php esc_html_e( ': Displays a link to the Kit product, and a login form. Useful to gate content that can only be accessed by purchasing the Kit product.', 'convertkit' ); ?>
		</p>
	</div>
</div>
