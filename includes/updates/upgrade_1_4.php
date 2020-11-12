<?php
function dmrfid_upgrade_1_4()
{
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->dmrfid_membership_levels = $wpdb->prefix . 'dmrfid_membership_levels';

	//confirmation message
	$sqlQuery = "
		ALTER TABLE  `" . $wpdb->dmrfid_membership_levels . "` ADD  `confirmation` LONGTEXT NOT NULL AFTER  `description`
	";
	$wpdb->query($sqlQuery);

	dmrfid_setOption("db_version", "1.4");
	return 1.4;
}
