<?php
/**
 * ConvertKit Forms Resource class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Reads ConvertKit Forms from the options table, and refreshes
 * ConvertKit Forms data stored locally from the API.
 *
 * @since   1.9.6
 */
class ConvertKit_Resource_Forms extends ConvertKit_Resource_V4 {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = 'convertkit_forms';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = 'forms';

	/**
	 * Constructor.
	 *
	 * @since   1.9.8.4
	 *
	 * @param   bool|string $context    Context.
	 */
	public function __construct( $context = false ) {

		// Initialize the API if the Access Token has been defined in the Plugin Settings.
		$settings = new ConvertKit_Settings();
		if ( $settings->has_access_and_refresh_token() ) {
			$this->api = new ConvertKit_API_V4(
				CONVERTKIT_OAUTH_CLIENT_ID,
				CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
				$settings->get_access_token(),
				$settings->get_refresh_token(),
				$settings->debug_enabled(),
				$context
			);
		}

		// Call parent initialization function.
		parent::init();

	}

	/**
	 * Returns all non-inline forms based on the sort order.
	 *
	 * @since   2.2.4
	 *
	 * @return  bool|array
	 */
	public function get_non_inline() {

		// If the ConvertKit WordPress Libraries are < 1.3.6 (e.g. loaded by an outdated
		// addon), or a WordPress site updates this Plugin before other ConvertKit Plugins,
		// get_by() won't be available and will cause an E_ERROR, crashing the site.
		// @see https://wordpress.org/support/topic/error-1795/.
		if ( ! method_exists( $this, 'get_by' ) ) { // @phpstan-ignore-line Older WordPress Libraries won't have this function.
			return false;
		}

		return $this->get_by( 'format', array( 'modal', 'slide in', 'sticky bar' ) );

	}


	/**
	 * Returns whether any non-inline forms exist in the options table.
	 *
	 * @since   2.2.4
	 *
	 * @return  bool
	 */
	public function non_inline_exist() {

		if ( ! $this->get_non_inline() ) {
			return false;
		}

		return true;

	}

	/**
	 * Determines if the given Form ID is a legacy Form or Landing Page.
	 *
	 * @since   2.5.0
	 *
	 * @param   int $id     Form or Landing Page ID.
	 */
	public function is_legacy( $id ) {

		// Get Form.
		$form = $this->get_by_id( (int) $id );

		// Return false if no Form exists.
		if ( ! $form ) {
			return false;
		}

		// If the `format` key exists, this is not a legacy Form.
		if ( array_key_exists( 'format', $form ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Returns a <select> field populated with all forms, based on the given parameters.
	 *
	 * @since   2.3.9
	 *
	 * @param   string            $name            Name.
	 * @param   string            $id              ID.
	 * @param   bool|array        $css_classes     <select> CSS class(es).
	 * @param   string            $selected_option <option> value to mark as selected.
	 * @param   bool|array        $prepend_options <option> elements to prepend before resources.
	 * @param   bool|array        $attributes      <select> attributes.
	 * @param   bool|string|array $description     Description.
	 * @return  string                             HTML Select Field
	 */
	public function get_select_field_all( $name, $id, $css_classes, $selected_option, $prepend_options = false, $attributes = false, $description = false ) {

		return $this->get_select_field(
			$this->get(),
			$name,
			$id,
			$css_classes,
			$selected_option,
			$prepend_options,
			$attributes,
			$description
		);

	}

	/**
	 * Returns a <select> field populated with all non-inline forms, based on the given parameters.
	 *
	 * @since   2.3.9
	 *
	 * @param   string            $name             Name.
	 * @param   string            $id               ID.
	 * @param   bool|array        $css_classes      <select> CSS class(es).
	 * @param   array             $selected_options <option> values to mark as selected.
	 * @param   bool|array        $prepend_options  <option> elements to prepend before resources.
	 * @param   bool|array        $attributes       <select> attributes.
	 * @param   bool|string|array $description      Description.
	 * @return  string                              HTML Select Field
	 */
	public function get_select_field_non_inline( $name, $id, $css_classes, $selected_options, $prepend_options = false, $attributes = false, $description = false ) {

		return $this->get_multi_select_field(
			$this->get_non_inline(),
			$name,
			$id,
			$css_classes,
			$selected_options,
			$prepend_options,
			$attributes,
			$description
		);

	}

	/**
	 * Returns a <select> field populated with the resources, based on the given parameters.
	 *
	 * @since   2.3.9
	 *
	 * @param   array             $forms           Forms.
	 * @param   string            $name            Name.
	 * @param   string            $id              ID.
	 * @param   bool|array        $css_classes     <select> CSS class(es).
	 * @param   string            $selected_option <option> value to mark as selected.
	 * @param   bool|array        $prepend_options <option> elements to prepend before resources.
	 * @param   bool|array        $attributes      <select> attributes.
	 * @param   bool|string|array $description     Description.
	 * @return  string                             HTML Select Field
	 */
	private function get_select_field( $forms, $name, $id, $css_classes, $selected_option, $prepend_options = false, $attributes = false, $description = false ) {

		$html = sprintf(
			'<select name="%s" id="%s" class="%s"',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( ( is_array( $css_classes ) ? implode( ' ', $css_classes ) : '' ) )
		);

		// Append any attributes.
		if ( $attributes ) {
			foreach ( $attributes as $key => $value ) {
				$html .= sprintf(
					' %s="%s"',
					esc_attr( $key ),
					esc_attr( $value )
				);
			}
		}

		// Close select tag.
		$html .= '>';

		// If any prepended options exist, add them now.
		if ( $prepend_options ) {
			foreach ( $prepend_options as $value => $label ) {
				$html .= sprintf(
					'<option value="%s" data-preserve-on-refresh="1"%s>%s</option>',
					esc_attr( $value ),
					selected( $selected_option, $value, false ),
					esc_attr( $label )
				);
			}
		}

		// Iterate through resources, if they exist, building <option> elements.
		if ( $forms ) {
			foreach ( $forms as $form ) {
				// Legacy forms don't include a `format` key, so define them as inline.
				$html .= sprintf(
					'<option value="%s"%s>%s [%s]</option>',
					esc_attr( $form['id'] ),
					selected( $selected_option, $form['id'], false ),
					esc_attr( $form['name'] ),
					( ! empty( $form['format'] ) ? esc_attr( $form['format'] ) : 'inline' )
				);
			}
		}

		// Close select.
		$html .= '</select>';

		// If no description is provided, return the select field now.
		if ( ! $description ) {
			return $html;
		}

		// Append description before returning field.
		if ( ! is_array( $description ) ) {
			return $html . '<p class="description">' . $description . '</p>';
		}

		// Return description lines in a paragraph, using breaklines for each description entry in the array.
		return $html . '<p class="description">' . implode( '<br />', $description ) . '</p>';

	}

	/**
	 * Returns a <select> field populated with the resources, based on the given parameters,
	 * that supports multiple selection.
	 *
	 * @since   2.6.9
	 *
	 * @param   array             $forms            Forms.
	 * @param   string            $name             Name.
	 * @param   string            $id               ID.
	 * @param   bool|array        $css_classes      <select> CSS class(es).
	 * @param   array             $selected_options <option> values to mark as selected.
	 * @param   bool|array        $prepend_options  <option> elements to prepend before resources.
	 * @param   bool|array        $attributes       <select> attributes.
	 * @param   bool|string|array $description      Description.
	 * @return  string                              HTML Select Field
	 */
	private function get_multi_select_field( $forms, $name, $id, $css_classes, $selected_options = array(), $prepend_options = false, $attributes = false, $description = false ) {

		$html = sprintf(
			'<select name="%s[]" id="%s" class="%s" multiple',
			esc_attr( $name ),
			esc_attr( $id ),
			esc_attr( ( is_array( $css_classes ) ? implode( ' ', $css_classes ) : '' ) )
		);

		// Append any attributes.
		if ( $attributes ) {
			foreach ( $attributes as $key => $value ) {
				$html .= sprintf(
					' %s="%s"',
					esc_attr( $key ),
					esc_attr( $value )
				);
			}
		}

		// Close select tag.
		$html .= '>';

		// If any prepended options exist, add them now.
		if ( $prepend_options ) {
			foreach ( $prepend_options as $value => $label ) {
				$html .= sprintf(
					'<option value="%s" data-preserve-on-refresh="1"%s>%s</option>',
					esc_attr( $value ),
					( in_array( $value, $selected_options, true ) ? ' selected' : '' ),
					esc_attr( $label )
				);
			}
		}

		// Iterate through resources, if they exist, building <option> elements.
		if ( $forms ) {
			foreach ( $forms as $form ) {
				// Legacy forms don't include a `format` key, so define them as inline.
				$html .= sprintf(
					'<option value="%s"%s>%s [%s]</option>',
					esc_attr( $form['id'] ),
					( in_array( $form['id'], $selected_options, true ) ? ' selected' : '' ),
					esc_attr( $form['name'] ),
					( ! empty( $form['format'] ) ? esc_attr( $form['format'] ) : 'inline' )
				);
			}
		}

		// Close select.
		$html .= '</select>';

		// If no description is provided, return the select field now.
		if ( ! $description ) {
			return $html;
		}

		// Append description before returning field.
		if ( ! is_array( $description ) ) {
			return $html . '<p class="description">' . $description . '</p>';
		}

		// Return description lines in a paragraph, using breaklines for each description entry in the array.
		return $html . '<p class="description">' . implode( '<br />', $description ) . '</p>';

	}

	/**
	 * Returns the HTML/JS markup for the given Form ID.
	 *
	 * Legacy Forms will return HTML.
	 * Current Forms will return a <script> embed string.
	 *
	 * @since   1.9.6
	 *
	 * @param   int $id     Form ID.
	 * @return  WP_Error|string
	 */
	public function get_html( $id ) {

		// Cast ID to integer.
		$id = absint( $id );

		// Bail if the resources are a WP_Error.
		if ( is_wp_error( $this->resources ) ) {
			return $this->resources;
		}

		// Bail if the resource doesn't exist.
		if ( ! isset( $this->resources[ $id ] ) ) {
			return new WP_Error(
				'convertkit_resource_forms_get_html',
				sprintf(
					/* translators: ConvertKit Form ID */
					__( 'Kit Form ID %s does not exist on Kit.', 'convertkit' ),
					$id
				)
			);
		}

		// If no uid is present in the Form API data, this is a legacy form that's served by directly fetching the HTML
		// from forms.kit.com.
		if ( ! isset( $this->resources[ $id ]['uid'] ) ) {
			// Initialize Settings.
			$settings = new ConvertKit_Settings();

			// Bail if no Access Token is specified in the Plugin Settings.
			if ( ! $settings->has_access_token() ) {
				return new WP_Error(
					'convertkit_resource_forms_get_html',
					__( 'Kit Legacy Form could not be fetched as no Access Token specified in Plugin Settings', 'convertkit' )
				);
			}

			// Initialize the API.
			$api = new ConvertKit_API_V4(
				CONVERTKIT_OAUTH_CLIENT_ID,
				CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
				$settings->get_access_token(),
				$settings->get_refresh_token(),
				$settings->debug_enabled(),
				'output_form'
			);

			// Return Legacy Form HTML.
			// We now call get_html() with the `embed_url` property, instead of get_form_html() with the `id` property,
			// because `embed_url` includes the API Key.
			return $api->get_html( $this->resources[ $id ]['embed_url'] );
		}

		// If the form's format is not an inline form, add the inline script before the closing </body> tag.
		// This prevents a modal form's overlay being constrained by the WordPress Theme's styles,
		// and accidentally embedding the same non-inline form twice, which would result in e.g. the same modal form
		// displaying twice.
		if ( $this->resources[ $id ]['format'] !== 'inline' ) {
			add_filter(
				'convertkit_output_scripts_footer',
				function ( $scripts ) use ( $id ) {

					$scripts[] = array(
						'async'    => true,
						'data-uid' => $this->resources[ $id ]['uid'],
						'src'      => $this->resources[ $id ]['embed_js'],
					);

					return $scripts;

				}
			);

			// Sanity check we're not in the WordPress Admin interface.
			// Some third party REST API Plugins seem to load frontend Posts, which would result in a wp_die() as
			// the output Plugin class (rightly) isn't initialized in the backend.
			if ( is_admin() ) {
				return '';
			}

			// Don't output the global non-inline form, if defined, because
			// a non-inline form was specified at either Post/Page default level, Post/Page level
			// or Post Category level.
			// This prevents multiple non-inline forms loading.
			remove_action( 'wp_footer', array( WP_ConvertKit()->get_class( 'output' ), 'output_global_non_inline_form' ), 1 );

			// Don't return a script for output, as it'll be output in the site's footer.
			return '';
		}

		// If here, return Form <script> embed now, as we want the inline form to display at this specific point of the content.

		// Define script key-value pairs.
		$script = array(
			'async'    => true,
			'data-uid' => $this->resources[ $id ]['uid'],
			'src'      => $this->resources[ $id ]['embed_js'],
		);

		/**
		 * Filter the form <script> key/value pairs immediately before the script is output.
		 *
		 * @since   2.4.5
		 *
		 * @param   array   $script     Form script key/value pairs to output as <script> tag.
		 */
		$script = apply_filters( 'convertkit_resource_forms_output_script', $script );

		// Build script output.
		$output = '<script';
		foreach ( $script as $attribute => $value ) {
			// If the value is true, just output the attribute.
			if ( $value === true ) {
				$output .= ' ' . esc_attr( $attribute );
				continue;
			}

			// Sanitize attribute and value.
			$attribute = esc_attr( $attribute );
			$value     = ( $attribute === 'src' ? esc_url( $value ) : esc_attr( $value ) );

			// Output the attribute and value.
			$output .= ' ' . $attribute;

			// Output the value, if it's not a blank string.
			if ( strlen( $value ) > 0 ) {
				$output .= '="' . $value . '"';
			}
		}
		$output .= '></script>';

		// Return script output.
		return $output;

	}

}
