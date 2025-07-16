<?php
/**
 * Add Term Fields template
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<div class="form-field term-description-wrap">
	<label for="wp-convertkit-form"><?php esc_html_e( 'Kit Form', 'convertkit' ); ?></label>

	<div class="convertkit-select2-container convertkit-select2-container-grid">
		<?php
		$convertkit_forms->output_select_field_all(
			'wp-convertkit[form]',
			'wp-convertkit-form',
			array(
				'convertkit-select2',
			),
			'-1',
			array(
				'-1' => esc_html__( 'Default', 'convertkit' ),
				'0'  => esc_html__( 'None', 'convertkit' ),
			)
		);
		?>

		<button class="wp-convertkit-refresh-resources" class="button button-secondary" title="<?php esc_attr_e( 'Refresh Forms from Kit account', 'convertkit' ); ?>" data-resource="forms" data-field="#wp-convertkit-form">
			<span class="dashicons dashicons-update"></span>
		</button>
		<p class="description">
			<code><?php esc_html_e( 'Default', 'convertkit' ); ?></code>
			<?php esc_html_e( ': Display a form based on the Post\'s settings.', 'convertkit' ); ?>
			<br />
				<code><?php esc_html_e( 'None', 'convertkit' ); ?></code>
				<?php esc_html_e( ': do not display a form.', 'convertkit' ); ?>
				<br />
			<?php
			printf(
				/* translators: Taxonomy Name */
				esc_html__( 'Any other option will display that form after the main content for Posts assigned to this %s.', 'convertkit' ),
				'category'
			);
			?>
		</p>
	</div>
</div>

<div class="form-field term-description-wrap">
	<label for="wp-convertkit-form-position"><?php esc_html_e( 'Display Kit Form on Archive?', 'convertkit' ); ?></label>

	<select name="wp-convertkit[form_position]" id="wp-convertkit-form-position" size="1">
		<option value="" selected><?php esc_attr_e( 'No', 'convertkit' ); ?></option>
		<option value="before"><?php esc_attr_e( 'Before Posts', 'convertkit' ); ?></option>
		<option value="after"><?php esc_attr_e( 'After Posts', 'convertkit' ); ?></option>
	</select>
	<p class="description">
		<?php
		esc_html_e( 'Whether to display the Form on this Category\'s archive page, above or below the main posts list.', 'convertkit' );
		?>
	</p>

	<?php
	wp_nonce_field( 'wp-convertkit-save-meta', 'wp-convertkit-save-meta-nonce' );
	?>
</div>
