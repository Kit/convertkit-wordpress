<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to registering Custom Post Types in WordPress.
 *
 * @since   2.3.5
 */
class CustomPostType extends \Codeception\Module
{
	/**
	 * Registers public and private Custom Post Types in WordPress
	 *
	 * @since   2.7.6
	 *
	 * @param   EndToEndTester $I End To End Tester.
	 */
	public function registerCustomPostTypes($I)
	{
		// Prevent CPT UI Plugin from showing its own welcome screen on activation.
		$I->haveOptionInDatabase('cptui_new_install', 'false');

		// Activate CPT UI Plugin.
		$I->activateThirdPartyPlugin($I, 'custom-post-type-ui');

		// Programmatically define the Custom Post Types.
		$I->haveOptionInDatabase(
			'cptui_post_types',
			[
				'article' => array(
					'name'                  => 'article',
					'label'                 => 'Article',
					'singular_label'        => 'Articles',
					'description'           => '',
					'public'                => true,
					'publicly_queryable'    => true,
					'show_ui'               => true,
					'show_in_nav_menus'     => true,
					'delete_with_user'      => false,
					'show_in_rest'          => true,
					'rest_base'             => '',
					'rest_controller_class' => '',
					'rest_namespace'        => '',
					'has_archive'           => false,
					'has_archive_string'    => '',
					'exclude_from_search'   => false,
					'capability_type'       => 'post',
					'hierarchical'          => false,
					'can_export'            => false,
					'rewrite'               => true,
					'rewrite_slug'          => '',
					'rewrite_withfront'     => true,
					'query_var'             => true,
					'query_var_slug'        => '',
					'menu_position'         => '',
					'show_in_menu'          => true,
					'show_in_menu_string'   => '',
					'menu_icon'             => null,
					'register_meta_box_cb'  => null,
					'supports'              => array( 'title', 'editor', 'thumbnail' ),
					'taxonomies'            => array(),
					'labels'                => array(
						'menu_name'                => '',
						'all_items'                => '',
						'add_new'                  => '',
						'add_new_item'             => '',
						'edit_item'                => '',
						'new_item'                 => '',
						'view_item'                => '',
						'view_items'               => '',
						'search_items'             => '',
						'not_found'                => '',
						'not_found_in_trash'       => '',
						'parent_item_colon'        => '',
						'featured_image'           => '',
						'set_featured_image'       => '',
						'remove_featured_image'    => '',
						'use_featured_image'       => '',
						'archives'                 => '',
						'insert_into_item'         => '',
						'uploaded_to_this_item'    => '',
						'filter_items_list'        => '',
						'items_list_navigation'    => '',
						'items_list'               => '',
						'attributes'               => '',
						'name_admin_bar'           => '',
						'item_published'           => '',
						'item_published_privately' => '',
						'item_reverted_to_draft'   => '',
						'item_trashed'             => '',
						'item_scheduled'           => '',
						'item_updated'             => '',
					),
					'custom_supports'       => '',
					'enter_title_here'      => '',
				),
				'private' => array(
					'name'                  => 'private',
					'label'                 => 'Private',
					'singular_label'        => 'Private',
					'description'           => '',
					'public'                => false,
					'publicly_queryable'    => true,
					'show_ui'               => true,
					'show_in_nav_menus'     => true,
					'delete_with_user'      => false,
					'show_in_rest'          => true,
					'rest_base'             => '',
					'rest_controller_class' => '',
					'rest_namespace'        => '',
					'has_archive'           => false,
					'has_archive_string'    => '',
					'exclude_from_search'   => false,
					'capability_type'       => 'post',
					'hierarchical'          => false,
					'can_export'            => false,
					'rewrite'               => true,
					'rewrite_slug'          => '',
					'rewrite_withfront'     => true,
					'query_var'             => true,
					'query_var_slug'        => '',
					'menu_position'         => '',
					'show_in_menu'          => true,
					'show_in_menu_string'   => '',
					'menu_icon'             => null,
					'register_meta_box_cb'  => null,
					'supports'              => array( 'title', 'editor', 'thumbnail' ),
					'taxonomies'            => array(),
					'labels'                => array(
						'menu_name'                => '',
						'all_items'                => '',
						'add_new'                  => '',
						'add_new_item'             => '',
						'edit_item'                => '',
						'new_item'                 => '',
						'view_item'                => '',
						'view_items'               => '',
						'search_items'             => '',
						'not_found'                => '',
						'not_found_in_trash'       => '',
						'parent_item_colon'        => '',
						'featured_image'           => '',
						'set_featured_image'       => '',
						'remove_featured_image'    => '',
						'use_featured_image'       => '',
						'archives'                 => '',
						'insert_into_item'         => '',
						'uploaded_to_this_item'    => '',
						'filter_items_list'        => '',
						'items_list_navigation'    => '',
						'items_list'               => '',
						'attributes'               => '',
						'name_admin_bar'           => '',
						'item_published'           => '',
						'item_published_privately' => '',
						'item_reverted_to_draft'   => '',
						'item_trashed'             => '',
						'item_scheduled'           => '',
						'item_updated'             => '',
					),
					'custom_supports'       => '',
					'enter_title_here'      => '',
				),
			]
		);

		// Navigate to Settings > General to flush Permalinks.
		$I->amOnAdminPage('options-permalink.php');
		$I->waitForElementVisible('body.options-permalink-php');
	}

	/**
	 * Unregisters existing Custom Post Types in WordPress
	 *
	 * @since   2.7.6
	 *
	 * @param   EndToEndTester $I EndToEnd Tester.
	 */
	public function unregisterCustomPostTypes($I)
	{
		$I->dontHaveOptionInDatabase('cptui_post_types');
		$I->deactivateThirdPartyPlugin($I, 'custom-post-type-ui');
	}
}
