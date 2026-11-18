<?php
/**
 * Quick Publish (Breaking News → Quick Publish): headline + one line + optional photo → live in seconds.
 * Can publish as a ticker item (Breaking), a Flash, and/or a normal news post.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=np_breaking', __( 'Quick Publish', 'newspaper-core' ), '⚡ ' . __( 'Quick Publish', 'newspaper-core' ), 'edit_posts', 'np-quick-publish', 'np_core_quick_publish_page', 0 );
} );

function np_core_quick_publish_page(): void {
	$can_publish = current_user_can( 'publish_posts' );
	$cats        = get_categories( array( 'hide_empty' => false ) );
	$done        = isset( $_GET['np_done'] ) ? absint( $_GET['np_done'] ) : 0; // phpcs:ignore
	?>
	<div class="wrap" style="max-width:720px">
		<h1>⚡ <?php esc_html_e( 'Quick Publish', 'newspaper-core' ); ?></h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p><?php echo $can_publish ? esc_html__( 'Published!', 'newspaper-core' ) : esc_html__( 'Submitted for review.', 'newspaper-core' ); ?>
				<a href="<?php echo esc_url( get_permalink( $done ) ); ?>" target="_blank"><?php esc_html_e( 'View', 'newspaper-core' ); ?></a> ·
				<a href="<?php echo esc_url( get_edit_post_link( $done ) ); ?>"><?php esc_html_e( 'Edit in full', 'newspaper-core' ); ?></a></p></div>
		<?php endif; ?>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="np_quick_publish">
			<?php wp_nonce_field( 'np_quick_publish', 'np_qp_nonce' ); ?>
			<p><label for="np-qp-title"><strong><?php esc_html_e( 'Headline', 'newspaper-core' ); ?></strong></label>
				<input type="text" id="np-qp-title" name="title" class="widefat" style="font-size:1.4em;padding:8px" required autofocus lang="or"></p>
			<p><label for="np-qp-line"><strong><?php esc_html_e( 'One line (optional)', 'newspaper-core' ); ?></strong></label>
				<textarea id="np-qp-line" name="line" class="widefat" rows="3" lang="or"></textarea></p>
			<p><label><strong><?php esc_html_e( 'Photo (optional)', 'newspaper-core' ); ?></strong><br><input type="file" name="photo" accept="image/*" capture="environment"></label></p>
			<fieldset style="margin:12px 0"><legend><strong><?php esc_html_e( 'Publish as', 'newspaper-core' ); ?></strong></legend>
				<label><input type="checkbox" name="as_breaking" value="1" checked> <?php esc_html_e( 'Breaking ticker item', 'newspaper-core' ); ?></label><br>
				<label><input type="checkbox" name="as_flash" value="1"> <?php esc_html_e( 'FLASH banner on homepage', 'newspaper-core' ); ?></label><br>
				<label><input type="checkbox" name="as_post" value="1"> <?php esc_html_e( 'Also create a news post in category:', 'newspaper-core' ); ?></label>
				<select name="cat"><?php foreach ( $cats as $c ) { echo '<option value="' . esc_attr( (string) $c->term_id ) . '">' . esc_html( $c->name ) . '</option>'; } ?></select>
			</fieldset>
			<p><label><?php esc_html_e( 'Hide ticker item after (hours)', 'newspaper-core' ); ?> <input type="number" name="hours" min="1" max="168" value="<?php echo esc_attr( (string) np_core_setting( 'breaking_default_hours' ) ); ?>" style="width:70px"></label></p>
			<?php submit_button( $can_publish ? __( 'Publish now', 'newspaper-core' ) : __( 'Submit for review', 'newspaper-core' ), 'primary hero' ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_post_np_quick_publish', function () {
	if ( ! current_user_can( 'edit_posts' ) || ! isset( $_POST['np_qp_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['np_qp_nonce'] ), 'np_quick_publish' ) ) { wp_die( esc_html__( 'Not allowed', 'newspaper-core' ), 403 ); }

	$title  = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
	$line   = sanitize_textarea_field( wp_unslash( $_POST['line'] ?? '' ) );
	$status = current_user_can( 'publish_posts' ) ? 'publish' : 'pending';
	$hours  = min( 168, max( 1, absint( $_POST['hours'] ?? 6 ) ) );
	if ( '' === $title ) { wp_die( esc_html__( 'Headline is required.', 'newspaper-core' ) ); }

	// Optional photo.
	$photo_id = 0;
	if ( ! empty( $_FILES['photo']['name'] ) && current_user_can( 'upload_files' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$photo_id = media_handle_upload( 'photo', 0, array(), array( 'test_form' => false, 'mimes' => array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp' ) ) );
		if ( is_wp_error( $photo_id ) ) { $photo_id = 0; }
	}

	$post_id = 0;
	if ( ! empty( $_POST['as_post'] ) ) {
		$post_id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => $status, 'post_title' => $title, 'post_content' => $line ? '<!-- wp:paragraph --><p>' . esc_html( $line ) . '</p><!-- /wp:paragraph -->' : '', 'post_category' => array( absint( $_POST['cat'] ?? 0 ) ) ) );
		if ( $post_id && $photo_id ) { set_post_thumbnail( $post_id, $photo_id ); }
	}

	$b_id = 0;
	if ( ! empty( $_POST['as_breaking'] ) || ! empty( $_POST['as_flash'] ) ) {
		$b_id = wp_insert_post( array( 'post_type' => 'np_breaking', 'post_status' => $status, 'post_title' => $title, 'post_content' => $line ? '<p>' . esc_html( $line ) . '</p>' : '' ) );
		if ( $b_id && ! is_wp_error( $b_id ) ) {
			if ( $photo_id ) { set_post_thumbnail( $b_id, $photo_id ); }
			np_core_save_breaking_meta( $b_id, ! empty( $_POST['as_flash'] ), wp_date( 'Y-m-d H:i', time() + $hours * HOUR_IN_SECONDS ), $post_id && 'publish' === $status ? get_permalink( $post_id ) : '' );
		}
	}

	$done = $post_id ?: $b_id;
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'np_breaking', 'page' => 'np-quick-publish', 'np_done' => is_wp_error( $done ) ? 0 : $done ), admin_url( 'edit.php' ) ) );
	exit;
} );
