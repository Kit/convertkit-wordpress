/**
 * Moves any buttons added from the filter list in a WP_List_Table
 * to be displayed next to the "Add New" button.
 *
 * @author ConvertKit
 */

document.addEventListener('DOMContentLoaded', function () {
	// Move any buttons from the filter list to display next to the Add New button.
	document.querySelectorAll('ul.subsubsub span').forEach(function (span) {
		// Ignore if not a ConvertKit Group Action.
		if (!span.classList.contains('convertkit-action')) {
			return;
		}

		// Clone and move.
		const clone = span.cloneNode(true);
		clone.classList.remove('hidden');
		document
			.querySelector('a.page-title-action')
			.insertAdjacentElement('afterend', clone);

		// Remove original.
		span.parentElement.remove();
	});
});
