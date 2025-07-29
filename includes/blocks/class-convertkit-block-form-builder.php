<?php
/**
 * Kit Form Builder Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Kit Form Builder Block for Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Form_Builder  {

	/**
	 * Constructor
	 *
	 * @since   3.0.0
	 */
	public function __construct() {

		// Register block.
		add_action( 'init', function() {

			register_block_type(
				CONVERTKIT_PLUGIN_PATH . '/includes/blocks/form-builder',
			);

		} );

		add_action( 'admin_enqueue_scripts', function() {

			wp_enqueue_script( 
				'convertkit-form-builder', 
				CONVERTKIT_PLUGIN_URL . 'includes/blocks/form-builder/editor.js', 
				array( 
					'wp-element',
					'wp-blocks',
					'wp-block-editor'
				), 
				CONVERTKIT_PLUGIN_VERSION, 
				true 
			);
			
		} );

	}

}
