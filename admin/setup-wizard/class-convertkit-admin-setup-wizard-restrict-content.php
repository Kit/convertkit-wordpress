<?php
/**
 * ConvertKit Admin Setup Wizard for Restrict Content class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Provides a UI for setting up Member's Only Content.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Setup_Wizard_Restrict_Content extends ConvertKit_Admin_Setup_Wizard {

	/**
	 * Holds the Post Type to generate Members Content for.
	 *
	 * @since   2.1.0
	 *
	 * @var     string
	 */
	public $post_type = 'page';

	/**
	 * Holds the type of Member's Content to generate (course|download).
	 *
	 * @since   2.1.0
	 *
	 * @var     string
	 */
	public $type = 'download';

	/**
	 * Holds the label for the type of Member's Content to generate.
	 *
	 * @since   2.1.0
	 *
	 * @var     string
	 */
	public $type_label = '';

	/**
	 * Holds the ConvertKit Forms resource class.
	 *
	 * @since   2.8.3
	 *
	 * @var     bool|ConvertKit_Resource_Forms
	 */
	public $forms = false;

	/**
	 * Holds the ConvertKit Products resource class.
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|ConvertKit_Resource_Products
	 */
	public $products = false;

	/**
	 * Holds the ConvertKit Tags resource class.
	 *
	 * @since   2.3.3
	 *
	 * @var     bool|ConvertKit_Resource_Tags
	 */
	public $tags = false;

	/**
	 * Holds the Pages created by this setup wizard.
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|array
	 */
	public $pages = false;

	/**
	 * Holds the URL to the setup wizard screen for the Download type of content.
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|string
	 */
	public $download_url = false;

	/**
	 * Holds the URL to the setup wizard screen for the Course type of content.
	 *
	 * @since   2.1.0
	 *
	 * @var     bool|string
	 */
	public $course_url = false;

	/**
	 * Holds the URL to the current setup wizard screen.
	 *
	 * @since   2.3.3
	 *
	 * @var     bool|string
	 */
	public $current_url = false;

	/**
	 * The required user capability to access the setup wizard.
	 *
	 * @since   2.1.0
	 *
	 * @var     string
	 */
	public $required_capability = 'edit_posts';

	/**
	 * The programmatic name for this wizard.
	 *
	 * @since   2.1.0
	 *
	 * @var     string
	 */
	public $page_name = 'convertkit-restrict-content-setup';

	/**
	 * The URL to take the user to when they click the Exit link.
	 *
	 * @since   2.1.0
	 *
	 * @var     string
	 */
	public $exit_url = 'edit.php?post_type=page';

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   2.1.0
	 */
	public function __construct() {

		// Define details for each step in the setup process.
		$this->steps = array(
			1 => array(
				'name' => __( 'Setup', 'convertkit' ),
			),
			2 => array(
				'name'        => __( 'Configure', 'convertkit' ),
				'next_button' => array(
					'label' => __( 'Submit', 'convertkit' ),
				),
			),
			3 => array(
				'name' => __( 'Done', 'convertkit' ),
			),
		);

		add_action( 'convertkit_admin_setup_wizard_process_form_convertkit-restrict-content-setup', array( $this, 'process_form' ) );
		add_action( 'convertkit_admin_setup_wizard_load_screen_data_convertkit-restrict-content-setup', array( $this, 'load_screen_data' ) );

		// Call parent class constructor.
		parent::__construct();

	}

	/**
	 * Process posted data from the submitted form.
	 *
	 * @since   2.1.0
	 *
	 * @param   int $step   Current step.
	 */
	public function process_form( $step ) {

		// Run security checks.
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), $this->page_name ) ) {
			$this->error = __( 'Invalid nonce specified.', 'convertkit' );
			return;
		}

		// Depending on the step, process the form data.
		switch ( $step ) {
			case 3:
				// Sanitize configuration.
				$configuration = array(
					'type'             => ( isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'download' ),
					'title'            => ( isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '' ),
					'description'      => ( isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '' ),
					'number_of_pages'  => ( isset( $_POST['number_of_pages'] ) ? absint( $_POST['number_of_pages'] ) : 0 ),
					'restrict_content' => ( isset( $_POST['restrict_content'] ) ? sanitize_text_field( wp_unslash( $_POST['restrict_content'] ) ) : '' ),
					'post_type'        => $this->post_type,
				);

				// Depending on the type of content selected, create WordPress Page(s) now.
				switch ( $configuration['type'] ) {
					/**
					 * Download
					 * - Single page with a link to a downloadable product.
					 */
					case 'download':
						$result = $this->create_download( $configuration );
						break;

					/**
					 * Course
					 */
					case 'course':
					default:
						$result = $this->create_course( $configuration );
						break;
				}

				// If here, an error occured as create_download() and create_course() perform a redirect on success.
				// Show an error message if Account Details could not be fetched e.g. API credentials supplied are invalid.
				// Decrement the step.
				$this->step  = ( $this->step - 1 );
				$this->error = $result->get_error_message();
				return;

		}

		// phpcs:enable

	}

	/**
	 * Load any data into class variables for the given setup wizard name and current step.
	 *
	 * @since   2.1.0
	 *
	 * @param   int $step   Current step.
	 */
	public function load_screen_data( $step ) {

		// Show an error screen if API credentials have not been specified.
		// This shouldn't happen, because the 'Add New Member Content' button is only displayed
		// if valid credentials have been specified.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			wp_die( esc_html__( 'Connect your ConvertKit account in the ConvertKit Plugin\'s settings to get started', 'convertkit' ) );
		}

		// Get the Post Type.
		if ( filter_has_var( INPUT_GET, 'ck_post_type' ) ) {
			$this->post_type = filter_input( INPUT_GET, 'ck_post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		} else {
			$this->post_type = 'page';
		}

		// Bail if the Post Type isn't supported.
		if ( ! in_array( $this->post_type, convertkit_get_supported_post_types(), true ) ) {
			wp_die(
				sprintf(
					/* translators: Post Type */
					esc_html__( 'The post type `%s` is not supported for Member Content.', 'convertkit' ),
					esc_html( $this->post_type )
				),
				esc_html__( 'WordPress Error', 'convertkit' ),
				array(
					'back_link' => true,
				)
			);
		}

		// Define Exit URL to take the user back to the WP_List_Table for the Post Type they were viewing.
		$this->exit_url = add_query_arg(
			array(
				'post_type' => $this->post_type,
			),
			admin_url( 'edit.php' )
		);

		// Load data depending on the current step.
		switch ( $step ) {
			case 1:
				// Fetch Forms, Products and Tags.
				$this->forms    = new ConvertKit_Resource_Forms( 'restrict_content_wizard' );
				$this->products = new ConvertKit_Resource_Products( 'restrict_content_wizard' );
				$this->tags     = new ConvertKit_Resource_Tags( 'restrict_content_wizard' );

				// Refresh Forms, Products and Tags resources, in case the user just created their first Form, Product or Tag
				// in ConvertKit.
				$this->forms->refresh();
				$this->products->refresh();
				$this->tags->refresh();

				// If no Forms, Products and Tags exist in ConvertKit, change the next button label and make it a link to reload
				// the screen.
				if ( ! $this->forms->exist() && ! $this->products->exist() && ! $this->tags->exist() ) {
					unset( $this->steps[1]['next_button'] );
					$this->current_url = add_query_arg(
						array(
							'page'         => $this->page_name,
							'ck_post_type' => $this->post_type,
							'step'         => 1,
						),
						admin_url( 'options.php' )
					);
				} else {
					// Define Download and Course button links.
					$this->download_url = add_query_arg(
						array(
							'type'         => 'download',
							'ck_post_type' => $this->post_type,
						),
						$this->next_step_url
					);

					$this->course_url = add_query_arg(
						array(
							'type'         => 'course',
							'ck_post_type' => $this->post_type,
						),
						$this->next_step_url
					);
				}
				break;

			case 2:
				// Define Member Content Type.
				if ( filter_has_var( INPUT_GET, 'type' ) ) {
					$this->type = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				} else {
					$this->type = 'download';
				}

				// Define Label for Title.
				switch ( $this->type ) {
					case 'download':
						$this->type_label = __( 'Download', 'convertkit' );
						break;
					case 'course':
						$this->type_label = __( 'Course', 'convertkit' );
						break;
				}

				// Fetch Forms, Products and Tags.
				$this->forms    = new ConvertKit_Resource_Forms( 'restrict_content_wizard' );
				$this->products = new ConvertKit_Resource_Products( 'restrict_content_wizard' );
				$this->tags     = new ConvertKit_Resource_Tags( 'restrict_content_wizard' );
				break;
		}

	}

	/**
	 * Creates a single WordPress Page for a downloadable product, restricted by
	 * a ConvertKit Form, Tag or Product, based on the supplied configuration.
	 *
	 * @since   2.1.0
	 *
	 * @param   array $configuration  Configuration.
	 * @return  WP_Error              WP_Error or Page ID
	 */
	private function create_download( $configuration ) {

		// Create Page.
		$page_id = $this->create_page(
			$configuration['title'],
			$configuration['description'],
			$configuration['post_type'],
			__( 'The downloadable member-only content goes here.', 'convertkit' ),
			$configuration['restrict_content']
		);

		// Bail if an error occured.
		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		// Redirect to the Pages WP_List_Table screen, showing the generated page.
		wp_safe_redirect(
			add_query_arg(
				array(
					's'         => rawurlencode( $configuration['title'] ),
					'post_type' => $configuration['post_type'],
				),
				'edit.php'
			)
		);
		die();

	}

	/**
	 * Creates multiple WordPress Pages for a course, restricted by
	 * a ConvertKit Form, Tag or Product, based on the supplied configuration.
	 *
	 * @since   2.1.0
	 *
	 * @param   array $configuration  Configuration.
	 * @return  WP_Error              WP_Error on error, wp_safe_redirect() on success.
	 */
	private function create_course( $configuration ) {

		// If here, we need to generate multiple pages.
		// Build top level Page, which pages will be children of.
		$parent_page_id = $this->create_page(
			$configuration['title'],
			$configuration['description'],
			$configuration['post_type']
		);

		// Bail if an error occured.
		if ( is_wp_error( $parent_page_id ) ) {
			return $parent_page_id;
		}

		// Build child Pages.
		$page_ids = array();
		for ( $i = 1; $i <= $configuration['number_of_pages']; $i++ ) {
			$result = $this->create_page(
				sprintf(
					'%s: %s/%s',
					$configuration['title'],
					$i,
					$configuration['number_of_pages']
				), // e.g. Title: 1/10.
				sprintf(
					'%s %s',
					esc_html__( 'Some introductory text about lesson', 'convertkit' ),
					$i
				),
				$configuration['post_type'],
				sprintf(
					'%s %s %s',
					esc_html__( 'Lesson', 'convertkit' ),
					$i,
					esc_html__( 'member-only content goes here.', 'convertkit' )
				),
				$configuration['restrict_content'],
				$i,
				$configuration['number_of_pages'],
				$parent_page_id
			);

			// Bail if an error occured.
			if ( is_wp_error( $result ) ) {
				return $result;
			}

			// Add Page ID to array.
			$page_ids[] = $result;
		}

		// Add a link from the parent page to the first child page.
		$this->add_link_from_parent_to_child_page( $parent_page_id, $page_ids[0] );

		// Redirect to the Pages WP_List_Table screen, showing the generated pages.
		wp_safe_redirect(
			add_query_arg(
				array(
					's'         => rawurlencode( $configuration['title'] ),
					'post_type' => $configuration['post_type'],
				),
				'edit.php'
			)
		);
		die();

	}

	/**
	 * Creates a WordPress Page for the given title, restricted to the given restrict content setting.
	 *
	 * @since   2.1.0
	 *
	 * @param   string      $title                      Page Title.
	 * @param   string      $content                    Non-restricted Content.
	 * @param   string      $post_type                  Post Type.
	 * @param   bool|string $restricted_content         Restricted Content.
	 * @param   bool|string $restrict_content_setting   ConvertKit Form, Tag or Product to restrict content to.
	 * @param   int         $page_number                Page Number.
	 * @param   int         $total_pages                Total Pages that will be created.
	 * @param   bool|int    $parent_page_id             Parent Page ID (false if none).
	 * @return  WP_Error|int                            Error or Page ID
	 */
	private function create_page( $title, $content, $post_type = 'page', $restricted_content = false, $restrict_content_setting = false, $page_number = 0, $total_pages = 0, $parent_page_id = false ) {

		// Build content.
		$content = $this->element_paragraph( $content );

		// If restricted content is defined, append it to the post content using a more block.
		if ( $restricted_content && $restrict_content_setting ) {
			$content .= $this->element_more_tag();
			$content .= $this->element_paragraph( $restricted_content );
		}

		// Define previous / next links, depending on the page number and total pages.
		$content .= $this->element_navigation( $page_number, $total_pages );

		// Build arguments to create Page.
		$args = array(
			'post_type'    => $post_type,
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_title'   => $title,
			'post_content' => $content,
			'menu_order'   => $page_number,
		);

		// If a parent page is specified, apply it to the arguments.
		if ( $parent_page_id !== false ) {
			$args['post_parent'] = $parent_page_id;
		}

		// Create Page.
		$page_id = wp_insert_post( $args, true );

		// Bail if an error occured.
		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		// Define Page's settings, ensuring no default Form displays.
		// If a restrict content setting was supplied, it's set to the Page now.
		WP_ConvertKit()->get_class( 'admin_post' )->save_post_settings(
			$page_id,
			array(
				'form'             => '0', // Don't display a Form.
				'restrict_content' => ( $restrict_content_setting !== false ? $restrict_content_setting : '0' ),
			)
		);

		// Return.
		return $page_id;

	}

	/**
	 * Returns a paragraph for the given text, depending on if the Classic Editor or Block Editor is used.
	 *
	 * @since   2.1.0
	 *
	 * @param   string $text   Text.
	 * @return  string          Text
	 */
	private function element_paragraph( $text ) {

		return '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';

	}

	/**
	 * Returns the more tag, depending on if the Classic Editor or Block Editor is used.
	 *
	 * @since   2.1.0
	 *
	 * @return  string  More Tag
	 */
	private function element_more_tag() {

		return '<!-- wp:more --><!--more--><!-- /wp:more -->';

	}

	/**
	 * Returns previous/next links, depending on if the Classic Editor or Block Editor is used.
	 *
	 * @since   2.1.0
	 *
	 * @param   int $page_number    Page Number.
	 * @param   int $total_pages    Total Pages.
	 * @return  string                  Previous / Next Links
	 */
	private function element_navigation( $page_number, $total_pages ) {

		// Assume no previous or next link required.
		$previous_link = '';
		$next_link     = '';

		// If the page number is greater than zero, a previous link is required.
		if ( $page_number > 0 ) {
			$previous_link = '<!-- wp:post-navigation-link {"type":"previous","label":"Previous Lesson"} /-->';
		}

		// If the page number is less than the total pages, a next link is required.
		if ( $page_number < $total_pages ) {
			$next_link = '<!-- wp:post-navigation-link {"textAlign":"right","label":"Next Lesson"} /-->';
		}

		// Return a blank string if no previous or next links required.
		if ( empty( $previous_link ) && empty( $next_link ) ) {
			return '';
		}

		// Return links.
		return '<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column">' . $previous_link . '</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">' . $next_link . '</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->';

	}

	/**
	 * Adds a link from the parent page to the child page.
	 *
	 * @since   2.1.0
	 *
	 * @param   int $parent_page_id     Parent Page ID.
	 * @param   int $child_page_id      Child Page ID.
	 */
	private function add_link_from_parent_to_child_page( $parent_page_id, $child_page_id ) {

		// Define button block linking to first child page.
		$button_block = '<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button">
<a class="wp-block-button__link" href="' . get_permalink( $child_page_id ) . '">' . __( 'Start Course', 'convertkit' ) . '</a>
</div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->';

		// Fetch parent page's content.
		$parent_page = get_post( $parent_page_id );

		// Update parent page's content to include a link to the first child page.
		return wp_update_post(
			array(
				'ID'           => $parent_page_id,
				'post_content' => $parent_page->post_content .= $button_block,
			),
			true
		);

	}

}
