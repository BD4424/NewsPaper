<?php
/** Fallback for Breaking, Gallery and Video single views (e-Paper uses single-np_epaper.php). */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<div class="np-container np-page">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'np-article' ); ?>>
			<h1 class="np-article__title"><?php the_title(); ?></h1>
			<p class="np-article__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( np_odia_date( get_post_time( 'U', true ), true ) ); ?></time></p>
			<?php np_share_buttons(); ?>
			<?php if ( 'np_video' === get_post_type() && function_exists( 'np_core_youtube_id' ) && np_core_youtube_id( get_the_ID() ) ) : ?>
				<div class="np-embed"><iframe src="<?php echo esc_url( 'https://www.youtube-nocookie.com/embed/' . np_core_youtube_id( get_the_ID() ) ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen></iframe></div>
			<?php elseif ( has_post_thumbnail() ) : the_post_thumbnail( 'large' ); endif; ?>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</div>
<?php get_footer();
