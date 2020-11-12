<?php
/*
	Code that runs on the init, set_current_user, or wp hooks to set up DmRFID
*/
//init code
function dmrfid_init() {
	require_once(DMRFID_DIR . '/includes/countries.php');
	require_once(DMRFID_DIR . '/includes/states.php');
	require_once(DMRFID_DIR . '/includes/currencies.php');

	global $dmrfid_pages, $dmrfid_core_pages, $dmrfid_ready, $dmrfid_currencies, $dmrfid_currency, $dmrfid_currency_symbol;
	$dmrfid_pages = array();
	$dmrfid_pages["account"] = dmrfid_getOption("account_page_id");
	$dmrfid_pages["billing"] = dmrfid_getOption("billing_page_id");
	$dmrfid_pages["cancel"] = dmrfid_getOption("cancel_page_id");
	$dmrfid_pages["checkout"] = dmrfid_getOption("checkout_page_id");
	$dmrfid_pages["confirmation"] = dmrfid_getOption("confirmation_page_id");
	$dmrfid_pages["invoice"] = dmrfid_getOption("invoice_page_id");
	$dmrfid_pages["levels"] = dmrfid_getOption("levels_page_id");
	$dmrfid_pages["login"] = dmrfid_getOption("login_page_id");
	$dmrfid_pages["member_profile_edit"] = dmrfid_getOption("member_profile_edit_page_id");

	//save this in case we want a clean version of the array with just the core pages
	$dmrfid_core_pages = $dmrfid_pages;

	$dmrfid_ready = dmrfid_is_ready();

	/**
	 * This action is documented in /adminpages/pagesettings.php
	 */
	$extra_pages = apply_filters('dmrfid_extra_page_settings', array());
	foreach($extra_pages as $name => $page)
		$dmrfid_pages[$name] = dmrfid_getOption($name . '_page_id');


	//set currency
	$dmrfid_currency = dmrfid_getOption("currency");
	if(!$dmrfid_currency)
	{
		global $dmrfid_default_currency;
		$dmrfid_currency = $dmrfid_default_currency;
	}

	//figure out what symbol to show for currency
	if(!empty($dmrfid_currencies[$dmrfid_currency]) && is_array($dmrfid_currencies[$dmrfid_currency])) {
		if ( isset( $dmrfid_currencies[$dmrfid_currency]['symbol'] ) ) {
			$dmrfid_currency_symbol = $dmrfid_currencies[$dmrfid_currency]['symbol'];
		} else {
			$dmrfid_currency_symbol = '';
		}
	} elseif(!empty($dmrfid_currencies[$dmrfid_currency]) && strpos($dmrfid_currencies[$dmrfid_currency], "(") !== false)
		$dmrfid_currency_symbol = dmrfid_getMatches("/\((.*)\)/", $dmrfid_currencies[$dmrfid_currency], true);
	else
		$dmrfid_currency_symbol = $dmrfid_currency . " ";	//just use the code
}
add_action("init", "dmrfid_init");

//this code runs after $post is set, but before template output
function dmrfid_wp()
{
	if(!is_admin())
	{
		global $post, $dmrfid_pages, $dmrfid_core_pages, $dmrfid_page_name, $dmrfid_page_id, $dmrfid_body_classes;

		//no pages yet?
		if(empty($dmrfid_pages))
			return;

		//run the appropriate preheader function
		foreach($dmrfid_core_pages as $dmrfid_page_name => $dmrfid_page_id)
		{
			if(!empty($post->post_content) && strpos($post->post_content, "[dmrfid_" . $dmrfid_page_name . "]") !== false)
			{
				//preheader
				require_once(DMRFID_DIR . "/preheaders/" . $dmrfid_page_name . ".php");

				//add class to body
				$dmrfid_body_classes[] = "dmrfid-" . str_replace("_", "-", $dmrfid_page_name);

				//shortcode
				function dmrfid_pages_shortcode($atts, $content=null, $code="")
				{
					global $dmrfid_page_name;
					$temp_content = dmrfid_loadTemplate($dmrfid_page_name, 'local', 'pages');
					return apply_filters("dmrfid_pages_shortcode_" . $dmrfid_page_name, $temp_content);
				}
				add_shortcode("dmrfid_" . $dmrfid_page_name, "dmrfid_pages_shortcode");
				break;	//only the first page found gets a shortcode replacement
			}
			elseif(!empty($dmrfid_page_id) && is_page($dmrfid_page_id))
			{
				//add class to body
				$dmrfid_body_classes[] = "dmrfid-" . str_replace("_", "-", $dmrfid_page_name);
				
				//shortcode has params, but we still want to load the preheader
				require_once(DMRFID_DIR . "/preheaders/" . $dmrfid_page_name . ".php");
			}
		}
	}
}
add_action("wp", "dmrfid_wp", 1);

/*
	Add DmRFID page names to the BODY class.
*/
function dmrfid_body_class($classes)
{
	global $dmrfid_body_classes;

	if(is_array($dmrfid_body_classes))
		$classes = array_merge($dmrfid_body_classes, $classes);

	return $classes;
}
add_filter("body_class", "dmrfid_body_class");

//add membership level to current user object
function dmrfid_set_current_user()
{
	//this code runs at the beginning of the plugin
	global $current_user, $wpdb;
	wp_get_current_user();
	$id = intval($current_user->ID);
	if($id)
	{
		$current_user->membership_level = dmrfid_getMembershipLevelForUser($current_user->ID);
		if(!empty($current_user->membership_level))
		{
			$current_user->membership_level->categories = dmrfid_getMembershipCategories($current_user->membership_level->ID);
		}
		$current_user->membership_levels = dmrfid_getMembershipLevelsForUser($current_user->ID);
	}

	//hiding ads?
	$hideads = dmrfid_getOption("hideads");
	$hideadslevels = dmrfid_getOption("hideadslevels");
	if(!is_array($hideadslevels))
		$hideadslevels = explode(",", $hideadslevels);
	if($hideads == 1 && dmrfid_hasMembershipLevel() || $hideads == 2 && dmrfid_hasMembershipLevel($hideadslevels))
	{
		//disable ads in ezAdsense
		if(class_exists("ezAdSense"))
		{
			global $ezCount, $urCount;
			$ezCount = 100;
			$urCount = 100;
		}

		//disable ads in Easy Adsense (newer versions)
		if(class_exists("EzAdSense"))
		{
			global $ezAdSense;
			$ezAdSense->ezCount = 100;
			$ezAdSense->urCount = 100;
		}

		//set a global variable to hide ads
		global $dmrfid_display_ads;
		$dmrfid_display_ads = false;
	}
	else
	{
		global $dmrfid_display_ads;
		$dmrfid_display_ads = true;
	}

	do_action("dmrfid_after_set_current_user");
}
add_action('set_current_user', 'dmrfid_set_current_user');
add_action('init', 'dmrfid_set_current_user');

/*
 * Add Membership Level to Users page in WordPress dashboard.
 */
function dmrfid_manage_users_columns($columns) {
    $columns['dmrfid_membership_level'] = __('Membership Level', 'digital-members-rfid' );
    return $columns;
}

function dmrfid_sortable_column($columns)
{
	// $columns['dmrfid_membership_level'] = ['level', 'desc'];
	$columns['dmrfid_membership_level'] = array( 'level', 'desc' );
	return $columns;
}

function dmrfid_manage_users_custom_column($column_data, $column_name, $user_id) {

    if($column_name == 'dmrfid_membership_level') {
        $levels = dmrfid_getMembershipLevelsForUser($user_id);
        $level_names = array();
        if(!empty($levels)) {
            foreach($levels as $key => $level)
                $level_names[] = $level->name;
            $column_data = implode(', ', $level_names);
        }
        else
            $column_data = __('None', 'digital-members-rfid' );
    }
    return $column_data;
}

function dmrfid_sortable_column_query($query) {
    global $wpdb;

	$vars = $query->query_vars;

	if($vars['orderby'] == 'level'){
		$query->query_from .= " LEFT JOIN {$wpdb->prefix}dmrfid_memberships_users AS dmrfid_mu ON {$wpdb->prefix}users.ID = dmrfid_mu.user_id AND dmrfid_mu.status = 'active'";
		$query->query_orderby = "ORDER BY dmrfid_mu.membership_id " . $vars['order'] . ", {$wpdb->prefix}users.user_registered";
	}

}

add_filter('manage_users_columns', 'dmrfid_manage_users_columns');
add_filter('manage_users_custom_column', 'dmrfid_manage_users_custom_column', 10, 3);
add_filter( 'manage_users_sortable_columns', 'dmrfid_sortable_column' );
add_action('pre_user_query','dmrfid_sortable_column_query');
