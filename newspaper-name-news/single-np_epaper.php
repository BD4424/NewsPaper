<?php
/** e-Paper edition viewer. Viewer markup comes from Newspaper Core ([np_epaper_viewer]). */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<div class="np-container np-epaper-page">
	<?php while ( have_posts() ) : the_post();
		echo do_shortcode( '[np_epaper_viewer id="' . absint( get_the_ID() ) . '"]' );
	endwhile; ?>
</div>
<?php get_footer();
