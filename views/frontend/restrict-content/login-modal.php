<?php
/**
 * Outputs the Restrict Content by Product Email Login Modal.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>

<div id="convertkit-restrict-content-modal-background">
	<div id="convertkit-restrict-content-modal">
		<div id="convertkit-restrict-content-modal-loading"></div>
		<button id="convertkit-restrict-content-modal-close"><?php esc_html_e( 'Close', 'convertkit' ); ?></button>
		<div id="convertkit-restrict-content-modal-content">
			<?php
			require 'login-modal-content-email.php';
			?>
		</div>
	</div>
</div>