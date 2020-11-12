<?php
/**
 * Theme My Login Compatibility.
 * Supports version 6.4 and latest only.
 * @since 2.3
 */

 /**
  * Force login redirect to go to TML page instead.
  * @since 2.3
  */
function dmrfid_tml_login_redirect( $login_url, $redirect ) {

	if ( function_exists( 'tml_get_action_url') ) {
		$login_url = tml_get_action_url( 'login' ); // support 7.x
	} else {
		$login_url = Theme_My_Login::get_page_link( 'login' ); // support < 7.x
	}

	// add the redirect/referrer back into login URL.
	if ( ! empty( $redirect ) ) {
		$login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url ) ;
	}
	
	return $login_url;
}
add_filter( 'wp_login_url', 'dmrfid_tml_login_redirect', 55, 2 );

/**
 * Remove frontend hooks from Digital Members RFID includes/login.php when TML is activated. Give preference to TML.
 * @since 2.3
 */
function dmrfid_remove_frontend_login_hooks() {

	if ( apply_filters( 'dmrfid_remove_frontend_login_hooks', false ) ) {
		remove_action( 'wp_login_failed', 'dmrfid_login_failed', 10, 2 );
		remove_filter( 'authenticate', 'dmrfid_authenticate_username_password', 30, 3);
		remove_filter( 'retrieve_password_message', 'dmrfid_password_reset_email_filter', 10, 4 );
		remove_action( 'login_form_rp', 'dmrfid_do_password_reset' );
		remove_action( 'login_form_resetpass', 'dmrfid_do_password_reset' );
		remove_action( 'login_form_rp', 'dmrfid_reset_password_redirect' );
		remove_action( 'login_form_resetpass', 'dmrfid_reset_password_redirect' );
		remove_action( 'login_form_lostpassword', 'dmrfid_lost_password_redirect' );
		remove_action( 'wp', 'dmrfid_login_head' );
		remove_action( 'login_init', 'dmrfid_login_head' );
	}

}
add_action( 'init', 'dmrfid_remove_frontend_login_hooks' );

/**
 * Adjust redirects when Theme My Login is active.
 * @since 2.3
 */
function dmrfid_tml_login_head() {
	global $dmrfid_pages;
	$login_redirect = apply_filters( "dmrfid_login_redirect", true );
	
	if ( ! is_user_logged_in() && dmrfid_is_login_page() && ! empty( $dmrfid_pages['account'] ) && is_page( $dmrfid_pages['account'] ) ) {
		if ( function_exists( 'tml_get_action_url') ) {
			$login_url = tml_get_action_url( 'login' ); // support 7.x
		} else {
			$login_url = Theme_My_Login::get_page_link( 'login' ); // support < 7.x
		}
	
		wp_redirect( $login_url );
		exit;
	}

    if ( class_exists("Theme_My_Login") && method_exists('Theme_My_Login', 'is_tml_page') && (Theme_My_Login::is_tml_page("register") || Theme_My_Login::is_tml_page("login")) ||
    function_exists( 'tml_is_action' ) && ( tml_is_action( 'register' ) || tml_is_action( 'login' ) ) && $login_redirect ){

        if ( isset($_REQUEST['action']) && $_REQUEST['action'] == "register" || 
        isset($_REQUEST['registration']) && $_REQUEST['registration'] == "disabled"	||
        !is_admin() && class_exists("Theme_My_Login") && method_exists('Theme_My_Login', 'is_tml_page') && Theme_My_Login::is_tml_page("register") ||
        function_exists( 'tml_is_action' ) && tml_is_action( 'register' ) ) {

            //redirect to levels page unless filter is set.
            $link = apply_filters( "dmrfid_register_redirect", dmrfid_url( "levels" ) );	
			if ( ! empty( $link ) ) {
				wp_redirect ( $link );
				exit;
			} else {
				return;	//don't redirect if dmrfid_register_redirect filter returns false or a blank URL
            }

        }

		// Redirect to frontend profile page.
		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == "profile" && is_user_logged_in() ) {
			$link = get_permalink($GLOBALS['theme_my_login']->options->options['page_id']);								
			wp_redirect($link);
			exit;
		}
    }
}
add_action('wp', 'dmrfid_tml_login_head', 20);
add_action('login_init', 'dmrfid_tml_login_head', 20);
