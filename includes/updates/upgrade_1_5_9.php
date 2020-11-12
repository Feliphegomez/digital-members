<?php
function dmrfid_upgrade_1_5_9()
{
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->dmrfid_membership_orders = $wpdb->prefix . 'dmrfid_membership_orders';

	//fix firstpayment statuses
	$sqlQuery = "UPDATE " . $wpdb->dmrfid_membership_orders . " SET status = 'success' WHERE status = 'firstpayment'";
	$wpdb->query($sqlQuery);

	dmrfid_setOption("db_version", "1.59");
	return 1.59;
}
