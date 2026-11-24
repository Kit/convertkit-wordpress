/**
 * Elementor Editor
 *
 * @author ConvertKit
 */

/**
 * Re-renders a Kit widget in the Elementor editor when an Elementor Global Color
 * is selected or removed, as Elementor doesn't re-render widgets for this change.
 *
 * @since 	3.5.4
 */
window.addEventListener('elementor/init', function () {
	elementor.hooks.addAction(
		'panel/open_editor/widget',
		function (panel, model, view) {
			// Bail if this isn't a Kit widget.
			if (!model.get('widgetType').startsWith('convertkit-elementor-')) {
				return;
			}

			const container = view.getContainer();

			// Bail if already listening for changes to this widget.
			if (container.convertKitGlobalsListener) {
				return;
			}
			container.convertKitGlobalsListener = true;

			// Re-render after Elementor has finished updating the widget's settings.
			container.settings.on('change:__globals__', function () {
				setTimeout(function () {
					container.render();
				});
			});
		}
	);
});
