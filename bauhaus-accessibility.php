<?php
/**
 * Plugin Name:       Bauhaus Accessibility
 * Description:       Adds the VLibras sign language widget and the Sienna accessibility toolbar to your WordPress site.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      7.0
 * Requires PHP:      8.2
 * Author:            Bauhaus Tech
 * Author URI:        https://bauhaustech.com/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       bauhaus-accessibility
 * Domain Path:       /languages
 *
 * @package Bauhaus_Accessibility
 */

/*
Bauhaus Accessibility is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

Bauhaus Accessibility is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Bauhaus Accessibility. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Autoloader for the plugin classes.
spl_autoload_register(
	function ( string $class_name ): void {
		$prefix   = 'Bauhaus_Accessibility\\';
		$base_dir = __DIR__ . '/includes/';

		if ( strncmp( $prefix, $class_name, strlen( $prefix ) ) !== 0 ) {
				return;
		}

		$relative_class = substr( $class_name, strlen( $prefix ) );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

/**
 * Load plugin textdomain for bundled translations.
 *
 * Must run before the init hook registers translatable strings.
 * Uses plugin_basename() so the path stays correct even if the
 * plugin folder is renamed.
 *
 * @return void
 */
function bauhaus_accessibility_load_textdomain(): void {
	load_plugin_textdomain(
		'bauhaus-accessibility',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'init', 'bauhaus_accessibility_load_textdomain' );

/**
 * Boot the plugin.
 *
 * Runs on 'plugins_loaded' so all WordPress functions are available
 * and other plugins have had a chance to load.
 *
 * @return void
 */
function bauhaus_accessibility_init(): void {
	$plugin = new \Bauhaus_Accessibility\Core\Plugin();
	$plugin->run();
}
add_action( 'plugins_loaded', 'bauhaus_accessibility_init' );
