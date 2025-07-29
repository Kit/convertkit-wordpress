const { registerBlockType }          = wp.blocks;
const { InnerBlocks, useBlockProps } = wp.blockEditor;
const { createElement }              = wp.element;
const convertKitFormBuilderTemplate  = [
	[ 'core/image', {} ],
	[ 'core/heading', { placeholder: 'Book Title' } ],
	[ 'core/paragraph', { placeholder: 'Summary' } ],
];


registerBlockType(
	'convertkit/form-builder',
	{
		edit: () => {
			const blockProps = useBlockProps();

			return createElement(
				'div',
				blockProps,
				createElement(
					InnerBlocks,
					{
						template: convertKitFormBuilderTemplate
						// templateLock: 'all'
					}
				)
			);
		},

		save: () => {
			const blockProps = useBlockProps.save();

			return createElement(
				'div',
				blockProps,
				createElement(
					'form',
					{
						action: '#',
						method: 'POST',
					},
					createElement( InnerBlocks.Content )
				)
			);
		},
	}
);
