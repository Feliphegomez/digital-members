<?php
function dmrfid_upgrade_1_2_3()
{
	global $wpdb;
	$wpdb->hide_errors();
	$wpdb->dmrfid_membership_levels = $wpdb->prefix . 'dmrfid_membership_levels';
	$wpdb->dmrfid_memberships_users = $wpdb->prefix . 'dmrfid_memberships_users';
	$wpdb->dmrfid_memberships_categories = $wpdb->prefix . 'dmrfid_memberships_categories';
	$wpdb->dmrfid_memberships_pages = $wpdb->prefix . 'dmrfid_memberships_pages';
	$wpdb->dmrfid_membership_orders = $wpdb->prefix . 'dmrfid_membership_orders';
	$wpdb->dmrfid_discount_codes = $wpdb->prefix . 'dmrfid_discount_codes';
	$wpdb->dmrfid_discount_codes_levels = $wpdb->prefix . 'dmrfid_discount_codes_levels';
	$wpdb->dmrfid_discount_codes_uses = $wpdb->prefix . 'dmrfid_discount_codes_uses';

	//expiration number and period for levels
	$sqlQuery = "
		ALTER TABLE  `" . $wpdb->dmrfid_membership_levels . "` ADD  `expiration_number` INT UNSIGNED NOT NULL ,
ADD  `expiration_period` ENUM(  'Day',  'Week',  'Month',  'Year' ) NOT NULL
	";
	$wpdb->query($sqlQuery);

	//expiration number and period for discount code levels
	$sqlQuery = "
		ALTER TABLE  `" . $wpdb->dmrfid_discount_codes_levels . "` ADD  `expiration_number` INT UNSIGNED NOT NULL ,
ADD  `expiration_period` ENUM(  'Day',  'Week',  'Month',  'Year' ) NOT NULL
	";
	$wpdb->query($sqlQuery);

	//end date for members
	$sqlQuery = "
		ALTER TABLE  `" . $wpdb->dmrfid_memberships_users . "` ADD  `enddate` DATETIME NULL AFTER  `startdate`
	";
	$wpdb->query($sqlQuery);

	$sqlQuery = "
		ALTER TABLE  `" . $wpdb->dmrfid_memberships_users . "` ADD INDEX (  `enddate` )
	";
	$wpdb->query($sqlQuery);

	dmrfid_setOption("db_version", "1.23");
	return 1.23;
}
