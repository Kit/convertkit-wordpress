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
	 * @param   string   $content HTML content to load.
	 * @param   bool|int $flags   DOMDocument flags.
	 */
	public function __construct( $content, $flags = false ) {

		// Wrap content in <html>, <head> and <body> tags with an UTF-8 Content-Type meta tag.
		// Forcibly tell DOMDocument that this HTML uses the UTF-8 charset.
		// <meta charset="utf-8"> isn't enough, as DOMDocument still interprets the HTML as ISO-8859, which breaks character encoding
		// Use of mb_convert_encoding() with HTML-ENTITIES is deprecated in PHP 8.2, so we have to use this method.
		// If we don't, special characters render incorrectly.
		$content = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>';

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
