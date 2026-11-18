<?php if ( ! defined( 'ABSPATH' ) ) { exit; } get_header(); ?>
<div class="np-container np-page np-404">
	<h1 class="np-article__title"><?php esc_html_e( 'Page not found', 'newspaper-name-news' ); ?></h1>
	<p><?php esc_html_e( 'The page you are looking for may have moved. Try searching:', 'newspaper-name-news' ); ?></p>
	<?php get_search_form(); ?>
	<p><a class="np-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'newspaper-name-news' ); ?></a></p>
</div>
<?php get_footer();
