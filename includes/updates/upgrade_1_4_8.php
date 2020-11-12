<?php
function dmrfid_upgrade_1_4_8()
{
	/*
		Adding a billing_country field to the orders table.		
	*/

	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->dmrfid_membership_orders = $wpdb->prefix . 'dmrfid_membership_orders';

	//billing_country
	$sqlQuery = "
		ALTER TABLE  `" . $wpdb->dmrfid_membership_orders . "` ADD  `billing_country` VARCHAR( 128 ) NOT NULL AFTER  `billing_zip`
	";
	$wpdb->query($sqlQuery);

	dmrfid_setOption("db_version", "1.48");
	return 1.48;
}
