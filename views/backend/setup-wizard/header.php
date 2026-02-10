<?php
/**
 * Outputs the header template for a Setup Wizard screen
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta name="viewport" content="width=device-width"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo esc_html( 'Kit' ); ?> &lsaquo; <?php bloginfo( 'name' ); ?>  &#8212; WordPress</title>
		<?php
		do_action( 'admin_print_scripts' );
		do_action( 'admin_print_styles' );
		do_action( 'admin_head' );
		?>
	</head>
	<body class="wp-admin wp-core-ui convertkit <?php echo esc_attr( $this->is_modal() ? ' convertkit-modal' : '' ); ?>">
		<div id="convertkit-setup-wizard">
			<?php
			if ( ! $this->is_modal() ) {
				?>
				<header id="convertkit-setup-wizard-header">
					<h1><?php echo esc_html( 'Kit' ); ?></h1>
				</header>
				<?php
			}
			?>

			<div class="wrap">
				<?php
				if ( ! $this->is_modal() ) {
					?>
					<div id="convertkit-setup-wizard-progress">
						<ol>
							<?php
							$step_count = 1;
							foreach ( $this->steps as $step_name => $step ) {
								?>
								<li class="step-<?php echo esc_attr( $step_name ); ?><?php echo ( $step_count <= $this->get_current_step_number() ? ' done' : '' ); ?>"><?php echo esc_html( $step['name'] ); ?></li>
								<?php
								++$step_count;
							}
							?>
						</ol>
					</div>
					<?php
				}

				// If an error occured, display an error notice.
				if ( $this->error ) {
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo esc_html( $this->error ); ?></p>
					</div>

					<?php
				}
				?>
			</div>

			<div id="convertkit-setup-wizard-body">
				<form action="<?php echo esc_attr( $this->next_step_url ); ?>" method="POST">
					<div id="convertkit-setup-wizard-content">
						<?php
						if ( ! $this->is_modal() ) {
							?>
								<div id="convertkit-setup-wizard-step">
								<?php
								printf(
									/* translators: %1$s: Current Step, %2$s: Total Steps */
									esc_html__( 'Step %1$s of %2$s', 'convertkit' ),
									esc_html( $this->get_current_step_number() ),
									esc_html( $this->get_total_steps() )
								);
								?>
							</div>
							<?php
						}
						?>
