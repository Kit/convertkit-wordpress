<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to metaboxes,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class WPMetabox extends \Codeception\Module
{
	/**
	 * Configure a given metabox's fields with the given values.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   AcceptanceHelper $I              Acceptance Helper.
	 * @param   string           $metabox        Programmatic Metabox Name.
	 * @param   array            $configuration  Metabox Configuration (field => value key/value array).
	 */
	public function configureMetaboxSettings($I, $metabox, $configuration)
	{
		// Check that the metabox exists.
		$I->seeElementInDOM('#' . $metabox);

		// Apply configuration.
		foreach ($configuration as $field => $attributes) {
			// Field ID will be prefixed with wp-convertkit- in the metabox.
			$fieldID = 'wp-convertkit-' . $field;

			// Check that the field exists.
			$I->seeElementInDOM('#' . $fieldID);

			// Depending on the field's type, define its value.
			switch ($attributes[0]) {
				case 'select2':
					$I->fillSelect2Field($I, '#select2-' . $fieldID . '-container', $attributes[1]);
					break;
				case 'select':
					$I->selectOption('#' . $fieldID, $attributes[1]);
					break;
				default:
					$I->fillField('#' . $fieldID, $attributes[1]);
					break;
			}
		}
	}
}
