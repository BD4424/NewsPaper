<?php
/** Today's (latest) e-Paper front page. */
if ( ! defined( 'ABSPATH' ) || ! post_type_exists( 'np_epaper' ) ) { return; }
$ed = get_posts( array( 'post_type' => 'np_epaper', 'posts_per_page' => 1, 'meta_key' => 'np_edition_date', 'orderby' => 'meta_value', 'order' => 'DESC' ) );
if ( ! $ed ) { return; }
$ed    = $ed[0];
$pages = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $ed->ID, 'np_pages', true ) ) ) );
$cover = $pages ? wp_get_attachment_image( $pages[0], 'medium_large', false, array( 'loading' => 'lazy', 'alt' => esc_attr( get_the_title( $ed ) ) ) ) : get_the_post_thumbnail( $ed, 'medium_large' );
$date  = get_post_meta( $ed->ID, 'np_edition_date', true );
?>
<section class="np-section np-epaper-promo">
	<div class="np-container np-epaper-promo__inner">
		<a class="np-epaper-promo__cover" href="<?php echo esc_url( get_permalink( $ed ) ); ?>"><?php echo $cover; // phpcs:ignore ?></a>
		<div>
			<h2 class="np-section__title"><?php esc_html_e( "Today's e-Paper", 'newspaper-name-news' ); ?></h2>
			<p><?php echo esc_html( $date ? np_odia_date( strtotime( $date ) ) : '' ); ?> · <?php echo esc_html( get_post_meta( $ed->ID, 'np_edition_name', true ) ); ?></p>
			<a class="np-btn" href="<?php echo esc_url( get_permalink( $ed ) ); ?>"><?php esc_html_e( 'Read e-Paper', 'newspaper-name-news' ); ?></a>
			<a class="np-btn np-btn--ghost" href="<?php echo esc_url( np_opt( 'epaper_url' ) ); ?>"><?php esc_html_e( 'Past editions', 'newspaper-name-news' ); ?></a>
		</div>
	</div>
</section>
