<?php
/**
 * Tests for the Plugin bootstrap class.
 */

namespace Bauhaus_Acessibilidade\Tests\Core;

use Bauhaus_Acessibilidade\Core\Plugin;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\tearDown();
		\Brain\Monkey\setUp();
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * The Plugin class must exist and be instantiable.
	 */
	public function test_plugin_class_exists_and_can_be_instantiated(): void {
		$plugin = new Plugin();
		$this->assertInstanceOf( Plugin::class, $plugin );
	}

	/**
	 * add_admin_menu() must call add_submenu_page under Settings.
	 */
	public function test_add_admin_menu_calls_add_submenu_page(): void {
		// Stub translation + escaping functions needed by render_admin_page.
		Functions\stubs(
			array(
				'__',
				'esc_html',
				'get_admin_page_title',
			)
		);

		$called = false;
		$args   = array();

		Functions\expect( 'add_submenu_page' )
			->once()
			->andReturnUsing(
				function ( ...$received ) use ( &$called, &$args ) {
					$called = true;
					$args   = $received;
					return 'hook-suffix';
				}
			);

		$plugin = new Plugin();
		$plugin->add_admin_menu();

		$this->assertTrue( $called, 'add_submenu_page was not called' );
		$this->assertSame( 'options-general.php', $args[0] );
		$this->assertSame( 'manage_options', $args[3] );
		$this->assertSame( 'bauhaus-acessibilidade-br', $args[4] );
	}

	/**
	 * register_settings() must call register_setting with the correct option group.
	 */
	public function test_register_settings_calls_register_setting(): void {
		$called = false;

		Functions\expect( 'register_setting' )
			->once()
			->andReturnUsing(
				function ( ...$received ) use ( &$called ) {
					$called = true;
					$this->assertSame( 'bauhaus_acessibilidade_settings_group', $received[0] );
					$this->assertSame( 'bauhaus_acessibilidade_settings', $received[1] );
					return null;
				}
			);

		$plugin = new Plugin();
		$plugin->register_settings();

		$this->assertTrue( $called, 'register_setting was not called' );
	}

	/**
	 * run() must hook add_admin_menu to 'admin_menu'.
	 */
	public function test_run_hooks_admin_menu(): void {
		$actions = array();

		Functions\expect( 'add_action' )
			->zeroOrMoreTimes()
			->andReturnUsing(
				function ( string $hook, $callback ) use ( &$actions ): bool {
					$actions[ $hook ][] = $callback;
					return true;
				}
			);

		$plugin = new Plugin();
		$plugin->run();

		$this->assertArrayHasKey( 'admin_menu', $actions );
		$this->assertContains( array( $plugin, 'add_admin_menu' ), $actions['admin_menu'] );
	}

	/**
	 * run() must hook register_settings to 'admin_init'.
	 */
	public function test_run_hooks_admin_init(): void {
		$actions = array();

		Functions\expect( 'add_action' )
			->zeroOrMoreTimes()
			->andReturnUsing(
				function ( string $hook, $callback ) use ( &$actions ): bool {
					$actions[ $hook ][] = $callback;
					return true;
				}
			);

		$plugin = new Plugin();
		$plugin->run();

		$this->assertArrayHasKey( 'admin_init', $actions );
		$this->assertContains( array( $plugin, 'register_settings' ), $actions['admin_init'] );
	}

	/**
	 * sanitize_settings must return correct defaults for empty input.
	 */
	public function test_sanitize_settings_returns_defaults_for_empty_input(): void {
		Functions\stubs( array( 'sanitize_text_field' ) );

		$plugin = new Plugin();
		$result = $plugin->sanitize_settings( array() );

		$this->assertFalse( $result['enable_vlibras'] );
		$this->assertFalse( $result['enable_sienna'] );
		$this->assertSame( 'right', $result['widget_position'] );
		$this->assertSame( 'libras', $result['sign_language'] );
	}

	/**
	 * sanitize_settings must coerce checkbox values to booleans.
	 */
	public function test_sanitize_settings_coerces_checkboxes_to_boolean(): void {
		Functions\stubs( array( 'sanitize_text_field' ) );

		$plugin = new Plugin();

		$result = $plugin->sanitize_settings(
			array(
				'enable_vlibras' => '1',
				'enable_sienna'  => 'on',
			)
		);

		$this->assertTrue( $result['enable_vlibras'] );
		$this->assertTrue( $result['enable_sienna'] );
	}

	/**
	 * sanitize_settings must reject invalid widget positions.
	 */
	public function test_sanitize_settings_rejects_invalid_position(): void {
		Functions\stubs( array( 'sanitize_text_field' ) );

		$plugin = new Plugin();

		$result = $plugin->sanitize_settings(
			array(
				'widget_position' => 'center',
			)
		);

		$this->assertSame( 'right', $result['widget_position'] );
	}

	/**
	 * sanitize_settings must accept 'left' as widget position.
	 */
	public function test_sanitize_settings_accepts_left_position(): void {
		Functions\stubs( array( 'sanitize_text_field' ) );

		$plugin = new Plugin();

		$result = $plugin->sanitize_settings(
			array(
				'widget_position' => 'left',
			)
		);

		$this->assertSame( 'left', $result['widget_position'] );
	}

	/**
	 * sanitize_settings must run sign_language through sanitize_text_field.
	 */
	public function test_sanitize_settings_sanitizes_sign_language(): void {
		// Provide a real sanitize_text_field implementation for the test.
		Functions\expect( 'sanitize_text_field' )
			->once()
			->andReturnUsing(
				function ( string $value ): string {
					// Simulate WordPress's sanitize_text_field behavior.
					$value = trim( $value );
					$value = wp_strip_all_tags( $value );
					return $value;
				}
			);

		Functions\stubs( array( 'wp_strip_all_tags' ) );

		$plugin = new Plugin();

		$result = $plugin->sanitize_settings(
			array(
				'sign_language' => '  libras  ',
			)
		);

		$this->assertSame( 'libras', $result['sign_language'] );
	}

	/**
	 * The plugin version constant must be defined.
	 */
	public function test_plugin_has_version_constant(): void {
		$this->assertNotEmpty( Plugin::VERSION );
	}

	/**
	 * The option name constant must be defined.
	 */
	public function test_plugin_has_option_name_constant(): void {
		$this->assertNotEmpty( Plugin::OPTION_NAME );
		$this->assertSame( 'bauhaus_acessibilidade_settings', Plugin::OPTION_NAME );
	}
}
