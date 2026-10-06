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

/**
 * Holds the most recently clicked reCAPTCHA submit button, so the reCAPTCHA
 * callback submits that button's form when a page has multiple forms.
 *
 * @since 3.4.6
 */
let convertKitRecaptchaSubmitButton = null;

// Store the clicked reCAPTCHA submit button. This runs in the capture phase,
// before reCAPTCHA's own click handler on the button.
document.addEventListener(
	'click',
	function (e) {
		if (!(e.target instanceof Element)) {
			return;
		}

		const button = e.target.closest(
			'[type="submit"][data-callback="convertKitRecaptchaFormSubmit"]'
		);
		if (button) {
			convertKitRecaptchaSubmitButton = button;
		}
	},
	true
);

/* eslint-disable no-unused-vars */
/**
 * Handles form submissions when reCAPTCHA is enabled.
 *
 * @param {string} token reCAPTCHA token.
 */
function convertKitRecaptchaFormSubmit(token) {
	// Use the clicked submit button, falling back to the first reCAPTCHA submit button on the page.
	const submitButton =
		convertKitRecaptchaSubmitButton ||
		document.querySelector(
			'[type="submit"][data-callback="convertKitRecaptchaFormSubmit"]'
		);

	// Get the parent form of the submit button.
	const form = submitButton.closest('form');

	// Submit the form, using requestSubmit() so any submit event listeners are honored
	// e.g. the Member Content login form, which submits using AJAX.
	form.requestSubmit();
}

// Scope the function to the window object as webpack will wrap everything in a closure,
// resulting in the function not being available globally.
window.convertKitRecaptchaFormSubmit = convertKitRecaptchaFormSubmit;

/**
 * Holds the form and submit button awaiting a Cloudflare Turnstile token.
 *
 * @since 3.4.6
 */
let convertKitTurnstileForm = null;
let convertKitTurnstileSubmitter = null;

// Generate a Cloudflare Turnstile token when a form is submitted, before other submit listeners run.
document.addEventListener(
	'submit',
	function (e) {
		const form = e.target;
		if (!(form instanceof HTMLFormElement)) {
			return;
		}

		// Bail if the form doesn't contain a Turnstile widget.
		const widget = form.querySelector(
			'.cf-turnstile[data-callback="convertKitTurnstileFormSubmit"]'
		);
		if (!widget) {
			return;
		}

		// Permit the submission if the form has a token, resetting the widget as each token can only be used once.
		const response = form.querySelector('[name="cf-turnstile-response"]');
		if (response !== null && response.value !== '') {
			setTimeout(function () {
				convertKitTurnstileReset(widget);
			}, 0);
			return;
		}

		// Permit the submission if the Turnstile script isn't loaded, so the server returns an error.
		if (typeof window.turnstile === 'undefined') {
			return;
		}

		// Prevent the submission until a token is generated.
		e.preventDefault();
		e.stopImmediatePropagation();

		// Store the form and submit button, so the callback submits this form.
		convertKitTurnstileForm = form;
		convertKitTurnstileSubmitter = e.submitter || null;

		// Render the widget if it was inserted after the page loaded e.g. following a failed Member Content login.
		if (response === null) {
			window.turnstile.render(widget);
		}

		// Generate a token.
		window.turnstile.execute(widget);
	},
	true
);

/**
 * Resets the given Cloudflare Turnstile widget, so a new token is generated on the
 * next submission.
 *
 * @since 3.4.6
 *
 * @param {Element} widget Turnstile widget.
 */
function convertKitTurnstileReset(widget) {
	if (typeof window.turnstile === 'undefined' || !widget.isConnected) {
		return;
	}

	window.turnstile.reset(widget);
}

/**
 * Submits the form awaiting a Cloudflare Turnstile token, once Turnstile has
 * generated the token.
 *
 * Turnstile populates a hidden `cf-turnstile-response` input inside the form,
 * which is included when the form is submitted.
 *
 * @param {string} token Turnstile response token.
 */
function convertKitTurnstileFormSubmit(token) {
	// Bail if no form is awaiting a token.
	if (convertKitTurnstileForm === null) {
		return;
	}

	const form = convertKitTurnstileForm;
	const submitter = convertKitTurnstileSubmitter;
	convertKitTurnstileForm = null;
	convertKitTurnstileSubmitter = null;

	// Submit the form, using requestSubmit() so any submit event listeners are honored
	// e.g. the Member Content login form, which submits using AJAX.
	if (submitter !== null && submitter.form === form) {
		form.requestSubmit(submitter);
	} else {
		form.requestSubmit();
	}
}

// Scope the function to the window object as webpack will wrap everything in a closure,
// resulting in the function not being available globally.
window.convertKitTurnstileFormSubmit = convertKitTurnstileFormSubmit;
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
