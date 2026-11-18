<?php
/** Breaking ticker – active (non-expired) Breaking News items from Newspaper Core. */
if ( ! defined( 'ABSPATH' ) || ! np_opt( 'ticker_enable' ) || ! function_exists( 'np_core_active_breaking' ) ) { return; }
$items = np_core_active_breaking( absint( np_opt( 'ticker_count' ) ) );
if ( ! $items ) { return; }
?>
<div class="np-ticker" id="np-breaking" role="region" aria-label="<?php esc_attr_e( 'Breaking news', 'newspaper-name-news' ); ?>">
	<div class="np-container np-ticker__inner">
		<strong class="np-ticker__label"><?php echo np_icon( 'bolt' ); // phpcs:ignore ?> <?php echo esc_html( np_opt( 'ticker_label' ) ); ?></strong>
		<div class="np-ticker__viewport">
			<ul class="np-ticker__track" data-speed="<?php echo absint( np_opt( 'ticker_speed' ) ); ?>">
				<?php foreach ( $items as $p ) :
					$link = get_post_meta( $p->ID, 'np_link', true ) ?: get_permalink( $p ); ?>
					<li><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<button type="button" class="np-ticker__pause" aria-pressed="false" aria-label="<?php esc_attr_e( 'Pause ticker', 'newspaper-name-news' ); ?>">❚❚</button>
	</div>
</div>
