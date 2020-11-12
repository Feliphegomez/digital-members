<?php
/**
 * Sets up confirmation-page block, does not format frontend
 *
 * @package blocks/confirmation-page
 **/

namespace DmRFID\blocks\confirmation_page;

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

// Only load if Gutenberg is available.
if ( ! function_exists( 'register_block_type' ) ) {
	return;
}

/**
 * Register the dynamic block.
 *
 * @since 2.1.0
 *
 * @return void
 */
function register_dynamic_block() {
	// Hook server side rendering into render callback.
	register_block_type( 'dmrfid/confirmation-page', [
		'render_callback' => __NAMESPACE__ . '\render_dynamic_block',
	] );
}
add_action( 'init', __NAMESPACE__ . '\register_dynamic_block' );

/**
 * Server rendering for confirmation-page block.
 *
 * @param array $attributes contains text, level, and css_class strings.
 * @return string
 **/
function render_dynamic_block( $attributes ) {
	return dmrfid_loadTemplate( 'confirmation', 'local', 'pages' );
}

/**
 * Load preheaders/confirmation.php if a page has the checkout block.
 */
function load_confirmation_preheader() {
	if ( has_block( 'dmrfid/confirmation-page' ) ) {
		require_once( DMRFID_DIR . "/preheaders/confirmation.php" );
	}
}
add_action( 'wp', __NAMESPACE__ . '\load_confirmation_preheader', 1 );
