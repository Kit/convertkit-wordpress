/**
 * Initializes Select2 for <select> dropdowns.
 *
 * @since   1.9.6.4
 *
 * @author ConvertKit
 */

/**
 * Initializes Select2 for <select> dropdowns.
 *
 * @since 	1.9.6.4
 */
function convertKitSelect2Init() {
	(function ($) {
		$('.convertkit-select2').select2();
	})(jQuery);
}

jQuery(document).ready(function () {
	convertKitSelect2Init();
});
