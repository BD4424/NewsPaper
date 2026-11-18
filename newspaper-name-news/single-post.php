<?php
/** Single article. Additional stories are appended below via "next story" infinite scroll. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<div class="np-container np-layout np-single">
	<div class="np-layout__main" id="np-articles">
		<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content/article' ); endwhile; ?>
	</div>
	<aside class="np-layout__side"><?php get_template_part( 'template-parts/content/most-read' ); np_ad( 'sidebar' ); dynamic_sidebar( 'sidebar-1' ); ?></aside>
</div>
<div id="np-next-sentinel" aria-hidden="true"></div>
<?php get_footer();
