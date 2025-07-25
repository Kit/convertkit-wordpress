<?php
/**
 * Quick Edit view
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<div id="convertkit-quick-edit" class="convertkit-bulk-quick-edit" style="display:none;">
	<!-- Form -->
	<div>
		<label for="wp-convertkit-quick-edit-form">
			<span class="title convertkit-icon-form"><?php esc_html_e( 'Form', 'convertkit' ); ?></span>

			<?php
			$convertkit_forms->output_select_field_all(
				'wp-convertkit[form]',
				'wp-convertkit-quick-edit-form',
				false,
				false,
				array(
					'-1' => esc_html__( 'Default', 'convertkit' ),
					'0'  => esc_html__( 'None', 'convertkit' ),
				)
			);
			?>
		</label>
		<button class="wp-convertkit-refresh-resources" class="button button-secondary" title="<?php esc_attr_e( 'Refresh Forms from Kit account', 'convertkit' ); ?>" data-resource="forms" data-field="#wp-convertkit-quick-edit-form">
			<span class="dashicons dashicons-update"></span>
		</button>
	</div>

	<!-- Tag -->
	<div>
		<label for="wp-convertkit-quick-edit-tag">
			<span class="title convertkit-icon-tag"><?php esc_html_e( 'Tag', 'convertkit' ); ?></span>
			<select name="wp-convertkit[tag]" id="wp-convertkit-quick-edit-tag" size="1">
				<option value="0" data-preserve-on-refresh="1"><?php esc_html_e( 'None', 'convertkit' ); ?></option>
				<?php
				if ( $convertkit_tags->exist() ) {
					foreach ( $convertkit_tags->get() as $convertkit_tag ) {
						?>
						<option value="<?php echo esc_attr( $convertkit_tag['id'] ); ?>"><?php echo esc_attr( $convertkit_tag['name'] ); ?></option>
						<?php
					}
				}
				?>
			</select>
		</label>
		<button class="wp-convertkit-refresh-resources" class="button button-secondary" title="<?php esc_attr_e( 'Refresh Tags from Kit account', 'convertkit' ); ?>" data-resource="tags" data-field="#wp-convertkit-quick-edit-tag">
			<span class="dashicons dashicons-update"></span>
		</button>
	</div>

	<!-- Restrict Content -->
	<div>
		<label for="wp-convertkit-quick-edit-restrict_content">
			<span class="title convertkit-icon-restrict-content"><?php esc_html_e( 'Member', 'convertkit' ); ?></span>
			<select name="wp-convertkit[restrict_content]" id="wp-convertkit-quick-edit-restrict_content" size="1">
				<option value="0" data-preserve-on-refresh="1"><?php esc_html_e( 'Don\'t restrict content to member-only.', 'convertkit' ); ?></option>

				<optgroup label="<?php esc_attr_e( 'Forms', 'convertkit' ); ?>" data-resource="forms">
					<?php
					// Forms.
					if ( $convertkit_forms->inline_exist() ) {
						foreach ( $convertkit_forms->get_inline() as $convertkit_form ) {
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
					if ( $convertkit_tags->exist() ) {
						foreach ( $convertkit_tags->get() as $convertkit_tag ) {
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
					if ( $convertkit_products->exist() ) {
						foreach ( $convertkit_products->get() as $product ) {
							?>
							<option value="product_<?php echo esc_attr( $product['id'] ); ?>"><?php echo esc_attr( $product['name'] ); ?></option>
							<?php
						}
					}
					?>
				</optgroup>
			</select>
		</label>
		<button class="wp-convertkit-refresh-resources" class="button button-secondary" title="<?php esc_attr_e( 'Refresh Products and Tags from Kit account', 'convertkit' ); ?>" data-resource="restrict_content" data-field="#wp-convertkit-quick-edit-restrict_content">
			<span class="dashicons dashicons-update"></span>
		</button>
	</div>
	<?php
	wp_nonce_field( 'wp-convertkit-save-meta', 'wp-convertkit-save-meta-nonce' );
	?>
</div>
