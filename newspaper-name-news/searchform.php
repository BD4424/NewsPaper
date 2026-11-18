<?php if ( ! defined( 'ABSPATH' ) ) { exit; } $np_sid = wp_unique_id( 'np-s-' ); ?>
<form role="search" method="get" class="np-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>" accept-charset="UTF-8">
	<label for="<?php echo esc_attr( $np_sid ); ?>" class="screen-reader-text"><?php esc_html_e( 'Search', 'newspaper-name-news' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $np_sid ); ?>" name="s" lang="or" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search news… (ଖବର ଖୋଜନ୍ତୁ)', 'newspaper-name-news' ); ?>">
	<button type="submit"><?php esc_html_e( 'Search', 'newspaper-name-news' ); ?></button>
</form>
