<?php
/**
 * Tests for the Sienna runtime files distributed with the plugin.
 */

namespace Bauhaus_Accessibility\Tests\Frontend;

use PHPUnit\Framework\TestCase;

class SiennaDistributionTest extends TestCase {

	/**
	 * The packaged fork must not retain the CDN paths the reviewer reported.
	 */
	public function test_sienna_distribution_has_no_jsdelivr_asset_urls(): void {
		$plugin_root = dirname( __DIR__, 2 );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads a local plugin source file.
		$php = file_get_contents( $plugin_root . '/includes/Frontend/SiennaWidget.php' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads the local packaged bundle.
		$bundle = file_get_contents( $plugin_root . '/assets/js/sienna-accessibility.umd.js' );

		$this->assertStringNotContainsString( 'cdn.jsdelivr.net/npm/sienna-accessibility', $php );
		$this->assertStringNotContainsString( 'cdn.jsdelivr.net/npm/sienna-accessibility', $bundle );
		$this->assertStringContainsString( 'fonts/OpenDyslexic3-Regular.woff', $bundle );
		$this->assertStringContainsString( 'fonts/OpenDyslexic3-Regular.ttf', $bundle );
	}

	/**
	 * The local bundle resolves its dyslexia font relative to the script file.
	 */
	public function test_sienna_distribution_packages_its_font_files_next_to_the_bundle(): void {
		$plugin_root = dirname( __DIR__, 2 );

		$this->assertFileExists( $plugin_root . '/assets/js/fonts/OpenDyslexic3-Regular.woff' );
		$this->assertFileExists( $plugin_root . '/assets/js/fonts/OpenDyslexic3-Regular.ttf' );
	}

	/**
	 * The local bundle keeps its own locale modules instead of requesting them.
	 */
	public function test_sienna_bundle_embeds_english_and_portuguese_locales_without_fetching_them(): void {
		$plugin_root = dirname( __DIR__, 2 );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads the local packaged bundle.
		$bundle = file_get_contents( $plugin_root . '/assets/js/sienna-accessibility.umd.js' );

		$this->assertStringContainsString( '../locales/en.json', $bundle );
		$this->assertStringContainsString( '../locales/pt.json', $bundle );
		$this->assertStringNotContainsString( 'fetch(', $bundle );
	}

	/**
	 * The local bundle must remain a compact control above the VLibras button.
	 */
	public function test_plugin_styles_keep_the_sienna_control_above_vlibras(): void {
		$plugin_root = dirname( __DIR__, 2 );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads a local plugin stylesheet.
		$css = file_get_contents( $plugin_root . '/assets/css/bauhaus-accessibility.css' );

		$this->assertStringContainsString( 'z-index: 2147483646 !important', $css );
		$this->assertStringContainsString( 'width: 40px !important', $css );
		$this->assertStringContainsString( 'height: 40px !important', $css );
	}

	/**
	 * Plugin positioning overrides Sienna's inline 10px offsets on either side.
	 *
	 * @return void
	 */
	public function test_plugin_styles_override_inline_sienna_offsets_to_align_with_vlibras(): void {
		$plugin_root = dirname( __DIR__, 2 );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads a local plugin stylesheet.
		$css = file_get_contents( $plugin_root . '/assets/css/bauhaus-accessibility.css' );

		$this->assertNotFalse( $css );
		$left_position_styles  = strstr( $css, '.bauhaus-widgets-left' );
		$right_position_styles = strstr( $css, '.bauhaus-widgets-right' );

		$this->assertNotFalse( $left_position_styles );
		$this->assertNotFalse( $right_position_styles );
		$this->assertStringContainsString( 'left: 20px !important;', $left_position_styles );
		$this->assertStringContainsString( 'right: 20px !important;', $right_position_styles );
	}
}
