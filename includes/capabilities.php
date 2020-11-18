<?php
//make sure administrators have correct capabilities
function dmrfid_check_admin_capabilities(){
    // Grab the defined (needed) admin capabilities
    $roles = dmrfid_get_capability_defs('administrator');

    $caps_configured = true;

    // check whether the current user has those capabilities already
    foreach( $roles as $r ){ $caps_configured = $caps_configured && current_user_can($r); }

    // if not, set the
    if ( false === $caps_configured && current_user_can('administrator')){ dmrfid_set_capabilities_for_role('administrator'); }
}
add_action('admin_init', 'dmrfid_check_admin_capabilities', 5, 2);

// use the capability definition for $role_name and add/remove capabilities as requested
function dmrfid_set_capabilities_for_role( $role_name, $action = 'enable' )
{
    $role = get_role( $role_name );
    if ( empty( $role ) ) { 
        // Role does not exist.
        return false;
    }

    $cap_array = dmrfid_get_capability_defs( $role_name );

    // Iterate through the relevant caps for the role & add or remove them
    foreach( $cap_array as $cap_name ) {
        if ( $action == 'enable' )
            $role->add_cap( $cap_name );

        if ( $action == 'disable' )
            $role->remove_cap( $cap_name );
    }
    return true;
}

// used to define what capabilities goes with what role.
function dmrfid_get_capability_defs($role)
{
    // TODO: Add other standard roles (if/when needed)

    // caps for the administrator role
    $cap_array = array(
        'dmrfid_memberships_menu',
        'dmrfid_dashboard',
        'dmrfid_membershiplevels',
        'dmrfid_edit_memberships',
        'dmrfid_pagesettings',
        'dmrfid_paymentsettings',
        'dmrfid_emailsettings',
        'dmrfid_advancedsettings',
        'dmrfid_addons',
        'dmrfid_memberslist',
        'dmrfid_memberslistcsv',
        'dmrfid_reports',
        'dmrfid_orders',
        'dmrfid_orderscsv',
        'dmrfid_discountcodes',
        'dmrfid_updates',
        'dmrfid_devices',
        'dmrfid_apir',
    );

    return apply_filters( "dmrfid_assigned_{$role}_capabilities", $cap_array);
}
