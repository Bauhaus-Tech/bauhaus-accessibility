<?php
/**
 * PHPUnit bootstrap for Bauhaus Accessibility tests.
 *
 * Uses Brain Monkey to mock WordPress functions so tests can run without
 * a full WordPress installation.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Brain\Monkey;

Monkey\setUp();

// We need to define ABSPATH for the plugin guard to pass.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

// Define commonly-used WordPress constants that aren't mocked by Brain Monkey.
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}
