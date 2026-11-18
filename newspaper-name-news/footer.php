<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
</main>

<footer class="np-footer" role="contentinfo">
	<div class="np-container np-footer__grid">
		<div class="np-footer__col">
			<p class="np-footer__name"><?php bloginfo( 'name' ); ?></p>
			<?php if ( np_opt( 'footer_about' ) ) : ?><p><?php echo nl2br( esc_html( np_opt( 'footer_about' ) ) ); ?></p><?php endif; ?>
			<?php np_social_links(); ?>
		</div>
		<div class="np-footer__col">
			<h2 class="np-footer__title"><?php esc_html_e( 'Quick Links', 'newspaper-name-news' ); ?></h2>
			<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1, 'fallback_cb' => false ) ); ?>
			<?php dynamic_sidebar( 'footer-1' ); ?>
		</div>
		<div class="np-footer__col">
			<h2 class="np-footer__title"><?php esc_html_e( 'Categories', 'newspaper-name-news' ); ?></h2>
			<ul><?php wp_list_categories( array( 'title_li' => '', 'depth' => 1, 'number' => 12, 'orderby' => 'count', 'order' => 'DESC' ) ); ?></ul>
			<?php dynamic_sidebar( 'footer-2' ); ?>
		</div>
		<div class="np-footer__col">
			<h2 class="np-footer__title"><?php esc_html_e( 'Registered Office', 'newspaper-name-news' ); ?></h2>
			<?php if ( np_opt( 'footer_address' ) ) : ?><address><?php echo nl2br( esc_html( np_opt( 'footer_address' ) ) ); ?></address><?php endif; ?>
			<?php if ( np_opt( 'footer_rni' ) ) : ?><p><?php esc_html_e( 'RNI No.:', 'newspaper-name-news' ); ?> <?php echo esc_html( np_opt( 'footer_rni' ) ); ?></p><?php endif; ?>
		</div>
	</div>
	<div class="np-footer__bottom">
		<div class="np-container">
			<?php echo esc_html( np_opt( 'footer_copyright' ) ?: '© ' . wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. ' . __( 'All rights reserved.', 'newspaper-name-news' ) ); ?>
		</div>
	</div>
</footer>

<?php np_ad( 'mobile_sticky' ); ?>

<nav class="np-bottom-nav" aria-label="<?php esc_attr_e( 'Quick navigation', 'newspaper-name-news' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo np_icon( 'home' ); // phpcs:ignore ?><span><?php esc_html_e( 'Home', 'newspaper-name-news' ); ?></span></a>
	<a href="<?php echo esc_url( np_opt( 'epaper_url' ) ); ?>"><?php echo np_icon( 'paper' ); // phpcs:ignore ?><span><?php esc_html_e( 'e-Paper', 'newspaper-name-news' ); ?></span></a>
	<a href="<?php echo esc_url( post_type_exists( 'np_breaking' ) ? get_post_type_archive_link( 'np_breaking' ) : home_url( '/' ) ); ?>"><?php echo np_icon( 'bolt' ); // phpcs:ignore ?><span><?php esc_html_e( 'Breaking', 'newspaper-name-news' ); ?></span></a>
	<a href="<?php echo esc_url( post_type_exists( 'np_video' ) ? get_post_type_archive_link( 'np_video' ) : home_url( '/' ) ); ?>"><?php echo np_icon( 'play' ); // phpcs:ignore ?><span><?php esc_html_e( 'Videos', 'newspaper-name-news' ); ?></span></a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
