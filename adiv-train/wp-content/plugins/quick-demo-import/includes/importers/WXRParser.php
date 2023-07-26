<?php
/**
 * WordPress eXtended RSS file parser implementations
 *
 * @package QuickDemoImport
 */

namespace QuickDemoImport\Importers;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress Importer class for managing parsing of WXR files.
 */
class WXRParser {

	public function parse( $file ) {
		// Attempt to use proper XML parsers first
		if ( extension_loaded( 'simplexml' ) ) {
			$parser = new WXRParserSimpleXML();
			$result = $parser->parse( $file );

			// If SimpleXML succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'SimpleXML_parse_error' !== $result->get_error_code() ) {
				return $result;
			}
		} elseif ( extension_loaded( 'xml' ) ) {
			$parser = new WXRParserXML();
			$result = $parser->parse( $file );

			// If XMLParser succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'XML_parse_error' !== $result->get_error_code() ) {
				return $result;
			}
		}

		// We have a malformed XML file, so display the error and fallthrough to regex
		if ( isset( $result ) && defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
			echo '<pre>';
			if ( 'SimpleXML_parse_error' === $result->get_error_code() ) {
				foreach ( $result->get_error_data() as $error ) {
					echo esc_html( $error->line ) . ':' . esc_html( $error->column ) . ' ' . esc_html( $error->message ) . "\n";
				}
			} elseif ( 'XML_parse_error' === $result->get_error_code() ) {
				$error = $result->get_error_data();
				echo esc_html( $error[0] ) . ':' . esc_html( $error[1] ) . ' ' . esc_html( $error[2] );
			}
			echo '</pre>';
			echo '<p><strong>' . esc_html__( 'There was an error when reading this WXR file', 'quick-demo-import' ) . '</strong><br />';
			echo esc_html__( 'Details are shown above. The importer will now try again with a different parser...', 'quick-demo-import' ) . '</p>';
		}

		// use regular expressions if nothing else available or this is bad XML
		$parser = new WXRParserRegex();

		return $parser->parse( $file );
	}
}