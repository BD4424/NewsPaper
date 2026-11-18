<?php
/** Reusable template helpers. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Kicker (sub-headline) saved by Newspaper Core. */
function np_kicker( ?int $id = null ): string {
	return (string) get_post_meta( $id ?? get_the_ID(), 'np_kicker', true );
}

/** Reading time in Odia minutes (Odia ≈ 150 words/min). */
function np_reading_time( ?int $id = null ): string {
	$text  = wp_strip_all_tags( get_post_field( 'post_content', $id ?? get_the_ID() ) );
	$words = count( preg_split( '/\s+/u', trim( $text ) ) ?: array() );
	return np_odia_digits( max( 1, (int) ceil( $words / 150 ) ) ) . ' ' . __( 'min read', 'newspaper-name-news' );
}

/** Primary category link (first category). */
function np_primary_cat( ?int $id = null ): string {
	$cats = get_the_category( $id ?? get_the_ID() );
	if ( ! $cats ) { return ''; }
	return '<a class="np-cat" href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
}

/** "Sponsored" label when the post is marked sponsored. */
function np_sponsored_label( ?int $id = null ): string {
	return get_post_meta( $id ?? get_the_ID(), 'np_sponsored', true ) ? '<span class="np-sponsored">' . esc_html__( 'Sponsored', 'newspaper-name-news' ) . '</span>' : '';
}

/** Card markup used everywhere (sizes: hero, card, list). */
function np_card( string $size = 'card', bool $excerpt = false ): void {
	$img = 'list' === $size ? 'np-thumb' : ( 'hero' === $size ? 'np-hero' : 'np-card' ); ?>
	<article <?php post_class( 'np-card np-card--' . $size ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<a class="np-card__img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( $img, array( 'loading' => 'hero' === $size ? 'eager' : 'lazy', 'alt' => esc_attr( get_the_title() ) ) ); ?></a>
		<?php endif; ?>
		<div class="np-card__body">
			<?php if ( 'list' !== $size ) { echo np_primary_cat() . np_sponsored_label(); } // phpcs:ignore ?>
			<h3 class="np-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<?php if ( $excerpt ) : ?><p class="np-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<time class="np-card__time" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( np_odia_time_ago( get_post_time( 'U', true ) ) ); ?></time>
		</div>
	</article>
	<?php
}

/** Social profile icons (inline SVG – no icon font). */
function np_icon( string $name ): string {
	$p = array(
		'facebook'  => 'M14 8h3V4h-3c-2.8 0-5 2.2-5 5v2H7v4h2v9h4v-9h3l1-4h-4V9c0-.6.4-1 1-1z',
		'x'         => 'M4 4l7.2 9.6L4.3 20H6l6-5.5 4.2 5.5H20l-7.5-10L19 4h-1.7l-5.5 5L8 4z',
		'youtube'   => 'M22 8s-.2-1.6-.8-2.2c-.8-.8-1.6-.8-2-.9C16.4 4.7 12 4.7 12 4.7s-4.4 0-7.2.2c-.4.1-1.2.1-2 .9C2.2 6.4 2 8 2 8s-.2 1.9-.2 3.7v1.7c0 1.9.2 3.7.2 3.7s.2 1.6.8 2.2c.8.8 1.8.8 2.2.9 1.6.2 7 .2 7 .2s4.4 0 7.2-.2c.4-.1 1.2-.1 2-.9.6-.6.8-2.2.8-2.2s.2-1.9.2-3.7v-1.7C22.2 9.9 22 8 22 8zM10 15.5v-7l6 3.5z',
		'instagram' => 'M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 5a5 5 0 100 10 5 5 0 000-10zm6-1a1 1 0 100 2 1 1 0 000-2zm-6 3a3 3 0 110 6 3 3 0 010-6z',
		'whatsapp'  => 'M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-3.3-.8-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.3 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l2 1c.3.1.5.2.5.3.1.1.1.7-.1 1.3z',
		'telegram'  => 'M21.9 4.3l-3.3 15.4c-.2 1.1-.9 1.4-1.8.9l-5-3.7-2.4 2.3c-.3.3-.5.5-1 .5l.4-5.1 9.2-8.3c.4-.4-.1-.6-.6-.2L6 13.3l-4.9-1.5c-1.1-.3-1.1-1.1.2-1.6L20.5 2.8c.9-.3 1.7.2 1.4 1.5z',
		'link'      => 'M10 14a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1m1 5a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1',
		'search'    => 'M10 2a8 8 0 016.3 12.9l5.4 5.4-1.4 1.4-5.4-5.4A8 8 0 1110 2zm0 2a6 6 0 100 12 6 6 0 000-12z',
		'moon'      => 'M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z',
		'home'      => 'M3 11l9-8 9 8v10h-6v-6H9v6H3z',
		'paper'     => 'M4 3h13l3 3v15H4zm3 5v2h10V8zm0 4v2h10v-2zm0 4v2h7v-2z',
		'bolt'      => 'M13 2L4 14h6l-1 8 9-12h-6z',
		'play'      => 'M8 5v14l11-7z',
	);
	$stroke = 'link' === $name ? ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"' : ' fill="currentColor"';
	return '<svg class="np-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path' . $stroke . ' d="' . esc_attr( $p[ $name ] ?? '' ) . '"/></svg>';
}

function np_social_links(): void {
	echo '<ul class="np-social">';
	foreach ( array( 'facebook', 'x', 'youtube', 'instagram', 'whatsapp', 'telegram' ) as $k ) {
		$url = np_opt( 'social_' . $k );
		if ( $url ) {
			printf( '<li><a href="%s" target="_blank" rel="noopener" aria-label="%s">%s</a></li>', esc_url( $url ), esc_attr( ucfirst( $k ) ), np_icon( $k ) ); // phpcs:ignore
		}
	}
	echo '</ul>';
}

/** Share buttons (WhatsApp first). */
function np_share_buttons( string $class = '' ): void {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ) );
	$links = array(
		'whatsapp' => "https://wa.me/?text={$title}%20{$url}",
		'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
		'x'        => "https://twitter.com/intent/tweet?url={$url}&text={$title}",
		'telegram' => "https://t.me/share/url?url={$url}&text={$title}",
	);
	echo '<div class="np-share ' . esc_attr( $class ) . '">';
	foreach ( $links as $k => $href ) {
		printf( '<a class="np-share__btn np-share__btn--%1$s" href="%2$s" target="_blank" rel="noopener" aria-label="%3$s">%4$s</a>', esc_attr( $k ), esc_url( $href ), esc_attr( sprintf( __( 'Share on %s', 'newspaper-name-news' ), ucfirst( $k ) ) ), np_icon( $k ) ); // phpcs:ignore
	}
	printf( '<button type="button" class="np-share__btn np-share__btn--copy" data-copy="%s" aria-label="%s">%s</button>', esc_url( get_permalink() ), esc_attr__( 'Copy link', 'newspaper-name-news' ), np_icon( 'link' ) ); // phpcs:ignore
	echo '</div>';
}

/** Districts mega-menu (terms from Newspaper Core "np_district" taxonomy). */
function np_district_mega_menu(): string {
	if ( ! taxonomy_exists( 'np_district' ) ) { return ''; }
	$terms = get_terms( array( 'taxonomy' => 'np_district', 'hide_empty' => false, 'orderby' => 'name' ) );
	if ( is_wp_error( $terms ) || ! $terms ) { return ''; }
	$out = '<div class="np-mega" role="region" aria-label="' . esc_attr__( 'Districts', 'newspaper-name-news' ) . '"><ul class="np-mega__grid">';
	foreach ( $terms as $t ) {
		$out .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a></li>';
	}
	return $out . '</ul></div>';
}

/**
 * Attach the district mega-menu to any menu item with CSS class "np-districts".
 * (Appearance → Menus → Screen Options → enable "CSS Classes".)
 */
add_filter( 'walker_nav_menu_start_el', function ( $output, $item, $depth, $args ) {
	if ( 'primary' === ( $args->theme_location ?? '' ) && in_array( 'np-districts', (array) $item->classes, true ) ) {
		$output .= np_district_mega_menu();
	}
	return $output;
}, 10, 4 );
add_filter( 'nav_menu_css_class', function ( $classes, $item ) {
	if ( in_array( 'np-districts', (array) $item->classes, true ) ) { $classes[] = 'menu-item-has-mega'; }
	return $classes;
}, 10, 2 );

/** Fallback primary menu when no menu has been assigned yet. */
function np_fallback_menu(): void {
	$items = array( 'ମୁଖ୍ୟ ଖବର' => home_url( '/' ) );
	foreach ( array( 'state' => 'ରାଜ୍ୟ', 'national' => 'ଜାତୀୟ', 'international' => 'ଅନ୍ତର୍ଜାତୀୟ', 'politics' => 'ରାଜନୀତି', 'sports' => 'ଖେଳ', 'entertainment' => 'ମନୋରଞ୍ଜନ', 'business' => 'ବାଣିଜ୍ୟ', 'editorial' => 'ସମ୍ପାଦକୀୟ' ) as $slug => $label ) {
		$cat = get_category_by_slug( $slug );
		$items[ $label ] = $cat ? get_category_link( $cat ) : home_url( '/category/' . $slug . '/' );
	}
	echo '<ul id="primary-menu" class="menu">';
	foreach ( $items as $label => $url ) {
		echo '<li class="menu-item"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	if ( taxonomy_exists( 'np_district' ) ) {
		echo '<li class="menu-item menu-item-has-mega"><a href="#">ଜିଲ୍ଲା</a>' . np_district_mega_menu() . '</li>'; // phpcs:ignore
	}
	echo '<li class="menu-item"><a href="' . esc_url( np_opt( 'epaper_url' ) ) . '">e-Paper</a></li></ul>';
}
