<?php
/**
 * Prep the ReCAPTCHA library if needed.
 * Fires on the wp hook.
 */
function dmrfid_init_recaptcha() {
	//don't load if setting is off
	global $recaptcha, $recaptcha_validated;
	$recaptcha = dmrfid_getOption( 'recaptcha' );
	if ( empty( $recaptcha ) ) {
		return;
	}
	
	//don't load unless we're on the checkout page
	if ( ! dmrfid_is_checkout() ) {
		return;
	}
	
	//check for validation
	$recaptcha_validated = dmrfid_get_session_var( 'dmrfid_recaptcha_validated' );
	if ( ! empty( $recaptcha_validated ) ) {
	    $recaptcha = false;
    }

	//captcha is needed. set up functions to output
	if($recaptcha) {
		global $recaptcha_publickey, $recaptcha_privatekey;
		
		require_once(DMRFID_DIR . '/includes/lib/recaptchalib.php' );
		
		function dmrfid_recaptcha_get_html ($pubkey, $error = null, $use_ssl = false) {

			// Figure out language.
			$locale = get_locale();
			if(!empty($locale)) {
				$parts = explode("_", $locale);
				$lang = $parts[0];
			} else {
				$lang = "en";	
			}
			$lang = apply_filters( 'dmrfid_recaptcha_lang', $lang );

			// Check which version of ReCAPTCHA we are using.
			$recaptcha_version = dmrfid_getOption( 'recaptcha_version' ); 

			if( $recaptcha_version == '3_invisible' ) { ?>
				<div class="g-recaptcha" data-sitekey="<?php echo $pubkey;?>" data-size="invisible" data-callback="onSubmit"></div>
				<script type="text/javascript">															
					var dmrfid_recaptcha_validated = false;
					var dmrfid_recaptcha_onSubmit = function(token) {
						if ( dmrfid_recaptcha_validated ) {
							jQuery('#dmrfid_form').submit();
							return;
						} else {
							jQuery.ajax({
							url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
							type: 'GET',
							timeout: 30000,
							dataType: 'html',
							data: {
								'action': 'dmrfid_validate_recaptcha',
								'g-recaptcha-response': token,
							},
							error: function(xml){
								alert('Error validating ReCAPTCHA.');
							},
							success: function(response){
								if ( response == '1' ) {
									dmrfid_recaptcha_validated = true;
									
									//get a new token to be submitted with the form
									grecaptcha.execute();
								} else {
									dmrfid_recaptcha_validated = false;
									
									//warn user validation failed
									alert( 'ReCAPTCHA validation failed. Try again.' );
									
									//get a new token to be submitted with the form
									grecaptcha.execute();
								}
							}
							});
						}						
	        		};

					var dmrfid_recaptcha_onloadCallback = function() {
						// Render on main submit button.
						grecaptcha.render('dmrfid_btn-submit', {
	            		'sitekey' : '<?php echo $pubkey;?>',
	            		'callback' : dmrfid_recaptcha_onSubmit
	          			});
						
						// Update other submit buttons.
						var submit_buttons = jQuery('.dmrfid_btn-submit-checkout');
						submit_buttons.each(function() {
							if(jQuery(this).attr('id') != 'dmrfid_btn-submit') {
								jQuery(this).click(function(event) {
									event.preventDefault();
									grecaptcha.execute();
								});
							}
						});
	        		};
	    		 </script>
				 <script type="text/javascript"
	 				src="https://www.google.com/recaptcha/api.js?onload=dmrfid_recaptcha_onloadCallback&hl=<?php echo $lang;?>&render=explicit" async defer>
	 			</script>
			<?php } else { ?>
				<div class="g-recaptcha" data-sitekey="<?php echo $pubkey;?>"></div>
				<script type="text/javascript"
					src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang;?>">
				</script>
			<?php }				
		}
		
		//for templates using the old recaptcha_get_html
		if( ! function_exists( 'recaptcha_get_html' ) ) {
			function recaptcha_get_html( $pubkey, $error = null, $use_ssl = false ) {
				return dmrfid_recaptcha_get_html( $pubkey, $error, $use_ssl );
			}
		}
		
		$recaptcha_publickey = dmrfid_getOption( 'recaptcha_publickey' );
		$recaptcha_privatekey = dmrfid_getOption( 'recaptcha_privatekey' );
	}
}
add_action( 'wp', 'dmrfid_init_recaptcha', 5 );

/**
 * AJAX Method to Validate a ReCAPTCHA Response Token
 */
function dmrfid_wp_ajax_validate_recaptcha() {
	require_once( DMRFID_DIR . '/includes/lib/recaptchalib.php' );
	
	$recaptcha_privatekey = dmrfid_getOption( 'recaptcha_privatekey' );
	
	$reCaptcha = new dmrfid_ReCaptcha( $recaptcha_privatekey );
	$resp      = $reCaptcha->verifyResponse( $_SERVER['REMOTE_ADDR'], $_REQUEST['g-recaptcha-response'] );

	if ( $resp->success ) {
	    dmrfid_set_session_var( 'dmrfid_recaptcha_validated', true );
		echo "1";
	} else {
		echo "0";
	}
	
	exit;	
} 
add_action( 'wp_ajax_nopriv_dmrfid_validate_recaptcha', 'dmrfid_wp_ajax_validate_recaptcha' );
add_action( 'wp_ajax_dmrfid_validate_recaptcha', 'dmrfid_wp_ajax_validate_recaptcha' );

function dmrfid_after_checkout_reset_recaptcha() {
    dmrfid_unset_session_var( 'dmrfid_recaptcha_validated' );
}
add_action( 'dmrfid_after_checkout', 'dmrfid_after_checkout_reset_recaptcha' );