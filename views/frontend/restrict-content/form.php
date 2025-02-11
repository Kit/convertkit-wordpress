<?php
/**
 * Outputs a form for the subscriber to enter their
 * email address to subscribe to the form, granting
 * them access.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

echo $form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>
<div id="convertkit-restrict-content">
	<?php
	// Output a login link or form, if require login enabled.
	require 'login.php';
	?>
</div>
