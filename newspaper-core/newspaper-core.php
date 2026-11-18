<?php
/**
 * Plugin Name:       Newspaper Core
 * Description:       Content layer for the [NEWSPAPER_NAME] News portal: Breaking/Flash news, e-Paper editions, Photo Galleries, Videos, Districts, Live Updates, newsroom roles, Quick Publish and forms. Content stays safe if you change the theme.
 * Version:           0.2.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Text Domain:       newspaper-core
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'NP_CORE_VERSION', '0.2.0' );
define( 'NP_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'NP_CORE_URL', plugin_dir_url( __FILE__ ) );

foreach ( array( 'post-types', 'meta', 'breaking', 'roles', 'settings', 'epaper', 'live-updates', 'quick-publish', 'dashboard', 'forms', 'demo' ) as $np_file ) {
	require_once NP_CORE_DIR . 'includes/' . $np_file . '.php';
}

add_action( 'plugins_loaded', function () {
	load_plugin_textdomain( 'newspaper-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
} );

register_activation_hook( __FILE__, function () {
	np_core_register_types();
	np_core_seed_districts();
	np_core_add_roles();
	flush_rewrite_rules();
} );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
