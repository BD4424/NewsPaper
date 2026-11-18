<?php
/**
 * Theme Options (Appearance → Customize).
 * Every value is read through np_opt() so nothing is hard-coded in templates.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Default values for all options. */
function np_defaults(): array {
	return array(
		'color_primary'   => '#c4161c',
		'color_dark'      => '#111111',
		'base_font_size'  => 17,
		'panjika_text'    => '',
		'epaper_url'      => home_url( '/epaper/' ),
		'ticker_enable'   => true,
		'ticker_label'    => 'ବ୍ରେକିଙ୍ଗ',
		'ticker_speed'    => 60,
		'ticker_count'    => 10,
		'flash_enable'    => true,
		'home_sections'   => 'hero,epaper,latest,state,national,districts,sports,entertainment,business,editorial,gallery,videos',
		'cat_state'       => 'state',
		'cat_national'    => 'national',
		'cat_sports'      => 'sports',
		'cat_entertainment' => 'entertainment',
		'cat_business'    => 'business',
		'cat_editorial'   => 'editorial',
		'social_facebook' => '', 'social_x' => '', 'social_youtube' => '', 'social_instagram' => '',
		'social_whatsapp' => '', 'social_telegram' => '',
		'footer_about'    => '',
		'footer_address'  => '',
		'footer_rni'      => '',
		'footer_copyright'=> '',
		'ga_id'           => '',
		'adsense_client'  => '',
		'onesignal_app_id'=> '',
		'dark_mode'       => true,
		'join_banner'     => true,
	);
}

/** Read a theme option with its default. */
function np_opt( string $key ) {
	$d = np_defaults();
	return get_theme_mod( 'np_' . $key, $d[ $key ] ?? '' );
}

/** Ad slots registry: key => label. */
function np_ad_slots(): array {
	return array(
		'header'     => __( 'Header (728×90)', 'newspaper-name-news' ),
		'sidebar'    => __( 'Sidebar (300×250)', 'newspaper-name-news' ),
		'between'    => __( 'Between homepage sections', 'newspaper-name-news' ),
		'in_article' => __( 'In-article (after paragraph 3)', 'newspaper-name-news' ),
		'mobile_sticky' => __( 'Sticky mobile bottom (320×50)', 'newspaper-name-news' ),
	);
}

/** Ad code may contain <script>; only trusted users keep raw HTML. */
function np_sanitize_code( $value ) {
	return current_user_can( 'unfiltered_html' ) ? $value : wp_kses_post( $value );
}
function np_sanitize_checkbox( $v ) { return (bool) $v; }

add_action( 'customize_register', function ( WP_Customize_Manager $c ) {
	$d = np_defaults();

	$add = function ( $key, $section, $label, $type = 'text', $sanitize = 'sanitize_text_field', $extra = array() ) use ( $c, $d ) {
		$c->add_setting( 'np_' . $key, array( 'default' => $d[ $key ] ?? '', 'sanitize_callback' => $sanitize ) );
		$c->add_control( 'np_' . $key, array_merge( array( 'label' => $label, 'section' => $section, 'type' => $type ), $extra ) );
	};

	$c->add_panel( 'np_panel', array( 'title' => __( 'Newspaper Options', 'newspaper-name-news' ), 'priority' => 20 ) );
	$sections = array(
		'np_brand'   => __( 'Colours & Fonts', 'newspaper-name-news' ),
		'np_topbar'  => __( 'Top Bar & e-Paper', 'newspaper-name-news' ),
		'np_ticker'  => __( 'Breaking Ticker & Flash', 'newspaper-name-news' ),
		'np_home'    => __( 'Homepage Sections', 'newspaper-name-news' ),
		'np_social'  => __( 'Social Links', 'newspaper-name-news' ),
		'np_ads'     => __( 'Ad Manager', 'newspaper-name-news' ),
		'np_footer'  => __( 'Footer & Legal', 'newspaper-name-news' ),
		'np_integr'  => __( 'Analytics & Engagement', 'newspaper-name-news' ),
	);
	foreach ( $sections as $id => $title ) {
		$c->add_section( $id, array( 'title' => $title, 'panel' => 'np_panel' ) );
	}

	// Colours & fonts.
	foreach ( array( 'color_primary' => __( 'Primary (brand) colour', 'newspaper-name-news' ), 'color_dark' => __( 'Dark/menu colour', 'newspaper-name-news' ) ) as $k => $l ) {
		$c->add_setting( 'np_' . $k, array( 'default' => $d[ $k ], 'sanitize_callback' => 'sanitize_hex_color' ) );
		$c->add_control( new WP_Customize_Color_Control( $c, 'np_' . $k, array( 'label' => $l, 'section' => 'np_brand' ) ) );
	}
	$add( 'base_font_size', 'np_brand', __( 'Base font size (px)', 'newspaper-name-news' ), 'number', 'absint', array( 'input_attrs' => array( 'min' => 14, 'max' => 22 ) ) );

	// Top bar.
	$add( 'panjika_text', 'np_topbar', __( 'Panjika / Tithi line (optional, Odia)', 'newspaper-name-news' ) );
	$add( 'epaper_url', 'np_topbar', __( 'e-Paper page URL', 'newspaper-name-news' ), 'url', 'esc_url_raw' );

	// Ticker & flash.
	$add( 'ticker_enable', 'np_ticker', __( 'Show breaking ticker', 'newspaper-name-news' ), 'checkbox', 'np_sanitize_checkbox' );
	$add( 'ticker_label', 'np_ticker', __( 'Ticker label', 'newspaper-name-news' ) );
	$add( 'ticker_speed', 'np_ticker', __( 'Ticker speed (pixels per second)', 'newspaper-name-news' ), 'number', 'absint', array( 'input_attrs' => array( 'min' => 20, 'max' => 200 ) ) );
	$add( 'ticker_count', 'np_ticker', __( 'Max items in ticker', 'newspaper-name-news' ), 'number', 'absint' );
	$add( 'flash_enable', 'np_ticker', __( 'Show full-width FLASH banner on homepage (for items marked Flash)', 'newspaper-name-news' ), 'checkbox', 'np_sanitize_checkbox' );

	// Homepage.
	$add( 'home_sections', 'np_home', __( 'Sections to show, in order (comma separated). Remove a key to hide it.', 'newspaper-name-news' ), 'textarea', 'sanitize_text_field', array(
		'description' => 'hero, epaper, latest, state, national, districts, sports, entertainment, business, editorial, gallery, videos',
	) );
	foreach ( array( 'state', 'national', 'sports', 'entertainment', 'business', 'editorial' ) as $k ) {
		$add( 'cat_' . $k, 'np_home', sprintf( __( 'Category slug for "%s" block', 'newspaper-name-news' ), $k ), 'text', 'sanitize_title' );
	}

	// Social.
	foreach ( array( 'facebook', 'x', 'youtube', 'instagram', 'whatsapp', 'telegram' ) as $k ) {
		$add( 'social_' . $k, 'np_social', ucfirst( $k ) . ' URL', 'url', 'esc_url_raw' );
	}

	// Ads – per slot: enable, device, code, or image+link.
	foreach ( np_ad_slots() as $slot => $label ) {
		$c->add_setting( "np_ad_{$slot}_on", array( 'default' => false, 'sanitize_callback' => 'np_sanitize_checkbox' ) );
		$c->add_control( "np_ad_{$slot}_on", array( 'label' => sprintf( __( '%s: enabled', 'newspaper-name-news' ), $label ), 'section' => 'np_ads', 'type' => 'checkbox' ) );
		$c->add_setting( "np_ad_{$slot}_device", array( 'default' => 'all', 'sanitize_callback' => 'sanitize_key' ) );
		$c->add_control( "np_ad_{$slot}_device", array( 'label' => __( 'Show on', 'newspaper-name-news' ), 'section' => 'np_ads', 'type' => 'select',
			'choices' => array( 'all' => __( 'All devices', 'newspaper-name-news' ), 'desktop' => __( 'Desktop only', 'newspaper-name-news' ), 'mobile' => __( 'Mobile only', 'newspaper-name-news' ) ) ) );
		$c->add_setting( "np_ad_{$slot}_code", array( 'default' => '', 'sanitize_callback' => 'np_sanitize_code' ) );
		$c->add_control( "np_ad_{$slot}_code", array( 'label' => __( 'AdSense / HTML code (leave empty to use image)', 'newspaper-name-news' ), 'section' => 'np_ads', 'type' => 'textarea' ) );
		$c->add_setting( "np_ad_{$slot}_img", array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$c->add_control( new WP_Customize_Image_Control( $c, "np_ad_{$slot}_img", array( 'label' => __( 'Banner image', 'newspaper-name-news' ), 'section' => 'np_ads' ) ) );
		$c->add_setting( "np_ad_{$slot}_link", array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$c->add_control( "np_ad_{$slot}_link", array( 'label' => __( 'Banner link', 'newspaper-name-news' ), 'section' => 'np_ads', 'type' => 'url' ) );
	}

	// Footer & legal.
	$add( 'footer_about', 'np_footer', __( 'About the paper', 'newspaper-name-news' ), 'textarea', 'sanitize_textarea_field' );
	$add( 'footer_address', 'np_footer', __( 'Registered office address', 'newspaper-name-news' ), 'textarea', 'sanitize_textarea_field' );
	$add( 'footer_rni', 'np_footer', __( 'RNI / Registration number', 'newspaper-name-news' ) );
	$add( 'footer_copyright', 'np_footer', __( 'Copyright text (blank = automatic)', 'newspaper-name-news' ) );

	// Integrations.
	$add( 'ga_id', 'np_integr', __( 'Google Analytics 4 ID (G-XXXX)', 'newspaper-name-news' ) );
	$add( 'adsense_client', 'np_integr', __( 'AdSense publisher ID (ca-pub-XXXX)', 'newspaper-name-news' ) );
	$add( 'onesignal_app_id', 'np_integr', __( 'OneSignal App ID (push notifications)', 'newspaper-name-news' ) );
	$add( 'dark_mode', 'np_integr', __( 'Show dark-mode toggle', 'newspaper-name-news' ), 'checkbox', 'np_sanitize_checkbox' );
	$add( 'join_banner', 'np_integr', __( 'Show WhatsApp/Telegram join banner on articles', 'newspaper-name-news' ), 'checkbox', 'np_sanitize_checkbox' );
} );
