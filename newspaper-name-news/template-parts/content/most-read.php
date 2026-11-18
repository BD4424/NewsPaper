<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="np-mostread" data-tabs>
	<h2 class="np-section__title"><?php esc_html_e( 'Most Read', 'newspaper-name-news' ); ?></h2>
	<div class="np-tabs" role="tablist">
		<button type="button" role="tab" aria-selected="true" aria-controls="np-mr-1d" id="np-tab-1d"><?php esc_html_e( 'Today', 'newspaper-name-news' ); ?></button>
		<button type="button" role="tab" aria-selected="false" aria-controls="np-mr-7d" id="np-tab-7d" tabindex="-1"><?php esc_html_e( '7 days', 'newspaper-name-news' ); ?></button>
	</div>
	<?php foreach ( array( '1d', '7d' ) as $r ) : $mr = np_most_read( $r, 6 ); ?>
		<ol class="np-mostread__list" id="np-mr-<?php echo esc_attr( $r ); ?>" role="tabpanel" aria-labelledby="np-tab-<?php echo esc_attr( $r ); ?>" <?php echo '7d' === $r ? 'hidden' : ''; ?>>
			<?php while ( $mr->have_posts() ) : $mr->the_post(); ?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
			<?php endwhile; wp_reset_postdata(); ?>
		</ol>
	<?php endforeach; ?>
</div>
