<?php
/*
	Upgrade to 1.9.4
	Update for div layout.
*/
function dmrfid_upgrade_1_9_4() {

	$parent_theme_template = get_template_directory() . "/digital-members-rfid/pages/checkout.php";
	$child_theme_template = get_stylesheet_directory() . "/digital-members-rfid/pages/checkout.php";

	$dmrfid_hide_notice = get_option( 'dmrfid_hide_div_notice', 0 );
		
		// Show admin notice if the user has a custom checkout page template.
		if( ( file_exists( $parent_theme_template ) || file_exists( $child_theme_template ) ) && empty( $dmrfid_hide_notice ) && empty( $_REQUEST['dmrfid_div_notice_hide'] ) ) {
			add_action( 'admin_notices', 'dmrfid_upgrade_1_9_4_show_div_notice' );
		}

		dmrfid_setOption( 'db_version', '1.94' );
		return 1.94;
}

// Code to handle the admin notice.
function dmrfid_upgrade_1_9_4_show_div_notice() {
 ?>
    <div class="notice notice-warning">
        <p><?php _e( 'We have detected that you are using a custom checkout page template for Digital Members RFID. This was recently changed and may need to be updated in order to display correctly.', 'digital-members-rfid')?>
        	<?php _e('If you notice UI issues after upgrading, <a href="https://www.paidmembershipspro.com/add-ons/table-layout-plugin-pages/">see this free add on to temporarily roll back to the table-based layout while you resolve the issues</a>.', 'digital-members-rfid' ); ?> <a href="<?php echo add_query_arg('dmrfid_div_notice_hide', '1', $_SERVER['REQUEST_URI']);?>"><?php _e( 'Dismiss', 'digital-members-rfid' );?></a></p>
    </div>
<?php
}

function dmrfid_update_1_9_4_notice_dismiss() {

	// check if query arg is available.
	if( !empty( $_REQUEST['dmrfid_div_notice_hide'] ) ) {
		update_option( 'dmrfid_hide_div_notice', 1 );
	}
}

add_action( 'admin_init', 'dmrfid_update_1_9_4_notice_dismiss' );