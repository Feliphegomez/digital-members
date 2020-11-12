<?php
/**
 * Sets up checkout-button block, does not format frontend
 *
 * @package blocks/checkout-button
 **/

namespace DmRFID\blocks\checkout_button;

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

// Only load if Gutenberg is available.
if ( ! function_exists( 'register_block_type' ) ) {
	return;
}

add_action( 'init', __NAMESPACE__ . '\register_dynamic_block' );
/**
 * Register the dynamic block.
 *
 * @since 2.1.0
 *
 * @return void
 */
function register_dynamic_block() {

	// Hook server side rendering into render callback.
	register_block_type( 'dmrfid/checkout-button', [
		'attributes' => array( 'all_levels' => dmrfid_getAllLevels( true, true ) ),
		'render_callback' => __NAMESPACE__ . '\render_dynamic_block',
	] );
}

/**
 * Server rendering for checkout-button block.
 *
 * @param array $attributes contains text, level, and css_class strings.
 * @return string
 **/
function render_dynamic_block( $attributes ) {
	$text      = 'Buy Now';
	$level     = null;
	$css_class = 'dmrfid_btn';

	if ( ! empty( $attributes['level'] ) ) {
		$level = $attributes['level'];
	} else {
		$level = null;
	}

	if ( ! empty( $attributes['text'] ) ) {
		$text = $attributes['text'];
	} else {
		$text = __( 'Buy Now', 'digital-members-rfid' );
	}
	
	if ( ! empty( $attributes['css_class'] ) ) {
		$css_class = $attributes['css_class'];
	} else {
		$css_class = null;
	}

	return( "<span class=\"" . dmrfid_get_element_class( 'span_dmrfid_checkout_button' ) . "\">" . dmrfid_getCheckoutButton( $level, $text, $css_class ) . "</span>" );
}
