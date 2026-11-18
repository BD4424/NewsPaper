<?php
/** Districts block with a district dropdown filter (navigates to district archive). */
if ( ! defined( 'ABSPATH' ) || ! taxonomy_exists( 'np_district' ) ) { return; }
$q = new WP_Query( array( 'posts_per_page' => 6, 'no_found_rows' => true, 'ignore_sticky_posts' => true,
	'tax_query' => array( array( 'taxonomy' => 'np_district', 'operator' => 'EXISTS' ) ) ) );
$terms = get_terms( array( 'taxonomy' => 'np_district', 'hide_empty' => false ) );
?>
<section class="np-section np-catblock">
	<div class="np-container">
		<div class="np-section__head">
			<h2 class="np-section__title"><?php esc_html_e( 'Districts', 'newspaper-name-news' ); ?></h2>
			<label class="np-district-filter">
				<span class="screen-reader-text"><?php esc_html_e( 'Choose district', 'newspaper-name-news' ); ?></span>
				<select data-navigate>
					<option value=""><?php esc_html_e( 'Choose district', 'newspaper-name-news' ); ?></option>
					<?php foreach ( (array) $terms as $t ) : if ( ! $t instanceof WP_Term ) { continue; } ?>
						<option value="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
		<div class="np-grid-3">
			<?php while ( $q->have_posts() ) : $q->the_post(); np_card( 'card' ); endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
