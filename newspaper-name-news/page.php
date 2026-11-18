<?php if ( ! defined( 'ABSPATH' ) ) { exit; } get_header(); ?>
<div class="np-container np-page">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?>>
			<h1 class="np-article__title"><?php the_title(); ?></h1>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</div>
<?php get_footer();
