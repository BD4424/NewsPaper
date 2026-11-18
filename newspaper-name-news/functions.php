<?php
/**
 * [NEWSPAPER_NAME] News – theme bootstrap.
 * Each feature lives in its own file under /inc.
 *
 * @package Newspaper_Name_News
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'NP_THEME_VERSION', '0.2.0' );
define( 'NP_THEME_DIR', get_template_directory() );
define( 'NP_THEME_URI', get_template_directory_uri() );

foreach ( array( 'setup', 'odia-date', 'customizer', 'enqueue', 'template-tags', 'ads', 'views', 'seo-schema', 'ajax' ) as $np_module ) {
	require_once NP_THEME_DIR . '/inc/' . $np_module . '.php';
}

/** Admin notice when the companion plugin is missing. */
add_action( 'admin_notices', function () {
	if ( current_user_can( 'activate_plugins' ) && ! defined( 'NP_CORE_VERSION' ) ) {
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Please activate the "Newspaper Core" plugin to enable Breaking News, e-Paper, Galleries, Videos and Districts.', 'newspaper-name-news' ) . '</p></div>';
	}
} );
