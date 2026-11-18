<?php
/** Ad manager – renders a slot configured in Customizer → Newspaper Options → Ad Manager. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function np_ad( string $slot ): void {
	if ( ! get_theme_mod( "np_ad_{$slot}_on", false ) ) { return; }
	$device = get_theme_mod( "np_ad_{$slot}_device", 'all' );
	$code   = get_theme_mod( "np_ad_{$slot}_code", '' );
	$img    = get_theme_mod( "np_ad_{$slot}_img", '' );
	$link   = get_theme_mod( "np_ad_{$slot}_link", '' );
	if ( ! $code && ! $img ) { return; }

	echo '<div class="np-ad np-ad--' . esc_attr( $slot ) . ' np-ad--' . esc_attr( $device ) . '"><span class="np-ad__label">' . esc_html__( 'Advertisement', 'newspaper-name-news' ) . '</span>';
	if ( $code ) {
		echo $code; // phpcs:ignore -- sanitized on save (np_sanitize_code), admin-only.
	} else {
		$tag = '<img src="' . esc_url( $img ) . '" alt="' . esc_attr__( 'Advertisement', 'newspaper-name-news' ) . '" loading="lazy">';
		echo $link ? '<a href="' . esc_url( $link ) . '" target="_blank" rel="sponsored noopener">' . $tag . '</a>' : $tag; // phpcs:ignore
	}
	echo '</div>';
}

/** Insert the in-article ad after paragraph 3. */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) { return $content; }
	ob_start();
	np_ad( 'in_article' );
	$ad = ob_get_clean();
	if ( '' === $ad ) { return $content; }
	return np_insert_after_paragraph( $content, $ad, 3 );
}, 20 );

/** Insert HTML after the Nth closing </p>; append at the end if there are fewer paragraphs. */
function np_insert_after_paragraph( string $content, string $insert, int $n ): string {
	$parts = explode( '</p>', $content );
	if ( count( $parts ) <= $n ) { return $content . $insert; }
	$parts[ $n - 1 ] .= '</p>' . $insert;
	$head = implode( '</p>', array_slice( $parts, 0, $n ) );
	$tail = implode( '</p>', array_slice( $parts, $n ) );
	// $head already ends with "</p>$insert"; join remaining parts without re-adding that separator.
	return $head . $tail;
}
