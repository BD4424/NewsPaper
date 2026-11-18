<?php
/** Theme supports, menus, image sizes, sidebars. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'newspaper-name-news', NP_THEME_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 100, 'width' => 400, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'newspaper-name-news' ),
		'footer'  => __( 'Footer Quick Links', 'newspaper-name-news' ),
	) );

	add_image_size( 'np-hero', 960, 540, true );
	add_image_size( 'np-card', 480, 270, true );
	add_image_size( 'np-thumb', 160, 100, true );
} );

add_action( 'after_setup_theme', function () { $GLOBALS['content_width'] = 820; }, 0 );

/* Force lang="or" (Odia) on the <html> tag unless the site locale is already Odia. */
add_filter( 'language_attributes', function ( $output ) {
	if ( is_admin() ) { return $output; }
	return preg_replace( '/lang="[^"]*"/', 'lang="or"', $output ) ?: 'lang="or"';
} );

add_action( 'widgets_init', function () {
	$areas = array(
		'sidebar-1' => __( 'Sidebar', 'newspaper-name-news' ),
		'footer-1'  => __( 'Footer Column 1', 'newspaper-name-news' ),
		'footer-2'  => __( 'Footer Column 2', 'newspaper-name-news' ),
	);
	foreach ( $areas as $id => $name ) {
		register_sidebar( array(
			'id' => $id, 'name' => $name,
			'before_widget' => '<section id="%1$s" class="widget %2$s">', 'after_widget' => '</section>',
			'before_title' => '<h2 class="widget-title">', 'after_title' => '</h2>',
		) );
	}
} );

/* Excerpt length for Odia (words are longer). */
add_filter( 'excerpt_length', fn() => 24 );
add_filter( 'excerpt_more', fn() => '…' );

add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
