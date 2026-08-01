<?php
/**
 * Uninstall handler for Bauhaus Acessibilidade BR.
 *
 * Runs when the plugin is deleted through the WordPress admin.
 * Cleans up the plugin's option from the database.
 *
 * @package Bauhaus_Acessibilidade
 */

// Prevent direct access.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'bauhaus_acessibilidade_settings' );
