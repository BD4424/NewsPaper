<?php
/**
 * Homepage – sections rendered in the order set in
 * Customizer → Newspaper Options → Homepage Sections.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

get_template_part( 'template-parts/home/flash' );

$np_sections = array_filter( array_map( 'trim', explode( ',', (string) np_opt( 'home_sections' ) ) ) );
$np_category_blocks = array( 'state', 'national', 'sports', 'entertainment', 'business' );
$np_i = 0;

foreach ( $np_sections as $np_section ) {
	$np_section = sanitize_key( $np_section );
	if ( in_array( $np_section, $np_category_blocks, true ) ) {
		get_template_part( 'template-parts/home/category-block', null, array( 'key' => $np_section ) );
	} elseif ( locate_template( "template-parts/home/{$np_section}.php" ) ) {
		get_template_part( 'template-parts/home/' . $np_section );
	} else {
		continue;
	}
	// Between-sections ad after every 2nd section.
	if ( ++$np_i % 2 === 0 ) {
		echo '<div class="np-container">';
		np_ad( 'between' );
		echo '</div>';
	}
}

get_footer();
