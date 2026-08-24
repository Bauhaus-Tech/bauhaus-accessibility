<?php
/**
 * Tests for the SiennaWidget front-end class.
 */

namespace Bauhaus_Accessibility\Tests\Frontend;

use Bauhaus_Accessibility\Frontend\SiennaWidget;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class SiennaWidgetTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\tearDown();
		\Brain\Monkey\setUp();

		// Common stubs.
		Functions\stubs(
			array(
				'get_locale',
				'esc_js',
				'esc_url',
				'esc_html',
				'wp_json_encode',
			)
		);

		Functions\when( 'get_locale' )->justReturn( 'pt_BR' );
		// The plugin version is read from the main file's header; stub it so
		// every asset cache-buster can be asserted against one known value.
		Functions\when( 'get_file_data' )->justReturn( array( 'Version' => '9.9.9' ) );
		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.com/wp-content/plugins/bauhaus-accessibility/assets/js/' );
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Class must exist and be instantiable.
	 */
	public function test_class_exists_and_is_instantiable(): void {
		$widget = new SiennaWidget();
		$this->assertInstanceOf( SiennaWidget::class, $widget );
	}

	/**
	 * When enabled, the widget must enqueue the packaged local fork bundle.
	 */
	public function test_enabled_widget_enqueues_the_local_fork_bundle(): void {
		$enqueued = array();

		Functions\expect( 'wp_register_script' )->never();

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'sienna-accessibility',
				'https://example.com/wp-content/plugins/bauhaus-accessibility/assets/js/sienna-accessibility.umd.js',
				array(),
				'9.9.9',
				true
			)
			->andReturnUsing(
				function ( string $handle ) use ( &$enqueued ) {
					$enqueued[ $handle ] = true;
				}
			);

		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with(
				'bauhaus-accessibility',
				'https://example.com/wp-content/plugins/bauhaus-accessibility/assets/js/../css/bauhaus-accessibility.css',
				array(),
				'9.9.9'
			)
			->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )->once()->andReturn( true );

		$options = array(
			'enable_sienna'   => true,
			'widget_position' => 'right',
		);
		$widget  = new SiennaWidget();
		$widget->maybe_enqueue( $options );

		$this->assertArrayHasKey( 'sienna-accessibility', $enqueued );
	}

	/**
	 * When enable_sienna is false, no assets must be enqueued.
	 */
	public function test_disabled_widget_enqueues_nothing(): void {
		Functions\expect( 'wp_register_script' )->never();
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_enqueue_style' )->never();

		$options = array( 'enable_sienna' => false );
		$widget  = new SiennaWidget();
		$widget->maybe_enqueue( $options );

		$this->assertTrue( true );
	}

	/**
	 * The widget must inject the config element before the bundle.
	 */
	public function test_widget_injects_config_before_script(): void {
		$before_script = null;

		Functions\expect( 'wp_register_script' )->never();

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->andReturn( true );

		Functions\expect( 'wp_enqueue_style' )
			->once()
			->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )
			->once()
			->andReturnUsing(
				function ( string $handle, string $script, string $position = 'after' ) use ( &$before_script ) {
					if ( 'before' === $position ) {
						$before_script = $script;
					}
					return true;
				}
			);

		$options = array(
			'enable_sienna'   => true,
			'widget_position' => 'left',
		);
		$widget  = new SiennaWidget();
		$widget->maybe_enqueue( $options );

		$this->assertNotNull( $before_script, 'No before-script was injected' );
		$this->assertStringContainsString( 'data-asw-position', $before_script );
		$this->assertStringContainsString( 'data-asw-lang', $before_script );
		$this->assertStringContainsString( 'data-asw-offset', $before_script );
		$this->assertStringContainsString( 'center-left', $before_script );
	}

	/**
	 * When position is 'right', the config must say 'center-right'.
	 */
	public function test_init_script_uses_center_right(): void {
		$before_script = null;

		Functions\expect( 'wp_register_script' )->never();
		Functions\expect( 'wp_enqueue_script' )->once()->andReturn( true );
		Functions\expect( 'wp_enqueue_style' )->once()->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )
			->once()
			->andReturnUsing(
				function ( string $handle, string $script, string $position = 'after' ) use ( &$before_script ) {
					if ( 'before' === $position ) {
						$before_script = $script;
					}
					return true;
				}
			);

		$options = array(
			'enable_sienna'   => true,
			'widget_position' => 'right',
		);
		$widget  = new SiennaWidget();
		$widget->maybe_enqueue( $options );

		$this->assertNotNull( $before_script );
		$this->assertStringContainsString( 'center-right', $before_script );
	}

	/**
	 * A body class filter must be registered for the chosen side.
	 */
	public function test_registers_body_class_filter(): void {
		$filter_callback = null;

		Functions\expect( 'wp_register_script' )->never();
		Functions\expect( 'wp_enqueue_script' )->once()->andReturn( true );
		Functions\expect( 'wp_enqueue_style' )->once()->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )
			->once()
			->andReturn( true );

		Functions\expect( 'add_filter' )
			->zeroOrMoreTimes()
			->andReturnUsing(
				function ( string $hook, $callback ) use ( &$filter_callback ): bool {
					if ( 'body_class' === $hook ) {
						$filter_callback = $callback;
					}
					return true;
				}
			);

		$options = array(
			'enable_sienna'   => true,
			'widget_position' => 'left',
		);
		$widget  = new SiennaWidget();
		$widget->maybe_enqueue( $options );

		$this->assertIsCallable( $filter_callback );
		$result = $filter_callback( array( 'existing-class' ) );
		$this->assertContains( 'bauhaus-widgets-left', $result );
	}
}
