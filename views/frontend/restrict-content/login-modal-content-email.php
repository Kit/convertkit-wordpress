<?php
/**
 * Outputs the restricted content product email field
 * for the subscriber to enter their email address if
 * they've already subscribed to the ConvertKit Product,
 * displayed within a modal.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<h3><?php echo esc_html( WP_ConvertKit()->get_class( 'output_restrict_content' )->restrict_content_settings->get_by_key( 'email_heading' ) ); ?></h3>
<?php
require 'login-email.php';
?>