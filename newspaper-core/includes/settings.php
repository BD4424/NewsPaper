<?php
/** Settings → Newspaper Core (e-Paper access, PDF download, WebP, defaults). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function np_core_setting_defaults(): array {
	return array(
		'epaper_access'          => 'public',   // public | logged_in
		'epaper_pdf_download'    => 1,
		'epaper_login_message'   => 'e-Paper ପଢ଼ିବା ପାଇଁ ଦୟାକରି ଲଗଇନ କରନ୍ତୁ।',
		'webp_convert'           => 1,
		'breaking_default_hours' => 6,
	);
}

function np_core_setting( string $key ) {
	$opts = wp_parse_args( (array) get_option( 'np_core_settings', array() ), np_core_setting_defaults() );
	return $opts[ $key ] ?? null;
}

add_action( 'admin_menu', function () {
	add_options_page( __( 'Newspaper Core', 'newspaper-core' ), __( 'Newspaper Core', 'newspaper-core' ), 'manage_options', 'np-core', 'np_core_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'np_core', 'np_core_settings', array( 'sanitize_callback' => function ( $in ) {
		$in = (array) $in;
		return array(
			'epaper_access'          => in_array( $in['epaper_access'] ?? '', array( 'public', 'logged_in' ), true ) ? $in['epaper_access'] : 'public',
			'epaper_pdf_download'    => empty( $in['epaper_pdf_download'] ) ? 0 : 1,
			'epaper_login_message'   => sanitize_text_field( $in['epaper_login_message'] ?? '' ),
			'webp_convert'           => empty( $in['webp_convert'] ) ? 0 : 1,
			'breaking_default_hours' => max( 1, absint( $in['breaking_default_hours'] ?? 6 ) ),
		);
	} ) );
} );

function np_core_settings_page(): void {
	$o = wp_parse_args( (array) get_option( 'np_core_settings', array() ), np_core_setting_defaults() ); ?>
	<div class="wrap"><h1><?php esc_html_e( 'Newspaper Core settings', 'newspaper-core' ); ?></h1>
	<form method="post" action="options.php"><?php settings_fields( 'np_core' ); ?>
	<table class="form-table">
		<tr><th><?php esc_html_e( 'e-Paper access', 'newspaper-core' ); ?></th><td>
			<label><input type="radio" name="np_core_settings[epaper_access]" value="public" <?php checked( $o['epaper_access'], 'public' ); ?>> <?php esc_html_e( 'Everyone', 'newspaper-core' ); ?></label><br>
			<label><input type="radio" name="np_core_settings[epaper_access]" value="logged_in" <?php checked( $o['epaper_access'], 'logged_in' ); ?>> <?php esc_html_e( 'Logged-in / subscribed users only', 'newspaper-core' ); ?></label></td></tr>
		<tr><th><?php esc_html_e( 'Message for logged-out readers', 'newspaper-core' ); ?></th><td><input class="regular-text" name="np_core_settings[epaper_login_message]" value="<?php echo esc_attr( $o['epaper_login_message'] ); ?>"></td></tr>
		<tr><th><?php esc_html_e( 'Allow PDF download', 'newspaper-core' ); ?></th><td><label><input type="checkbox" name="np_core_settings[epaper_pdf_download]" value="1" <?php checked( $o['epaper_pdf_download'], 1 ); ?>> <?php esc_html_e( 'Show "Download PDF" button', 'newspaper-core' ); ?></label></td></tr>
		<tr><th><?php esc_html_e( 'WebP', 'newspaper-core' ); ?></th><td><label><input type="checkbox" name="np_core_settings[webp_convert]" value="1" <?php checked( $o['webp_convert'], 1 ); ?>> <?php esc_html_e( 'Save uploaded JPEG/PNG sizes as WebP (server must support WebP)', 'newspaper-core' ); ?></label></td></tr>
		<tr><th><?php esc_html_e( 'Default Breaking expiry (hours)', 'newspaper-core' ); ?></th><td><input type="number" min="1" name="np_core_settings[breaking_default_hours]" value="<?php echo esc_attr( $o['breaking_default_hours'] ); ?>"></td></tr>
	</table><?php submit_button(); ?></form></div>
	<?php
}

/* WebP output for generated image sizes (WordPress 5.8+ image editor API). */
add_filter( 'image_editor_output_format', function ( $formats ) {
	if ( np_core_setting( 'webp_convert' ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
	}
	return $formats;
} );
