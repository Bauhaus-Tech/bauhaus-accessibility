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
			$js = str_replace(
				self::CDN_BASE,
				plugin_dir_url( $umd_file ) . '../',
				$js
			);

			wp_add_inline_script( 'sienna-accessibility', $js );
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

		// Inject button style overrides in wp_footer at the very end,
		// AFTER Sienna's JS has injected its own inline styles.
		add_action( 'wp_footer', array( $this, 'render_button_overrides' ), PHP_INT_MAX );
	}

	/**
	 * Output CSS overrides for the Sienna button.
	 *
	 * Must run after Sienna's JS-injected styles. Hooked to wp_footer
	 * at PHP_INT_MAX priority so it's the last thing on the page.
	 *
	 * @return void
	 */
	public function render_button_overrides(): void {
		?>
		<style id="bauhaus-sienna-overrides">
		/* Match VLibras: 40x40px square with 8px radius, blue */
		.asw-menu-btn {
			width: 40px !important;
			height: 40px !important;
			border-radius: 8px !important;
			border: none !important;
			outline: none !important;
			background: #005eb8 !important;
			background: linear-gradient(135deg, #005eb8 0%, #003d7a 100%) !important;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25) !important;
		}

		.asw-menu-btn svg {
			width: 24px !important;
			height: 24px !important;
			min-width: 24px !important;
			min-height: 24px !important;
			max-width: 24px !important;
			max-height: 24px !important;
		}

		/* Stack below VLibras */
		.asw-container .asw-menu-btn {
			top: calc(50% + 28px) !important;
			bottom: auto !important;
		}

		/* Hide footer branding */
		.asw-footer,
		.asw-footer-powered,
		#asw-statement-link,
		.asw-menu a[href*="accessibility-widget.pages.dev"],
		.asw-menu a[href*="sienna.app"] {
			display: none !important;
		}

		@media only screen and (max-width: 768px) {
			.asw-menu-btn {
				width: 38px !important;
				height: 38px !important;
			}
			.asw-menu-btn svg {
				width: 22px !important;
				height: 22px !important;
				min-width: 22px !important;
				min-height: 22px !important;
				max-width: 22px !important;
				max-height: 22px !important;
			}
		}
		</style>
		<?php
	}
}
