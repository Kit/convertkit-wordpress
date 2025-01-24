<?php
/**
 * Outputs a heading and paragraph text based on the supplied
 * $heading and $text variables, used across Restrict Content
 * by Tag and Product.
 *
 * @since   2.7.1
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<h3><?php echo esc_html( $heading ); ?></h3>
<p>
	<?php
	foreach ( explode( "\n", $text ) as $text_line ) {
		echo esc_html( $text_line ) . '<br />';
	}
	?>
</p>