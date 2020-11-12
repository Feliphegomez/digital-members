<?php
global $current_user, $dmrfid_invoice;

// Redirect non-user to the login page; pass the Confirmation page as the redirect_to query arg.
if ( ! is_user_logged_in() ) {
	// Get level ID from URL parameter.
	if ( ! empty( $_REQUEST['level'] ) ) {
		$confirmation_url = add_query_arg( 'level', sanitize_text_field( $_REQUEST['level'] ), dmrfid_url( 'confirmation' ) );
	} else {
		$confirmation_url = dmrfid_url( 'confirmation' );
	}
	wp_redirect( add_query_arg( 'redirect_to', urlencode( $confirmation_url ), dmrfid_login_url() ) );
	exit;
}

// Get the membership level for the current user.
if ( $current_user->ID ) {
	$current_user->membership_level = dmrfid_getMembershipLevelForUser($current_user->ID);
}

/*
	Use the filter to add your gateway here if you want to show them a message on the confirmation page while their checkout is pending.
	For example, when PayPal Standard is used, we need to wait for PayPal to send a message through IPN that the payment was accepted.
	In the meantime, the order is in pending status and the confirmation page shows a message RE waiting.
*/
$gateways_with_pending_status = apply_filters('dmrfid_gateways_with_pending_status', array('paypalstandard', 'twocheckout', 'gourl'));
if ( ! dmrfid_hasMembershipLevel() && ! in_array( dmrfid_getGateway(), $gateways_with_pending_status ) ) {
    // Logged in, but doesn't have a level
    $redirect_url = dmrfid_url( 'account' );
    wp_redirect( $redirect_url );
    exit;
}

// If membership is a paying one, get invoice from DB
if ( ! empty( $current_user->membership_level ) && ! dmrfid_isLevelFree( $current_user->membership_level ) ) {
    $dmrfid_invoice = new MemberOrder();
    $dmrfid_invoice->getLastMemberOrder( $current_user->ID, apply_filters( "dmrfid_confirmation_order_status", array( "success", "pending" ) ) );
}
