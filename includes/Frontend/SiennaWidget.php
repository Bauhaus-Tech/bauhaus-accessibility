<?php
/**
 * Sienna Accessibility Widget front-end integration.
 *
 * Loads the packaged Sienna UMD bundle so its runtime assets resolve locally.
 *
 * @package Bauhaus_Accessibility\Frontend
 */

namespace Bauhaus_Accessibility\Frontend;

/**
 * Handles front-end output for the Sienna Accessibility Widget.
 */
class SiennaWidget {

	/**
	 * Path to the packaged Sienna UMD bundle, relative to plugin root.
	 *
	 * @var string
	 */
	const UMD_PATH = 'assets/js/sienna-accessibility.umd.js';

	/**
	 * Enqueue scripts and styles if the widget is enabled.
	 *
	 * Called on 'wp_enqueue_scripts'. Reads plugin options to decide
	 * whether to load Sienna assets and how to configure them.
	 *
	 * @param array $options Plugin settings array.
	 * @return void
	 */
	public function maybe_enqueue( array $options ): void {
		if ( empty( $options['enable_sienna'] ) ) {
			return;
		}

		$position = $options['widget_position'] ?? 'right';

		// Sienna 2.0.1 auto-initializes on load. It reads these attributes
		// from the DOM before the external bundle executes.
		// We inject a hidden element BEFORE the bundle so it picks up our config.
		$sienna_position = 'center-' . $position; // center-right or center-left.
		$sienna_lang     = substr( get_locale(), 0, 2 );

		$umd_file = dirname( __DIR__, 2 ) . '/' . self::UMD_PATH;
		$umd_url  = plugin_dir_url( $umd_file ) . basename( $umd_file );

		// Load the reproducible local fork build. Its font resolver derives the
		// sibling fonts directory from this script URL (ADR-006). The cache
		// buster is the plugin version: our fork changes between releases even
		// when upstream's version does not.
		wp_enqueue_script( 'sienna-accessibility', $umd_url, array(), \Bauhaus_Accessibility\Core\Plugin::version(), true );

		// Inject config element before the bundle loads.
		// offset: [horizontal, vertical]. 45px vertical offset places
		// Sienna below VLibras (which sits at center with a 10px nudge).
		wp_add_inline_script(
			'sienna-accessibility',
			sprintf(
				'(function(){'
					. 'var d=document.createElement("div");'
					. 'd.style.display="none";'
					. 'd.setAttribute("data-asw-position","%s");'
					. 'd.setAttribute("data-asw-lang","%s");'
					. 'd.setAttribute("data-asw-offset","10,45");'
					. 'document.body.appendChild(d);'
					. '})();',
				esc_js( $sienna_position ),
				esc_js( $sienna_lang )
			),
			'before'
		);

		// Position CSS for the widget buttons (side + stacking).
		wp_enqueue_style(
			'bauhaus-accessibility',
			plugin_dir_url( $umd_file ) . '../css/bauhaus-accessibility.css',
			array(),
			\Bauhaus_Accessibility\Core\Plugin::version()
		);

		// Add a body class so our CSS can target the correct side.
		add_filter(
			'body_class',
			function ( array $classes ) use ( $position ): array {
				$classes[] = 'bauhaus-widgets-' . $position;
				return $classes;
			}
		);
	}
}
