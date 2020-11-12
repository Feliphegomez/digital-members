<?php
/**
 * Code to aid with user data privacy, e.g. GDPR compliance
 * 
 * @since  1.9.5
 */

/** 
 * Add suggested Privacy Policy language for DmRFID
 * @since 1.9.5
 */
function dmrfid_add_privacy_policy_content() {	
	// Check for support.
	if ( ! function_exists( 'wp_add_privacy_policy_content') ) {
		return;
	}

	$content = '';
	$content .= '<h2>' . __( 'Data Collected to Manage Your Membership', 'digital-members-rfid' ) . '</h2>';
	$content .= '<p>' . __( "At checkout, we will collect your name, email address, username, and password. This information is used to setup your account for our site. If you are redirected to an offsite payment gateway to complete your payment, we may store this information in a temporary session variable to setup your account when you return to our site.", 'digital-members-rfid' ) . '</p>';
	$content .= '<p>' . __( "At checkout, we may also collect your billing address and phone number. This information is used to confirm your credit card. The billing address and phone number are saved by our site to prepopulate the checkout form for future purchases and so we can get in touch with you if needed to discuss your order.", 'digital-members-rfid' ) . '</p>';
	$content .= '<p>' . __( "At checkout, we may also collect your credit card number, expiration date, and security code. This information is passed to our payment gateway to process your purchase. The last 4 digits of your credit card number and the expiration date are saved by our site to use for reference and to send you an email if your credit card will expire before the next recurring payment.", 'digital-members-rfid' ) . '</p>';
	$content .= '<p>' . __( "When logged in, we use cookies to track some of your activity on our site including logins, visits, and page views.", 'digital-members-rfid' ) . '</p>';

	wp_add_privacy_policy_content( 'Digital Members RFID', $content );
}
add_action( 'admin_init', 'dmrfid_add_privacy_policy_content' );

/**
 * Register the personal data eraser for DmRFID
 * @param array $erasers All erasers added so far
 */
function dmrfid_register_personal_data_erasers( $erasers = array() ) {
	$erasers[] = array(
 		'eraser_friendly_name' => __( 'Digital Members RFID Data' ),
 		'callback'             => 'dmrfid_personal_data_eraser',
 	);

	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'dmrfid_register_personal_data_erasers' );

/**
 * Personal data eraser for DmRFID data.
 * @since 1.9.5
 * @param string $email_address Email address of the user to be erased.
 * @param int    $page          For batching
 */
function dmrfid_personal_data_eraser( $email_address, $page = 1 ) {
	global $wpdb;

	// What user is this?
	$user = get_user_by( 'email', $email_address );

	$num_items_removed = 0;
	$num_items_retained = 0;
	$messages = array();
	$done = false;

	if( !empty( $user ) ) {
		// Erase any data we have about this user.
		$user_meta_fields_to_erase = dmrfid_get_personal_user_meta_fields_to_erase();

		$sqlQuery = $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key IN( [IN_CLAUSE] )", intval( $user->ID ) );

		$in_clause_data = array_map( 'esc_sql', $user_meta_fields_to_erase );
		$in_clause = "'" . implode( "', '", $in_clause_data ) . "'";	
		$sqlQuery = preg_replace( '/\[IN_CLAUSE\]/', $in_clause, $sqlQuery );

		$wpdb->query( $sqlQuery );
		$num_deleted = $wpdb->rows_affected;
		$num_items_removed += $num_deleted;

		// We retain all orders. Get the number of them to report them as retained.
		$sqlQuery = $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->dmrfid_membership_orders} WHERE user_id = %d", intval( $user->ID ) );
		$num_orders = $wpdb->get_var( $sqlQuery );
		if( $num_orders > 0 ) {
			$num_items_retained += $num_orders;
			// We could have used _n below, but that doesn't work well with our script for generating the .pot file.
			if( $num_orders == 1 ) {
				$messages[] = __( '1 DmRFID order was retained for business records.', 'digital-members-rfid' );
			} else {
				$messages[] = sprintf( __( '%d DmRFID orders were retained for business records.', 'digital-members-rfid' ), $num_orders );
			}
		}

		// Warn the admin if this user has an active subscription
		$messages[] = __( "Please note that data erasure will not cancel a user's membership level or any active subscriptions. Please edit or delete the user through the WordPress dashboard.", 'digital-members-rfid' );
	}

	// Set done to false if we still have stuff to erase.
	$done = true;

	return array(
 		'items_removed'  => $num_items_removed,
 		'items_retained' => $num_items_retained,
 		'messages'       => $messages,
 		'done'           => $done,
 	);
}

/**
 * Register the personal data exporter for DmRFID.
 * @since 1.9.5
 * @param array $exporters All exporters added so far
 */
function dmrfid_register_personal_data_exporters( $exporters ) {
	$exporters[] = array(
		'exporter_friendly_name' => __( 'Digital Members RFID Data' ),
		'callback'               => 'dmrfid_personal_data_exporter',
	);

	return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'dmrfid_register_personal_data_exporters' );

/**
 * Personal data exporter for DmRFID data.
 * @since 1.9.5
 */
function dmrfid_personal_data_exporter( $email_address, $page = 1 ) {
	global $wpdb;

	$data_to_export = array();

	// What user is this?
	$user = get_user_by( 'email', $email_address );

	if( !empty( $user ) ) {
		// Add data stored in user meta.
		$personal_user_meta_fields = dmrfid_get_personal_user_meta_fields();
		$sqlQuery = $wpdb->prepare( 
			"SELECT meta_key, meta_value
			 FROM {$wpdb->usermeta}
			 WHERE user_id = %d
			 AND meta_key IN( [IN_CLAUSE] )", intval( $user->ID ) );
		
		$in_clause_data = array_map( 'esc_sql', array_keys( $personal_user_meta_fields ) );
		$in_clause = "'" . implode( "', '", $in_clause_data ) . "'";	
		$sqlQuery = preg_replace( '/\[IN_CLAUSE\]/', $in_clause, $sqlQuery );
		
		$personal_user_meta_data = $wpdb->get_results( $sqlQuery, OBJECT_K );
		
		$user_meta_data_to_export = array();
		foreach( $personal_user_meta_fields as $key => $name ) {
			if( !empty( $personal_user_meta_data[$key] ) ) {
				$value = $personal_user_meta_data[$key]->meta_value;
			} else {
				$value = '';
			}

			$user_meta_data_to_export[] = array(
				'name' => $name,
				'value' => $value,
			);
		}

		$data_to_export[] = array(
			'group_id'    => 'dmrfid_user_data',
			'group_label' => __( 'Digital Members RFID User Data' ),
			'item_id'     => "user-{$user->ID}",
			'data'        => $user_meta_data_to_export,
		);
		

		// Add membership history.
		$sqlQuery = $wpdb->prepare(
			"SELECT * FROM {$wpdb->dmrfid_memberships_users}
			 WHERE user_id = %d
			 ORDER BY id DESC", intval( $user->ID ) );
			 
		$history = $wpdb->get_results( $sqlQuery );
		foreach( $history as $item ) {
			if( $item->enddate === null || $item->enddate == '0000-00-00 00:00:00' ) {
				$item->enddate = __( 'Never', 'digital-members-rfid' );
			} else {
				$item->enddate = date( get_option( 'date_format' ), strtotime( $item->enddate, current_time( 'timestamp' ) ) );
			}

			$history_data_to_export = array(
				array(
					'name'  => __( 'Level ID', 'digital-members-rfid' ),
					'value' => $item->membership_id, 
				),
				array(
					'name'  => __( 'Start Date', 'digital-members-rfid' ),
					'value' => date( get_option( 'date_format' ), strtotime( $item->startdate, current_time( 'timestamp' ) ) ),
				),
				array(
					'name'  => __( 'Date Modified', 'digital-members-rfid' ),
					'value' => date( get_option( 'date_format' ), strtotime( $item->modified, current_time( 'timestamp' ) ) ),
				),
				array(
					'name'  => __( 'End Date', 'digital-members-rfid' ),
					'value' => $item->enddate,
				),
				array(
					'name'  => __( 'Level Cost', 'digital-members-rfid' ),
					'value' => dmrfid_getLevelCost( $item, false, true ),
				),
				array(
					'name' => __( 'Status', 'digital-members-rfid' ),
					'value' => $item->status,
				),
			);

			$data_to_export[] = array(
				'group_id'    => 'dmrfid_membership_history',
				'group_label' => __( 'Digital Members RFID Membership History' ),
				'item_id'     => "memberships_users-{$item->id}",
				'data'        => $history_data_to_export,
			);
		}

		// Add order history.
		$sqlQuery = $wpdb->prepare(
			"SELECT id FROM {$wpdb->dmrfid_membership_orders}
			 WHERE user_id = %d
			 ORDER BY id DESC", intval( $user->ID ) );
			 
		$order_ids = $wpdb->get_col( $sqlQuery );		
		
		foreach( $order_ids as $order_id ) {
			$order = new MemberOrder( $order_id );
			$order->getMembershipLevel();
			
			$order_data_to_export = array(
				array(
					'name' => __( 'Order ID', 'digital-members-rfid' ),
					'value' => $order->id,
				),
				array(
					'name' => __( 'Order Code', 'digital-members-rfid' ),
					'value' => $order->code,
				),
				array(
					'name' => __( 'Order Date', 'digital-members-rfid' ),
					'value' => date( get_option( 'date_format' ), $order->getTimestamp() ),
				),
				array(
					'name' => __( 'Level', 'digital-members-rfid' ),
					'value' => $order->membership_level->name,
				),
				array(
					'name' => __( 'Billing Name', 'digital-members-rfid' ),
					'value' => $order->billing->name,
				),
				array(
					'name' => __( 'Billing Street', 'digital-members-rfid' ),
					'value' => $order->billing->street,
				),
				array(
					'name' => __( 'Billing City', 'digital-members-rfid' ),
					'value' => $order->billing->city,
				),
				array(
					'name' => __( 'Billing State', 'digital-members-rfid' ),
					'value' => $order->billing->state,
				),
				array(
					'name' => __( 'Billing Postal Code', 'digital-members-rfid' ),
					'value' => $order->billing->zip,
				),
				array(
					'name' => __( 'Billing Country', 'digital-members-rfid' ),
					'value' => $order->billing->country,
				),
				array(
					'name' => __( 'Billing Phone', 'digital-members-rfid' ),
					'value' => formatPhone( $order->billing->phone ),
				),
				array(
					'name' => __( 'Sub Total', 'digital-members-rfid' ),
					'value' => $order->subtotal,
				),
				array(
					'name' => __( 'Tax', 'digital-members-rfid' ),
					'value' => $order->tax,
				),
				array(
					'name' => __( 'Coupon Amount', 'digital-members-rfid' ),
					'value' => $order->couponamount,
				),
				array(
					'name' => __( 'Total', 'digital-members-rfid' ),
					'value' => $order->total,
				),
				array(
					'name' => __( 'Payment Type', 'digital-members-rfid' ),
					'value' => $order->payment_type,
				),
				array(
					'name' => __( 'Card Type', 'digital-members-rfid' ),
					'value' => $order->cardtype,
				),
				array(
					'name' => __( 'Account Number', 'digital-members-rfid' ),
					'value' => $order->accountnumber,
				),
				array(
					'name' => __( 'Expiration Month', 'digital-members-rfid' ),
					'value' => $order->expirationmonth,
				),
				array(
					'name' => __( 'Expiration Year', 'digital-members-rfid' ),
					'value' => $order->expirationyear,
				),
				array(
					'name' => __( 'Status', 'digital-members-rfid' ),
					'value' => $order->status,
				),
				array(
					'name' => __( 'Gateway', 'digital-members-rfid' ),
					'value' => $order->gateway,
				),
				array(
					'name' => __( 'Gateway Environment', 'digital-members-rfid' ),
					'value' => $order->gateway_environment,
				),
				array(
					'name' => __( 'Payment Transaction ID', 'digital-members-rfid' ),
					'value' => $order->payment_transaction_id,
				),
				array(
					'name' => __( 'Subscription Transaction ID', 'digital-members-rfid' ),
					'value' => $order->subscription_transaction_id,
				),
				// Note: Order notes, session_id, and paypal_token are excluded.
			);
			
			$data_to_export[] = array(
				'group_id'    => 'dmrfid_order_history',
				'group_label' => __( 'Digital Members RFID Order History' ),
				'item_id'     => "membership_order-{$order->id}",
				'data'        => $order_data_to_export,
			);
		}		
	}

	$done = true;
	
	return array(
		'data' => $data_to_export,
		'done' => $done,
	);
}

/**
 * Get list of user meta fields with labels to include in the DmRFID data exporter
 * @since 1.9.5
 */
function dmrfid_get_personal_user_meta_fields() {
	$fields = array(
		'dmrfid_bfirstname' => __( 'Billing First Name', 'digital-members-rfid' ),
		'dmrfid_blastname' => __( 'Billing Last Name', 'digital-members-rfid' ),
		'dmrfid_baddress1' => __( 'Billing Address 1', 'digital-members-rfid' ),
		'dmrfid_baddress2' => __( 'Billing Address 2', 'digital-members-rfid' ),
		'dmrfid_bcity' => __( 'Billing City', 'digital-members-rfid' ),
		'dmrfid_bstate' => __( 'Billing State/Province', 'digital-members-rfid' ),
		'dmrfid_bzipcode' => __( 'Billing Postal Code', 'digital-members-rfid' ),
		'dmrfid_bphone' => __( 'Billing Phone Number', 'digital-members-rfid' ),
		'dmrfid_bcountry' => __( 'Billing Country', 'digital-members-rfid' ),
		'dmrfid_CardType' => __( 'Credit Card Type', 'digital-members-rfid' ),
		'dmrfid_AccountNumber' => __( 'Credit Card Account Number', 'digital-members-rfid' ),
		'dmrfid_ExpirationMonth' => __( 'Credit Card Expiration Month', 'digital-members-rfid' ),
		'dmrfid_ExpirationYear' => __( 'Credit Card Expiration Year', 'digital-members-rfid' ),
		'dmrfid_logins' => __( 'Login Data', 'digital-members-rfid' ),
		'dmrfid_visits' => __( 'Visits Data', 'digital-members-rfid' ),
		'dmrfid_views' => __( 'Views Data', 'digital-members-rfid' ),
	);

	$fields = apply_filters( 'dmrfid_get_personal_user_meta_fields', $fields );

	return $fields;
}

/**
 * Get list of user meta fields to include in the DmRFID data eraser
 * @since 1.9.5
 */
function dmrfid_get_personal_user_meta_fields_to_erase() {
	$fields = array(
		'dmrfid_bfirstname',
		'dmrfid_blastname',
		'dmrfid_baddress1',
		'dmrfid_baddress2',
		'dmrfid_bcity',
		'dmrfid_bstate',
		'dmrfid_bzipcode',
		'dmrfid_bphone',
		'dmrfid_bcountry',
		'dmrfid_CardType',
		'dmrfid_AccountNumber',
		'dmrfid_ExpirationMonth',
		'dmrfid_ExpirationYear',
		'dmrfid_logins',
		'dmrfid_visits',
		'dmrfid_views',
	);

	$fields = apply_filters( 'dmrfid_get_personal_user_meta_fields_to_erase', $fields );

	return $fields;
}

/**
 * Save a TOS consent timestamp to user meta.
 * @since 1.9.5
 */
function dmrfid_save_consent( $user_id = NULL, $post_id = NULL, $post_modified = NULL, $order_id = NULL ) {
	// Default to current user.
	if( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	if( empty( $user_id ) ) {
		return false;
	}

	// Default to the TOS post chosen on the advanced settings page
	if( empty( $post_id ) ) {
		$post_id = dmrfid_getOption( 'tospage' );
	}

	if( empty( $post_id ) ) {
		return false;
	}

	$post = get_post( $post_id );

	if( empty( $post_modified ) ) {
		$post_modified = $post->post_modified;
	}

	$log = dmrfid_get_consent_log( $user_id );
	$log[] = array(
		'user_id' => $user_id,
		'post_id' => $post_id,
		'post_modified' => $post_modified,
		'order_id' => $order_id,
		'consented' => true,
		'timestamp' => current_time( 'timestamp' ),
	);

	update_user_meta( $user_id, 'dmrfid_consent_log', $log );
	return true;
}

/**
 * Get the TOS consent log from user meta.
 * @since  1.9.5
 */
function dmrfid_get_consent_log( $user_id = NULL, $reversed = true ) {
	// Default to current user.
	if( empty( $user_id ) ) {
		global $current_user;
		$user_id = $current_user->ID;
	}

	if( empty( $user_id ) ) {
		return false;
	}

	$log = get_user_meta( $user_id, 'dmrfid_consent_log', true );

	// Default log.
	if( empty( $log ) ) {
		$log = array();
	}

	if( $reversed ) {
		$log = array_reverse( $log );
	}

	return $log;
}

/**
 * Update TOS consent log after checkout.
 * @since 1.9.5
 */
function dmrfid_after_checkout_update_consent( $user_id, $order ) {
	if( !empty( $_REQUEST['tos'] ) ) {
		$tospage_id = dmrfid_getOption( 'tospage' );
		dmrfid_save_consent( $user_id, $tospage_id, NULL, $order->id );
	} elseif ( !empty( $_SESSION['tos'] ) ) {
		// PayPal Express and others might save tos info into a session variable
		$tospage_id = $_SESSION['tos']['post_id'];
		$tospage_modified = $_SESSION['tos']['post_modified'];
		dmrfid_save_consent( $user_id, $tospage_id, $tospage_modified, $order->id );
		unset( $_SESSION['tos'] );
	}
}
add_action( 'dmrfid_after_checkout', 'dmrfid_after_checkout_update_consent', 10, 2 );
add_action( 'dmrfid_before_send_to_paypal_standard', 'dmrfid_after_checkout_update_consent', 10, 2);
add_action( 'dmrfid_before_send_to_twocheckout', 'dmrfid_after_checkout_update_consent', 10, 2);

/**
 * Convert a consent entry into a English sentence.
 * @since  1.9.5
 */
function dmrfid_consent_to_text( $entry ) {
	// Check for bad data. Shouldn't happen in practice.
	if ( empty( $entry ) || empty( $entry['user_id'] ) ) {		
		return '';
	}
	
	$user = get_userdata( $entry['user_id'] );
	$post = get_post( $entry['post_id'] );

	$s = sprintf( __('%s agreed to %s (ID #%d, last modified %s) on %s.' ),
				  $user->display_name,
				  $post->post_title,
				  $post->ID,
				  $entry['post_modified'],
				  date( get_option( 'date_format' ), $entry['timestamp'] ) );

	if( !dmrfid_is_consent_current( $entry ) ) {
		$s .= ' ' . __( 'That post has since been updated.', 'digital-members-rfid' );
	}

	return $s;
}

/**
 * Check if a consent entry is current.
 * @since  1.9.5
 */
function dmrfid_is_consent_current( $entry ) {
	$post = get_post( $entry['post_id'] );
	if( !empty( $post ) && !empty( $post->post_modified ) && $post->post_modified == $entry['post_modified'] ) {
		return true;
	}
	return false;
}
