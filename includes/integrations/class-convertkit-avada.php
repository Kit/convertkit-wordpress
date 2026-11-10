<?php
/**
 * ConvertKit Avada Theme Integration.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Adds compatibility with the Avada theme by not restricting content
 * in Avada Layout Sections that are not the Post's content, such as
 * the Header, Page Title Bar and Footer.
 *
 * @since   3.5.0
 */
class ConvertKit_Avada {

	/**
	 * Avada Layout Sections that should not have Restrict Content applied.
	 *
	 * @since   3.5.0
	 *
	 * @var     array
	 */
	private $sections = array(
		'header',
		'page_title_bar',
		'footer',
	);

	/**
	 * Whether the Restrict Content filter was removed whilst rendering an Avada Layout Section.
	 *
	 * @since   3.5.0
	 *
	 * @var     bool
	 */
	private $restrict_content_filter_removed = false;

	/**
	 * Constructor.
	 *
	 * @since   3.5.0
	 */
	public function __construct() {

		// Avada fires these actions immediately before and after running a Layout Section through `the_content`.
		add_action( 'awb_remove_third_party_the_content_changes', array( $this, 'maybe_remove_restrict_content_filter' ) );
		add_action( 'awb_readd_third_party_the_content_changes', array( $this, 'maybe_readd_restrict_content_filter' ) );

	}

	/**
	 * Removes the Restrict Content filter if Avada is rendering a Header, Page Title Bar or Footer Layout Section.
	 *
	 * @since   3.5.0
	 */
	public function maybe_remove_restrict_content_filter() {

		// Bail if Avada Builder is not active.
		if ( ! function_exists( 'Fusion_Template_Builder' ) ) {
			return;
		}

		// Bail if the Layout Section being rendered might be the Post's content.
		if ( ! in_array( Fusion_Template_Builder()->get_current_override_name(), $this->sections, true ) ) { // @phpstan-ignore-line
			return;
		}

		// Remove the Restrict Content filter, tracking if it was registered so it's only re-added if it was removed.
		if ( remove_filter( 'the_content', array( WP_ConvertKit()->get_class( 'output_restrict_content' ), 'maybe_restrict_content' ) ) ) {
			$this->restrict_content_filter_removed = true;
		}

	}

	/**
	 * Re-adds the Restrict Content filter if it was removed by maybe_remove_restrict_content_filter().
	 *
	 * @since   3.5.0
	 */
	public function maybe_readd_restrict_content_filter() {

		// Bail if the Restrict Content filter wasn't removed.
		if ( ! $this->restrict_content_filter_removed ) {
			return;
		}

		add_filter( 'the_content', array( WP_ConvertKit()->get_class( 'output_restrict_content' ), 'maybe_restrict_content' ) );
		$this->restrict_content_filter_removed = false;

	}

}

new ConvertKit_Avada();
