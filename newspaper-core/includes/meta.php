<?php
/**
 * Post meta (registered for REST so Gutenberg/apps can use it) + classic meta boxes
 * for news posts (kicker, dateline, reporter, photo credit, sponsored) and videos.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'init', function () {
	$auth = fn() => current_user_can( 'edit_posts' );
	foreach ( array( 'np_kicker', 'np_dateline', 'np_reporter', 'np_photo_credit' ) as $k ) {
		register_post_meta( 'post', $k, array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth ) );
	}
	register_post_meta( 'post', 'np_sponsored', array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'auth_callback' => $auth ) );
	register_post_meta( 'post', 'np_live', array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'auth_callback' => $auth ) );
	register_post_meta( 'np_video', 'np_youtube', array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'esc_url_raw', 'auth_callback' => $auth ) );
	register_post_meta( 'np_breaking', 'np_flash', array( 'type' => 'boolean', 'single' => true, 'show_in_rest' => true, 'auth_callback' => $auth ) );
	register_post_meta( 'np_breaking', 'np_expiry', array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => $auth ) );
	register_post_meta( 'np_breaking', 'np_link', array( 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'esc_url_raw', 'auth_callback' => $auth ) );
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'np-news-details', __( 'News details', 'newspaper-core' ), 'np_core_news_box', 'post', 'side', 'high' );
	add_meta_box( 'np-video-details', __( 'YouTube video', 'newspaper-core' ), 'np_core_video_box', 'np_video', 'normal', 'high' );
} );

function np_core_news_box( WP_Post $p ): void {
	wp_nonce_field( 'np_news_save', 'np_news_nonce' );
	$f = array(
		'np_kicker'       => __( 'Sub-headline (kicker)', 'newspaper-core' ),
		'np_dateline'     => __( 'Dateline / location (e.g. ଭୁବନେଶ୍ୱର)', 'newspaper-core' ),
		'np_reporter'     => __( 'Reporter name (if different from author)', 'newspaper-core' ),
		'np_photo_credit' => __( 'Featured photo credit', 'newspaper-core' ),
	);
	foreach ( $f as $k => $label ) {
		printf( '<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s"></p>', esc_attr( $k ), esc_html( $label ), esc_attr( get_post_meta( $p->ID, $k, true ) ) );
	}
	printf( '<p><label><input type="checkbox" name="np_sponsored" value="1" %s> %s</label></p>', checked( (bool) get_post_meta( $p->ID, 'np_sponsored', true ), true, false ), esc_html__( 'Sponsored post (shows "Sponsored" label)', 'newspaper-core' ) );
	printf( '<p><label><input type="checkbox" name="np_live" value="1" %s> %s</label></p>', checked( (bool) get_post_meta( $p->ID, 'np_live', true ), true, false ), esc_html__( 'Live Updates post (enables live entries box below)', 'newspaper-core' ) );
}

function np_core_video_box( WP_Post $p ): void {
	wp_nonce_field( 'np_video_save', 'np_video_nonce' );
	printf( '<p><label for="np_youtube">%s</label><input type="url" class="widefat" id="np_youtube" name="np_youtube" value="%s" placeholder="https://www.youtube.com/watch?v=..."></p>', esc_html__( 'YouTube URL', 'newspaper-core' ), esc_attr( get_post_meta( $p->ID, 'np_youtube', true ) ) );
}

add_action( 'save_post_post', function ( $id ) {
	if ( ! isset( $_POST['np_news_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['np_news_nonce'] ), 'np_news_save' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_revision( $id ) ) { return; }
	foreach ( array( 'np_kicker', 'np_dateline', 'np_reporter', 'np_photo_credit' ) as $k ) {
		update_post_meta( $id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ?? '' ) ) );
	}
	update_post_meta( $id, 'np_sponsored', ! empty( $_POST['np_sponsored'] ) );
	update_post_meta( $id, 'np_live', ! empty( $_POST['np_live'] ) );
} );

add_action( 'save_post_np_video', function ( $id ) {
	if ( ! isset( $_POST['np_video_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['np_video_nonce'] ), 'np_video_save' ) || ! current_user_can( 'edit_post', $id ) ) { return; }
	update_post_meta( $id, 'np_youtube', esc_url_raw( wp_unslash( $_POST['np_youtube'] ?? '' ) ) );
} );
