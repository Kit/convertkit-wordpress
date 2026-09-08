<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to the Kit API,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class KitAPI extends \Codeception\Module
{
	/**
	 * Returns an encoded `state` parameter compatible with OAuth.
	 *
	 * @since   2.5.0
	 *
	 * @param   string $returnTo   Return URL.
	 * @param   string $clientID   OAuth Client ID.
	 * @return  string
	 */
	public function apiEncodeState($returnTo, $clientID)
	{
		$str = json_encode( // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			array(
				'return_to' => $returnTo,
				'client_id' => $clientID,
			)
		);

		// Encode to Base64 string.
		$str = base64_encode( $str ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

		// Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”.
		$str = strtr( $str, '+/', '-_' );

		// Remove padding character from the end of line.
		$str = rtrim( $str, '=' );

		return $str;
	}

	/**
	 * Check the given email address exists as a subscriber.
	 *
	 * @param   EndToEndTester $I              EndToEndTester.
	 * @param   string         $emailAddress   Email Address.
	 * @param   string         $firstName      First Name (false = don't check name matches).
	 * @return  array                          Subscriber.
	 */
	public function apiCheckSubscriberExists($I, $emailAddress, $firstName = false)
	{
		// Wait for the subscriber to be queryable, as list endpoints are eventually consistent.
		$results = $this->retryUntil(
			function () use ($emailAddress) {
				$results = $this->apiRequest(
					'subscribers',
					'GET',
					[
						'email_address'       => $emailAddress,
						'include_total_count' => true,

						// Check all subscriber states.
						'status'              => 'all',
					]
				);

				// Return the results only if a subscriber was found, so
				// retryUntil() will keep trying otherwise.
				return ( $results['pagination']['total_count'] > 0 ) ? $results : false;
			}
		);

		// Check at least one subscriber was returned and it matches the email address.
		$I->assertNotFalse(
			$results,
			sprintf('Subscriber %s was not returned by the API in time.', $emailAddress)
		);
		$I->assertGreaterThan(0, $results['pagination']['total_count']);
		$I->assertEquals($emailAddress, $results['subscribers'][0]['email_address']);

		// If defined, check that the name matches for the subscriber.
		if ($firstName) {
			$I->assertEquals($firstName, $results['subscribers'][0]['first_name']);
		}

		// Return subscriber ID.
		return $results['subscribers'][0];
	}

	/**
	 * Check the given subscriber ID has been assigned to the given form ID.
	 *
	 * @since   2.7.1
	 *
	 * @param   EndToEndTester $I             EndToEndTester.
	 * @param   int            $subscriberID  Subscriber ID.
	 * @param   int            $formID        Form ID.
	 * @param   string         $referrer      Referrer.
	 */
	public function apiCheckSubscriberHasForm($I, $subscriberID, $formID, $referrer = false)
	{
		// Wait for the subscriber to be assigned to the form, as list endpoints are eventually consistent.
		$subscriber = $this->retryUntil(
			function () use ($subscriberID, $formID) {
				$results = $this->apiRequest(
					'forms/' . $formID . '/subscribers',
					'GET',
					[
						// Check all subscriber states.
						'status' => 'all',
					]
				);

				// Return the subscriber only if they're assigned to the form, so
				// retryUntil() will keep trying otherwise.
				foreach ($results['subscribers'] as $subscriber) {
					if ( (int) $subscriber['id'] === (int) $subscriberID) {
						return $subscriber;
					}
				}

				return false;
			}
		);

		// Assert the subscriber has the form.
		$I->assertNotFalse(
			$subscriber,
			sprintf('Subscriber %s was not assigned to Form %s in time.', $subscriberID, $formID)
		);

		// If a referrer is specified, assert it matches the subscriber's referrer now.
		if ($referrer) {
			$I->assertEquals($subscriber['referrer'], $referrer);
		}
	}

	/**
	 * Check the given subscriber ID has been assigned to the given sequence ID.
	 *
	 * @since   2.5.2
	 *
	 * @param   EndToEndTester $I             EndToEndTester.
	 * @param   int            $subscriberID  Subscriber ID.
	 * @param   int            $sequenceID         Sequence ID.
	 */
	public function apiCheckSubscriberHasSequence($I, $subscriberID, $sequenceID)
	{
		// Wait for the subscriber to be assigned to the sequence, as list endpoints are eventually consistent.
		$subscriber = $this->retryUntil(
			function () use ($subscriberID, $sequenceID) {
				$results = $this->apiRequest(
					'sequences/' . $sequenceID . '/subscribers',
					'GET',
					[
						'status' => 'all',
					]
				);

				// Return the subscriber only if they're assigned to the sequence, so
				// retryUntil() will keep trying otherwise.
				foreach ($results['subscribers'] as $subscriber) {
					if ( (int) $subscriber['id'] === (int) $subscriberID) {
						return $subscriber;
					}
				}

				return false;
			}
		);

		// Assert the subscriber has the sequence.
		$I->assertNotFalse(
			$subscriber,
			sprintf('Subscriber %s was not assigned to Sequence %s in time.', $subscriberID, $sequenceID)
		);
	}

	/**
	 * Check the given subscriber ID has been assigned to the given tag ID.
	 *
	 * @param   EndToEndTester $I             EndToEndTester.
	 * @param   int            $subscriberID  Subscriber ID.
	 * @param   int            $tagID         Tag ID.
	 */
	public function apiCheckSubscriberHasTag($I, $subscriberID, $tagID)
	{
		// Wait for the tag to be assigned to the subscriber, as list endpoints are eventually consistent.
		$results = $this->retryUntil(
			function () use ($subscriberID) {
				$results = $this->apiRequest(
					'subscribers/' . $subscriberID . '/tags',
					'GET'
				);

				// Return the results only if a tag is assigned, so
				// retryUntil() will keep trying otherwise.
				return count($results['tags']) ? $results : false;
			}
		);

		// Assert the subscriber has a tag.
		$I->assertNotFalse(
			$results,
			sprintf('Subscriber %s was not assigned Tag %s in time.', $subscriberID, $tagID)
		);

		// Confirm the tag has been assigned to the subscriber.
		$I->assertEquals($tagID, $results['tags'][0]['id']);
	}

	/**
	 * Check the given subscriber ID has no tags assigned.
	 *
	 * @since   2.4.9.1
	 *
	 * @param   EndToEndTester $I             EndToEndTester.
	 * @param   int            $subscriberID  Subscriber ID.
	 */
	public function apiCheckSubscriberHasNoTags($I, $subscriberID)
	{
		// Run request.
		$results = $this->apiRequest(
			'subscribers/' . $subscriberID . '/tags',
			'GET'
		);

		// Confirm no tags have been assigned to the subscriber.
		$I->assertCount(0, $results['tags']);
	}

	/**
	 * Check the given email address does not exists as a subscriber.
	 *
	 * @param   EndToEndTester $I             EndToEndTester.
	 * @param   string         $emailAddress   Email Address.
	 */
	public function apiCheckSubscriberDoesNotExist($I, $emailAddress)
	{
		// Wait for the API to update.
		$I->wait(3);

		// Run request.
		$results = $this->apiRequest(
			'subscribers',
			'GET',
			[
				'email_address'       => $emailAddress,
				'include_total_count' => true,
			]
		);

		// Check no subscribers are returned by this request.
		$I->assertEquals(0, $results['pagination']['total_count']);
	}

	/**
	 * Subscribes the given email address. Useful for
	 * creating a subscriber to use in tests.
	 *
	 * @param   string $emailAddress   Email Address.
	 * @return  int                    Subscriber ID
	 */
	public function apiSubscribe($emailAddress)
	{
		// Run request.
		$result = $this->apiRequest(
			'subscribers',
			'POST',
			[
				'email_address' => $emailAddress,
			]
		);

		// Return subscriber ID.
		return $result['subscriber']['id'];
	}

	/**
	 * Unsubscribes the given email address. Useful for clearing the API
	 * between tests.
	 *
	 * @param   string $emailAddress   Email Address.
	 */
	public function apiUnsubscribe($emailAddress)
	{
		// Run request.
		$this->apiRequest(
			'unsubscribe',
			'PUT',
			[
				'email' => $emailAddress,
			]
		);
	}

	/**
	 * Fetches the given broadcast from Kit.
	 *
	 * @since   2.4.0
	 *
	 * @param   int $broadcastID    Broadcast ID.
	 */
	public function apiGetBroadcast($broadcastID)
	{
		// Run request.
		return $this->apiRequest(
			'broadcasts/' . $broadcastID,
			'GET'
		);
	}

	/**
	 * Deletes the given broadcast from Kit.
	 *
	 * @since   2.4.0
	 *
	 * @param   int $broadcastID    Broadcast ID.
	 */
	public function apiDeleteBroadcast($broadcastID)
	{
		// Run request.
		$this->apiRequest(
			'broadcasts/' . $broadcastID,
			'DELETE'
		);
	}

	/**
	 * Sends a request to the Kit API, typically used to read an endpoint to confirm
	 * that data in an EndToEnd Test was added/edited/deleted successfully.
	 *
	 * @param   string $endpoint   Endpoint.
	 * @param   string $method     Method (GET|POST|PUT).
	 * @param   array  $params     Endpoint Parameters.
	 */
	public function apiRequest($endpoint, $method = 'GET', $params = array())
	{
		// Send request.
		$client = new \GuzzleHttp\Client();
		switch ($method) {
			case 'GET':
				$result = $client->request(
					$method,
					'https://api.kit.com/v4/' . $endpoint . '?' . http_build_query($params),
					[
						'headers' => [
							'Authorization' => 'Bearer ' . $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
						],
						'timeout' => 5,
					]
				);
				break;

			default:
				$result = $client->request(
					$method,
					'https://api.kit.com/v4/' . $endpoint,
					[
						'headers' => [
							'Accept'        => 'application/json',
							'Content-Type'  => 'application/json; charset=utf-8',
							'Authorization' => 'Bearer ' . $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
						],
						'timeout' => 5,
						'body'    => (string) json_encode($params), // phpcs:ignore WordPress.WP.AlternativeFunctions
					]
				);
				break;
		}

		// Return JSON decoded response.
		return json_decode($result->getBody()->getContents(), true);
	}

	/**
	 * Repeatedly invokes the given callback until it returns a truthy value, or the
	 * maximum number of attempts is reached.
	 *
	 * Use this to wrap API checks that can be flaky due to eventual consistency at Kit's
	 * end. List endpoints typically reflect a write within ~30 seconds, and can take up
	 * to 5 minutes, so reading back immediately after a write is not reliable.
	 *
	 * @since   3.4.1
	 *
	 * @see     https://developers.kit.com/api-reference/eventual-consistency
	 *
	 * @param   callable $callback  Callback to invoke. Should return the value to use, or
	 *                              false / null when the check has not yet succeeded.
	 * @param   int      $attempts  Maximum number of attempts.
	 * @param   int      $delay     Seconds to wait between attempts. Defaults give up after ~30 seconds.
	 * @return  mixed               Value returned by the callback, or false if all attempts are exhausted.
	 */
	public function retryUntil(callable $callback, $attempts = 10, $delay = 3)
	{
		for ($i = 0; $i < $attempts; $i++) {
			$result = $callback();
			if ($result) {
				return $result;
			}

			// Don't sleep after the final attempt.
			if ($i < ( $attempts - 1 )) {
				sleep($delay);
			}
		}

		return false;
	}
}
