/**
 * Registers blocks in the Gutenberg editor.
 *
 * @since   1.9.6.5
 *
 * @author ConvertKit
 */

/**
 * @typedef {import('@wordpress/element').WPElement} WPElement
 */

// Register Gutenberg Blocks if the Gutenberg Editor is loaded on screen.
// This prevents JS errors if this script is accidentally enqueued on a non-
// Gutenberg editor screen, or the Classic Editor Plugin is active.
if (typeof wp !== 'undefined' && typeof wp.blockEditor !== 'undefined') {
	// Register each ConvertKit Block in Gutenberg.
	for (const block in convertkit_blocks) {
		convertKitGutenbergRegisterBlock(convertkit_blocks[block]);
	}

	// Register ConvertKit Pre-publish actions in Gutenberg.
	if (typeof convertkit_pre_publish_actions !== 'undefined') {
		convertKitGutenbergRegisterPrePublishActions(
			convertkit_pre_publish_actions
		);
	}
}

/**
 * Registers the given block in Gutenberg.
 *
 * @since 	1.9.6.5
 *
 * @param {Object} block Block.
 */
function convertKitGutenbergRegisterBlock(block) {
	(function (blocks, editor, element, components) {
		// Define some constants for the various items we'll use.
		const el = element.createElement;
		const { registerBlockType } = blocks;
		const { InspectorControls, InnerBlocks } = editor;
		const { Fragment, useState } = element;
		const {
			Button,
			Icon,
			TextControl,
			SelectControl,
			ToggleControl,
			Flex,
			FlexItem,
			PanelBody,
			PanelRow,
			ProgressBar,
		} = components;

		/**
		 * Returns the icon to display for this block, depending
		 * on the supplied block's configuration.
		 *
		 * @since   2.2.0
		 *
		 * @return  {WPElement|string} Either a WordPress element (RawHTML) or a dashicon string.
		 */
		const getIcon = function () {
			// Return a fallback default icon if none is specified for this block.
			if (typeof block.gutenberg_icon === 'undefined') {
				return 'dashicons-tablet';
			}

			// Return HTML element if the icon is an SVG string.
			if (block.gutenberg_icon.search('svg') >= 0) {
				return element.RawHTML({
					children: block.gutenberg_icon,
				});
			}

			// Just return the string, as it's a dashicon CSS class.
			return block.gutenberg_icon;
		};

		/**
		 * Return a field element for the block sidebar, which is displayed in a panel's row
		 * when this block is being edited.
		 *
		 * @since   2.2.0
		 *
		 * @param {Object} props     Block properties.
		 * @param {Object} field     Field attributes.
		 * @param {string} attribute Attribute name to store the field's data in.
		 * @return {Object}            Field element.
		 */
		const getField = function (props, field, attribute) {
			// Define some field properties shared across all field types.
			const fieldProperties = {
				id:
					'convertkit_' +
					block.name.replace(/-/g, '_') +
					'_' +
					attribute,
				label: field.label,
				help: field.description,
				value: props.attributes[attribute],
				onChange(value) {
					if (field.type === 'number') {
						// If value is a blank string i.e. no attribute value was provided,
						// cast it to the field's minimum number setting.
						// This prevents WordPress' block renderer API returning a 400 error
						// because a blank value will be passed as a string, when WordPress
						// expects it to be a numerical value.
						if (value === '') {
							value = field.min;
						}

						// Cast value to integer if a value exists.
						if (value.length > 0) {
							value = Number(value);
						}
					}

					const newValue = {};
					newValue[attribute] = value;
					props.setAttributes(newValue);
				},
			};

			const fieldOptions = [];

			// Define additional Field Properties and the Field Element,
			// depending on the Field Type (select, textarea, text etc).
			switch (field.type) {
				case 'select':
					// Build options for <select> input.
					fieldOptions.push({
						label: '(None)',
						value: '',
					});
					for (const value of Object.keys(field.values)) {
						fieldOptions.push({
							label: field.values[value],
							value,
						});
					}

					// Sort field's options alphabetically by label.
					fieldOptions.sort(function (x, y) {
						const a = x.label.toUpperCase(),
							b = y.label.toUpperCase();
						return a.localeCompare(b);
					});

					// Assign options to field.
					fieldProperties.options = fieldOptions;

					// Return field element.
					return el(SelectControl, fieldProperties);

				case 'resource':
					// Build options for <select> input.
					fieldOptions.push({
						label: '(None)',
						value: '',
					});
					for (const value of Object.keys(field.values)) {
						fieldOptions.push({
							label: field.values[value],
							value,
						});
					}

					// Sort field's options alphabetically by label.
					fieldOptions.sort(function (x, y) {
						const a = x.label.toUpperCase(),
							b = y.label.toUpperCase();
						return a.localeCompare(b);
					});

					// Assign options to field.
					fieldProperties.options = fieldOptions;

					return el(
						Flex,
						{
							align: 'start',
						},
						[
							el(
								FlexItem,
								{},
								el(SelectControl, fieldProperties)
							),
							el(FlexItem, {}, inlineRefreshButton(props)),
						]
					);

				case 'toggle':
					// Define field properties.
					fieldProperties.checked = props.attributes[attribute];

					// Return field element.
					return el(ToggleControl, fieldProperties);

				case 'number':
					// Define field properties.
					fieldProperties.type = field.type;
					fieldProperties.min = field.min;
					fieldProperties.max = field.max;
					fieldProperties.step = field.step;

					// Return field element.
					return el(TextControl, fieldProperties);

				default:
					// Return field element.
					return el(TextControl, fieldProperties);
			}
		};

		/**
		 * Return an array of rows to display in the given block sidebar's panel when
		 * this block is being edited.
		 *
		 * @since   2.2.0
		 *
		 * @param {Object} props Block properties.
		 * @param {string} panel Panel name.
		 * @return {Array}        Panel rows.
		 */
		const getPanelRows = function (props, panel) {
			// Build Inspector Control Panel Rows, one for each Field.
			const rows = [];
			for (const i in block.panels[panel].fields) {
				const attribute = block.panels[panel].fields[i], // e.g. 'term'.
					field = block.fields[attribute]; // field array.

				// If this field doesn't exist as an attribute in the block's get_attributes(),
				// this is a non-Gutenberg field (such as a color picker for shortcodes),
				// which should be ignored.
				if (typeof block.attributes[attribute] === 'undefined') {
					continue;
				}

				rows.push(
					el(
						PanelRow,
						{
							key: attribute,
						},
						getField(props, field, attribute)
					)
				);
			}

			return rows;
		};

		/**
		 * Return an array of panels to display in the block's sidebar when the block
		 * is being edited.
		 *
		 * @since   2.2.0
		 *
		 * @param {Object} props Block formatter properties.
		 * @return {Array} 	      Block sidebar panels.
		 */
		const getPanels = function (props) {
			const panels = [];
			let initialOpen = true;

			// Build Inspector Control Panels.
			for (const panel in block.panels) {
				const panelRows = getPanelRows(props, panel);

				// If no panel rows exist (e.g. this is a shortcode only panel,
				// for styles, which Gutenberg registers in its own styles tab),
				// don't add this panel.
				if (!panelRows.length) {
					continue;
				}

				panels.push(
					el(
						PanelBody,
						{
							title: block.panels[panel].label,
							key: panel,
							initialOpen,
						},
						panelRows
					)
				);

				// Don't open any further panels.
				initialOpen = false;
			}

			return panels;
		};

		/**
		 * Display settings sidebar when the block is being edited, and save
		 * changes that are made.
		 *
		 * @since   2.2.0
		 *
		 * @param {Object} props Block properties.
		 * @return {Object}       Block settings sidebar elements.
		 */
		const editBlock = function (props) {
			// If requesting an example of how this block looks (which is requested
			// when the user adds a new block and hovers over this block's icon),
			// show the preview image.
			if (props.attributes.is_gutenberg_example === true) {
				return (
					Fragment,
					{},
					el('img', {
						src: block.gutenberg_example_image,
					})
				);
			}

			// If no access token has been defined in the Plugin, or no resources exist in Kit
			// for this block, show a message in the block to tell the user what to do.
			if (!block.has_access_token || !block.has_resources) {
				return DisplayNoticeWithLink(props);
			}

			// Build Inspector Control Panels, which will appear in the Sidebar when editing the Block.
			const panels = getPanels(props);

			// Generate Block Preview.
			let preview = '';

			// If a custom callback function to render this block's preview in the Gutenberg Editor
			// has been defined, use it.
			// This doesn't affect the output for this block on the frontend site, which will always
			// use the block's PHP's render() function.
			if (
				typeof block.gutenberg_preview_render_callback !== 'undefined'
			) {
				preview = window[block.gutenberg_preview_render_callback](
					block,
					props
				);
				return editBlockWithPanelsAndPreview(panels, preview);
			}

			// If no settings have been defined for this block, render the block with a notice
			// with instructions on how to configure the block.
			if (
				typeof block.gutenberg_help_description_attribute !==
					'undefined' &&
				props.attributes[block.gutenberg_help_description_attribute] ===
					''
			) {
				preview = convertKitGutenbergDisplayBlockNotice(
					block.name,
					block.gutenberg_help_description
				);
				return editBlockWithPanelsAndPreview(panels, preview);
			}

			// If no render_callback is defined, render the block.
			if (typeof block.gutenberg_template !== 'undefined') {
				// Build template for the new block.
				const template = [];
				for (const templateBlockName in block.gutenberg_template) {
					if (
						block.gutenberg_template.hasOwnProperty(
							templateBlockName
						)
					) {
						template.push([
							templateBlockName,
							block.gutenberg_template[templateBlockName],
						]);
					}
				}

				preview = el(
					'div',
					{},
					el(InnerBlocks, {
						template,
					})
				);
				return editBlockWithPanelsAndPreview(panels, preview);
			}

			// Use the block's PHP's render() function by calling the ServerSideRender component.
			preview = el(wp.serverSideRender, {
				block: 'convertkit/' + block.name,
				attributes: props.attributes,

				// This is only output in the Gutenberg editor, so must be slightly different from the inner class name used to
				// apply styles with i.e. convertkit-block.name.
				className: 'convertkit-ssr-' + block.name,
			});
			return editBlockWithPanelsAndPreview(panels, preview);
		};

		/**
		 * Display settings sidebar when the block is being edited, and save
		 * changes that are made.
		 *
		 * @since   3.0.0
		 *
		 * @param {Object} panels  Block panels.
		 * @param {Object} preview Block preview.
		 * @return {Object}         Block settings sidebar elements.
		 */
		const editBlockWithPanelsAndPreview = function (panels, preview) {
			return el(
				// Sidebar Panel with Fields.
				Fragment,
				{},
				el(InspectorControls, {}, panels),
				// Block Preview.
				preview
			);
		};

		/**
		 * Save the block's content.
		 *
		 * @since   3.0.0
		 *
		 * @return {Object}       Block content.
		 */
		const saveBlock = function () {
			if (typeof block.gutenberg_template !== 'undefined') {
				return el('div', {}, el(InnerBlocks.Content));
			}

			// Deliberate; preview in the editor is determined by the return statement in `edit` above.
			// On the frontend site, the block's render() PHP class is always called, so we dynamically
			// fetch the content.
			return null;
		};

		/**
		 * Display a notice in the block with a clickable link to perform an action, and a refresh
		 * button to trigger editBlock().  Typically used when no API key exists in the Plugin,
		 * or no resources (forms, products) exist in ConvertKit.
		 *
		 * @since 	2.2.5
		 *
		 * @param {Object} props Block properties.
		 * @return {Object}       Notice.
		 */
		const DisplayNoticeWithLink = function (props) {
			// useState to toggle the refresh button's disabled state.
			const [buttonDisabled, setButtonDisabled] = useState(false);

			// Holds the array of elements to display in the notice component.
			let elements;

			// Define elements to display, based on whether the refresh button is disabled.
			if (buttonDisabled) {
				// Refresh button disabled; display a loading indicator and the button.
				elements = [
					loadingIndicator(props),
					refreshButton(props, buttonDisabled, setButtonDisabled),
				];
			} else {
				// Refresh button enabled; display the notice, link and button.
				elements = [
					!block.has_access_token
						? block.no_access_token.notice
						: block.no_resources.notice,
					noticeLink(props, setButtonDisabled),
					refreshButton(props, buttonDisabled, setButtonDisabled),
				];
			}

			// Return the element.
			return el(
				'div',
				{
					// convertkit-no-content class allows resources/backend/css/gutenberg.css
					// to apply styling/branding to the block.
					className:
						'convertkit-' + block.name + ' convertkit-no-content',
				},
				elements
			);
		};

		/**
		 * Returns an indeterminate progress bar element, to show that a block is loading / refreshing.
		 *
		 * @since 	2.2.6
		 *
		 * @param {Object} props Block properties.
		 * @return {Object}       Progress Bar.
		 */
		const loadingIndicator = function (props) {
			return el(ProgressBar, {
				key: props.clientId + '-progress-bar',
				className: 'convertkit-progress-bar',
			});
		};

		/**
		 * Returns a WordPress Icon element.
		 *
		 * @since 	2.7.7
		 *
		 * @param {string} iconName Icon Name.
		 * @return {Object} 		 Icon.
		 */
		const iconType = function (iconName) {
			return el(Icon, {
				icon: iconName,
			});
		};

		/**
		 * Returns the notice link for the DisplayNoticeWithLink element.
		 *
		 * @since 	2.2.6
		 *
		 * @param {Object}   props             Block properties.
		 * @param {Function} setButtonDisabled Function to enable or disable the refresh button.
		 * @return {Object}          			Notice Link.
		 */
		const noticeLink = function (props, setButtonDisabled) {
			// Get the URL to set the button to.
			const url = !block.has_access_token
				? block.no_access_token.link
				: block.no_resources.link;

			return el(
				Button,
				{
					key: props.clientId + '-notice-link',
					className: !block.has_access_token
						? 'convertkit-block-modal'
						: '',
					variant: 'link',
					onClick(e) {
						e.preventDefault();

						// Show popup window with setup wizard if we need to connect via OAuth.
						if (!block.has_access_token) {
							showConvertKitPopupWindow(
								props,
								url,
								setButtonDisabled
							);
							return;
						}

						// Allow the link to load, as it's likely a link to the Kit site.
						window.open(url, '_blank');
					},
				},
				!block.has_access_token
					? block.no_access_token.link_text
					: block.no_resources.link_text
			);
		};

		/**
		 * Returns a refresh button, used to refresh a block when it has no API Keys
		 * or resources.
		 *
		 * @since 	2.2.6
		 *
		 * @param {Object}   props             Block properties.
		 * @param {boolean}  buttonDisabled    Whether the refresh button is disabled (true) or enabled (false)
		 * @param {Function} setButtonDisabled Function to enable or disable the refresh button.
		 * @return {Object} 					Button.
		 */
		const refreshButton = function (
			props,
			buttonDisabled,
			setButtonDisabled
		) {
			return el(Button, {
				key: props.clientId + '-refresh-button',
				className:
					'wp-convertkit-refresh-resources' +
					(buttonDisabled ? ' is-refreshing' : ''),
				disabled: buttonDisabled,
				text: 'Refresh',
				icon: iconType('update'),
				variant: 'secondary',
				onClick() {
					// Refresh block definitions.
					refreshBlocksDefinitions(props, setButtonDisabled);
				},
			});
		};

		/**
		 * Returns an inline refresh button, used to refresh a block's resources.
		 *
		 * @since 	2.7.1
		 *
		 * @param {Object} props Block properties.
		 * @return {Object} 	  Button.
		 */
		const inlineRefreshButton = function (props) {
			return el(BlockInlineRefreshButton, props);
		};

		/**
		 * Returns a refresh button.
		 *
		 * @since 	2.7.1
		 *
		 * @param {Object} props Block properties.
		 * @return {Object} 	  Button.
		 */
		const BlockInlineRefreshButton = function (props) {
			const [buttonDisabled, setButtonDisabled] = useState(false);

			return el(Button, {
				key: props.clientId + '-refresh-button',
				className:
					'button button-secondary wp-convertkit-refresh-resources' +
					(buttonDisabled ? ' is-refreshing' : ''),
				disabled: buttonDisabled,
				icon: iconType('update'),
				onClick() {
					// Refresh block definitions.
					refreshBlocksDefinitions(props, setButtonDisabled);
				},
			});
		};

		/**
		 * Displays a new window with a given width and height to display the given URL.
		 *
		 * Typically used for displaying a modal version of the Setup Wizard, where the
		 * user clicks the 'Click here to connect your ConvertKit account' link in a block, and then
		 * enters their API Key and Secret.  Will be used to show the ConvertKit
		 * OAuth window in the future.
		 *
		 * @since 	2.2.6
		 *
		 * @param {Object}   props             Block properties.
		 * @param {string}   url               URL to display in the popup window.
		 * @param {Function} setButtonDisabled Function to enable or disable the refresh button.
		 */
		const showConvertKitPopupWindow = function (
			props,
			url,
			setButtonDisabled
		) {
			// Define popup width, height and positioning.
			const width = 640,
				height = 750,
				top = (window.screen.height - height) / 2,
				left = (window.screen.width - width) / 2;

			// Open popup.
			const convertKitPopup = window.open(
				url + '&convertkit-modal=1',
				'convertkit_popup_window',
				'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' +
					width +
					',height=' +
					height +
					',top=' +
					top +
					',left=' +
					left
			);

			// Center popup and focus.
			convertKitPopup.moveTo(left, top);
			convertKitPopup.focus();

			// Refresh the block when the popup is closed using self.close().
			// Won't fire if the user closes the popup manually, which is fine because that means
			// they didn't complete the steps, so refreshing wouldn't show anything new.
			// The onbeforeunload would seem suitable here, but it fires whenever the popup window's
			// document changes (e.g. as the user steps through a wizard), and doesn't fire when
			// the window is closed.
			// See https://stackoverflow.com/questions/9388380/capture-the-close-event-of-popup-window-in-javascript/48240128#48240128.
			const convertKitPopupTimer = setInterval(function () {
				if (convertKitPopup.closed) {
					clearInterval(convertKitPopupTimer);

					// Refresh block.
					refreshBlocksDefinitions(props, setButtonDisabled);
				}
			}, 1000);
		};

		/**
		 * Refreshes this block's properties by:
		 * - making an AJAX call to fetch all registered blocks via convertkit_get_blocks(),
		 * - storing the registered blocks in the `convertkit_blocks` global object,
		 * - updating this block's properties by updating the `block` object.
		 *
		 * @since 	2.2.6
		 *
		 * @param {Object}   props             Block properties.
		 * @param {Function} setButtonDisabled Function to enable or disable the refresh button.
		 */
		const refreshBlocksDefinitions = function (props, setButtonDisabled) {
			// Define data for WordPress AJAX request.
			const data = new FormData();
			data.append('action', 'convertkit_get_blocks');
			data.append('nonce', convertkit_gutenberg.get_blocks_nonce);

			// Disable the button.
			if (typeof setButtonDisabled !== 'undefined') {
				setButtonDisabled(true);
			}

			// Send AJAX request.
			fetch(ajaxurl, {
				method: 'POST',
				credentials: 'same-origin',
				body: data,
			})
				.then(function (response) {
					// Convert response JSON string to object.
					return response.json();
				})
				.then(function (response) {
					// Update global ConvertKit Blocks object, so that any updated resources
					// are reflected when adding new ConvertKit Blocks.
					convertkit_blocks = response.data;

					// Update this block's properties, so that has_access_token, has_resources
					// and the resources properties are updated.
					block = convertkit_blocks[block.name];

					// Call setAttributes on props to trigger the editBlock() function, which will re-render
					// the block, reflecting any changes to its properties.
					props.setAttributes({
						refresh: Date.now(),
					});

					// Enable refresh button.
					if (typeof setButtonDisabled !== 'undefined') {
						setButtonDisabled(false);
					}
				})
				.catch(function (error) {
					// Show an error in the Gutenberg editor.
					wp.data
						.dispatch('core/notices')
						.createErrorNotice('ConvertKit: ' + error, {
							id: 'convertkit-error',
						});

					// Enable refresh button.
					setButtonDisabled(false);
				});
		};

		// Register Block.
		registerBlockType('convertkit/' + block.name, {
			title: block.title,
			description: block.description,
			category: block.category,
			icon: getIcon,
			keywords: block.keywords,
			attributes: block.attributes,
			supports: block.supports,
			example: {
				attributes: {
					is_gutenberg_example: true,
				},
			},

			// Editor.
			edit: editBlock,

			// Output.
			save: saveBlock,
		});
	})(
		window.wp.blocks,
		window.wp.blockEditor,
		window.wp.element,
		window.wp.components
	);
}

/**
 * Registers pre-publish actions in Gutenberg's pre-publish checks panel.
 *
 * @since 2.4.0
 *
 * @param {Object} actions Pre-publish actions.
 */
function convertKitGutenbergRegisterPrePublishActions(actions) {
	(function (plugins, editPost, element, components, data) {
		const el = element.createElement;
		const { ToggleControl } = components;
		const { registerPlugin } = plugins;
		const { PluginPrePublishPanel } = editPost;
		const { useSelect, useDispatch, select } = data;

		/**
		 * Returns a PluginPrePublishPanel for this plugin, containing all
		 * pre-publish actions.
		 *
		 * @since 2.4.0
		 * @return {WPElement|null} Pre-publish panel element or null if not a post.
		 */
		const RenderPanel = function () {
			// --- Hooks must be called first ---
			const { meta } = useSelect((wpSelect) => ({
				meta: wpSelect('core/editor').getEditedPostAttribute('meta'),
			}));

			const { editPost: wpEditPost } = useDispatch('core/editor');

			const currentPostType = select('core/editor').getCurrentPostType();

			// Bail early if not a 'post'
			if (currentPostType !== 'post') {
				return null;
			}

			// Build rows safely using .map()
			const rows = Object.values(actions).map((action) => {
				const key = '_convertkit_action_' + action.name;

				return el(ToggleControl, {
					key,
					id: 'convertkit_action_' + action.name,
					label: action.label,
					help: action.description,
					value: true,
					checked: meta[key],
					onChange(value) {
						wpEditPost({ meta: { [key]: value } });
					},
				});
			});

			// Return the pre-publish panel with rows
			return el(
				PluginPrePublishPanel,
				{
					className: 'convertkit-pre-publish-actions',
					title: 'ConvertKit',
					initialOpen: true,
				},
				rows
			);
		};

		// Register pre-publish actions
		registerPlugin('convertkit-pre-publish-actions', {
			render: RenderPanel,
		});
	})(
		window.wp.plugins,
		window.wp.editPost,
		window.wp.element,
		window.wp.components,
		window.wp.data
	);
}

/**
 * Outputs a notice for the block.  Typically used when a block's settings
 * have not been defined, no API key exists in the Plugin or no resources
 * (forms, products) exist in ConvertKit, and the user adds an e.g.
 * Form / Product block.
 *
 * @since 	2.2.3
 *
 * @param {string} block_name Block Name.
 * @param {string} notice     Notice to display.
 * @return {Object} 		   HTMLElement
 */
function convertKitGutenbergDisplayBlockNotice(block_name, notice) {
	return wp.element.createElement(
		'div',
		{
			// convertkit-no-content class allows resources/backend/css/gutenberg.css
			// to apply styling/branding to the block.
			className: 'convertkit-' + block_name + ' convertkit-no-content',
		},
		notice
	);
}
