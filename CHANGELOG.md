### 3.0.0 2025-09-08
* Added: Blocks: Form Builder. Create your own subscription forms with custom fields, tag and sequence support, within the block editor
* Added: Blocks: Form: Alignment option
* Added: Settings: Form Entries: View, search, delete and export entries submitted via Form Builder blocks
* Added: Settings: Non-inline Forms: Option to only display one non-inline form per session
* Fix: Settings: Improve Intercom help integration
* Updated: Settings: Renamed `Site Wide` section to `Non-inline Forms`
* Updated: Settings: Moved reCAPTCHA settings from Member Content to General settings, as reCAPTCHA is used for both the Form Builder and Member Content
* Updated: Settings: Documentation links
* Updated: Settings: Improved wording on `Disable Scripts` and `Disable CSS`, to make clearer what these settings do

### 2.8.7 2025-07-31
* Added: Blocks: Form: Margin, padding and background color options
* Added: Debug: Forms: `data-kit-source-post-id` attribute to scripts when Debugging enabled
* Added: JS: Emit `kit_subscriber_id_removed_from_url` event when the `ck_subscriber_id` is removed from a URL
* Fix: Debloat Plugin: Exclude Forms from Debloat's Defer and Delay JS
* Fix: Classic Widgets: Form: Uncaught error: Call to undefined method `ConvertKit_Resource_Forms::output_select_field_all`
* Fix: Blocks: Refactored and removed unnecessary duplicate render callback functions in the block editor

### 2.8.6.1 2025-07-23
* Fix: Form Trigger: Revert running `script` tags through `wp_kses`, as WordPress.com strip the script tag

### 2.8.6 2025-07-23
* Fix: Form Block: Correctly style "No Content" message when selecting a modal form
* Fix: Member Content: Display all Posts if filter = All content

### 2.8.5 2025-07-17
* Fix: Broadcasts, Form Trigger and Product Blocks: Improve rendering accuracy between block editor and frontend site
* Fix: Sanitization and security enhancements

### 2.8.4 2025-07-10
* Added: Broadcasts Block: Display order option
* Added: Broadcasts, Form Trigger and Product Blocks: Use `get_block_wrapper_attributes` for block `class` and `style` attributes
* Added: Broadcasts, Form Trigger and Product Blocks: Typography options
* Added: Broadcasts, Form Trigger and Product Blocks: Margin and padding options

### 2.8.3 2025-07-03
* Added: Member Content: `All member-only content` option added to filter dropdown in Posts and Pages tables
* Fix: Member Content: Replace `Members only` with `member-only`
* Fix: Automatically refresh Access Token when expired

### 2.8.2 2025-05-28
* Fix: Broadcasts: Remove emoijs from Permalink whem importing Broadcasts to WordPress Posts
* Fix: Coding Standards: Use `wp_doing_cron` function, instead of `DOING_CRON` constant

### 2.8.1 2025-05-07
* Added: Member Content: Permits Apple, DuckDuckGo, OpenAI Search, Perplexity and Yandex to crawl Member Content if the Permit Search Engine Crawlers option is enabled
* Fix: Classic Editor: Display refresh button icon
* Updated: Require PHP 7.1 or greater
* Updated: Use WordPress Libraries 2.0.9

### 2.8.0 2025-04-24
* Fix: Divi Theme: Register modules correctly (Broadcasts, Form, Form Trigger, Product)

### 2.7.9 2025-04-18
* Fix: Fatal error `Uncaught Error: Class "ConvertKit_Admin_Bulk_Edit" not found` when using Solid Central (previously iThemes Sync)
* Fix: Use `add_filter` for `convertkit_admin_settings_register_sections` hook

### 2.7.8 2025-04-10
* Fix: Divi Builder: Display Visual Editor / TinyMCE buttons when Divi Builder or Divi Theme active
* Fix: Member Content: Improved support for Uncode Theme
* Updated: Use WordPress Libraries 2.0.8

### 2.7.7 2025-04-01
* Added: Member Content: Support for Uncode Theme
* Fix: Member Content: Prevent fatal error when both `Add a Tag` and `Member Content` settings defined on a Page, and the visitor has entered the Member Content code / clicked the magic link via email
* Fix: Default Forms (Site Wide): Don't output non-inline site wide forms on 404 or search pages
* Fix: Gutenberg / Block Editor: Improved method for block editor detection to prevent loading JS when a page builder is active

### 2.7.6 2025-03-25
* Fix: Remove `load_plugin_textdomain` call, as it's not needed since WordPress 4.6
* Fix: Issue client ID, site-specific v4 API Access and Refresh Token when upgrading from < 2.5.0
* Fix: Member Content: Only automatically configure caching Plugins if Member Content is used on a Page, Post or Custom Post
* Updated: Use WordPress Libraries 2.0.7

### 2.7.5 2025-03-07
* Fix: Member Content: Set `wordpress_ck_subscriber_id` cookie to beat WP Engine's server caching
* Fix: Settings: Forms: Check Post Type of main Post, to ensure correct form is output when multiple Posts output on a singular Post, Page or CPT

### 2.7.4 2025-02-24
* Added: Broadcasts (Beta): `convertkit-broadcast` body CSS class to Posts imported from Kit Broadcasts, to allow targeted CSS styles
* Fix: Broadcasts (Beta): Remove table borders and background colors added by Elementor on imported Kit Broadcasts
* Fix: Member Content: Support magic links in emails
* Fix: Tools: Import & Export: Include Broadcasts settings

### 2.7.3 2025-02-18
* Added: Member Content: Tags: Display login link when `Require login` option enabled
* Added: Member Content: Option to gate content by Kit Form
* Added: Default Forms (Site Wide): Option to honor `None` setting on Page, Post and Custom Post level
* Updated: Use WordPress Libraries 2.0.6

### 2.7.2 2025-02-04
* Added: Member Content: Option to require login when gating content by Tag. Subscribers receive an email with a magic link / code to securely access member-only content
* Added: Broadcasts (Beta): Option to export WordPress Pages and Custom Post Types to draft Kit Broadcasts using `Create Broadcast in Kit` option in admin tables
* Added: Settings: Intercom help integration
* Fix: Remove `ck_subscriber_id` from URL using `replaceState`, ensuring current URL is only stored once in browser history

### 2.7.1 2025-01-23
* Added: Populate the `referrer` property in Kit when Contact Form 7 or Forminator is configured to subscribe an email address to a Kit Form
* Added: Settings: Group Settings in UI
* Added: Refresh resources button to update the list of Kit Forms / Products when using an applicable block or shortcode
* Added: Member Content: Support using a signed subscriber ID when viewing content gated by Kit Tag
* Fix: Settings: Uncaught TypeError when clicking Save Changes at Settings > Kit when a Kit account has no resources and no checkboxes are checked
* Updated: Kit WordPress Libraries to 2.0.6

### 2.7.0 2024-12-18
* Added: Member Content: Automatically configure WP Rocket to not cache when the `ck_subscriber_id` cookie is present, to ensure Member Content correctly displays
* Fix: Don't strip hash from URL, ensuring anchor links work

### 2.6.9 2024-12-12
* Added: Settings: Default Forms (Site Wide): Option to specify more than one non-inline site wide Form
* Added: Settings: Member Content: Option to enable Google reCAPTCHA v3 when using Member Content by Tag functionality 

### 2.6.8 2024-12-07
* Fix: Fatal error when a non-inline Form specified in Plugin, Post or Category settings and Form Position set to `After element`

### 2.6.7 2024-12-04
* Removed: `convertkit_store_subscriber_id_in_cookie` AJAX call, as logic exists server side

### 2.6.6 2024-11-27
* Added: Categories: `None` option added to specify no Form displays on Posts assigned to the Category
* Added: Honor Block Visibility Plugin's conditions when used on a Kit Form block set to output a non-inline Form
* Fix: Character encoding when a Form is set to display after a specific element at `Settings > Kit > Form Position`
* Fix: Settings: Broadcasts: Ensure `Import Now` displays
* Fix: Broadcasts: Automatically reschedule cron event for importing Broadcasts to WordPress Posts, if removed

### 2.6.5 2024-11-13
* Added: OAuth: Issue site-specific Access and Refresh Token when using the same Kit account on multiple WordPress sites
* Fix: Exclude Forms from WP Rocket Minification and Combine
* Updated: ConvertKit WordPress Libraries to 2.0.5

### 2.6.4 2024-11-05
* Added: Settings: Broadcasts: Option to import images to the WordPress Media Library when importing Broadcasts to WordPress Posts
* Added: Broadcasts: Remove heading title from content when it matches the Broadcast's title
* Fix: Broadcasts: Retain `figure` and `figcaption` elements when importing Broadcasts to WordPress Posts

### 2.6.3 2024-10-28
* Added: Broadcasts: Exclude Polls when importing Broadcasts to WordPress Posts
* Added: Settings: Forms: Only display Form settings if Forms exist in Kit
* Fix: Add a Tag: Honor setting when defined on individual WordPress Pages and Posts, using the v4 API Access Token

### 2.6.2 2024-10-18
* Added: Settings: Option to output Default Forms after a given number of elements (paragraphs, headings, images)

### 2.6.1 2024-10-08
* Fix: Kit branding tweaks and secondary button colors

### 2.6.0 2024-10-01
* Fix: Landing Pages: Replace Cloudflare JS `type` prefixes, to ensure JS runs
* Updated: Changed branding to Kit
* Updated: Kit WordPress Libraries to 2.0.4

### 2.5.9 2024-09-27
* Added: Settings: Option to output Default Forms above, below or above and below content
* Fix: Settings: Initialize Forms Resource class when no public Post Types (resolves PHP Uncaught Error: `Call to a member function non_inline_exist() on bool`)
* Fix: Landing Pages: Correctly parse HTML, honoring redirect if "Redirect to an external page" defined on Landing Page in Kit
* Fix: Query separator / question mark incorrectly appended to URLs when `ck_subscriber_id` cookie set
* Fix: JS Uncaught TypeError: `Cannot read properties of null (reading ‘matches’)`
* Fix: PHP Warning: `Attempt to read property “base” on null`
* Updated: Kit WordPress Libraries to 2.0.3

### 2.5.8 2024-09-18
* Added: Remove jQuery as a dependency from some backend JS
* Fix: Fatal error `Uncaught TypeError: Kit_API_V4::get_subscriber(): Argument #1 ($form_id) must be of type int, string given` when a blank or non-numeric `ck_subscriber_id` supplied in the URL

### 2.5.7 2024-09-16
* Added: Divi: Kit Broadcasts Module
* Added: Divi: Kit Form Trigger Module
* Added: Divi: Kit Product Module
* Fix: Landing Pages: Honor redirect if "Redirect to an external page" defined on Landing Page in Kit
* Fix: Preserve other URL query parameters when removing `ck_subscriber_id`

### 2.5.6 2024-08-15
* Added: Pages: Add New Landing Page Setup Wizard, to easily create a WordPress Page that displays a Kit Landing Page
* Added: Divi: Kit Form Module
* Updated: Pages: Landing Page + Member Content Wizards now display in a dropdown button

### 2.5.5 2024-08-15
* Fix: Contact Form 7: Set subscriber to `inactive` when a Kit Form is specified, honoring the Form's opt in setting.
* Fix: Forminator: Set subscriber to `inactive` when a Kit Form is specified, honoring the Form's opt in setting.
* Fix: WishList Member: Set subscriber to `inactive` when a Kit Form is specified, honoring the Form's opt in setting.

### 2.5.4 2024-08-08
* Added: WishList Member: Option to just subscribe email, subscribe to a Form, Tag or Sequence or unsubscribe
* Fix: Contact Form 7: Typo on description text
* Fix: Forminator: Typo on description text

### 2.5.3 2024-08-06
* Fix: Contact Form 7: Update description on settings screen to reflect new settings
* Fix: Forminator: Update description on settings screen to reflect new settings
* Fix: WishList Member: Fatal error `Uncaught TypeError: Kit_API_V4::add_subscriber_to_form(): Argument #1 ($form_id) must be of type int, string given`

### 2.5.2 2024-07-25
* Added: Contact Form 7: Option to just subscribe email, or subscribe to a Form, Tag or Sequence
* Added: Forminator: Option to just subscribe email, or subscribe to a Form, Tag or Sequence

### 2.5.1 2024-07-17
* Fix: Landing Pages: Prevent Perfmatters lazy loading images on Landing Pages, which would result in missing images
* Updated: Kit WordPress Libraries to 2.0.1

### 2.5.0 2024-07-09
* Added: Use Kit v4 API and OAuth
* Updated: Kit WordPress Libraries to 2.0.0

### 2.4.9.1 2024-06-20
* Added: Categories: Option to display specified Kit Form on Category archives
* Fix: Move "Add a Tag" logic to backend when tagging a subscriber, instead of using AJAX

### 2.4.9 2024-05-13
* Fix: Settings: Don't import Broadcasts to WordPress Posts when viewing Settings screen, for performance
* Fix: Broadcasts (Beta): Check `DOMDocument` available to parse Broadcasts, instead of producing a fatal error
* Fix: Autoptimize: Don't output duplicate Forms when Autoptimize's `Defer JS` option is enabled
* Updated: Removed upgrade path from Plugin versions below 1.4.1

### 2.4.8 2024-04-11
* Fix: Member Content: Display Content when using a frontend Page Builder, such as Beaver Builder
* Fix: Forms: Sticky Bar: Set `z-index` CSS property to ensure Sticky Bar Forms correctly display over some WordPress Theme headers and menus

### 2.4.7 2024-04-08
* Fix: WP-Rocket: Display Forms when WP-Rocket's `Delay JavaScript Execution` option is enabled
* Fix: Perfmatters: Display Forms when Perfmatters' `Delay JavaScript` option is enabled
* Fix: Broadcasts (Beta): Improve parsing of Broadcasts when removing unsubscribe and hidden sections

### 2.4.6 2024-03-08
* Fix: Debug Log: Change log file location to log subfolder, with .htaccess and index.html protection
* Fix: Debug Log: Mask email addresses, first names and signed subscriber IDs

### 2.4.5 2024-02-28
* Added: Products: Block and Shortcode: Option to immediately load checkout step when button clicked, instead of Product image and description
* Fix: Siteground Speed Optimizer: Display Forms when Siteground's `Combine JavaScript Files` option is enabled
* Fix: LiteSpeed Cache: Don't output duplicate non-inline Forms when LiteSpeed Cache's `Load JS Deferred` option is enabled
* Fix: Member Content: Improved UI on mobile devices

### 2.4.4 2024-02-19
* Added: Remove jQuery as a dependency from Setup Wizard, Modals and Refresh buttons
* Added: Forms: Automatically center inline forms on non-block Themes
* Fix: Forms: Remove bottom margin on non-inline forms
* Fix: Setup Wizard: Preview Form link for Pages would incorrectly display a Post
* Fix: Landing Pages: Prevent WP Rocket caching and lazy loading images on Landing Pages, which would result in missing background images
* Fix: Classic Editor: Modal window: Don't display vertical scrollbar on Safari
* Fix: Member Content: Refresh button: Populate Tags and Products in applicable option groups
* Fix: Member Content: Refresh button: Define correct option values on refresh, ensuring settings save successfully.
* Fix: Member Content: Corrected grammar for `member-only`

### 2.4.3 2024-01-31
* Added: Settings: Option to specify Default Form on public Custom Post Types
* Added: Custom Posts: Option to specify Form and Tag on individual public Custom Post Types
* Added: Member Content: Support for Member Content functionality on public Custom Post Types
* Added: Removed jQuery as a dependency from Bulk Edit, Quick Edit and TinyMCE JS
* Fix: Divi: Allow scrolling when editing using the Divi Builder in tablet or mobile view
* Fix: Jetpack Boost: Prevent inline Forms from displaying in footer when added to a Page or Widget area when Jetpack Boost Plugin active
* Fix: Settings: Remove whitespaces from API Key and Secret when saving
* Fix: Broadcasts (Beta): Improve descriptions to make clear that only public Broadcasts are imported to WordPress

### 2.4.2 2024-01-22
* Added: Member Content: Option to permit search engines to crawl Member Content at `ConvertKit > Member Content > Permit Search Engine Crawlers`
* Added: Removed jQuery from frontend JS and as a dependency
* Fix: Site Editor: Use `enqueue_block_assets` hook instead of `enqueue_block_editor_assets` hook
* Updated: WordPress Libraries to 1.4.1

### 2.4.1 2024-01-08
* Added: Broadcasts (Beta): Option to store ConvertKit Broadcast thumbnails as WordPress Post's Featured Image
* Added: Products: Block and Shortcode: Option to include a discount code
* Added: Products: Block and Shortcode: Option to disable modal on mobile. Recommended if the ConvertKit Product is a digital download (PDF) being purchased on mobile, to ensure the subscriber can immediately download the PDF once purchased.

### 2.4.0 2023-12-06
* Fix: ConvertKit Error: Could not load Plugin class `output` when editing, quick or bulk editing a Post.

### 2.3.9 2023-12-04
* Added: Settings: Display ConvertKit Form's format (inline, slide in, sticky bar, modal) where a `select` dropdown option is presented
* Fix: Settings: Default Form (Site Wide): If defined, honor non-inline Form specified at Page, Post or Post Category level, to avoid two non-inline forms displaying
* Fix: Settings: Moved Documentation tab to Help link in header, for compat. with smaller screen resolutions

### 2.3.8 2023-11-20
* Added: Member Content: Display log in and authentication code forms in modal to better match ConvertKit
* Added: Broadcasts (Beta): Define WordPress Post Author when importing ConvertKit Broadcasts
* Fix: Broadcasts (Beta): Don't show next scheduled date and time immediately after clicking `Import Now`
* Fix: PHP Warning: Use of deprecated `FILTER_SANITIZE_STRING`

### 2.3.7 2023-11-09
* Added: Member Content: Output manual or generated excerpt if no read more tag present in the Post's content
* Fix: Member Content: Force padding on buttons to prevent Themes overriding button layout
* Fix: Member Content: Add `required` attribute on form fields

### 2.3.6 2023-11-02
* Added: Member Content: Updated UI of gated content screens to better match ConvertKit
* Added: Settings: Member Content: Separate text settings can be configured for display when restricting by ConvertKit Tag

### 2.3.5 2023-10-30
* Added: Member Content: Improve UI of authentication code screen
* Fix: Don't attempt to append ConvertKit Forms to unsupported Post Types

### 2.3.4 2023-10-24
* Added: Broadcasts (Beta): Define WordPress Post status (Draft, Pending Review, Private or Published) when importing ConvertKit Broadcasts
* Added: Settings: General: Option to display a site wide non-inline form
* Updated: WordPress Libraries to 1.4.0

### 2.3.3 2023-10-18
* Added: Broadcasts (Beta): Option to export WordPress Posts to draft ConvertKit Broadcasts
* Added: Member Content: Wizard: Option to restrict content by ConvertKit Tag
* Fix: Member Content: Check Product or Tag exists in ConvertKit before restricting content
* Fix: Form Trigger Block: Add spacing to button on non-block themes
* Fix: Form Trigger Block: Render button in Gutenberg to better match frontend output when changing background color
* Fix: Product Block: Add spacing to button on non-block themes
* Fix: Product Block: Render button in Gutenberg to better match frontend output when changing background color

### 2.3.2 2023-10-05
* Added: Member Content: Option to restrict content by ConvertKit Tag, displaying a subscription form and subscribing the entered email address to the tag
* Added: Member Content: Posts: Display Filter dropdown in Posts table
* Fix: Member Content: Always enable Member Content options, ensuring imported Paid Broadcasts correctly show / hide content.
* Fix: Prevent blank submenu entries displaying under `Dashboard` menu when using a third party admin menu editor Plugin
* Updated: ConvertKit WordPress Libraries to 1.3.9

### 2.3.1 2023-09-14
* Fix: Settings: Member Content: Added missing`for` label attributes
* Fix: Settings: Broadcasts: Added missing`for` label attributes
* Fix: Posts: Add / Edit Category: `for` label attribute now matches the field ID
* Fix: Select2: Remove whitespace on tooltip hover
* Fix: Blocks: Include `editorScript` in `block.json`

### 2.3.0 2023-09-12
* Added: Forminator: Option to map Forminator Forms to ConvertKit Forms, to subscribe email addresses at Settings > Kit > Forminator
* Added: Forminator: Option to Enable Creator Network Recommendations modal on individual Forms at Settings > Kit > Forminator
* Added: Blocks: Register blocks using block.json
* Fix: Landing Pages: Use WordPress Site Icon as favicon, if defined

### 2.2.9 2023-09-06
* Added: Broadcasts (Beta): Automatically publish public ConvertKit Broadcasts as WordPress Posts. Head over to `Settings > Kit > Broadcasts` to get started.
* Updated: WordPress Coding Standards
* Updated: ConvertKit WordPress Libraries to 1.3.8

### 2.2.8 2023-08-07
* Fix: Use `file_get_contents` instead of `WP_Filesystem` for reading plugin files, to avoid fatal error on activation when file ownership/permission issues occur
* Fix: Block Editor: Prevent block error in WordPress 6.0 and lower by checking if `useAnchor` is available

### 2.2.7 2023-07-24
* Added: Contact Form 7: Option to Enable Creator Network Recommendations modal on individual Forms at Settings > Kit > Contact Form 7

### 2.2.6 2023-07-18
* Added: Form Trigger: Block: When no API Key specified, link to Setup Wizard in a popup window to complete setup
* Added: Form: Block: When no API Key specified, link to Setup Wizard in a popup window to complete setup
* Added: Product: Block: When no API Key specified, link to Setup Wizard in a popup window to complete setup
* Updated: Member Content: Removed beta label

### 2.2.5 2023-06-21
* Added: Broadcasts: Shortcode: Tabbed UI when adding broadcasts through the Classic or Text Editor
* Added: Broadcasts: Block: Moved pagination settings to own sidebar panel
* Fix: Classic Editor: Insert button would stop working when switching from Text Editor to Classic Editor
* Fix: Uncaught Error: Call to undefined method ConvertKit_Resource_Forms::get_by()

### 2.2.4 2023-06-15
* Added: Form Trigger: Block: Display message with link when no API Key specified, or no non-inline Forms exist in ConvertKit
* Added: Form Trigger: Shortcode: Display message with link when no API Key specified, or no non-inline Forms exist in ConvertKit
* Added: Forms: Shortcode: Display message with link when no API Key specified, or no inline Forms exist in ConvertKit
* Added: Products: Shortcode: Display message with link when no API Key specified, or no Products exist in ConvertKit
* Fix: Bulk & Quick Edit: Show contextual icons for Form, Tag and Member Content settings, instead of the default Form icon
* Fix: Settings: Conditionally load CSS and JS depending on the section (General, Tools, Member Content)
* Fix: Settings: Link to ConvertKit form creator when no Forms exist in ConvertKit
* Fix: Use higher quality SVG icons for blocks, shortcodes and formatters 
* Updated: ConvertKit WordPress Libraries to 1.3.6

### 2.2.3 2023-06-06
* Added: Broadcasts: Options to display grid, images, descriptions and/or read more link
* Added: Broadcasts: Output as single column on smaller screen resolutions
* Added: Forms: Block: Display message with link when no API Key specified, or no Forms exist in ConvertKit
* Added: Products: Block: Display message with link when no API Key specified, or no Products exist in ConvertKit
* Fix: Settings: Disable CSS: Improve description of Disable CSS functionality, making it clearer what this setting does
* Fix: Use `esc_url` instead of `esc_attr` for link `href` properties

### 2.2.2 2023-05-24
* Added: Elementor: ConvertKit Form Trigger Block
* Added: Member Content: Automatically configure WP Fastest Cache and WP-Optimize Plugins to not cache when the `ck_subscriber_id` cookie is present, to ensure Member Content correctly displays
* Added: Member Content: Display a notice if Litespeed Cache, W3 Total Cache or WP Super Cache Plugins are active and have not been configured to exclude caching when the `ck_subscriber_id` cookie is present
* Fix: Elementor: ConvertKit Product: Button icon was missing
* Fix: Block Editor: Don't display options to link text to display a non-inline form if no forms exist in ConvertKit
* Fix: Block Editor: Don't display options to link text to display a Product or Tip Jar if no Product / Tip Jar exists in ConvertKit

### 2.2.1 2023-05-10
* Fix: Settings: Escape tab links on output

### 2.2.0 2023-05-04
* Added: ConvertKit Form Trigger Block, outputting a button which displays a non-inline form (modal, slide in, sticky bar) when pressed
* Added: ConvertKit Form Trigger Shortcode, outputting a button which displays a non-inline form (modal, slide in, sticky bar) when pressed
* Added: Block Editor: Link text to display a non-inline form (modal, slide in, sticky bar) when pressed
* Added: Block Editor: Link text to display a ConvertKit Product or Tip Jar when pressed
* Fix: Forms: Output non-inline scripts once per form, to avoid the same form displaying twice when embedded two or more times in a page
* Fix: Forms: Output non-inline scripts using the `wp_footer` hook, ensuring modal overlays fill the screen
* Fix: Member Content: Append `ck-cache-bust` query parameter after entering code, to prevent plugin / host caching showing stale data
* Fix: Settings: Tools: Import / Export: Include Member Content settings in import and export configuration
* Fix: Settings: Member Content: Display warning notice that web host caching / caching plugins must be configured to disable caching when the `ck_subscriber_id` cookie is present

### 2.1.3 2023-04-06
* Fix: Improve UI compatibility for buttons in WordPress 5.x, using `button-hero` CSS class instead of custom padding 
* Updated: ConvertKit WordPress Libraries to 1.3.4

### 2.1.2 2023-03-30
* Added: Link to Setup Wizard on Plugins screen
* Added: Improved ConvertKit Icons in Classic Editor and block editor for Broadcasts, Forms, Products and Custom Content

### 2.1.1 2023-02-23
* Fix: Post: Settings: PHP notices when settings are not an array
* Fix: Landing Pages and Legacy Forms: Deprecated `mb_convert_encoding()` message in PHP 8.2

### 2.1.0 2023-02-15
* Added: Member Content (Beta): Require subscribers to purchase a ConvertKit Product to access specific Pages on your WordPress site. Head over to `Settings > Kit > Member Content` to get started.
* Fix: Product: Block and Shortcode: Set stylesheet ID to `convertkit-product-css`, to match other blocks
* Fix: Blocks: Use wp.serverSideRender instead of soon to be deprecated wp.components.ServerSideRender
* Fix: Forms: Preview: Support for previewing and editing Legacy Forms
* Fix: Improved performance in WordPress Admin when invalid API credentials specified on new installation
* Fix: Display notice in WordPress Admin with link to settings screen when invalid API credentials specified

### 2.0.8 2023-02-02
* Added: Settings: ConvertKit: Documentation tab
* Added: Forms: Link to edit form in ConvertKit when previewing a Page, Post or Custom Post containing a ConvertKit form
* Fix: Display Forms, Landing Pages, Products and Tags in alphabetical order when listed in a `<select>` dropdown
* Fix: Form: Block and Shortcode: Check Forms exist in ConvertKit before outputting Block / Shortcode options
* Fix: Form: Shortcode: Remove unused API Key output
* Fix: Product: Block: Preview: Improved performance when previewing the Product block to determine if a ConvertKit Product was specified in the Block's settings
* Fix: Product: Shortcode: Remove unused API Key and data-attributes output

### 2.0.7 2023-01-16
* Fix: Elementor 3.9.0+ compatibility
* Updated: ConvertKit WordPress Libraries to 1.3.0

### 2.0.6 2023-01-05
* Fix: Honor "Add a tag" setting when enabled on a Page/Post
* Fix: PHP Warning: Cannot modify header information - headers already sent, when ?ck_subscriber_id included in request URI in some Page Builders (e.g. Elementor)
* Fix: PHP Warning: Trying to access array offset on value of type null

### 2.0.5 2022-12-15
* Fix: Broadcasts: Strip slashes on output when pagination clicked and Broadcasts are reloaded
* Fix: Broadcasts: Sanitize and escape HTML attributes on output
* Fix: Forms: Escape HTML attributes on output
* Fix: Products: Sanitize and escape HTML attributes on output

### 2.0.4 2022-12-13
* Fix: Products: PHP warning when attempting to parse an invalid Product URL
* Fix: Landing Pages: Catch and log when an error occurs fetching a Landing Page
* Fix: Remove double forwardslash on product.css

### 2.0.3 2022-12-08
* Added: Categories: Option to specify ConvertKit Form to display when adding a new Post Category
* Fix: Bulk & Quick Edit: Improve layout of ConvertKit settings on desktop and mobile
* Fix: Post: Improve layout of ConvertKit settings on desktop and mobile
* Fix: Categories: Improve layout of ConvertKit settings on desktop and mobile
* Fix: Products: Block: Display preview when adding new block

### 2.0.2 2022-11-21
* Fixed: Removed argument count on `in_admin_footer` action calls

### 2.0.1 2022-11-01
* Added: Broadcasts: Block: Display message in editor when no Broadcasts exist in ConvertKit
* Fixed: Settings: Contact Form 7: Render screen correctly when no Forms in ConvertKit
* Fixed: Settings: WishList Member: Render screen correctly when no Forms in ConvertKit

### 2.0.0 2022-10-24
* Added: ConvertKit Products Block, to output a button linking to a ConvertKit Product or Tip Jar
* Added: ConvertKit Products Shortcode, to output a button linking to a ConvertKit Product or Tip Jar
* Added: Gutenberg: Option to link text or button to a ConvertKit Product or Tip Jar
* Added: Classic Editor: Option to link text or button to a ConvertKit Product or Tip Jar
* Added: Settings: Improved UI

### 1.9.8.5 2022-10-03
* Added: Broadcasts: Shortcode: Options to specify background, text and link colors
* Added: Broadcasts: Elementor: Options to specify background, text and link colors
* Added: Settings: General: Links added to preview Default Form for each Post Type

### 1.9.8.4 2022-09-08
* Added: Setup Wizard for new installations
* Fix: Text Editor: Quicktag Buttons: Position and size modal window correctly to avoid scrollbars and whitespace
* Fix: Widgets: Legacy Forms Widget: "The convertkit_form block was affected by errors and may not function properly" when attempting to add legacy form widget
* Development: Moved /lib folder to managed repository

### 1.9.8.3 2022-08-19
* Added: Settings: Tools: Use WordPress' Site Info to populate System Info section
* Added: Refresh button: Show error notification when refreshing fails
* Fix: Widgets: Broadcasts Block: JSON response error when attempting to save Broadcasts Block in a Widget area
* Fix: Classic (Visual) and Text Editor: Insert shortcode into correct editor when multiple editor instances exist (e.g. WooCommerce Products)

### 1.9.8.2 2022-08-04
* Fix: API: Show error notification when API returns HTTP 500 and 502 errors, instead of showing PHP warnings
* Fix: Bulk and Quick Edit: `for` label attribute now matches the field ID

### 1.9.8.1 2022-07-18
* Added: Refresh button for Form, Landing Page and Tag fields to fetch latest data from ConvertKit account
* Fix: Bulk Edit: Don't display fields when no Pages / Posts exist

### 1.9.8.0 2022-07-14
* Added: Bulk and Quick Edit Form and Tag when viewing list of Pages/Posts
* Fix: Performance: Don't perform API requests on every WordPress Administration screen when no Forms, Tags or Landing Pages exist

### 1.9.7.9 2022-06-24
* Fix: API: Prevent fatal error when API returns null instead of expected array

### 1.9.7.8 2022-06-23
* Added: Elementor Page Builder: ConvertKit Broadcasts Widget
* Fix: Integration: WishList Member: Unsubscribe email address from ConvertKit if 'unsubscribe' configured and member level removed
* Fix: Remove double forwardslash on some enqueued scripts and styles 

### 1.9.7.7 2022-06-09
* Added: Broadcasts: Option to enable pagination on block/shortcode

### 1.9.7.6 2022-06-01
* Added: ConvertKit Broadcasts Block when editing Widgets using the block editor in WordPress 5.8+
* Added: ConvertKit Form Block when editing Widgets using the block editor in WordPress 5.8+
* Fix: ConvertKit Broadcasts Block/Shortcode: Fetch all Broadcasts from ConvertKit, not just the first 50
* Fix: Settings: Added label element for setting field names

### 1.9.7.5 2022-05-12
* Fix: PHP Warning: Cannot modify header information, caused by QuickTags modal template output
* Fix: Text Editor: Quicktag Buttons: Block could not be found error when using a Quicktag

### 1.9.7.4 2022-05-04
* Added: ConvertKit Broadcasts Block, to output a list of ConvertKit broadcasts
* Added: ConvertKit Broadcasts Shortcode [convertkit_broadcasts], to output a list of ConvertKit broadcasts
* Added: Settings: Tools: Import and Export configuration
* Fix: Page/Post: If a specific Form is selected that no longer exists in ConvertKit, fallback to the Default Form setting

### 1.9.7.3 2022-04-04
* Added: Elementor Page Builder: ConvertKit Form Widget
* Fix: Default Form would not display on Posts assigned to Categories, where Categories were created prior to 1.9.6.0 and site uses PHP 8.0 or greater
* Fix: Categories: Improved wording of Form setting on per-Category level

### 1.9.7.2 2022-03-30
* Fix: Default Form would not display on Posts due to regression in 1.9.7.1

### 1.9.7.1 2022-03-23
* Fix: Default Form would not display on Posts due to regression in 1.9.7.0

### 1.9.7.0 2022-03-17
* Fix: ConvertKit Form Block: Order Form names alphabetically
* Fix: Prevent Select2 styling from applying to non-Plugin elements

### 1.9.6.9 2022-03-07
* Added: ConvertKit Form Block: When editing, display the Form Name if a non-inline form has been selected, as non-inline forms cannot be previewed in the editor
* Fix: Include email address in API request when attempting to fetch subscriber ID by email when ConvertKit Form is submitted with no email address

### 1.9.6.8 2022-02-18
* Fix: Performance: Don't query API to fetch subscriber ID by email when ConvertKit Form is submitted with no email address

### 1.9.6.7 2022-02-14
* Fix: Localization: Corrected path to load language files

### 1.9.6.6 2022-01-27
* Fix: Plugin Activation: Parse error when using PHP 7.2 or below due to trailing comma in sprintf() call

### 1.9.6.5 2022-01-26
* Added: ConvertKit Form Block for Gutenberg
* Added: Select2 dropdown for Forms, Landing Pages and Tags with search functionality for improved UX.
* Fix: Legacy Forms: Removed erronous <html>, <head> and <body> tags from markup 

### 1.9.6.4 2022-01-11
* Fix: Render Legacy Form when shortcode is copied from app.convertkit.com for a Legacy Form
* Fix: Don't check for Landing Page when viewing any non-Page public Post Type which doesn't support Landing Pages
* Fix: PHP Notice: Undefined index landing_page when upgrading from 1.4.6 or earlier
* Fix: PHP Notice: Undefined index tag when upgrading from 1.4.6 or earlier

### 1.9.6.3 2021-12-23
* Fix: Render Legacy Landing Pages

### 1.9.6.2 2021-12-22
* Fix: Render Form Shortcode when a new ConvertKit Form ID specified that does not yet exist in Plugin's cached Form Resources

### 1.9.6.1 2021-12-16
* Fix: Character encoding issue on Landing Pages
* Fix: Removed unused .scripts directory and .MD files

### 1.9.6 2021-12-15
* Added: ConvertKit Form Shortcode Button for Classic Editor
* Added: Text Editor: Quicktag Buttons for inserting ConvertKit Forms and Custom Content
* Added: Settings: ConvertKit: Logo and branding header
* Added: Option to specify ConvertKit API Key and Secret as constants `CONVERTKIT_API_KEY` and `CONVERTKIT_API_SECRET` in wp-config.php
* Added: Settings: ConvertKit: General: Different Default Forms can be specified for Pages and Posts
* Added: Settings: ConvertKit: General: Enabling Debug option will also output data to browser console and inline HTML comments
* Added: Settings: ConvertKit: Tools: Option to Download Log to text file
* Added: Settings: ConvertKit: Tools: Option to Download System Info to text file
* Added: PHP 8.x compatibility
* Added: Developers: Action and filter hooks.  See https://github.com/ConvertKit/convertkit-wordpress/blob/1.9.6/ACTIONS-FILTERS.md
* Fix: PHP warnings on new installations when adding/editing Pages or Posts where the plugin was not yet configured
* Fix: PHP 8.x: PHP Deprecated warnings where required parameters wrongly followed optional parameters
* Fix: gzinflate() data error
* Fix: Deprecated edit_category_form_fields warning
* Fix: Integration: WishList Member: Unsubscribe Action 'Unsubscribe from all' now honored when selected and saved
* Fix: Ensure code meets WordPress Coding Standards
* Fix: Use WP_Filesystem instead of PHP functions to read/write log file, per WordPress Coding Standards

### 1.9.5.2 2021-07-28
* Fix: Fixed an issue where the ConvertKit shortcode would not function properly.

### 1.9.5.1 2021-07-27
* Fix: Updated GitHub zip URL for build.

### 1.9.5 2021-07-21
* Fix: Changed how API responses are logged.
* Fix: Reduced Debug output to improve performance.

### 1.9.4 2021-02-22
* New: Increase minimum supported WordPress version to 4.9 and maximum supported WordPress version to 5.6.2
* Fix: Restore original plugin file name (`wp-convertkit.php`), which reverts the breaking file name change (`plugin.php`) released in 1.9.3
* Fix: Fixed inconsistent refreshing of forms in the ConvertKit forms dropdown
* Fix: Fixed occasional "undefined variable" notices regarding the API key and API secret
* Tweak: Add ConvertKit plugin information to useragent of plugin API requests

### 1.9.3 2020-11-11
* New: Increase minimum supported WordPress version to 4.8 and maximum supported WordPress version to 5.5.3
* Fix: Fix occasional "undefined variable" notice when viewing ConvertKit forms dropdown
* Fix: Only log transients from `get_form()` method when debugging is enabled
* Tweak: Remove `sslverify => false` argument from main ConvertKit API call

### 1.9.2 2019-12-20
* Fix issue that prevented Google fonts from loading on landing pages
* Fix issue that resulted in multiple failed API calls for non-existent subscribers passed in via query parameter

### 1.9.1 2019-12-20
* Fix bug that prevented "refresh forms" button on settings page from working

### 1.9.0 2019-12-17
* Allow appending forms to WooCommerce products
* Fix bug that prevented setting "none" for form on a post from overriding category setting
* Add account name to settings page

### 1.8.1 2019-05-30
* Fix bug that could result in fatal error with certain other plugins active
* Add additional server debug info for tech support

### 1.8.0 2019-05-30
* Displays tags/LPs/forms alphabetically
* Several edge-case bug fixes
* Removes confusing utf8 warning on settings page

### 1.7.5 2019-04-30
* Fix false positive detection of character set issues related to using emojis in forms & landing pages
* Don't show error on tools tab on first visit with logging turned on
* Fix code conflict with some other plugins that resulted in PHP warnings being displayed

### 1.7.4 2019-03-27
* Fixed problem with a library that was only compatible with PHP 7+

### 1.7.3 2019-03-26
* Adds checks and notices for outdated character set (utf8 vs. utf8mb4) use
* Add notice to metabox on blog archive page that our plugin does not do anything on this page
* Update library to make plugin work with PHP 7.3 (previously, landing pages did not work)
* Fix issue where global default form would never show for some categories
* Fix include path for system status box on tools tab

### 1.7.2 2019-02-18
* Fix bug that caused fatal error on upgrade

### 1.7.1 2019-02-18
* Fix bug that caused fatal error on upgrade

### 1.7.0 2019-02-18
* New: Significantly improve performance of plugin by only attempting to tag visitors if needed (not on every page)
* New: Add option to disable javascript entirely (prevents tagging and custom content features from working)
* Fix conflict with Yoast SEO plugin
* Fix bug that prevented plugin from working with PHP 7.3
* Fix bug that prevented changing a category default form back to None
* Clarify that both API key and secret are required
* Fix bug that stripped out URL query parameters unrelated to ConvertKit
* Better handle refreshing list of forms in the connected ConvertKit account

### 1.6.4 2019-01-18
* Added tools tab with debug log and system info boxes
* Fixed a bug that would show a PHP notice in some cases
* Fixed two bugs that would sometimes result in the custom content feature failing
* Fixed a bug that would sometimes cause WishList Member integration settings to not save


### 1.6.3 2019-01-07
* Fixes issues with Contact Form 7 integration not saving form settings.
* Adds button to refresh on settings page, to fetch new forms added to the connected ConvertKit account.

### 1.6.2 2018-07-12
* Fix for this message when Landing Page is set to None: PHP Notice:  Undefined offset: 0
* Fix for new form builder being used in shortcode with "form" attribute instead of "id"

### 1.6.1 2018-07-03
* Fix for landing pages not showing in the admin area drop down
* Fix for showing new landing pages on the front end of the site
* Added jquery to landing pages as new landing page builder does not include it

### 1.6.0 2018-06-30
* Add support for new form builder
* Remove unnecessary API calls
* Store form/landing page/tag data in WP Options
* Add update routine for refreshing local convertkit data

### 1.5.5 2018-06-01
* Fix for error in javascript added to landing pages.
* Fix for applying tags based on page views on initial visit. This adds an ajax call
to each visit which some customers expressed concern about. We will add a way to remove this in an upcoming version.

### 1.5.4 2018-05-19
* Fix for adding ck_subscriber_id to cookie

### 1.5.3 2018-03-08
* Added a default form setting for post categories
* Clean up logging
* Remove admin ajax calls from each page load
* Remove api calls from the dashboard list posts page
* Add dependency to javascript enqueue to fix javascript error on landing pages

### 1.5.2 2017-11-30
* Fixed plugin settings link
* Added javascript subscriber tagging to landing pages

### 1.5.1 2017-10-31
* Fixed encoding of javascript.
* Fixed localization of data.
* Removing console.log from js.

### 1.5.0 2017-10-13
* Added saving subscriber_id after visitor fills out ConvertKit form
* Added saving subscriber_id when visitor lands on site from ConvertKit email link
* Added "Add a tag" to Posts/Page metabox
* Added convertkit_content shortcode to show content to subscribers who have a tag

### 1.4.10 2017-10-04
* Removed transient for API calls.
* Changed widget to use option setting instead of making API call.
* Reduced number of API calls while the site is being browsed.
* Updated contributors

### 1.4.9 2017-07-24
* Fix _get_meta_defaults() because Posts and Pages set to Default were not showing forms.
* Updated ConvertKit_API::_get_api_response() to inflate response body if necessary.

### 1.4.8 2017-07-13
* Fixed API response not getting unzipped
* Added check for multibyte string PHP extension
* Fixed CF7 mapping not showing all forms
* Changed log file to write locally instead of using WP_Filesystem

### 1.4.7 2017-06-01
* Code refactor with WordPress Code Standards
* Added ability to tag a customer when WishList Member membership lapses
* Added WishList Member tag a customer
* Removed curl and replaced with wp_remote_request

### 1.4.6 2017-03-29
* Fix for landing pages not appearing.
* Added code to API to not return status_code 404 content

### 1.4.5 2017-03-28
* Uncommented logging around api calls.
* Do not show 404 page content when shortcode is used with form ID that does not exist
* If ConvertKit API is unavailable fail gracefully
* Fix for Contact Form 7 message sending when no ConvertKit forms are mapped
* Removed archived forms from the list of available forms in settings page

### 1.4.4
* Added i18n support
* Added Contact Form 7 integration. Site admins can now map CF7 name and email fields to CK subscription.
* Verified functionality with latest version of WishlistMember

### 1.4.3

* Add WP widget for form
* Updates form version to v6
* Add logger to help troubleshoot issues
* Add link to CK account if no forms available
* Add notice if can't connect to API

### 1.4.2

* Fixed issue with WishlistMember integration where members were not being subscribed.

### 1.4.1

* Add upgrade routine to change ID to form_id for API version 3.0

### 1.4.0

* Update ConvertKit API to version 3.0

### 1.3.9

* Fix WishList Member email sent to CK API when shopping cart used.

### 1.3.8

* Fix crash when API response is slow

### 1.3.7

* Avoid calling API endpoints when no forms need to be shown

### 1.3.6

* Fixes issue with illegal offset showing warning message

### 1.3.5

* Fix bug showing warning messages for some users

### 1.3.4

* Fix bug showing error messages for some users

### 1.3.3

* Updated for compatibility with WordPress 4.3

### 1.3.2

* Another fix for a pesky bug causing syntax errors

### 1.3.1

* Fixes a bug causing syntax error when getting options

### 1.3.0

* Added WishList Member integration
* Updated API methods

### 1.2.1

* Fixed a warning that appeared sometimes when no forms were loaded.

### 1.2.0

* Updated to use responsive forms

### 1.0.0

* Initial release