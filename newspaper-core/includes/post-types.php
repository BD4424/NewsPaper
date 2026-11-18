<?php
/** Custom post types + Districts taxonomy. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function np_core_register_types(): void {
	$labels = function ( $plural, $singular ) {
		return array( 'name' => $plural, 'singular_name' => $singular, 'add_new_item' => sprintf( __( 'Add %s', 'newspaper-core' ), $singular ), 'edit_item' => sprintf( __( 'Edit %s', 'newspaper-core' ), $singular ), 'all_items' => sprintf( __( 'All %s', 'newspaper-core' ), $plural ), 'menu_name' => $plural );
	};

	register_post_type( 'np_breaking', array(
		'labels' => $labels( __( 'Breaking News', 'newspaper-core' ), __( 'Breaking item', 'newspaper-core' ) ),
		'public' => true, 'show_in_rest' => true, 'has_archive' => 'breaking', 'rewrite' => array( 'slug' => 'breaking-news' ),
		'menu_icon' => 'dashicons-megaphone', 'menu_position' => 5, 'supports' => array( 'title', 'editor', 'thumbnail', 'author' ),
	) );

	register_post_type( 'np_epaper', array(
		'labels' => $labels( __( 'e-Paper', 'newspaper-core' ), __( 'e-Paper Edition', 'newspaper-core' ) ),
		'public' => true, 'show_in_rest' => false, // classic screen: the page uploader works best there.
		'has_archive' => false, 'rewrite' => array( 'slug' => 'epaper-edition' ),
		'menu_icon' => 'dashicons-media-document', 'menu_position' => 6, 'supports' => array( 'title', 'thumbnail' ),
		'capability_type' => array( 'np_epaper', 'np_epapers' ), 'map_meta_cap' => true,
	) );

	register_post_type( 'np_gallery', array(
		'labels' => $labels( __( 'Photo Galleries', 'newspaper-core' ), __( 'Photo Gallery', 'newspaper-core' ) ),
		'public' => true, 'show_in_rest' => true, 'has_archive' => 'photos', 'rewrite' => array( 'slug' => 'photo' ),
		'menu_icon' => 'dashicons-format-gallery', 'menu_position' => 7, 'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
	) );

	register_post_type( 'np_video', array(
		'labels' => $labels( __( 'Videos', 'newspaper-core' ), __( 'Video', 'newspaper-core' ) ),
		'public' => true, 'show_in_rest' => true, 'has_archive' => 'videos', 'rewrite' => array( 'slug' => 'video' ),
		'menu_icon' => 'dashicons-video-alt3', 'menu_position' => 8, 'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
	) );

	register_taxonomy( 'np_district', array( 'post', 'np_gallery', 'np_video' ), array(
		'labels' => array( 'name' => __( 'Districts', 'newspaper-core' ), 'singular_name' => __( 'District', 'newspaper-core' ) ),
		'public' => true, 'hierarchical' => true, 'show_in_rest' => true, 'show_admin_column' => true,
		'rewrite' => array( 'slug' => 'district' ),
	) );
}
add_action( 'init', 'np_core_register_types' );

/** The 30 districts of Odisha (Odia name => English slug). Created on activation, never duplicated. */
function np_core_districts(): array {
	return array(
		'ଅନୁଗୋଳ' => 'angul', 'ବଲାଙ୍ଗୀର' => 'balangir', 'ବାଲେଶ୍ୱର' => 'balasore', 'ବରଗଡ଼' => 'bargarh', 'ଭଦ୍ରକ' => 'bhadrak',
		'ବୌଦ୍ଧ' => 'boudh', 'କଟକ' => 'cuttack', 'ଦେବଗଡ଼' => 'deogarh', 'ଢେଙ୍କାନାଳ' => 'dhenkanal', 'ଗଜପତି' => 'gajapati',
		'ଗଞ୍ଜାମ' => 'ganjam', 'ଜଗତସିଂହପୁର' => 'jagatsinghpur', 'ଯାଜପୁର' => 'jajpur', 'ଝାରସୁଗୁଡ଼ା' => 'jharsuguda', 'କଳାହାଣ୍ଡି' => 'kalahandi',
		'କନ୍ଧମାଳ' => 'kandhamal', 'କେନ୍ଦ୍ରାପଡ଼ା' => 'kendrapara', 'କେନ୍ଦୁଝର' => 'keonjhar', 'ଖୋର୍ଦ୍ଧା' => 'khordha', 'କୋରାପୁଟ' => 'koraput',
		'ମାଲକାନଗିରି' => 'malkangiri', 'ମୟୂରଭଞ୍ଜ' => 'mayurbhanj', 'ନବରଙ୍ଗପୁର' => 'nabarangpur', 'ନୟାଗଡ଼' => 'nayagarh', 'ନୂଆପଡ଼ା' => 'nuapada',
		'ପୁରୀ' => 'puri', 'ରାୟଗଡ଼ା' => 'rayagada', 'ସମ୍ବଲପୁର' => 'sambalpur', 'ସୁବର୍ଣ୍ଣପୁର' => 'subarnapur', 'ସୁନ୍ଦରଗଡ଼' => 'sundargarh',
	);
}

function np_core_seed_districts(): void {
	foreach ( np_core_districts() as $name => $slug ) {
		if ( ! term_exists( $slug, 'np_district' ) ) {
			wp_insert_term( $name, 'np_district', array( 'slug' => $slug ) );
		}
	}
}

/** Extract a YouTube video ID from the video's URL field. */
function np_core_youtube_id( int $post_id ): string {
	$url = (string) get_post_meta( $post_id, 'np_youtube', true );
	if ( preg_match( '~(?:youtu\.be/|v=|embed/|shorts/|live/)([A-Za-z0-9_-]{11})~', $url, $m ) ) { return $m[1]; }
	return preg_match( '~^[A-Za-z0-9_-]{11}$~', $url ) ? $url : '';
}
