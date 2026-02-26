<?php
/**
 * TinyMCE Modal Field Row view
 *
 * @package ConvertKit
 * @author ConvertKit
 */

// Output Field.
$condition = '';
if ( isset( $field['condition'] ) ) {
	if ( is_array( $field['condition']['value'] ) ) {
		$condition = implode( ' ', $field['condition']['value'] );
	} else {
		$condition = $field['condition']['value'];
	}
}
?>
<div class="convertkit-option">
	<div class="left">
		<label for="tinymce_modal_<?php echo esc_attr( $field_name ); ?>">
			<?php echo esc_attr( $field['label'] ); ?>
		</label>
	</div>
	<div class="right<?php echo ! empty( $condition ) ? ' ' . esc_attr( $condition ) : ''; ?>">
		<?php
		require 'modal-field.php';
		?>
	</div>
</div>
