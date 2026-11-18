<?php
/**
 * Tools → Newspaper Demo: one-click sample content (categories, Odia posts, districts, breaking,
 * legal pages, menus, 1 e-Paper edition with placeholder pages). Everything is tagged np_demo=1
 * so it can be removed with one click.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
	add_management_page( __( 'Newspaper Demo', 'newspaper-core' ), __( 'Newspaper Demo', 'newspaper-core' ), 'manage_options', 'np-demo', 'np_core_demo_page' );
} );

function np_core_demo_page(): void { ?>
	<div class="wrap"><h1><?php esc_html_e( 'Newspaper demo content', 'newspaper-core' ); ?></h1>
		<?php if ( isset( $_GET['np_msg'] ) ) : ?><div class="notice notice-success"><p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['np_msg'] ) ) ); ?></p></div><?php endif; // phpcs:ignore ?>
		<p><?php esc_html_e( 'Creates categories, ~16 sample Odia stories, 3 breaking items (1 Flash), legal pages, contact/advertise pages, menus and one sample e-Paper edition.', 'newspaper-core' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
			<input type="hidden" name="action" value="np_demo_import"><?php wp_nonce_field( 'np_demo' ); ?>
			<?php submit_button( __( 'Import demo content', 'newspaper-core' ), 'primary', 'submit', false ); ?>
		</form>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;margin-left:12px" onsubmit="return confirm('Delete all demo content?')">
			<input type="hidden" name="action" value="np_demo_remove"><?php wp_nonce_field( 'np_demo' ); ?>
			<?php submit_button( __( 'Remove demo content', 'newspaper-core' ), 'delete', 'submit', false ); ?>
		</form>
	</div>
	<?php
}

/** Create a simple placeholder JPEG with GD and register it as an attachment. */
function np_core_demo_image( string $label, int $w = 1200, int $h = 675, array $rgb = array( 196, 22, 28 ) ): int {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) { return 0; }
	$up = wp_upload_dir();
	$fn = wp_unique_filename( $up['path'], sanitize_file_name( $label ) . '.jpg' );
	$im = imagecreatetruecolor( $w, $h );
	for ( $y = 0; $y < $h; $y++ ) { // vertical gradient.
		$f = $y / $h;
		$c = imagecolorallocate( $im, (int) ( $rgb[0] * ( 1 - $f * .6 ) ), (int) ( $rgb[1] * ( 1 - $f * .6 ) ), (int) ( $rgb[2] * ( 1 - $f * .6 ) ) );
		imageline( $im, 0, $y, $w, $y, $c );
	}
	$white = imagecolorallocate( $im, 255, 255, 255 );
	imagestring( $im, 5, 30, $h - 40, strtoupper( $label ), $white );
	imagejpeg( $im, $up['path'] . '/' . $fn, 82 );
	imagedestroy( $im );

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$id = wp_insert_attachment( array( 'post_mime_type' => 'image/jpeg', 'post_title' => $label, 'post_status' => 'inherit' ), $up['path'] . '/' . $fn );
	if ( ! $id || is_wp_error( $id ) ) { return 0; }
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['path'] . '/' . $fn ) );
	update_post_meta( $id, 'np_demo', 1 );
	return $id;
}

add_action( 'admin_post_np_demo_import', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed', 403 ); }
	check_admin_referer( 'np_demo' );
	@set_time_limit( 300 ); // phpcs:ignore

	np_core_register_types();
	np_core_seed_districts();

	// Categories.
	$cats = array( 'state' => 'ରାଜ୍ୟ', 'national' => 'ଜାତୀୟ', 'international' => 'ଅନ୍ତର୍ଜାତୀୟ', 'politics' => 'ରାଜନୀତି', 'sports' => 'ଖେଳ', 'entertainment' => 'ମନୋରଞ୍ଜନ', 'business' => 'ବାଣିଜ୍ୟ', 'editorial' => 'ସମ୍ପାଦକୀୟ' );
	$cat_ids = array();
	foreach ( $cats as $slug => $name ) {
		$t = term_exists( $slug, 'category' );
		if ( ! $t ) { $t = wp_insert_term( $name, 'category', array( 'slug' => $slug ) ); }
		$cat_ids[ $slug ] = is_array( $t ) ? (int) $t['term_id'] : (int) get_option( 'default_category' );
	}

	// Sample stories (fictional demo text).
	$stories = array(
		array( 'state', 'ରାଜ୍ୟରେ ନୂଆ ଶିକ୍ଷା ଯୋଜନା ଆରମ୍ଭ, ହଜାର ହଜାର ଛାତ୍ରଛାତ୍ରୀ ଉପକୃତ ହେବେ', 'khordha', 'ଭୁବନେଶ୍ୱର' ),
		array( 'state', 'ମହାନଦୀ ଅବବାହିକାରେ ଜଳସ୍ତର ବୃଦ୍ଧି, ପ୍ରଶାସନ ସତର୍କ', 'cuttack', 'କଟକ' ),
		array( 'state', 'ଗ୍ରାମାଞ୍ଚଳ ରାସ୍ତା ପାଇଁ ନୂଆ ପ୍ରକଳ୍ପ ମଞ୍ଜୁର', 'ganjam', 'ବ୍ରହ୍ମପୁର' ),
		array( 'national', 'ଦେଶରେ ଡିଜିଟାଲ ଦେୟ କାରବାରରେ ରେକର୍ଡ ବୃଦ୍ଧି', '', 'ନୂଆଦିଲ୍ଲୀ' ),
		array( 'national', 'ନୂଆ ରେଳ ମାର୍ଗ ପାଇଁ ସର୍ଭେ କାର୍ଯ୍ୟ ଶେଷ', '', 'ନୂଆଦିଲ୍ଲୀ' ),
		array( 'international', 'ଜଳବାୟୁ ପରିବର୍ତ୍ତନ ନେଇ ଆନ୍ତର୍ଜାତିକ ସମ୍ମିଳନୀ ଆରମ୍ଭ', '', '' ),
		array( 'politics', 'ବିଧାନସଭା ଶୀତକାଳୀନ ଅଧିବେଶନ ପାଇଁ ପ୍ରସ୍ତୁତି ଜୋରଦାର', 'khordha', 'ଭୁବନେଶ୍ୱର' ),
		array( 'sports', 'ରାଜ୍ୟସ୍ତରୀୟ ଫୁଟବଲ ପ୍ରତିଯୋଗିତାରେ ସମ୍ବଲପୁର ଦଳ ବିଜୟୀ', 'sambalpur', 'ସମ୍ବଲପୁର' ),
		array( 'sports', 'ହକି: ଯୁବ ଖେଳାଳିଙ୍କ ପାଇଁ ନୂଆ ପ୍ରଶିକ୍ଷଣ କେନ୍ଦ୍ର', 'sundargarh', 'ରାଉରକେଲା' ),
		array( 'entertainment', 'ନୂଆ ଓଡ଼ିଆ ଚଳଚ୍ଚିତ୍ରର ଟ୍ରେଲର ମୁକ୍ତିଲାଭ, ଦର୍ଶକଙ୍କ ଭଲ ପ୍ରତିକ୍ରିୟା', '', 'ଭୁବନେଶ୍ୱର' ),
		array( 'entertainment', 'ଲୋକନୃତ୍ୟ ମହୋତ୍ସବରେ ଦେଶର ବିଭିନ୍ନ ପ୍ରାନ୍ତରୁ କଳାକାର', 'puri', 'ପୁରୀ' ),
		array( 'business', 'ଶିଳ୍ପ ନିବେଶ ସମ୍ମିଳନୀରେ ଅନେକ ପ୍ରସ୍ତାବ ଆଗତ', 'khordha', 'ଭୁବନେଶ୍ୱର' ),
		array( 'business', 'ଧାନ କିଣାବିକା ପାଇଁ ମଣ୍ଡିରେ ପ୍ରସ୍ତୁତି', 'bargarh', 'ବରଗଡ଼' ),
		array( 'editorial', 'ସମ୍ପାଦକୀୟ: ଗ୍ରାମୀଣ ସ୍ୱାସ୍ଥ୍ୟସେବାକୁ ପ୍ରାଥମିକତା ଆବଶ୍ୟକ', '', '' ),
		array( 'editorial', 'ମତାମତ: ମାତୃଭାଷାରେ ଶିକ୍ଷାର ଗୁରୁତ୍ୱ', '', '' ),
		array( 'state', 'ଉପକୂଳ ଅଞ୍ଚଳରେ ଘୂର୍ଣ୍ଣିବାତ୍ୟା ପ୍ରସ୍ତୁତି ମକ୍ ଡ୍ରିଲ', 'balasore', 'ବାଲେଶ୍ୱର' ),
	);
	$body = '<!-- wp:paragraph --><p>ଏହା ଏକ ନମୁନା ଖବର। ଏଠାରେ ଆପଣଙ୍କ ସମ୍ବାଦଦାତା ଖବରର ମୁଖ୍ୟ ବିଷୟବସ୍ତୁ ଲେଖିବେ। ପ୍ରଥମ ଅନୁଚ୍ଛେଦରେ କିଏ, କ’ଣ, କେବେ, କେଉଁଠି ଓ କାହିଁକି ସ୍ପଷ୍ଟ ହେବା ଉଚିତ।</p><!-- /wp:paragraph -->'
		. '<!-- wp:paragraph --><p>ଦ୍ୱିତୀୟ ଅନୁଚ୍ଛେଦରେ ଅଧିକ ବିବରଣୀ, ସମ୍ପୃକ୍ତ ବ୍ୟକ୍ତିଙ୍କ ପ୍ରତିକ୍ରିୟା ଓ ପୃଷ୍ଠଭୂମି ରହିବ।</p><!-- /wp:paragraph -->'
		. '<!-- wp:paragraph --><p>ତୃତୀୟ ଅନୁଚ୍ଛେଦ ପରେ ଆର୍ଟିକିଲ ଭିତର ବିଜ୍ଞାପନ ସ୍ୱୟଂଚାଳିତ ଭାବେ ଦେଖାଯିବ।</p><!-- /wp:paragraph -->'
		. '<!-- wp:paragraph --><p>ଶେଷ ଅନୁଚ୍ଛେଦରେ ଆଗାମୀ ପଦକ୍ଷେପ ବା ସମ୍ଭାବ୍ୟ ପ୍ରଭାବ ବିଷୟରେ ଉଲ୍ଲେଖ କରାଯାଇପାରେ।</p><!-- /wp:paragraph -->';
	$palette = array( array( 196, 22, 28 ), array( 20, 90, 160 ), array( 30, 130, 80 ), array( 150, 80, 20 ), array( 90, 40, 140 ) );
	$first = 0;
	foreach ( $stories as $i => $s ) {
		$id = wp_insert_post( array(
			'post_title' => $s[1], 'post_content' => $body, 'post_status' => 'publish', 'post_category' => array( $cat_ids[ $s[0] ] ),
			'post_date' => wp_date( 'Y-m-d H:i:s', time() - $i * 1800 ), 'post_excerpt' => 'ଏହା ଏକ ନମୁନା ଖବରର ସାରାଂଶ।',
		) );
		if ( ! $id || is_wp_error( $id ) ) { continue; }
		$first = $first ?: $id;
		update_post_meta( $id, 'np_demo', 1 );
		update_post_meta( $id, 'np_kicker', 'ନମୁନା ଉପଶୀର୍ଷକ – ଖବରର ମୁଖ୍ୟ କଥା ଗୋଟିଏ ଧାଡ଼ିରେ' );
		update_post_meta( $id, 'np_dateline', $s[3] );
		update_post_meta( $id, 'np_views_1d', 100 - $i * 5 );
		update_post_meta( $id, 'np_views_7d', 500 - $i * 20 );
		update_post_meta( $id, 'np_views', 900 - $i * 30 );
		if ( $s[2] ) { wp_set_object_terms( $id, $s[2], 'np_district' ); }
		$img = np_core_demo_image( 'news-' . ( $i + 1 ), 1200, 675, $palette[ $i % 5 ] );
		if ( $img ) { set_post_thumbnail( $id, $img ); }
	}
	if ( $first ) { stick_post( $first ); }

	// Breaking.
	foreach ( array( array( 'ଭୁବନେଶ୍ୱରରେ ପ୍ରବଳ ବର୍ଷା, ଅନେକ ସ୍ଥାନ ଜଳମଗ୍ନ', true ), array( 'ରାଜ୍ୟ ମନ୍ତ୍ରିମଣ୍ଡଳ ବୈଠକରେ ୧୨ ପ୍ରସ୍ତାବକୁ ମଞ୍ଜୁରି', false ), array( 'ରେଳ ଯାତ୍ରୀଙ୍କ ପାଇଁ ବିଶେଷ ଟ୍ରେନ ଘୋଷଣା', false ) ) as $b ) {
		$id = wp_insert_post( array( 'post_type' => 'np_breaking', 'post_title' => $b[0], 'post_status' => 'publish' ) );
		if ( $id && ! is_wp_error( $id ) ) { update_post_meta( $id, 'np_demo', 1 ); np_core_save_breaking_meta( $id, $b[1], wp_date( 'Y-m-d H:i', time() + 2 * DAY_IN_SECONDS ) ); }
	}

	// e-Paper edition with 4 placeholder pages.
	$pages = array();
	for ( $p = 1; $p <= 4; $p++ ) { $pages[] = np_core_demo_image( sprintf( 'page-%02d', $p ), 1240, 1754, array( 235, 235, 230 ) ); }
	$pages = array_filter( $pages );
	$ep = wp_insert_post( array( 'post_type' => 'np_epaper', 'post_title' => 'ଭୁବନେଶ୍ୱର ' . wp_date( 'Y-m-d' ), 'post_status' => 'publish' ) );
	if ( $ep && ! is_wp_error( $ep ) ) {
		update_post_meta( $ep, 'np_demo', 1 );
		update_post_meta( $ep, 'np_edition_date', wp_date( 'Y-m-d' ) );
		update_post_meta( $ep, 'np_edition_name', 'ଭୁବନେଶ୍ୱର' );
		update_post_meta( $ep, 'np_pages', implode( ',', $pages ) );
		if ( $pages ) { set_post_thumbnail( $ep, reset( $pages ) ); }
	}

	// Pages.
	$legal = '<!-- wp:paragraph --><p>' . 'ଏହି ପୃଷ୍ଠାର ବିଷୟବସ୍ତୁ ଆପଣଙ୍କ ଆଇନ ପରାମର୍ଶଦାତାଙ୍କ ସହ ପରାମର୍ଶ କରି ଅଦ୍ୟତନ କରନ୍ତୁ।' . '</p><!-- /wp:paragraph -->';
	$page_defs = array(
		'about-us'          => array( 'ଆମ ବିଷୟରେ (About Us)', $legal ),
		'contact'           => array( 'ଯୋଗାଯୋଗ (Contact)', '<!-- wp:shortcode -->[np_form type="contact"]<!-- /wp:shortcode -->' ),
		'advertise'         => array( 'ବିଜ୍ଞାପନ ଦିଅନ୍ତୁ (Advertise With Us)', '<!-- wp:table --><figure class="wp-block-table"><table><thead><tr><th>Slot</th><th>Size</th><th>Rate</th></tr></thead><tbody><tr><td>Header</td><td>728×90</td><td>₹ —</td></tr><tr><td>Sidebar</td><td>300×250</td><td>₹ —</td></tr><tr><td>In-article</td><td>Responsive</td><td>₹ —</td></tr><tr><td>e-Paper</td><td>Full / Half page</td><td>₹ —</td></tr></tbody></table></figure><!-- /wp:table --><!-- wp:shortcode -->[np_form type="advertise"]<!-- /wp:shortcode -->' ),
		'privacy-policy'    => array( 'ଗୋପନୀୟତା ନୀତି (Privacy Policy)', $legal ),
		'terms'             => array( 'ସର୍ତ୍ତାବଳୀ (Terms)', $legal ),
		'grievance-officer' => array( 'ଅଭିଯୋଗ ଅଧିକାରୀ ଓ ସମ୍ପାଦକୀୟ ନୀତି (Grievance Officer & Editorial Policy)', '<!-- wp:paragraph --><p>Grievance Officer: [Name], [Email], [Phone], [Address]. Complaints are acknowledged within 24 hours and disposed of within 15 days, as required under the IT (Intermediary Guidelines and Digital Media Ethics Code) Rules, 2021.</p><!-- /wp:paragraph -->' . $legal ),
		'epaper'            => array( 'e-Paper', '<!-- wp:shortcode -->[np_epaper]<!-- /wp:shortcode -->' ),
	);
	$page_ids = array();
	foreach ( $page_defs as $slug => $d ) {
		$existing = get_page_by_path( $slug );
		$page_ids[ $slug ] = $existing ? $existing->ID : wp_insert_post( array( 'post_type' => 'page', 'post_name' => $slug, 'post_title' => $d[0], 'post_content' => $d[1], 'post_status' => 'publish' ) );
		if ( ! $existing ) { update_post_meta( $page_ids[ $slug ], 'np_demo', 1 ); }
	}
	set_theme_mod( 'np_epaper_url', get_permalink( $page_ids['epaper'] ) );

	// Menus.
	if ( ! wp_get_nav_menu_object( 'Main Menu' ) ) {
		$m = wp_create_nav_menu( 'Main Menu' );
		wp_update_nav_menu_item( $m, 0, array( 'menu-item-title' => 'ମୁଖ୍ୟ ଖବର', 'menu-item-url' => home_url( '/' ), 'menu-item-status' => 'publish' ) );
		foreach ( array( 'state', 'national', 'international' ) as $s ) {
			wp_update_nav_menu_item( $m, 0, array( 'menu-item-object' => 'category', 'menu-item-object-id' => $cat_ids[ $s ], 'menu-item-type' => 'taxonomy', 'menu-item-status' => 'publish' ) );
		}
		wp_update_nav_menu_item( $m, 0, array( 'menu-item-title' => 'ଜିଲ୍ଲା', 'menu-item-url' => '#', 'menu-item-classes' => 'np-districts', 'menu-item-status' => 'publish' ) );
		foreach ( array( 'politics', 'sports', 'entertainment', 'business', 'editorial' ) as $s ) {
			wp_update_nav_menu_item( $m, 0, array( 'menu-item-object' => 'category', 'menu-item-object-id' => $cat_ids[ $s ], 'menu-item-type' => 'taxonomy', 'menu-item-status' => 'publish' ) );
		}
		wp_update_nav_menu_item( $m, 0, array( 'menu-item-title' => 'ଫଟୋ/ଭିଡିଓ', 'menu-item-url' => home_url( '/videos/' ), 'menu-item-status' => 'publish' ) );
		wp_update_nav_menu_item( $m, 0, array( 'menu-item-title' => 'e-Paper', 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['epaper'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
		$f = wp_create_nav_menu( 'Footer Menu' );
		foreach ( array( 'about-us', 'contact', 'advertise', 'privacy-policy', 'terms', 'grievance-officer' ) as $s ) {
			wp_update_nav_menu_item( $f, 0, array( 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids[ $s ], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
		}
		$loc = get_theme_mod( 'nav_menu_locations', array() );
		$loc['primary'] = $m;
		$loc['footer']  = $f;
		set_theme_mod( 'nav_menu_locations', $loc );
	}

	update_option( 'show_on_front', 'posts' );
	flush_rewrite_rules();
	wp_safe_redirect( admin_url( 'tools.php?page=np-demo&np_msg=' . rawurlencode( 'Demo content imported. Visit your homepage!' ) ) );
	exit;
} );

add_action( 'admin_post_np_demo_remove', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed', 403 ); }
	check_admin_referer( 'np_demo' );
	$ids = get_posts( array( 'post_type' => 'any', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => 'np_demo', 'meta_value' => 1 ) );
	$att = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => 'np_demo', 'meta_value' => 1 ) );
	foreach ( $ids as $id ) { wp_delete_post( $id, true ); }
	foreach ( $att as $id ) { wp_delete_attachment( $id, true ); }
	wp_safe_redirect( admin_url( 'tools.php?page=np-demo&np_msg=' . rawurlencode( 'Demo content removed.' ) ) );
	exit;
} );
