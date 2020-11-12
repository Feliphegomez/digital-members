<?php
/**
 * Get array of DmRFID Capabilities
 */
function dmrfid_getDmRFIDCaps() {
	$dmrfid_caps = array(
		//dmrfid_memberships_menu //this controls viewing the menu itself
		'dmrfid_dashboard',
		'dmrfid_membershiplevels',
		'dmrfid_pagesettings',
		'dmrfid_paymentsettings',
		'dmrfid_emailsettings',
		'dmrfid_advancedsettings',
		'dmrfid_addons',
		'dmrfid_memberslist',
		'dmrfid_reports',
		'dmrfid_orders',
		'dmrfid_discountcodes',
		'dmrfid_updates'
	);

	return $dmrfid_caps;
}

/**
 * Dashboard Menu
 */
function dmrfid_add_pages() {
	global $wpdb;

	//array of all caps in the menu
	$dmrfid_caps = dmrfid_getDmRFIDCaps();

	//the top level menu links to the first page they have access to
	foreach( $dmrfid_caps as $cap ) {
		if( current_user_can( $cap ) ) {
			$top_menu_cap = $cap;
			break;
		}
	}

	if( empty( $top_menu_cap ) ) {
		return;
	}

	// Top level menu
	add_menu_page( __( 'Memberships', 'paid-memberships-pro' ), __( 'Memberships', 'paid-memberships-pro' ), 'dmrfid_memberships_menu', 'dmrfid-dashboard', $top_menu_cap, 'dashicons-groups', 30 );
	
	// Main submenus
	add_submenu_page( 'dmrfid-dashboard', __( 'Dashboard', 'paid-memberships-pro' ), __( 'Dashboard', 'paid-memberships-pro' ), 'dmrfid_dashboard', 'dmrfid-dashboard', 'dmrfid_dashboard' );
	$list_table_hook = add_submenu_page( 'dmrfid-dashboard', __( 'Members', 'paid-memberships-pro' ), __( 'Members', 'paid-memberships-pro' ), 'dmrfid_memberslist', 'dmrfid-memberslist', 'dmrfid_memberslist' );
	add_submenu_page( 'dmrfid-dashboard', __( 'Orders', 'paid-memberships-pro' ), __( 'Orders', 'paid-memberships-pro' ), 'dmrfid_orders', 'dmrfid-orders', 'dmrfid_orders' );
	add_submenu_page( 'dmrfid-dashboard', __( 'Reports', 'paid-memberships-pro' ), __( 'Reports', 'paid-memberships-pro' ), 'dmrfid_reports', 'dmrfid-reports', 'dmrfid_reports' );
	add_submenu_page( 'dmrfid-dashboard', __( 'Settings', 'paid-memberships-pro' ), __( 'Settings', 'paid-memberships-pro' ), 'dmrfid_membershiplevels', 'dmrfid-membershiplevels', 'dmrfid_membershiplevels' );
	add_submenu_page( 'dmrfid-dashboard', __( 'Add Ons', 'paid-memberships-pro' ), __( 'Add Ons', 'paid-memberships-pro' ), 'dmrfid_addons', 'dmrfid-addons', 'dmrfid_addons' );

	// Check License Key for Correct Link Color
	$key = get_option( 'dmrfid_license_key', '' );
	if ( dmrfid_license_isValid( $key, NULL ) ) {
		$span_color = '#33FF00';
	} else {
		$span_color = '#FF3333';
	}
	add_submenu_page( 'dmrfid-dashboard', __( 'License', 'paid-memberships-pro' ), __( '<span style="color: ' . $span_color . '">License</span>', 'paid-memberships-pro' ), 'manage_options', 'dmrfid-license', 'dmrfid_license_settings_page' );

	// Settings tabs
	add_submenu_page( 'admin.php', __( 'Discount Codes', 'paid-memberships-pro' ), __( 'Discount Codes', 'paid-memberships-pro' ), 'dmrfid_discountcodes', 'dmrfid-discountcodes', 'dmrfid_discountcodes' );
	add_submenu_page( 'admin.php', __( 'Page Settings', 'paid-memberships-pro' ), __( 'Page Settings', 'paid-memberships-pro' ), 'dmrfid_pagesettings', 'dmrfid-pagesettings', 'dmrfid_pagesettings' );
	add_submenu_page( 'admin.php', __( 'Payment Settings', 'paid-memberships-pro' ), __( 'Payment Settings', 'paid-memberships-pro' ), 'dmrfid_paymentsettings', 'dmrfid-paymentsettings', 'dmrfid_paymentsettings' );
	add_submenu_page( 'admin.php', __( 'Email Settings', 'paid-memberships-pro' ), __( 'Email Settings', 'paid-memberships-pro' ), 'dmrfid_emailsettings', 'dmrfid-emailsettings', 'dmrfid_emailsettings' );
	add_submenu_page( 'admin.php', __( 'Advanced Settings', 'paid-memberships-pro' ), __( 'Advanced Settings', 'paid-memberships-pro' ), 'dmrfid_advancedsettings', 'dmrfid-advancedsettings', 'dmrfid_advancedsettings' );

	add_action( 'load-' . $list_table_hook, 'dmrfid_list_table_screen_options' );

	//updates page only if needed
	if ( dmrfid_isUpdateRequired() ) {
		add_submenu_page( 'dmrfid-dashboard', __( 'Updates Required', 'paid-memberships-pro' ), __( 'Updates Required', 'paid-memberships-pro' ), 'dmrfid_updates', 'dmrfid-updates', 'dmrfid_updates' );
	}
}
add_action( 'admin_menu', 'dmrfid_add_pages' );

/**
 * Keep the Memberships menu selected on subpages.
 */
function dmrfid_parent_file( $parent_file ) {
	global $parent_file, $plugin_page, $submenu_file;
	
	$dmrfid_settings_tabs = array(
		'dmrfid-membershiplevels',
		'dmrfid-discountcodes',
		'dmrfid-pagesettings',
		'dmrfid-paymentsettings',
		'dmrfid-emailsettings',
		'dmrfid-advancedsettings',
	);
	
	if( isset( $_REQUEST['page']) && in_array( $_REQUEST['page'], $dmrfid_settings_tabs ) ) {
		$parent_file = 'dmrfid-dashboard';
		$plugin_page = 'dmrfid-dashboard';
		$submenu_file = 'dmrfid-membershiplevels';
	}
	
	return $parent_file;
}
add_filter( 'parent_file', 'dmrfid_parent_file' );

/**
 * Admin Bar
 */
function dmrfid_admin_bar_menu() {
	global $wp_admin_bar;

	//view menu at all?
	if ( ! current_user_can( 'dmrfid_memberships_menu' ) || ! is_admin_bar_showing() ) {
		return;
	}
	
	//array of all caps in the menu
	$dmrfid_caps = dmrfid_getDmRFIDCaps();

	//the top level menu links to the first page they have access to
	foreach ( $dmrfid_caps as $cap ) {
		if ( current_user_can( $cap ) ) {
			$top_menu_page = str_replace( '_', '-', $cap );
			break;
		}
	}

	$wp_admin_bar->add_menu(
		array(
			'id' => 'paid-memberships-pro',
			'title' => __( '<span class="ab-icon"></span>Memberships', 'paid-memberships-pro' ),
			'href' => get_admin_url( NULL, '/admin.php?page=' . $top_menu_page )
		) 
	);

	// Add menu item for Dashboard.
	if ( current_user_can( 'dmrfid_dashboard' ) ) {
		$wp_admin_bar->add_menu( 
			array(
				'id' => 'dmrfid-dashboard',
				'parent' => 'paid-memberships-pro',
				'title' => __( 'Dashboard', 'paid-memberships-pro' ),
				'href' => get_admin_url( NULL, '/admin.php?page=dmrfid-dashboard' ) 
			)
		);
	}
	
	// Add menu item for Members List.
	if ( current_user_can( 'dmrfid_memberslist' ) ) {
		$wp_admin_bar->add_menu( 
			array(
				'id' => 'dmrfid-members-list',
				'parent' => 'paid-memberships-pro',
				'title' => __( 'Members', 'paid-memberships-pro' ),
				'href' => get_admin_url( NULL, '/admin.php?page=dmrfid-memberslist' )
			)
		);
	}

	// Add menu item for Orders.
	if ( current_user_can( 'dmrfid_orders' ) ) {
		$wp_admin_bar->add_menu(
			array(
				'id' => 'dmrfid-orders',
				'parent' => 'paid-memberships-pro',
				'title' => __( 'Orders', 'paid-memberships-pro' ),
				'href' => get_admin_url( NULL, '/admin.php?page=dmrfid-orders' )
			)
		);
	}

	// Add menu item for Reports.
	if ( current_user_can( 'dmrfid_reports' ) ) {
		$wp_admin_bar->add_menu(
			array(
				'id' => 'dmrfid-reports',
				'parent' => 'paid-memberships-pro',
				'title' => __( 'Reports', 'paid-memberships-pro' ),
				'href' => get_admin_url( NULL, '/admin.php?page=dmrfid-reports' )
			)
		);
	}

	// Add menu item for Settings.
	if ( current_user_can( 'dmrfid_membershiplevels' ) ) {
		$wp_admin_bar->add_menu(
			array(
				'id' => 'dmrfid-membership-levels',
				'parent' => 'paid-memberships-pro',
				'title' => __( 'Settings', 'paid-memberships-pro' ),
				'href' => get_admin_url( NULL, '/admin.php?page=dmrfid-membershiplevels' )
			)
		);
	}

	// Add menu item for Add Ons.
	if ( current_user_can( 'dmrfid_addons' ) ) {
		$wp_admin_bar->add_menu(
			array(
				'id' => 'dmrfid-addons',
				'parent' => 'paid-memberships-pro',
				'title' => __( 'Add Ons', 'paid-memberships-pro' ),
				'href' => get_admin_url( NULL, '/admin.php?page=dmrfid-addons' )
			)
		);
	}

	// Add menu item for License.
	if ( current_user_can( 'manage_options' ) ) {
		// Check License Key for Correct Link Color
		$key = get_option( 'dmrfid_license_key', '' );
		if ( dmrfid_license_isValid( $key, NULL ) ) {
			$span_color = '#33FF00';
		} else {
			$span_color = '#FF3333';
		}
		$wp_admin_bar->add_menu(
			array(
				'id' => 'dmrfid-license',
				'parent' => 'paid-memberships-pro',
				'title' => __( '<span style="color: ' . $span_color . '; line-height: 26px;">License</span>', 'paid-memberships-pro' ),
				'href' => get_admin_url( NULL, '/admin.php?page=dmrfid-license' )
			)
		);
	}
}
add_action( 'admin_bar_menu', 'dmrfid_admin_bar_menu', 1000);

/**
 * Functions to load pages from adminpages directory
 */
function dmrfid_reports() {
	//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );

	require_once( DMRFID_DIR . '/adminpages/reports.php' );
}

function dmrfid_memberslist() {
	require_once( DMRFID_DIR . '/adminpages/memberslist.php' );
}

function dmrfid_discountcodes() {
	require_once( DMRFID_DIR . '/adminpages/discountcodes.php' );
}

function dmrfid_dashboard() {
	//ensure, that the needed javascripts been loaded to allow drag/drop, expand/collapse and hide/show of boxes
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );

	require_once( DMRFID_DIR . '/adminpages/dashboard.php' );
}

function dmrfid_membershiplevels() {
	require_once( DMRFID_DIR . '/adminpages/membershiplevels.php' );
}

function dmrfid_pagesettings() {
	require_once( DMRFID_DIR . '/adminpages/pagesettings.php' );
}

function dmrfid_paymentsettings() {
	require_once( DMRFID_DIR . '/adminpages/paymentsettings.php' );
}

function dmrfid_emailsettings() {
	require_once( DMRFID_DIR . '/adminpages/emailsettings.php' );
}

function dmrfid_advancedsettings() {
	require_once( DMRFID_DIR . '/adminpages/advancedsettings.php' );
}

function dmrfid_addons() {
	require_once( DMRFID_DIR . '/adminpages/addons.php' );
}

function dmrfid_orders() {
	require_once( DMRFID_DIR . '/adminpages/orders.php' );
}

function dmrfid_license_settings_page() {
	require_once( DMRFID_DIR . '/adminpages/license.php' );
}

function dmrfid_updates() {
	require_once( DMRFID_DIR . '/adminpages/updates.php' );
}

/**
 * Move orphaned pages under the dmrfid-dashboard menu page.
 */
function dmrfid_fix_orphaned_sub_menu_pages( ) {
	global $submenu;

	if ( is_array( $submenu) && array_key_exists( 'dmrfid-membershiplevels', $submenu ) ) {
		$dmrfid_dashboard_submenu = $submenu['dmrfid-dashboard'];	
		$dmrfid_old_memberships_submenu = $submenu['dmrfid-membershiplevels'];
	
		if ( is_array( $dmrfid_dashboard_submenu ) && is_array( $dmrfid_old_memberships_submenu ) ) {
			$submenu['dmrfid-dashboard'] = array_merge( $dmrfid_dashboard_submenu, $dmrfid_old_memberships_submenu );
		}
	}
}
add_action( 'admin_init', 'dmrfid_fix_orphaned_sub_menu_pages', 99 );

/**
 * Add a post display state for special DmRFID pages in the page list table.
 *
 * @param array   $post_states An array of post display states.
 * @param WP_Post $post The current post object.
 */
function dmrfid_display_post_states( $post_states, $post ) {
	// Get assigned page settings.
	global $dmrfid_pages;

	if ( intval( $dmrfid_pages['account'] ) === $post->ID ) {
		$post_states['dmrfid_account_page'] = __( 'Membership Account Page', 'paid-memberships-pro' );
	}

	if ( intval( $dmrfid_pages['billing'] ) === $post->ID ) {
		$post_states['dmrfid_billing_page'] = __( 'Membership Billing Information Page', 'paid-memberships-pro' );
	}

	if ( intval( $dmrfid_pages['cancel'] ) === $post->ID ) {
		$post_states['dmrfid_cancel_page'] = __( 'Membership Cancel Page', 'paid-memberships-pro' );
	}

	if ( intval( $dmrfid_pages['checkout'] ) === $post->ID ) {
		$post_states['dmrfid_checkout_page'] = __( 'Membership Checkout Page', 'paid-memberships-pro' );
	}

	if ( intval( $dmrfid_pages['confirmation'] ) === $post->ID ) {
		$post_states['dmrfid_confirmation_page'] = __( 'Membership Confirmation Page', 'paid-memberships-pro' );
	}

	if ( intval( $dmrfid_pages['invoice'] ) === $post->ID ) {
		$post_states['dmrfid_invoice_page'] = __( 'Membership Invoice Page', 'paid-memberships-pro' );
	}

	if ( intval( $dmrfid_pages['levels'] ) === $post->ID ) {
		$post_states['dmrfid_levels_page'] = __( 'Membership Levels Page', 'paid-memberships-pro' );
	}

	if ( intval( $dmrfid_pages['member_profile_edit'] ) === $post->ID ) {
		$post_states['dmrfid_member_profile_edit_page'] = __( 'Member Profile Edit Page', 'paid-memberships-pro' );
	}

	return $post_states;
}
add_filter( 'display_post_states', 'dmrfid_display_post_states', 10, 2 );

/**
 * Screen options for the List Table
 *
 * Callback for the load-($page_hook_suffix)
 * Called when the plugin page is loaded
 *
 * @since    2.0.0
 */
function dmrfid_list_table_screen_options() {
	global $user_list_table;
	$arguments = array(
		'label'   => __( 'Members Per Page', 'paid-memberships-pro' ),
		'default' => 13,
		'option'  => 'users_per_page',
	);
	add_screen_option( 'per_page', $arguments );
	// instantiate the User List Table
	$user_list_table = new DmRFID_Members_List_Table();
}

/**
 * Add links to the plugin action links
 */
function dmrfid_add_action_links( $links ) {

	//array of all caps in the menu
	$dmrfid_caps = dmrfid_getDmRFIDCaps();

	//the top level menu links to the first page they have access to
	foreach( $dmrfid_caps as $cap ) {
		if ( current_user_can( $cap ) ) {
			$top_menu_page = str_replace( '_', '-', $cap );
			break;
		}
	}

	$new_links = array(
		'<a href="' . get_admin_url( NULL, 'admin.php?page=' . $top_menu_page ) . '">Settings</a>',
	);
	return array_merge( $new_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( DMRFID_DIR . '/paid-memberships-pro.php' ), 'dmrfid_add_action_links' );

/**
 * Add links to the plugin row meta
 */
function dmrfid_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'paid-memberships-pro.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( apply_filters( 'dmrfid_docs_url', 'http://paidmembershipspro.com/documentation/' ) ) . '" title="' . esc_attr( __( 'View DmRFID Documentation', 'paid-memberships-pro' ) ) . '">' . __( 'Docs', 'paid-memberships-pro' ) . '</a>',
			'<a href="' . esc_url( apply_filters( 'dmrfid_support_url', 'http://paidmembershipspro.com/support/' ) ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'paid-memberships-pro' ) ) . '">' . __( 'Support', 'paid-memberships-pro' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'dmrfid_plugin_row_meta', 10, 2 );
