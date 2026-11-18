<?php
/**
 * View counter (cache-safe: counted via AJAX, so it works with LiteSpeed/page cache).
 * Meta: np_views (total), np_views_daily (array Ymd => count, last 8 days),
 *       np_views_1d (today), np_views_7d (rolling 7 days) – used for "Most Read".
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function np_count_view( int $post_id ): void {
	if ( 'post' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) { return; }
	$today = wp_date( 'Ymd' );
	$daily = (array) get_post_meta( $post_id, 'np_views_daily', true );
	$daily[ $today ] = (int) ( $daily[ $today ] ?? 0 ) + 1;
	krsort( $daily );
	$daily = array_slice( $daily, 0, 8, true );
	$week  = 0;
	$limit = (int) wp_date( 'Ymd', strtotime( '-6 days' ) );
	foreach ( $daily as $day => $n ) {
		if ( (int) $day >= $limit ) { $week += (int) $n; }
	}
	update_post_meta( $post_id, 'np_views_daily', $daily );
	update_post_meta( $post_id, 'np_views_1d', $daily[ $today ] );
	update_post_meta( $post_id, 'np_views_7d', $week );
	update_post_meta( $post_id, 'np_views', (int) get_post_meta( $post_id, 'np_views', true ) + 1 );
}

function np_views( ?int $id = null ): string {
	return np_odia_digits( number_format_i18n( (int) get_post_meta( $id ?? get_the_ID(), 'np_views', true ) ) );
}

/** Most-read query. $range: '1d' | '7d'. */
function np_most_read( string $range = '1d', int $count = 6 ): WP_Query {
	$key  = '7d' === $range ? 'np_views_7d' : 'np_views_1d';
	$args = array(
		'post_type' => 'post', 'posts_per_page' => $count, 'ignore_sticky_posts' => true, 'no_found_rows' => true,
		'meta_key' => $key, 'orderby' => 'meta_value_num', 'order' => 'DESC',
		'date_query' => array( array( 'after' => '7d' === $range ? '8 days ago' : '2 days ago' ) ),
	);
	$cache = 'np_mr_' . $range . '_' . $count;
	$ids   = get_transient( $cache );
	if ( false === $ids ) {
		$ids = ( new WP_Query( $args + array( 'fields' => 'ids' ) ) )->posts;
		set_transient( $cache, $ids, 10 * MINUTE_IN_SECONDS );
	}
	return new WP_Query( array( 'post__in' => $ids ?: array( 0 ), 'orderby' => 'post__in', 'posts_per_page' => $count, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
}
