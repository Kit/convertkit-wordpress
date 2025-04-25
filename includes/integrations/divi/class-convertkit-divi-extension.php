<?php
/**
 * Divi Extension class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers Plugin as an extension in Divi.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Divi_Extension extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since   2.5.6
	 *
	 * @var     string
	 */
	public $gettext_domain = 'convertkit';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since   2.5.6
	 *
	 * @var     string
	 */
	public $name = 'convertkit-divi';

	/**
	 * The extension's version.
	 *
	 * @since   2.5.6
	 *
	 * @var     string
	 */
	public $version = '2.5.6';

	/**
	 * Constructor.
	 *
	 * @since   2.5.6
	 *
	 * @param   string $name Extension name.
	 * @param   array  $args Arguments.
	 */
	public function __construct( $name = 'convertkit-divi', $args = array() ) {

		$this->plugin_dir     = CONVERTKIT_PLUGIN_PATH . '/includes/integrations/divi/';
		$this->plugin_dir_url = CONVERTKIT_PLUGIN_URL . 'includes/integrations/divi/';

		// Divi Builder Plugin calls `divi_extensions_init` later, resulting in convertkit_get_blocks() containing data.
		// Using `init` to populate _builder_js_data is too late, so we do this now.
		$this->_builder_js_data = convertkit_get_blocks();

		// Divi Theme calls `divi_extensions_init` earlier, resulting in convertkit_get_blocks() not containing any data.
		// Using `init` to repopulate _builder_js_data resolves.
		add_action( 'init', array( $this, 'init' ) );

		// Call parent construct.
		parent::__construct( $name, $args );

	}

	/**
	 * Store any JS data that can be accessed by builder-bundle.min.js using window.ConvertkitDiviBuilderData.
	 *
	 * @since   2.8.0
	 */
	public function init() {

		$this->_builder_js_data = convertkit_get_blocks();

	}

}

new ConvertKit_Divi_Extension();
