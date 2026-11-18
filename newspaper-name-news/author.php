<?php
/** Reporter / author profile with photo, bio and article list. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$np_author = get_queried_object(); ?>
<div class="np-container np-layout">
	<div class="np-layout__main">
		<header class="np-author">
			<?php echo get_avatar( $np_author->ID, 120, '', esc_attr( $np_author->display_name ) ); ?>
			<div>
				<h1 class="np-author__name"><?php echo esc_html( $np_author->display_name ); ?></h1>
				<?php $np_desig = get_user_meta( $np_author->ID, 'np_designation', true ); if ( $np_desig ) : ?><p class="np-author__role"><?php echo esc_html( $np_desig ); ?></p><?php endif; ?>
				<?php if ( $np_author->description ) : ?><p><?php echo esc_html( $np_author->description ); ?></p><?php endif; ?>
			</div>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="np-list"><?php while ( have_posts() ) : the_post(); np_card( 'list', true ); endwhile; ?></div>
			<?php the_posts_pagination(); ?>
		<?php else : get_template_part( 'template-parts/content/none' ); endif; ?>
	</div>
	<aside class="np-layout__side"><?php get_template_part( 'template-parts/content/most-read' ); ?></aside>
</div>
<?php get_footer();
