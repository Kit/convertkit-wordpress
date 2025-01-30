<?php
/**
 * Outputs the footer template for a Setup Wizard screen
 *
 * @package ConvertKit
 * @author ConvertKit
 */

?>				
					</div><!-- /#convertkit-setup-wizard-content -->

					<div id="convertkit-setup-wizard-footer">
						<?php
						// Display a back link if supplied.
						if ( $this->previous_step_url ) {
							?>
							<div class="left">
								<a href="<?php echo esc_url( $this->previous_step_url ); ?>" class="button button-hero">
									<?php esc_html_e( 'Back', 'convertkit' ); ?>
								</a>
							</div>
							<?php
						}

						// Display a submit button if supplied.
						if ( isset( $this->steps[ $this->step ]['next_button'] ) ) {
							?>
							<div class="right">
								<?php
								if ( isset( $this->steps[ $this->step ]['next_button']['link'] ) ) {
									// Link.
									?>
									<a href="<?php echo esc_url( $this->steps[ $this->step ]['next_button']['link'] ); ?>" class="button button-primary button-hero"><?php echo esc_html( $this->steps[ $this->step ]['next_button']['label'] ); ?></a>
									<?php
								} else {
									// Submit button.
									wp_nonce_field( $this->page_name );
									?>
									<button class="button button-primary button-hero"><?php echo esc_html( $this->steps[ $this->step ]['next_button']['label'] ); ?></button>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
				</form>
			</div><!-- /#convertkit-setup-wizard-body -->

			<?php
			// Show exit link if we're not on the last step.
			if ( $this->step < count( $this->steps ) && ! $this->is_modal() ) {
				?>
				<div id="convertkit-setup-wizard-exit-link">
					<a href="<?php echo esc_url( $this->exit_url ); ?>" class="convertkit-confirm" title="<?php esc_html_e( 'Exit wizard', 'convertkit' ); ?>" data-message="<?php esc_html_e( 'Are you sure you want to exit the wizard? Setup is incomplete.', 'convertkit' ); ?>">
						<?php esc_html_e( 'Exit wizard', 'convertkit' ); ?>
					</a>
				</div>
				<?php
			}

			// Output Intercom.
			convertkit_output_intercom_messenger();
			?>
		</div>
	</body>
</html>
