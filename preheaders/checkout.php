<?php
global $post, $gateway, $wpdb, $besecure, $discount_code, $discount_code_id, $dmrfid_level, $dmrfid_levels, $dmrfid_msg, $dmrfid_msgt, $dmrfid_review, $skip_account_fields, $dmrfid_paypal_token, $dmrfid_show_discount_code, $dmrfid_error_fields, $dmrfid_required_billing_fields, $dmrfid_required_user_fields, $wp_version, $current_user;

// we are on the checkout page
add_filter( 'dmrfid_is_checkout', '__return_true' );

//make sure we know current user's membership level
if ( $current_user->ID ) {
	$current_user->membership_level = dmrfid_getMembershipLevelForUser( $current_user->ID );
}

//this var stores fields with errors so we can make them red on the frontend
$dmrfid_error_fields = array();

//blank array for required fields, set below
$dmrfid_required_billing_fields = array();
$dmrfid_required_user_fields    = array();

//was a gateway passed?
if ( ! empty( $_REQUEST['gateway'] ) ) {
	$gateway = sanitize_text_field($_REQUEST['gateway']);
} elseif ( ! empty( $_REQUEST['review'] ) ) {
	$gateway = "paypalexpress";
} else {
	$gateway = dmrfid_getOption( "gateway" );
}

//set valid gateways - the active gateway in the settings and any gateway added through the filter will be allowed
if ( dmrfid_getOption( "gateway", true ) == "paypal" ) {
	$valid_gateways = apply_filters( "dmrfid_valid_gateways", array( "paypal", "paypalexpress" ) );
} else {
	$valid_gateways = apply_filters( "dmrfid_valid_gateways", array( dmrfid_getOption( "gateway", true ) ) );
}

//let's add an error now, if an invalid gateway is set
if ( ! in_array( $gateway, $valid_gateways ) ) {
	$dmrfid_msg  = __( "Invalid gateway.", 'digital-members-rfid' );
	$dmrfid_msgt = "dmrfid_error";
}

/**
 * Action to run extra preheader code before setting checkout level.
 *
 * @since 2.0.5
 */
do_action( 'dmrfid_checkout_preheader_before_get_level_at_checkout' );

//what level are they purchasing? (discount code passed)
$dmrfid_level = dmrfid_getLevelAtCheckout();

/**
 * Action to run extra preheader code after setting checkout level.
 *
 * @since 2.0.5
 * //TODO update docblock
 */
do_action( 'dmrfid_checkout_preheader_after_get_level_at_checkout', $dmrfid_level );

if ( empty( $dmrfid_level->id ) ) {
	wp_redirect( dmrfid_url( "levels" ) );
	exit( 0 );
}

//enqueue some scripts
wp_enqueue_script( 'jquery.creditCardValidator', plugins_url( '/js/jquery.creditCardValidator.js', dirname( __FILE__ ) ), array( 'jquery' ) );

global $wpdb, $current_user, $dmrfid_requirebilling;
//unless we're submitting a form, let's try to figure out if https should be used

if ( ! dmrfid_isLevelFree( $dmrfid_level ) ) {
	//require billing and ssl
	$pagetitle            = __( "Checkout: Payment Information", 'digital-members-rfid' );
	$dmrfid_requirebilling = true;
	$besecure             = dmrfid_getOption( "use_ssl" );
} else {
	//no payment so we don't need ssl
	$pagetitle            = __( "Set Up Your Account", 'digital-members-rfid' );
	$dmrfid_requirebilling = false;
	$besecure             = false;
}

// Allow for filters.
// TODO: docblock.
$dmrfid_requirebilling = apply_filters( 'dmrfid_require_billing', $dmrfid_requirebilling, $dmrfid_level );

//in case a discount code was used or something else made the level free, but we're already over ssl
if ( ! $besecure && ! empty( $_REQUEST['submit-checkout'] ) && is_ssl() ) {
	$besecure = true;
}    //be secure anyway since we're already checking out

//action to run extra code for gateways/etc
do_action( 'dmrfid_checkout_preheader' );

//get all levels in case we need them
global $dmrfid_levels;
$dmrfid_levels = dmrfid_getAllLevels();

// We set a global var for add-ons that are expecting it.
$dmrfid_show_discount_code = dmrfid_show_discount_code();

//by default we show the account fields if the user isn't logged in
if ( $current_user->ID ) {
	$skip_account_fields = true;
} else {
	$skip_account_fields = false;
}
//in case people want to have an account created automatically
$skip_account_fields = apply_filters( "dmrfid_skip_account_fields", $skip_account_fields, $current_user );

//some options
global $tospage;
$tospage = dmrfid_getOption( "tospage" );
if ( $tospage ) {
	$tospage = get_post( $tospage );
}

//load em up (other fields)
global $username, $password, $password2, $bfirstname, $blastname, $baddress1, $baddress2, $bcity, $bstate, $bzipcode, $bcountry, $bphone, $bemail, $bconfirmemail, $CardType, $AccountNumber, $ExpirationMonth, $ExpirationYear;

if ( isset( $_REQUEST['order_id'] ) ) {
	$order_id = intval( $_REQUEST['order_id'] );
} else {
	$order_id = "";
}
if ( isset( $_REQUEST['bfirstname'] ) ) {
	$bfirstname = sanitize_text_field( stripslashes( $_REQUEST['bfirstname'] ) );
} else {
	$bfirstname = "";
}
if ( isset( $_REQUEST['blastname'] ) ) {
	$blastname = sanitize_text_field( stripslashes( $_REQUEST['blastname'] ) );
} else {
	$blastname = "";
}
if ( isset( $_REQUEST['fullname'] ) ) {
	$fullname = $_REQUEST['fullname'];
}        //honeypot for spammers
if ( isset( $_REQUEST['baddress1'] ) ) {
	$baddress1 = sanitize_text_field( stripslashes( $_REQUEST['baddress1'] ) );
} else {
	$baddress1 = "";
}
if ( isset( $_REQUEST['baddress2'] ) ) {
	$baddress2 = sanitize_text_field( stripslashes( $_REQUEST['baddress2'] ) );
} else {
	$baddress2 = "";
}
if ( isset( $_REQUEST['bcity'] ) ) {
	$bcity = sanitize_text_field( stripslashes( $_REQUEST['bcity'] ) );
} else {
	$bcity = "";
}

if ( isset( $_REQUEST['bstate'] ) ) {
	$bstate = sanitize_text_field( stripslashes( $_REQUEST['bstate'] ) );
} else {
	$bstate = "";
}

//convert long state names to abbreviations
if ( ! empty( $bstate ) ) {
	global $dmrfid_states;
	foreach ( $dmrfid_states as $abbr => $state ) {
		if ( $bstate == $state ) {
			$bstate = $abbr;
			break;
		}
	}
}

if ( isset( $_REQUEST['bzipcode'] ) ) {
	$bzipcode = sanitize_text_field( stripslashes( $_REQUEST['bzipcode'] ) );
} else {
	$bzipcode = "";
}
if ( isset( $_REQUEST['bcountry'] ) ) {
	$bcountry = sanitize_text_field( stripslashes( $_REQUEST['bcountry'] ) );
} else {
	$bcountry = "";
}
if ( isset( $_REQUEST['bphone'] ) ) {
	$bphone = sanitize_text_field( stripslashes( $_REQUEST['bphone'] ) );
} else {
	$bphone = "";
}
if ( isset ( $_REQUEST['bemail'] ) ) {
	$bemail = sanitize_email( stripslashes( $_REQUEST['bemail'] ) );
} elseif ( is_user_logged_in() ) {
	$bemail = $current_user->user_email;
} else {
	$bemail = "";
}
if ( isset( $_REQUEST['bconfirmemail_copy'] ) ) {
	$bconfirmemail = $bemail;
} elseif ( isset( $_REQUEST['bconfirmemail'] ) ) {
	$bconfirmemail = sanitize_email( stripslashes( $_REQUEST['bconfirmemail'] ) );
} elseif ( is_user_logged_in() ) {
	$bconfirmemail = $current_user->user_email;
} else {
	$bconfirmemail = "";
}

if ( isset( $_REQUEST['CardType'] ) && ! empty( $_REQUEST['AccountNumber'] ) ) {
	$CardType = sanitize_text_field( $_REQUEST['CardType'] );
} else {
	$CardType = "";
}
if ( isset( $_REQUEST['AccountNumber'] ) ) {
	$AccountNumber = sanitize_text_field( $_REQUEST['AccountNumber'] );
} else {
	$AccountNumber = "";
}

if ( isset( $_REQUEST['ExpirationMonth'] ) ) {
	$ExpirationMonth = sanitize_text_field( $_REQUEST['ExpirationMonth'] );
} else {
	$ExpirationMonth = "";
}
if ( isset( $_REQUEST['ExpirationYear'] ) ) {
	$ExpirationYear = sanitize_text_field( $_REQUEST['ExpirationYear'] );
} else {
	$ExpirationYear = "";
}
if ( isset( $_REQUEST['CVV'] ) ) {
	$CVV = sanitize_text_field( $_REQUEST['CVV'] );
} else {
	$CVV = "";
}

if ( isset( $_REQUEST['discount_code'] ) ) {
	$discount_code = preg_replace( "/[^A-Za-z0-9\-]/", "", $_REQUEST['discount_code'] );
} else {
	$discount_code = "";
}
if ( isset( $_REQUEST['username'] ) ) {
	$username = sanitize_user( $_REQUEST['username'] , true);
} else {
	$username = "";
}
if ( isset( $_REQUEST['password'] ) ) {
	$password = $_REQUEST['password'];
} else {
	$password = "";
}
if ( isset( $_REQUEST['password2_copy'] ) ) {
	$password2 = $password;
} elseif ( isset( $_REQUEST['password2'] ) ) {
	$password2 = $_REQUEST['password2'];
} else {
	$password2 = "";
}
if ( isset( $_REQUEST['tos'] ) ) {
	$tos = intval( $_REQUEST['tos'] );
} else {
	$tos = "";
}

$submit = dmrfid_was_checkout_form_submitted();

/**
 * Hook to run actions after the parameters are set on the checkout page.
 * @since 2.1
 */
do_action( 'dmrfid_checkout_after_parameters_set' );

//require fields
$dmrfid_required_billing_fields = array(
	"bfirstname"      => $bfirstname,
	"blastname"       => $blastname,
	"baddress1"       => $baddress1,
	"bcity"           => $bcity,
	"bstate"          => $bstate,
	"bzipcode"        => $bzipcode,
	"bphone"          => $bphone,
	"bemail"          => $bemail,
	"bcountry"        => $bcountry,
	"CardType"        => $CardType,
	"AccountNumber"   => $AccountNumber,
	"ExpirationMonth" => $ExpirationMonth,
	"ExpirationYear"  => $ExpirationYear,
	"CVV"             => $CVV
);
$dmrfid_required_billing_fields = apply_filters( "dmrfid_required_billing_fields", $dmrfid_required_billing_fields );
$dmrfid_required_user_fields    = array(
	"username"      => $username,
	"password"      => $password,
	"password2"     => $password2,
	"bemail"        => $bemail,
	"bconfirmemail" => $bconfirmemail
);
$dmrfid_required_user_fields    = apply_filters( "dmrfid_required_user_fields", $dmrfid_required_user_fields );

//dmrfid_confirmed is set to true later if payment goes through
$dmrfid_confirmed = false;

//check their fields if they clicked continue
if ( $submit && $dmrfid_msgt != "dmrfid_error" ) {

	//make sure javascript is ok
	if ( apply_filters( "dmrfid_require_javascript_for_checkout", true ) && ! empty( $_REQUEST['checkjavascript'] ) && empty( $_REQUEST['javascriptok'] ) ) {
		dmrfid_setMessage( __( "There are JavaScript errors on the page. Please contact the webmaster.", 'digital-members-rfid' ), "dmrfid_error" );
	}

	// If we're skipping the account fields and there is no user, we need to create a username and password.
	if ( $skip_account_fields && ! $current_user->ID ) {
		// Generate the username using the first name, last name and/or email address.
		$username = dmrfid_generateUsername( $bfirstname, $blastname, $bemail );

		// Generate the password.
		$password  = wp_generate_password();

		// Set the password confirmation to the generated password.
		$password2 = $password;
	}

	//check billing fields
	if ( $dmrfid_requirebilling ) {
		//filter
		foreach ( $dmrfid_required_billing_fields as $key => $field ) {
			if ( ! $field ) {
				$dmrfid_error_fields[] = $key;
			}
		}
	}

	//check user fields
	if ( empty( $current_user->ID ) ) {
		foreach ( $dmrfid_required_user_fields as $key => $field ) {
			if ( ! $field ) {
				$dmrfid_error_fields[] = $key;
			}
		}
	}

	if ( ! empty( $dmrfid_error_fields ) ) {
		dmrfid_setMessage( __( "Please complete all required fields.", 'digital-members-rfid' ), "dmrfid_error" );
	}
	if ( ! empty( $password ) && $password != $password2 ) {
		dmrfid_setMessage( __( "Your passwords do not match. Please try again.", 'digital-members-rfid' ), "dmrfid_error" );
		$dmrfid_error_fields[] = "password";
		$dmrfid_error_fields[] = "password2";
	}
	if ( strcasecmp($bemail, $bconfirmemail) !== 0 ) {
		dmrfid_setMessage( __( "Your email addresses do not match. Please try again.", 'digital-members-rfid' ), "dmrfid_error" );
		$dmrfid_error_fields[] = "bemail";
		$dmrfid_error_fields[] = "bconfirmemail";
	}
	if ( ! empty( $bemail ) && ! is_email( $bemail ) ) {
		dmrfid_setMessage( __( "The email address entered is in an invalid format. Please try again.", 'digital-members-rfid' ), "dmrfid_error" );
		$dmrfid_error_fields[] = "bemail";
		$dmrfid_error_fields[] = "bconfirmemail";
	}
	if ( ! empty( $tospage ) && empty( $tos ) ) {
		dmrfid_setMessage( sprintf( __( "Please check the box to agree to the %s.", 'digital-members-rfid' ), $tospage->post_title ), "dmrfid_error" );
		$dmrfid_error_fields[] = "tospage";
	}
	if ( ! in_array( $gateway, $valid_gateways ) ) {
		dmrfid_setMessage( __( "Invalid gateway.", 'digital-members-rfid' ), "dmrfid_error" );
	}
	if ( ! empty( $fullname ) ) {
		dmrfid_setMessage( __( "Are you a spammer?", 'digital-members-rfid' ), "dmrfid_error" );
	}

	if ( $dmrfid_msgt == "dmrfid_error" ) {
		$dmrfid_continue_registration = false;
	} else {
		$dmrfid_continue_registration = true;
	}
	$dmrfid_continue_registration = apply_filters( "dmrfid_registration_checks", $dmrfid_continue_registration );

	if ( $dmrfid_continue_registration ) {
		//if creating a new user, check that the email and username are available
		if ( empty( $current_user->ID ) ) {
			$ouser      = get_user_by( 'login', $username );
			$oldem_user = get_user_by( 'email', $bemail );

			//this hook can be used to allow multiple accounts with the same email address
			$oldemail = apply_filters( "dmrfid_checkout_oldemail", ( false !== $oldem_user ? $oldem_user->user_email : null ) );
		}

		if ( ! empty( $ouser->user_login ) ) {
			dmrfid_setMessage( __( "That username is already taken. Please try another.", 'digital-members-rfid' ), "dmrfid_error" );
			$dmrfid_error_fields[] = "username";
		}

		if ( ! empty( $oldemail ) ) {
			dmrfid_setMessage( __( "That email address is already in use. Please log in, or use a different email address.", 'digital-members-rfid' ), "dmrfid_error" );
			$dmrfid_error_fields[] = "bemail";
			$dmrfid_error_fields[] = "bconfirmemail";
		}

		//only continue if there are no other errors yet
		if ( $dmrfid_msgt != "dmrfid_error" ) {
			//check recaptcha first
			global $recaptcha, $recaptcha_validated;
			if ( ! $skip_account_fields && ( $recaptcha == 2 || ( $recaptcha == 1 && dmrfid_isLevelFree( $dmrfid_level ) ) ) ) {

				global $recaptcha_privatekey;

				if ( isset( $_POST["recaptcha_challenge_field"] ) ) {
					//using older recaptcha lib
					$resp = recaptcha_check_answer( $recaptcha_privatekey,
						$_SERVER["REMOTE_ADDR"],
						$_POST["recaptcha_challenge_field"],
						$_POST["recaptcha_response_field"] );

					$recaptcha_valid  = $resp->is_valid;
					$recaptcha_errors = $resp->error;
				} else {
					//using newer recaptcha lib
					$reCaptcha = new dmrfid_ReCaptcha( $recaptcha_privatekey );
					$resp      = $reCaptcha->verifyResponse( $_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"] );

					$recaptcha_valid  = $resp->success;
					$recaptcha_errors = $resp->errorCodes;
				}

				if ( ! $recaptcha_valid ) {
					$dmrfid_msg  = sprintf( __( "reCAPTCHA failed. (%s) Please try again.", 'digital-members-rfid' ), $recaptcha_errors );
					$dmrfid_msgt = "dmrfid_error";
				} else {
					// Your code here to handle a successful verification
					if ( $dmrfid_msgt != "dmrfid_error" ) {
						$dmrfid_msg = "All good!";
					}
					dmrfid_set_session_var( 'dmrfid_recaptcha_validated', true );
				}
			} else {
				if ( $dmrfid_msgt != "dmrfid_error" ) {
					$dmrfid_msg = "All good!";
				}
			}

			//no errors yet
			if ( $dmrfid_msgt != "dmrfid_error" ) {
				do_action( 'dmrfid_checkout_before_processing' );

				//process checkout if required
				if ( $dmrfid_requirebilling ) {
					$morder = dmrfid_build_order_for_checkout();

					$dmrfid_processed = $morder->process();

					if ( ! empty( $dmrfid_processed ) ) {
						$dmrfid_msg       = __( "Payment accepted.", 'digital-members-rfid' );
						$dmrfid_msgt      = "dmrfid_success";
						$dmrfid_confirmed = true;
					} else {
						$dmrfid_msg = !empty( $morder->error ) ? $morder->error : null;
						if ( empty( $dmrfid_msg ) ) {
							$dmrfid_msg = __( "Unknown error generating account. Please contact us to set up your membership.", 'digital-members-rfid' );
						}
						
						if ( ! empty( $morder->error_type ) ) {
							$dmrfid_msgt = $morder->error_type;
						} else {
							$dmrfid_msgt = "dmrfid_error";
						}						
					}

				} else // !$dmrfid_requirebilling
				{
					//must have been a free membership, continue
					$dmrfid_confirmed = true;
				}
			}
		}
	}    //endif ($dmrfid_continue_registration)
}

//make sure we have at least an empty morder here to avoid a warning
if ( empty( $morder ) ) {
	$morder = false;
}

//Hook to check payment confirmation or replace it. If we get an array back, pull the values (morder) out
$dmrfid_confirmed_data = apply_filters( 'dmrfid_checkout_confirmed', $dmrfid_confirmed, $morder );

/**
 * @todo Refactor this to avoid using extract.
 */
if ( is_array( $dmrfid_confirmed_data ) ) {
	extract( $dmrfid_confirmed_data );
} else {
	$dmrfid_confirmed = $dmrfid_confirmed_data;
}

//if payment was confirmed create/update the user.
if ( ! empty( $dmrfid_confirmed ) ) {
	//just in case this hasn't been set yet
	$submit = true;

	//do we need to create a user account?
	if ( ! $current_user->ID ) {
		/*
			create user
		*/
		if ( version_compare( $wp_version, "3.1" ) < 0 ) {
			require_once( ABSPATH . WPINC . '/registration.php' );
		}    //need this for WP versions before 3.1

		//first name
		if ( ! empty( $_REQUEST['first_name'] ) ) {
			$first_name = $_REQUEST['first_name'];
		} else {
			$first_name = $bfirstname;
		}
		//last name
		if ( ! empty( $_REQUEST['last_name'] ) ) {
			$last_name = $_REQUEST['last_name'];
		} else {
			$last_name = $blastname;
		}

		//insert user
		$new_user_array = apply_filters( 'dmrfid_checkout_new_user_array', array(
				"user_login" => $username,
				"user_pass"  => $password,
				"user_email" => $bemail,
				"first_name" => $first_name,
				"last_name"  => $last_name
			)
		);

		$user_id = apply_filters( 'dmrfid_new_user', '', $new_user_array );
		if ( empty( $user_id ) ) {
			$user_id = wp_insert_user( $new_user_array );
		}

		if ( empty( $user_id ) || is_wp_error( $user_id ) ) {
			$e_msg = '';

			if ( is_wp_error( $user_id ) ) {
				$e_msg = $user_id->get_error_message();
			}

			$dmrfid_msg  = __( "Your payment was accepted, but there was an error setting up your account. Please contact us.", 'digital-members-rfid' ) . sprintf( " %s", $e_msg ); // Dirty 'don't break translation hack.
			$dmrfid_msgt = "dmrfid_error";
		} elseif ( apply_filters( 'dmrfid_setup_new_user', true, $user_id, $new_user_array, $dmrfid_level ) ) {

			//check dmrfid_wp_new_user_notification filter before sending the default WP email
			if ( apply_filters( "dmrfid_wp_new_user_notification", true, $user_id, $dmrfid_level->id ) ) {
				if ( version_compare( $wp_version, "4.3.0" ) >= 0 ) {
					wp_new_user_notification( $user_id, null, 'both' );
				} else {
					wp_new_user_notification( $user_id, $new_user_array['user_pass'] );
				}
			}

			$wpuser = get_userdata( $user_id );

			//make the user a subscriber
			$wpuser->set_role( get_option( 'default_role', 'subscriber' ) );

			//okay, log them in to WP
			$creds                  = array();
			$creds['user_login']    = $new_user_array['user_login'];
			$creds['user_password'] = $new_user_array['user_pass'];
			$creds['remember']      = true;
			$user                   = wp_signon( $creds, false );

			//setting some cookies
			wp_set_current_user( $user_id, $username );
			wp_set_auth_cookie( $user_id, true, apply_filters( 'dmrfid_checkout_signon_secure', force_ssl_admin() ) );
		}
	} else {
		$user_id = $current_user->ID;
	}

	if ( ! empty( $user_id ) && ! is_wp_error( $user_id ) ) {
		do_action( 'dmrfid_checkout_before_change_membership_level', $user_id, $morder );

		//start date is NOW() but filterable below
		$startdate = current_time( "mysql" );

		/**
		 * Filter the start date for the membership/subscription.
		 *
		 * @since 1.8.9
		 *
		 * @param string $startdate , datetime formatsted for MySQL (NOW() or YYYY-MM-DD)
		 * @param int $user_id , ID of the user checking out
		 * @param object $dmrfid_level , object of level being checked out for
		 */
		$startdate = apply_filters( "dmrfid_checkout_start_date", $startdate, $user_id, $dmrfid_level );

		//calculate the end date
		if ( ! empty( $dmrfid_level->expiration_number ) ) {
			$enddate =  date( "Y-m-d H:i:s", strtotime( "+ " . $dmrfid_level->expiration_number . " " . $dmrfid_level->expiration_period, current_time( "timestamp" ) ) );
		} else {
			$enddate = "NULL";
		}

		/**
		 * Filter the end date for the membership/subscription.
		 *
		 * @since 1.8.9
		 *
		 * @param string $enddate , datetime formatsted for MySQL (YYYY-MM-DD)
		 * @param int $user_id , ID of the user checking out
		 * @param object $dmrfid_level , object of level being checked out for
		 * @param string $startdate , startdate calculated above
		 */
		$enddate = apply_filters( "dmrfid_checkout_end_date", $enddate, $user_id, $dmrfid_level, $startdate );

		//check code before adding it to the order
		global $dmrfid_checkout_level_ids; // Set by MMPU.
		if ( isset( $dmrfid_checkout_level_ids ) ) {
			$code_check = dmrfid_checkDiscountCode( $discount_code, $dmrfid_checkout_level_ids, true );
		} else {
			$code_check = dmrfid_checkDiscountCode( $discount_code, $dmrfid_level->id, true );
		}
		
		if ( $code_check[0] == false ) {
			//error
			$dmrfid_msg  = $code_check[1];
			$dmrfid_msgt = "dmrfid_error";

			//don't use this code
			$use_discount_code = false;
		} else {
			//all okay
			$use_discount_code = true;
		}
		
		//update membership_user table.		
		if ( ! empty( $discount_code ) && ! empty( $use_discount_code ) ) {
			$discount_code_id = $wpdb->get_var( "SELECT id FROM $wpdb->dmrfid_discount_codes WHERE code = '" . esc_sql( $discount_code ) . "' LIMIT 1" );
		} else {
			$discount_code_id = "";
		}

		$custom_level = array(
			'user_id'         => $user_id,
			'membership_id'   => $dmrfid_level->id,
			'code_id'         => $discount_code_id,
			'initial_payment' => dmrfid_round_price( $dmrfid_level->initial_payment ),
			'billing_amount'  => dmrfid_round_price( $dmrfid_level->billing_amount ),
			'cycle_number'    => $dmrfid_level->cycle_number,
			'cycle_period'    => $dmrfid_level->cycle_period,
			'billing_limit'   => $dmrfid_level->billing_limit,
			'trial_amount'    => dmrfid_round_price( $dmrfid_level->trial_amount ),
			'trial_limit'     => $dmrfid_level->trial_limit,
			'startdate'       => $startdate,
			'enddate'         => $enddate
		);

		if ( dmrfid_changeMembershipLevel( $custom_level, $user_id, 'changed' ) ) {
			//we're good
			//blank order for free levels
			if ( empty( $morder ) ) {
				$morder                 = new MemberOrder();
				$morder->InitialPayment = 0;
				$morder->Email          = $bemail;
				$morder->gateway        = 'free';
				$morder->status			= 'success';
				$morder = apply_filters( "dmrfid_checkout_order_free", $morder );
			}

			//add an item to the history table, cancel old subscriptions
			if ( ! empty( $morder ) ) {
				$morder->user_id       = $user_id;
				$morder->membership_id = $dmrfid_level->id;
				$morder->saveOrder();
			}

			//update the current user
			global $current_user;
			if ( ! $current_user->ID && $user->ID ) {
				$current_user = $user;
			} //in case the user just signed up
			dmrfid_set_current_user();

			//add discount code use
			if ( $discount_code && $use_discount_code ) {
				if ( ! empty( $morder->id ) ) {
					$code_order_id = $morder->id;
				} else {
					$code_order_id = "";
				}

				$wpdb->query( "INSERT INTO $wpdb->dmrfid_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES('" . $discount_code_id . "', '" . $user_id . "', '" . intval( $code_order_id ) . "', '" . current_time( "mysql" ) . "')" );
			}

			//save billing info ect, as user meta
			$meta_keys   = array(
				"dmrfid_bfirstname",
				"dmrfid_blastname",
				"dmrfid_baddress1",
				"dmrfid_baddress2",
				"dmrfid_bcity",
				"dmrfid_bstate",
				"dmrfid_bzipcode",
				"dmrfid_bcountry",
				"dmrfid_bphone",
				"dmrfid_bemail",
				"dmrfid_CardType",
				"dmrfid_AccountNumber",
				"dmrfid_ExpirationMonth",
				"dmrfid_ExpirationYear"
			);
			$meta_values = array(
				$bfirstname,
				$blastname,
				$baddress1,
				$baddress2,
				$bcity,
				$bstate,
				$bzipcode,
				$bcountry,
				$bphone,
				$bemail,
				$CardType,
				hideCardNumber( $AccountNumber ),
				$ExpirationMonth,
				$ExpirationYear
			);
			dmrfid_replaceUserMeta( $user_id, $meta_keys, $meta_values );

			//save first and last name fields
			if ( ! empty( $bfirstname ) ) {
				$old_firstname = get_user_meta( $user_id, "first_name", true );
				if ( empty( $old_firstname ) ) {
					update_user_meta( $user_id, "first_name", $bfirstname );
				}
			}
			if ( ! empty( $blastname ) ) {
				$old_lastname = get_user_meta( $user_id, "last_name", true );
				if ( empty( $old_lastname ) ) {
					update_user_meta( $user_id, "last_name", $blastname );
				}
			}

			//show the confirmation
			$ordersaved = true;

			//hook
			do_action( "dmrfid_after_checkout", $user_id, $morder );    //added $morder param in v2.0

			$sendemails = apply_filters( "dmrfid_send_checkout_emails", true);
	
			if($sendemails) { // Send the emails only if the flag is set to true

				//setup some values for the emails
				if ( ! empty( $morder ) ) {
					$invoice = new MemberOrder( $morder->id );
				} else {
					$invoice = null;
				}
				$current_user->membership_level = $dmrfid_level; //make sure they have the right level info

				//send email to member
				$dmrfidemail = new DmRFIDEmail();
				$dmrfidemail->sendCheckoutEmail( $current_user, $invoice );

				//send email to admin
				$dmrfidemail = new DmRFIDEmail();
				$dmrfidemail->sendCheckoutAdminEmail( $current_user, $invoice );
			}

			//redirect to confirmation
			$rurl = dmrfid_url( "confirmation", "?level=" . $dmrfid_level->id );
			$rurl = apply_filters( "dmrfid_confirmation_url", $rurl, $user_id, $dmrfid_level );
			wp_redirect( $rurl );
			exit;
		} else {

			//uh oh. we charged them then the membership creation failed

			// test that the order object contains data
			$test = (array) $morder;
			if ( ! empty( $test ) && $morder->cancel() ) {
				$dmrfid_msg = __( "IMPORTANT: Something went wrong during membership creation. Your credit card authorized, but we cancelled the order immediately. You should not try to submit this form again. Please contact the site owner to fix this issue.", 'digital-members-rfid' );
				$morder    = null;
			} else {
				$dmrfid_msg = __( "IMPORTANT: Something went wrong during membership creation. Your credit card was charged, but we couldn't assign your membership. You should not submit this form again. Please contact the site owner to fix this issue.", 'digital-members-rfid' );
			}
		}
	}
}

//default values
if ( empty( $submit ) ) {
	//show message if the payment gateway is not setup yet
	if ( $dmrfid_requirebilling && ! dmrfid_getOption( "gateway", true ) ) {
		if ( dmrfid_isAdmin() ) {
			$dmrfid_msg = sprintf( __( 'You must <a href="%s">set up a Payment Gateway</a> before any payments will be processed.', 'digital-members-rfid' ), get_admin_url( null, '/admin.php?page=dmrfid-paymentsettings' ) );
		} else {
			$dmrfid_msg = __( "A Payment Gateway must be set up before any payments will be processed.", 'digital-members-rfid' );
		}
		$dmrfid_msgt = "";
	}

	//default values from DB
	if ( ! empty( $current_user->ID ) ) {
		$bfirstname    = get_user_meta( $current_user->ID, "dmrfid_bfirstname", true );
		$blastname     = get_user_meta( $current_user->ID, "dmrfid_blastname", true );
		$baddress1     = get_user_meta( $current_user->ID, "dmrfid_baddress1", true );
		$baddress2     = get_user_meta( $current_user->ID, "dmrfid_baddress2", true );
		$bcity         = get_user_meta( $current_user->ID, "dmrfid_bcity", true );
		$bstate        = get_user_meta( $current_user->ID, "dmrfid_bstate", true );
		$bzipcode      = get_user_meta( $current_user->ID, "dmrfid_bzipcode", true );
		$bcountry      = get_user_meta( $current_user->ID, "dmrfid_bcountry", true );
		$bphone        = get_user_meta( $current_user->ID, "dmrfid_bphone", true );
		$bemail        = get_user_meta( $current_user->ID, "dmrfid_bemail", true );
		$bconfirmemail = $bemail;    //as of 1.7.5, just setting to bemail
		$CardType      = get_user_meta( $current_user->ID, "dmrfid_CardType", true );
		//$AccountNumber = hideCardNumber(get_user_meta($current_user->ID, "dmrfid_AccountNumber", true), false);
		$ExpirationMonth = get_user_meta( $current_user->ID, "dmrfid_ExpirationMonth", true );
		$ExpirationYear  = get_user_meta( $current_user->ID, "dmrfid_ExpirationYear", true );
	}
}

//clear out XXXX numbers (e.g. with Stripe)
if ( ! empty( $AccountNumber ) && strpos( $AccountNumber, "XXXX" ) === 0 ) {
	$AccountNumber = "";
}

/**
 * Hook to run actions after the checkout preheader is loaded.
 * @since 2.1
 */
do_action( 'dmrfid_after_checkout_preheader', $morder );