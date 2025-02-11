<?php
/**
 * Bulk Edit view
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<div id="convertkit-bulk-edit" class="convertkit-bulk-quick-edit" style="display:none;">
	<!-- Form -->
	<div>
		<label for="wp-convertkit-bulk-edit-form">
			<span class="title convertkit-icon-form"><?php esc_html_e( 'Form', 'convertkit' ); ?></span>

			<?php
			// For Bulk Edit, the 'No Change' value is -1. However, because this Plugin has historically used -1
			// to mean that the Default form for the Post Type should be displayed, using -1 as 'No Change' in Bulk Edit
			// would result in Posts/Pages having (or not having) the Form setting updated, when the user may/may not
			// have selected the 'Default' option.
			// Therefore, we use -2 to denote 'No Change'.
			echo $convertkit_forms->get_select_field_all( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'wp-convertkit[form]',
				'wp-convertkit-bulk-edit-form',
				false,
				false,
				array(
					'-2' => esc_html__( '— No Change —', 'convertkit' ),
					'-1' => esc_html__( 'Default', 'convertkit' ),
					'0'  => esc_html__( 'None', 'convertkit' ),
				)
			);
			?>
		</label>
		<button class="wp-convertkit-refresh-resources" class="button button-secondary" title="<?php esc_attr_e( 'Refresh Forms from Kit account', 'convertkit' ); ?>" data-resource="forms" data-field="#wp-convertkit-bulk-edit-form">
			<span class="dashicons dashicons-update"></span>
		</button>
	</div>

	<!-- Tag -->
	<div>
		<label for="wp-convertkit-bulk-edit-tag">
			<span class="title convertkit-icon-tag"><?php esc_html_e( 'Tag', 'convertkit' ); ?></span>
			<select name="wp-convertkit[tag]" id="wp-convertkit-bulk-edit-tag" size="1">
				<?php
				// For Bulk Edit, the 'No Change' value is -1. However, because this Plugin has historically used -1
				// to mean that the Default form for the Post Type should be displayed, using -1 as 'No Change' in Bulk Edit
				// would result in Posts/Pages having (or not having) the Form setting updated, when the user may/may not
				// have selected the 'Default' option.
				// Therefore, we use -2 to denote 'No Change', even though this setting is for the Tag, so we're at least consistent.
				?>
				<option value="-2" data-preserve-on-refresh="1"><?php esc_html_e( '— No Change —', 'convertkit' ); ?></option>
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
		<button class="wp-convertkit-refresh-resources" class="button button-secondary" title="<?php esc_attr_e( 'Refresh Tags from Kit account', 'convertkit' ); ?>" data-resource="tags" data-field="#wp-convertkit-bulk-edit-tag">
			<span class="dashicons dashicons-update"></span>
		</button>
	</div>

	<!-- Restrict Content -->
	<div>
		<label for="wp-convertkit-bulk-edit-restrict_content">
			<span class="title convertkit-icon-restrict-content"><?php esc_html_e( 'Member', 'convertkit' ); ?></span>
			<select name="wp-convertkit[restrict_content]" id="wp-convertkit-bulk-edit-restrict_content" size="1">
				<?php
				// For Bulk Edit, the 'No Change' value is -1. However, because this Plugin has historically used -1
				// to mean that the Default form for the Post Type should be displayed, using -1 as 'No Change' in Bulk Edit
				// would result in Posts/Pages having (or not having) the Form setting updated, when the user may/may not
				// have selected the 'Default' option.
				// Therefore, we use -2 to denote 'No Change', even though this setting is for the Tag, so we're at least consistent.
				?>
				<option value="-2" data-preserve-on-refresh="1"><?php esc_html_e( '— No Change —', 'convertkit' ); ?></option>
				<option value="0" data-preserve-on-refresh="1"><?php esc_html_e( 'Don\'t restrict content to members only.', 'convertkit' ); ?></option>

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
		<button class="wp-convertkit-refresh-resources" class="button button-secondary" title="<?php esc_attr_e( 'Refresh Products and Tags from Kit account', 'convertkit' ); ?>" data-resource="restrict_content" data-field="#wp-convertkit-bulk-edit-restrict_content">
			<span class="dashicons dashicons-update"></span>
		</button>
	</div>
	<?php
	wp_nonce_field( 'wp-convertkit-save-meta', 'wp-convertkit-save-meta-nonce' );
	?>
</div>
