/**
 * Provides the tabbed AI client instructions, and copy to clipboard buttons,
 * on the Settings > Kit > MCP screen.
 *
 * @since   3.4.1
 *
 * @author  ConvertKit
 */

document.addEventListener('DOMContentLoaded', function () {
	convertKitMCPInitClientTabs();
	convertKitMCPInitCopyButtons();
});

/**
 * Displays the configuration for the AI client whose tab is clicked, hiding
 * the configuration for all other AI clients.
 *
 * @since 3.4.1
 */
function convertKitMCPInitClientTabs() {
	document
		.querySelectorAll('.convertkit-mcp-clients')
		.forEach(function (container) {
			const tabs = container.querySelectorAll(
				'button.convertkit-mcp-client-tab'
			);
			const panels = container.querySelectorAll(
				'.convertkit-mcp-client-panel'
			);

			tabs.forEach(function (tab) {
				tab.addEventListener('click', function () {
					const client = tab.dataset.client;

					// Activate the clicked tab, deactivating all other tabs.
					tabs.forEach(function (item) {
						item.classList.toggle(
							'is-active',
							item.dataset.client === client
						);
					});

					// Display the clicked tab's panel, hiding all other panels.
					panels.forEach(function (panel) {
						panel.classList.toggle(
							'is-active',
							panel.dataset.client === client
						);
					});
				});
			});
		});
}

/**
 * Copies the configuration to the clipboard when a Copy button is clicked.
 *
 * @since 3.4.1
 */
function convertKitMCPInitCopyButtons() {
	document
		.querySelectorAll('button.convertkit-mcp-copy')
		.forEach(function (button) {
			button.addEventListener('click', function () {
				const code = button.parentNode.querySelector('pre code');

				// Bail if no code block precedes this button.
				if (!code) {
					return;
				}

				convertKitMCPCopyToClipboard(code.textContent, button);
			});
		});
}

/**
 * Copies the given text to the clipboard, updating the button to tell the user
 * whether copying succeeded.
 *
 * @since 3.4.1
 *
 * @param {string} text   Text to copy to the clipboard.
 * @param {Object} button Button element clicked.
 */
function convertKitMCPCopyToClipboard(text, button) {
	// Use the Clipboard API where it's available. It requires a secure context,
	// which isn't guaranteed on e.g. a development site served over HTTP.
	if (navigator.clipboard && window.isSecureContext) {
		navigator.clipboard.writeText(text).then(
			function () {
				convertKitMCPButtonFeedback(
					button,
					convertkit_admin_settings_mcp.copied
				);
			},
			function () {
				convertKitMCPButtonFeedback(
					button,
					convertkit_admin_settings_mcp.failed
				);
			}
		);
		return;
	}

	// Fall back to copying from a temporary, off screen textarea.
	const textarea = document.createElement('textarea');
	textarea.value = text;
	textarea.setAttribute('readonly', '');
	textarea.style.position = 'absolute';
	textarea.style.left = '-9999px';
	document.body.appendChild(textarea);
	textarea.select();

	let copied = false;
	try {
		copied = document.execCommand('copy');
	} catch (error) {
		copied = false;
	}

	document.body.removeChild(textarea);

	convertKitMCPButtonFeedback(
		button,
		copied
			? convertkit_admin_settings_mcp.copied
			: convertkit_admin_settings_mcp.failed
	);
}

/**
 * Displays the given label on the button for a couple of seconds, before
 * restoring the button's original label.
 *
 * @since 3.4.1
 *
 * @param {Object} button Button element clicked.
 * @param {string} label  Label to display on the button.
 */
function convertKitMCPButtonFeedback(button, label) {
	button.textContent = label;

	setTimeout(function () {
		button.textContent = convertkit_admin_settings_mcp.copy;
	}, 2000);
}
