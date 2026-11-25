<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests that only users who can publish a Post can enable its pre-publish actions.
 *
 * @since   3.5.3
 */
class PrePublishActionTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * The pre-publish action's meta key.
	 *
	 * @since   3.5.3
	 *
	 * @var     string
	 */
	private $meta_key = '_convertkit_action_broadcast_export';

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.5.3
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Enable the Broadcast export pre-publish action.
		$settings = new \ConvertKit_Settings_Broadcasts();
		$settings->save([ 'enabled_export' => 'on' ]);
		$action = new \ConvertKit_Pre_Publish_Action_Broadcast_Export();
		$action->register_meta_key();
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.5.3
	 */
	public function tearDown(): void
	{
		unset($_POST['wp-convertkit-pre-publish-actions-nonce'], $_POST[ $this->meta_key ]);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the pre-publish action is only registered for users who can publish Posts.
	 *
	 * @since   3.5.3
	 */
	public function testActionRegisteredForUsersWhoCanPublish()
	{
		$this->setUser('contributor');
		$this->assertArrayNotHasKey('broadcast_export', convertkit_get_pre_publish_actions());

		$this->setUser('author');
		$this->assertArrayHasKey('broadcast_export', convertkit_get_pre_publish_actions());
	}

	/**
	 * Test that a Contributor can't enable the pre-publish action on their own Post using the REST API.
	 *
	 * @since   3.5.3
	 */
	public function testContributorCannotEnableActionUsingRESTAPI()
	{
		$post_id  = $this->createDraftAs('contributor');
		$response = $this->updateMetaUsingRESTAPI($post_id);

		// Confirm the Post saved, but the action wasn't enabled, as the block editor sends all registered meta.
		$this->assertEquals(200, $response->get_status());
		$this->assertEmpty(get_post_meta($post_id, $this->meta_key, true));
	}

	/**
	 * Test that an Author can enable the pre-publish action on their own Post using the REST API.
	 *
	 * @since   3.5.3
	 */
	public function testAuthorCanEnableActionUsingRESTAPI()
	{
		$post_id  = $this->createDraftAs('author');
		$response = $this->updateMetaUsingRESTAPI($post_id);

		$this->assertEquals(200, $response->get_status());
		$this->assertTrue( (bool) get_post_meta($post_id, $this->meta_key, true));
	}

	/**
	 * Test that a Contributor can't enable the pre-publish action on their own Post using the Classic Editor.
	 *
	 * @since   3.5.3
	 */
	public function testContributorCannotEnableActionUsingClassicEditor()
	{
		$post_id = $this->createDraftAs('contributor');
		$this->saveUsingClassicEditor($post_id);

		$this->assertEmpty(get_post_meta($post_id, $this->meta_key, true));
	}

	/**
	 * Test that an Author can enable the pre-publish action on their own Post using the Classic Editor.
	 *
	 * @since   3.5.3
	 */
	public function testAuthorCanEnableActionUsingClassicEditor()
	{
		$post_id = $this->createDraftAs('author');
		$this->saveUsingClassicEditor($post_id);

		$this->assertTrue( (bool) get_post_meta($post_id, $this->meta_key, true));
	}

	/**
	 * Logs in as a new user with the given role.
	 *
	 * @since   3.5.3
	 *
	 * @param   string $role   User role.
	 * @return  int            User ID.
	 */
	private function setUser($role)
	{
		$user_id = static::factory()->user->create([ 'role' => $role ]);
		wp_set_current_user($user_id);
		return $user_id;
	}

	/**
	 * Creates a draft Post authored by a new user with the given role, and logs in as that user.
	 *
	 * @since   3.5.3
	 *
	 * @param   string $role   User role.
	 * @return  int            Post ID.
	 */
	private function createDraftAs($role)
	{
		$user_id = $this->setUser($role);
		return static::factory()->post->create(
			[
				'post_author' => $user_id,
				'post_status' => 'draft',
			]
		);
	}

	/**
	 * Enables the pre-publish action on the given Post using the REST API.
	 *
	 * @since   3.5.3
	 *
	 * @param   int $post_id   Post ID.
	 * @return  \WP_REST_Response
	 */
	private function updateMetaUsingRESTAPI($post_id)
	{
		$request = new \WP_REST_Request('POST', '/wp/v2/posts/' . $post_id);
		$request->set_body_params([ 'meta' => [ $this->meta_key => true ] ]);
		return rest_do_request($request);
	}

	/**
	 * Enables the pre-publish action on the given Post, as if saved using the Classic Editor.
	 *
	 * @since   3.5.3
	 *
	 * @param   int $post_id   Post ID.
	 */
	private function saveUsingClassicEditor($post_id)
	{
		$_POST['wp-convertkit-pre-publish-actions-nonce'] = wp_create_nonce('wp-convertkit-pre-publish-actions');
		$_POST[ $this->meta_key ]                         = '1';

		$action = new \ConvertKit_Pre_Publish_Action_Broadcast_Export();
		$action->save_post_meta($post_id);
	}
}
