<?php
/**
 * WooCommerce Compatibility
 *
 * This code resolves common conflicts between DmRFID and WooCommerce.
 * For more advanced integration, see the DmRFID WooCommerce Add-On.
 * https://www.managertechnology.com.co/add-ons/dmrfid-woocommerce/
 *
 * @since 2.3
 */
 
/**
 * Make sure the DmRFID lost password form
 * doesn't submit to the WC lost password form.
 *
 * @since 2.3
 */
function dmrfid_maybe_remove_wc_lostpassword_url_filter() {
	global $dmrfid_pages;
	
	if ( ! empty( $dmrfid_pages ) && ! empty( $dmrfid_pages['login'] ) && is_page( $dmrfid_pages['login'] ) ) {
		remove_filter( 'lostpassword_url', 'wc_lostpassword_url', 10, 1 );		
	}
}	
add_action( 'wp', 'dmrfid_maybe_remove_wc_lostpassword_url_filter' );
