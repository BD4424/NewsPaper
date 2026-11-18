<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'newspaper-name-news' ); ?></a>

<?php get_template_part( 'template-parts/header/topbar' ); ?>

<header class="np-header" role="banner">
	<div class="np-container np-header__inner">
		<div class="np-brand">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<a class="np-brand__name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
				<?php if ( get_bloginfo( 'description' ) ) : ?><p class="np-brand__tag"><?php bloginfo( 'description' ); ?></p><?php endif; ?>
			<?php endif; ?>
		</div>
		<div class="np-header__ad"><?php np_ad( 'header' ); ?></div>
	</div>
</header>

<nav class="np-nav" id="np-nav" aria-label="<?php esc_attr_e( 'Main menu', 'newspaper-name-news' ); ?>">
	<div class="np-container np-nav__inner">
		<button type="button" class="np-burger" aria-controls="np-menu" aria-expanded="false"><span></span><span class="screen-reader-text"><?php esc_html_e( 'Menu', 'newspaper-name-news' ); ?></span></button>
		<div class="np-menu" id="np-menu">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_id' => 'primary-menu', 'fallback_cb' => 'np_fallback_menu', 'depth' => 2 ) ); ?>
		</div>
		<button type="button" class="np-search-toggle" aria-controls="np-search" aria-expanded="false" aria-label="<?php esc_attr_e( 'Search', 'newspaper-name-news' ); ?>"><?php echo np_icon( 'search' ); // phpcs:ignore ?></button>
	</div>
	<div class="np-search" id="np-search" hidden><div class="np-container"><?php get_search_form(); ?></div></div>
</nav>

<?php get_template_part( 'template-parts/header/ticker' ); ?>

<main id="content" class="np-main" tabindex="-1">
