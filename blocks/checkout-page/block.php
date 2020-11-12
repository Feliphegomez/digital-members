<?php
/**
 * Sets up checkout-page block, does not format frontend
 *
 * @package blocks/checkout-page
 **/

namespace DmRFID\blocks\checkout_page;

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
	// Need to explicitly register the default level meta
	register_meta( 'post', 'dmrfid_default_level', array(
	   'show_in_rest' => true,
	   'single' => true,
	   'type' => 'integer',
   	) );
	
	// Hook server side rendering into render callback.
	register_block_type( 'dmrfid/checkout-page', [
		'render_callback' => __NAMESPACE__ . '\render_dynamic_block',
	] );
}
add_action( 'init', __NAMESPACE__ . '\register_dynamic_block' );

/**
 * Server rendering for checkout-page block.
 *
 * @param array $attributes contains level.
 * @return string
 **/
function render_dynamic_block( $attributes ) {
	return dmrfid_loadTemplate( 'checkout', 'local', 'pages' );
}

/**
 * Load preheaders/checkout.php if a page has the checkout block.
 */
function load_checkout_preheader() {
	if ( has_block( 'dmrfid/checkout-page' ) ) {
		require_once( DMRFID_DIR . "/preheaders/checkout.php" );
	}
}
add_action( 'wp', __NAMESPACE__ . '\load_checkout_preheader', 1 );
