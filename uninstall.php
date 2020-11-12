<?php
/**
 * Leave no trace...
 * Use this file to remove all elements added by plugin, including database table
 */

// exit if uninstall/delete not called
if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
    exit();

if ( get_option( 'dmrfid_uninstall', 0 ) ) {
	// otherwise remove pages
	$dmrfid_pages = array(
		'account' => get_option( 'dmrfid_account_page_id' ),
		'billing' => get_option( 'dmrfid_billing_page_id' ),
		'cancel' =>get_option( 'dmrfid_cancel_page_id' ),
		'checkout' => get_option( 'dmrfid_checkout_page_id' ),
		'confirmation' => get_option( 'dmrfid_confirmation_page_id' ),
		'invoice' => get_option( 'dmrfid_invoice_page_id' ),
		'levels' => get_option( 'dmrfid_levels_page_id' ),
	  'login' => get_option( 'dmrfid_login_page_id' ),
	  'member_profile_edit' => get_option( 'dmrfid_member_profile_edit_page_id' )
	);

	foreach ( $dmrfid_pages as $dmrfid_page_id => $dmrfid_page ) {
		$shortcode_prefix = 'dmrfid_';
		$shortcode = '[' . $shortcode_prefix . $dmrfid_page_id . ']';
		$post = get_post( $dmrfid_page );

		// If shortcode is found at the beginning of the page content and it is the only content that exists, remove the page
		if ( strpos( $post->post_content, $shortcode ) === 0 && strcmp( $post->post_content, $shortcode ) === 0 )
			wp_delete_post( $post->ID, true ); // Force delete (no trash)
	}

	// otherwise remove db tables
	global $wpdb;

	$tables = array(
	    'dmrfid_discount_codes',
	    'dmrfid_discount_codes_levels',
	    'dmrfid_discount_codes_uses',
	    'dmrfid_memberships_categories',
	    'dmrfid_memberships_pages',
	    'dmrfid_memberships_users',
	    'dmrfid_membership_levels',
	    'dmrfid_membership_orders'
	);

	foreach($tables as $table){
	    $delete_table = $wpdb->prefix . $table;
	    // setup sql query
	    $sql = "DROP TABLE `$delete_table`";
	    // run the query
	    $wpdb->query($sql);
	}

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	//delete options
	global $wpdb;
	$sqlQuery = "DELETE FROM $wpdb->options WHERE option_name LIKE 'dmrfid_%'";
	$wpdb->query($sqlQuery);
}
