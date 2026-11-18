<?php
/** Generic category block: 1 big + 4 small. $args['key'] maps to Customizer slug option. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$slug = np_opt( 'cat_' . ( $args['key'] ?? '' ) );
$cat  = $slug ? get_category_by_slug( $slug ) : null;
if ( ! $cat ) { return; }
$q = new WP_Query( array( 'cat' => $cat->term_id, 'posts_per_page' => 5, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
if ( ! $q->have_posts() ) { return; }
?>
<section class="np-section np-catblock">
	<div class="np-container">
		<div class="np-section__head">
			<h2 class="np-section__title"><a href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></h2>
			<a class="np-more" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php esc_html_e( 'More', 'newspaper-name-news' ); ?> →</a>
		</div>
		<div class="np-catblock__grid">
			<?php $n = 0; while ( $q->have_posts() ) : $q->the_post(); np_card( 0 === $n ? 'card' : 'list', 0 === $n ); $n++; endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
