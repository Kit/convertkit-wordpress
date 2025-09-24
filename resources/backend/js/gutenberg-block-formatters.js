/**
 * Registers formatters in the Block Toolbar in the Gutenberg editor.
 *
 * @since   2.2.0
 *
 * @author ConvertKit
 */

/**
 * @typedef {import('@wordpress/element').WPElement} WPElement
 */

// Register Gutenberg Block Toolbar formatters if the Gutenberg Editor is loaded on screen.
// This prevents JS errors if this script is accidentally enqueued on a non-
// Gutenberg editor screen, or the Classic Editor Plugin is active.
if (typeof wp !== 'undefined' && typeof wp.blockEditor !== 'undefined') {
	// Register each ConvertKit formatter in Gutenberg.
	for (const formatter in convertkit_block_formatters) {
		convertKitGutenbergRegisterBlockFormatter(
			convertkit_block_formatters[formatter]
		);
	}
}

/**
 * Registers the given formatter in Gutenberg.
 *
 * @since   2.2.0
 *
 * @param {Object} formatter Block formatter.
 */
function convertKitGutenbergRegisterBlockFormatter(formatter) {
	(function (editor, richText, element, components) {
		// Define the Gutenberg/React components to use.
		const { Fragment, useState, createElement } = element;
		const {
			registerFormatType,
			toggleFormat,
			applyFormat,
			useAnchorRef,
			useAnchor,
		} = richText;
		const { RichTextToolbarButton } = editor;
		const { Popover, SelectControl } = components;

		/**
		 * Returns the icon to display in the block toolbar for this formatter, depending
		 * on the supplied formatter's configuration.
		 *
		 * @since   2.2.0
		 *
		 * @return  {WPElement|string} Either a WordPress element (RawHTML) or a dashicon string.
		 */
		const getIcon = function () {
			// Return a fallback default icon if none is specified for this block formatter.
			if (typeof formatter.gutenberg_icon === 'undefined') {
				return 'dashicons-tablet';
			}

			// Return HTML element if the icon is an SVG string.
			if (formatter.gutenberg_icon.search('svg') >= 0) {
				return element.RawHTML({
					children: formatter.gutenberg_icon,
				});
			}

			// Just return the string, as it's a dashicon CSS class.
			return formatter.gutenberg_icon;
		};

		/**
		 * Returns an object of this formatter's attributes if this formatter
		 * has been used on the selected text.
		 *
		 * Returns blank attributes if this formatter has not been used on the
		 * selected text.
		 *
		 * @since   2.2.0
		 *
		 * @param {Object} activeFormats All active formatters applied to the selected text.
		 * @return {Object} Attributes.
		 */
		const getAttributes = function (activeFormats) {
			// Define the attribute object.
			const attributes = {};
			for (const attribute in formatter.attributes) {
				attributes[attribute] = '';
			}

			// Return if no active formats have been applied to the selected text.
			if (typeof activeFormats === 'undefined') {
				return attributes;
			}

			// Return if this formatter has not been used on the selected text.
			const formats = activeFormats.filter(
				(format) => 'convertkit/' + formatter.name === format.type
			);
			if (formats.length === 0) {
				return attributes;
			}

			// This formatter has been applied to the selected text.
			// Build the object of attributes and return.
			for (const attribute in formatter.attributes) {
				if (typeof formats[0].unregisteredAttributes !== 'undefined') {
					attributes[attribute] =
						formats[0].unregisteredAttributes[attribute];
				} else if (typeof formats[0].attributes !== 'undefined') {
					attributes[attribute] = formats[0].attributes[attribute];
				}
			}

			return attributes;
		};

		/**
		 * Updates the block formatter's attributes when a field in the
		 * formatter's popover modal has its value changed.
		 *
		 * @since   2.2.0
		 *
		 * @param {Object} props    Block formatter properties.
		 * @param {Object} field    Field definition.
		 * @param {string} newValue New value.
		 */
		const setAttributes = function (props, field, newValue) {
			// Define properties and functions to use.
			const { onChange, value } = props;

			// If no value exists, remove the formatter.
			if (newValue === '') {
				return onChange(
					toggleFormat(value, {
						type: 'convertkit/' + formatter.name,
					})
				);
			}

			// Build object of new attributes.
			const attributes = {};
			for (const attribute in formatter.attributes) {
				// If 'None' selected, blank the attribute's value.
				if (newValue === '') {
					attributes[attribute] = '';
				} else {
					attributes[attribute] = field.data[newValue][attribute];
				}
			}

			// Apply formatter with new attributes.
			return onChange(
				applyFormat(value, {
					type: 'convertkit/' + formatter.name,
					attributes,
				})
			);
		};

		/**
		 * Return an array of field elements to display in the popover modal when
		 * this formatter is active.
		 *
		 * @since   2.2.0
		 *
		 * @param {Object}   props          Block formatter properties.
		 * @param {Function} setShowPopover Function to toggle showing/hiding the popover.
		 * @param {Object}   attributes     Field attributes.
		 * @return  {Array}                   Field elements
		 */
		const getFields = function (props, setShowPopover, attributes) {
			// Define array of field elements.
			const elements = [];

			// Return if no fields exist.
			if (formatter.fields.length === 0) {
				return elements;
			}

			// Iterate through the formatter's fields, adding a field element for each.
			for (const fieldName in formatter.fields) {
				const field = formatter.fields[fieldName];

				// Build options for <select> input.
				const fieldOptions = [
					{
						label: '(None)',
						value: '',
					},
				];
				for (const fieldValue in field.values) {
					fieldOptions.push({
						label: field.values[fieldValue],
						value: fieldValue,
					});
				}

				// Sort field's options alphabetically by label.
				fieldOptions.sort(function (x, y) {
					const a = x.label.toUpperCase(),
						b = y.label.toUpperCase();
					return a.localeCompare(b);
				});

				// Add field to array.
				elements.push(
					createElement(SelectControl, {
						key: 'convertkit_' + formatter.name + '_' + fieldName,
						id: 'convertkit-' + formatter.name + '-' + fieldName,
						label: field.label,
						value: attributes[fieldName],
						help: field.description,
						options: fieldOptions,
						onChange(newValue) {
							// Hide popover.
							setShowPopover(false);

							// Update and apply attributes to the selected text.
							setAttributes(props, field, newValue);
						},
					})
				);
			}

			// Return elements.
			return elements;
		};

		/**
		 * Display modal when the formatter's button is clicked, and save
		 * changes that are made.
		 *
		 * @since   2.2.0
		 *
		 * @param {Object} props Block formatter properties.
		 * @return  {Object}          Block formatter button and modal elements
		 */
		const EditFormatType = function (props) {
			// Get props.
			const { contentRef, isActive, value } = props;
			const { activeFormats } = value;
			let anchorRef;

			// Get anchor reference to the text.
			if (typeof useAnchor === 'undefined') {
				// Use WordPress 6.0 and lower useAnchorRef(), as useAnchor() isn't available.
				anchorRef = useAnchorRef({ ref: contentRef, value }); // eslint-disable-line react-hooks/rules-of-hooks
			} else {
				// Use WordPress 6.1+ useAnchor(), as useAnchorRef() is deprecated in 6.2+.
				// eslint-disable-next-line react-hooks/rules-of-hooks
				anchorRef = useAnchor({
					editableContentElement: contentRef.current,
					value,
				});
			}

			// State to show popover.
			const [showPopover, setShowPopover] = useState(false);

			// Get attributes.
			const attributes = getAttributes(activeFormats);

			// Define fields to display in the popover modal.
			const popoverModalElements = getFields(
				props,
				setShowPopover,
				attributes
			);

			// Return block toolbar button and its modal.
			return createElement(
				Fragment,
				{
					key:
						'convertkit_' +
						formatter.name +
						'_rich_text_toolbar_fragment',
				},
				// Register the button in the rich text toolbar.
				createElement(RichTextToolbarButton, {
					key:
						'convertkit_' +
						formatter.name +
						'_rich_text_toolbar_button',
					icon: getIcon(formatter),
					title: formatter.title,
					isActive,
					onClick() {
						setShowPopover(true);
					},
				}),
				// Popover which displays fields when the button is active.
				showPopover &&
					createElement(
						Popover,
						{
							key: 'convertkit_' + formatter.name + '_popover',
							className: 'convertkit-popover',
							anchor: anchorRef,
							onClose() {
								setShowPopover(false);
							},
						},
						popoverModalElements
					)
			);
		};

		// Register Format Type.
		registerFormatType('convertkit/' + formatter.name, {
			title: formatter.title,

			// The tagName and className combination allow Gutenberg to uniquely identify
			// whether this formatter has been used on the selected text.
			tagName: formatter.tag,
			className: 'convertkit-' + formatter.name,
			attributes: formatter.attributes,
			edit: EditFormatType,
		});
	})(
		window.wp.blockEditor,
		window.wp.richText,
		window.wp.element,
		window.wp.components
	);
}
