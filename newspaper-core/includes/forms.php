<?php
/**
 * [np_form type="contact"] and [np_form type="advertise"] – nonce + honeypot + rate limit, sends to admin email.
 * Optional: [np_form type="advertise" to="ads@example.com"]
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_shortcode( 'np_form', function ( $atts ) {
	$a    = shortcode_atts( array( 'type' => 'contact', 'to' => '' ), $atts );
	$type = 'advertise' === $a['type'] ? 'advertise' : 'contact';
	$msg  = '';

	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['np_form_type'] ) && $type === $_POST['np_form_type'] ) {
		$ip_key = 'np_form_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		if ( ! isset( $_POST['np_form_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['np_form_nonce'] ), 'np_form_' . $type ) ) {
			$msg = '<p class="np-form__err">' . esc_html__( 'Session expired, please try again.', 'newspaper-core' ) . '</p>';
		} elseif ( ! empty( $_POST['np_website'] ) || get_transient( $ip_key ) ) { // honeypot / 1 per minute.
			$msg = '<p class="np-form__ok">' . esc_html__( 'Thank you! We will get back to you soon.', 'newspaper-core' ) . '</p>';
		} else {
			$name  = sanitize_text_field( wp_unslash( $_POST['np_name'] ?? '' ) );
			$email = sanitize_email( wp_unslash( $_POST['np_email'] ?? '' ) );
			$phone = sanitize_text_field( wp_unslash( $_POST['np_phone'] ?? '' ) );
			$extra = sanitize_text_field( wp_unslash( $_POST['np_extra'] ?? '' ) );
			$body  = sanitize_textarea_field( wp_unslash( $_POST['np_message'] ?? '' ) );
			if ( ! $name || ! is_email( $email ) || ! $body ) {
				$msg = '<p class="np-form__err">' . esc_html__( 'Please fill name, a valid email and message.', 'newspaper-core' ) . '</p>';
			} else {
				$to      = is_email( $a['to'] ) ? $a['to'] : get_option( 'admin_email' );
				$subject = sprintf( '[%s] %s: %s', get_bloginfo( 'name' ), 'advertise' === $type ? 'Advertising enquiry' : 'Contact', $name );
				$content = "Name: $name\nEmail: $email\nPhone: $phone\n" . ( 'advertise' === $type ? "Company / Ad type: $extra\n" : '' ) . "\n$body";
				wp_mail( $to, $subject, $content, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
				set_transient( $ip_key, 1, MINUTE_IN_SECONDS );
				$msg = '<p class="np-form__ok">' . esc_html__( 'Thank you! We will get back to you soon.', 'newspaper-core' ) . '</p>';
			}
		}
	}

	ob_start();
	echo $msg; // phpcs:ignore
	?>
	<form class="np-form" method="post">
		<?php wp_nonce_field( 'np_form_' . $type, 'np_form_nonce' ); ?>
		<input type="hidden" name="np_form_type" value="<?php echo esc_attr( $type ); ?>">
		<p style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="np_website" tabindex="-1" autocomplete="off"></p>
		<p><label><?php esc_html_e( 'Name', 'newspaper-core' ); ?> *<br><input type="text" name="np_name" required></label></p>
		<p><label><?php esc_html_e( 'Email', 'newspaper-core' ); ?> *<br><input type="email" name="np_email" required></label></p>
		<p><label><?php esc_html_e( 'Phone', 'newspaper-core' ); ?><br><input type="tel" name="np_phone"></label></p>
		<?php if ( 'advertise' === $type ) : ?>
			<p><label><?php esc_html_e( 'Company / Ad type (Print, Web, e-Paper)', 'newspaper-core' ); ?><br><input type="text" name="np_extra"></label></p>
		<?php endif; ?>
		<p><label><?php esc_html_e( 'Message', 'newspaper-core' ); ?> *<br><textarea name="np_message" rows="5" required></textarea></label></p>
		<p><button type="submit" class="np-btn"><?php esc_html_e( 'Send', 'newspaper-core' ); ?></button></p>
	</form>
	<style>.np-form input,.np-form textarea{width:100%;padding:.6em;font:inherit;border:1px solid #ccc;border-radius:4px}.np-form__ok{color:#0a7a2f;font-weight:700}.np-form__err{color:#b00;font-weight:700}</style>
	<?php
	return ob_get_clean();
} );
