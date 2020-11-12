<?php
/*
	Code related to HTTPS/SSL
*/

/**
 * Check if we have set the $isapage variable,
 * and if so prevents WP from sending a 404.
 */
function dmrfid_status_filter( $s ) {
	global $isapage;
	if($isapage && strpos( $s, '404' ) )
		return false;	//don't send the 404
	else
		return $s;
}
add_filter('status_header', 'dmrfid_status_filter');

/**
 * Filters links/etc to add HTTPS to URL if needed.
 */
function dmrfid_https_filter( $s ) {
	global $besecure;
	$besecure = apply_filters( 'dmrfid_besecure', $besecure );
		
	if( $besecure || is_ssl() )
		return str_replace( 'http:', 'https:', $s );
	else
		return str_replace( 'https:', 'http:', $s );
}
add_filter('bloginfo_url', 'dmrfid_https_filter');
add_filter('wp_list_pages', 'dmrfid_https_filter');
add_filter('option_home', 'dmrfid_https_filter');
add_filter('option_siteurl', 'dmrfid_https_filter');
add_filter('logout_url', 'dmrfid_https_filter');
add_filter('login_url', 'dmrfid_https_filter');
add_filter('home_url', 'dmrfid_https_filter');

/**
 * This function updates the besecure global
 * with post data and redirects if needed.
 * Will only redirect if the Force SSL setting is true.
 */
function dmrfid_besecure() {
	global $besecure, $post;
	
	//check the post option
	if( ! is_admin() && ! empty( $post->ID ) && ! $besecure ) {
		$besecure = get_post_meta( $post->ID, 'besecure', true );
	}

	//if forcing ssl on admin, be secure in admin and login page
	if( ! $besecure && force_ssl_admin() && ( is_admin() || dmrfid_is_login_page() ) ) {
		$besecure = true;
	}

	$besecure = apply_filters( 'dmrfid_besecure', $besecure );

	$use_ssl = dmrfid_getOption( 'use_ssl' );
	if( $use_ssl == 1 ) {
		if( $besecure && ( empty( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == 'off' || $_SERVER['HTTPS'] == 'false' ) ) {
			//need to be secure		
			wp_safe_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			exit;
		} elseif ( ! $besecure && ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' && $_SERVER['HTTPS'] != 'false' ) {
			//don't need to be secure		
			wp_safe_redirect('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
			exit;
		}	
	}
}
add_action( 'wp', 'dmrfid_besecure', 2 );
add_action( 'login_init', 'dmrfid_besecure', 2 );

/**
 * Echo the JavaScript SSL redirect
 * if the Force SSL option is set.
 */
function dmrfid_ssl_javascript_redirect() {
	global $besecure;
	$use_ssl = dmrfid_getOption( 'use_ssl' );
	if( ! is_admin() && $use_ssl == 2 ) {
		if( ! empty( $besecure ) ) {
		?>
			<script lang="JavaScript">
				//needs to be secure
				if (window.location.protocol != "https:")
					window.location.href = "https:" + window.location.href.substring(window.location.protocol.length);
			</script>
		<?php
		} else {
		?>
			<script lang="JavaScript">
				//should be over http
				if (window.location.protocol != "http:")
					window.location.href = "http:" + window.location.href.substring(window.location.protocol.length);
			</script>
		<?php
		}
	}
}
add_action( 'wp_print_scripts', 'dmrfid_ssl_javascript_redirect' );

//If the site URL starts with https:, then force SSL/besecure to true. (Added 1.5.2)
function dmrfid_check_site_url_for_https( $besecure = NULL ) {	
	global $wpdb, $dmrfid_siteurl;

	//need to get this from the database because we filter get_option
	if( empty( $dmrfid_siteurl ) ) {
		$dmrfid_siteurl = $wpdb->get_var( "SELECT option_value FROM $wpdb->options WHERE option_name = 'siteurl' LIMIT 1" );
	}
	
	//entire site is over https?
	if( strpos( $dmrfid_siteurl, 'https:' ) !== false ) {
		$besecure = true;
	}
	
	return $besecure;
}
add_filter( 'dmrfid_besecure', 'dmrfid_check_site_url_for_https' );

//capturing case where a user links to https admin without admin over https
function dmrfid_admin_https_handler() {
	if( ! empty( $_SERVER['HTTPS'] ) ) {
		if( $_SERVER['HTTPS'] && strtolower( $_SERVER['HTTPS'] ) != 'off' && strtolower( $_SERVER['HTTPS'] ) != 'false' && is_admin() ) {
			if( substr( get_option( 'siteurl' ), 0, 5 ) == 'http:' && ! force_ssl_admin() ) {
				//need to redirect to non https
				wp_safe_redirect( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
				exit;
			}
		}
	}
}
add_action( 'init', 'dmrfid_admin_https_handler' );

/*
	This code is for the "nuke" option to make URLs secure on secure pages.
*/
function dmrfid_NuclearHTTPS() {
	//did they choose the option?
	$nuking = dmrfid_getOption( 'nuclear_HTTPS' );
	if(!empty($nuking)) {
		ob_start( 'dmrfid_replaceURLsInBuffer' );
	}
}
add_action( 'init', 'dmrfid_NuclearHTTPS' );

function dmrfid_replaceURLsInBuffer($buffer) {
	global $besecure;
	
	//only swap URLs if this page is secure
	if($besecure) {
		/*
			okay swap out all links like these:
			* http://domain.com
			* http://anysubdomain.domain.com
			* http://any.number.of.sub.domains.domain.com
		*/
		$buffer = preg_replace("/http\:\/\/([a-zA-Z0-9\.\-]*" . str_replace(".", "\.", DMRFID_DOMAIN) . ")/i", "https://$1", $buffer);		
	}
	
	return $buffer;
}