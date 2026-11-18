<?php
/** Latest news list (load more) + Most Read sidebar (today / 7 days). */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$q = new WP_Query( array( 'posts_per_page' => 10, 'offset' => 0, 'ignore_sticky_posts' => true ) );
?>
<section class="np-section">
	<div class="np-container np-layout">
		<div class="np-layout__main">
			<h2 class="np-section__title"><?php esc_html_e( 'Latest News', 'newspaper-name-news' ); ?></h2>
			<div class="np-list" id="np-latest">
				<?php while ( $q->have_posts() ) : $q->the_post(); np_card( 'list', true ); endwhile; wp_reset_postdata(); ?>
			</div>
			<?php if ( $q->max_num_pages > 1 ) : ?>
				<button type="button" class="np-btn np-load-more" data-target="#np-latest" data-page="2"><?php esc_html_e( 'Load more', 'newspaper-name-news' ); ?></button>
			<?php endif; ?>
		</div>
		<aside class="np-layout__side"><?php get_template_part( 'template-parts/content/most-read' ); np_ad( 'sidebar' ); ?></aside>
	</div>
</section>
