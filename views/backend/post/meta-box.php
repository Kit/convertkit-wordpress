<?php
/**
 * Metabox view
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<table class="form-table">
	<tbody>
		<!-- Form -->
		<tr valign="top">
			<th scope="row">
				<label for="wp-convertkit-form"><?php esc_html_e( 'Form', 'convertkit' ); ?></label>
			</th>
			<td>
				<div class="convertkit-select2-container convertkit-select2-container-grid">
					<?php
					$convertkit_forms->output_select_field_all(
						'wp-convertkit[form]',
						'wp-convertkit-form',
						array(
							'convertkit-select2',
							'widefat',
						),
						esc_attr( $convertkit_post->get_form() ),
						array(
							'-1' => esc_html__( 'Default', 'convertkit' ),
							'0'  => esc_html__( 'None', 'convertkit' ),
						)
					);
					?>
					<button class="wp-convertkit-refresh-resources" class="button button-secondary hide-if-no-js" title="<?php esc_attr_e( 'Refresh Forms from Kit account', 'convertkit' ); ?>" data-resource="forms" data-field="#wp-convertkit-form">
						<span class="dashicons dashicons-update"></span>
					</button>
					<p class="description">
						<code><?php esc_html_e( 'Default', 'convertkit' ); ?></code>
						<?php esc_html_e( ': Uses the form specified on the', 'convertkit' ); ?>
						<a href="<?php echo esc_url( $settings_link ); ?>"><?php esc_html_e( 'settings page', 'convertkit' ); ?></a>
						<br />
						<code><?php esc_html_e( 'None', 'convertkit' ); ?></code>
						<?php esc_html_e( ': do not display a form.', 'convertkit' ); ?>
						<br />
						<?php esc_html_e( 'Any other option will display that form after the main content.', 'convertkit' ); ?>
						<br />
						<?php
						printf(
							/* translators: Link to sign in to ConvertKit */
							esc_html__( 'To make changes to your forms, %s', 'convertkit' ),
							'<a href="' . esc_url( convertkit_get_sign_in_url() ) . '" target="_blank">' . esc_html__( 'sign in to Kit', 'convertkit' ) . '</a>'
						);
						?>
					</p>
				</div>
			</td>
		</tr>

		<!-- Landing Page -->
		<?php
		if ( 'page' === $post->post_type ) {
			?>
			<tr valign="top">
				<th scope="row">
					<label for="wp-convertkit-landing_page"><?php esc_html_e( 'Landing Page', 'convertkit' ); ?></label>
				</th>
				<td>
					<div class="convertkit-select2-container convertkit-select2-container-grid">
						<select name="wp-convertkit[landing_page]" id="wp-convertkit-landing_page" class="convertkit-select2">
							<option <?php selected( '', $convertkit_post->get_landing_page() ); ?> value="0" data-preserve-on-refresh="1"><?php esc_html_e( 'None', 'convertkit' ); ?></option>
							<?php
							if ( $convertkit_landing_pages->exist() ) {
								foreach ( $convertkit_landing_pages->get() as $landing_page ) {
									if ( isset( $convertkit_landing_page['url'] ) ) {
										?>
										<option value="<?php echo esc_attr( $landing_page['url'] ); ?>"<?php selected( $landing_page['url'], $convertkit_post->get_landing_page() ); ?>><?php echo esc_attr( $landing_page['name'] ); ?></option>
										<?php
									} else {
										?>
										<option value="<?php echo esc_attr( $landing_page['id'] ); ?>"<?php selected( $landing_page['id'], $convertkit_post->get_landing_page() ); ?>><?php echo esc_attr( $landing_page['name'] ); ?></option>
										<?php
									}
								}
							}
							?>
						</select>
						<button class="wp-convertkit-refresh-resources" class="button button-secondary hide-if-no-js" title="<?php esc_attr_e( 'Refresh Landing Pages from Kit account', 'convertkit' ); ?>" data-resource="landing_pages" data-field="#wp-convertkit-landing_page">
							<span class="dashicons dashicons-update"></span>
						</button>
						<p class="description">
							<?php esc_html_e( 'Select a landing page to make it appear in place of this page.', 'convertkit' ); ?>
							<br />
							<?php
							printf(
								/* translators: Link to sign in to ConvertKit */
								esc_html__( 'To make changes to your landing pages, %s', 'convertkit' ),
								'<a href="' . esc_url( convertkit_get_sign_in_url() ) . '" target="_blank">' . esc_html__( 'sign in to Kit', 'convertkit' ) . '</a>'
							);
							?>
						</p>
					</div>
				</td>
			</tr>
			<?php
		}
		?>

		<!-- Tag -->
		<tr valign="top">
			<th scope="row">
				<label for="wp-convertkit-tag"><?php esc_html_e( 'Add a Tag', 'convertkit' ); ?></label>
			</th>
			<td>
				<div class="convertkit-select2-container convertkit-select2-container-grid">
					<select name="wp-convertkit[tag]" id="wp-convertkit-tag" class="convertkit-select2">
						<option value="0"<?php selected( '', $convertkit_post->get_tag() ); ?> data-preserve-on-refresh="1"><?php esc_html_e( 'None', 'convertkit' ); ?></option>
						<?php
						if ( $convertkit_tags->exist() ) {
							foreach ( $convertkit_tags->get() as $convertkit_tag ) {
								?>
								<option value="<?php echo esc_attr( $convertkit_tag['id'] ); ?>"<?php selected( $convertkit_tag['id'], $convertkit_post->get_tag() ); ?>><?php echo esc_attr( $convertkit_tag['name'] ); ?></option>
								<?php
							}
						}
						?>
					</select>
					<button class="wp-convertkit-refresh-resources" class="button button-secondary hide-if-no-js" title="<?php esc_attr_e( 'Refresh Tags from Kit account', 'convertkit' ); ?>" data-resource="tags" data-field="#wp-convertkit-tag">
						<span class="dashicons dashicons-update"></span>
					</button>
					<p class="description">
						<?php esc_html_e( 'Select a tag to apply to visitors of this page who are subscribed.', 'convertkit' ); ?>
						<br />
						<?php esc_html_e( 'A visitor is deemed to be subscribed if they have clicked a link in an email to this site which includes their subscriber ID, or have entered their email address in a Kit Form on this site.', 'convertkit' ); ?>
					</p>
				</div>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="wp-convertkit-restrict_content"><?php esc_html_e( 'Member Content', 'convertkit' ); ?></label>
			</th>
			<td>
				<div class="convertkit-select2-container convertkit-select2-container-grid">
					<select name="wp-convertkit[restrict_content]" id="wp-convertkit-restrict_content" class="convertkit-select2">
						<option value="0"<?php selected( '', $convertkit_post->get_restrict_content() ); ?> data-preserve-on-refresh="1"><?php esc_html_e( 'Don\'t restrict content to member-only.', 'convertkit' ); ?></option>

						<optgroup label="<?php esc_attr_e( 'Forms', 'convertkit' ); ?>" data-resource="forms">
							<?php
							// Forms.
							if ( $convertkit_forms->inline_exist() ) {
								foreach ( $convertkit_forms->get_inline() as $convertkit_form ) {
									printf(
										'<option value="form_%s"%s>%s [%s]</option>',
										esc_attr( $convertkit_form['id'] ),
										selected( $convertkit_post->get_restrict_content(), 'form_' . $convertkit_form['id'], false ),
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
							if ( $convertkit_tags->exist() ) {
								foreach ( $convertkit_tags->get() as $convertkit_tag ) {
									?>
									<option value="tag_<?php echo esc_attr( $convertkit_tag['id'] ); ?>"<?php selected( 'tag_' . $convertkit_tag['id'], $convertkit_post->get_restrict_content() ); ?>><?php echo esc_attr( $convertkit_tag['name'] ); ?></option>
									<?php
								}
							}
							?>
						</optgroup>

						<optgroup label="<?php esc_attr_e( 'Products', 'convertkit' ); ?>" data-resource="products">
							<?php
							// Products.
							if ( $convertkit_products->exist() ) {
								foreach ( $convertkit_products->get() as $product ) {
									?>
									<option value="product_<?php echo esc_attr( $product['id'] ); ?>"<?php selected( 'product_' . $product['id'], $convertkit_post->get_restrict_content() ); ?>><?php echo esc_attr( $product['name'] ); ?></option>
									<?php
								}
							}
							?>
						</optgroup>
					</select>
					<button class="wp-convertkit-refresh-resources" class="button button-secondary hide-if-no-js" title="<?php esc_attr_e( 'Refresh Products and Tags from Kit account', 'convertkit' ); ?>" data-resource="restrict_content" data-field="#wp-convertkit-restrict_content">
						<span class="dashicons dashicons-update"></span>
					</button>
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
			</td>
		</tr>
	</tbody>
</table>

<?php
wp_nonce_field( 'wp-convertkit-save-meta', 'wp-convertkit-save-meta-nonce' );
