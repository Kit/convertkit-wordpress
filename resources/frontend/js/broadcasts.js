/**
 * Frontend functionality for the Broadcasts block and shortcode.
 *
 * @since   1.9.7.4
 *
 * @author ConvertKit
 */

/**
 * Register events
 */
document.addEventListener('DOMContentLoaded', function () {
	// Listen for click events.
	document.addEventListener('click', function (e) {
		// Check the broadcasts pagination was clicked.
		if (e.target.matches('ul.convertkit-broadcasts-pagination a')) {
			e.preventDefault();

			// Get block container and build object of data-* attributes.
			const blockContainer = e.target.closest(
				'div.convertkit-broadcasts'
			);
			const atts = {
				display_date: blockContainer.dataset.displayDate,
				date_format: blockContainer.dataset.dateFormat,
				display_image: blockContainer.dataset.displayImage,
				display_description: blockContainer.dataset.displayDescription,
				display_read_more: blockContainer.dataset.displayReadMore,
				read_more_label: blockContainer.dataset.readMoreLabel,
				limit: blockContainer.dataset.limit,
				paginate: blockContainer.dataset.paginate,
				paginate_label_prev: blockContainer.dataset.paginateLabelPrev,
				paginate_label_next: blockContainer.dataset.paginateLabelNext,
				link_color: blockContainer.dataset.linkColor,
				page: e.target.dataset.page,
				nonce: e.target.dataset.nonce,
			};

			convertKitBroadcastsRender(blockContainer, atts);
		}
	});
});

/**
 * Sends an AJAX request to request HTML based on the supplied block attributes,
 * when pagination is used on a Broadcast block.
 *
 * @since 	1.9.7.6
 *
 * @param {Object} blockContainer DOM object of the block to refresh the HTML in.
 * @param {Object} atts           Block attributes
 */
function convertKitBroadcastsRender(blockContainer, atts) {
	// Append action.
	atts.action = convertkit_broadcasts.action;

	if (convertkit_broadcasts.debug) {
		console.log('convertKitBroadcastsRender()');
		console.log(atts);
	}

	// Show loading indicator.
	blockContainer.classList.add('convertkit-broadcasts-loading');

	// Fetch HTML.
	fetch(convertkit_broadcasts.ajax_url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
		body: new URLSearchParams(atts),
	})
		.then(function (response) {
			if (convertkit_broadcasts.debug) {
				console.log(response);
			}

			return response.json();
		})
		.then(function (result) {
			if (convertkit_broadcasts.debug) {
				console.log(result);
			}

			// Remove loading indicator.
			blockContainer.classList.remove('convertkit-broadcasts-loading');

			// Replace block container's HTML with response data.
			blockContainer.innerHTML = result.data;
		})
		.catch(function (error) {
			if (convertkit.debug) {
				console.error(error);
			}

			// Remove loading indicator.
			blockContainer.classList.remove('convertkit-broadcasts-loading');
		});
}
