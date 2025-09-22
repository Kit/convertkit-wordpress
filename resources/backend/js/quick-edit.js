/**
 * Quick Edit
 *
 * @author ConvertKit
 */

/**
 * Copies Quick Edit fields into WordPress' #inline-edit table row on load.
 *
 * Populates Quick Edit fields for this Plugin with the values output
 * in the `add_inline_data` WordPress action when the user clicks
 * a Quick Edit link in a Post, Page or Custom Post Type WP_List_Table.
 *
 * WordPress' built in Quick Edit functionality does not do this automatically
 * for Plugins that register settings, which is why we have this code here.
 *
 * @since 	1.9.8.0
 */

document.addEventListener('DOMContentLoaded', function () {
	const convertKitQuickEditWrapper = document.querySelector(
			'tr#inline-edit .inline-edit-wrapper fieldset.inline-edit-col-left'
		),
		convertKitQuickEdit = document.querySelector('#convertkit-quick-edit'),
		convertKitInlineEditPost = inlineEditPost.edit;

	if (convertKitQuickEditWrapper) {
		// Move Quick Edit fields from footer into the hidden inline-edit table row.
		convertKitQuickEditWrapper.appendChild(convertKitQuickEdit);

		// Show the Quick Edit fields, as they are now contained in the inline-edit row which WordPress will show/hide as necessary.
		convertKitQuickEdit.style.display = 'block';

		// Extend WordPress' inline edit function to load the Plugin's Quick Edit fields.
		inlineEditPost.edit = function (id) {
			// Merge arguments from the original function.
			convertKitInlineEditPost.apply(this, arguments);

			// Get Post ID.
			if (typeof id === 'object') {
				id = parseInt(this.getId(id));
			}

			// Iterate through any ConvertKit inline data, assigning values to Quick Edit fields.
			document
				.querySelectorAll('#inline_' + id + ' .convertkit')
				.forEach(function (element) {
					// Get Quick Edit field.
					const convertKitQuickEditField = document.querySelector(
						'#convertkit-quick-edit select[name="wp-convertkit[' +
							element.dataset.setting +
							']"]'
					);

					// If the Quick Edit field doesn't exist for this setting, skip it.
					// (e.g. we're editing a Post and this is a Landing Page setting, that isn't supported in Posts).
					if (convertKitQuickEditField === null) {
						return;
					}

					// Assign the setting's value to the setting's Quick Edit field.
					convertKitQuickEditField.value = element.dataset.value;
				});

			// Bind refresh resource event listeners.
			convertKitRefreshResourcesInitEventListeners();
		};
	}
});
