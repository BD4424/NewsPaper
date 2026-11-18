<?php
/** Editorial / Opinion with author photos. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$cat = get_category_by_slug( np_opt( 'cat_editorial' ) );
if ( ! $cat ) { return; }
$q = new WP_Query( array( 'cat' => $cat->term_id, 'posts_per_page' => 4, 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
if ( ! $q->have_posts() ) { return; }
?>
<section class="np-section np-editorial">
	<div class="np-container">
		<h2 class="np-section__title"><?php echo esc_html( $cat->name ); ?></h2>
		<div class="np-grid-4">
			<?php while ( $q->have_posts() ) : $q->the_post(); ?>
				<article class="np-opinion">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 72, '', esc_attr( get_the_author() ), array( 'loading' => 'lazy' ) ); ?>
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p class="np-opinion__author"><?php the_author_posts_link(); ?></p>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
