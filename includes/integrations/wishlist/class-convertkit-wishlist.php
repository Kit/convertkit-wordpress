<?php
/**
 * ConvertKit Wishlist class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Wishlist Integration
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Wishlist {

	/**
	 * Constructor. Registers required hooks with WishList Member.
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		// When a user has levels added or registers, perform actions on ConvertKit.
		add_action( 'wishlistmember_add_user_levels', array( $this, 'add_user_levels' ), 10, 2 );

		// When a user has levels removed check, perform actions on ConvertKit.
		add_action( 'wishlistmember_remove_user_levels', array( $this, 'remove_user_levels' ), 10, 2 );

	}

	/**
	 * When a user has levels added or registers, subscribe them to ConvertKit
	 * and optionally assign a Form, Tag or Sequence, based on the WishList Member Level
	 * setting.
	 *
	 * @since   1.9.6
	 *
	 * @param   string $member_id  ID for member that has just had levels added.
	 * @param   array  $levels     Levels to which member was added.
	 */
	public function add_user_levels( $member_id, $levels ) {

		$this->manage_member( $member_id, $levels, 'add' );

	}

	/**
	 * When a user has levels removed, unsubscribe or subscribe them to ConvertKit
	 * and optionally assign a Form, Tag or Sequence, based on the WishList Member Level
	 * setting.
	 *
	 * @since   1.9.6
	 *
	 * @param   string $member_id  ID for member that has just had levels added.
	 * @param   array  $levels     Levels to which member was added.
	 */
	public function remove_user_levels( $member_id, $levels ) {

		$this->manage_member( $member_id, $levels, 'remove' );

	}

	/**
	 * When a user has levels added or registers, subscribe them to a ConvertKit Form
	 * if the given WishList Member Level is mapped to a ConvertKit Form.
	 *
	 * @since   1.9.6
	 *
	 * @param   string $member_id  ID for member that has just had levels added.
	 * @param   array  $levels     Levels to which member was added.
	 * @param   string $wlm_action  WishList Member action (add,remove).
	 */
	private function manage_member( $member_id, $levels, $wlm_action = 'add' ) {

		// Get WishList Member.
		$member = $this->get_member( $member_id );

		// Bail if no Member was returned.
		if ( ! $member ) {
			return;
		}

		// Bail if the API hasn't been configured.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			return;
		}

		// Initialize the API.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$settings->get_access_token(),
			$settings->get_refresh_token(),
			$settings->debug_enabled(),
			'wishlist_member'
		);

		// Check for temp email.
		if ( preg_match( '/temp_[a-f0-9]{32}/', $member['user_email'] ) ) {
			$email = $member['wlm_origemail'];
		} else {
			$email = $member['user_email'];
		}

		// Extract the first name.
		$first_name = '';
		if ( isset( $member['display_name'] ) && ! empty( $member['display_name'] ) ) {
			$name       = explode( ' ', $member['display_name'] );
			$first_name = $name[0];
		}

		// Initialize Wishlist Settings class.
		$wlm_settings = new ConvertKit_Wishlist_Settings();

		// Iterate through the member's levels.
		foreach ( $levels as $wlm_level_id ) {
			// Fetch action setting.
			switch ( $wlm_action ) {
				case 'add':
					$setting = $wlm_settings->get_convertkit_add_setting_by_wishlist_member_level_id( $wlm_level_id );
					break;

				case 'remove':
					$setting = $wlm_settings->get_convertkit_remove_setting_by_wishlist_member_level_id( $wlm_level_id );
					break;

				default:
					$setting = false;
					break;
			}

			// If no setting / action exists, skip this level.
			if ( ! $setting ) {
				continue;
			}

			// If the resource setting is 'unsubscribe', just unsubscribe the member.
			if ( $setting === 'unsubscribe' ) {
				// Get subscriber ID.
				$subscriber_id = $api->get_subscriber_id( $email );

				// Bail if an error occurred e.g. no subscriber exists.
				if ( is_wp_error( $subscriber_id ) ) {
					return $subscriber_id;
				}

				// Unsubscribe.
				$api->unsubscribe( $subscriber_id );
				continue;
			}

			// If the resource setting is 'subscribe', create the subscriber in an active state and don't assign to a resource.
			if ( $setting === 'subscribe' ) {
				$api->create_subscriber( $email, $first_name );
				continue;
			}

			// Extract resource type and ID from the setting.
			list( $resource_type, $resource_id ) = explode( ':', $setting );

			// Cast ID.
			$resource_id = absint( $resource_id );

			// Add the subscriber to the resource type (form, tag etc).
			switch ( $resource_type ) {

				/**
				 * Form
				 */
				case 'form':
					// Subscribe with inactive state.
					$subscriber = $api->create_subscriber( $email, $first_name, 'inactive' );

					// If an error occurred, don't attempt to add the subscriber to the Form, as it won't work.
					if ( is_wp_error( $subscriber ) ) {
						break;
					}

					// For Legacy Forms, a different endpoint is used.
					$forms = new ConvertKit_Resource_Forms();
					if ( $forms->is_legacy( $resource_id ) ) {
						$api->add_subscriber_to_legacy_form( $resource_id, $subscriber['subscriber']['id'] );
						break;
					}

					// Add subscriber to form.
					$api->add_subscriber_to_form( $resource_id, $subscriber['subscriber']['id'] );
					break;

				/**
				 * Sequence
				 */
				case 'sequence':
					// Subscribe.
					$subscriber = $api->create_subscriber( $email, $first_name );

					// If an error occurred, don't attempt to add the subscriber to the Form, as it won't work.
					if ( is_wp_error( $subscriber ) ) {
						break;
					}

					// Add subscriber to sequence.
					$api->add_subscriber_to_sequence( $resource_id, $subscriber['subscriber']['id'] );
					break;

				/**
				 * Tag
				 */
				case 'tag':
					// Subscribe with inactive state.
					$subscriber = $api->create_subscriber( $email, $first_name );

					// If an error occurred, don't attempt to add the subscriber to the Form, as it won't work.
					if ( is_wp_error( $subscriber ) ) {
						break;
					}

					// Add subscriber to tag.
					$api->tag_subscriber( $resource_id, $subscriber['subscriber']['id'] );
					break;

			}
		}

	}

	/**
	 * Gets a WLM member using the wlmapi functions
	 *
	 * @param  string $id The member id.
	 * @return bool|array
	 */
	public function get_member( $id ) {

		// Get WishList Member.
		$wlm_get_member = wlmapi_get_member( $id );

		// Bail if an error occurred.
		if ( 0 === $wlm_get_member['success'] ) {
			return false;
		}

		// Return user's information.
		return $wlm_get_member['member'][0]['UserInfo'];

	}

}

// Bootstrap.
add_action(
	'convertkit_initialize_global',
	function () {

		new ConvertKit_Wishlist();

	}
);
