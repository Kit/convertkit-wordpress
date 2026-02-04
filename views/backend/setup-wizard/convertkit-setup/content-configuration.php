<?php
/**
 * Outputs the content for the Plugin Setup Wizard > Form Configuration step.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

// If no Forms exist on the ConvertKit account, show the user how to add a Form to ConvertKit,
// with an option to refresh this page so that they can then select the Form to be displayed on e.g. Posts.
if ( ! $this->forms->exist() ) {
	?>
	<h1><?php esc_html_e( 'Create your first Kit Form', 'convertkit' ); ?></h1>
	<p>
		<?php
		esc_html_e( 'To get email subscribers, you first need to create a form in Kit. Click the button below to get started.', 'convertkit' );
		?>
	</p>

	<hr />

	<a href="<?php echo esc_url( convertkit_get_new_form_url() ); ?>" target="_blank" class="button button-primary button-hero">
		<?php esc_html_e( 'Create form', 'convertkit' ); ?>
	</a>

	<p>
		<?php
		printf(
			'%1$s <a href="https://help.kit.com/en/articles/3860348-how-to-create-your-first-form-in-convertkit" target="_blank">%2$s</a>',
			esc_html__( 'Not sure how to do this in Kit?', 'convertkit' ),
			esc_html__( 'Follow our step by step documentation', 'convertkit' )
		);
		?>
	</p>
	<?php
} else {
	// Show options to configure Form to display on Posts and Pages.
	?>
	<h1><?php esc_html_e( 'Display an email capture form', 'convertkit' ); ?></h1>
	<p>
		<?php
		esc_html_e( 'To get email subscribers, you need to display a Kit form on your site, using the options below.', 'convertkit' );
		?>
	</p>

	<hr />

	<div>
		<label for="wp-convertkit-form-posts">
			<?php esc_html_e( 'Which form would you like to display below all blog posts?', 'convertkit' ); ?>
		</label>

		<?php
		$this->forms->output_select_field_all(
			'post_form',
			'wp-convertkit-form-posts',
			array(
				'convertkit-select2',
				'convertkit-preview-output-link',
				'widefat',
			),
			esc_attr( $this->settings->get_default_form( 'post' ) ),
			array(
				'0' => esc_html__( 'Don\'t display an email subscription form on posts.', 'convertkit' ),
			),
			array(
				'data-target' => '#convertkit-preview-form-post',
				'data-link'   => esc_attr( $this->preview_post_url ) . '&convertkit_form_id=',
			)
		);
		?>

		<p class="description">
			<?php
			if ( $this->preview_post_url ) {
				printf(
					'%s %s %s',
					esc_html__( 'Select a form above.', 'convertkit' ),
					'<a href="' . esc_url( $this->preview_post_url ) . '" id="convertkit-preview-form-post" target="_blank">' . esc_html__( 'Click here', 'convertkit' ) . '</a>',
					esc_html__( 'to preview how this will look on individual posts.', 'convertkit' )
				);
			} else {
				esc_html_e( 'Select a form above.', 'convertkit' );
			}
			?>
		</p>
	</div>

	<div>
		<label for="wp-convertkit-form-pages">
			<?php esc_html_e( 'Which form would you like to display below all pages?', 'convertkit' ); ?>
		</label>

		<?php
		$this->forms->output_select_field_all(
			'page_form',
			'wp-convertkit-form-pages',
			array(
				'convertkit-select2',
				'convertkit-preview-output-link',
				'widefat',
			),
			esc_attr( $this->settings->get_default_form( 'page' ) ),
			array(
				'0' => esc_html__( 'Don\'t display an email subscription form on pages.', 'convertkit' ),
			),
			array(
				'data-target' => '#convertkit-preview-form-page',
				'data-link'   => esc_attr( $this->preview_page_url ) . '&convertkit_form_id=',
			)
		);
		?>

		<p class="description">
			<?php
			if ( $this->preview_page_url ) {
				printf(
					'%s %s %s',
					esc_html__( 'Select a form above.', 'convertkit' ),
					'<a href="' . esc_url( $this->preview_page_url ) . '" id="convertkit-preview-form-page" target="_blank">' . esc_html__( 'Click here', 'convertkit' ) . '</a>',
					esc_html__( 'to preview how this will look on individual pages.', 'convertkit' )
				);
			} else {
				esc_html_e( 'Select a form above.', 'convertkit' );
			}
			?>
		</p>
	</div>

	<div class="notice notice-info">
		<p class="description">
			<?php
			esc_html_e( 'To embed email subscriber forms in particular sections of your content on specific Pages or Posts, use the Kit Form block or shortcode when editing a Page or Post.', 'convertkit' );
			?>
		</p>
	</div>

	<div>
		<label for="wp-convertkit-usage-tracking">
			<?php esc_html_e( 'Usage tracking', 'convertkit' ); ?>
		</label>

		<input type="checkbox" name="usage_tracking" id="wp-convertkit-usage-tracking" value="on" checked />

		<p class="description">
			<?php
			printf(
				'%s <a href="https://help.kit.com/en/articles/2502591-how-to-set-up-the-kit-plugin-on-your-wordpress-website" target="_blank">%s</a>',
				esc_html__( 'By allowing us to collect usage data, we can better understand which WordPress configurations, themes and plugins we should test. Complete documentation on usage tracking can be found', 'convertkit' ),
				esc_html__( 'here', 'convertkit' )
			);
			?>
		</p>
	</div>
	<?php
}
