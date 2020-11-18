<?php
/**
 * Plugin Name: Digital Members RFID
 * Plugin URI: http://managertechnology.com.co/
 * Description: The RFID compliant member management and membership subscription plugin for WordPress
 * Version: 0.1
 * Author: FelipheGomez
 * Author URI: https://github.com/FelipheGomez
 * Text Domain: digital-digital-rfid
 * Domain Path: /languages
 */
/**
 * Copyright 2020-2021	Stranger Studios
 * (email : feliphegomez@gmail.com)
 * GPLv2 Full license details in license.txt
 */

// version constant
define( 'DMRFID_VERSION', '0.1' );
define( 'DMRFID_USER_AGENT', 'Digital Members RFID v' . DMRFID_VERSION . '; ' . site_url() );
define( 'DMRFID_MIN_PHP_VERSION', '5.6' );

/*
	Includes
*/
define( 'DMRFID_BASE_FILE', __FILE__ );
define( 'DMRFID_DIR', dirname( __FILE__ ) );

require_once( DMRFID_DIR . '/classes/class-deny-network-activation.php' );   // stop DmRFID from being network activated
require_once( DMRFID_DIR . '/includes/sessions.php' );               // start/close PHP seession vars

require_once( DMRFID_DIR . '/includes/localization.php' );           // localization functions
require_once( DMRFID_DIR . '/includes/lib/name-parser.php' );        // parses "Jason Coleman" into firstname=>Jason, lastname=>Coleman
require_once( DMRFID_DIR . '/includes/functions.php' );              // misc functions used by the plugin
require_once( DMRFID_DIR . '/includes/updates.php' );                // database and other updates
require_once( DMRFID_DIR . '/includes/upgradecheck.php' );           // database and other updates
require_once( DMRFID_DIR . '/includes/deprecated.php' );              // deprecated hooks and functions

if ( ! defined( 'DMRFID_LICENSE_SERVER' ) ) {
	require_once( DMRFID_DIR . '/includes/license.php' );            // defines location of addons data and licenses
}

require_once( DMRFID_DIR . '/scheduled/crons.php' );                 // crons for expiring members, sending expiration emails, etc

require_once( DMRFID_DIR . '/classes/class.memberorder.php' );       // class to process and save orders
require_once( DMRFID_DIR . '/classes/class.dmrfidemail.php' );        // setup and filter emails sent by DmRFID
require_once( DMRFID_DIR . '/classes/class-dmrfid-levels.php' );
require_once( DMRFID_DIR . '/classes/class-dmrfid-admin-activity-email.php' );        // setup the admin activity email
require_once( DMRFID_DIR . '/classes/class-dmrfid-api.php' );

require_once( DMRFID_DIR . '/includes/filters.php' );                // filters, hacks, etc, moved into the plugin
require_once( DMRFID_DIR . '/includes/reports.php' );                // load reports for admin (reports may also include tracking code, etc)
require_once( DMRFID_DIR . '/includes/admin.php' );					// admin notices and functionality
require_once( DMRFID_DIR . '/includes/adminpages.php' );             // dashboard pages
require_once( DMRFID_DIR . '/classes/class-dmrfid-members-list-table.php' ); // Members List

if ( version_compare( PHP_VERSION, '5.3.29', '>=' ) ) {
	require_once( DMRFID_DIR . '/blocks/blocks.php' );             	// Gutenberg blocks
}

require_once( DMRFID_DIR . '/includes/services.php' );               // services loaded by AJAX and via webhook, etc
require_once( DMRFID_DIR . '/includes/metaboxes.php' );              // metaboxes for dashboard
require_once( DMRFID_DIR . '/includes/profile.php' );                // edit user/profile fields
require_once( DMRFID_DIR . '/includes/https.php' );                  // code related to HTTPS/SSL
require_once( DMRFID_DIR . '/includes/menus.php' );          		// custom menu functions for DmRFID
require_once( DMRFID_DIR . '/includes/notifications.php' );          // check for notifications at DmRFID, shown in DmRFID settings
require_once( DMRFID_DIR . '/includes/init.php' );                   // code run during init, set_current_user, and wp hooks
require_once( DMRFID_DIR . '/includes/scripts.php' );                // enqueue frontend and admin JS and CSS

require_once( DMRFID_DIR . '/includes/content.php' );                // code to check for memebrship and protect content
require_once( DMRFID_DIR . '/includes/compatibility.php' );          // code to support compatibility for popular page builders
require_once( DMRFID_DIR . '/includes/email.php' );                  // code related to email
require_once( DMRFID_DIR . '/includes/recaptcha.php' );              // load recaptcha files if needed
require_once( DMRFID_DIR . '/includes/cleanup.php' );                // clean things up when deletes happen, etc.
require_once( DMRFID_DIR . '/includes/login.php' );                  // code to redirect away from login/register page
require_once( DMRFID_DIR . '/includes/capabilities.php' );           // manage DmRFID capabilities for roles
require_once( DMRFID_DIR . '/includes/privacy.php' );                // code to aid with user data privacy, e.g. GDPR compliance
require_once( DMRFID_DIR . '/includes/pointers.php' );

require_once( DMRFID_DIR . '/includes/xmlrpc.php' );                 // xmlrpc methods
require_once( DMRFID_DIR . '/includes/rest-api.php' );				// rest API endpoints
require_once( DMRFID_DIR . '/includes/widgets.php' );          		// widgets for DmRFID

require_once( DMRFID_DIR . '/shortcodes/checkout_button.php' );      // [dmrfid_checkout_button] shortcode to show link to checkout for a level
require_once( DMRFID_DIR . '/shortcodes/membership.php' );           // [membership] shortcode to hide/show member content
require_once( DMRFID_DIR . '/shortcodes/dmrfid_account.php' );        // [dmrfid_account] shortcode to show account information
require_once( DMRFID_DIR . '/shortcodes/dmrfid_login.php' );      // [dmrfid_login] shortcode to show a login form or logged in member info and menu.
require_once( DMRFID_DIR . '/shortcodes/dmrfid_member.php' );         // [dmrfid_member] shortcode to show user fields
require_once( DMRFID_DIR . '/shortcodes/dmrfid_member_profile_edit.php' );         // [dmrfid_member_profile_edit] shortcode to allow members to edit their profile

// load gateway
require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway.php' ); // loaded by memberorder class when needed

// load payment gateway class
require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_authorizenet.php' );

if ( version_compare( PHP_VERSION, '5.4.45', '>=' ) ) {
	require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_braintree.php' );
}

require_once( DMRFID_DIR . '/classes/class-dmrfid-discount-codes.php' ); // loaded by memberorder class when needed

require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_check.php' );
require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_cybersource.php' );
require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_payflowpro.php' );
require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_paypal.php' );
require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_paypalexpress.php' );
require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_paypalstandard.php' );

if ( version_compare( PHP_VERSION, '5.3.29', '>=' ) ) {
	require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_stripe.php' );
	require_once( DMRFID_DIR . '/includes/lib/stripe-apple-pay/stripe-apple-pay.php' ); // rewrite rules to set up Apple Pay.
}

require_once( DMRFID_DIR . '/classes/gateways/class.dmrfidgateway_twocheckout.php' );

/*
	Setup the DB and check for upgrades
*/
global $wpdb;

// check if the DB needs to be upgraded
if ( is_admin() || defined('WP_CLI') ) {
	dmrfid_checkForUpgrades();
}

// load plugin updater
if ( is_admin() ) {
	require_once( DMRFID_DIR . '/includes/addons.php' );
}

/*
	Definitions
*/
define( 'SITENAME', str_replace( '&#039;', "'", get_bloginfo( 'name' ) ) );
$urlparts = explode( '//', home_url() );
if ( ! defined( 'SITEURL'  ) ) {
	define( 'SITEURL', $urlparts[1] );
}

if ( ! defined( 'SECUREURL'  ) ) {
	define( 'SECUREURL', str_replace( 'http://', 'https://', get_bloginfo( 'wpurl' ) ) );
}
define( 'DMRFID_URL', plugins_url( '', DMRFID_BASE_FILE ) );
define( 'DMRFID_DOMAIN', dmrfid_getDomainFromURL( site_url() ) );
define( 'PAYPAL_BN_CODE', 'DigitalMembersRFID_SP' );

/*
	Globals
*/
global $gateway_environment;
$gateway_environment = dmrfid_getOption( 'gateway_environment' );


// Returns a list of all available gateway
function dmrfid_gateways() {
	$dmrfid_gateways = array(
		''                  => __( 'Testing Only', 'digital-members-rfid' ),
		'check'             => __( 'Pay by Check', 'digital-members-rfid' ),
		'stripe'            => __( 'Stripe', 'digital-members-rfid' ),
		'paypalexpress'     => __( 'PayPal Express', 'digital-members-rfid' ),
		'paypal'            => __( 'PayPal Website Payments Pro', 'digital-members-rfid' ),
		'payflowpro'        => __( 'PayPal Payflow Pro/PayPal Pro', 'digital-members-rfid' ),
		'paypalstandard'    => __( 'PayPal Standard', 'digital-members-rfid' ),
		'authorizenet'      => __( 'Authorize.net', 'digital-members-rfid' ),
		'braintree'         => __( 'Braintree Payments', 'digital-members-rfid' ),
		'twocheckout'       => __( '2Checkout', 'digital-members-rfid' ),
		'cybersource'       => __( 'Cybersource', 'digital-members-rfid' ),
	);

	if ( dmrfid_onlyFreeLevels() ) {
		$dmrfid_gateways[''] = __( 'Default', 'digital-members-rfid' );
	}

	return apply_filters( 'dmrfid_gateways', $dmrfid_gateways );
}


// when checking levels for users, we save the info here for caching. each key is a user id for level object for that user.
global $all_membership_levels;

// we sometimes refer to this array of levels
// DEPRECATED: Remove this in v3.0.
global $membership_levels;
$membership_levels = dmrfid_getAllLevels( true, true );

/*
	Activation/Deactivation
*/
// we need monthly crons
function dmrfid_cron_schedules_monthly( $schedules ) {
	$schedules['monthly'] = array(
		'interval' => 2635200,
		'display' => __( 'Once a month' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'dmrfid_cron_schedules_monthly' );

// activation
function dmrfid_activation() {
	// schedule crons
	dmrfid_maybe_schedule_event( current_time( 'timestamp' ), 'daily', 'dmrfid_cron_expire_memberships' );
	dmrfid_maybe_schedule_event( current_time( 'timestamp' ) + 1, 'daily', 'dmrfid_cron_expiration_warnings' );
	dmrfid_maybe_schedule_event( current_time( 'timestamp' ), 'monthly', 'dmrfid_cron_credit_card_expiring_warnings' );
	dmrfid_maybe_schedule_event( strtotime( '10:30:00' ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ), 'daily', 'dmrfid_cron_admin_activity_email' );

	dmrfid_set_capabilities_for_role( 'administrator', 'enable' );

	do_action( 'dmrfid_activation' );
}

// deactivation
function dmrfid_deactivation() {
	// remove crons
	wp_clear_scheduled_hook( 'dmrfid_cron_expiration_warnings' );
	wp_clear_scheduled_hook( 'dmrfid_cron_trial_ending_warnings' );
	wp_clear_scheduled_hook( 'dmrfid_cron_expire_memberships' );
	wp_clear_scheduled_hook( 'dmrfid_cron_credit_card_expiring_warnings' );
	wp_clear_scheduled_hook( 'dmrfid_cron_admin_activity_email' );

	// remove caps from admin role
	dmrfid_set_capabilities_for_role( 'administrator', 'disable' );

	do_action( 'dmrfid_deactivation' );
}
register_activation_hook( __FILE__, 'dmrfid_activation' );
register_deactivation_hook( __FILE__, 'dmrfid_deactivation' );
