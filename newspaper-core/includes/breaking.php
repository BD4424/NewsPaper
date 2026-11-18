<?php
/**
 * Breaking News: Flash toggle, expiry (auto-hide), optional link to full story.
 * Expiry is stored as "Y-m-d H:i:s" in the site's timezone. Empty expiry = never expires.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'np-breaking', __( 'Breaking settings', 'newspaper-core' ), 'np_core_breaking_box', 'np_breaking', 'side', 'high' );
} );

function np_core_breaking_box( WP_Post $p ): void {
	wp_nonce_field( 'np_breaking_save', 'np_breaking_nonce' );
	$exp = (string) get_post_meta( $p->ID, 'np_expiry', true );
	if ( ! $exp && 'auto-draft' === $p->post_status ) {
		$exp = wp_date( 'Y-m-d H:i:s', time() + absint( np_core_setting( 'breaking_default_hours' ) ) * HOUR_IN_SECONDS );
	}
	?>
	<p><label><input type="checkbox" name="np_flash" value="1" <?php checked( (bool) get_post_meta( $p->ID, 'np_flash', true ) ); ?>> <strong><?php esc_html_e( 'FLASH (show big banner on homepage)', 'newspaper-core' ); ?></strong></label></p>
	<p><label for="np_expiry"><?php esc_html_e( 'Auto-hide after', 'newspaper-core' ); ?></label><br>
		<input type="datetime-local" id="np_expiry" name="np_expiry" value="<?php echo esc_attr( $exp ? substr( str_replace( ' ', 'T', $exp ), 0, 16 ) : '' ); ?>"><br>
		<small><?php esc_html_e( 'Leave empty to never expire.', 'newspaper-core' ); ?></small></p>
	<p><label for="np_link"><?php esc_html_e( 'Link to full story (optional)', 'newspaper-core' ); ?></label>
		<input type="url" class="widefat" id="np_link" name="np_link" value="<?php echo esc_attr( get_post_meta( $p->ID, 'np_link', true ) ); ?>"></p>
	<?php
}

add_action( 'save_post_np_breaking', function ( $id ) {
	if ( ! isset( $_POST['np_breaking_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['np_breaking_nonce'] ), 'np_breaking_save' ) || ! current_user_can( 'edit_post', $id ) ) { return; }
	np_core_save_breaking_meta( $id, ! empty( $_POST['np_flash'] ), sanitize_text_field( wp_unslash( $_POST['np_expiry'] ?? '' ) ), esc_url_raw( wp_unslash( $_POST['np_link'] ?? '' ) ) );
} );

/** Shared by the edit screen and Quick Publish. $expiry accepts "Y-m-d\TH:i" or "Y-m-d H:i[:s]". */
function np_core_save_breaking_meta( int $id, bool $flash, string $expiry, string $link = '' ): void {
	update_post_meta( $id, 'np_flash', $flash );
	$expiry = trim( str_replace( 'T', ' ', $expiry ) );
	if ( $expiry && preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $expiry ) ) {
		update_post_meta( $id, 'np_expiry', substr( $expiry, 0, 16 ) . ':00' );
	} else {
		delete_post_meta( $id, 'np_expiry' );
	}
	update_post_meta( $id, 'np_link', $link );
	delete_transient( 'np_breaking_cache' );
}

/** Meta query: not expired. */
function np_core_not_expired_query(): array {
	return array(
		'relation' => 'OR',
		array( 'key' => 'np_expiry', 'compare' => 'NOT EXISTS' ),
		array( 'key' => 'np_expiry', 'value' => '', 'compare' => '=' ),
		array( 'key' => 'np_expiry', 'value' => current_time( 'mysql' ), 'compare' => '>=', 'type' => 'DATETIME' ),
	);
}

/**
 * Active breaking items for the theme ticker / flash banner.
 * @return WP_Post[]
 */
function np_core_active_breaking( int $count = 10, bool $flash_only = false ): array {
	$mq = array( np_core_not_expired_query() );
	if ( $flash_only ) {
		$mq[] = array( 'key' => 'np_flash', 'value' => '1' );
		$mq['relation'] = 'AND';
	}
	return get_posts( array( 'post_type' => 'np_breaking', 'post_status' => 'publish', 'posts_per_page' => max( 1, $count ), 'meta_query' => $mq, 'no_found_rows' => true, 'suppress_filters' => false ) );
}

/* Hide expired items on the public Breaking archive. */
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'np_breaking' ) ) {
		$q->set( 'meta_query', array( np_core_not_expired_query() ) );
	}
} );

/* Admin list columns. */
add_filter( 'manage_np_breaking_posts_columns', function ( $c ) {
	$c['np_flash'] = __( 'Flash', 'newspaper-core' );
	$c['np_expiry'] = __( 'Status', 'newspaper-core' );
	return $c;
} );
add_action( 'manage_np_breaking_posts_custom_column', function ( $col, $id ) {
	if ( 'np_flash' === $col ) { echo get_post_meta( $id, 'np_flash', true ) ? '⚡' : '—'; }
	if ( 'np_expiry' === $col ) {
		$e = get_post_meta( $id, 'np_expiry', true );
		if ( ! $e ) { esc_html_e( 'Active (no expiry)', 'newspaper-core' ); }
		elseif ( $e < current_time( 'mysql' ) ) { echo '<span style="color:#a00">' . esc_html__( 'Expired', 'newspaper-core' ) . '</span>'; }
		else { echo esc_html( sprintf( __( 'Active until %s', 'newspaper-core' ), $e ) ); }
	}
}, 10, 2 );
