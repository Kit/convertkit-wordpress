<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Kit Forms integration with WishList Member.
 *
 * @since   1.9.6
 */
class WishListMemberCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'wishlist-member');
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that WishList Member Level to Kit Form Mapping works,
	 * and the email address is added to Kit when assigned the WishList Member Level
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitFormMappingOnLevelAdded(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'add', $_ENV['CONVERTKIT_API_THIRD_PARTY_INTEGRATIONS_FORM_NAME']);

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists($I, $emailAddress);
	}

	/**
	 * Test that WishList Member Level to Kit Legacy Form Mapping works,
	 * and the email address is added to Kit when assigned the WishList Member Level
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitLegacyFormMappingOnLevelAdded(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'add', $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME']);

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists($I, $emailAddress);
	}

	/**
	 * Test that WishList Member Level to Kit Tag Mapping works,
	 * and the email address is added to Kit when assigned the WishList Member Level
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitTagMappingOnLevelAdded(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'add', $_ENV['CONVERTKIT_API_TAG_NAME']);

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$subscriber = $I->apiCheckSubscriberExists($I, $emailAddress);

		// Check subscriber assigned to tag.
		$I->apiCheckSubscriberHasTag($I, $subscriber['id'], $_ENV['CONVERTKIT_API_TAG_ID']);
	}

	/**
	 * Test that WishList Member Level to Kit Sequence Mapping works,
	 * and the email address is added to Kit when assigned the WishList Member Level
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitSequenceMappingOnLevelAdded(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'add', $_ENV['CONVERTKIT_API_SEQUENCE_NAME']);

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$subscriber = $I->apiCheckSubscriberExists($I, $emailAddress);

		// Check that the subscriber has been assigned to the sequence.
		$I->apiCheckSubscriberHasSequence($I, $subscriber['id'], $_ENV['CONVERTKIT_API_SEQUENCE_ID']);
	}

	/**
	 * Test that the email address is added to Kit when assigned the WishList Member Level.
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitSubscribeMappingOnLevelAdded(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'add', 'Subscribe');

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists($I, $emailAddress);
	}

	/**
	 * Test that WishList Member Level to Kit Form Mapping works,
	 * and the email address is added to Kit when removed from the WishList Member Level
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitFormMappingOnLevelRemoved(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'remove', $_ENV['CONVERTKIT_API_THIRD_PARTY_INTEGRATIONS_FORM_NAME']);

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was not added to Kit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);

		// Remove level from user.
		$this->_removeLevelFromUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists($I, $emailAddress);
	}

	/**
	 * Test that WishList Member Level to Kit Legacy Form Mapping works,
	 * and the email address is added to Kit when removed from the WishList Member Level
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitLegacyFormMappingOnLevelRemoved(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'remove', $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME']);

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was not added to Kit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);

		// Remove level from user.
		$this->_removeLevelFromUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists($I, $emailAddress);
	}

	/**
	 * Test that WishList Member Level to Kit Tag Mapping works,
	 * and the email address is added to Kit when removed from the WishList Member Level
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitTagMappingOnLevelRemoved(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'remove', $_ENV['CONVERTKIT_API_TAG_NAME']);

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was not added to Kit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);

		// Remove level from user.
		$this->_removeLevelFromUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$subscriber = $I->apiCheckSubscriberExists($I, $emailAddress);

		// Check subscriber assigned to tag.
		$I->apiCheckSubscriberHasTag($I, $subscriber['id'], $_ENV['CONVERTKIT_API_TAG_ID']);
	}

	/**
	 * Test that WishList Member Level to Kit Sequence Mapping works,
	 * and the email address is added to Kit when removed from the WishList Member Level
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitSequenceMappingOnLevelRemoved(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'remove', $_ENV['CONVERTKIT_API_SEQUENCE_NAME']);

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was not added to Kit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);

		// Remove level from user.
		$this->_removeLevelFromUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$subscriber = $I->apiCheckSubscriberExists($I, $emailAddress);

		// Check that the subscriber has been assigned to the sequence.
		$I->apiCheckSubscriberHasSequence($I, $subscriber['id'], $_ENV['CONVERTKIT_API_SEQUENCE_ID']);
	}

	/**
	 * Test that the email address is removed from to Kit when removed from the WishList Member Level.
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWLMToKitUnsubscribeMappingOnLevelRemoved(EndToEndTester $I)
	{
		// Get WishList Member Level ID defined.
		$wlmLevelID = $this->_getWishListMemberLevelID($I);

		// Define email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Create a test WordPress User.
		$userID = $this->_createUser($I, $emailAddress);

		// Configure mapping.
		$this->_configureMapping($I, $wlmLevelID, 'add', 'Subscribe');
		$this->_configureMapping($I, $wlmLevelID, 'remove', 'Unsubscribe');

		// Assign level to user.
		$this->_assignLevelToUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was added to Kit.
		$I->apiCheckSubscriberExists($I, $emailAddress);

		// Remove level from user.
		$this->_removeLevelFromUser($I, $wlmLevelID, $userID);

		// Confirm that the email address was not added to Kit.
		$I->apiCheckSubscriberDoesNotExist($I, $emailAddress);
	}

	/**
	 * Tests that existing settings are automatically migrated when updating
	 * the Plugin to 2.5.3 or higher, with:
	 * - Form IDs with value `default` are changed to a blank string
	 *
	 * @since   2.5.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testSettingsMigratedOnUpgrade(EndToEndTester $I)
	{
		// Create settings as if they were created / edited when the Kit Plugin < 2.5.3
		// was active.
		$I->haveOptionInDatabase(
			'_wp_convertkit_integration_wishlistmember_settings',
			[
				'1_form'        => $_ENV['CONVERTKIT_API_FORM_ID'],
				'2_form'        => '',
				'3_form'        => 'default',
				'4_unsubscribe' => $_ENV['CONVERTKIT_API_TAG_ID'],
			]
		);

		// Downgrade the Plugin version to simulate an upgrade.
		$I->haveOptionInDatabase('convertkit_version', '2.4.9');

		// Load admin screen.
		$I->amOnAdminPage('index.php');

		// Check settings structure has been updated.
		$settings = $I->grabOptionFromDatabase('_wp_convertkit_integration_wishlistmember_settings');
		$I->assertEquals($settings['1_add'], 'form:' . $_ENV['CONVERTKIT_API_FORM_ID']);
		$I->assertEquals($settings['2_add'], '');
		$I->assertEquals($settings['3_add'], '');
		$I->assertEquals($settings['4_remove'], 'tag:' . $_ENV['CONVERTKIT_API_TAG_ID']);
	}

	/**
	 * Returns the WishList Member Level ID created when setupWishListMemberPlugin() was called.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @return  int                     Level ID
	 */
	private function _getWishListMemberLevelID(EndToEndTester $I)
	{
		$table     = $I->grabPrefixedTableNameFor('wlm_options');
		$wlmLevels = $I->grabAllFromDatabase($table, 'option_value', [ 'option_name' => 'wpm_levels' ]);
		$wlmLevels = unserialize( $wlmLevels[0]['option_value'] );
		return array_key_first( $wlmLevels );
	}

	/**
	 * Creates a WordPress User, returning their User ID.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I              Tester.
	 * @param   string         $emailAddress   Email Address.
	 * @return  int                                 User ID
	 */
	private function _createUser(EndToEndTester $I, $emailAddress)
	{
		return $I->haveUserInDatabase(
			'wlm_test_user',
			'subscriber',
			[
				'user_email'   => $emailAddress,
				'first_name'   => 'Test',
				'last_name'    => 'User',
				'display_name' => 'Test User',
			]
		);
	}

	/**
	 * Configure WishList Member Levels to Kit settings mapping.
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   int            $wlmLevelID    WishList Member Level ID.
	 * @param   string         $action        Action (subscribe,unsubscribe).
	 * @param   string         $resourceName  Resource option to select (subscribe, form, tag, sequence etc).
	 */
	private function _configureMapping(EndToEndTester $I, $wlmLevelID, $action, $resourceName)
	{
		// Load WishList Member Plugin Settings.
		$I->amOnAdminPage('options-general.php?page=_wp_convertkit_settings&tab=wishlist-member');

		// Check that a Form Mapping option is displayed.
		$I->seeElementInDOM('#_wp_convertkit_integration_wishlistmember_settings_' . $wlmLevelID . '_' . $action);

		// Change Form to value specified in the .env file.
		$I->selectOption('#_wp_convertkit_integration_wishlistmember_settings_' . $wlmLevelID . '_' . $action, $resourceName);

		// Save Changes.
		$I->click('Save Changes');

		// Wait for the Settings to save.
		$I->waitForElementVisible('#setting-error-settings_updated');
		$I->see('Settings saved.');

		// Check the value of the Form field matches the input provided.
		$I->seeOptionIsSelected('#_wp_convertkit_integration_wishlistmember_settings_' . $wlmLevelID . '_' . $action, $resourceName);
	}

	/**
	 * Assigns the given WLM Level to the given WordPress User.
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I              Tester.
	 * @param   int            $wlmLevelID     WishList Member Level ID.
	 * @param   int            $userID         WordPress User ID.
	 */
	private function _assignLevelToUser(EndToEndTester $I, $wlmLevelID, $userID)
	{
		// Edit the Test User.
		$I->amOnAdminPage('user-edit.php?user_id=' . $userID . '&wp_http_referer=%2Fwp-admin%2Fusers.php');

		// Map the User to the WLM Level.
		$I->checkOption('#WishListMemberUserProfile input[value="' . $wlmLevelID . '"]');

		// Save Changes.
		$I->click('Update Member Profile');

		// Wait for the Settings to save.
		$I->waitForElementVisible('#message');
		$I->see('User updated.');

		// Confirm that the User is still assigned to the WLM Level.
		$I->seeCheckboxIsChecked('#WishListMemberUserProfile input[value="' . $wlmLevelID . '"]');
	}

	/**
	 * Removes the given WLM Level from the given WordPress User.
	 *
	 * @since   2.5.4
	 *
	 * @param   EndToEndTester $I              Tester.
	 * @param   int            $wlmLevelID     WishList Member Level ID.
	 * @param   int            $userID         WordPress User ID.
	 */
	private function _removeLevelFromUser(EndToEndTester $I, $wlmLevelID, $userID)
	{
		// Edit the Test User.
		$I->amOnAdminPage('user-edit.php?user_id=' . $userID . '&wp_http_referer=%2Fwp-admin%2Fusers.php');

		// Unmap the User to the WLM Level.
		$I->uncheckOption('#WishListMemberUserProfile input[value="' . $wlmLevelID . '"]');

		// Save Changes.
		$I->click('Update Member Profile');

		// Wait for the Settings to save.
		$I->waitForElementVisible('#message');
		$I->see('User updated.');

		// Confirm that the User is no longer assigned to the WLM Level.
		$I->dontSeeCheckboxIsChecked('#WishListMemberUserProfile input[value="' . $wlmLevelID . '"]');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'wishlist-member');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
