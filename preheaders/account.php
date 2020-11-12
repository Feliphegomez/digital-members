<?php
global $current_user, $dmrfid_msg, $dmrfid_msgt, $dmrfid_levels, $dmrfid_pages;

// Redirect to login.
if ( ! is_user_logged_in() ) {
	$redirect = apply_filters( 'dmrfid_account_preheader_redirect', dmrfid_login_url( get_permalink( $dmrfid_pages['account'] ) ) );
	if ( $redirect ) {
		wp_redirect( $redirect );
		exit;
	}
}

// Check if we are processing a confirmaction for a Data Request.
$request_id = dmrfid_confirmaction_handler();
if ( $request_id ) {
	$dmrfid_msg = _wp_privacy_account_request_confirmed_message( $request_id );
	$dmrfid_msgt = 'dmrfid_success';
} else {
	$dmrfid_msg = 'What?';
	$dmrfid_msgt = 'dmrfid_error';
}

// Make sure the membership level is set for the user.
if( $current_user->ID ) {
    $current_user->membership_level = dmrfid_getMembershipLevelForUser( $current_user->ID );
}

// Process the msg param.
if ( isset($_REQUEST['msg'] ) ) {
    if ( $_REQUEST['msg'] == 1 ) {
        $dmrfid_msg = __( 'Your membership status has been updated - Thank you!', 'digital-members-rfid' );
    } else {
        $dmrfid_msg = __( 'Sorry, your request could not be completed - please try again in a few moments.', 'digital-members-rfid' );
        $dmrfid_msgt = 'dmrfid_error';
    }
} else {
    $dmrfid_msg = false;
}

/**
 * Check if the current logged in user has a membership level.
 * If not, and the site is using the dmrfid_account_preheader_redirect
 * filter, redirect to that page.
 */
if ( ! empty( $current_user->ID ) && empty( $current_user->membership_level->ID ) ) {
	$redirect = apply_filters( 'dmrfid_account_preheader_redirect', false );
	if ( $redirect ) {
		wp_redirect( $redirect );
		exit;
	}
}

/**
 * Add-Ons might need this global to be set.
 */
$dmrfid_levels = dmrfid_getAllLevels();