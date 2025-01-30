/**
 * Frontend functionality for Restrict Content functionality.
 *
 * @since   2.3.7
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Register events
 */
document.addEventListener(
	'DOMContentLoaded',
	function () {

		// Open modal.
		document.querySelectorAll( '.convertkit-restrict-content-modal-open' ).forEach(
			function ( element ) {
				element.addEventListener(
					'click',
					function ( e ) {
						e.preventDefault();
						convertKitRestrictContentOpenModal();
					}
				);
			}
		);

		// Handle modal form submissions.
		document.addEventListener(
			'submit',
			function ( e ) {
				// Bail if the submission was not for the Restrict Content form.
				if ( ! e.target.matches( '#convertkit-restrict-content-modal form#convertkit-restrict-content-form' ) ) {
					return;
				}

				e.preventDefault();
				convertKitRestrictContentFormSubmit( e );
			}
		);

		// Close modal.
		// Check if the element exists on screen; if another JS error occurs, OTP submission might not be async,
		// resulting in the non-JS OTP code screen loading without a modal (i.e. ?convertkit_login=1).
		let convertKitRestrictContentCloseElement = document.querySelector( '#convertkit-restrict-content-modal-close' );
		if ( convertKitRestrictContentCloseElement !== null ) {
			convertKitRestrictContentCloseElement.addEventListener(
				'click',
				function ( e ) {
					e.preventDefault();
					convertKitRestrictContentCloseModal();
				}
			);
		}

	}
);

function convertKitRestrictContentTagFormSubmit( token ) {

	document.getElementById( 'convertkit-restrict-content-form' ).submit();

}

/**
 * Handles Restrict Content form submission.
 *
 * @since 	2.4.2
 *
 * @param 	Event 	e 	Form submission event.
 */
function convertKitRestrictContentFormSubmit( e ) {

	// Disable inputs.
	document.querySelectorAll( 'input[type="text"], input[type="email"], input[type="submit"]' ).forEach(
		function ( input ) {
			input.setAttribute( 'disabled', 'disabled' );
		}
	);

	// Show loading overlay.
	document.querySelector( '#convertkit-restrict-content-modal-loading' ).style.display = 'block';

	// Determine if this is the email or code submission.
	let isCodeSubmission = document.querySelector( 'input#convertkit_subscriber_code' ) !== null;

	if ( isCodeSubmission ) {
		// Code submission.
		convertKitRestrictContentSubscriberVerification(
			e.target.querySelector( 'input[name="_wpnonce"]' ).value,
			e.target.querySelector( 'input[name="subscriber_code"]' ).value,
			e.target.querySelector( 'input[name="token"]' ).value,
			e.target.querySelector( 'input[name="convertkit_post_id"]' ).value
		);

		return false;
	}

	// Email submission.
	convertKitRestrictContentSubscriberAuthenticationSendCode(
		e.target.querySelector( 'input[name="_wpnonce"]' ).value,
		e.target.querySelector( 'input[name="convertkit_email"]' ).value,
		e.target.querySelector( 'input[name="convertkit_resource_type"]' ).value,
		e.target.querySelector( 'input[name="convertkit_resource_id"]' ).value,
		e.target.querySelector( 'input[name="convertkit_post_id"]' ).value
	);

}

/**
 * Opens the modal, displaying the content stored within the
 * #convertkit-restrict-content-modal-content element.
 *
 * @since 	2.3.8
 */
function convertKitRestrictContentOpenModal() {

	document.querySelector( '#convertkit-restrict-content-modal-background' ).style.display = 'block';
	document.querySelector( '#convertkit-restrict-content-modal' ).style.display            = 'block';

}

/**
 * Closes the modal.
 *
 * @since 	2.3.8
 */
function convertKitRestrictContentCloseModal() {

	document.querySelector( '#convertkit-restrict-content-modal-background' ).style.display = 'none';
	document.querySelector( '#convertkit-restrict-content-modal-loading' ).style.display    = 'none';
	document.querySelector( '#convertkit-restrict-content-modal' ).style.display            = 'none';

}

/**
 * Submits the given email address to maybe_run_subscriber_authentication(), which
 * will return either:
 * - the email form view, with an error message e.g. invalid email,
 * - the code form view, where the user can enter the OTP.
 *
 * @since 	2.3.8
 *
 * @param   string   nonce   	     WordPress nonce.
 * @param   string   email   	     Email address.
 * @param   string   resource_type   Resource Type (tag|product).
 * @param   int      resource_id     Resource ID (ConvertKit Tag or Product ID).
 * @param   int      post_id         WordPress Post ID being viewed / accessed.
 */
function convertKitRestrictContentSubscriberAuthenticationSendCode( nonce, email, resource_type, resource_id, post_id ) {

	fetch(
		convertkit_restrict_content.ajaxurl,
		{
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams(
				{
					action: 'convertkit_subscriber_authentication_send_code',
					'_wpnonce': nonce,
					convertkit_email: email,
					convertkit_resource_type: resource_type,
					convertkit_resource_id: resource_id,
					convertkit_post_id: post_id,
				}
			),
		}
	)
	.then(
		function ( response ) {
			if ( convertkit_restrict_content.debug ) {
				console.log( response );
			}

			return response.json();
		}
	)
	.then(
		function ( result ) {
			if ( convertkit_restrict_content.debug ) {
				console.log( result );
			}

			// Output response, which will be a form with/without an error message.
			document.querySelector( '#convertkit-restrict-content-modal-content' ).innerHTML = result.data;

			// Hide loading overlay.
			document.querySelector( '#convertkit-restrict-content-modal-loading' ).style.display = 'none';

			// Re-bind OTP listener.
			convertKitRestrictContentOTPField();
		}
	)
	.catch(
		function ( error ) {
			if ( convertkit_restrict_content.debug ) {
				console.error( error );
			}
		}
	);

}

/**
 * Submits the given email address to maybe_run_subscriber_verification(), which
 * will return either:
 * - the code form view, with an error message e.g. invalid code entered,
 * - the Post's URL, with a `ck-cache-bust` parameter appended, which can then be loaded to show the content.
 *
 * @since 	2.3.8
 *
 * @param   string   nonce   	     WordPress nonce.
 * @param   string   subscriber_code OTP Subscriber Code.
 * @param   string   token           Subscriber Token.
 * @param   int      post_id         WordPress Post ID being viewed / accessed.
 */
function convertKitRestrictContentSubscriberVerification( nonce, subscriber_code, token, post_id ) {

	fetch(
		convertkit_restrict_content.ajaxurl,
		{
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams(
				{
					action: 'convertkit_subscriber_verification',
					'_wpnonce': nonce,
					subscriber_code: subscriber_code,
					token: token,
					convertkit_post_id: post_id,
				}
			),
		}
	)
	.then(
		function ( response ) {
			if ( convertkit_restrict_content.debug ) {
				console.log( response );
			}

			return response.json();
		}
	)
	.then(
		function ( result ) {
			if ( convertkit_restrict_content.debug ) {
				console.log( result );
			}

			// If the entered code is invalid, show the response in the modal.
			if ( ! result.success ) {
				document.querySelector( '#convertkit-restrict-content-modal-content' ).innerHTML = result.data;

				// Hide loading overlay.
				document.querySelector( '#convertkit-restrict-content-modal-loading' ).style.display = 'none';

				// Re-bind OTP listener.
				convertKitRestrictContentOTPField();
				return;
			}

			// Code entered is valid; load the URL in the response data.
			window.location = result.data;
		}
	)
	.catch(
		function ( error ) {
			if ( convertkit_restrict_content.debug ) {
				console.error( error );
			}
		}
	);

}

/**
 * Defines the `--opt-digit` CSS var, so that the background color shifts to the next input
 * when entering the one time code.
 *
 * @since 	2.3.8
 */
function convertKitRestrictContentOTPField() {

	let otpInput = document.querySelector( '#convertkit_subscriber_code' );

	// Bail if the OTP input isn't displayed on screen.
	if ( otpInput === null ) {
		return;
	}

	otpInput.addEventListener(
		'input',
		function () {
			// Update the --_otp-digit property when the input value changes.
			otpInput.style.setProperty( '--_otp-digit', otpInput.value.length );

			// If all 6 digits have been entered:
			// - move the caret input to the start,
			// - blur the input now that all numbers are entered,
			// - submit the form.
			if ( otpInput.value.length === 6 ) {
				otpInput.setSelectionRange( 0, 0 );
				otpInput.blur();
				document.querySelector( '#convertkit-restrict-content-form' ).requestSubmit();
			}
		}
	);

}
