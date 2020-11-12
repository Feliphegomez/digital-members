<div class="<?php echo dmrfid_get_element_class( 'dmrfid_billing_wrap' ); ?>">
<?php
	global $wpdb, $current_user, $gateway, $dmrfid_msg, $dmrfid_msgt, $show_check_payment_instructions, $show_paypal_link;
	global $bfirstname, $blastname, $baddress1, $baddress2, $bcity, $bstate, $bzipcode, $bcountry, $bphone, $bemail, $bconfirmemail, $CardType, $AccountNumber, $ExpirationMonth, $ExpirationYear;

	/**
	 * Filter to set if DmRFID uses email or text as the type for email field inputs.
	 *
	 * @since 1.8.4.5
	 *
	 * @param bool $use_email_type, true to use email type, false to use text type
	 */
	$dmrfid_email_field_type = apply_filters('dmrfid_email_field_type', true);

	// Get the default gateway for the site.
	$default_gateway = dmrfid_getOption( 'gateway' );

	// Set the wrapping class for the checkout div based on the default gateway;
	if ( empty( $gateway ) ) {
		$dmrfid_billing_gateway_class = 'dmrfid_billing_gateway-none';
	} else {
		$dmrfid_billing_gateway_class = 'dmrfid_billing_gateway-' . $gateway;
	}

	$levels = $current_user->membership_levels;
	$has_recurring_levels = dmrfid_has_recurring_level();

	//Make sure the $level object is a valid level definition
	if(!empty($levels) ) {
		$level = $levels[0];
		$checkout_url = dmrfid_url( 'checkout', '?level=' . $level->id );
		$logout_url = wp_logout_url( $checkout_url );
		?>
		<p><?php printf(__("Logged in as <strong>%s</strong>.", 'paid-memberships-pro' ), $current_user->user_login);?> <small><a href="<?php echo esc_url( $logout_url ); ?>"><?php _e("logout", 'paid-memberships-pro' );?></a></small></p>
		<?php
		 /**
		 * dmrfid_billing_message_top hook to add in general content to the billing page without using custom page templates.
		 *
		 * @since 1.9.2
		 */
		 do_action('dmrfid_billing_message_top'); ?>

		<ul>
			<?php
			 /**
			 * dmrfid_billing_bullets_top hook allows you to add information to the billing list (at the top).
			 *
			 * @since 1.9.2
			 * @param {objects} {$level} {Passes the $level object}
			 */
			do_action('dmrfid_billing_bullets_top', $level);?>

			<?php foreach( $levels as $level ) {
				if ( $has_recurring_levels != dmrfid_isLevelRecurring( $level ) ) {
					continue;
				}
				?>
				<li><strong><?php _e("Level", 'paid-memberships-pro' );?>:</strong> <?php echo $level->name?></li>
				<?php if($level->billing_amount > 0) { ?>
					<li><strong><?php _e("Membership Fee", 'paid-memberships-pro' );?>:</strong>
						<?php
							$level = $current_user->membership_level;
							if($current_user->membership_level->cycle_number > 1) {
								printf(__('%s every %d %s.', 'paid-memberships-pro' ), dmrfid_formatPrice($level->billing_amount), $level->cycle_number, dmrfid_translate_billing_period($level->cycle_period, $level->cycle_number));
							} elseif($current_user->membership_level->cycle_number == 1) {
								printf(__('%s per %s.', 'paid-memberships-pro' ), dmrfid_formatPrice($level->billing_amount), dmrfid_translate_billing_period($level->cycle_period));
							} else {
								echo dmrfid_formatPrice($current_user->membership_level->billing_amount);
							}
						?>

					</li>
				<?php } ?>
			<?php } ?>

			<?php if($level->billing_limit) { ?>
				<li><strong><?php _e("Duration", 'paid-memberships-pro' );?>:</strong> <?php echo $level->billing_limit.' '.sornot($level->cycle_period,$level->billing_limit)?></li>
			<?php } ?>

			<?php
				$dmrfid_billing_show_payment_method = apply_filters( 'dmrfid_billing_show_payment_method'
					, true);
				if ( $dmrfid_billing_show_payment_method && ! empty( $CardType ) && $has_recurring_levels ) { ?>
					<li><strong><?php _e( 'Payment Method', 'paid-memberships-pro' ); ?>: </strong>
						<?php echo esc_html( ucwords( $CardType ) ); ?>
						<?php _e('ending in', 'paid-memberships-pro' ); ?>
						<?php echo esc_html( last4( get_user_meta( $current_user->ID, 'dmrfid_AccountNumber', true ) ) ); ?>.
						<?php _e('Expiration', 'paid-memberships-pro' );?>: <?php echo esc_html( $ExpirationMonth ); ?>/<?php echo esc_html( $ExpirationYear ); ?>
					</li>
					<?php
				}
			?>

			<?php
			 /**
			 * dmrfid_billing_bullets_top hook allows you to add information to the billing list (at the bottom).
			 *
			 * @since 1.9.2
			 * @param {objects} {$level} {Passes the $level object}
			 */
			do_action('dmrfid_billing_bullets_bottom', $level);?>
		</ul>
	<?php
	}
?>

<?php if ( $has_recurring_levels ) {
	if ( $show_check_payment_instructions ) {
		$instructions = dmrfid_getOption("instructions"); ?>
		<div class="<?php echo dmrfid_get_element_class( 'dmrfid_check_instructions' ); ?>"><?php echo wpautop( wp_unslash( $instructions ) ); ?></div>
		<hr />
		<p class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav' ); ?>">
			<span class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav-right' ); ?>"><a href="<?php echo dmrfid_url( 'account' )?>"><?php _e('View Your Membership Account &rarr;', 'paid-memberships-pro' );?></a></span>
		</p> <!-- end dmrfid_actions_nav -->
	<?php } elseif ( $show_paypal_link ) { ?>
		<p><?php  _e('Your payment subscription is managed by PayPal. Please <a href="http://www.paypal.com">login to PayPal here</a> to update your billing information.', 'paid-memberships-pro' );?></p>
		<hr />
		<p class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav' ); ?>">
			<span class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav-right' ); ?>"><a href="<?php echo dmrfid_url( 'account' )?>"><?php _e('View Your Membership Account &rarr;', 'paid-memberships-pro' );?></a></span>
		</p> <!-- end dmrfid_actions_nav -->
	<?php } elseif ( $gateway != $default_gateway ) {
		// This membership's gateway is not the default site gateway, Pay by Check, or PayPal Express.
		?>
		<p><?php _e( 'Your billing information cannot be updated at this time.', 'paid-memberships-pro' ); ?></p>
	<?php } else {
		// Show the default gateway form and allow billing information update.
		?>
		<div id="dmrfid_level-<?php echo $level->id; ?>" class="<?php echo dmrfid_get_element_class( $dmrfid_billing_gateway_class, 'dmrfid_level-' . $level->id ); ?>">
		<form id="dmrfid_form" class="<?php echo dmrfid_get_element_class( 'dmrfid_form' ); ?>" action="<?php echo dmrfid_url("billing", "", "https")?>" method="post">

			<input type="hidden" name="level" value="<?php echo esc_attr($level->id);?>" />
			<?php if($dmrfid_msg)
				{
			?>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_message ' . $dmrfid_msgt, $dmrfid_msgt ); ?>"><?php echo $dmrfid_msg?></div>
			<?php
				}
			?>

			<?php
				$dmrfid_include_billing_address_fields = apply_filters('dmrfid_include_billing_address_fields', true);
				if($dmrfid_include_billing_address_fields)
				{
			?>
			<div id="dmrfid_billing_address_fields" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout', 'dmrfid_billing_address_fields' ); ?>">
				<hr />
				<h3>
					<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-name' ); ?>"><?php _e('Billing Address', 'paid-memberships-pro' );?></span>
				</h3>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields' ); ?>">
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bfirstname', 'dmrfid_checkout-field-bfirstname' ); ?>">
						<label for="bfirstname"><?php _e('First Name', 'paid-memberships-pro' );?></label>
						<input id="bfirstname" name="bfirstname" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bfirstname' ); ?>" size="30" value="<?php echo esc_attr($bfirstname);?>" />
					</div> <!-- end dmrfid_checkout-field-bfirstname -->
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-blastname', 'dmrfid_checkout-field-blastname' ); ?>">
						<label for="blastname"><?php _e('Last Name', 'paid-memberships-pro' );?></label>
						<input id="blastname" name="blastname" type="text" 

						class="<?php echo dmrfid_get_element_class( 'input', 'blastname' ); ?>" size="30" value="<?php echo esc_attr($blastname);?>" />
					</div> <!-- end dmrfid_checkout-field-blastname -->
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-baddress1', 'dmrfid_checkout-field-baddress1' ); ?>">
						<label for="baddress1"><?php _e('Address 1', 'paid-memberships-pro' );?></label>
						<input id="baddress1" name="baddress1" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'baddress1' ); ?>" size="30" value="<?php echo esc_attr($baddress1);?>" />
					</div> <!-- end dmrfid_checkout-field-baddress1 -->
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-baddress2', 'dmrfid_checkout-field-baddress2' ); ?>">
						<label for="baddress2"><?php _e('Address 2', 'paid-memberships-pro' );?></label>
						<input id="baddress2" name="baddress2" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'baddress2' ); ?>" size="30" value="<?php echo esc_attr($baddress2);?>" /> <small class="<?php echo dmrfid_get_element_class( 'lite' ); ?>">(<?php _e('optional', 'paid-memberships-pro' );?>)</small>
					</div> <!-- end dmrfid_checkout-field-baddress2 -->

					<?php
						$longform_address = apply_filters("dmrfid_longform_address", false);
						if($longform_address)
						{
						?>
							<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bcity', 'dmrfid_checkout-field-bcity' ); ?>">
								<label for="bcity"><?php _e('City', 'paid-memberships-pro' );?></label>
								<input id="bcity" name="bcity" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bcity' ); ?>" size="30" value="<?php echo esc_attr($bcity)?>" />
							</div> <!-- end dmrfid_checkout-field-bcity -->
							<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bstate', 'dmrfid_checkout-field-bstate' ); ?>">
								<label for="bstate"><?php _e('State', 'paid-memberships-pro' );?></label>
								<input id="bstate" name="bstate" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bstate' ); ?>" size="30" value="<?php echo esc_attr($bstate)?>" />
							</div> <!-- end dmrfid_checkout-field-bstate -->
							<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bzipcode', 'dmrfid_checkout-field-bzipcode' ); ?>">
								<label for="bzipcode"><?php _e('Postal Code', 'paid-memberships-pro' );?></label>
								<input id="bzipcode" name="bzipcode" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bzipcode' ); ?>" size="30" value="<?php echo esc_attr($bzipcode)?>" />
							</div> <!-- end dmrfid_checkout-field-bzipcode -->
						<?php
						}
						else
						{
						?>
							<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bcity_state_zip', 'dmrfid_checkout-field-bcity_state_zip' ); ?>">
								<label for="bcity_state_zip"><?php _e('City, State Zip', 'paid-memberships-pro' );?></label>
								<input id="bcity" name="bcity" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bcity' ); ?>" size="14" value="<?php echo esc_attr($bcity)?>" />,
								<?php
									$state_dropdowns = apply_filters("dmrfid_state_dropdowns", false);
									if($state_dropdowns === true || $state_dropdowns == "names")
									{
										global $dmrfid_states;
									?>
									<select name="bstate" class="<?php echo dmrfid_get_element_class( '', 'bstate' ); ?>">
										<option value="">--</option>
										<?php
											foreach($dmrfid_states as $ab => $st)
											{
										?>
											<option value="<?php echo esc_attr($ab);?>" <?php if($ab == $bstate) { ?>selected="selected"<?php } ?>><?php echo $st;?></option>
										<?php } ?>
									</select>
									<?php
									}
									elseif($state_dropdowns == "abbreviations")
									{
										global $dmrfid_states_abbreviations;
									?>
										<select name="bstate" class="<?php echo dmrfid_get_element_class( '', 'bstate' ); ?>">
											<option value="">--</option>
											<?php
												foreach($dmrfid_states_abbreviations as $ab)
												{
											?>
												<option value="<?php echo esc_attr($ab);?>" <?php if($ab == $bstate) { ?>selected="selected"<?php } ?>><?php echo $ab;?></option>
											<?php } ?>
										</select>
									<?php
									}
									else
									{
									?>
									<input id="bstate" name="bstate" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bstate' ); ?>" size="2" value="<?php echo esc_attr($bstate)?>" />
									<?php
									}
								?>
								<input id="bzipcode" name="bzipcode" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bzipcode' ); ?>" size="5" value="<?php echo esc_attr($bzipcode)?>" />
							</div> <!-- end dmrfid_checkout-field-bcity_state_zip -->
						<?php
						}
					?>

					<?php
						$show_country = apply_filters("dmrfid_international_addresses", true);
						if($show_country)
						{
					?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bcountry', 'dmrfid_checkout-field-bcountry' ); ?>">
						<label for="bcountry"><?php _e('Country', 'paid-memberships-pro' );?></label>
						<select name="bcountry" class="<?php echo dmrfid_get_element_class( '', 'bcountry' );?>">
							<?php
								global $dmrfid_countries, $dmrfid_default_country;
								foreach($dmrfid_countries as $abbr => $country)
								{
									if(!$bcountry)
										$bcountry = $dmrfid_default_country;
								?>
								<option value="<?php echo $abbr?>" <?php if($abbr == $bcountry) { ?>selected="selected"<?php } ?>><?php echo $country?></option>
								<?php
								}
							?>
						</select>
					</div> <!-- end dmrfid_checkout-field-bcountry -->
					<?php
						}
						else
						{
						?>
							<input type="hidden" id="bcountry" name="bcountry" value="US" />
						<?php
						}
					?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bphone', 'dmrfid_checkout-field-bphone' ); ?>">
						<label for="bphone"><?php _e('Phone', 'paid-memberships-pro' );?></label>
						<input id="bphone" name="bphone" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bphone' ); ?>" size="30" value="<?php echo esc_attr($bphone)?>" />
					</div> <!-- end dmrfid_checkout-field-bphone -->
					<?php if($current_user->ID) { ?>
					<?php
						if(!$bemail && $current_user->user_email)
							$bemail = $current_user->user_email;
						if(!$bconfirmemail && $current_user->user_email)
							$bconfirmemail = $current_user->user_email;
					?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bemail', 'dmrfid_checkout-field-bemail' ); ?>">
						<label for="bemail"><?php _e('Email Address', 'paid-memberships-pro' );?></label>
						<input id="bemail" name="bemail" type="<?php echo ($dmrfid_email_field_type ? 'email' : 'text'); ?>" class="<?php echo dmrfid_get_element_class( 'input', 'bemail' ); ?>" size="30" value="<?php echo esc_attr($bemail)?>" />
					</div> <!-- end dmrfid_checkout-field-bemail -->
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bconfirmemail', 'dmrfid_checkout-field-bconfirmemail' ); ?>">
						<label for="bconfirmemail"><?php _e('Confirm Email', 'paid-memberships-pro' );?></label>
						<input id="bconfirmemail" name="bconfirmemail" type="<?php echo ($dmrfid_email_field_type ? 'email' : 'text'); ?>" class="<?php echo dmrfid_get_element_class( 'input', 'bconfirmemail' ); ?>" size="30" value="<?php echo esc_attr($bconfirmemail)?>" />
					</div> <!-- end dmrfid_checkout-field-bconfirmemail -->
					<?php } ?>
				</div> <!-- end dmrfid_checkout-fields -->
			</div> <!-- end dmrfid_billing -->
			<?php } ?>

			<?php
			//make sure gateways will show up credit card fields
			global $dmrfid_requirebilling;
			$dmrfid_requirebilling = true;

			//do we need to show the payment information (credit card) fields? gateways will override this
			$dmrfid_include_payment_information_fields = apply_filters('dmrfid_include_payment_information_fields', true);
			if($dmrfid_include_payment_information_fields)
			{
				$dmrfid_accepted_credit_cards = dmrfid_getOption("accepted_credit_cards");
				$dmrfid_accepted_credit_cards = explode(",", $dmrfid_accepted_credit_cards);
				$dmrfid_accepted_credit_cards_string = dmrfid_implodeToEnglish($dmrfid_accepted_credit_cards);
				?>
				<div id="dmrfid_payment_information_fields" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout', 'dmrfid_payment_information_fields' ); ?>">
					<h3>
						<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-name' ); ?>"><?php _e('Credit Card Information', 'paid-memberships-pro' );?></span>
						<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-msg' ); ?>"><?php printf(__('We accept %s', 'paid-memberships-pro' ), $dmrfid_accepted_credit_cards_string);?></span>
					</h3>
					<?php $sslseal = dmrfid_getOption("sslseal"); ?>
					<?php if(!empty($sslseal)) { ?>
						<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields-display-seal' ); ?>">
					<?php } ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields' ); ?>">
						<?php
							$dmrfid_include_cardtype_field = apply_filters('dmrfid_include_cardtype_field', false);
							if($dmrfid_include_cardtype_field) { ?>
								<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_payment-card-type', 'dmrfid_payment-card-type' ); ?>">
									<label for="CardType"><?php _e('Card Type', 'paid-memberships-pro' );?></label>
									<select id="CardType" name="CardType" class="<?php echo dmrfid_get_element_class( '', 'CardType' );?>">
										<?php foreach($dmrfid_accepted_credit_cards as $cc) { ?>
											<option value="<?php echo $cc?>" <?php if($CardType == $cc) { ?>selected="selected"<?php } ?>><?php echo $cc?></option>
										<?php } ?>
									</select>
								</div> <!-- end dmrfid_payment-card-type -->
							<?php } else { ?>
								<input type="hidden" id="CardType" name="CardType" value="<?php echo esc_attr($CardType);?>" />
								<script>
									<!--
									jQuery(document).ready(function() {
											jQuery('#AccountNumber').validateCreditCard(function(result) {
												var cardtypenames = {
													"amex"                      : "American Express",
													"diners_club_carte_blanche" : "Diners Club Carte Blanche",
													"diners_club_international" : "Diners Club International",
													"discover"                  : "Discover",
													"jcb"                       : "JCB",
													"laser"                     : "Laser",
													"maestro"                   : "Maestro",
													"mastercard"                : "Mastercard",
													"visa"                      : "Visa",
													"visa_electron"             : "Visa Electron"
												};

												if(result.card_type)
													jQuery('#CardType').val(cardtypenames[result.card_type.name]);
												else
													jQuery('#CardType').val('Unknown Card Type');
											});
									});
									-->
								</script>
								<?php
								}
							?>
						<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_payment-account-number', 'dmrfid_payment-account-number' ); ?>">
							<label for="AccountNumber"><?php _e('Card Number', 'paid-memberships-pro' );?></label>
							<input id="AccountNumber" name="AccountNumber" class="<?php echo dmrfid_get_element_class( 'input', 'AccountNumber' );?>" type="text" size="25" value="<?php echo esc_attr($AccountNumber)?>" autocomplete="off" />
						</div>
						<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_payment-expiration', 'dmrfid_payment-expiration' ); ?>">
							<label for="ExpirationMonth"><?php _e('Expiration Date', 'paid-memberships-pro' );?></label>
							<select id="ExpirationMonth" name="ExpirationMonth" class="<?php echo dmrfid_get_element_class( '', 'ExpirationMonth' ); ?>">
								<option value="01" <?php if($ExpirationMonth == "01") { ?>selected="selected"<?php } ?>>01</option>
								<option value="02" <?php if($ExpirationMonth == "02") { ?>selected="selected"<?php } ?>>02</option>
								<option value="03" <?php if($ExpirationMonth == "03") { ?>selected="selected"<?php } ?>>03</option>
								<option value="04" <?php if($ExpirationMonth == "04") { ?>selected="selected"<?php } ?>>04</option>
								<option value="05" <?php if($ExpirationMonth == "05") { ?>selected="selected"<?php } ?>>05</option>
								<option value="06" <?php if($ExpirationMonth == "06") { ?>selected="selected"<?php } ?>>06</option>
								<option value="07" <?php if($ExpirationMonth == "07") { ?>selected="selected"<?php } ?>>07</option>
								<option value="08" <?php if($ExpirationMonth == "08") { ?>selected="selected"<?php } ?>>08</option>
								<option value="09" <?php if($ExpirationMonth == "09") { ?>selected="selected"<?php } ?>>09</option>
								<option value="10" <?php if($ExpirationMonth == "10") { ?>selected="selected"<?php } ?>>10</option>
								<option value="11" <?php if($ExpirationMonth == "11") { ?>selected="selected"<?php } ?>>11</option>
								<option value="12" <?php if($ExpirationMonth == "12") { ?>selected="selected"<?php } ?>>12</option>
							</select>/<select id="ExpirationYear" name="ExpirationYear" class="<?php echo dmrfid_get_element_class( '', 'ExpirationYear' ); ?>">
								<?php
									for($i = date_i18n("Y"); $i < date_i18n("Y") + 10; $i++)
									{
								?>
									<option value="<?php echo $i?>" <?php if($ExpirationYear == $i) { ?>selected="selected"<?php } ?>><?php echo $i?></option>
								<?php
									}
								?>
							</select>
						</div>
						<?php
							$dmrfid_show_cvv = apply_filters("dmrfid_show_cvv", true);
							if($dmrfid_show_cvv) {
								if ( true == ini_get('allow_url_include') ) {
									$cvv_template = dmrfid_loadTemplate('popup-cvv', 'url', 'pages', 'html');
								} else {
									$cvv_template = plugins_url( 'paid-memberships-pro/pages/popup-cvv.html', DMRFID_DIR );
								}
							?>
							<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_payment-cvv', 'dmrfid_payment-cvv' ); ?>">
								<label for="CVV"><?php _e('CVV', 'paid-memberships-pro' );?></label>
								<input id="CVV" name="CVV" type="text" size="4" value="<?php if(!empty($_REQUEST['CVV'])) { echo esc_attr($_REQUEST['CVV']); }?>" class="<?php echo dmrfid_get_element_class( 'input', 'CVV ');?>" />  <small>(<a href="javascript:void(0);" onclick="javascript:window.open('<?php echo dmrfid_https_filter($cvv_template); ?>','cvv','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=600, height=475');"><?php _e("what's this?", 'paid-memberships-pro' );?></a>)</small>
							</div>
						<?php } ?>
					</div> <!-- end dmrfid_checkout-fields -->
				</div> <!-- end dmrfid_payment_information_fields -->
			<?php
			}
			?>

			<?php do_action("dmrfid_billing_before_submit_button"); ?>

			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_submit' ); ?>">
				<hr />
				<input type="hidden" name="update-billing" value="1" />
				<input type="submit" class="<?php echo dmrfid_get_element_class( 'dmrfid_btn dmrfid_btn-submit', 'dmrfid_btn-submit' ); ?>" value="<?php _e('Update', 'paid-memberships-pro' );?>" />
				<input type="button" name="cancel" class="<?php echo dmrfid_get_element_class( 'dmrfid_btn dmrfid_btn-cancel', 'dmrfid_btn-cancel' ); ?>" value="<?php _e('Cancel', 'paid-memberships-pro' );?>" onclick="location.href='<?php echo dmrfid_url("account")?>';" />
			</div>
		</form>
		<script>
			<!--
			// Find ALL <form> tags on your page
			jQuery('form').submit(function(){
				// On submit disable its submit button
				jQuery('input[type=submit]', this).attr('disabled', 'disabled');
				jQuery('input[type=image]', this).attr('disabled', 'disabled');
			});
			-->
		</script>
		</div> <!-- end dmrfid_level-ID -->
	<?php } ?>

<?php } else { // End for recurring level check.
	// Check to see if the user has a cancelled order
	$order = new MemberOrder();
	$order->getLastMemberOrder( $current_user->ID, array( 'cancelled', 'expired', 'admin_cancelled' ) );

	if ( isset( $order->membership_id ) && ! empty( $order->membership_id ) && empty( $level->id ) ) {
		$level = dmrfid_getLevel( $order->membership_id );

		// If no level check for a default level.
		if ( empty( $level ) || ! $level->allow_signups ) {
			$default_level_id = apply_filters( 'dmrfid_default_level', 0 );
		}

		// Show the correct checkout link.
		if ( ! empty( $level ) && ! empty( $level->allow_signups ) ) {
			$url = dmrfid_url( 'checkout', '?level=' . $level->id );
			printf( __( "Your membership is not active. <a href='%s'>Renew now.</a>", 'paid-memberships-pro' ), $url );
		} elseif ( ! empty( $default_level_id ) ) {
			$url = dmrfid_url( 'checkout', '?level=' . $default_level_id );
			printf( __( "You do not have an active membership. <a href='%s'>Register here.</a>", 'paid-memberships-pro' ), $url );
		} else {
			$url = dmrfid_url( 'levels' );
			printf( __( "You do not have an active membership. <a href='%s'>Choose a membership level.</a>", 'paid-memberships-pro' ), $url );
		}
	} else { ?>
		<p><?php _e("This subscription is not recurring. So you don't need to update your billing information.", 'paid-memberships-pro' );?></p>
	<?php }
} ?>
</div> <!-- end dmrfid_billing_wrap -->
