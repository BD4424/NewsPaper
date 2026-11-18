<?php
/** Related news – same category, excluding current. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$cats = wp_get_post_categories( get_the_ID() );
if ( ! $cats ) { return; }
$q = new WP_Query( array( 'category__in' => $cats, 'post__not_in' => array( get_the_ID() ), 'posts_per_page' => 4, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
if ( ! $q->have_posts() ) { return; }
?>
<section class="np-related">
	<h2 class="np-section__title"><?php esc_html_e( 'Related News', 'newspaper-name-news' ); ?></h2>
	<div class="np-grid-2"><?php while ( $q->have_posts() ) : $q->the_post(); np_card( 'list' ); endwhile; wp_reset_postdata(); ?></div>
</section>
