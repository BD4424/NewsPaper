<?php
/**
 * Newsroom roles:
 *  - Administrator / Editor: everything (incl. e-Paper).
 *  - Reporter: write & upload, submit for review only (cannot publish).
 *  - e-Paper Uploader: only manages e-Paper editions + media.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function np_core_epaper_caps(): array {
	return array( 'edit_np_epapers', 'edit_others_np_epapers', 'publish_np_epapers', 'read_private_np_epapers', 'delete_np_epapers', 'delete_published_np_epapers', 'delete_others_np_epapers', 'edit_published_np_epapers' );
}

function np_core_add_roles(): void {
	add_role( 'np_reporter', __( 'Reporter', 'newspaper-core' ), array(
		'read' => true, 'edit_posts' => true, 'delete_posts' => true, 'upload_files' => true, 'publish_posts' => false,
	) );
	add_role( 'np_epaper_uploader', __( 'e-Paper Uploader', 'newspaper-core' ), array_merge(
		array( 'read' => true, 'upload_files' => true ),
		array_fill_keys( np_core_epaper_caps(), true )
	) );
	foreach ( array( 'administrator', 'editor' ) as $r ) {
		$role = get_role( $r );
		if ( $role ) { foreach ( np_core_epaper_caps() as $cap ) { $role->add_cap( $cap ); } }
	}
}

/* Re-run once if roles are missing (e.g. after updating the plugin without re-activation). */
add_action( 'admin_init', function () {
	if ( get_option( 'np_core_roles_version' ) !== NP_CORE_VERSION ) {
		np_core_add_roles();
		update_option( 'np_core_roles_version', NP_CORE_VERSION );
	}
} );

/* e-Paper Uploader: keep the admin menu focused. */
add_action( 'admin_menu', function () {
	$u = wp_get_current_user();
	if ( in_array( 'np_epaper_uploader', (array) $u->roles, true ) ) {
		remove_menu_page( 'edit.php' );
		remove_menu_page( 'edit-comments.php' );
		remove_menu_page( 'tools.php' );
	}
}, 999 );

/* Reporter profile field: designation (shown on author page). */
function np_core_designation_field( WP_User $user ): void { ?>
	<h2><?php esc_html_e( 'Newsroom profile', 'newspaper-core' ); ?></h2>
	<table class="form-table"><tr><th><label for="np_designation"><?php esc_html_e( 'Designation', 'newspaper-core' ); ?></label></th>
	<td><input type="text" class="regular-text" id="np_designation" name="np_designation" value="<?php echo esc_attr( get_user_meta( $user->ID, 'np_designation', true ) ); ?>" placeholder="ବରିଷ୍ଠ ସାମ୍ବାଦିକ"></td></tr></table>
	<?php
}
add_action( 'show_user_profile', 'np_core_designation_field' );
add_action( 'edit_user_profile', 'np_core_designation_field' );
$np_save_desig = function ( $uid ) {
	if ( current_user_can( 'edit_user', $uid ) && isset( $_POST['np_designation'] ) && check_admin_referer( 'update-user_' . $uid ) ) {
		update_user_meta( $uid, 'np_designation', sanitize_text_field( wp_unslash( $_POST['np_designation'] ) ) );
	}
};
add_action( 'personal_options_update', $np_save_desig );
add_action( 'edit_user_profile_update', $np_save_desig );
