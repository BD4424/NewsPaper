<?php
/** Public AJAX endpoints: view counting, homepage "load more", next-story infinite scroll. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* View counter. Nonce is not enforced here on purpose: cached pages may serve stale nonces. Input is an int only. */
function np_ajax_view() {
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $id ) { np_count_view( $id ); }
	wp_send_json_success();
}
add_action( 'wp_ajax_np_view', 'np_ajax_view' );
add_action( 'wp_ajax_nopriv_np_view', 'np_ajax_view' );

/* Latest news "load more". */
function np_ajax_more() {
	$page = isset( $_GET['page'] ) ? max( 2, absint( $_GET['page'] ) ) : 2; // phpcs:ignore
	$q    = new WP_Query( array( 'post_type' => 'post', 'paged' => $page, 'posts_per_page' => 10, 'ignore_sticky_posts' => true ) );
	ob_start();
	while ( $q->have_posts() ) { $q->the_post(); np_card( 'list', true ); }
	wp_reset_postdata();
	wp_send_json_success( array( 'html' => ob_get_clean(), 'more' => $page < $q->max_num_pages ) );
}
add_action( 'wp_ajax_np_more', 'np_ajax_more' );
add_action( 'wp_ajax_nopriv_np_more', 'np_ajax_more' );

/* Next story (previous-in-time post of same category) for single-page infinite scroll. */
function np_ajax_next() {
	$id   = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore
	$post = $id ? get_post( $id ) : null;
	if ( ! $post || 'publish' !== $post->post_status ) { wp_send_json_error(); }
	$GLOBALS['post'] = $post; // phpcs:ignore
	setup_postdata( $post );
	$prev = get_previous_post( true );
	wp_reset_postdata();
	if ( ! $prev ) { wp_send_json_error(); }
	$q = new WP_Query( array( 'p' => $prev->ID, 'post_type' => 'post' ) );
	ob_start();
	while ( $q->have_posts() ) { $q->the_post(); get_template_part( 'template-parts/content/article', null, array( 'next' => true ) ); }
	wp_reset_postdata();
	wp_send_json_success( array( 'html' => ob_get_clean(), 'id' => $prev->ID, 'url' => get_permalink( $prev ), 'title' => get_the_title( $prev ) ) );
}
add_action( 'wp_ajax_np_next', 'np_ajax_next' );
add_action( 'wp_ajax_nopriv_np_next', 'np_ajax_next' );
