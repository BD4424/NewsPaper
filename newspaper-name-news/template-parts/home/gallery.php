<?php
/** Photo gallery slider (horizontal scroll-snap, no JS library). */
if ( ! defined( 'ABSPATH' ) || ! post_type_exists( 'np_gallery' ) ) { return; }
$q = new WP_Query( array( 'post_type' => 'np_gallery', 'posts_per_page' => 8, 'no_found_rows' => true ) );
if ( ! $q->have_posts() ) { return; }
?>
<section class="np-section np-dark-band">
	<div class="np-container">
		<h2 class="np-section__title"><?php esc_html_e( 'Photo Gallery', 'newspaper-name-news' ); ?></h2>
		<div class="np-slider" tabindex="0" aria-label="<?php esc_attr_e( 'Photo galleries', 'newspaper-name-news' ); ?>">
			<?php while ( $q->have_posts() ) : $q->the_post(); np_card( 'card' ); endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
