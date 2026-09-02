/**
 * Provides reusable UI components for the Plugin's administration screens:
 * - inline tabs
 * - copy to clipboard buttons on code blocks
 *
 * @since   3.4.1
 *
 * @author  ConvertKit
 */

document.addEventListener('DOMContentLoaded', function () {
	convertKitInlineTabsInit();
	convertKitCopyButtonsInit();
});

/**
 * Displays the panel belonging to the inline tab that is clicked, hiding all
 * other panels in the same tabbed interface.
 *
 * @since 3.4.1
 */
function convertKitInlineTabsInit() {
	document.querySelectorAll('.kit-inline-tabs').forEach(function (container) {
		const tabs = container.querySelectorAll('button.kit-inline-tab');
		const panels = container.querySelectorAll('.kit-inline-tab-panel');

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				const activeTab = tab.dataset.tab;

				// Activate the clicked tab, deactivating all other tabs.
				tabs.forEach(function (item) {
					item.classList.toggle(
						'is-active',
						item.dataset.tab === activeTab
					);
				});

				// Display the clicked tab's panel, hiding all other panels.
				panels.forEach(function (panel) {
					panel.classList.toggle(
						'is-active',
						panel.dataset.tab === activeTab
					);
				});
			});
		});
	});
}

/**
 * Copies a code block's contents to the clipboard when its Copy button is clicked.
 *
 * @since 3.4.1
 */
function convertKitCopyButtonsInit() {
	document
		.querySelectorAll('button.kit-code-copy')
		.forEach(function (button) {
			button.addEventListener('click', function () {
				const code = button.parentNode.querySelector('pre code');

				// Bail if the button isn't in a code block.
				if (!code) {
					return;
				}

				convertKitCopyToClipboard(code.textContent, button);
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
function convertKitCopyToClipboard(text, button) {
	// Use the Clipboard API where it's available. It requires a secure context,
	// which isn't guaranteed on e.g. a development site served over HTTP.
	if (navigator.clipboard && window.isSecureContext) {
		navigator.clipboard.writeText(text).then(
			function () {
				convertKitCopyButtonFeedback(button, convertkit_ui.copied);
			},
			function () {
				convertKitCopyButtonFeedback(button, convertkit_ui.failed);
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

	convertKitCopyButtonFeedback(
		button,
		copied ? convertkit_ui.copied : convertkit_ui.failed
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
function convertKitCopyButtonFeedback(button, label) {
	button.textContent = label;

	setTimeout(function () {
		button.textContent = convertkit_ui.copy;
	}, 2000);
}
