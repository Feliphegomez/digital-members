<?php
function dmrfid_upgrade_1()
{
	/*
		default options
	*/
	$nonmembertext = sprintf( __( 'This content is for !!levels!! members only.<br /><a href="%s">Join Now</a>', 'paid-memberships-pro' ), "!!levels_page_url!!" );
	dmrfid_setOption("nonmembertext", $nonmembertext);

	$notloggedintext = sprintf( __( 'This content is for !!levels!! members only.<br /><a href="%s">Login</a> <a href="%s">Join Now</a>', 'paid-memberships-pro' ), '!!login_url!!', "!!levels_page_url!!" );
	dmrfid_setOption("notloggedintext", $notloggedintext);

	$rsstext = __( 'This content is for members only. Visit the site and log in/register to read.', 'paid-memberships-pro' );
	dmrfid_setOption("rsstext", $rsstext);

	$gateway_environment = "sandbox";
	dmrfid_setOption("gateway_environment", $gateway_environment);

	$dmrfid_currency = "USD";
	dmrfid_setOption("currency", $dmrfid_currency);

	$dmrfid_accepted_credit_cards = "Visa,Mastercard,American Express,Discover";
	dmrfid_setOption("accepted_credit_cards", $dmrfid_accepted_credit_cards);

	$parsed = parse_url( home_url() );
	$hostname = $parsed['host'];
	$host_parts = explode( ".", $hostname );
	if ( count( $host_parts ) > 1 ) {
		$email_domain = $host_parts[count($host_parts) - 2] . "." . $host_parts[count($host_parts) - 1];
	} else {
		$email_domain = $parsed['host'];
	}
	
	$from_email = "wordpress@" . $email_domain;
	dmrfid_setOption("from_email", $from_email);

	$from_name = "WordPress";
	dmrfid_setOption("from_name", $from_name);

	//setting new email settings defaults
	dmrfid_setOption("email_admin_checkout", "1");
	dmrfid_setOption("email_admin_changes", "1");
	dmrfid_setOption("email_admin_cancels", "1");
	dmrfid_setOption("email_admin_billing", "1");
	dmrfid_setOption("tospage", "");
	
	//don't want these pointers to show on new installs
	update_option( 'dmrfid_dismissed_wp_pointers', array( 'dmrfid_v2_menu_moved' ) );

	//let's pause the nag for the first week of use
	$dmrfid_nag_paused = current_time('timestamp')+(3600*24*7);
	update_option('dmrfid_nag_paused', $dmrfid_nag_paused, 'no');

	//db update
	dmrfid_db_delta();

	//update version and return
	dmrfid_setOption("db_version", "1.71");		//no need to run other updates
	return 1.71;
}
