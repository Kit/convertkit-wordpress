<?php
/**
 * Elementor Integration class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers blocks as Elementor Widgets.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Elementor {

	/**
	 * Constructor
	 *
	 * @since   1.9.7.2
	 */
	public function __construct() {

		// Enqueue CSS when using the Elementor editor.
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_styles' ) );

		// Register Widget Category.
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_elementor_widget_categories' ) );

		// Register Blocks as Elementor Widgets.
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );

	}

	/**
	 * Enqueue styles for widget icons.
	 *
	 * @since   1.9.7.2
	 */
	public function enqueue_styles() {

		// Don't load stylesheets if not in editor mode.
		if ( ! filter_has_var( INPUT_GET, 'action' ) || filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) !== 'elementor' ) {
			return;
		}

		// Enqueue styles for block icons.
		wp_enqueue_style(
			'convertkit-elementor',
			CONVERTKIT_PLUGIN_URL . 'resources/backend/css/elementor.css',
			array(),
			CONVERTKIT_PLUGIN_VERSION
		);

	}

	/**
	 * Registers this Plugin's Name as a Category for Elementor Widgets registered
	 * by this Plugin.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   object $elements_manager   Elements Manager.
	 */
	public function register_elementor_widget_categories( $elements_manager ) {

		$elements_manager->add_category(
			'convertkit',
			array(
				'title' => __( 'Kit', 'convertkit' ),
				'icon'  => 'fa fa-plug',
			)
		);

	}

	/**
	 * Registers Blocks as Elementor Widgets.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   Elementor\Widgets_Manager $widgets_manager    Widgets Manager, used to register/unregister Elementor Widgets.
	 */
	public function register_widgets( $widgets_manager ) {

		// Get blocks.
		$blocks = convertkit_get_blocks();

		// Bail if no blocks are available.
		if ( ! count( $blocks ) ) {
			return;
		}

		// Load widget class here, as we know that Elementor is active.
		require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/elementor/class-convertkit-elementor-widget.php';

		// Iterate through blocks, registering them.
		foreach ( $blocks as $block => $properties ) {
			// Skip if this block doesn't have an Elementor Widget class.
			if ( ! file_exists( CONVERTKIT_PLUGIN_PATH . '/includes/integrations/elementor/class-convertkit-elementor-widget-' . $block . '.php' ) ) {
				continue;
			}

			// Load widget class for this block.
			require_once CONVERTKIT_PLUGIN_PATH . '/includes/integrations/elementor/class-convertkit-elementor-widget-' . $block . '.php';
			$class_name = 'ConvertKit_Elementor_Widget_' . str_replace( '-', '_', $block );

			// Register Widget, using applicable function depending on the Elementor version.
			if ( method_exists( $widgets_manager, 'register' ) ) { // @phpstan-ignore-line - older versions of Elementor don't have this method.
				// Use register() function, available in Elementor 3.5.0.
				// Required per https://developers.elementor.com/docs/managers/registering-widgets/.
				$widgets_manager->register( new $class_name() );
			} else {
				// Fallback to register_widget_type(), available in Elementor < 3.5.0.
				$widgets_manager->register_widget_type( new $class_name() );
			}
		}

	}

}
