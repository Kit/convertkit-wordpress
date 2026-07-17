<?php
/**
 * ConvertKit Restrict Content Cache class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Maintains a cached map of Post IDs to their relative permalinks for every
 * Post/Page/Custom Post Type with Member Content (Restrict Content)
 * functionality enabled.
 *
 * @since   3.3.6
 */
class ConvertKit_Restrict_Content_Cache {

	/**
	 * The option name that stores the cache.
	 *
	 * @since   3.3.6
	 *
	 * @var     string
	 */
	const OPTION_NAME = '_wp_convertkit_restrict_content_posts';

	/**
	 * Constructor. Registers hooks that keep the cache in sync with post
	 * meta changes, post deletions, post status transitions, and permalink
	 * structure changes.
	 *
	 * @since   3.3.6
	 */
	public function __construct() {

		// Post meta changes (covers ConvertKit_Post::save() and direct
		// update_post_meta() calls).
		add_action( 'added_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
		add_action( 'updated_post_meta', array( $this, 'on_meta_change' ), 10, 4 );
		add_action( 'deleted_post_meta', array( $this, 'on_meta_delete' ), 10, 4 );

		// Post deletion / trashing / restoring.
		add_action( 'before_delete_post', array( $this, 'on_post_delete' ) );
		add_action( 'wp_trash_post', array( $this, 'on_post_delete' ) );
		add_action( 'untrash_post', array( $this, 'on_post_untrash' ) );

		// Post status transitions (draft <-> publish etc).
		add_action( 'transition_post_status', array( $this, 'on_post_status_transition' ), 10, 3 );

		// Post updated: refresh cached URL if the permalink for a cached
		// post has changed (e.g. slug edit, parent change).
		add_action( 'post_updated', array( $this, 'on_post_updated' ), 10, 3 );

		// Rebuild the cache when the permalink structure or site URL changes.
		add_action( 'update_option_permalink_structure', array( $this, 'rebuild' ) );
		add_action( 'update_option_siteurl', array( $this, 'rebuild' ) );
		add_action( 'update_option_home', array( $this, 'rebuild' ) );

	}

	/**
	 * Returns the cache as an associative array of post_id => relative_url.
	 *
	 * @since   3.3.6
	 *
	 * @return  array   Associative array of post_id => relative_url.
	 */
	public function get() {

		// Read cache from the option.
		$cache = get_option( self::OPTION_NAME );

		// If no cache exists, rebuild it.
		if ( $cache === false ) {
			$cache = $this->rebuild();
		}

		return is_array( $cache ) ? $cache : array();

	}

	/**
	 * Returns post IDs stored in the cache.
	 *
	 * @since   3.3.6
	 *
	 * @return  array   Array of post IDs.
	 */
	public function get_post_ids() {

		return array_map( 'intval', array_keys( $this->get() ) );

	}

	/**
	 * Adds or updates a Post ID and relative URL in the cache with its current relative
	 * permalink. Only Posts with a `publish` post_status are stored.
	 *
	 * @since   3.4.0
	 *
	 * @param   int $post_id    Post ID.
	 * @return  bool            Whether the option was updated.
	 */
	public function add( $post_id ) {

		// Validate post ID.
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}

		// If the Post is not published, remove it from the cache.
		if ( get_post_status( $post_id ) !== 'publish' ) {
			return $this->remove( $post_id );
		}

		// Get the Post's relative permalink.
		$permalink = get_permalink( $post_id );
		$relative  = wp_make_link_relative( $permalink );

		// Get the cache.
		$cache = $this->get();

		// Skip write if the Post ID already exists in the cache and the relative permalink is the same.
		if ( isset( $cache[ $post_id ] ) && $cache[ $post_id ] === $relative ) {
			return false;
		}

		// Update the cache.
		$cache[ $post_id ] = $relative;
		return update_option( self::OPTION_NAME, $cache );

	}

	/**
	 * Removes a Post ID and relative URL from the cache.
	 *
	 * @since   3.3.6
	 *
	 * @param   int $post_id    Post ID.
	 * @return  bool
	 */
	public function remove( $post_id ) {

		// Validate post ID.
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}

		// Get the cache.
		$cache = $this->get();

		// If the Post ID is not in the cache, don't update anything.
		if ( ! array_key_exists( $post_id, $cache ) ) {
			return false;
		}

		// Remove the Post ID and relative URL from the cache.
		unset( $cache[ $post_id ] );
		return update_option( self::OPTION_NAME, $cache );

	}

	/**
	 * Rebuilds the cache from scratch by querying the database for every
	 * post whose Kit meta contains a non-empty restrict_content setting,
	 * then validating each via ConvertKit_Post.
	 *
	 * This is a potentially expensive operation, so it should be avoided if possible.
	 *
	 * @since   3.3.6
	 *
	 * @return  array   Cache as an associative array of post_id => relative_url.
	 */
	public function rebuild() {

		global $wpdb;

		// Serialized array values for `restrict_content` are of the form
		// s:N:"..." where N is the string length. Empty values are s:0:"".
		// Matching s:_ (a single digit followed by ":) narrows to values
		// with length 1..9 which is enough for form_/tag_/product_ IDs.
		$post_ids = $wpdb->get_col(
			"
			SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wp_convertkit_post_meta'
			AND meta_value LIKE '%\"restrict_content\";s:%'
			AND meta_value NOT LIKE '%\"restrict_content\";s:0:%'
			"
		);

		$cache = array();

		// Iterate over the post IDs.
		foreach ( $post_ids as $post_id ) {
			// Validate post ID.
			$post_id = absint( $post_id );
			if ( ! $post_id ) {
				continue;
			}

			// Skip if the Post is not published.
			if ( get_post_status( $post_id ) !== 'publish' ) {
				continue;
			}

			// Check if the Post has restrict_content enabled.
			$post_settings = new ConvertKit_Post( $post_id );
			if ( ! $post_settings->restrict_content_enabled() ) {
				continue;
			}

			// Get the Post's relative permalink.
			$permalink         = get_permalink( $post_id );
			$cache[ $post_id ] = wp_make_link_relative( $permalink );
		}

		// Update the cache.
		update_option( self::OPTION_NAME, $cache );

		return $cache;

	}

	/**
	 * Hook: fires when post meta is added or updated. Adds/removes the
	 * Post ID from the cache based on whether the new value has
	 * restrict_content enabled.
	 *
	 * @since   3.3.6
	 *
	 * @param   int    $meta_id     Meta ID (unused).
	 * @param   int    $post_id     Post ID.
	 * @param   string $meta_key    Meta key.
	 * @param   mixed  $meta_value  Meta value.
	 */
	public function on_meta_change( $meta_id, $post_id, $meta_key, $meta_value ) {

		// Check if the meta key is the ConvertKit post meta key.
		if ( $meta_key !== '_wp_convertkit_post_meta' ) {
			return;
		}

		// WP core passes $meta_value as the unserialized value for these
		// hooks. Fall back gracefully if it's a string (some plugins may
		// bypass core's unserialize).
		if ( is_string( $meta_value ) ) {
			$meta_value = maybe_unserialize( $meta_value );
		}

		if ( ! is_array( $meta_value ) || empty( $meta_value['restrict_content'] ) ) {
			$this->remove( $post_id );
			return;
		}

		$this->add( $post_id );

	}

	/**
	 * Hook: fires when post meta is deleted. Removes the Post ID from the
	 * cache if the Kit meta was deleted.
	 *
	 * @since   3.3.6
	 *
	 * @param   array  $meta_ids    Meta IDs (unused).
	 * @param   int    $post_id     Post ID.
	 * @param   string $meta_key    Meta key.
	 * @param   mixed  $meta_value  Meta value (unused).
	 */
	public function on_meta_delete( $meta_ids, $post_id, $meta_key, $meta_value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Check if the meta key is the ConvertKit post meta key.
		if ( $meta_key !== '_wp_convertkit_post_meta' ) {
			return;
		}

		// Remove the Post ID from the cache.
		$this->remove( $post_id );

	}

	/**
	 * Hook: fires when a Post is deleted or trashed. Removes the Post ID
	 * from the cache.
	 *
	 * @since   3.3.6
	 *
	 * @param   int $post_id    Post ID.
	 */
	public function on_post_delete( $post_id ) {

		// Remove the Post ID from the cache.
		$this->remove( $post_id );

	}

	/**
	 * Hook: fires when a Post is restored from trash. Re-adds the Post ID
	 * to the cache if the Post has restrict_content enabled.
	 *
	 * @since   3.4.0
	 *
	 * @param   int $post_id    Post ID.
	 */
	public function on_post_untrash( $post_id ) {

		$post_settings = new ConvertKit_Post( $post_id );

		// If the Post has restrict_content enabled, add it to the cache.
		if ( $post_settings->restrict_content_enabled() ) {
			// Add the Post ID to the cache.
			$this->add( $post_id );
		}

	}

	/**
	 * Hook: fires on any post status transition. Adds/removes the Post ID
	 * from the cache depending on whether the Post is transitioning to or
	 * from `publish`.
	 *
	 * @since   3.3.6
	 *
	 * @param   string  $new_status New post status.
	 * @param   string  $old_status Old post status.
	 * @param   WP_Post $post       Post object.
	 */
	public function on_post_status_transition( $new_status, $old_status, $post ) {

		// Skip if the post status is the same.
		if ( $new_status === $old_status ) {
			return;
		}

		// Check if the Post has restrict_content enabled.
		$post_settings = new ConvertKit_Post( $post->ID );
		if ( ! $post_settings->restrict_content_enabled() ) {
			return;
		}

		// Add or remove the Post ID from the cache based on the new status.
		if ( $new_status === 'publish' ) {
			$this->add( $post->ID );
		} elseif ( $old_status === 'publish' ) {
			$this->remove( $post->ID );
		}

	}

	/**
	 * Hook: fires when a Post is updated. If the Post is in the cache,
	 * refresh its cached URL in case the permalink changed (e.g. slug edit,
	 * parent change).
	 *
	 * @since   3.3.6
	 *
	 * @param   int     $post_id      Post ID.
	 * @param   WP_Post $post_after   Post object after update (unused).
	 * @param   WP_Post $post_before  Post object before update (unused).
	 */
	public function on_post_updated( $post_id, $post_after, $post_before ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Get the cache.
		$cache = $this->get();

		// Skip if the Post ID is not in the cache.
		if ( ! array_key_exists( $post_id, $cache ) ) {
			return;
		}

		// Update the cache.
		$this->add( $post_id );

	}

}
