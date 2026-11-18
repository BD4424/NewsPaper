<?php
/**
 * NewsArticle JSON-LD + Open Graph/Twitter cards.
 * Automatically steps aside when Yoast SEO or Rank Math is active (they output their own).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function np_seo_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}

add_action( 'wp_head', function () {
	if ( ! is_singular( 'post' ) ) { return; }
	$id    = get_queried_object_id();
	$img   = get_the_post_thumbnail_url( $id, 'full' );
	$desc  = np_kicker( $id ) ?: wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 30, '…' );
	$title = get_the_title( $id );

	if ( ! np_seo_plugin_active() ) {
		$tags = array(
			'og:type' => 'article', 'og:title' => $title, 'og:description' => $desc, 'og:url' => get_permalink( $id ),
			'og:site_name' => get_bloginfo( 'name' ), 'og:locale' => 'or_IN', 'og:image' => $img,
			'article:published_time' => get_the_date( 'c', $id ), 'article:modified_time' => get_the_modified_date( 'c', $id ),
		);
		foreach ( $tags as $p => $v ) {
			if ( $v ) { printf( "<meta property=\"%s\" content=\"%s\">\n", esc_attr( $p ), esc_attr( $v ) ); }
		}
		printf( "<meta name=\"twitter:card\" content=\"summary_large_image\"><meta name=\"twitter:title\" content=\"%s\"><meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $title ), esc_attr( $desc ) );
		if ( $img ) { printf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_url( $img ) ); }
		printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
	}

	// NewsArticle schema (Rank Math/Yoast add Article; NewsArticle is still useful – skip if Rank Math handles news schema).
	if ( defined( 'RANK_MATH_VERSION' ) ) { return; }
	$author = get_post_field( 'post_author', $id );
	$logo   = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
	$schema = array(
		'@context' => 'https://schema.org', '@type' => 'NewsArticle',
		'mainEntityOfPage' => get_permalink( $id ), 'headline' => wp_strip_all_tags( $title ), 'description' => $desc,
		'inLanguage' => 'or', 'datePublished' => get_the_date( 'c', $id ), 'dateModified' => get_the_modified_date( 'c', $id ),
		'author' => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $author ), 'url' => get_author_posts_url( $author ) ),
		'publisher' => array( '@type' => 'NewsMediaOrganization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ),
	);
	if ( $logo ) { $schema['publisher']['logo'] = array( '@type' => 'ImageObject', 'url' => $logo ); }
	if ( $img ) { $schema['image'] = array( $img ); }
	echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $schema ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
}, 5 );
