<?php
/**
 * Core Plugin class for Bauhaus Acessibilidade BR.
 *
 * Responsible for bootstrapping the plugin: registering hooks,
 * loading the admin interface, and coordinating front-end widgets.
 *
 * @package Bauhaus_Acessibilidade
 */

namespace Bauhaus_Acessibilidade\Core;

/**
 * Main plugin class.
 *
 * Hooks into WordPress lifecycle to register admin pages, settings,
 * and front-end assets.
 */
class Plugin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Option name used to store plugin settings.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'bauhaus_acessibilidade_settings';

	/**
	 * Run the plugin: register all WordPress hooks.
	 *
	 * This is the single entry point called from the bootstrap file.
	 * It attaches callbacks to the appropriate WordPress actions.
	 *
	 * @return void
	 */
	public function run(): void {
		load_plugin_textdomain(
			'bauhaus-acessibilidade-br',
			false,
			dirname( __DIR__, 2 ) . '/languages'
		);

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Add the plugin's admin page under Settings.
	 *
	 * Hooked to 'admin_menu'.
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'options-general.php',
			__( 'Acessibilidade BR', 'bauhaus-acessibilidade-br' ),
			__( 'Acessibilidade BR', 'bauhaus-acessibilidade-br' ),
			'manage_options',
			'bauhaus-acessibilidade-br',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Register plugin settings with the WordPress Settings API.
	 *
	 * Hooked to 'admin_init'.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'bauhaus_acessibilidade_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'enable_vlibras'  => false,
					'enable_sienna'   => false,
					'widget_position' => 'right',
					'sign_language'   => 'libras',
				),
			)
		);
	}

	/**
	 * Sanitize the settings array before saving.
	 *
	 * @param array $input The raw input array.
	 * @return array The sanitized settings array.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();

		$sanitized['enable_vlibras']  = ! empty( $input['enable_vlibras'] );
		$sanitized['enable_sienna']   = ! empty( $input['enable_sienna'] );
		$position                     = $input['widget_position'] ?? 'right';
		$sanitized['widget_position'] = in_array( $position, array( 'left', 'right' ), true )
			? $position
			: 'right';
		$sanitized['sign_language']   = sanitize_text_field( $input['sign_language'] ?? 'libras' );

		return $sanitized;
	}

	/**
	 * Render the admin settings page.
	 *
	 * Outputs the page wrapper and form. Individual fields are rendered
	 * by the SettingsPage class in later phases.
	 *
	 * @return void
	 */
	public function render_admin_page(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'bauhaus_acessibilidade_settings_group' );
				do_settings_sections( 'bauhaus_acessibilidade_settings_group' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Enqueue front-end scripts and styles for enabled widgets.
	 *
	 * Hooked to 'wp_enqueue_scripts'.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets(): void {
		$options = get_option( self::OPTION_NAME, array() );

		$sienna  = new \Bauhaus_Acessibilidade\Frontend\SiennaWidget();
		$vlibras = new \Bauhaus_Acessibilidade\Frontend\VlibrasWidget();

		$sienna->maybe_enqueue( $options );
		$vlibras->maybe_enqueue( $options );
	}
}
