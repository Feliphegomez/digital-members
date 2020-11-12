<?php
	global $isapage;
	$isapage = true;

	//wp includes
	define('WP_USE_THEMES', false);
	require('../../../../wp-load.php');

	//this function is defined in /scheduled/crons.php
	dmrfid_cron_expire_memberships();
