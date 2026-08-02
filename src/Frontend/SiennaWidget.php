<?php
/**
 * Sienna Accessibility Widget front-end integration.
 *
 * Outputs the Sienna UMD bundle inline (with CDN asset URLs patched to local
 * paths) so no external requests are made for Sienna assets.
 *
 * @package Bauhaus_Acessibilidade\Frontend
 */

namespace Bauhaus_Acessibilidade\Frontend;

/**
 * Handles front-end output for the Sienna Accessibility Widget.
 */
class SiennaWidget {

	/**
	 * Path to the original Sienna UMD bundle, relative to plugin root.
	 *
	 * @var string
	 */
	const UMD_PATH = 'assets/js/sienna-accessibility.umd.js';

	/**
	 * CDN base URL hardcoded in the Sienna 2.2.333 UMD bundle.
	 *
	 * @var string
	 */
	const CDN_BASE = 'https://cdn.jsdelivr.net/npm/sienna-accessibility/dist/';

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

		// Sienna 2.x auto-initializes on load. It reads its config from
		// data-position/data-lang attributes on any element in the DOM.
		// We inject a hidden element BEFORE the bundle so it picks up our config.
		$sienna_position = 'center-' . $position; // center-right or center-left.
		$sienna_lang     = substr( get_locale(), 0, 2 );

		// Register a dummy script handle so we can attach inline JS to it.
		// Using false as src means no external file — all JS is inline.
		wp_register_script( 'sienna-accessibility', false, array(), '2.2.333', true );
		wp_enqueue_script( 'sienna-accessibility' );

		// Inject config element before the bundle loads.
		wp_add_inline_script(
			'sienna-accessibility',
			sprintf(
				'(function(){'
					. 'var d=document.createElement("div");'
					. 'd.style.display="none";'
					. 'd.setAttribute("data-position","%s");'
					. 'd.setAttribute("data-lang","%s");'
					. 'document.body.appendChild(d);'
					. '})();',
				esc_js( $sienna_position ),
				esc_js( $sienna_lang )
			),
			'before'
		);

		// Read the UMD bundle and patch CDN URLs to local paths.
		$umd_file = dirname( __DIR__, 2 ) . '/' . self::UMD_PATH;
		if ( file_exists( $umd_file ) ) {
			$js = file_get_contents( $umd_file );

			// Patch CDN asset URLs → local plugin directory.
			$js = str_replace(
				self::CDN_BASE,
				plugin_dir_url( $umd_file ) . '../',
				$js
			);

			// Patch button style to match VLibras: square, 40px, blue.
			$js = str_replace(
				array(
					'--asw-btn-size: 58px',
					'--asw-icon-size: 36px',
					'border-radius:50%!important',
					'border:3px solid white!important',
					'outline:5px solid var(--asw-primary)!important',
					',20],size:58,',
					'primaryColor:"#0848ca"',
				),
				array(
					'--asw-btn-size: 40px',
					'--asw-icon-size: 24px',
					'border-radius:8px!important',
					'border:none!important',
					'outline:none!important',
					',20],size:40,',
					'primaryColor:"#005eb8"',
				),
				$js
			);

			wp_add_inline_script( 'sienna-accessibility', $js );

		// Hide Sienna footer branding after the widget initializes.
		wp_add_inline_script(
			'sienna-accessibility',
			'(function(){'
				. 'var tries=0;'
				. 'var ival=setInterval(function(){'
				. 'var f=document.querySelector(".asw-footer");'
				. 'if(f){f.remove();clearInterval(ival);}'
				. 'if(++tries>20) clearInterval(ival);'
				. '}, 200);'
				. '})();',
			'after'
		);
		}

		// Position CSS for the widget buttons (side + stacking).
		wp_enqueue_style(
			'bauhaus-accessibility',
			plugin_dir_url( $umd_file ) . '../css/bauhaus-accessibility.css',
			array(),
			'1.0.0'
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
