<?php
/**
 * Divi module loader.
 *
 * Divi automagically loads this file based on the `plugin_dir` defined
 * in the ConvertKit_Divi_Extension class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

// Bail if Divi isn't loaded.
if ( ! class_exists( 'ET_Builder_Element' ) ) {
	return;
}

// Load Divi modules.
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/divi/class-convertkit-divi-module.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/divi/class-convertkit-divi-module-broadcasts.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/divi/class-convertkit-divi-module-form.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/divi/class-convertkit-divi-module-form-trigger.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/divi/class-convertkit-divi-module-product.php';
require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/divi/class-convertkit-divi-module-login.php';
