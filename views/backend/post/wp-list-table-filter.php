<?php
/**
 * Outputs a dropdown filter comprising of Tags and Products
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<select name="convertkit_restrict_content" id="wp-convertkit-restrict-content-filter">
	<option value="0"><?php esc_html_e( 'All content', 'convertkit' ); ?></option>
	<option value="all-member-only"<?php selected( $this->restrict_content_filter, 'all-member-only' ); ?>><?php esc_html_e( 'All member-only content', 'convertkit' ); ?></option>

	<?php
	// Forms.
	if ( $this->forms->inline_exist() ) {
		?>
		<optgroup label="<?php esc_attr_e( 'Forms', 'convertkit' ); ?>">
			<?php
			foreach ( $this->forms->get_inline() as $convertkit_form ) {
				printf(
					'<option value="form_%s"%s>%s [%s]</option>',
					esc_attr( $convertkit_form['id'] ),
					selected( $this->restrict_content_filter, 'form_' . $convertkit_form['id'], false ),
					esc_attr( $convertkit_form['name'] ),
					( ! empty( $convertkit_form['format'] ) ? esc_attr( $convertkit_form['format'] ) : 'inline' )
				);
			}
			?>
		</optgroup>
		<?php
	}

	// Tags.
	if ( $this->tags->exist() ) {
		?>
		<optgroup label="<?php esc_attr_e( 'Tags', 'convertkit' ); ?>">
			<?php
			foreach ( $this->tags->get() as $convertkit_tag ) {
				?>
				<option value="tag_<?php echo esc_attr( $convertkit_tag['id'] ); ?>"<?php selected( 'tag_' . $convertkit_tag['id'], $this->restrict_content_filter ); ?>><?php echo esc_attr( $convertkit_tag['name'] ); ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}

	// Products.
	if ( $this->products->exist() ) {
		?>
		<optgroup label="<?php esc_attr_e( 'Products', 'convertkit' ); ?>">
			<?php
			foreach ( $this->products->get() as $product ) {
				?>
				<option value="product_<?php echo esc_attr( $product['id'] ); ?>"<?php selected( 'product_' . $product['id'], $this->restrict_content_filter ); ?>><?php echo esc_attr( $product['name'] ); ?></option>
				<?php
			}
			?>
		</optgroup>
		<?php
	}
	?>
</select>
