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

		Functions\stubs( array( 'add_settings_section', 'add_settings_field', '__' ) );

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

		Functions\stubs( array( 'load_plugin_textdomain' ) );

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

		Functions\stubs( array( 'load_plugin_textdomain' ) );

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
	 * run() must call load_plugin_textdomain for i18n support.
	 */
	public function test_run_loads_textdomain(): void {
		$called_with_domain = null;

		Functions\expect( 'load_plugin_textdomain' )
			->once()
			->andReturnUsing(
				function ( string $domain ) use ( &$called_with_domain ): bool {
					$called_with_domain = $domain;
					return true;
				}
			);

		// Also stub add_action since run() will register hooks too.
		Functions\expect( 'add_action' )
			->zeroOrMoreTimes()
			->andReturn( true );

		$plugin = new Plugin();
		$plugin->run();

		$this->assertSame(
			'bauhaus-acessibilidade-br',
			$called_with_domain,
			'load_plugin_textdomain was not called with the correct domain'
		);
	}

	/**
	 * sanitize_settings must return correct defaults for empty input.
	 */
	public function test_sanitize_settings_returns_defaults_for_empty_input(): void {
		Functions\expect( 'sanitize_text_field' )
			->zeroOrMoreTimes()
			->andReturnUsing( fn( string $v ) => $v );

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
		Functions\expect( 'sanitize_text_field' )
			->zeroOrMoreTimes()
			->andReturnUsing( fn( string $v ) => $v );

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
		Functions\expect( 'sanitize_text_field' )
			->zeroOrMoreTimes()
			->andReturnUsing( fn( string $v ) => $v );

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
		Functions\expect( 'sanitize_text_field' )
			->zeroOrMoreTimes()
			->andReturnUsing( fn( string $v ) => $v );

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
	 * register_settings must register a settings section.
	 */
	public function test_register_settings_adds_section(): void {
		$section_registered = false;

		Functions\when( '__' )->returnArg();

		Functions\expect( 'register_setting' )
			->once()
			->andReturn( true );

		Functions\expect( 'add_settings_field' )
			->zeroOrMoreTimes()
			->andReturn( true );

		Functions\expect( 'add_settings_section' )
			->once()
			->with(
				'bauhaus_acessibilidade_main',
				\Mockery::any(),
				\Mockery::any(),
				'bauhaus_acessibilidade_settings_group'
			)
			->andReturnUsing(
				function () use ( &$section_registered ) {
					$section_registered = true;
				}
			);

		$plugin = new Plugin();
		$plugin->register_settings();

		$this->assertTrue( $section_registered );
	}

	/**
	 * register_settings must register all four setting fields.
	 */
	public function test_register_settings_adds_fields(): void {
		$fields = array();

		Functions\when( '__' )->returnArg();

		Functions\expect( 'register_setting' )->once()->andReturn( true );
		Functions\expect( 'add_settings_section' )->once()->andReturn( true );

		Functions\expect( 'add_settings_field' )
			->times( 4 )
			->andReturnUsing(
				function ( string $id ) use ( &$fields ) {
					$fields[] = $id;
				}
			);

		$plugin = new Plugin();
		$plugin->register_settings();

		$this::assertContains( 'enable_vlibras', $fields );
		$this::assertContains( 'enable_sienna', $fields );
		$this::assertContains( 'widget_position', $fields );
		$this::assertContains( 'sign_language', $fields );
	}

	/**
	 * The enable_vlibras field callback must render a checkbox.
	 */
	public function test_enable_vlibras_field_renders_checkbox(): void {
		Functions\stubs(
			array(
				'checked',
				'esc_attr',
				'esc_html_e',
				'esc_html',
				'__',
				'selected',
			)
		);
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( '__' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();

		$plugin = new Plugin();

		ob_start();
		$plugin->render_enable_vlibras_field();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'type="checkbox"', $output );
		$this->assertStringContainsString( 'enable_vlibras', $output );
	}

	/**
	 * The widget_position field callback must render radio buttons.
	 */
	public function test_widget_position_field_renders_radio_buttons(): void {
		Functions\stubs(
			array(
				'checked',
				'esc_attr',
				'esc_html_e',
				'esc_html',
				'__',
			)
		);
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( '__' )->returnArg();

		$plugin = new Plugin();

		ob_start();
		$plugin->render_widget_position_field();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'type="radio"', $output );
		$this->assertStringContainsString( 'value="right"', $output );
		$this->assertStringContainsString( 'value="left"', $output );
	}

	/**
	 * The sign_language field callback must render a dropdown with Libras.
	 */
	public function test_sign_language_field_renders_select(): void {
		Functions\stubs(
			array(
				'checked',
				'esc_attr',
				'esc_html',
				'__',
				'selected',
				'apply_filters',
			)
		);
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( '__' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'apply_filters' )->justReturn( array( 'libras' => 'Libras' ) );

		$plugin = new Plugin();

		ob_start();
		$plugin->render_sign_language_field();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<select', $output );
		$this->assertStringContainsString( 'libras', $output );
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
