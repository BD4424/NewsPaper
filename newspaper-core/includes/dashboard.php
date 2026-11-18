<?php
/** Dashboard widget: today's e-Paper status + active Breaking items. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_np_epapers' ) ) { return; }
	wp_add_dashboard_widget( 'np_newsroom', __( 'Newsroom today', 'newspaper-core' ), function () {
		$today = wp_date( 'Y-m-d' );
		$eds   = get_posts( array( 'post_type' => 'np_epaper', 'post_status' => array( 'publish', 'draft', 'pending', 'future' ), 'posts_per_page' => 10, 'meta_query' => array( array( 'key' => 'np_edition_date', 'value' => $today ) ) ) );
		echo '<h3>' . esc_html__( "Today's e-Paper", 'newspaper-core' ) . ' (' . esc_html( $today ) . ')</h3>';
		if ( $eds ) {
			echo '<ul>';
			foreach ( $eds as $e ) {
				$n = count( array_filter( explode( ',', (string) get_post_meta( $e->ID, 'np_pages', true ) ) ) );
				$ok = 'publish' === $e->post_status;
				printf( '<li>%s <a href="%s">%s</a> – %s, %s</li>', $ok ? '✅' : '🟡', esc_url( get_edit_post_link( $e->ID ) ), esc_html( get_post_meta( $e->ID, 'np_edition_name', true ) ?: $e->post_title ), esc_html( sprintf( _n( '%d page', '%d pages', $n, 'newspaper-core' ), $n ) ), esc_html( $ok ? __( 'Uploaded', 'newspaper-core' ) : __( 'Not published yet', 'newspaper-core' ) ) );
			}
			echo '</ul>';
		} else {
			echo '<p>❌ <strong>' . esc_html__( 'Pending – no edition uploaded yet.', 'newspaper-core' ) . '</strong> <a class="button" href="' . esc_url( admin_url( 'post-new.php?post_type=np_epaper' ) ) . '">' . esc_html__( 'Upload now', 'newspaper-core' ) . '</a></p>';
		}

		if ( current_user_can( 'edit_posts' ) ) {
			$b = np_core_active_breaking( 10 );
			echo '<h3>' . esc_html__( 'Active Breaking', 'newspaper-core' ) . ' (' . count( $b ) . ')</h3><ul>';
			foreach ( $b as $p ) {
				$exp = get_post_meta( $p->ID, 'np_expiry', true );
				printf( '<li>%s<a href="%s">%s</a> <small>%s</small></li>', get_post_meta( $p->ID, 'np_flash', true ) ? '⚡ ' : '', esc_url( get_edit_post_link( $p->ID ) ), esc_html( get_the_title( $p ) ), esc_html( $exp ? sprintf( __( 'until %s', 'newspaper-core' ), substr( $exp, 0, 16 ) ) : '' ) );
			}
			echo '</ul><p><a class="button button-primary" href="' . esc_url( admin_url( 'edit.php?post_type=np_breaking&page=np-quick-publish' ) ) . '">⚡ ' . esc_html__( 'Quick Publish', 'newspaper-core' ) . '</a></p>';
		}
	} );
} );
