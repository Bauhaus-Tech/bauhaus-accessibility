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
			->with( 'plugins_loaded', \Mockery::type( 'callable' ) )
			->andReturn( true );

		Functions\expect( 'load_plugin_textdomain' )->never();

		require dirname( __DIR__, 2 ) . '/bauhaus-accessibility.php';

		$this->assertFalse( function_exists( 'bauhaus_accessibility_load_textdomain' ) );
	}
}
