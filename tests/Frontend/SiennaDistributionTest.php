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
	 * The opened Sienna menu must stack above both widget controls: the bundle
	 * ships it with z-index 500000 while this plugin pins the Sienna button at
	 * 2147483646 and VLibras renders its access control at 2147483639.
	 */
	public function test_plugin_styles_keep_the_opened_menu_above_the_widget_buttons(): void {
		$plugin_root = dirname( __DIR__, 2 );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads a local plugin stylesheet.
		$css = file_get_contents( $plugin_root . '/assets/css/bauhaus-accessibility.css' );

		$this->assertMatchesRegularExpression(
			'/\\.asw-container\\s+\\.asw-menu\\s*\\{[^}]*z-index:\\s*2147483647\\s*!important;/s',
			(string) $css,
			'The opened menu must sit above the Sienna and VLibras buttons.'
		);
	}

	/**
	 * Plugin styles must not override Sienna's own inline offsets: since the
	 * VLibras fix, the bundle positions its control itself via
	 * data-asw-position/data-asw-offset, and forcing left/right in CSS fights it.
	 *
	 * @return void
	 */
	public function test_plugin_styles_do_not_override_inline_sienna_offsets(): void {
		$plugin_root = dirname( __DIR__, 2 );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads a local plugin stylesheet.
		$css = file_get_contents( $plugin_root . '/assets/css/bauhaus-accessibility.css' );

		$this->assertNotFalse( $css );
		$this->assertDoesNotMatchRegularExpression(
			'/\.asw-menu-btn\s*\{[^}]*?(left|right):\s*[0-9]+px\s*!important;/s',
			$css,
			'The stylesheet must not force a side offset on the Sienna button.'
		);

		// The VLibras shell overrides stay: they align the two controls.
		$this->assertStringContainsString( '.bauhaus-widgets-left', $css );
		$this->assertStringContainsString( '.bauhaus-widgets-right', $css );
		$this->assertStringContainsString( 'div[vw]', $css );
	}
}
