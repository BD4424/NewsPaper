<?php
/** Hero: 1 lead story + 4 side stories. Lead = newest sticky post ("Stick to top") if any, else newest post. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$sticky = array_map( 'absint', (array) get_option( 'sticky_posts' ) );
$lead   = $sticky ? get_posts( array( 'post__in' => $sticky, 'posts_per_page' => 1 ) ) : array();
$lead_id = $lead ? $lead[0]->ID : 0;
$q = new WP_Query( array( 'posts_per_page' => $lead_id ? 4 : 5, 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'post__not_in' => $lead_id ? array( $lead_id ) : array() ) );
$ids = wp_list_pluck( $q->posts, 'ID' );
if ( $lead_id ) { array_unshift( $ids, $lead_id ); }
if ( ! $ids ) { return; }
global $post;
?>
<section class="np-section np-hero">
	<div class="np-container np-hero__grid">
		<div class="np-hero__lead">
			<?php $post = get_post( $ids[0] ); setup_postdata( $post ); np_card( 'hero', true ); // phpcs:ignore ?>
		</div>
		<div class="np-hero__side">
			<?php foreach ( array_slice( $ids, 1, 4 ) as $id ) { $post = get_post( $id ); setup_postdata( $post ); np_card( 'card' ); } // phpcs:ignore ?>
		</div>
	</div>
</section>
<?php wp_reset_postdata();
