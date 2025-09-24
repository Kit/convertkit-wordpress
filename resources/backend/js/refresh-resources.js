/**
 * Refresh Resources
 *
 * @author ConvertKit
 */

document.addEventListener(
	'DOMContentLoaded',
	convertKitRefreshResourcesInitEventListeners
);

/**
 * Binds click event listeners to refresh resource buttons.
 *
 * @since 	2.4.4
 */
function convertKitRefreshResourcesInitEventListeners() {
	// Refreshes resources when the Refresh button is clicked.
	document
		.querySelectorAll('button.wp-convertkit-refresh-resources')
		.forEach(function (element) {
			element.addEventListener(
				'click',
				function (e) {
					e.preventDefault();
					convertKitRefreshResources(element);
				},
				false
			);
		});
}

/**
 * Refresh resources when a button is clicked.
 *
 * @since 	2.4.4
 *
 * @param {Object} button Button element.
 */
function convertKitRefreshResources(button) {
	// Remove any existing error notices that might be displayed.
	convertKitRefreshResourcesRemoveNotices();

	const resource = button.dataset.resource,
		field = button.dataset.field;

	// Disable button and set is-refreshing class.
	button.disabled = true;
	button.classList.add('is-refreshing');

	// Perform AJAX request to refresh resource.
	fetch(convertkit_admin_refresh_resources.ajaxurl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
		body: new URLSearchParams({
			action: 'convertkit_admin_refresh_resources',
			nonce: convertkit_admin_refresh_resources.nonce,
			resource, // e.g. forms, landing_pages, tags.
		}),
	})
		.then(function (response) {
			// Convert response JSON string to object.
			return response.json();
		})
		.then(function (response) {
			if (convertkit_admin_refresh_resources.debug) {
				console.log(response);
			}

			// Show an error if the request wasn't successful.
			if (!response.success) {
				// Show error notice.
				convertKitRefreshResourcesOutputErrorNotice(response.data);

				// Enable button and remove is-refreshing class.
				button.disabled = false;
				button.classList.remove('is-refreshing');

				return;
			}

			const selectedOption = document.querySelector(field).value;

			// Remove existing select options.
			document
				.querySelectorAll(field + ' option')
				.forEach(function (option) {
					// Skip if data-preserve-on-refresh is specified, as this means we want to keep this specific option.
					// This will be present on the 'None' and 'Default' options.
					if (option.dataset.preserveOnRefresh !== undefined) {
						return;
					}

					// Remove this option.
					option.remove();
				});

			// Depending on the resource we're refreshing, populate the <select> options.
			switch (resource) {
				case 'restrict_content':
					// Populate select `optgroup`` from response data, which comprises of Tags and Products.
					// Tags.
					response.data.tags.forEach(function (item) {
						document
							.querySelector(
								field + ' optgroup[data-resource=tags]'
							)
							.appendChild(
								new Option(
									item.name,
									'tag_' + item.id,
									false,
									selectedOption === 'tag_' + item.id
								)
							);
					});

					// Products.
					response.data.products.forEach(function (item) {
						document
							.querySelector(
								field + ' optgroup[data-resource=products]'
							)
							.appendChild(
								new Option(
									item.name,
									'product_' + item.id,
									false,
									selectedOption === 'product_' + item.id
								)
							);
					});
					break;

				default:
					// Populate select options from response data.
					response.data.forEach(function (item) {
						// Define label.
						let label = '';
						switch (resource) {
							case 'forms':
								label =
									item.name +
									' [' +
									(item.format !== ''
										? item.format
										: 'inline') +
									']';
								break;

							default:
								label = item.name;
								break;
						}

						// Add option.
						document
							.querySelector(field)
							.add(
								new Option(
									label,
									item.id,
									false,
									selectedOption === item.id ? true : false
								)
							);
					});
					break;
			}

			// Trigger a change event on the select field, to allow Select2 instances to repopulate their options.
			document.querySelector(field).dispatchEvent(new Event('change'));

			// Enable button and remove is-refreshing class.
			button.disabled = false;
			button.classList.remove('is-refreshing');
		})
		.catch(function (error) {
			if (convertkit_admin_refresh_resources.debug) {
				console.error(error);
			}

			// Remove any existing error notices that might be displayed.
			convertKitRefreshResourcesRemoveNotices();

			// Show error notice.
			convertKitRefreshResourcesOutputErrorNotice(error);

			// Enable button and remove is-refreshing class.
			button.disabled = false;
			button.classList.remove('is-refreshing');
		});
}

/**
 * Removes any existing ConvertKit WordPress style error notices.
 *
 * @since 	1.9.8.3
 */
function convertKitRefreshResourcesRemoveNotices() {
	// If we're editing a Page, Post or Custom Post Type in Gutenberg, use wp.data.dispatch to remove the error.
	if (typeof wp !== 'undefined' && typeof wp.blockEditor !== 'undefined') {
		// Gutenberg Editor.
		wp.data.dispatch('core/notices').removeNotice('convertkit-error');
		return;
	}

	// Classic Editor, WP_List_Table (Bulk/Quick edit) or Edit Term.
	document.querySelectorAll('div.convertkit-error').forEach(function (div) {
		div.remove();
	});
}

/**
 * Removes any existing ConvertKit WordPress style error notices, before outputting
 * an error notice.
 *
 * @since 	1.9.8.3
 *
 * @param {string} message Error message to display.
 */
function convertKitRefreshResourcesOutputErrorNotice(message) {
	// Prefix the message with the Plugin name.
	message = 'ConvertKit: ' + message;

	// If we're editing a Page, Post or Custom Post Type in Gutenberg, use wp.data.dispatch to show the error.
	if (typeof wp !== 'undefined' && typeof wp.blockEditor !== 'undefined') {
		// Gutenberg Editor.
		wp.data
			.dispatch('core/notices')
			.createErrorNotice(message, { id: 'convertkit-error' });
		return;
	}

	// Classic Editor, WP_List_Table (Bulk/Quick edit) or Edit Term.
	const notice =
		'<div id="message" class="error convertkit-error notice is-dismissible"><p>' +
		message +
		'</p></div>';

	// Append the WordPress style error notice, depending on the screen.
	const insertLocation =
		document.querySelector('hr.wp-header-end') ||
		document.querySelector('#ajax-response');
	if (insertLocation) {
		insertLocation.insertAdjacentHTML('afterend', notice);

		// Notify WordPress that a new dismissible notification exists, triggering WordPress' makeNoticesDismissible() function,
		// which adds a dismiss button and binds necessary events to hide the notification.
		// We can't directly call makeNoticesDismissible(), as its minified function name will be different.
		document.dispatchEvent(new Event('wp-updates-notice-added'));
	}
}
