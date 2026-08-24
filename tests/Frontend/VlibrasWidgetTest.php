<?php
/**
 * Tests for the VlibrasWidget front-end class.
 */

namespace Bauhaus_Accessibility\Tests\Frontend;

use Bauhaus_Accessibility\Frontend\VlibrasWidget;
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

		Functions\when( 'plugin_dir_url' )->justReturn( 'https://example.com/wp-content/plugins/bauhaus-accessibility/' );
		Functions\when( 'get_file_data' )->justReturn( array( 'Version' => '9.9.9' ) );
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
	 * When enabled, the widget must enqueue the official government-hosted script.
	 */
	public function test_enabled_widget_enqueues_government_hosted_script(): void {
		$enqueued = array();

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'vlibras-plugin',
				'https://vlibras.gov.br/app/vlibras-plugin.js',
				array(),
				'9.9.9',
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
	 * The plugin must not distribute a local VLibras bundle.
	 */
	public function test_plugin_does_not_include_a_local_vlibras_script(): void {
		$this->assertFileDoesNotExist(
			dirname( __DIR__, 2 ) . '/assets/js/vlibras-plugin.js'
		);
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
	 * The init script must call the v7.5.0 positional Widget(path, configUrl,
	 * avatar, position) API. A config object passed as first argument becomes
	 * the asset path and yields "[object Object]/assets/..." requests (404s).
	 * The widget compares the position against lowercase 'l', so the side must
	 * be emitted in lower case.
	 */
	public function test_init_script_preserves_configured_position_with_government_root_path(): void {
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
						return 'window.VLibras.Widget("https://vlibras.gov.br/app",undefined,undefined,"l");' === $script;
					}
				)
			)
			->andReturn( true );

		$options = array(
			'enable_vlibras'  => true,
			'widget_position' => 'left',
		);
		$widget  = new VlibrasWidget();
		$widget->maybe_enqueue( $options );

		$this->assertNotNull( $inline_script );
	}

	/**
	 * The init script must keep the right-side position when it is configured.
	 */
	public function test_init_script_preserves_right_position_with_government_root_path(): void {
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
						return 'window.VLibras.Widget("https://vlibras.gov.br/app",undefined,undefined,"r");' === $script;
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
