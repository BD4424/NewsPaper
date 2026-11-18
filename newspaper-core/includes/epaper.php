<?php
/**
 * e-Paper editions.
 * Meta: np_edition_date (Y-m-d), np_edition_name, np_pages (comma-separated attachment IDs, in order), np_pdf (attachment ID).
 * Shortcodes:
 *   [np_epaper]               – landing page: date picker + edition list + recent editions.
 *   [np_epaper_viewer id=""]  – page-by-page viewer (used by the theme's single-np_epaper.php).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------- Admin: edition meta box ---------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'np-epaper', __( 'Edition details & pages', 'newspaper-core' ), 'np_core_epaper_box', 'np_epaper', 'normal', 'high' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || 'np_epaper' !== $screen->post_type || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
	wp_enqueue_media();
	wp_enqueue_script( 'np-epaper-admin', NP_CORE_URL . 'assets/epaper-admin.js', array(), NP_CORE_VERSION, true );
	wp_enqueue_style( 'np-epaper-admin', NP_CORE_URL . 'assets/epaper-admin.css', array(), NP_CORE_VERSION );
} );

/** Known edition names (suggestions) – filterable. */
function np_core_edition_names(): array {
	return apply_filters( 'np_core_edition_names', array( 'ଭୁବନେଶ୍ୱର', 'କଟକ', 'ବ୍ରହ୍ମପୁର', 'ସମ୍ବଲପୁର', 'ବାଲେଶ୍ୱର' ) );
}

function np_core_epaper_box( WP_Post $p ): void {
	wp_nonce_field( 'np_epaper_save', 'np_epaper_nonce' );
	$date  = get_post_meta( $p->ID, 'np_edition_date', true ) ?: wp_date( 'Y-m-d' );
	$name  = get_post_meta( $p->ID, 'np_edition_name', true );
	$pages = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $p->ID, 'np_pages', true ) ) ) );
	$pdf   = absint( get_post_meta( $p->ID, 'np_pdf', true ) );
	?>
	<div class="np-ep-fields">
		<p><label><strong><?php esc_html_e( 'Edition date', 'newspaper-core' ); ?></strong><br><input type="date" name="np_edition_date" value="<?php echo esc_attr( $date ); ?>" required></label></p>
		<p><label><strong><?php esc_html_e( 'Edition name', 'newspaper-core' ); ?></strong><br><input type="text" name="np_edition_name" list="np-ed-names" value="<?php echo esc_attr( $name ); ?>" placeholder="ଭୁବନେଶ୍ୱର"></label>
			<datalist id="np-ed-names"><?php foreach ( np_core_edition_names() as $n ) { echo '<option value="' . esc_attr( $n ) . '">'; } ?></datalist></p>
	</div>

	<h3><?php esc_html_e( 'Page images', 'newspaper-core' ); ?></h3>
	<p class="description"><?php esc_html_e( 'Click "Add / upload pages", then drag all page images (page-01.jpg, page-02.jpg …) into the window. Drag thumbnails to reorder, or use "Sort by filename".', 'newspaper-core' ); ?></p>
	<p>
		<button type="button" class="button button-primary" id="np-ep-add"><?php esc_html_e( 'Add / upload pages', 'newspaper-core' ); ?></button>
		<button type="button" class="button" id="np-ep-sort"><?php esc_html_e( 'Sort by filename', 'newspaper-core' ); ?></button>
		<button type="button" class="button-link-delete" id="np-ep-clear"><?php esc_html_e( 'Remove all', 'newspaper-core' ); ?></button>
	</p>
	<ol class="np-ep-pages" id="np-ep-pages">
		<?php foreach ( $pages as $id ) : $src = wp_get_attachment_image_url( $id, 'thumbnail' ); if ( ! $src ) { continue; } ?>
			<li draggable="true" data-id="<?php echo esc_attr( $id ); ?>" data-name="<?php echo esc_attr( basename( (string) get_attached_file( $id ) ) ); ?>">
				<img src="<?php echo esc_url( $src ); ?>" alt=""><button type="button" class="np-ep-remove" aria-label="<?php esc_attr_e( 'Remove page', 'newspaper-core' ); ?>">×</button>
			</li>
		<?php endforeach; ?>
	</ol>
	<input type="hidden" name="np_pages" id="np-ep-ids" value="<?php echo esc_attr( implode( ',', $pages ) ); ?>">

	<h3><?php esc_html_e( 'Full PDF (optional)', 'newspaper-core' ); ?></h3>
	<p><button type="button" class="button" id="np-ep-pdf"><?php esc_html_e( 'Select / upload PDF', 'newspaper-core' ); ?></button>
		<span id="np-ep-pdf-name"><?php echo $pdf ? esc_html( basename( (string) get_attached_file( $pdf ) ) ) : ''; ?></span>
		<button type="button" class="button-link-delete" id="np-ep-pdf-clear"><?php esc_html_e( 'Remove', 'newspaper-core' ); ?></button>
		<input type="hidden" name="np_pdf" id="np-ep-pdf-id" value="<?php echo esc_attr( $pdf ?: '' ); ?>"></p>
	<?php
}

add_action( 'save_post_np_epaper', function ( $id, $post ) {
	if ( ! isset( $_POST['np_epaper_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['np_epaper_nonce'] ), 'np_epaper_save' ) || ! current_user_can( 'edit_post', $id ) || wp_is_post_revision( $id ) ) { return; }
	$date = sanitize_text_field( wp_unslash( $_POST['np_edition_date'] ?? '' ) );
	$date = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : wp_date( 'Y-m-d' );
	$name = sanitize_text_field( wp_unslash( $_POST['np_edition_name'] ?? '' ) );
	$ids  = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_POST['np_pages'] ?? '' ) ) ) ) );
	update_post_meta( $id, 'np_edition_date', $date );
	update_post_meta( $id, 'np_edition_name', $name );
	update_post_meta( $id, 'np_pages', implode( ',', $ids ) );
	update_post_meta( $id, 'np_pdf', absint( $_POST['np_pdf'] ?? 0 ) );

	// Front page becomes the featured image; auto title if empty.
	if ( $ids ) { set_post_thumbnail( $id, $ids[0] ); }
	if ( '' === trim( $post->post_title ) || __( 'Auto Draft' ) === $post->post_title ) {
		// Second save runs with a non-empty title, so this cannot loop.
		wp_update_post( array( 'ID' => $id, 'post_title' => trim( $name . ' ' . $date ), 'post_name' => sanitize_title( $date . '-' . ( $name ?: 'edition' ) ) ) );
	}
}, 10, 2 );

/* ---------- Access control ---------- */

function np_core_epaper_allowed(): bool {
	return 'public' === np_core_setting( 'epaper_access' ) || is_user_logged_in();
}

function np_core_epaper_login_box(): string {
	return '<div class="np-ep-locked"><p>' . esc_html( np_core_setting( 'epaper_login_message' ) ) . '</p><a class="np-btn" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Log in', 'newspaper-core' ) . '</a></div>';
}

/** PDF download URL – routed through WordPress so access rules apply. */
function np_core_pdf_url( int $edition_id ): string {
	return add_query_arg( array( 'np_epaper_pdf' => $edition_id ), home_url( '/' ) );
}

add_action( 'template_redirect', function () {
	if ( empty( $_GET['np_epaper_pdf'] ) ) { return; } // phpcs:ignore
	$id  = absint( $_GET['np_epaper_pdf'] ); // phpcs:ignore
	$pdf = absint( get_post_meta( $id, 'np_pdf', true ) );
	if ( 'np_epaper' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) || ! $pdf || ! np_core_setting( 'epaper_pdf_download' ) ) { wp_die( esc_html__( 'Not available.', 'newspaper-core' ), '', 404 ); }
	if ( ! np_core_epaper_allowed() ) { auth_redirect(); }
	$file = get_attached_file( $pdf );
	if ( ! $file || ! is_readable( $file ) ) { wp_die( esc_html__( 'File missing.', 'newspaper-core' ), '', 404 ); }
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: inline; filename="' . sanitize_file_name( basename( $file ) ) . '"' );
	header( 'Content-Length: ' . filesize( $file ) );
	readfile( $file ); // phpcs:ignore
	exit;
} );

/* ---------- Front end ---------- */

function np_core_epaper_assets(): void {
	wp_enqueue_style( 'np-epaper', NP_CORE_URL . 'assets/epaper.css', array(), NP_CORE_VERSION );
	wp_enqueue_script( 'np-epaper', NP_CORE_URL . 'assets/epaper-viewer.js', array(), NP_CORE_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
}

function np_core_epaper_query( array $extra = array() ): array {
	return get_posts( array_merge( array( 'post_type' => 'np_epaper', 'posts_per_page' => 12, 'meta_key' => 'np_edition_date', 'orderby' => 'meta_value', 'order' => 'DESC' ), $extra ) );
}

/** [np_epaper] – landing page with calendar. */
add_shortcode( 'np_epaper', function () {
	np_core_epaper_assets();
	$date = isset( $_GET['ed_date'] ) ? sanitize_text_field( wp_unslash( $_GET['ed_date'] ) ) : ''; // phpcs:ignore
	$date = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '';
	$found = $date ? np_core_epaper_query( array( 'meta_query' => array( array( 'key' => 'np_edition_date', 'value' => $date ) ), 'meta_key' => '', 'orderby' => 'title', 'order' => 'ASC' ) ) : array();
	$recent = np_core_epaper_query();
	$latest = $recent ? get_post_meta( $recent[0]->ID, 'np_edition_date', true ) : wp_date( 'Y-m-d' );

	ob_start(); ?>
	<div class="np-ep-landing">
		<form class="np-ep-picker" method="get">
			<label for="np-ed-date"><?php esc_html_e( 'Choose a date', 'newspaper-core' ); ?></label>
			<input type="date" id="np-ed-date" name="ed_date" value="<?php echo esc_attr( $date ?: $latest ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
			<button type="submit" class="np-btn"><?php esc_html_e( 'Open', 'newspaper-core' ); ?></button>
		</form>
		<?php if ( $date ) : ?>
			<h2><?php echo esc_html( sprintf( __( 'Editions for %s', 'newspaper-core' ), $date ) ); ?></h2>
			<?php echo $found ? np_core_epaper_grid( $found ) : '<p>' . esc_html__( 'No edition was uploaded for this date.', 'newspaper-core' ) . '</p>'; // phpcs:ignore ?>
		<?php endif; ?>
		<h2><?php esc_html_e( 'Recent editions', 'newspaper-core' ); ?></h2>
		<?php echo np_core_epaper_grid( $recent ); // phpcs:ignore ?>
	</div>
	<?php
	return ob_get_clean();
} );

function np_core_epaper_grid( array $editions ): string {
	$out = '<ul class="np-ep-grid">';
	foreach ( $editions as $e ) {
		$out .= '<li><a href="' . esc_url( get_permalink( $e ) ) . '">' . get_the_post_thumbnail( $e, 'medium', array( 'loading' => 'lazy' ) )
			. '<strong>' . esc_html( get_post_meta( $e->ID, 'np_edition_name', true ) ) . '</strong><span>' . esc_html( get_post_meta( $e->ID, 'np_edition_date', true ) ) . '</span></a></li>';
	}
	return $out . '</ul>';
}

/** [np_epaper_viewer id=""] – page viewer. */
add_shortcode( 'np_epaper_viewer', function ( $atts ) {
	$id = absint( shortcode_atts( array( 'id' => get_the_ID() ), $atts )['id'] );
	if ( 'np_epaper' !== get_post_type( $id ) ) { return ''; }
	if ( ! np_core_epaper_allowed() ) { return np_core_epaper_login_box(); }
	np_core_epaper_assets();

	$pages = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $id, 'np_pages', true ) ) ) );
	$pdf   = absint( get_post_meta( $id, 'np_pdf', true ) );
	$name  = get_post_meta( $id, 'np_edition_name', true );
	$date  = get_post_meta( $id, 'np_edition_date', true );
	$siblings = np_core_epaper_query( array( 'posts_per_page' => 20, 'meta_query' => array( array( 'key' => 'np_edition_date', 'value' => $date ) ), 'meta_key' => '', 'orderby' => 'title', 'order' => 'ASC' ) );

	ob_start(); ?>
	<div class="np-ep" data-np-epaper tabindex="-1">
		<div class="np-ep__bar">
			<div class="np-ep__info"><strong><?php echo esc_html( $name ); ?></strong> · <?php echo esc_html( $date ); ?>
				<?php if ( count( $siblings ) > 1 ) : ?>
					<select data-navigate aria-label="<?php esc_attr_e( 'Switch edition', 'newspaper-core' ); ?>">
						<?php foreach ( $siblings as $s ) : ?><option value="<?php echo esc_url( get_permalink( $s ) ); ?>" <?php selected( $s->ID, $id ); ?>><?php echo esc_html( get_post_meta( $s->ID, 'np_edition_name', true ) ); ?></option><?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div>
			<div class="np-ep__tools">
				<button type="button" data-ep="prev" aria-label="<?php esc_attr_e( 'Previous page', 'newspaper-core' ); ?>">‹</button>
				<span class="np-ep__count" aria-live="polite"><span data-ep-current>1</span> / <?php echo esc_html( (string) count( $pages ) ); ?></span>
				<button type="button" data-ep="next" aria-label="<?php esc_attr_e( 'Next page', 'newspaper-core' ); ?>">›</button>
				<button type="button" data-ep="zoom-out" aria-label="<?php esc_attr_e( 'Zoom out', 'newspaper-core' ); ?>">−</button>
				<button type="button" data-ep="zoom-in" aria-label="<?php esc_attr_e( 'Zoom in', 'newspaper-core' ); ?>">+</button>
				<button type="button" data-ep="fullscreen" aria-label="<?php esc_attr_e( 'Full screen', 'newspaper-core' ); ?>">⛶</button>
				<?php if ( $pdf && np_core_setting( 'epaper_pdf_download' ) ) : ?>
					<a class="np-ep__pdf" href="<?php echo esc_url( np_core_pdf_url( $id ) ); ?>" target="_blank" rel="noopener">PDF</a>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $pages ) : ?>
			<div class="np-ep__stage" data-ep-stage>
				<?php foreach ( $pages as $i => $pid ) : $full = wp_get_attachment_image_src( $pid, 'full' ); ?>
					<figure class="np-ep__page<?php echo 0 === $i ? ' is-active' : ''; ?>" data-full="<?php echo esc_url( $full[0] ?? '' ); ?>" <?php echo 0 === $i ? '' : 'hidden'; ?>>
						<?php echo wp_get_attachment_image( $pid, 'large', false, array( 'loading' => 0 === $i ? 'eager' : 'lazy', 'alt' => esc_attr( sprintf( __( 'Page %d', 'newspaper-core' ), $i + 1 ) ), 'draggable' => 'false' ) ); ?>
					</figure>
				<?php endforeach; ?>
			</div>
			<ol class="np-ep__thumbs" aria-label="<?php esc_attr_e( 'Pages', 'newspaper-core' ); ?>">
				<?php foreach ( $pages as $i => $pid ) : ?>
					<li><button type="button" data-ep-goto="<?php echo esc_attr( (string) $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Go to page %d', 'newspaper-core' ), $i + 1 ) ); ?>" <?php echo 0 === $i ? 'aria-current="true"' : ''; ?>>
						<?php echo wp_get_attachment_image( $pid, 'thumbnail', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?><span><?php echo esc_html( (string) ( $i + 1 ) ); ?></span></button></li>
				<?php endforeach; ?>
			</ol>
		<?php elseif ( $pdf ) : ?>
			<iframe class="np-ep__pdfframe" src="<?php echo esc_url( np_core_pdf_url( $id ) ); ?>" title="<?php echo esc_attr( get_the_title( $id ) ); ?>"></iframe>
		<?php else : ?>
			<p><?php esc_html_e( 'Pages for this edition are not uploaded yet.', 'newspaper-core' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
} );
