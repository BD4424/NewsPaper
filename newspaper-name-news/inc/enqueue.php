<?php
/** Styles, scripts, inline CSS variables, analytics. No external CDNs except optional GA/AdSense/OneSignal. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'np-main', NP_THEME_URI . '/assets/css/main.css', array(), NP_THEME_VERSION );
	wp_add_inline_style( 'np-main', sprintf(
		':root{--np-primary:%1$s;--np-dark:%2$s;--np-font-size:%3$dpx;--np-ticker-speed:%4$d}',
		esc_attr( np_opt( 'color_primary' ) ), esc_attr( np_opt( 'color_dark' ) ), absint( np_opt( 'base_font_size' ) ), absint( np_opt( 'ticker_speed' ) )
	) );

	wp_enqueue_script( 'np-main', NP_THEME_URI . '/assets/js/main.js', array(), NP_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script( 'np-main', 'NP', array(
		'ajax'   => admin_url( 'admin-ajax.php' ),
		'nonce'  => wp_create_nonce( 'np_ajax' ),
		'postId' => is_singular( 'post' ) ? get_the_ID() : 0,
		'i18n'   => array( 'copied' => __( 'Link copied', 'newspaper-name-news' ), 'loading' => __( 'Loading…', 'newspaper-name-news' ) ),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
} );

/* Preload the self-hosted Odia font if present. */
add_action( 'wp_head', function () {
	if ( file_exists( NP_THEME_DIR . '/assets/fonts/NotoSansOriya-Regular.woff2' ) ) {
		echo '<link rel="preload" href="' . esc_url( NP_THEME_URI . '/assets/fonts/NotoSansOriya-Regular.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	}
}, 1 );

/* Prevent dark-mode flash: apply saved preference before paint. */
add_action( 'wp_head', function () {
	echo "<script>try{if(localStorage.getItem('np-theme')==='dark')document.documentElement.classList.add('np-dark')}catch(e){}</script>\n";
}, 2 );

/* Google Analytics 4, AdSense auto script, OneSignal – only when IDs are set. */
add_action( 'wp_head', function () {
	$ga = np_opt( 'ga_id' );
	if ( $ga && preg_match( '/^G-[A-Z0-9]+$/', $ga ) ) {
		printf( "<script async src=\"https://www.googletagmanager.com/gtag/js?id=%1\$s\"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','%1\$s');</script>\n", esc_attr( $ga ) );
	}
	$ads = np_opt( 'adsense_client' );
	if ( $ads && preg_match( '/^ca-pub-\d+$/', $ads ) ) {
		printf( "<script async src=\"https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=%s\" crossorigin=\"anonymous\"></script>\n", esc_attr( $ads ) );
	}
	$os = np_opt( 'onesignal_app_id' );
	if ( $os && preg_match( '/^[a-f0-9\-]{36}$/i', $os ) ) {
		printf( "<script src=\"https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js\" defer></script><script>window.OneSignalDeferred=window.OneSignalDeferred||[];OneSignalDeferred.push(async function(O){await O.init({appId:'%s'});});</script>\n", esc_attr( $os ) );
	}
}, 20 );
