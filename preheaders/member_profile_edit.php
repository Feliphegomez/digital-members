<?php

// Redirect to login.
if ( ! is_user_logged_in() ) {
	$redirect = apply_filters( 'dmrfid_member_profile_edit_preheader_redirect', dmrfid_login_url() );
	if ( $redirect ) {
		wp_redirect( $redirect );
		exit;
	}
}