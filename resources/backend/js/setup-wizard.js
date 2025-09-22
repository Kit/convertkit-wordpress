/**
 * Setup Wizard
 *
 * @author ConvertKit
 */

/**
 * Conditionally shows and hides elements when a button or link is clicked, based on that
 * button/link's configuration.
 *
 * @since 	1.9.8.4
 */
document.addEventListener('DOMContentLoaded', function () {
	// Redirect parent screen to a given URL after clicking a link that opens
	// the href URL in a new tab.
	document
		.querySelectorAll('a.convertkit-redirect')
		.forEach(function (element) {
			element.addEventListener('click', function () {
				// Delay the redirect, otherwise browsers will block opening the href attribute
				// thinking it's a popup.
				setTimeout(function () {
					// Redirect the parent screen to the link's data-convertkit-redirect-url property.
					window.location.href =
						element.dataset.convertkitRedirectUrl;
				}, 1000);
			});
		});

	// Show a confirmation dialog for specific links.
	document
		.querySelectorAll('a.convertkit-confirm')
		.forEach(function (element) {
			element.addEventListener('click', function (e) {
				if (!confirm(element.dataset.message)) {
					e.preventDefault();
				}
			});
		});
});
