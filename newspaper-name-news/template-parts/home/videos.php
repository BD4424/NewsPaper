<?php
/** Video section – YouTube via lite facade (iframe loads only on click). */
if ( ! defined( 'ABSPATH' ) || ! post_type_exists( 'np_video' ) ) { return; }
$q = new WP_Query( array( 'post_type' => 'np_video', 'posts_per_page' => 4, 'no_found_rows' => true ) );
if ( ! $q->have_posts() ) { return; }
?>
<section class="np-section">
	<div class="np-container">
		<h2 class="np-section__title"><?php esc_html_e( 'Videos', 'newspaper-name-news' ); ?></h2>
		<div class="np-grid-4">
			<?php while ( $q->have_posts() ) : $q->the_post(); $yt = function_exists( 'np_core_youtube_id' ) ? np_core_youtube_id( get_the_ID() ) : ''; ?>
				<article class="np-video">
					<?php if ( $yt ) : ?>
						<button type="button" class="np-yt" data-yt="<?php echo esc_attr( $yt ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Play: %s', 'newspaper-name-news' ), get_the_title() ) ); ?>">
							<img src="<?php echo esc_url( "https://i.ytimg.com/vi/{$yt}/hqdefault.jpg" ); ?>" alt="" loading="lazy" width="480" height="360"><?php echo np_icon( 'play' ); // phpcs:ignore ?>
						</button>
					<?php endif; ?>
					<h3 class="np-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
