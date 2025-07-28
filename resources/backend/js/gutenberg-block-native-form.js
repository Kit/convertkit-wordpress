/**
 * Native Form Block specific functions for Gutenberg.
 *
 * @since   3.0.0
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Custom callback function to render the ConvertKit Native Form Block preview in the Gutenberg Editor.
 *
 * @since 	3.0.0
 */
function convertKitGutenbergNativeFormBlockRenderPreview( block, props ) {

	// Use the block's PHP's render() function by calling the ServerSideRender component.
	return wp.element.createElement(
		wp.serverSideRender,
		{
			block: 'convertkit/' + block.name,
			attributes: props.attributes,

			// This is only output in the Gutenberg editor, so must be slightly different from the inner class name used to
			// apply styles with i.e. convertkit-block.name.
			className: 'convertkit-ssr-' + block.name,
		}
	);

}
