<?php
/**
 * Tests for the main plugin bootstrap.
 *
 * @package Bauhaus_Accessibility
 */

namespace Bauhaus_Accessibility\Tests\Core;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Proves that the WordPress runtime owns language-pack loading for this plugin.
 */
class PluginBootstrapTest extends TestCase {

	/**
	 * Starts a fresh WordPress function mock environment for each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\tearDown();
		Monkey\setUp();
	}

	/**
	 * Stops the WordPress function mock environment after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Bootstrap delegates translation loading to WordPress.org language packs.
	 *
	 * @return void
	 */
	public function test_bootstrap_does_not_register_a_manual_textdomain_loader(): void {
		Functions\expect( 'add_action' )
			->once()
			->with( 'plugins_loaded', 'bauhaus_accessibility_init' )
			->andReturn( true );

		Functions\expect( 'load_plugin_textdomain' )->never();

		require dirname( __DIR__, 2 ) . '/bauhaus-accessibility.php';

		$this->assertFalse( function_exists( 'bauhaus_accessibility_load_textdomain' ) );
	}

	/**
	 * Plugin metadata declares the domain WordPress.org uses for language packs.
	 *
	 * @return void
	 */
	public function test_plugin_header_declares_the_wordpress_org_translation_domain(): void {
		$plugin_file = dirname( __DIR__, 2 ) . '/bauhaus-accessibility.php';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads the repository-local plugin header.
		$plugin_contents = file_get_contents( $plugin_file );

		$this->assertNotFalse( $plugin_contents );
		$this->assertMatchesRegularExpression(
			'/^ \* Text Domain:\s+bauhaus-accessibility$/m',
			$plugin_contents
		);
	}

	/**
	 * Distribution metadata records the WordPress version validated for this release.
	 *
	 * @return void
	 */
	public function test_distribution_metadata_declares_wordpress_7_1_as_tested(): void {
		$plugin_file         = dirname( __DIR__, 2 ) . '/bauhaus-accessibility.php';
		$readme_file         = dirname( __DIR__, 2 ) . '/readme.txt';
		$project_readme_file = dirname( __DIR__, 2 ) . '/README.md';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads repository-local distribution metadata.
		$plugin_contents = file_get_contents( $plugin_file );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads repository-local distribution metadata.
		$readme_contents = file_get_contents( $readme_file );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- The test reads repository-local compatibility documentation.
		$project_readme_contents = file_get_contents( $project_readme_file );

		$this->assertNotFalse( $plugin_contents );
		$this->assertNotFalse( $readme_contents );
		$this->assertNotFalse( $project_readme_contents );
		$this->assertMatchesRegularExpression(
			'/^ \\* Tested up to:\\s+7\\.1$/m',
			$plugin_contents
		);
		$this->assertMatchesRegularExpression(
			'/^Tested up to: 7\\.1$/m',
			$readme_contents
		);
		$this->assertStringContainsString(
			'WordPress 6.0+ (tested through WordPress 7.1)',
			$project_readme_contents
		);
	}
}
