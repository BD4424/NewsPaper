<?php
/** Full-width FLASH banner – newest active Breaking item marked "Flash". */
if ( ! defined( 'ABSPATH' ) || ! np_opt( 'flash_enable' ) || ! function_exists( 'np_core_active_breaking' ) ) { return; }
$flash = np_core_active_breaking( 1, true );
if ( ! $flash ) { return; }
$p    = $flash[0];
$link = get_post_meta( $p->ID, 'np_link', true ) ?: get_permalink( $p );
?>
<section class="np-flash" aria-live="polite">
	<div class="np-container np-flash__inner">
		<span class="np-flash__badge">FLASH</span>
		<a class="np-flash__title" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a>
	</div>
</section>
