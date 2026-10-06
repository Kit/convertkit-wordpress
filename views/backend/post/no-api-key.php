<?php
/**
 * Outputs a message in the metabox when no Access Token is defined.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<p>
	<?php
	printf(
		'%s %s',
		esc_html__( 'For the Kit Plugin to function, please', 'convertkit' ),
		sprintf(
			'<a href="%s">%s</a>',
			esc_url( convertkit_get_oauth_url() ),
			esc_html__( 'connect your Kit account.', 'convertkit' )
		)
	);
	?>
</p>
