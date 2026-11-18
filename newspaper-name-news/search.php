<?php if ( ! defined( 'ABSPATH' ) ) { exit; } get_header(); ?>
<div class="np-container np-layout">
	<div class="np-layout__main">
		<h1 class="np-section__title"><?php printf( esc_html__( 'Search results for: %s', 'newspaper-name-news' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); ?></h1>
		<?php get_search_form(); ?>
		<?php if ( have_posts() ) : ?>
			<div class="np-list"><?php while ( have_posts() ) : the_post(); np_card( 'list', true ); endwhile; ?></div>
			<?php the_posts_pagination(); ?>
		<?php else : get_template_part( 'template-parts/content/none' ); endif; ?>
	</div>
	<aside class="np-layout__side"><?php get_template_part( 'template-parts/content/most-read' ); ?></aside>
</div>
<?php get_footer();
