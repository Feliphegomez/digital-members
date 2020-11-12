<?php
/**
 * Enqueue frontend JavaScript and CSS
 */
function dmrfid_enqueue_scripts() {
    global $dmrfid_pages;
    
    // Frontend styles.
    $frontend_css_rtl = false;
    if(file_exists(get_stylesheet_directory() . "/digital-members-rfid/css/frontend.css")) {
        $frontend_css = get_stylesheet_directory_uri() . "/digital-members-rfid/css/frontend.css";
        if( is_rtl() && file_exists(get_stylesheet_directory() . "/digital-members-rfid/css/frontend-rtl.css") ) {
            $frontend_css_rtl = get_stylesheet_directory_uri() . "/digital-members-rfid/css/frontend-rtl.css";
        }
    } elseif(file_exists(get_template_directory() . "/digital-members-rfid/frontend.css")) {
        $frontend_css = get_template_directory_uri() . "/digital-members-rfid/frontend.css";
        if( is_rtl() && file_exists(get_template_directory() . "/digital-members-rfid/css/frontend-rtl.css") ) {
            $frontend_css_rtl = get_template_directory_uri() . "/digital-members-rfid/css/frontend-rtl.css";
        }
    } else {
        $frontend_css = plugins_url('css/frontend.css',dirname(__FILE__) );
        if( is_rtl() ) {
            $frontend_css_rtl = plugins_url('css/frontend-rtl.css',dirname(__FILE__) );
        }
    }
    wp_enqueue_style('dmrfid_frontend', $frontend_css, array(), DMRFID_VERSION, "screen");
    if( $frontend_css_rtl ) {
        wp_enqueue_style('dmrfid_frontend_rtl', $frontend_css_rtl, array(), DMRFID_VERSION, "screen");
    }

    // Print styles.
    if(file_exists(get_stylesheet_directory() . "/digital-members-rfid/css/print.css"))
        $print_css = get_stylesheet_directory_uri() . "/digital-members-rfid/css/print.css";
    elseif(file_exists(get_template_directory() . "/digital-members-rfid/print.css"))
        $print_css = get_template_directory_uri() . "/digital-members-rfid/print.css";
    else
        $print_css = plugins_url('css/print.css',dirname(__FILE__) );
    wp_enqueue_style('dmrfid_print', $print_css, array(), DMRFID_VERSION, "print");
    
    // Checkout page JS
    if ( dmrfid_is_checkout() ) {
        wp_register_script( 'dmrfid_checkout',
                            plugins_url( 'js/dmrfid-checkout.js', dirname(__FILE__) ),
                            array( 'jquery' ),
                            DMRFID_VERSION );

        wp_localize_script( 'dmrfid_checkout', 'dmrfid', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'ajax_timeout' => apply_filters( 'dmrfid_ajax_timeout', 5000, 'applydiscountcode' ),
            'show_discount_code' => dmrfid_show_discount_code(),
			'discount_code_passed_in' => !empty( $_REQUEST['discount_code'] ),
        ));
        wp_enqueue_script( 'dmrfid_checkout' );
    }
    
    // Change Password page JS 
	$is_change_pass_page = ! empty( $dmrfid_pages['member_profile_edit'] )
							&& is_page( $dmrfid_pages['member_profile_edit'] )
							&& ! empty( $_REQUEST['view'] )
							&& $_REQUEST['view'] === 'change-password';
	$is_reset_pass_page = ! empty( $dmrfid_pages['login'] )
							&& is_page( $dmrfid_pages['login'] )
							&& ! empty( $_REQUEST['action'] )
							&& $_REQUEST['action'] === 'rp';
		
	if ( $is_change_pass_page || $is_reset_pass_page ) {
        wp_register_script( 'dmrfid_login',
                            plugins_url( 'js/dmrfid-login.js', dirname(__FILE__) ),
                            array( 'jquery', 'password-strength-meter' ),
                            DMRFID_VERSION );

        /**
         * Filter to allow weak passwords on the 
         * change password and reset password forms.
         * At this time, this only disables the JS check on the frontend.
         * There is no backend check for weak passwords on those forms.
         * 
         * @since 2.3.3
         *
         * @param bool $allow_weak_passwords    Whether to allow weak passwords.
         */
        $allow_weak_passwords = apply_filters( 'dmrfid_allow_weak_passwords', false );

        wp_localize_script( 'dmrfid_login', 'dmrfid', array(
            'dmrfid_login_page' => 'changepassword',
			'strength_indicator_text' => __( 'Strength Indicator', 'digital-members-rfid' ),
            'allow_weak_passwords' => $allow_weak_passwords ) );
        wp_enqueue_script( 'dmrfid_login' );	
    }
}
add_action( 'wp_enqueue_scripts', 'dmrfid_enqueue_scripts' );

/**
 * Enqueue admin JavaScript and CSS
 */
function dmrfid_admin_enqueue_scripts() {
    // Admin JS
    wp_register_script( 'dmrfid_admin',
                        plugins_url( 'js/dmrfid-admin.js', dirname(__FILE__) ),
                        array( 'jquery', 'jquery-ui-sortable' ),
                        DMRFID_VERSION );
    $all_levels = dmrfid_getAllLevels( true, true );
    $all_level_values_and_labels = array();
    foreach( $all_levels as $level ) {
        $all_level_values_and_labels[] = array( 'value' => $level->id, 'label' => $level->name );
    }
    wp_localize_script( 'dmrfid_admin', 'dmrfid', array(
        'all_levels' => $all_levels,
        'all_level_values_and_labels' => $all_level_values_and_labels
    ));
    wp_enqueue_script( 'dmrfid_admin' );

    // Admin CSS
    $admin_css_rtl = false;
    if(file_exists(get_stylesheet_directory() . "/digital-members-rfid/css/admin.css")) {
        $admin_css = get_stylesheet_directory_uri() . "/digital-members-rfid/css/admin.css";
        if( is_rtl() && file_exists(get_stylesheet_directory() . "/digital-members-rfid/css/admin-rtl.css") ) {
            $admin_css_rtl = get_stylesheet_directory_uri() . "/digital-members-rfid/css/admin-rtl.css";
        }
    } elseif(file_exists(get_template_directory() . "/digital-members-rfid/admin.css")) {
        $admin_css = get_template_directory_uri() . "/digital-members-rfid/admin.css";
        if( is_rtl() && file_exists(get_template_directory() . "/digital-members-rfid/css/admin-rtl.css") ) {
            $admin_css_rtl = get_template_directory_uri() . "/digital-members-rfid/css/admin-rtl.css";
        }
    } else {
        $admin_css = plugins_url('css/admin.css',dirname(__FILE__) );
        if( is_rtl() ) {
            $admin_css_rtl = plugins_url('css/admin-rtl.css',dirname(__FILE__) );
        }
    }
    wp_enqueue_style('dmrfid_admin', $admin_css, array(), DMRFID_VERSION, "screen");
    if( $admin_css_rtl ) {
        wp_enqueue_style('dmrfid_admin_rtl', $admin_css_rtl, array(), DMRFID_VERSION, "screen");
    }
}
add_action( 'admin_enqueue_scripts', 'dmrfid_admin_enqueue_scripts' );