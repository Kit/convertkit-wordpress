<?php
/**
 * ConvertKit Broadcasts Exporter class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Class to create ConvertKit Broadcasts from WordPress Posts.
 *
 * @since   2.4.0
 */
class ConvertKit_Broadcasts_Exporter {

	/**
	 * Holds the action name in the WP_List_Table.
	 *
	 * @since 2.4.0
	 *
	 * @var   string
	 */
	private $action_name = 'broadcast-export';

	/**
	 * Holds the Broadcast ID that was created on ConvertKit.
	 *
	 * @since 2.4.0
	 *
	 * @var   bool|int
	 */
	private $broadcast_id = false;

	/**
	 * Holds the Broadcasts Settings class.
	 *
	 * @since 2.4.0
	 *
	 * @var   bool|ConvertKit_Settings_Broadcasts
	 */
	private $broadcasts_settings = false;

	/**
	 * Holds the Settings class.
	 *
	 * @since 2.4.0
	 *
	 * @var   bool|ConvertKit_Settings
	 */
	private $settings = false;

	/**
	 * Constructor
	 *
	 * @since   2.4.0
	 */
	public function __construct() {

		// Bail if no API credentials have been set.
		$this->settings = new ConvertKit_Settings();
		if ( ! $this->settings->has_access_and_refresh_token() ) {
			return;
		}

		// Bail if the Enable Export Actions setting is not enabled.
		$this->broadcasts_settings = new ConvertKit_Settings_Broadcasts();
		if ( ! $this->broadcasts_settings->enabled_export() ) {
			return;
		}

		// Run action, if selected.
		add_action( 'init', array( $this, 'run_row_action' ) );

		// Add action below Post Title in WP_List_Table classes.
		add_filter( 'page_row_actions', array( $this, 'add_row_action' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'add_row_action' ), 10, 2 );

	}

	/**
	 * Checks if a Plugin row action was clicked by the User, and if so performs that action.
	 *
	 * @since   2.4.0
	 */
	public function run_row_action() {

		// Bail if no nonce exists or fails verification.
		if ( ! array_key_exists( 'nonce', $_REQUEST ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'action-convertkit-' . $this->action_name ) ) {
			return;
		}

		// If no action specified, return.
		if ( ! isset( $_REQUEST['convertkit-action'] ) ) {
			return;
		}

		// Fetch action and post ID.
		$action  = sanitize_text_field( $_REQUEST['convertkit-action'] );
		$post_id = absint( $_REQUEST['id'] );

		// Bail if the action isn't for exporting a post.
		if ( $action !== $this->action_name ) {
			return;
		}

		// Export Post to a draft ConvertKit Broadcast.
		$result = $this->export_post_to_broadcast( $post_id );

		// If an error occured, display an error message.
		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) );
		}

		// Store Broadcast ID for success notice.
		$this->broadcast_id = $result['id'];

		// Display a success notice with a link to editing the Broadcast on ConvertKit.
		add_action( 'admin_notices', array( $this, 'output_success_notice' ) );

	}

	/**
	 * Outputs a success notice in the WordPress Administration interface that the Post was
	 * successfully created in ConvertKit as a Broadcast, with a link to edit in ConvertKit.
	 *
	 * @since   2.4.0
	 */
	public function output_success_notice() {

		// Bail if no Broadcast ID specified.
		if ( ! $this->broadcast_id ) {
			return;
		}

		// Output success notice.
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					'%s <a href="%s" target="_blank">%s</a> %s',
					esc_html__( 'Successfully created Kit Broadcast from Post.', 'convertkit' ),
					esc_url( convertkit_get_edit_broadcast_url( $this->broadcast_id ) ),
					esc_html__( 'Click here', 'convertkit' ),
					esc_html__( 'to edit and send the broadcast in Kit.', 'convertkit' )
				);
				?>
			</p>
		</div>
		<?php

	}

	/**
	 * Adds a 'Create ConvertKit Broadcast' action below the Post Title in WP_List_Table classes.
	 *
	 * @since   2.4.0
	 *
	 * @param   array   $actions    Row Actions.
	 * @param   WP_Post $post       WordPress Post.
	 * @return  array                   Row Actions
	 */
	public function add_row_action( $actions, $post ) {

		// Build URL.
		$url = add_query_arg(
			array(
				'post_type'         => $post->post_type,
				'convertkit-action' => 'broadcast-export',
				'id'                => $post->ID,
				'nonce'             => wp_create_nonce( 'action-convertkit-broadcast-export' ),
			),
			'edit.php'
		);

		// Add action.
		$actions['convertkit_broadcast_export'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Create as Kit Broadcast', 'convertkit' ) . '</a>';

		// Return.
		return $actions;

	}

	/**
	 * Creates a draft Broadcast in ConvertKit from the given WordPress Post ID,
	 * storing the Broadcast ID against in the Post's meta.
	 *
	 * @since   2.4.0
	 *
	 * @param   int $post_id    Post ID.
	 * @return  WP_Error|array
	 */
	public function export_post_to_broadcast( $post_id ) {

		// Get Post.
		$post = get_post( $post_id );

		// Return an error if the Post could not be fetched.
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_broadcasts_exporter_export_post_to_broadcast',
				sprintf(
					/* translators: WordPress Post ID */
					esc_html__( 'Could not fetch Post ID %s.', 'convertkit' ),
					$post_id
				)
			);
		}

		// Fetch post's content by running it through the_content filter.
		// This will convert e.g. page builders and blocks to HTML.
		$content = apply_filters( 'the_content', $post->post_content );

		// Remove HTML tags that are not supported in ConvertKit Broadcasts.
		$content = WP_ConvertKit()->get_class( 'broadcasts_importer' )->get_permitted_html( $content, $this->broadcasts_settings->no_styles() );

		// Initialize the API.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$this->settings->get_access_token(),
			$this->settings->get_refresh_token(),
			$this->settings->debug_enabled(),
			'settings'
		);

		// Create draft Broadcast in ConvertKit.
		$result = $api->create_broadcast(
			$post->post_title,
			$content,
			$post->post_excerpt
		);

		// If an error occured, return it now.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Store the Broadcast ID against the WordPress Post.
		update_post_meta( $post->ID, '_convertkit_broadcast_export_id', $result['broadcast']['id'] );

		// Return result.
		return $result['broadcast'];

	}

}
