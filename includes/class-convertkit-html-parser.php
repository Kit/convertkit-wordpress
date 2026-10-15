<?php
/**
 * ConvertKit HTML Parser class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Provides functionality for parsing HTML content and extracting relevant information.
 *
 * @since   3.0.0
 */
class ConvertKit_HTML_Parser {

	/**
	 * DOMDocument.
	 *
	 * @var DOMDocument
	 */
	public $html;

	/**
	 * XPath.
	 *
	 * @var DOMXPath
	 */
	public $xpath;

	/**
	 * Loads HTML content into a DOMDocument and returns the DOMDocument and XPath.
	 *
	 * @since   3.0.0
	 *
	 * @param   string   $content            HTML content to load.
	 * @param   bool|int $flags              DOMDocument flags.
	 * @param   bool     $is_html_document   The content is a full HTML document, comprising of
	 *                                       <html>, <head> and <body> tags, instead of the HTML
	 *                                       that belongs within a <body> tag.
	 */
	public function __construct( $content, $flags = false, $is_html_document = false ) {

		if ( $is_html_document ) {
			// The content is a full HTML document. Add the UTF-8 Content-Type meta tag to its
			// <head>, instead of wrapping the document in another document, which would result
			// in the document's <head> elements being output within its <body>.
			$content = $this->add_content_type_meta_tag( $content );
		} else {
			// Wrap content in <html>, <head> and <body> tags with an UTF-8 Content-Type meta tag.
			// Forcibly tell DOMDocument that this HTML uses the UTF-8 charset.
			// <meta charset="utf-8"> isn't enough, as DOMDocument still interprets the HTML as ISO-8859, which breaks character encoding
			// Use of mb_convert_encoding() with HTML-ENTITIES is deprecated in PHP 8.2, so we have to use this method.
			// If we don't, special characters render incorrectly.
			$content = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>';
		}

		// Load the HTML into a DOMDocument.
		libxml_use_internal_errors( true );
		$this->html = new DOMDocument();
		if ( $flags ) {
			$this->html->loadHTML( $content, $flags );
		} else {
			$this->html->loadHTML( $content );
		}

		// Load DOMDocument into XPath.
		$this->xpath = new DOMXPath( $this->html );

	}

	/**
	 * Adds a UTF-8 Content-Type meta tag to the given HTML document's <head>.
	 *
	 * DOMDocument doesn't honor a document's <meta charset="utf-8"> tag, interpreting the
	 * HTML as ISO-8859 and therefore rendering special characters incorrectly. Use of
	 * mb_convert_encoding() with HTML-ENTITIES is deprecated in PHP 8.2, so we have to
	 * use this method.
	 *
	 * @since   3.4.1
	 *
	 * @param   string $content   HTML document.
	 * @return  string            HTML document, with a UTF-8 Content-Type meta tag.
	 */
	private function add_content_type_meta_tag( $content ) {

		$meta_tag = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

		// Add the meta tag immediately after the opening <head> tag.
		$html = preg_replace( '/<head(\s[^>]*)?>/i', '$0' . $meta_tag, $content, 1, $count );
		if ( $count ) {
			return (string) $html;
		}

		// No <head> tag exists; add one immediately after the opening <html> tag.
		$html = preg_replace( '/<html(\s[^>]*)?>/i', '$0<head>' . $meta_tag . '</head>', $content, 1, $count );
		if ( $count ) {
			return (string) $html;
		}

		// Neither a <head> or <html> tag exists; prepend the meta tag in its own <head> tag.
		return '<head>' . $meta_tag . '</head>' . $content;

	}

	/**
	 * Returns the HTML within the DOMDocument's <body> tag as a string.
	 *
	 * @since   3.0.0
	 *
	 * @return  string
	 */
	public function get_body_html() {

		$body = $this->html->getElementsByTagName( 'body' )->item( 0 );

		$html = '';
		foreach ( $body->childNodes as $child ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$html .= $this->html->saveHTML( $child );
		}

		return $html;

	}

}
