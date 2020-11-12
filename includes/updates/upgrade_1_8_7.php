<?php
/*
	Remove extra cron jobs inserted in version 1.8.7 and 1.8.7.1
*/
function dmrfid_upgrade_1_8_7() {
	
	//fix cron jobs
    $jobs = _get_cron_array();
	
    // Remove all dmrfid cron jobs (for now).
    foreach( $jobs as $when => $job_array ) {

        foreach($job_array as $name => $job) {
	        //delete dmrfid cron
	        if ( false !== stripos( $name, 'dmrfid_cron') )
	            unset($jobs[$when][$name]);	     
    	}

    	//delete empty cron time slots
    	if( empty($jobs[$when]) )
	        unset($jobs[$when]);
    }

    // Save the data
    _set_cron_array($jobs);

    //add the three we want back
	dmrfid_maybe_schedule_event(current_time('timestamp'), 'daily', 'dmrfid_cron_expire_memberships');
	dmrfid_maybe_schedule_event(current_time('timestamp')+1, 'daily', 'dmrfid_cron_expiration_warnings');
	dmrfid_maybe_schedule_event(current_time('timestamp'), 'monthly', 'dmrfid_cron_credit_card_expiring_warnings');

	dmrfid_setOption("db_version", "1.87");	

	return 1.87;
}
