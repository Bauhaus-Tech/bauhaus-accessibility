<?php
/**
 * Core Plugin class for Bauhaus Accessibility.
 *
 * Responsible for bootstrapping the plugin: registering hooks,
 * loading the admin interface, and coordinating front-end widgets.
 *
 * @package Bauhaus_Accessibility
 */

namespace Bauhaus_Accessibility\Core;

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
	const OPTION_NAME = 'bauhaus_accessibility_settings';

	/**
	 * Run the plugin: register all WordPress hooks.
	 *
	 * This is the single entry point called from the bootstrap file.
	 * It attaches callbacks to the appropriate WordPress actions.
	 *
	 * @return void
	 */
	public function run(): void {
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
			__( 'Accessibility', 'bauhaus-accessibility' ),
			__( 'Accessibility', 'bauhaus-accessibility' ),
			'manage_options',
			'bauhaus-accessibility',
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
			'bauhaus_accessibility_settings_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'enable_vlibras'  => false,
					'enable_sienna'   => false,
					'widget_position' => 'right',
				),
			)
		);

		add_settings_section(
			'bauhaus_accessibility_main',
			__( 'Widget Settings', 'bauhaus-accessibility' ),
			'__return_empty_string',
			'bauhaus_accessibility_settings_group'
		);

		add_settings_field(
			'enable_vlibras',
			__( 'Enable VLibras Sign Language Interpreter', 'bauhaus-accessibility' ),
			array( $this, 'render_enable_vlibras_field' ),
			'bauhaus_accessibility_settings_group',
			'bauhaus_accessibility_main'
		);

		add_settings_field(
			'enable_sienna',
			__( 'Enable Accessibility Widget', 'bauhaus-accessibility' ),
			array( $this, 'render_enable_sienna_field' ),
			'bauhaus_accessibility_settings_group',
			'bauhaus_accessibility_main'
		);

		add_settings_field(
			'widget_position',
			__( 'Widget Position', 'bauhaus-accessibility' ),
			array( $this, 'render_widget_position_field' ),
			'bauhaus_accessibility_settings_group',
			'bauhaus_accessibility_main'
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
				settings_fields( 'bauhaus_accessibility_settings_group' );
				do_settings_sections( 'bauhaus_accessibility_settings_group' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the enable_vlibras checkbox field.
	 *
	 * @return void
	 */
	public function render_enable_vlibras_field(): void {
		$options = get_option( self::OPTION_NAME, array() );
		$value   = ! empty( $options['enable_vlibras'] );
		?>
		<input
			type="checkbox"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_vlibras]"
			value="1"
			<?php checked( $value ); ?>
		>
		<?php
	}

	/**
	 * Render the enable_sienna checkbox field.
	 *
	 * @return void
	 */
	public function render_enable_sienna_field(): void {
		$options = get_option( self::OPTION_NAME, array() );
		$value   = ! empty( $options['enable_sienna'] );
		?>
		<input
			type="checkbox"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_sienna]"
			value="1"
			<?php checked( $value ); ?>
		>
		<?php
	}

	/**
	 * Render the widget_position radio buttons.
	 *
	 * @return void
	 */
	public function render_widget_position_field(): void {
		$options  = get_option( self::OPTION_NAME, array() );
		$position = $options['widget_position'] ?? 'right';
		?>
		<fieldset>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( self::OPTION_NAME ); ?>[widget_position]"
					value="right"
					<?php checked( $position, 'right' ); ?>
				>
				<?php esc_html_e( 'Right', 'bauhaus-accessibility' ); ?>
			</label>
			<br>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( self::OPTION_NAME ); ?>[widget_position]"
					value="left"
					<?php checked( $position, 'left' ); ?>
				>
				<?php esc_html_e( 'Left', 'bauhaus-accessibility' ); ?>
			</label>
		</fieldset>
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

		$sienna  = new \Bauhaus_Accessibility\Frontend\SiennaWidget();
		$vlibras = new \Bauhaus_Accessibility\Frontend\VlibrasWidget();

		$sienna->maybe_enqueue( $options );
		$vlibras->maybe_enqueue( $options );
	}
}
