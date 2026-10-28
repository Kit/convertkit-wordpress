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
	 * Whether an existing Broadcast was updated, instead of a new Broadcast being created.
	 *
	 * @since 3.4.8
	 *
	 * @var   bool
	 */
	private $broadcast_updated = false;

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
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce'] ) ), 'action-convertkit-' . $this->action_name ) ) {
			return;
		}

		// If no action or ID specified, return.
		if ( ! isset( $_REQUEST['convertkit-action'] ) ) {
			return;
		}
		if ( ! isset( $_REQUEST['id'] ) ) {
			return;
		}

		// Fetch action and post ID.
		$action  = sanitize_text_field( wp_unslash( $_REQUEST['convertkit-action'] ) );
		$post_id = absint( $_REQUEST['id'] );

		// Bail if the action isn't for exporting a post.
		if ( $action !== $this->action_name ) {
			return;
		}

		// Bail if the current user cannot edit the Post being exported.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to export this Post.', 'convertkit' ) );
		}

		// Export Post to a draft ConvertKit Broadcast.
		$result = $this->export_post_to_broadcast( $post_id );

		// If an error occurred, display an error message.
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
					( $this->broadcast_updated ? esc_html__( 'Successfully updated Kit Broadcast from Post.', 'convertkit' ) : esc_html__( 'Successfully created Kit Broadcast from Post.', 'convertkit' ) ),
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

		// If the Post was previously exported, add actions to update and open its Broadcast.
		$broadcast_id = $this->get_post_broadcast_id( $post->ID );
		if ( $broadcast_id ) {
			$actions['convertkit_broadcast_export'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Update Kit Broadcast', 'convertkit' ) . '</a>';
			$actions['convertkit_broadcast_open']   = '<a href="' . esc_url( convertkit_get_edit_broadcast_url( $broadcast_id ) ) . '" target="_blank">' . esc_html__( 'Open in Kit', 'convertkit' ) . '</a>';
			return $actions;
		}

		// Add action.
		$actions['convertkit_broadcast_export'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Create as Kit Broadcast', 'convertkit' ) . '</a>';

		// Return.
		return $actions;

	}

	/**
	 * Creates a draft Broadcast in ConvertKit from the given WordPress Post ID,
	 * storing the Broadcast ID against in the Post's meta.
	 *
	 * If the Post was previously exported, its draft Broadcast is updated instead.
	 * An error is returned if that Broadcast has been sent or scheduled.
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

		// Remove responsive image attributes, which email clients don't support.
		$content = $this->remove_responsive_image_attributes( $content );

		// Convert relative URLs to absolute URLs, as emails have no site context.
		$content = links_add_base_url( $content, get_permalink( $post ) );

		// Initialize the API.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$this->settings->get_access_token(),
			$this->settings->get_refresh_token(),
			$this->settings->debug_enabled(),
			'settings'
		);

		// Get the Post's existing draft Broadcast ID, if it was previously exported.
		$broadcast_id = $this->get_existing_draft_broadcast_id( $api, $post->ID );

		// If an error occurred, or the Broadcast has been sent or scheduled, return it now.
		if ( is_wp_error( $broadcast_id ) ) {
			return $broadcast_id;
		}

		// Define the Broadcast's parameters.
		$args = array(
			$post->post_title,
			$content,
			$post->post_excerpt,
			$this->broadcasts_settings->export_public(),
			$this->get_published_at( $post ),
			null,
			'',
			'',
			'',
			'',
			'',
			$this->get_subscriber_filter(),
		);

		// Update the existing draft Broadcast, or create a new draft Broadcast, in Kit.
		$this->broadcast_updated = (bool) $broadcast_id;
		if ( $broadcast_id ) {
			$result = $api->update_broadcast( $broadcast_id, ...$args );
		} else {
			$result = $api->create_broadcast( ...$args );
		}

		// If an error occurred, return it now.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Store the Broadcast ID against the WordPress Post.
		update_post_meta( $post->ID, '_convertkit_broadcast_export_id', $result['broadcast']['id'] );

		// Return result.
		return $result['broadcast'];

	}

	/**
	 * Returns the Broadcast ID the given Post was previously exported to, if any.
	 *
	 * @since   3.4.8
	 *
	 * @param   int $post_id    Post ID.
	 * @return  int             Broadcast ID (0 = not exported)
	 */
	public function get_post_broadcast_id( $post_id ) {

		return absint( get_post_meta( $post_id, '_convertkit_broadcast_export_id', true ) );

	}

	/**
	 * Returns the Broadcast ID the given Post was previously exported to, if the Broadcast
	 * is still a draft in Kit.
	 *
	 * @since   3.4.8
	 *
	 * @param   ConvertKit_API_V4 $api        API.
	 * @param   int               $post_id    Post ID.
	 * @return  WP_Error|int                  Broadcast ID (0 = not exported, or deleted in Kit)
	 */
	private function get_existing_draft_broadcast_id( $api, $post_id ) {

		// Bail if the Post hasn't been exported.
		$broadcast_id = $this->get_post_broadcast_id( $post_id );
		if ( ! $broadcast_id ) {
			return 0;
		}

		// Fetch the Broadcast.
		$result = $api->get_broadcast( $broadcast_id );

		// If the Broadcast was deleted in Kit, a new Broadcast can be created.
		if ( is_wp_error( $result ) && $result->get_error_data() === 404 ) {
			return 0;
		}

		// If an error occurred, return it now.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return an error if the Broadcast has been sent or scheduled.
		$is_draft = ( isset( $result['broadcast']['status'] ) ? $result['broadcast']['status'] === 'draft' : empty( $result['broadcast']['send_at'] ) );
		if ( ! $is_draft ) {
			return new WP_Error(
				'convertkit_broadcasts_exporter_export_post_to_broadcast',
				__( 'This Post\'s Kit Broadcast has already been sent or scheduled, so it can\'t be updated.', 'convertkit' )
			);
		}

		return $broadcast_id;

	}

	/**
	 * Returns the Post's published date and time, if the Broadcast is public and the Post is published.
	 *
	 * @since   3.4.8
	 *
	 * @param   WP_Post $post   WordPress Post.
	 * @return  null|DateTime
	 */
	private function get_published_at( $post ) {

		// Bail if the Broadcast isn't public.
		if ( ! $this->broadcasts_settings->export_public() ) {
			return null;
		}

		// Bail if the Post hasn't been published.
		if ( $post->post_date_gmt === '0000-00-00 00:00:00' ) {
			return null;
		}

		return new DateTime( $post->post_date_gmt, new DateTimeZone( 'UTC' ) );

	}

	/**
	 * Returns the subscriber filter for the Broadcast, based on the Send To setting.
	 *
	 * @since   3.4.8
	 *
	 * @return  array
	 */
	private function get_subscriber_filter() {

		// Send to all subscribers, using an empty filter group so updating a Broadcast clears any existing filter.
		$all_subscribers = array(
			array(
				'all' => array(),
			),
		);

		// Send to all subscribers if no Tag or Segment is specified.
		if ( ! $this->broadcasts_settings->export_send_to() ) {
			return $all_subscribers;
		}

		// Get the resource type and ID e.g. tag_123.
		list( $type, $id ) = array_pad( explode( '_', $this->broadcasts_settings->export_send_to(), 2 ), 2, 0 );

		// Send to all subscribers if the setting is invalid.
		if ( ! in_array( $type, array( 'tag', 'segment' ), true ) || ! absint( $id ) ) {
			return $all_subscribers;
		}

		return array(
			array(
				'all' => array(
					array(
						'type' => $type,
						'ids'  => array( absint( $id ) ),
					),
				),
			),
		);

	}

	/**
	 * Removes `srcset` and `sizes` attributes from images in the given content.
	 *
	 * @since   3.4.8
	 *
	 * @param   string $content    HTML Content.
	 * @return  string             HTML Content
	 */
	private function remove_responsive_image_attributes( $content ) {

		// Bail if no images have responsive image attributes.
		if ( strpos( $content, 'srcset=' ) === false && strpos( $content, 'sizes=' ) === false ) {
			return $content;
		}

		// Load the content into the parser.
		$parser = new ConvertKit_HTML_Parser( $content );

		// Remove attributes.
		foreach ( $parser->xpath->query( '//img[@srcset or @sizes]' ) as $node ) {
			$node->removeAttribute( 'srcset' ); // @phpstan-ignore-line
			$node->removeAttribute( 'sizes' ); // @phpstan-ignore-line
		}

		return $parser->get_body_html();

	}

}
