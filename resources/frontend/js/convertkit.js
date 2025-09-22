/**
 * Frontend functionality for subscribers and tags.
 *
 * @since   1.9.6
 *
 * @author ConvertKit
 */

/**
 * Gets the subscriber ID for the given email address, storing
 * it in the `ck_subscriber_id` cookie if it exists.
 *
 * Typically called when the user completes a ConvertKit Form
 * that has either "Auto-confirm new subscribers" or
 * "Send subscriber to thank you page" enabled (both scenarios
 * include a ck_subscriber_id).
 *
 * @since   1.9.6
 *
 * @param {string} emailAddress Email Address
 */
function convertStoreSubscriberEmailAsIDInCookie(emailAddress) {
	if (convertkit.debug) {
		console.log('convertStoreSubscriberEmailAsIDInCookie');
		console.log(emailAddress);
	}

	fetch(convertkit.ajaxurl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
		body: new URLSearchParams({
			action: 'convertkit_store_subscriber_email_as_id_in_cookie',
			convertkit_nonce: convertkit.nonce,
			email: emailAddress,
		}),
	})
		.then(function (response) {
			if (convertkit.debug) {
				console.log(response);
			}

			return response.json();
		})
		.then(function (result) {
			if (convertkit.debug) {
				console.log(result);
			}

			// Emit custom event with subscriber ID.
			convertKitEmitCustomEvent('convertkit_user_subscribed', {
				id: result.data.id,
				email: emailAddress,
			});
		})
		.catch(function (error) {
			if (convertkit.debug) {
				console.error(error);
			}
		});
}

/**
 * Remove the url subscriber_id url param
 *
 * The 'ck_subscriber_id' should only be set on URLs included on
 * links from a ConvertKit email with no other URL parameters.
 * This function removes the parameters so a customer won't share
 * a URL with their subscriber ID in it.
 *
 * @param {string} url URL.
 */
function convertKitRemoveSubscriberIDFromURL(url) {
	// Parse URL.
	const url_object = new URL(url);
	const ck_subscriber_id = url_object.searchParams.get('ck_subscriber_id');

	// If ck_subscriber_id is null, it's not included in the URL.
	// Don't modify the URL.
	if (ck_subscriber_id === null) {
		return;
	}

	// Remove ck_subscriber_id from URL params.
	url_object.searchParams.delete('ck_subscriber_id');

	// Get title and string of parameters.
	const title = document.getElementsByTagName('title')[0].innerHTML;
	let params = url_object.searchParams.toString();

	// Only add '?' if there are parameters.
	if (params.length > 0) {
		params = '?' + params;
	}

	// Update history.
	window.history.replaceState(
		null,
		title,
		url_object.pathname + params + url_object.hash
	);

	// Emit custom event with the removed subscriber ID.
	convertKitEmitCustomEvent('kit_subscriber_id_removed_from_url', {
		id: ck_subscriber_id,
	});
}

/**
 * Utility function to pause for the given number of milliseconds
 *
 * @since   1.9.6
 * @param {number} milliseconds Number of milliseconds to pause for.
 */
function convertKitSleep(milliseconds) {
	const start = new Date().getTime();
	for (let i = 0; i < 1e7; i++) {
		if (new Date().getTime() - start > milliseconds) {
			break;
		}
	}
}

/**
 * Emit a custom event with optional detail data.
 *
 * This function creates and dispatches a custom event with the specified
 * event name and detail data.
 *
 * @since 2.5.0
 *
 * @param {string} eventName   The name of the custom event to emit.
 * @param {Object} [detail={}] Optional detail data to include with the event.
 */
function convertKitEmitCustomEvent(eventName, detail) {
	const event = new CustomEvent(eventName, { detail });
	document.dispatchEvent(event);
}

/* eslint-disable no-unused-vars */
/**
 * Handles form submissions when reCAPTCHA is enabled.
 *
 * @param {string} token reCAPTCHA token.
 */
function convertKitRecaptchaFormSubmit(token) {
	// Find submit button with the data-callback attribute.
	const submitButton = document.querySelector(
		'[type="submit"][data-callback="convertKitRecaptchaFormSubmit"]'
	);

	// Get the parent form of the submit button.
	const form = submitButton.closest('form');

	// Submit the form.
	form.submit();
}
/* eslint-enable no-unused-vars */

/**
 * Register events
 */
document.addEventListener('DOMContentLoaded', function () {
	// Removes `ck_subscriber_id` from the URI.
	convertKitRemoveSubscriberIDFromURL(window.location.href);

	// Store subscriber ID as a cookie from the email address used when a ConvertKit Form is submitted.
	document.addEventListener('click', function (e) {
		// Check if the form submit button was clicked, or the span element was clicked and its parent is the form submit button.
		if (
			!e.target.matches('.formkit-submit') &&
			(!e.target.parentElement ||
				!e.target.parentElement.matches('.formkit-submit'))
		) {
			if (convertkit.debug) {
				console.log('not a ck form');
			}

			return;
		}

		// Get email address.
		const emailAddress = document.querySelector(
			'input[name="email_address"]'
		).value;

		// If the email address is empty, don't attempt to get the subscriber ID by email.
		if (!emailAddress.length) {
			if (convertkit.debug) {
				console.log('email empty');
			}

			return;
		}

		// If the email address is invalid, don't attempt to get the subscriber ID by email.
		const validator =
			/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		if (!validator.test(emailAddress.toLowerCase())) {
			if (convertkit.debug) {
				console.log('email not an email address');
			}

			return;
		}

		// Wait a moment before sending the AJAX request.
		convertKitSleep(2000);
		convertStoreSubscriberEmailAsIDInCookie(emailAddress);
	});

	// Set a cookie if any scripts with data-kit-limit-per-session attribute exist.
	if (
		document.querySelectorAll('script[data-kit-limit-per-session]').length >
		0
	) {
		document.cookie = 'ck_non_inline_form_displayed=1; path=/';
		if (convertkit.debug) {
			console.log(
				'Set `ck_non_inline_form_displayed` cookie for non-inline form limit'
			);
		}
	}
});
