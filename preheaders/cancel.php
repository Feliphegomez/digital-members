<?php
	global $besecure;
	$besecure = false;

	global $wpdb, $current_user, $dmrfid_msg, $dmrfid_msgt, $dmrfid_confirm, $dmrfid_error;

	// Get the level IDs they are requesting to cancel using the old ?level param.
	if ( ! empty( $_REQUEST['level'] ) && empty( $_REQUEST['levelstocancel'] ) ) {
		$requested_ids = $_REQUEST['level'];
	}

	// Get the level IDs they are requesting to cancel from the ?levelstocancel param.
	if ( ! empty( $_REQUEST['levelstocancel'] ) ) {
		$requested_ids = $_REQUEST['levelstocancel'];
	}

	// Redirection logic.
	if ( ! is_user_logged_in() ) {
		if ( ! empty( $requested_ids ) ) {
			$redirect = add_query_arg( 'levelstocancel', $requested_ids, dmrfid_url( 'cancel' ) );
		} else {
			$redirect = dmrfid_url( 'cancel' );
		}
		// Redirect non-user to the login page; pass the Cancel page with specific ?levelstocancel as the redirect_to query arg.
		wp_redirect( add_query_arg( 'redirect_to', urlencode( $redirect ), dmrfid_login_url() ) );
		exit;
	} else {
		// Get the membership level for the current user.
		$current_user->membership_level = dmrfid_getMembershipLevelForUser( $current_user->ID) ;
		// If user has no membership level, redirect to levels page.
		if ( ! isset( $current_user->membership_level->ID ) ) {
			wp_redirect( dmrfid_url( 'levels' ) );
			exit;
		}
	}

	//check if a level was passed in to cancel specifically
	if ( ! empty ( $requested_ids ) && $requested_ids != 'all' ) {
		//convert spaces back to +
		$requested_ids = str_replace(array(' ', '%20'), '+', $requested_ids );

		//get the ids
		$requested_ids = preg_replace("/[^0-9\+]/", "", $requested_ids );
		$old_level_ids = array_map( 'intval', explode( "+", $requested_ids ) );

		// Make sure the user has the level they are trying to cancel.
		if ( ! dmrfid_hasMembershipLevel( $old_level_ids ) ) {
			// If they don't have the level, return to Membership Account.
			wp_redirect( dmrfid_url( 'account' ) );
			exit;
		}
	} else {
		$old_level_ids = false;	//cancel all levels
	}

	//are we confirming a cancellation?
	if(isset($_REQUEST['confirm']))
		$dmrfid_confirm = (bool)$_REQUEST['confirm'];
	else
		$dmrfid_confirm = false;

	if($dmrfid_confirm) {
        if(!empty($old_level_ids)) {
        	$worked = true;
			foreach($old_level_ids as $old_level_id) {
				$worked = $worked && dmrfid_cancelMembershipLevel($old_level_id, $current_user->ID, 'cancelled');
			}
        }
		else {
			$old_level_ids = $wpdb->get_col("SELECT DISTINCT(membership_id) FROM $wpdb->dmrfid_memberships_users WHERE user_id = '" . $current_user->ID . "' AND status = 'active'");
			$worked = dmrfid_changeMembershipLevel(0, $current_user->ID, 'cancelled');
		}
        
		if($worked === true && empty($dmrfid_error))
		{
			$dmrfid_msg = __("Your membership has been cancelled.", 'paid-memberships-pro' );
			$dmrfid_msgt = "dmrfid_success";

			//send an email to the member
			$myemail = new DmRFIDEmail();
			$myemail->sendCancelEmail($current_user, $old_level_ids);

			//send an email to the admin
			$myemail = new DmRFIDEmail();
			$myemail->sendCancelAdminEmail($current_user, $old_level_ids);
		} else {
			global $dmrfid_error;
			$dmrfid_msg = $dmrfid_error;
			$dmrfid_msgt = "dmrfid_error";
		}
	}
