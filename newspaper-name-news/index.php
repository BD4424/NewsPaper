<?php
/** Category, tag, district, date and CPT archives. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<div class="np-container np-layout">
	<div class="np-layout__main">
		<header class="np-archive-head">
			<h1 class="np-section__title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
			<?php the_archive_description( '<div class="np-archive-desc">', '</div>' ); ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="np-list"><?php while ( have_posts() ) : the_post(); np_card( 'list', true ); endwhile; ?></div>
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php else : get_template_part( 'template-parts/content/none' ); endif; ?>
	</div>
	<aside class="np-layout__side"><?php get_template_part( 'template-parts/content/most-read' ); np_ad( 'sidebar' ); dynamic_sidebar( 'sidebar-1' ); ?></aside>
</div>
<?php get_footer();
