<?php

/*
	Copyright 2011	Stranger Studios	(email : jason@strangerstudios.com)
	GPLv2 Full license details in license.txt
*/


/**
 * A general function to start sessions for Digital Members RFID.
 * @since 1.9.2
 */
function dmrfid_start_session() {
    // If headers were already sent, we can't use sessions.
	if ( headers_sent() ) {
		return;
    }

    //if the session hasn't been started yet, start it (ignore if running from command line)
    if (!defined('DMRFID_USE_SESSIONS') || DMRFID_USE_SESSIONS == true) {
        if (defined('STDIN')) {
            //command line
        } else {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
            } else {
                if (!session_id()) {
                    session_start();
                }
            }
        }
    }
}

add_action('dmrfid_checkout_preheader_before_get_level_at_checkout', 'dmrfid_start_session', -1);

/**
 * Close the session object for new updates
 * @since 1.9.2
 */
function dmrfid_close_session() {
    if (!defined('DMRFID_USE_SESSIONS') || DMRFID_USE_SESSIONS == true) {
        if (defined('STDIN')) {
            //command line
        } else {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                if (session_status() == PHP_SESSION_ACTIVE) {
                    session_write_close();
                }
            } else {
                if (session_id()) {
                    session_write_close();
                }
            }
        }
    }
}
add_action('dmrfid_after_checkout', 'dmrfid_close_session', 32768);

/**
 * Set a session variable.
 *
 * @since 2.1.0
 *
 * TODO: Update docblock.
 */
function dmrfid_set_session_var($key, $value) {
    dmrfid_start_session();
    $_SESSION[$key] = $value;
}

/**
 * Get a session variable.
 *
 * @since 2.1.0
 *
 * TODO: Update docblock.
 */
function dmrfid_get_session_var( $key ) {
    dmrfid_start_session();
	if ( ! empty( $_SESSION ) && isset( $_SESSION[$key] ) ) {
		return  $_SESSION[$key];
	} else {
		return false;
	}
}

/**
 * Unset a session variable.
 *
 * @since 2.1.0
 *
 * TODO: Update docblock.
 */
function dmrfid_unset_session_var($key) {
    dmrfid_start_session();
    unset($_SESSION[$key]);
}
