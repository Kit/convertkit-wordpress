/**
 * Frontend functionality for Restrict Content functionality.
 *
 * @since   2.3.7
 *
 * @author ConvertKit
 */

/**
 * Register events
 */
document.addEventListener('DOMContentLoaded', function () {
	// Open modal.
	document
		.querySelectorAll('.convertkit-restrict-content-modal-open')
		.forEach(function (element) {
			element.addEventListener('click', function (e) {
				e.preventDefault();
				convertKitRestrictContentOpenModal();
			});
		});

	// Handle modal form submissions.
	document.addEventListener('submit', function (e) {
		// Bail if the submission was not for the Restrict Content form.
		if (!e.target.matches('form#convertkit-restrict-content-form')) {
			return;
		}

		e.preventDefault();
		convertKitRestrictContentFormSubmit(e);
	});

	// Close modal.
	// Check if the element exists on screen; if another JS error occurs, OTP submission might not be async,
	// resulting in the non-JS OTP code screen loading without a modal (i.e. ?convertkit_login=1).
	const convertKitRestrictContentCloseElement = document.querySelector(
		'#convertkit-restrict-content-modal-close'
	);
	if (convertKitRestrictContentCloseElement !== null) {
		convertKitRestrictContentCloseElement.addEventListener(
			'click',
			function (e) {
				e.preventDefault();
				convertKitRestrictContentCloseModal();
			}
		);
	}
});

/**
 * Handles Restrict Content form submission.
 *
 * @since 	2.4.2
 *
 * @param {Event} e Form submission event.
 */
function convertKitRestrictContentFormSubmit(e) {
	// Determine where the response should be displayed; either the embedded login
	// form, or the modal.
	const container = convertKitRestrictContentContainer(e.target);

	// Disable inputs.
	container
		.querySelectorAll(
			'input[type="text"], input[type="email"], input[type="submit"]'
		)
		.forEach(function (input) {
			input.setAttribute('disabled', 'disabled');
		});

	// Show loading overlay.
	convertKitRestrictContentLoading(true);

	// Determine if this is the email or code submission.
	const isCodeSubmission =
		container.querySelector('input#convertkit-subscriber-code') !== null;

	if (isCodeSubmission) {
		// Code submission.
		convertKitRestrictContentSubscriberVerification(
			convertkit_restrict_content.nonce,
			e.target.querySelector('input[name="subscriber_code"]').value,
			e.target.querySelector('input[name="token"]').value,
			e.target.querySelector('input[name="convertkit_post_id"]').value,
			container
		);

		return false;
	}

	// Email submission.
	convertKitRestrictContentSubscriberAuthenticationSendCode(
		convertkit_restrict_content.nonce,
		e.target.querySelector('input[name="convertkit_email"]').value,
		e.target.querySelector('input[name="convertkit_resource_type"]').value,
		e.target.querySelector('input[name="convertkit_resource_id"]').value,
		e.target.querySelector('input[name="convertkit_post_id"]').value,
		convertKitRestrictContentSpamProtectionResponse(e.target),
		container
	);
}

/**
 * Returns whether the heading should be displayed above the login form, which is
 * the case when the modal is used.
 *
 * @since 	3.4.2
 *
 * @param {Object} container Container element.
 * @return {boolean} Display the heading.
 */
function convertKitRestrictContentDisplayHeading(container) {
	return container.id === 'convertkit-restrict-content-modal-content';
}

/**
 * Returns the element to display the response in for the given form, which is
 * either the embedded login form's container, or the modal's content.
 *
 * @since 	3.4.2
 *
 * @param {Object} form Form element.
 * @return {Object} Container element.
 */
function convertKitRestrictContentContainer(form) {
	return (
		form.closest('.convertkit-restrict-content-content') ||
		document.querySelector('#convertkit-restrict-content-modal-content')
	);
}

/**
 * Shows or hides the modal's loading overlay, if the modal is used.
 *
 * @since 	3.4.2
 *
 * @param {boolean} display Display the loading overlay.
 */
function convertKitRestrictContentLoading(display) {
	const loading = document.querySelector(
		'#convertkit-restrict-content-modal-loading'
	);

	if (loading === null) {
		return;
	}

	loading.style.display = display ? 'block' : 'none';
}

/**
 * Returns the spam protection response for the given form, if a spam protection
 * provider is enabled.
 *
 * @since 	3.4.2
 *
 * @param {Object} form Form element.
 * @return {string} Spam protection response.
 */
function convertKitRestrictContentSpamProtectionResponse(form) {
	const field = form.querySelector(
		'[name="g-recaptcha-response"], [name="cf-turnstile-response"]'
	);

	return field === null ? '' : field.value;
}

/**
 * Opens the modal, displaying the content stored within the
 * #convertkit-restrict-content-modal-content element.
 *
 * @since 	2.3.8
 */
function convertKitRestrictContentOpenModal() {
	document.querySelector(
		'#convertkit-restrict-content-modal-background'
	).style.display = 'block';
	document.querySelector('#convertkit-restrict-content-modal').style.display =
		'block';
}

/**
 * Closes the modal.
 *
 * @since 	2.3.8
 */
function convertKitRestrictContentCloseModal() {
	document.querySelector(
		'#convertkit-restrict-content-modal-background'
	).style.display = 'none';
	document.querySelector(
		'#convertkit-restrict-content-modal-loading'
	).style.display = 'none';
	document.querySelector('#convertkit-restrict-content-modal').style.display =
		'none';
}

/**
 * Submits the given email address to the WP REST API kit/v1/restrict-content/subscriber-authentication
 * endpoint, which will return either:
 * - the email form view, with an error message e.g. invalid email,
 * - the code form view, where the user can enter the OTP.
 *
 * @since 	2.3.8
 *
 * @param {string} nonce                    WordPress nonce.
 * @param {string} email                    Email address.
 * @param {string} resource_type            Resource Type (form|tag|product).
 * @param {string} resource_id              Resource ID (Kit Form,Tag or Product ID).
 * @param {number} post_id                  WordPress Post ID being viewed / accessed.
 * @param {string} spam_protection_response Spam protection response.
 * @param {Object} container                Element to display the response in.
 */
function convertKitRestrictContentSubscriberAuthenticationSendCode(
	nonce,
	email,
	resource_type,
	resource_id,
	post_id,
	spam_protection_response,
	container
) {
	fetch(convertkit_restrict_content.subscriber_authentication_url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': nonce,
		},
		body: JSON.stringify({
			convertkit_email: email,
			convertkit_resource_type: resource_type,
			convertkit_resource_id: resource_id,
			convertkit_post_id: post_id,
			spam_protection_response,
			display_heading: convertKitRestrictContentDisplayHeading(container),
		}),
	})
		.then(function (response) {
			if (convertkit_restrict_content.debug) {
				console.log(response);
			}

			return response.json();
		})
		.then(function (result) {
			if (convertkit_restrict_content.debug) {
				console.log(result);
			}

			// Output error message if the response contains a code.
			if (typeof result.code !== 'undefined') {
				container.innerHTML = result.message;
			} else {
				// Output response, which will be either:
				// - the email form view, with an error message e.g. invalid email,
				// - the code form view, where the user can enter the OTP.
				container.innerHTML = result.data;
			}

			// Hide loading overlay.
			convertKitRestrictContentLoading(false);

			// Re-bind OTP listener.
			convertKitRestrictContentOTPField();
		})
		.catch(function (error) {
			if (convertkit_restrict_content.debug) {
				console.error(error);
			}
		});
}

/**
 * Submits the given email address to the WP REST API kit/v1/restrict-content/subscriber-verification
 * endpoint, which will return either:
 * - the code form view, with an error message e.g. invalid code entered,
 * - the Post's URL, with a `ck-cache-bust` parameter appended, which can then be loaded to show the content.
 *
 * @since 	2.3.8
 *
 * @param {string} nonce           WordPress nonce.
 * @param {string} subscriber_code OTP Subscriber Code.
 * @param {string} token           Subscriber Token.
 * @param {number} post_id         WordPress Post ID being viewed / accessed.
 * @param {Object} container       Element to display the response in.
 */
function convertKitRestrictContentSubscriberVerification(
	nonce,
	subscriber_code,
	token,
	post_id,
	container
) {
	fetch(convertkit_restrict_content.subscriber_verification_url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': nonce,
		},
		body: JSON.stringify({
			subscriber_code,
			token,
			convertkit_post_id: post_id,
		}),
	})
		.then(function (response) {
			if (convertkit_restrict_content.debug) {
				console.log(response);
			}

			return response.json();
		})
		.then(function (result) {
			if (convertkit_restrict_content.debug) {
				console.log(result);
			}

			// If the entered code is invalid, show the response.
			if (!result.success) {
				container.innerHTML = result.data;

				// Hide loading overlay.
				convertKitRestrictContentLoading(false);

				// Re-bind OTP listener.
				convertKitRestrictContentOTPField();
				return;
			}

			// Code entered is valid; load the URL in the response data.
			window.location = result.url;
		})
		.catch(function (error) {
			if (convertkit_restrict_content.debug) {
				console.error(error);
			}
		});
}

/**
 * Defines the `--opt-digit` CSS var, so that the background color shifts to the next input
 * when entering the one time code.
 *
 * @since 	2.3.8
 */
function convertKitRestrictContentOTPField() {
	const otpInput = document.querySelector('#convertkit-subscriber-code');

	// Bail if the OTP input isn't displayed on screen.
	if (otpInput === null) {
		return;
	}

	otpInput.addEventListener('input', function () {
		// Update the --_otp-digit property when the input value changes.
		otpInput.style.setProperty('--_otp-digit', otpInput.value.length);

		// If all 6 digits have been entered:
		// - move the caret input to the start,
		// - blur the input now that all numbers are entered,
		// - submit the form.
		if (otpInput.value.length === 6) {
			otpInput.setSelectionRange(0, 0);
			otpInput.blur();
			document
				.querySelector('#convertkit-restrict-content-form')
				.requestSubmit();
		}
	});
}
