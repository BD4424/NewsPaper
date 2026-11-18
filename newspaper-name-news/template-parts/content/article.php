<?php
/** One full article (used by single.php and the next-story AJAX). */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$id      = get_the_ID();
$is_next = ! empty( $args['next'] );
$credit  = get_post_meta( $id, 'np_photo_credit', true );
$dateline= get_post_meta( $id, 'np_dateline', true );
$reporter= get_post_meta( $id, 'np_reporter', true ) ?: get_the_author();
?>
<article id="post-<?php echo esc_attr( $id ); ?>" <?php post_class( 'np-article' ); ?> data-id="<?php echo esc_attr( $id ); ?>" data-url="<?php the_permalink(); ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>">
	<header class="np-article__head">
		<?php echo np_primary_cat() . np_sponsored_label(); // phpcs:ignore ?>
		<?php if ( $is_next ) : ?><h2 class="np-article__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php else : ?><h1 class="np-article__title"><?php the_title(); ?></h1><?php endif; ?>
		<?php if ( np_kicker() ) : ?><p class="np-article__kicker"><?php echo esc_html( np_kicker() ); ?></p><?php endif; ?>
		<div class="np-article__meta">
			<span class="np-byline"><?php echo get_avatar( get_the_author_meta( 'ID' ), 36, '', '', array( 'loading' => 'lazy' ) ); ?>
				<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( $reporter ); ?></a></span>
			<span><?php esc_html_e( 'Published:', 'newspaper-name-news' ); ?> <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( np_odia_date( get_post_time( 'U', true ), true ) ); ?></time></span>
			<?php if ( get_the_modified_time( 'U' ) - get_the_time( 'U' ) > 300 ) : ?>
				<span><?php esc_html_e( 'Updated:', 'newspaper-name-news' ); ?> <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( np_odia_date( get_post_modified_time( 'U', true ), true ) ); ?></time></span>
			<?php endif; ?>
			<span><?php echo esc_html( np_reading_time() ); ?></span>
			<span class="np-views"><?php echo esc_html( np_views() ); ?> <?php esc_html_e( 'views', 'newspaper-name-news' ); ?></span>
		</div>
		<div class="np-article__tools">
			<?php np_share_buttons(); ?>
			<div class="np-fontsize" role="group" aria-label="<?php esc_attr_e( 'Text size', 'newspaper-name-news' ); ?>">
				<button type="button" data-font="-1" aria-label="<?php esc_attr_e( 'Decrease text size', 'newspaper-name-news' ); ?>">A-</button>
				<button type="button" data-font="1" aria-label="<?php esc_attr_e( 'Increase text size', 'newspaper-name-news' ); ?>">A+</button>
			</div>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="np-article__figure">
			<?php the_post_thumbnail( 'large', array( 'loading' => $is_next ? 'lazy' : 'eager', 'fetchpriority' => $is_next ? 'auto' : 'high' ) ); ?>
			<?php $cap = get_the_post_thumbnail_caption(); if ( $cap || $credit ) : ?>
				<figcaption><?php echo esc_html( $cap ); ?><?php if ( $credit ) : ?> <span class="np-credit"><?php esc_html_e( 'Photo:', 'newspaper-name-news' ); ?> <?php echo esc_html( $credit ); ?></span><?php endif; ?></figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>

	<div class="np-article__body entry-content">
		<?php if ( $dateline ) : ?><strong class="np-dateline"><?php echo esc_html( $dateline ); ?>:</strong><?php endif; ?>
		<?php the_content(); ?>
	</div>

	<?php if ( np_opt( 'join_banner' ) && ( np_opt( 'social_whatsapp' ) || np_opt( 'social_telegram' ) ) ) : ?>
		<div class="np-join">
			<?php if ( np_opt( 'social_whatsapp' ) ) : ?><a class="np-join__wa" href="<?php echo esc_url( np_opt( 'social_whatsapp' ) ); ?>" target="_blank" rel="noopener"><?php echo np_icon( 'whatsapp' ); // phpcs:ignore ?> <?php esc_html_e( 'Join our WhatsApp channel', 'newspaper-name-news' ); ?></a><?php endif; ?>
			<?php if ( np_opt( 'social_telegram' ) ) : ?><a class="np-join__tg" href="<?php echo esc_url( np_opt( 'social_telegram' ) ); ?>" target="_blank" rel="noopener"><?php echo np_icon( 'telegram' ); // phpcs:ignore ?> <?php esc_html_e( 'Join on Telegram', 'newspaper-name-news' ); ?></a><?php endif; ?>
		</div>
	<?php endif; ?>

	<?php the_tags( '<div class="np-tags">', '', '</div>' ); ?>

	<?php if ( ! $is_next ) : ?>
		<?php get_template_part( 'template-parts/content/related' ); ?>
		<?php np_share_buttons( 'np-share--sticky' ); ?>
	<?php endif; ?>
</article>
