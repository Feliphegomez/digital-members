<?php

global $current_user;

if($current_user->ID)
    $current_user->membership_level = dmrfid_getMembershipLevelForUser($current_user->ID);

//is there a default level to redirect to?
if (defined("DMRFID_DEFAULT_LEVEL"))
    $default_level = intval(DMRFID_DEFAULT_LEVEL);
else
    $default_level = false;

if ($default_level) {
    wp_redirect(dmrfid_url("checkout", "?level=" . $default_level));
    exit;
}

global $wpdb, $dmrfid_msg, $dmrfid_msgt;
if (isset($_REQUEST['msg'])) {
    if ($_REQUEST['msg'] == 1) {
        $dmrfid_msg = __('Your membership status has been updated - Thank you!', 'paid-memberships-pro' );
    } else {
        $dmrfid_msg = __('Sorry, your request could not be completed - please try again in a few moments.', 'paid-memberships-pro' );
        $dmrfid_msgt = "dmrfid_error";
    }
} else {
    $dmrfid_msg = false;
}

global $dmrfid_levels, $dmrfid_level_order;

$dmrfid_levels = dmrfid_getAllLevels(false, true);
$dmrfid_level_order = dmrfid_getOption('level_order');

if(!empty($dmrfid_level_order))
{
    $order = explode(',',$dmrfid_level_order);

    //reorder array
    $reordered_levels = array();
    foreach($order as $level_id) {
        foreach($dmrfid_levels as $key=>$level) {
            if($level_id == $level->id)
                $reordered_levels[$key] = $dmrfid_levels[$key];
        }
    }

    $dmrfid_levels = $reordered_levels;
}

$dmrfid_levels = apply_filters("dmrfid_levels_array", $dmrfid_levels);
