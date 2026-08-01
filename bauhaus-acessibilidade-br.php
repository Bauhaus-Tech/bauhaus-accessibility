<?php
/**
 * Plugin Name:     Bauhaus Acessibilidade BR
 * Plugin URI:      https://bauhaus.com.br
 * Description:     Adds the VLibras sign language widget and the Sienna accessibility toolbar to your WordPress site.
 * Version:         1.0.0
 * Author:          Bauhaus
 * Author URI:      https://bauhaus.com.br
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     bauhaus-acessibilidade-br
 * Domain Path:     /languages
 *
 * @package Bauhaus_Acessibilidade
 */

/*
Bauhaus Acessibilidade BR is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Bauhaus Acessibilidade BR is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Bauhaus Acessibilidade BR. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Composer autoloader if available.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// If no Composer autoloader, fall back to a manual autoloader.
if ( ! class_exists( \Bauhaus_Acessibilidade\Core\Plugin::class ) ) {
	spl_autoload_register(
		function ( string $class_name ): void {
			$prefix   = 'Bauhaus_Acessibilidade\\';
			$base_dir = __DIR__ . '/src/';

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
}

/**
 * Boot the plugin.
 *
 * Runs on 'plugins_loaded' so all WordPress functions are available
 * and other plugins have had a chance to load.
 *
 * @return void
 */
function bauhaus_acessibilidade_br_init(): void {
	$plugin = new \Bauhaus_Acessibilidade\Core\Plugin();
	$plugin->run();
}
add_action( 'plugins_loaded', 'bauhaus_acessibilidade_br_init' );
