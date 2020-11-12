<?php
/*
	Loading a service?
*/
/*
	Note: The applydiscountcode goes through the site_url() instead of admin-ajax to avoid HTTP/HTTPS issues.
*/
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "applydiscountcode")
{		
	function dmrfid_applydiscountcode_init()
	{
		require_once(dirname(__FILE__) . "/../services/applydiscountcode.php");	
		exit;
	}
	add_action("init", "dmrfid_applydiscountcode_init", 11);
}
function dmrfid_wp_ajax_authnet_silent_post()
{		
	require_once(dirname(__FILE__) . "/../services/authnet-silent-post.php");	
	exit;	
}
add_action('wp_ajax_nopriv_authnet_silent_post', 'dmrfid_wp_ajax_authnet_silent_post');
add_action('wp_ajax_authnet_silent_post', 'dmrfid_wp_ajax_authnet_silent_post');
function dmrfid_wp_ajax_getfile()
{
	require_once(dirname(__FILE__) . "/../services/getfile.php");	
	exit;	
}
add_action('wp_ajax_nopriv_getfile', 'dmrfid_wp_ajax_getfile');
add_action('wp_ajax_getfile', 'dmrfid_wp_ajax_getfile');
function dmrfid_wp_ajax_ipnhandler()
{
	require_once(dirname(__FILE__) . "/../services/ipnhandler.php");	
	exit;	
}
add_action('wp_ajax_nopriv_ipnhandler', 'dmrfid_wp_ajax_ipnhandler');
add_action('wp_ajax_ipnhandler', 'dmrfid_wp_ajax_ipnhandler');
function dmrfid_wp_ajax_stripe_webhook()
{
	require_once(dirname(__FILE__) . "/../services/stripe-webhook.php");	
	exit;	
}
add_action('wp_ajax_nopriv_stripe_webhook', 'dmrfid_wp_ajax_stripe_webhook');
add_action('wp_ajax_stripe_webhook', 'dmrfid_wp_ajax_stripe_webhook');
function dmrfid_wp_ajax_braintree_webhook()
{
	require_once(dirname(__FILE__) . "/../services/braintree-webhook.php");	
	exit;	
}
add_action('wp_ajax_nopriv_braintree_webhook', 'dmrfid_wp_ajax_braintree_webhook');
add_action('wp_ajax_braintree_webhook', 'dmrfid_wp_ajax_braintree_webhook');
function dmrfid_wp_ajax_twocheckout_ins()
{
	require_once(dirname(__FILE__) . "/../services/twocheckout-ins.php");	
	exit;	
}
add_action('wp_ajax_nopriv_twocheckout-ins', 'dmrfid_wp_ajax_twocheckout_ins');
add_action('wp_ajax_twocheckout-ins', 'dmrfid_wp_ajax_twocheckout_ins');
function dmrfid_wp_ajax_memberlist_csv()
{
	require_once(dirname(__FILE__) . "/../adminpages/memberslist-csv.php");	
	exit;	
}
add_action('wp_ajax_memberslist_csv', 'dmrfid_wp_ajax_memberlist_csv');
function dmrfid_wp_ajax_orders_csv()
{
	require_once(dirname(__FILE__) . "/../adminpages/orders-csv.php");	
	exit;	
}
add_action('wp_ajax_orders_csv', 'dmrfid_wp_ajax_orders_csv');

/**
 * Load the Orders print view.
 *
 * @since 1.8.6
 */
function dmrfid_orders_print_view() {
	require_once(dirname(__FILE__) . "/../adminpages/orders-print.php");
	exit;
}
add_action('wp_ajax_dmrfid_orders_print_view', 'dmrfid_orders_print_view');

/**
 * Get order JSON.
 *
 * @since 1.8.6
 */
function dmrfid_get_order_json() {
	$order_id = $_REQUEST['order_id'];
	$order = new MemberOrder($order_id);
	echo json_encode($order);
	exit;
}
add_action('wp_ajax_dmrfid_get_order_json', 'dmrfid_get_order_json');

function dmrfid_update_level_order() {
	
	$level_order = null;
	
	if ( isset( $_REQUEST['level_order'] ) && is_array( $_REQUEST['level_order'] ) ) {
		$level_order = array_map( 'intval', $_REQUEST['level_order'] );
		$level_order = implode(',', $level_order );
	} else if ( isset( $_REQUEST['level_order'] ) ) {
		$level_order = sanitize_text_field( $_REQUEST['level_order'] );
	}
	
	echo dmrfid_setOption('level_order', $level_order);
    exit;
}
add_action('wp_ajax_dmrfid_update_level_order', 'dmrfid_update_level_order');
