<?php 

/**
 * SiteOrigin Page Builder Compatibility
 */
function dmrfid_siteorigin_compatibility() {
	// Remove the default the_content filter added to membership level descriptions and confirmation messages in DmRFID.
	remove_filter( 'the_content', 'dmrfid_level_description' );
	remove_filter( 'dmrfid_level_description', 'dmrfid_dmrfid_level_description' );
	remove_filter( 'the_content', 'dmrfid_confirmation_message' );
	remove_filter( 'dmrfid_confirmation_message', 'dmrfid_dmrfid_confirmation_message' );
	
	// Filter members-only content later so that the builder's filters run before DmRFID.
	remove_filter( 'the_content', 'dmrfid_membership_content_filter', 5 );
	add_filter( 'the_content', 'dmrfid_membership_content_filter', 15 );
}
add_action( 'init', 'dmrfid_siteorigin_compatibility' );
