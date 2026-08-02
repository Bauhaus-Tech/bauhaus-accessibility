<?php
/**
 * VLibras Widget front-end integration.
 *
 * Enqueues the local VLibras plugin script, injects the container markup
 * into wp_footer, and initializes the VLibras interpreter.
 *
 * @package Bauhaus_Acessibilidade\Frontend
 */

namespace Bauhaus_Acessibilidade\Frontend;

/**
 * Handles front-end output for the VLibras Sign Language widget.
 */
class VlibrasWidget {

	/**
	 * Default rootPath for VLibras assets (chunks, avatars).
	 *
	 * @var string
	 */
	const DEFAULT_ROOT_PATH = 'https://vlibras.gov.br/app';

	/**
	 * Enqueue scripts and inject footer markup if the widget is enabled.
	 *
	 * Called on 'wp_enqueue_scripts'. Also hooks 'wp_footer' for markup.
	 *
	 * @param array $options Plugin settings array.
	 * @return void
	 */
	public function maybe_enqueue( array $options ): void {
		if ( empty( $options['enable_vlibras'] ) ) {
			return;
		}

		$position = $options['widget_position'] ?? 'right';

		// Enqueue the local VLibras plugin script (footer, before the init).
		wp_enqueue_script(
			'vlibras-plugin',
			plugin_dir_url( __FILE__ ) . '../../assets/js/vlibras-plugin.js',
			array(),
			'1.0.0',
			true
		);

		// Map our left/right setting to VLibras position codes.
		// VLibras uses: L, R, T, B, TL, TR, BL, BR.
		$vlibras_position = 'left' === $position ? 'L' : 'R';

		// Initialize VLibras with position-aware config.
		// Passing position ensures the panel opens in the correct direction.
		wp_add_inline_script(
			'vlibras-plugin',
			sprintf(
				'new window.VLibras.Widget({rootPath:"%s",position:"%s"});',
				esc_js( self::DEFAULT_ROOT_PATH ),
				esc_js( $vlibras_position )
			)
		);

		// Inject the VLibras container markup into wp_footer.
		add_action( 'wp_footer', array( $this, 'render_container' ) );

		// Add a body class for CSS positioning (shared with Sienna).
		add_filter(
			'body_class',
			function ( array $classes ) use ( $position ): array {
				if ( ! in_array( 'bauhaus-widgets-' . $position, $classes, true ) ) {
					$classes[] = 'bauhaus-widgets-' . $position;
				}
				return $classes;
			}
		);
	}

	/**
	 * Output the VLibras container markup.
	 *
	 * Hooked to 'wp_footer'. This is the DOM structure that VLibras
	 * expects to render the sign language interpreter button and panel.
	 *
	 * @return void
	 */
	public function render_container(): void {
		?>
		<div vw class="enabled">
			<div vw-access-button class="active"></div>
			<div vw-plugin-wrapper>
				<div class="vw-plugin-top-wrapper"></div>
			</div>
		</div>
		<?php
	}
}
