/**
 * Preview Output Wizard
 *
 * @author ConvertKit
 */

/**
 * Appends a <select> field value to a link, to build a preview link for e.g. forms.
 *
 * @since 	1.9.8.5
 */
jQuery(document).ready(function ($) {
	// Appends a <select> field value to a link. Used for previews.
	$('select.convertkit-preview-output-link')
		.on('change', function () {
			const target = $(this).data('target'),
				link = $(this).data('link') + $(this).val();

			$(target).attr('href', link);
		})
		.trigger('change');
});
