<?php

/*
 * Add query var if Stripe is looking for domain association file.
 */
function dmrfid_stripe_apple_pay_rewrite_rule() {
	add_rewrite_rule( '^\.well-known\/apple-developer-merchantid-domain-association$', 'index.php?dmrfid_stripe_apple_pay=true', 'top' );
}
add_action( 'init', 'dmrfid_stripe_apple_pay_rewrite_rule' );

/*
 * Create query var to detect if Stripe is looking for domain association file.
 */
function wpd_add_query_vars( $qvars ) {
	$qvars[] = 'dmrfid_stripe_apple_pay';
	return $qvars;
}
add_filter( 'query_vars', 'wpd_add_query_vars' );

/**
 * If query var is present, serve the domain association file.
 */
function dmrfid_stripe_apple_pay_controller() {
	global $wp_filesystem;

	if ( empty( get_query_var( 'dmrfid_stripe_apple_pay' ) ) ) {
		return;
	}

	require_once ( ABSPATH . '/wp-admin/includes/file.php' );
	WP_Filesystem();
	echo $wp_filesystem->get_contents( DMRFID_DIR . '/includes/lib/stripe-apple-pay/apple-developer-merchantid-domain-association' );
	exit;
}
add_action( 'template_redirect', 'dmrfid_stripe_apple_pay_controller' );

/**
 * Remove trailing slash from WP redirect if serving domain association file.
 */
function dmrfid_stripe_apple_pay_redirect_canonical_filter( $redirect, $request ) {
	if ( ! empty( get_query_var( 'dmrfid_stripe_apple_pay' ) ) ) {
		return false;
	}
	return $redirect;
}
add_filter( 'redirect_canonical', 'dmrfid_stripe_apple_pay_redirect_canonical_filter', 10, 2 );