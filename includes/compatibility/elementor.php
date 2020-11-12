<?php

// Include custom settings to restrict Elementor widgets.
require_once( 'elementor/class-dmrfid-elementor.php' );


/**
 * Elementor Compatibility
 */
function dmrfid_elementor_compatibility() {
	// Remove the default the_content filter added to membership level descriptions and confirmation messages in DmRFID.
	remove_filter( 'the_content', 'dmrfid_level_description' );
	remove_filter( 'dmrfid_level_description', 'dmrfid_dmrfid_level_description' );
	remove_filter( 'the_content', 'dmrfid_confirmation_message' );
	remove_filter( 'dmrfid_confirmation_message', 'dmrfid_dmrfid_confirmation_message' );
	
    // Filter members-only content later so that the builder's filters run before DmRFID.
	remove_filter('the_content', 'dmrfid_membership_content_filter', 5);
	add_filter('the_content', 'dmrfid_membership_content_filter', 15);
}

/**
 * Get all available levels for elementor widget setting.
 * @return array Associative array of level ID and name.
 * @since 2.2.6
 */
function dmrfid_elementor_get_all_levels() {

	$levels_array = get_transient( 'dmrfid_elementor_levels_cache' );

	if ( empty( $levels_array ) ) {
		$all_levels = dmrfid_getAllLevels( true, false );

		$levels_array = array();

		$levels_array[0] = __( 'Non-members', 'paid-memberships-pro' );
		foreach( $all_levels as $level ) {
			$levels_array[ $level->id ] = $level->name;
		}

		set_transient( 'dmrfid_elementor_levels_cache', $levels_array, 1 * DAY_IN_SECONDS );
	}
	
	$levels_array = apply_filters( 'dmrfid_elementor_levels_array', $levels_array );

	return $levels_array;
}
add_action( 'plugins_loaded', 'dmrfid_elementor_compatibility', 15 );



function dmrfid_elementor_clear_level_cache( $level_id ) {
	delete_transient( 'dmrfid_elementor_levels_cache' );
}
add_action( 'dmrfid_save_membership_level', 'dmrfid_elementor_clear_level_cache' );
