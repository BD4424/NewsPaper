<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div class="np-topbar">
	<div class="np-container np-topbar__inner">
		<div class="np-topbar__date">
			<span><?php echo esc_html( np_odia_date( null, false, true ) ); ?></span>
			<?php if ( np_opt( 'panjika_text' ) ) : ?><span class="np-topbar__panjika"><?php echo esc_html( np_opt( 'panjika_text' ) ); ?></span><?php endif; ?>
		</div>
		<div class="np-topbar__right">
			<?php np_social_links(); ?>
			<?php if ( np_opt( 'dark_mode' ) ) : ?>
				<button type="button" class="np-dark-toggle" aria-pressed="false" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'newspaper-name-news' ); ?>"><?php echo np_icon( 'moon' ); // phpcs:ignore ?></button>
			<?php endif; ?>
			<a class="np-btn np-btn--epaper" href="<?php echo esc_url( np_opt( 'epaper_url' ) ); ?>"><?php esc_html_e( 'e-Paper', 'newspaper-name-news' ); ?></a>
		</div>
	</div>
</div>
