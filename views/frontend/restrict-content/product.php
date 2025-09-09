<?php
/**
 * Outputs the restricted content message,
 * and a form for the subscriber to enter their
 * email address if they've already subscribed
 * to the Kit Product.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<div id="convertkit-restrict-content">
	<?php
	require 'header.php';

	// Output product button, if specified.
	if ( isset( $button ) ) {
		echo wp_kses( $button, convertkit_kses_allowed_html() );
	}

	// Output a login link or form, if require login enabled.
	require 'login.php';
	?>
</div>
