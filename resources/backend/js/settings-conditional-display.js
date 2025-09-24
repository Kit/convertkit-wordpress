/**
 * Displays or hides settings in the UI, depending on which settings are enabled
 * or disabled.
 *
 * @author ConvertKit
 */

/**
 * Displays or hides settings in the UI, depending on which settings are enabled
 * or disabled.
 *
 * @since 	2.2.4
 */
document.addEventListener('DOMContentLoaded', function () {
	// Update settings and refresh UI when a setting is changed.
	const sourceInputs = document.querySelectorAll(
		'.convertkit-conditional-display'
	);
	sourceInputs.forEach(function (input) {
		input.addEventListener('change', function () {
			convertKitConditionallyDisplaySettings(this);
		});

		convertKitConditionallyDisplaySettings(input);
	});
});

/**
 * Shows all table rows on a ConvertKit settings screen, and then hides
 * table rows related to a setting, if that setting is disabled.
 *
 * @since 	2.2.4
 *
 * @param {Object} input Element interacted with.
 */
function convertKitConditionallyDisplaySettings(input) {
	const rows = document.querySelectorAll('table.form-table tr');

	switch (input.type) {
		case 'checkbox':
			// Show all rows.
			rows.forEach((row) => (row.style.display = ''));

			// Don't do anything else if the checkbox is checked.
			if (input.checked) {
				return;
			}

			// Iterate through the table rows, hiding any settings.
			rows.forEach(function (row) {
				// Skip if this table row is for the setting we've just checked/unchecked.
				if (row.querySelector(`[id = "${input.id}"]`)) {
					return;
				}

				// Hide this row if the input, select, link or span element within the row has the CSS class of the setting ID.
				if (
					row.querySelector(
						`input.${input.id}, select.${input.id}, a.${input.id}, span.${input.id}`
					)
				) {
					row.style.display = 'none';
				}
			});
			break;

		default:
			// Iterate through the table rows, hiding any settings.
			rows.forEach(function (row) {
				// Skip if this table row is for the setting we've just changed.
				if (row.querySelector(`[id = "${input.id}"]`)) {
					return;
				}

				if (
					row.querySelector(
						`input#${input.dataset.conditionalElement}`
					)
				) {
					if (input.value !== input.dataset.conditionalValue) {
						row.style.display = 'none';
					} else {
						row.style.display = '';
					}
				}
			});
			break;
	}
}
