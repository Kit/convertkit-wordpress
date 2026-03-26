<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to WordPress' Classic Editor,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class WPClassicEditor extends \Codeception\Module
{
	/**
	 * Add a Page, Post or Custom Post Type using the Classic Editor in WordPress.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I          EndToEnd Tester.
	 * @param   string         $postType   Post Type.
	 * @param   string         $title      Post Title.
	 */
	public function addClassicEditorPage($I, $postType = 'page', $title = 'Classic Editor Title')
	{
		// Activate Classic Editor Plugin.
		$I->activateThirdPartyPlugin($I, 'classic-editor');

		// Navigate to Post Type (e.g. Pages / Posts) > Add New.
		$I->amOnAdminPage('post-new.php?post_type=' . $postType);

		// Define the Title.
		$I->fillField('#title', $title);
	}

	/**
	 * Adds a paragraph block when adding or editing a Page, Post or Custom Post Type
	 * in the Classic Editor.
	 *
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 * @param   string         $text   Paragraph Text.
	 * @param   string         $editor Target TinyMCE editor instance.
	 */
	public function addClassicEditorParagraph($I, $text, $editor = 'content')
	{
		// There's no way for Codeception to fill an iframe's contenteditable using fillField(),
		// so use JS instead.
		$I->executeJS("tinymce.get('" . $editor . "').insertContent('<p>" . $text . "</p>');");
	}

	/**
	 * Add the given shortcode when adding or editing a Page, Post or Custom Post Type
	 * in the Visual Editor (TinyMCE).
	 *
	 * If a shortcode configuration is specified, applies it to the newly added shortcode.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I                          EndToEnd Tester.
	 * @param   string         $shortcodeName              Shortcode Name (e.g. 'Kit Form').
	 * @param   bool|array     $shortcodeConfiguration     Shortcode Configuration (field => value key/value array).
	 * @param   bool|string    $expectedShortcodeOutput    Expected Shortcode Output (e.g. [convertkit_form form="12345"]).
	 * @param   string         $targetEditor               Target TinyMCE editor instance.
	 */
	public function addVisualEditorShortcode($I, $shortcodeName, $shortcodeConfiguration = false, $expectedShortcodeOutput = false, $targetEditor = 'content')
	{
		// Open Visual Editor shortcode modal.
		$I->openVisualEditorShortcodeModal($I, $shortcodeName, $targetEditor);

		// Wait for the modal's form to load.
		$I->waitForElementVisible('#convertkit-modal-body form.convertkit-tinymce-popup');

		// If a shortcode configuration is specified, apply it to the shortcode's modal window now.
		if ($shortcodeConfiguration) {
			foreach ($shortcodeConfiguration as $field => $attributes) {
				// Field ID will be the attribute name, prefixed with tinymce_modal.
				$fieldID = '#tinymce_modal_' . $field;

				// If the attribute has a third value, we may need to open the panel
				// to see the fields.
				if (count($attributes) > 2) {
					$I->click($attributes[2], '#convertkit-modal-body');
				}

				// Depending on the field's type, define its value.
				switch ($attributes[0]) {
					case 'select':
						$I->selectOption('#convertkit-modal-body-body ' . $fieldID, $attributes[1]);
						break;
					case 'toggle':
						$I->selectOption('#convertkit-modal-body-body ' . $fieldID, $attributes[1]);
						break;
					default:
						$I->fillField('#convertkit-modal-body-body ' . $fieldID, $attributes[1]);
						break;
				}
			}
		}

		// Click the Insert button.
		$I->click('#convertkit-modal-body div.mce-insert button');

		// Confirm the modal closes.
		$I->waitForElementNotVisible('#convertkit-modal-body');

		// If the expected shortcode output is provided, check it exists in the Visual editor.
		if ($expectedShortcodeOutput) {
			$I->switchToIFrame('iframe#' . $targetEditor . '_ifr');
			$I->seeInSource($expectedShortcodeOutput);
			$I->switchToIFrame();
		}
	}

	/**
	 * Open the Visual Editor (TinyMCE) modal for the given shortcode.
	 *
	 * @since   2.2.4
	 *
	 * @param   EndToEndTester $I                          EndToEnd Tester.
	 * @param   string         $shortcodeName              Shortcode Name (e.g. 'Kit Form').
	 * @param   string         $targetEditor               Target TinyMCE editor instance.
	 */
	public function openVisualEditorShortcodeModal($I, $shortcodeName, $targetEditor = 'content')
	{
		// Scroll to the applicable TinyMCE editor.
		switch ($targetEditor) {
			case 'excerpt':
				$I->scrollTo('#tagsdiv-product_tag');
				break;
			default:
				$I->scrollTo('h1.wp-heading-inline');
				break;
		}

		// Click the Visual tab on the applicable TinyMCE editor.
		$I->click('button#' . $targetEditor . '-tmce');

		// Click the TinyMCE Button for this shortcode.
		$I->click('#wp-' . $targetEditor . '-editor-container div.mce-container div[aria-label="' . $shortcodeName . '"] button');

		// Confirm that the modal is displayed.
		$I->waitForElementVisible('#convertkit-modal-body');
	}

	/**
	 * Add the given shortcode when adding or editing a Page, Post or Custom Post Type
	 * in the Text Editor.
	 *
	 * If a shortcode configuration is specified, applies it to the newly added shortcode.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I                          EndToEnd Tester.
	 * @param   string         $shortcodeProgrammaticName  Programmatic Shortcode Name (e.g. 'convertkit-form').
	 * @param   bool|array     $shortcodeConfiguration     Shortcode Configuration (field => value key/value array).
	 * @param   bool|string    $expectedShortcodeOutput    Expected Shortcode Output (e.g. [convertkit_form form="12345"]).
	 * @param   string         $targetEditor               ID of text editor instance.
	 */
	public function addTextEditorShortcode($I, $shortcodeProgrammaticName, $shortcodeConfiguration = false, $expectedShortcodeOutput = false, $targetEditor = 'content')
	{
		// Open Text Editor shortcode modal.
		$I->openTextEditorShortcodeModal($I, $shortcodeProgrammaticName, $targetEditor);

		// Wait for the modal's form to load.
		$I->waitForElementVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup');

		// If a shortcode configuration is specified, apply it to the shortcode's modal window now.
		if ($shortcodeConfiguration) {
			foreach ($shortcodeConfiguration as $field => $attributes) {
				// Field ID will be the attribute name, prefixed with tinymce_modal.
				$fieldID = '#tinymce_modal_' . $field;

				// If the attribute has a third value, we may need to open the panel
				// to see the fields.
				if (count($attributes) > 2) {
					$I->click($attributes[2], '#convertkit-quicktags-modal');
				}

				// Depending on the field's type, define its value.
				switch ($attributes[0]) {
					case 'select':
						$I->selectOption($fieldID, $attributes[1]);
						break;
					case 'toggle':
						$I->selectOption($fieldID, $attributes[1]);
						break;
					default:
						$I->fillField($fieldID, $attributes[1]);
						break;
				}
			}
		}

		// Click the Insert button.
		$I->click('#convertkit-quicktags-modal button.button-primary');

		// Confirm the modal closes.
		$I->waitForElementNotVisible('#convertkit-quicktags-modal');

		// If the expected shortcode output is provided, check it exists in the Text editor.
		if ($expectedShortcodeOutput) {
			$I->seeInField('textarea#' . $targetEditor, $expectedShortcodeOutput);
		}
	}

	/**
	 * Open the Text Editor modal for the given shortcode.
	 *
	 * @since   2.2.4
	 *
	 * @param   EndToEndTester $I                          EndToEnd Tester.
	 * @param   string         $shortcodeProgrammaticName  Programmatic Shortcode Name (e.g. 'convertkit-form').
	 * @param   string         $targetEditor               Target TinyMCE editor instance.
	 */
	public function openTextEditorShortcodeModal($I, $shortcodeProgrammaticName, $targetEditor = 'content')
	{
		// Scroll to the applicable TinyMCE editor.
		switch ($targetEditor) {
			case 'excerpt':
				$I->scrollTo('#tagsdiv-product_tag');
				break;
			default:
				$I->scrollTo('h1.wp-heading-inline');
				break;
		}

		// Click the Text tab.
		$I->click('button#' . $targetEditor . '-html');

		// Click the QuickTags Button for this shortcode.
		$I->click('input#qt_' . $targetEditor . '_' . $shortcodeProgrammaticName);

		// Confirm that the modal is displayed.
		$I->waitForElementVisible('#convertkit-quicktags-modal');
	}

	/**
	 * Adds a link to the given Page, Post or Custom Post Type Name using the Classic Editor's
	 * link button.
	 *
	 * @since   2.0.0
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 * @param   string         $name   Page, Post or Custom Post Type Title/Name to link to.
	 */
	public function addClassicEditorLink($I, $name)
	{
		// Click link button in toolbar.
		$I->click('div.mce-container i.mce-i-link');

		// Enter Product name in search field.
		$I->waitForElementVisible('input.ui-autocomplete-input');
		$I->fillField('input.ui-autocomplete-input', $name);
		$I->waitForElementVisible('ul.wplink-autocomplete');

		// Click the Product name in the search list.
		$I->click('ul.wplink-autocomplete li');

		// Press the enter key to insert the link.
		$I->pressKey('input.ui-autocomplete-input', \Facebook\WebDriver\WebDriverKeys::ENTER);
	}

	/**
	 * Publish a Page, Post or Custom Post Type initiated by the addClassicEditorPage() function.
	 *
	 * @since   2.5.6
	 *
	 * @param   EndToEndTester $I     EndToEnd Tester.
	 */
	public function publishClassicEditorPage($I)
	{
		// Scroll to Publish meta box, so its buttons are not hidden.
		$I->scrollTo('#submitdiv');

		// Wait for the Publish button to change its state from disabled (WordPress disables it for a moment when auto-saving).
		$I->waitForElementVisible('input#publish:not(:disabled)');

		// Click the Publish button.
		$I->click('input#publish');

		// Wait for notice to display.
		$I->waitForElementVisible('.notice-success');
	}

	/**
	 * Publish a Page, Post or Custom Post Type initiated by the addClassicEditorPage() function,
	 * loading it on the frontend web site.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I     EndToEnd Tester.
	 */
	public function publishAndViewClassicEditorPage($I)
	{
		// Publish Page.
		$I->publishClassicEditorPage($I);

		// Load the Page on the frontend site.
		$I->click('.notice-success a');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Add a Page, Post or Custom Post Type directly to the WordPress database,
	 * with dummy content used for testing.
	 *
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I                     EndToEnd Tester.
	 * @param   string         $postType              Post Type.
	 * @param   string         $title                 Post Title.
	 * @param   string         $formID                Meta Box `Form` value (-1: Default).
	 */
	public function addClassicEditorPageToDatabase($I, $postType = 'page', $title = 'Classic Editor Title', $formID = '-1')
	{
		return $I->havePostInDatabase(
			[
				'post_title'   => $title,
				'post_type'    => $postType,
				'post_status'  => 'publish',
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'         => $formID,
						'landing_page' => '',
						'tag'          => '',
					],
				],
				'post_content' => 'Item #1

<h2 class="wp-block-heading">Item #1</h2>

Item #2: Adhaésionés altéram improbis mi pariendarum sit stulti triarium

<figure class="wp-block-image size-large"><img src="https://placehold.co/600x400" alt="Image #1" /></figure>

<h2 class="wp-block-heading">Item #2</h2>

Item #3

<figure class="wp-block-image size-full"><img src="https://placehold.co/600x400" alt="Image #2" /></figure>

<h3 class="wp-block-heading">Item #1</h3>

Item #4

<h4 class="wp-block-heading">Item #1</h4>

Item #5

<h5 class="wp-block-heading">Item #1</h5>

Item #6

<h6 class="wp-block-heading">Item #1</h6>

Item #7

<h3 class="wp-block-heading">Item #2</h3>

<h4 class="wp-block-heading">Item #2</h4>

<h5 class="wp-block-heading">Item #2</h5>

<h6 class="wp-block-heading">Item #2</h6>',
			]
		);
	}
}
