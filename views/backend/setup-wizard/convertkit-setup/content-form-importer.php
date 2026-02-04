<?php
/**
 * Outputs the content for the Plugin Setup Wizard > Form Importer step.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

foreach ( $this->form_importers as $form_importer ) {
	?>
	<div>
		<h1><?php echo esc_html( $form_importer['title'] ); ?></h1>
		<p>
			<?php
			printf(
				/* translators: %s: Form importer title */
				esc_html__( 'We detected the following %s forms in your content. We recommend replacing these with Kit forms, but you may leave them as is.', 'convertkit' ),
				esc_html( $form_importer['title'] )
			);
			?>
		</p>

		<table class="widefat striped">
			<thead>
				<tr>
					<th>
						<?php
						/* translators: %s: Form importer title */
						printf(
							/* translators: %s: Form importer title */
							esc_html__( '%s Form', 'convertkit' ),
							esc_html( $form_importer['title'] )
						);
						?>
					</th>
					<th>
						<?php esc_html_e( 'Kit Form', 'convertkit' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $form_importer['forms'] as $form_importer_form_id => $form_importer_form_title ) {
					?>
					<tr>
						<td><?php echo esc_html( $form_importer_form_title ); ?></td>
						<td>
						<?php
							$this->forms->output_select_field_all(
								'form_importer[' . $form_importer['name'] . '][' . $form_importer_form_id . ']',
								'form-importer-' . $form_importer['name'] . '-' . $form_importer_form_id,
								array(
									'convertkit-select2',
									'widefat',
								),
								'',
								array(
									'0' => esc_html__( 'Don\'t replace.', 'convertkit' ),
								)
							);
						?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<?php
}
