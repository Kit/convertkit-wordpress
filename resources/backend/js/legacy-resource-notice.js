/**
 * Renders a non-dismissible warning notice inside Gutenberg
 * when a post/page references a legacy Kit resource.
 *
 * @author ConvertKit
 * @since  3.3.7
 */

document.addEventListener('DOMContentLoaded', function () {
	// Bail if the notice data is not available.
	if (typeof convertkit_legacy_resource_notice === 'undefined') {
		return;
	}

	// Bail if no warnings are found.
	if (
		typeof convertkit_legacy_resource_notice.warnings === 'undefined' ||
		convertkit_legacy_resource_notice.warnings.length === 0
	) {
		return;
	}

	// Bail if Gutenberg is not available.
	if (
		typeof wp === 'undefined' ||
		typeof wp.data === 'undefined' ||
		typeof wp.data.dispatch !== 'function'
	) {
		return;
	}

	// Build the notice.
	const message =
		convertkit_legacy_resource_notice.intro +
		'\n' +
		convertkit_legacy_resource_notice.warnings
			.map(function (warning) {
				return '- ' + warning;
			})
			.join('\n');

	// Create the notice.
	wp.data.dispatch('core/notices').createWarningNotice(message, {
		id: convertkit_legacy_resource_notice.id,
		isDismissible: false,
	});
});
