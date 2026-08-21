<?php
/**
 * Tests for WordPress translation package boundaries.
 *
 * @package Bauhaus_Accessibility
 */

namespace Bauhaus_Accessibility\Tests\Core;

use PHPUnit\Framework\TestCase;

/**
 * Ensures language packs, rather than plugin files, deliver WordPress translations.
 */
class TranslationPackagingTest extends TestCase {

	/**
	 * WordPress translation artifacts stay out of the plugin package.
	 *
	 * The Brazilian Portuguese source remains in the repository documentation so
	 * it can be imported through the official translation platform.
	 *
	 * @return void
	 */
	public function test_plugin_does_not_bundle_translations_and_keeps_pt_br_source_in_docs(): void {
		$plugin_root        = dirname( __DIR__, 2 );
		$translation_source = $plugin_root . '/docs/translations/bauhaus-accessibility-pt_BR.po';

		$this->assertDirectoryDoesNotExist( $plugin_root . '/languages' );
		$this->assertFileExists( $translation_source );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads a repository-local PO file; no HTTP request is appropriate.
		$translation_contents = file_get_contents( $translation_source );
		$this->assertNotFalse( $translation_contents );
		$this->assertStringContainsString( "\"Language: pt_BR\\n\"", $translation_contents );
		$this->assertStringContainsString( '"PO-Revision-Date: ', $translation_contents );
		$this->assertStringContainsString( '"Last-Translator: ', $translation_contents );
		$this->assertStringContainsString( "\"Language-Team: Portuguese (Brazil)\\n\"", $translation_contents );
		$this->assertStringContainsString(
			"msgid \"Accessibility\"\nmsgstr \"Acessibilidade\"",
			$translation_contents
		);
	}
}
