<?php
/**
 * Tests for the SiennaWidget front-end class.
 */

namespace Bauhaus_Acessibilidade\Tests\Frontend;

use Bauhaus_Acessibilidade\Frontend\SiennaWidget;
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
		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.com/wp-content/plugins/bauhaus-acessibilidade-br/assets/js/' );
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
	 * When enable_sienna is true, the widget must register and enqueue assets.
	 */
	public function test_enabled_widget_enqueues_assets(): void {
		$enqueued = array();

		Functions\expect( 'wp_register_script' )
			->once()
			->with( 'sienna-accessibility', false, \Mockery::any(), '2.2.333', true )
			->andReturn( true );

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with( 'sienna-accessibility' )
			->andReturnUsing(
				function ( string $handle ) use ( &$enqueued ) {
					$enqueued[ $handle ] = true;
				}
			);

		Functions\expect( 'wp_enqueue_style' )
			->once()
			->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )
			->zeroOrMoreTimes()
			->andReturn( true );

		// Let file_exists/file_get_contents work — the UMD bundle is present.

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

		Functions\expect( 'wp_register_script' )
			->once()
			->andReturn( true );

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->andReturn( true );

		Functions\expect( 'wp_enqueue_style' )
			->once()
			->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )
			->zeroOrMoreTimes()
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
		$this->assertStringContainsString( 'data-position', $before_script );
		$this->assertStringContainsString( 'center-left', $before_script );
	}

	/**
	 * When position is 'right', the config must say 'center-right'.
	 */
	public function test_init_script_uses_center_right(): void {
		$before_script = null;

		Functions\expect( 'wp_register_script' )->once()->andReturn( true );
		Functions\expect( 'wp_enqueue_script' )->once()->andReturn( true );
		Functions\expect( 'wp_enqueue_style' )->once()->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )
			->zeroOrMoreTimes()
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

		Functions\expect( 'wp_register_script' )->once()->andReturn( true );
		Functions\expect( 'wp_enqueue_script' )->once()->andReturn( true );
		Functions\expect( 'wp_enqueue_style' )->once()->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )
			->zeroOrMoreTimes()
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
