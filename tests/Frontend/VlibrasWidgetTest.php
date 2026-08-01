<?php
/**
 * Tests for the VlibrasWidget front-end class.
 */

namespace Bauhaus_Acessibilidade\Tests\Frontend;

use Bauhaus_Acessibilidade\Frontend\VlibrasWidget;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class VlibrasWidgetTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		\Brain\Monkey\tearDown();
		\Brain\Monkey\setUp();

		Functions\stubs(
			array(
				'esc_js',
				'esc_url',
				'plugin_dir_url',
			)
		);

		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.com/wp-content/plugins/bauhaus-acessibilidade-br/' );
	}

	protected function tearDown(): void {
		\Brain\Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Class must exist and be instantiable.
	 */
	public function test_class_exists_and_is_instantiable(): void {
		$widget = new VlibrasWidget();
		$this->assertInstanceOf( VlibrasWidget::class, $widget );
	}

	/**
	 * When enabled, the widget must enqueue the local VLibras script.
	 */
	public function test_enabled_widget_enqueues_script(): void {
		$enqueued = array();

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'vlibras-plugin',
				\Mockery::on( fn( $src ) => strpos( $src, 'vlibras-plugin.js' ) !== false ),
				array(),
				\Mockery::any(),
				true
			)
			->andReturnUsing(
				function ( string $handle ) use ( &$enqueued ) {
					$enqueued[ $handle ] = true;
				}
			);

		Functions\expect( 'wp_add_inline_script' )
			->once()
			->andReturn( true );

		$options = array(
			'enable_vlibras'  => true,
			'widget_position' => 'right',
		);
		$widget  = new VlibrasWidget();
		$widget->maybe_enqueue( $options );

		$this->assertArrayHasKey( 'vlibras-plugin', $enqueued );
	}

	/**
	 * When disabled, nothing must be enqueued.
	 */
	public function test_disabled_widget_enqueues_nothing(): void {
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_add_inline_script' )->never();

		$options = array( 'enable_vlibras' => false );
		$widget  = new VlibrasWidget();
		$widget->maybe_enqueue( $options );

		$this->assertTrue( true );
	}

	/**
	 * The init script must call VLibras.Widget with the gov.br rootPath.
	 */
	public function test_init_script_calls_vlibras_widget(): void {
		$inline_script = null;

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->andReturn( true );

		Functions\expect( 'wp_add_inline_script' )
			->once()
			->with(
				'vlibras-plugin',
				\Mockery::on(
					function ( string $script ) use ( &$inline_script ) {
						$inline_script = $script;
						return strpos( $script, 'VLibras.Widget' ) !== false
							&& strpos( $script, 'vlibras.gov.br' ) !== false;
					}
				)
			)
			->andReturn( true );

		$options = array(
			'enable_vlibras'  => true,
			'widget_position' => 'right',
		);
		$widget  = new VlibrasWidget();
		$widget->maybe_enqueue( $options );

		$this->assertNotNull( $inline_script );
	}

	/**
	 * The footer markup must include the VLibras container.
	 */
	public function test_footer_markup_contains_vlibras_container(): void {
		$widget = new VlibrasWidget();

		ob_start();
		$widget->render_container();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'vw', $output );
		$this->assertStringContainsString( 'vw-access-button', $output );
		$this->assertStringContainsString( 'vw-plugin-wrapper', $output );
	}

	/**
	 * The footer markup must include the 'enabled' class so VLibras is visible.
	 */
	public function test_footer_markup_has_enabled_class(): void {
		$widget = new VlibrasWidget();

		ob_start();
		$widget->render_container();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'class="enabled"', $output );
	}

	/**
	 * A body class must be added for the widget side.
	 */
	public function test_adds_body_class_filter(): void {
		$filter_callback = null;

		Functions\expect( 'wp_enqueue_script' )->once()->andReturn( true );
		Functions\expect( 'wp_add_inline_script' )->once()->andReturn( true );

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
			'enable_vlibras'  => true,
			'widget_position' => 'left',
		);
		$widget  = new VlibrasWidget();
		$widget->maybe_enqueue( $options );

		$this->assertIsCallable( $filter_callback );
		$result = $filter_callback( array() );
		$this->assertContains( 'bauhaus-widgets-left', $result );
	}
}
