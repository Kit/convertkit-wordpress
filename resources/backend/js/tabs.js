/**
 * Provides functionality for tabbed interfaces.
 *
 * @since   2.2.5
 *
 * @author  ConvertKit
 */

/* eslint-disable no-unused-vars */
/**
 * Initializes tabbed interfaces.
 *
 * @since 2.2.5
 */
function convertKitTabsInit() {
	// eslint-disable-line no-unused-vars
	// Safely call this function by destroying any previously initialized instances.
	convertKitTabsDestroy();

	// Iterate through all JS tab instances, initializing each one.
	document
		.querySelectorAll('.convertkit-js-tabs')
		.forEach(function (navTabContainer) {
			const navTabPanelsContainer =
				navTabContainer.dataset.panelsContainer;
			const navTabPanel = navTabContainer.dataset.panel;
			const navTabActive = navTabContainer.dataset.active;

			// Call update.
			const activeTabElement = navTabContainer.querySelector(
				'a.' + navTabActive
			);
			convertKitTabsUpdate(
				navTabContainer,
				navTabPanelsContainer,
				navTabPanel,
				navTabActive,
				activeTabElement ? activeTabElement.getAttribute('href') : null
			);

			// Register a listener when a tab is clicked.
			navTabContainer.addEventListener('click', function (e) {
				if (e.target.tagName === 'A') {
					e.preventDefault();

					// Update the UI to show the active tab and content associated with it.
					convertKitTabsUpdate(
						navTabContainer,
						navTabPanelsContainer,
						navTabPanel,
						navTabActive,
						e.target.getAttribute('href')
					);
				}
			});
		});
}
/* eslint-enable no-unused-vars */

/**
 * For the given active tab:
 * - Hides all other content in the group
 * - Shows content associated with the active tab
 *
 * @since 1.0.0
 *
 * @param {Object} navTabContainer       <ul> Navigation Tab Container.
 * @param {string} navTabPanelsContainer ID of element containing content panel, to display, which associate with the navigation tabs.
 * @param {string} navTabPanel           Class of elements containing content panels, to hide, which associate with the navigation tabs.
 * @param {string} navTabActive          Class to apply to the clicked activeTab element.
 * @param {string} activeTab             ID of <a> tab which has been selected / clicked.
 */
function convertKitTabsUpdate(
	navTabContainer,
	navTabPanelsContainer,
	navTabPanel,
	navTabActive,
	activeTab
) {
	// If we don't have an active tab at this point, we don't have any tabs, so bail.
	if (
		typeof activeTab === 'undefined' ||
		activeTab === null ||
		activeTab.length === 0
	) {
		return;
	}

	// Deactivate all tabs in this container.
	navTabContainer
		.querySelectorAll('a')
		.forEach((tab) => tab.classList.remove(navTabActive));

	// Hide all panels in this container.
	document
		.querySelectorAll(navTabPanelsContainer + ' ' + navTabPanel)
		.forEach((panel) => (panel.style.display = 'none'));

	// Activate the clicked / selected tab in this container.
	navTabContainer
		.querySelector('a[href="' + activeTab + '"]')
		.classList.add(navTabActive);

	// Show the active tab's panels in this container.
	document.querySelector(activeTab).style.display = 'block';
}

/**
 * Destroys previously initialized tabbed interfaces, no longer
 * listening to events.
 *
 * @since 1.0.0
 */
function convertKitTabsDestroy() {
	// Iterate through all JS tab instances, destroying each one.
	document
		.querySelectorAll('.convertkit-js-tabs')
		.forEach(function (tabInstance) {
			// Remove the click event listener.
			tabInstance.removeEventListener('click', tabInstance.clickHandler);
			// Remove the clickHandler property.
			delete tabInstance.clickHandler;
		});
}
