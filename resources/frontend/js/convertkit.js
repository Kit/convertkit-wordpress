/**
 * Frontend functionality for subscribers and tags.
 *
 * @since   1.9.6
 *
 * @author ConvertKit
 */

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

// Scope the function to the window object as webpack will wrap everything in a closure,
// resulting in the function not being available globally.
window.convertKitRecaptchaFormSubmit = convertKitRecaptchaFormSubmit;
/* eslint-enable no-unused-vars */

/**
 * Register events on frontend.
 *
 * @since   3.2.0
 */
if (typeof convertkit !== 'undefined') {
	document.addEventListener('DOMContentLoaded', function () {
		// Removes `ck_subscriber_id` from the URI.
		convertKitRemoveSubscriberIDFromURL(window.location.href);

		// Set a cookie if any scripts with data-kit-limit-per-session attribute exist.
		if (
			document.querySelectorAll('script[data-kit-limit-per-session="1"]')
				.length > 0
		) {
			document.cookie = 'ck_non_inline_form_displayed=1; path=/';
			if (convertkit.debug) {
				console.log(
					'Set `ck_non_inline_form_displayed` cookie for non-inline form limit'
				);
			}
		}
	});
}
