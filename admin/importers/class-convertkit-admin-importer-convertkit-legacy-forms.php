<?php
/**
 * ConvertKit Admin Importer MC4WP class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Import and migrate data from Mailchimp (MC4WP) to Kit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms extends ConvertKit_Admin_Importer {

	/**
	 * Holds the programmatic name of the importer (lowercase, no spaces).
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $name = 'convertkit_legacy_forms';

	/**
	 * Holds the title of the importer (for display in the importer list).
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $title = 'Kit Legacy Forms';

	/**
	 * Holds the shortcode name for ConvertKit Legacy Forms.
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $shortcode_name = 'convertkit_form';

	/**
	 * Holds the ID attribute name for ConvertKit Legacy Forms.
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = 'form';

	/**
	 * Holds the block name for ConvertKit Legacy Forms.
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $block_name = 'convertkit/form';

	/**
	 * Holds the ID attribute name for ConvertKit Legacy Forms.
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $block_id_attribute = 'form';

	/**
	 * Constructor
	 *
	 * @since   3.3.5
	 */
	public function __construct() {

		// Define a custom description for this importer.
		$this->description = __( 'Kit Legacy Forms are being phased out. Use this tool to replace Kit Form shortcodes and blocks using a Legacy Form with a new Kit Form.', 'convertkit' );

		// Register this as an importer, if ConvertKit Legacy Forms exist.
		add_filter( 'convertkit_get_form_importers', array( $this, 'register' ) );

	}

	/**
	 * Returns an array of Kit Legacy Forms form IDs and titles.
	 *
	 * @since   3.3.5
	 *
	 * @return  array
	 */
	public function get_forms() {

		// Query resource class to fetch legacy forms.
		$forms            = array();
		$convertkit_forms = new ConvertKit_Resource_Forms( 'settings' );
		if ( $convertkit_forms->exist() ) {
			foreach ( $convertkit_forms->get() as $form ) {
				// Skip if not a Legacy Form.
				if ( ! $convertkit_forms->is_legacy( $form['id'] ) ) {
					continue;
				}

				$forms[ $form['id'] ] = $form['name'];
			}
		}

		return $forms;

	}

	/**
	 * Returns an array of legacy Kit form IDs (as strings) for use in
	 * filtering shortcodes/blocks that reference them.
	 *
	 * @since   3.3.5
	 *
	 * @return  array
	 */
	private function get_legacy_form_ids() {

		$legacy_ids       = array();
		$convertkit_forms = new ConvertKit_Resource_Forms( 'settings' );

		if ( ! $convertkit_forms->exist() ) {
			return $legacy_ids;
		}

		foreach ( $convertkit_forms->get() as $form ) {
			if ( ! $convertkit_forms->is_legacy( $form['id'] ) ) {
				continue;
			}
			$legacy_ids[] = (string) $form['id'];
		}

		return $legacy_ids;

	}

	/**
	 * Overrides the parent method to:
	 * - return form IDs found in both shortcodes AND blocks (the parent only
	 *   handles shortcodes), and
	 * - filter the result so only legacy form IDs are returned.
	 *
	 * @since   3.3.5
	 *
	 * @param   string $content    Content containing Kit Form shortcodes / blocks.
	 * @return  array
	 */
	public function get_form_ids_from_content( $content ) {

		// Get shortcode-derived form IDs from the parent.
		$shortcode_ids = parent::get_form_ids_from_content( $content );

		// Get block-derived form IDs (parent only handles shortcodes).
		$block_ids = $this->get_block_form_ids_from_content( $content );

		// Combine and filter to legacy form IDs only.
		$all_ids    = array_unique( array_merge( $shortcode_ids, $block_ids ) );
		$legacy_ids = $this->get_legacy_form_ids();

		// Cast both sides to strings for safe comparison.
		$all_ids = array_map( 'strval', $all_ids );

		return array_values( array_intersect( $all_ids, $legacy_ids ) );

	}

	/**
	 * Returns an array of form IDs from convertkit/form blocks in the given
	 * content. Walks innerBlocks recursively.
	 *
	 * @since   3.3.5
	 *
	 * @param   string $content    Content containing Kit Form blocks.
	 * @return  array
	 */
	private function get_block_form_ids_from_content( $content ) {

		return $this->extract_block_form_ids( parse_blocks( $content ) );

	}

	/**
	 * Recursively walks blocks (and innerBlocks) and returns an array of form
	 * IDs from any convertkit/form block's `form` attribute.
	 *
	 * @since   3.3.5
	 *
	 * @param   array $blocks    Blocks.
	 * @return  array
	 */
	private function extract_block_form_ids( $blocks ) {

		$form_ids = array();

		foreach ( $blocks as $block ) {
			if ( ! empty( $block['innerBlocks'] ) ) {
				$form_ids = array_merge(
					$form_ids,
					$this->extract_block_form_ids( $block['innerBlocks'] )
				);
			}

			if ( $block['blockName'] !== $this->block_name ) {
				continue;
			}

			if ( empty( $block['attrs'][ $this->block_id_attribute ] ) ) {
				continue;
			}

			$form_ids[] = (string) $block['attrs'][ $this->block_id_attribute ];
		}

		return $form_ids;

	}

	/**
	 * Overrides the parent method to only return post IDs whose content
	 * contains a Kit Form shortcode or block referencing a legacy form ID.
	 *
	 * The parent's broad SQL match returns any post containing a
	 * `[convertkit_form` shortcode or `<!-- wp:convertkit/form` block, which
	 * for the Kit Legacy Forms importer is too broad: non-legacy uses of these
	 * shortcodes/blocks are valid and should not appear in the importer UI.
	 *
	 * @since   3.3.5
	 *
	 * @return  array
	 */
	public function get_forms_in_posts() {

		$candidate_post_ids = parent::get_forms_in_posts();

		if ( empty( $candidate_post_ids ) ) {
			return array();
		}

		$legacy_ids = $this->get_legacy_form_ids();
		if ( empty( $legacy_ids ) ) {
			return array();
		}

		$matched_post_ids = array();
		foreach ( $candidate_post_ids as $post_id ) {
			$content = get_post_field( 'post_content', $post_id );

			// get_form_ids_from_content() (overridden above) returns only legacy IDs
			// from both shortcodes and blocks, so any non-empty result means this
			// post contains at least one legacy form reference.
			$legacy_form_ids_in_content = $this->get_form_ids_from_content( $content );
			if ( ! empty( $legacy_form_ids_in_content ) ) {
				$matched_post_ids[] = $post_id;
			}
		}

		return $matched_post_ids;

	}

}
