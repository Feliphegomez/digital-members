<?php
/*
	Upgrade to 1.8.9.3
	Fixing incorrect start and end dates.
*/
function dmrfid_upgrade_1_8_9_3() {
	global $wpdb;	
	
	//Fixing incorrect start and end dates. (Sets up update via AJAX)
	$user_ids = $wpdb->get_col("SELECT user_id FROM $wpdb->dmrfid_memberships_users WHERE status = 'active' AND modified > '2016-05-19'");

	if(!empty($user_ids))
		dmrfid_addUpdate('dmrfid_upgrade_1_8_9_3_ajax');

	dmrfid_setOption("db_version", "1.91");
	return 1.893;
}

/*
	We run this part of the update via AJAX from the updates page.
*/
function dmrfid_upgrade_1_8_9_3_ajax() {
	global $wpdb;
	
	$debug = false;
	$run = true;

	//some vars
	$all_levels = dmrfid_getAllLevels(true, true);

	//keeping track of which user we're working on
	$last_user_id = get_option('dmrfid_upgrade_1_8_9_3_last_user_id', 0);

	//get all active users during the period where things may have been broken
	$user_ids = $wpdb->get_col("SELECT user_id FROM $wpdb->dmrfid_memberships_users WHERE status = 'active' AND modified > '2016-05-19' AND user_id > $last_user_id ORDER BY user_id LIMIT 10");

	//track progress
	$first_load = get_transient('dmrfid_updates_first_load');
	if($first_load) {
		$total_users = $wpdb->get_var("SELECT COUNT(user_id) FROM $wpdb->dmrfid_memberships_users WHERE status = 'active' AND modified > '2016-05-19' ORDER BY user_id");
		update_option('dmrfid_upgrade_1_8_9_3_total', $total_users, 'no');
		$progress = 0;
	} else {
		$total_users = get_option('dmrfid_upgrade_1_8_9_3_total', 0);
		$progress = get_option('dmrfid_upgrade_1_8_9_3_progress', 0);
	}
	update_option('dmrfid_upgrade_1_8_9_3_progress', $progress + count($user_ids), 'no');
	global $dmrfid_updates_progress;
	if($total_users > 0)
		$dmrfid_updates_progress = "[" . $progress . "/" . $total_users . "]";
	else
		$dmrfid_updates_progress = "";

	if(empty($user_ids)) {
		//done with this update			
		dmrfid_removeUpdate('dmrfid_upgrade_1_8_9_3_ajax');
		delete_option('dmrfid_upgrade_1_8_9_3_last_user_id');
		delete_option('dmrfid_upgrade_1_8_9_3_total');
		delete_option('dmrfid_upgrade_1_8_9_3_progress');
	} else {
		foreach($user_ids as $user_id) {
			$last_user_id = $user_id; //keeping track of the last user we processed
			$user = get_userdata($user_id);
			
			//user not found for some reason
			if(empty($user)) {
				if($debug)
					echo "User #" . $user_id . " not found.\n";
				continue;
			}

			//get level
			$user->membership_level = dmrfid_getMembershipLevelForUser($user->ID);

			//has a start and end date already
			if(!empty($user->membership_level->enddate) && !empty($user->membership_level->startdate)) {
				if($debug)
					echo "User #" . $user_id . ", " . $user->user_email . " already has a start and end date.\n";
				continue;
			}

			//get order
			$last_order = new MemberOrder();
			$last_order->getLastMemberOrder();

			/*
				Figure out if this user should have been given an end date.
				The level my have an end date.
				They might have used a discount code.
				They might be using the set-expiration-dates code.
				They might have custom code setting the end date.

				Let's setup some vars as if we are at checkout.
				Then pass recreate the level with the dmrfid_checkout_level filter.
				And use the end date there if there is one.
			*/
			global $dmrfid_level, $discount_code, $discount_code_id;
			
			//level
			$level_id = $user->membership_level->id;
			$_REQUEST['level'] = $level_id;

			//gateway
			if(!empty($last_order) && !empty($last_order->gateway))
				$_REQUEST['gateway'] = $last_order->gateway;
			else
				$_REQUEST['gateway'] = dmrfid_getGateway();

			//discount code
			$discount_code_id = $user->membership_level->code_id;
			$discount_code = $wpdb->get_var( "SELECT code FROM $wpdb->dmrfid_discount_codes WHERE id = '" . $discount_code_id . "' LIMIT 1" );

			//get level
			if(!empty($discount_code_id)) {
				$sqlQuery    = "SELECT l.id, cl.*, l.name, l.description, l.allow_signups FROM $wpdb->dmrfid_discount_codes_levels cl LEFT JOIN $wpdb->dmrfid_membership_levels l ON cl.level_id = l.id LEFT JOIN $wpdb->dmrfid_discount_codes dc ON dc.id = cl.code_id WHERE dc.code = '" . $discount_code . "' AND cl.level_id = '" . (int) $level_id . "' LIMIT 1";
		
				$dmrfid_level = $wpdb->get_row( $sqlQuery );

				//if the discount code doesn't adjust the level, let's just get the straight level
				if ( empty( $dmrfid_level ) ) {
					$dmrfid_level = $all_levels[$level_id];
				}

				//filter adjustments to the level
				$dmrfid_level->code_id = $discount_code_id;
				$dmrfid_level          = apply_filters( "dmrfid_discount_code_level", $dmrfid_level, $discount_code_id );
			}

			//no level yet, use default
			if ( empty( $dmrfid_level ) ) {
				$dmrfid_level = $all_levels[$level_id];
			}

			//no level for some reason
			if(empty($dmrfid_level) && empty($dmrfid_level->id)) {
				if($debug)
					echo "No level found with ID #" . $level_id . " for user #" . $user_id . ", " . $user->user_email . ".\n";
				continue;
			}

			//filter level
			$dmrfid_level = apply_filters( "dmrfid_checkout_level", $dmrfid_level );

			if($debug)
				echo "User #" . $user_id . ", " . $user->user_email . ". Fixing.\n";

			//calculate and fix start date
			if(empty($user->membership_level->startdate)) {
				$startdate = $wpdb->get_var("SELECT modified FROM $wpdb->dmrfid_memberships_users WHERE user_id = $user_id AND membership_id = $level_id AND status = 'active' LIMIT 1");

				//filter
				$filtered_startdate = apply_filters( "dmrfid_checkout_start_date", $startdate, $user_id, $dmrfid_level );

				//only use filtered value if it's not 0
				if(!empty($filtered_startdate) && $filtered_startdate != '0000-00-00 00:00:00' && $filtered_startdate != "'0000-00-00 00:00:00'")
					$startdate = $filtered_startdate;

				if($debug)
					echo "- Adding startdate " . $startdate . ".\n";
				if($run) {
					$sqlQuery = "UPDATE $wpdb->dmrfid_memberships_users SET startdate = '" . esc_sql($startdate) . "' WHERE user_id = $user_id AND membership_id = $level_id AND status = 'active' LIMIT 1";
					$wpdb->query($sqlQuery);
				}
			} else {
				$startdate = date_i18n( "Y-m-d", $user->membership_level->startdate );
			}
			
			//calculate and fix the end date
			if(empty($user->membership_level->enddate)) {
				if ( ! empty( $dmrfid_level->expiration_number ) ) {
					$enddate =  date_i18n( "Y-m-d", strtotime( "+ " . $dmrfid_level->expiration_number . " " . $dmrfid_level->expiration_period, $last_order->getTimestamp() ) );
				} else {
					$enddate = "NULL";
				}

				$enddate = apply_filters( "dmrfid_checkout_end_date", $enddate, $user_id, $dmrfid_level, $startdate );

				if(!empty($enddate) && $enddate != "NULL") {
					if($debug)
						echo "- Adding enddate " . $enddate . ".\n";
					if($run) {
						$sqlQuery = "UPDATE $wpdb->dmrfid_memberships_users SET enddate = '" . esc_sql($enddate) . "' WHERE user_id = $user_id AND membership_id = $level_id AND status = 'active' LIMIT 1";
						$wpdb->query($sqlQuery);
					}
				}
			}

			//clear vars for next pass
			$user_id = NULL;
			$level_id = NULL;
			$discount_code = NULL;
			$discount_code_id = NULL;
			$dmrfid_level = NULL;
			$last_order = NULL;
			$startdate = NULL;
			$filtered_startdate = NULL;
			$enddate = NULL;

			echo "\n";
		}

		update_option('dmrfid_upgrade_1_8_9_3_last_user_id', $last_user_id, 'no');
	}
}
