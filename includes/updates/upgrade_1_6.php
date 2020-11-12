<?php
function dmrfid_upgrade_1_6()
{
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->dmrfid_membership_orders = $wpdb->prefix . 'dmrfid_membership_orders';

	//add notes column to orders
	$sqlQuery = "ALTER TABLE  `" . $wpdb->dmrfid_membership_orders . "` ADD  `notes` TEXT NOT NULL";
	$wpdb->query($sqlQuery);

	dmrfid_setOption("db_version", "1.6");
	return 1.6;
}
