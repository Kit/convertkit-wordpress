const { registerBlockType } = wp.blocks;
const { InnerBlocks, useBlockProps } = wp.blockEditor;

registerBlockType( 'convertkit/form-builder', {
    edit: () => {
        const blockProps = useBlockProps();

        return wp.element.createElement(
            'div',
            blockProps,
            wp.element.createElement(InnerBlocks)
        );
    },

    save: () => {
        const blockProps = useBlockProps.save();

        console.log( InnerBlocks.Content );

        return wp.element.createElement(
            'div',
            blockProps,
            wp.element.createElement(
                'form',
                {
                    action: '#',
                    method: 'POST',
                },
                wp.element.createElement(InnerBlocks.Content)
            )
        );
    },
} );

