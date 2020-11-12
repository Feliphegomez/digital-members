<?php
	global $gateway, $dmrfid_review, $skip_account_fields, $dmrfid_paypal_token, $wpdb, $current_user, $dmrfid_msg, $dmrfid_msgt, $dmrfid_requirebilling, $dmrfid_level, $dmrfid_levels, $tospage, $dmrfid_show_discount_code, $dmrfid_error_fields;
	global $discount_code, $username, $password, $password2, $bfirstname, $blastname, $baddress1, $baddress2, $bcity, $bstate, $bzipcode, $bcountry, $bphone, $bemail, $bconfirmemail, $CardType, $AccountNumber, $ExpirationMonth,$ExpirationYear;

	/**
	 * Filter to set if DmRFID uses email or text as the type for email field inputs.
	 *
	 * @since 1.8.4.5
	 *
	 * @param bool $use_email_type, true to use email type, false to use text type
	 */
	$dmrfid_email_field_type = apply_filters('dmrfid_email_field_type', true);

	// Set the wrapping class for the checkout div based on the default gateway;
	$default_gateway = dmrfid_getOption( 'gateway' );
	if ( empty( $default_gateway ) ) {
		$dmrfid_checkout_gateway_class = 'dmrfid_checkout_gateway-none';
	} else {
		$dmrfid_checkout_gateway_class = 'dmrfid_checkout_gateway-' . $default_gateway;
	}
?>
<div id="dmrfid_level-<?php echo $dmrfid_level->id; ?>" class="<?php echo dmrfid_get_element_class( $dmrfid_checkout_gateway_class, 'dmrfid_level-' . $dmrfid_level->id ); ?>">
<form id="dmrfid_form" class="<?php echo dmrfid_get_element_class( 'dmrfid_form' ); ?>" action="<?php if(!empty($_REQUEST['review'])) echo dmrfid_url("checkout", "?level=" . $dmrfid_level->id); ?>" method="post">

	<input type="hidden" id="level" name="level" value="<?php echo esc_attr($dmrfid_level->id) ?>" />
	<input type="hidden" id="checkjavascript" name="checkjavascript" value="1" />
	<?php if ($discount_code && $dmrfid_review) { ?>
		<input class="<?php echo dmrfid_get_element_class( 'input dmrfid_alter_price', 'discount_code' ); ?>" id="discount_code" name="discount_code" type="hidden" size="20" value="<?php echo esc_attr($discount_code) ?>" />
	<?php } ?>

	<?php if($dmrfid_msg) { ?>
		<div id="dmrfid_message" class="<?php echo dmrfid_get_element_class( 'dmrfid_message ' . $dmrfid_msgt, $dmrfid_msgt ); ?>"><?php echo $dmrfid_msg?></div>
	<?php } else { ?>
		<div id="dmrfid_message" class="<?php echo dmrfid_get_element_class( 'dmrfid_message' ); ?>" style="display: none;"></div>
	<?php } ?>

	<?php if($dmrfid_review) { ?>
		<p><?php _e('Almost done. Review the membership information and pricing below then <strong>click the "Complete Payment" button</strong> to finish your order.', 'paid-memberships-pro' );?></p>
	<?php } ?>

	<?php
		$include_pricing_fields = apply_filters( 'dmrfid_include_pricing_fields', true );
		if ( $include_pricing_fields ) {
		?>
		<div id="dmrfid_pricing_fields" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout', 'dmrfid_pricing_fields' ); ?>">
			<h3>
				<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-name' ); ?>"><?php _e('Membership Level', 'paid-memberships-pro' );?></span>
				<?php if(count($dmrfid_levels) > 1) { ?><span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-msg' ); ?>"><a href="<?php echo dmrfid_url("levels"); ?>"><?php _e('change', 'paid-memberships-pro' );?></a></span><?php } ?>
			</h3>
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields' ); ?>">
				<p>
					<?php printf(__('You have selected the <strong>%s</strong> membership level.', 'paid-memberships-pro' ), $dmrfid_level->name);?>
				</p>

				<?php
					/**
					 * All devs to filter the level description at checkout.
					 * We also have a function in includes/filters.php that applies the the_content filters to this description.
					 * @param string $description The level description.
					 * @param object $dmrfid_level The DmRFID Level object.
					 */
					$level_description = apply_filters('dmrfid_level_description', $dmrfid_level->description, $dmrfid_level);
					if(!empty($level_description))
						echo $level_description;
				?>

				<div id="dmrfid_level_cost">
					<?php if($discount_code && dmrfid_checkDiscountCode($discount_code)) { ?>
						<?php printf(__('<p class="' . dmrfid_get_element_class( 'dmrfid_level_discount_applied' ) . '">The <strong>%s</strong> code has been applied to your order.</p>', 'paid-memberships-pro' ), $discount_code);?>
					<?php } ?>
					<?php echo wpautop(dmrfid_getLevelCost($dmrfid_level)); ?>
					<?php echo wpautop(dmrfid_getLevelExpiration($dmrfid_level)); ?>
				</div>

				<?php do_action("dmrfid_checkout_after_level_cost"); ?>

				<?php if($dmrfid_show_discount_code) { ?>
					<?php if($discount_code && !$dmrfid_review) { ?>
						<p id="other_discount_code_p" class="<?php echo dmrfid_get_element_class( 'dmrfid_small', 'other_discount_code_p' ); ?>"><a id="other_discount_code_a" href="#discount_code"><?php _e('Click here to change your discount code.', 'paid-memberships-pro' );?></a></p>
					<?php } elseif(!$dmrfid_review) { ?>
						<p id="other_discount_code_p" class="<?php echo dmrfid_get_element_class( 'dmrfid_small', 'other_discount_code_p' ); ?>"><?php _e('Do you have a discount code?', 'paid-memberships-pro' );?> <a id="other_discount_code_a" href="#discount_code"><?php _e('Click here to enter your discount code', 'paid-memberships-pro' );?></a>.</p>
					<?php } elseif($dmrfid_review && $discount_code) { ?>
						<p><strong><?php _e('Discount Code', 'paid-memberships-pro' );?>:</strong> <?php echo $discount_code?></p>
					<?php } ?>
				<?php } ?>

				<?php if($dmrfid_show_discount_code) { ?>
				<div id="other_discount_code_tr" style="display: none;">
					<label for="other_discount_code"><?php _e('Discount Code', 'paid-memberships-pro' );?></label>
					<input id="other_discount_code" name="other_discount_code" type="text" class="<?php echo dmrfid_get_element_class( 'input dmrfid_alter_price', 'other_discount_code' ); ?>" size="20" value="<?php echo esc_attr($discount_code); ?>" />
					<input type="button" name="other_discount_code_button" id="other_discount_code_button" value="<?php _e('Apply', 'paid-memberships-pro' );?>" />
				</div>
				<?php } ?>
			</div> <!-- end dmrfid_checkout-fields -->
		</div> <!-- end dmrfid_pricing_fields -->
		<?php
		} // if ( $include_pricing_fields )
	?>

	<?php
		do_action('dmrfid_checkout_after_pricing_fields');
	?>

	<?php if(!$skip_account_fields && !$dmrfid_review) { ?>

	<?php 
		// Get discount code from URL parameter, so if the user logs in it will keep it applied.
		$discount_code_link = !empty( $discount_code) ? '&discount_code=' . $discount_code : ''; 
	?>
	<div id="dmrfid_user_fields" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout', 'dmrfid_user_fields' ); ?>">
		<hr />
		<h3>
			<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-name' ); ?>"><?php _e('Account Information', 'paid-memberships-pro' );?></span>
			<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-msg' ); ?>"><?php _e('Already have an account?', 'paid-memberships-pro' );?> <a href="<?php echo wp_login_url( apply_filters( 'dmrfid_checkout_login_redirect', dmrfid_url("checkout", "?level=" . $dmrfid_level->id . $discount_code_link) ) ); ?>"><?php _e('Log in here', 'paid-memberships-pro' );?></a></span>
		</h3>
		<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields' ); ?>">
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-username', 'dmrfid_checkout-field-username' ); ?>">
				<label for="username"><?php _e('Username', 'paid-memberships-pro' );?></label>
				<input id="username" name="username" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'username' ); ?>" size="30" value="<?php echo esc_attr($username); ?>" />
			</div> <!-- end dmrfid_checkout-field-username -->

			<?php
				do_action('dmrfid_checkout_after_username');
			?>

			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-password', 'dmrfid_checkout-field-password' ); ?>">
				<label for="password"><?php _e('Password', 'paid-memberships-pro' );?></label>
				<input id="password" name="password" type="password" class="<?php echo dmrfid_get_element_class( 'input', 'password' ); ?>" size="30" value="<?php echo esc_attr($password); ?>" />
			</div> <!-- end dmrfid_checkout-field-password -->

			<?php
				$dmrfid_checkout_confirm_password = apply_filters("dmrfid_checkout_confirm_password", true);
				if($dmrfid_checkout_confirm_password) { ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-password2', 'dmrfid_checkout-field-password2' ); ?>">
						<label for="password2"><?php _e('Confirm Password', 'paid-memberships-pro' );?></label>
						<input id="password2" name="password2" type="password" class="<?php echo dmrfid_get_element_class( 'input', 'password2' ); ?>" size="30" value="<?php echo esc_attr($password2); ?>" />
					</div> <!-- end dmrfid_checkout-field-password2 -->
				<?php } else { ?>
					<input type="hidden" name="password2_copy" value="1" />
				<?php }
			?>

			<?php
				do_action('dmrfid_checkout_after_password');
			?>

			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bemail', 'dmrfid_checkout-field-bemail' ); ?>">
				<label for="bemail"><?php _e('Email Address', 'paid-memberships-pro' );?></label>
				<input id="bemail" name="bemail" type="<?php echo ($dmrfid_email_field_type ? 'email' : 'text'); ?>" class="<?php echo dmrfid_get_element_class( 'input', 'bemail' ); ?>" size="30" value="<?php echo esc_attr($bemail); ?>" />
			</div> <!-- end dmrfid_checkout-field-bemail -->

			<?php
				$dmrfid_checkout_confirm_email = apply_filters("dmrfid_checkout_confirm_email", true);
				if($dmrfid_checkout_confirm_email) { ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bconfirmemail', 'dmrfid_checkout-field-bconfirmemail' ); ?>">
						<label for="bconfirmemail"><?php _e('Confirm Email Address', 'paid-memberships-pro' );?></label>
						<input id="bconfirmemail" name="bconfirmemail" type="<?php echo ($dmrfid_email_field_type ? 'email' : 'text'); ?>" class="<?php echo dmrfid_get_element_class( 'input', 'bconfirmemail' ); ?>" size="30" value="<?php echo esc_attr($bconfirmemail); ?>" />
					</div> <!-- end dmrfid_checkout-field-bconfirmemail -->
				<?php } else { ?>
					<input type="hidden" name="bconfirmemail_copy" value="1" />
				<?php }
			?>

			<?php
				do_action('dmrfid_checkout_after_email');
			?>

			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_hidden' ); ?>">
				<label for="fullname"><?php _e('Full Name', 'paid-memberships-pro' );?></label>
				<input id="fullname" name="fullname" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'fullname' ); ?>" size="30" value="" autocomplete="off"/> <strong><?php _e('LEAVE THIS BLANK', 'paid-memberships-pro' );?></strong>
			</div> <!-- end dmrfid_hidden -->

			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_captcha', 'dmrfid_captcha' ); ?>">
			<?php
				global $recaptcha, $recaptcha_publickey;
				if($recaptcha == 2 || ($recaptcha == 1 && dmrfid_isLevelFree($dmrfid_level))) {
					echo dmrfid_recaptcha_get_html($recaptcha_publickey, NULL, true);
				}
			?>
			</div> <!-- end dmrfid_captcha -->

			<?php
				do_action('dmrfid_checkout_after_captcha');
			?>
		</div>  <!-- end dmrfid_checkout-fields -->
	</div> <!-- end dmrfid_user_fields -->
	<?php } elseif($current_user->ID && !$dmrfid_review) { ?>
		<div id="dmrfid_account_loggedin" class="<?php echo dmrfid_get_element_class( 'dmrfid_message dmrfid_alert', 'dmrfid_account_loggedin' ); ?>">
			<?php printf(__('You are logged in as <strong>%s</strong>. If you would like to use a different account for this membership, <a href="%s">log out now</a>.', 'paid-memberships-pro' ), $current_user->user_login, wp_logout_url($_SERVER['REQUEST_URI'])); ?>
		</div> <!-- end dmrfid_account_loggedin -->
	<?php } ?>

	<?php
		do_action('dmrfid_checkout_after_user_fields');
	?>

	<?php
		do_action('dmrfid_checkout_boxes');
	?>

	<?php if(dmrfid_getGateway() == "paypal" && empty($dmrfid_review) && true == apply_filters('dmrfid_include_payment_option_for_paypal', true ) ) { ?>
	<div id="dmrfid_payment_method" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout', 'dmrfid_payment_method' ); ?>" <?php if(!$dmrfid_requirebilling) { ?>style="display: none;"<?php } ?>>
		<hr />
		<h3>
			<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-name' ); ?>"><?php _e('Choose your Payment Method', 'paid-memberships-pro' ); ?></span>
		</h3>
		<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields' ); ?>">
			<span class="<?php echo dmrfid_get_element_class( 'gateway_paypal' ); ?>">
				<input type="radio" name="gateway" value="paypal" <?php if(!$gateway || $gateway == "paypal") { ?>checked="checked"<?php } ?> />
				<a href="javascript:void(0);" class="<?php echo dmrfid_get_element_class( 'dmrfid_radio' ); ?>"><?php _e('Check Out with a Credit Card Here', 'paid-memberships-pro' );?></a>
			</span>
			<span class="<?php echo dmrfid_get_element_class( 'gateway_paypalexpress' ); ?>">
				<input type="radio" name="gateway" value="paypalexpress" <?php if($gateway == "paypalexpress") { ?>checked="checked"<?php } ?> />
				<a href="javascript:void(0);" class="<?php echo dmrfid_get_element_class( 'dmrfid_radio' ); ?>"><?php _e('Check Out with PayPal', 'paid-memberships-pro' );?></a>
			</span>
		</div> <!-- end dmrfid_checkout-fields -->
	</div> <!-- end dmrfid_payment_method -->
	<?php } ?>

	<?php
		$dmrfid_include_billing_address_fields = apply_filters('dmrfid_include_billing_address_fields', true);
		if($dmrfid_include_billing_address_fields) { ?>
	<div id="dmrfid_billing_address_fields" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout', 'dmrfid_billing_address_fields' ); ?>" <?php if(!$dmrfid_requirebilling || apply_filters("dmrfid_hide_billing_address_fields", false) ){ ?>style="display: none;"<?php } ?>>
		<hr />
		<h3>
			<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-name' ); ?>"><?php _e('Billing Address', 'paid-memberships-pro' );?></span>
		</h3>
		<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields' ); ?>">
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bfirstname', 'dmrfid_checkout-field-bfirstname' ); ?>">
				<label for="bfirstname"><?php _e('First Name', 'paid-memberships-pro' );?></label>
				<input id="bfirstname" name="bfirstname" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bfirstname' ); ?>" size="30" value="<?php echo esc_attr($bfirstname); ?>" />
			</div> <!-- end dmrfid_checkout-field-bfirstname -->
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-blastname', 'dmrfid_checkout-field-blastname' ); ?>">
				<label for="blastname"><?php _e('Last Name', 'paid-memberships-pro' );?></label>
				<input id="blastname" name="blastname" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'blastname' ); ?>" size="30" value="<?php echo esc_attr($blastname); ?>" />
			</div> <!-- end dmrfid_checkout-field-blastname -->
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-baddress1', 'dmrfid_checkout-field-baddress1' ); ?>">
				<label for="baddress1"><?php _e('Address 1', 'paid-memberships-pro' );?></label>
				<input id="baddress1" name="baddress1" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'baddress1' ); ?>" size="30" value="<?php echo esc_attr($baddress1); ?>" />
			</div> <!-- end dmrfid_checkout-field-baddress1 -->
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-baddress2', 'dmrfid_checkout-field-baddress2' ); ?>">
				<label for="baddress2"><?php _e('Address 2', 'paid-memberships-pro' );?></label>
				<input id="baddress2" name="baddress2" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'baddress2' ); ?>" size="30" value="<?php echo esc_attr($baddress2); ?>" />
			</div> <!-- end dmrfid_checkout-field-baddress2 -->
			<?php
				$longform_address = apply_filters("dmrfid_longform_address", true);
				if($longform_address) { ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bcity', 'dmrfid_checkout-field-bcity' ); ?>">
						<label for="bcity"><?php _e('City', 'paid-memberships-pro' );?></label>
						<input id="bcity" name="bcity" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bcity' ); ?>" size="30" value="<?php echo esc_attr($bcity); ?>" />
					</div> <!-- end dmrfid_checkout-field-bcity -->
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bstate', 'dmrfid_checkout-field-bstate' ); ?>">
						<label for="bstate"><?php _e('State', 'paid-memberships-pro' );?></label>
						<input id="bstate" name="bstate" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bstate' ); ?>" size="30" value="<?php echo esc_attr($bstate); ?>" />
					</div> <!-- end dmrfid_checkout-field-bstate -->
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bzipcode', 'dmrfid_checkout-field-bzipcode' ); ?>">
						<label for="bzipcode"><?php _e('Postal Code', 'paid-memberships-pro' );?></label>
						<input id="bzipcode" name="bzipcode" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bzipcode' ); ?>" size="30" value="<?php echo esc_attr($bzipcode); ?>" />
					</div> <!-- end dmrfid_checkout-field-bzipcode -->
				<?php } else { ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bcity_state_zip', 'dmrfid_checkout-field-bcity_state_zip' ); ?>">
						<label for="bcity_state_zip' ); ?>"><?php _e('City, State Zip', 'paid-memberships-pro' );?></label>
						<input id="bcity" name="bcity" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bcity' ); ?>" size="14" value="<?php echo esc_attr($bcity); ?>" />,
						<?php
							$state_dropdowns = apply_filters("dmrfid_state_dropdowns", false);
							if($state_dropdowns === true || $state_dropdowns == "names") {
								global $dmrfid_states;
								?>
								<select name="bstate" class="<?php echo dmrfid_get_element_class( '', 'bstate' ); ?>">
									<option value="">--</option>
									<?php
										foreach($dmrfid_states as $ab => $st) { ?>
											<option value="<?php echo esc_attr($ab);?>" <?php if($ab == $bstate) { ?>selected="selected"<?php } ?>><?php echo $st;?></option>
									<?php } ?>
								</select>
							<?php } elseif($state_dropdowns == "abbreviations") {
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
							<?php } else { ?>
								<input id="bstate" name="bstate" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bstate' ); ?>" size="2" value="<?php echo esc_attr($bstate); ?>" />
						<?php } ?>
						<input id="bzipcode" name="bzipcode" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bzipcode' ); ?>" size="5" value="<?php echo esc_attr($bzipcode); ?>" />
					</div> <!-- end dmrfid_checkout-field-bcity_state_zip -->
			<?php } ?>

			<?php
				$show_country = apply_filters("dmrfid_international_addresses", true);
				if($show_country) { ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bcountry', 'dmrfid_checkout-field-bcountry' ); ?>">
						<label for="bcountry"><?php _e('Country', 'paid-memberships-pro' );?></label>
						<select name="bcountry" id="bcountry" class="<?php echo dmrfid_get_element_class( '', 'bcountry' ); ?>">
						<?php
							global $dmrfid_countries, $dmrfid_default_country;
							if(!$bcountry) {
								$bcountry = $dmrfid_default_country;
							}
							foreach($dmrfid_countries as $abbr => $country) { ?>
								<option value="<?php echo $abbr?>" <?php if($abbr == $bcountry) { ?>selected="selected"<?php } ?>><?php echo $country?></option>
							<?php } ?>
						</select>
					</div> <!-- end dmrfid_checkout-field-bcountry -->
				<?php } else { ?>
					<input type="hidden" name="bcountry" value="US" />
				<?php } ?>
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bphone', 'dmrfid_checkout-field-bphone' ); ?>">
				<label for="bphone"><?php _e('Phone', 'paid-memberships-pro' );?></label>
				<input id="bphone" name="bphone" type="text" class="<?php echo dmrfid_get_element_class( 'input', 'bphone' ); ?>" size="30" value="<?php echo esc_attr(formatPhone($bphone)); ?>" />
			</div> <!-- end dmrfid_checkout-field-bphone -->
			<?php if($skip_account_fields) { ?>
			<?php
				if($current_user->ID) {
					if(!$bemail && $current_user->user_email) {
						$bemail = $current_user->user_email;
					}
					if(!$bconfirmemail && $current_user->user_email) {
						$bconfirmemail = $current_user->user_email;
					}
				}
			?>
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bemail', 'dmrfid_checkout-field-bemail' ); ?>">
				<label for="bemail"><?php _e('Email Address', 'paid-memberships-pro' );?></label>
				<input id="bemail" name="bemail" type="<?php echo ($dmrfid_email_field_type ? 'email' : 'text'); ?>" class="<?php echo dmrfid_get_element_class( 'input', 'bemail' ); ?>" size="30" value="<?php echo esc_attr($bemail); ?>" />
			</div> <!-- end dmrfid_checkout-field-bemail -->
			<?php
				$dmrfid_checkout_confirm_email = apply_filters("dmrfid_checkout_confirm_email", true);
				if($dmrfid_checkout_confirm_email) { ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_checkout-field-bconfirmemail', 'dmrfid_checkout-field-bconfirmemail' ); ?>">
						<label for="bconfirmemail"><?php _e('Confirm Email', 'paid-memberships-pro' );?></label>
						<input id="bconfirmemail" name="bconfirmemail" type="<?php echo ($dmrfid_email_field_type ? 'email' : 'text'); ?>" class="<?php echo dmrfid_get_element_class( 'input', 'bconfirmemail' ); ?>" size="30" value="<?php echo esc_attr($bconfirmemail); ?>" />
					</div> <!-- end dmrfid_checkout-field-bconfirmemail -->
				<?php } else { ?>
					<input type="hidden" name="bconfirmemail_copy" value="1" />
				<?php } ?>
			<?php } ?>
		</div> <!-- end dmrfid_checkout-fields -->
	</div> <!--end dmrfid_billing_address_fields -->
	<?php } ?>

	<?php do_action("dmrfid_checkout_after_billing_fields"); ?>

	<?php
		$dmrfid_accepted_credit_cards = dmrfid_getOption("accepted_credit_cards");
		$dmrfid_accepted_credit_cards = explode(",", $dmrfid_accepted_credit_cards);
		$dmrfid_accepted_credit_cards_string = dmrfid_implodeToEnglish($dmrfid_accepted_credit_cards);
	?>

	<?php
		$dmrfid_include_payment_information_fields = apply_filters("dmrfid_include_payment_information_fields", true);
		if($dmrfid_include_payment_information_fields) { ?>
		<div id="dmrfid_payment_information_fields" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout', 'dmrfid_payment_information_fields' ); ?>" <?php if(!$dmrfid_requirebilling || apply_filters("dmrfid_hide_payment_information_fields", false) ) { ?>style="display: none;"<?php } ?>>
			<hr />
			<h3>
				<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-name' ); ?>"><?php _e('Payment Information', 'paid-memberships-pro' );?></span>
				<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-msg' ); ?>"><?php printf(__('We Accept %s', 'paid-memberships-pro' ), $dmrfid_accepted_credit_cards_string);?></span>
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
							<select id="CardType" name="CardType" class="<?php echo dmrfid_get_element_class( '', 'CardType' ); ?>">
								<?php foreach($dmrfid_accepted_credit_cards as $cc) { ?>
									<option value="<?php echo $cc; ?>" <?php if($CardType == $cc) { ?>selected="selected"<?php } ?>><?php echo $cc; ?></option>
								<?php } ?>
							</select>
						</div>
					<?php } else { ?>
						<input type="hidden" id="CardType" name="CardType" value="<?php echo esc_attr($CardType);?>" />						
					<?php } ?>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_payment-account-number', 'dmrfid_payment-account-number' ); ?>">
					<label for="AccountNumber"><?php _e('Card Number', 'paid-memberships-pro' );?></label>
					<input id="AccountNumber" name="AccountNumber" class="<?php echo dmrfid_get_element_class( 'input', 'AccountNumber' ); ?>" type="text" size="30" value="<?php echo esc_attr($AccountNumber); ?>" data-encrypted-name="number" autocomplete="off" />
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
							$num_years = apply_filters( 'dmrfid_num_expiration_years', 10 );

							for($i = date_i18n("Y"); $i < intval( date_i18n("Y") ) + intval( $num_years ); $i++)
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
					if($dmrfid_show_cvv) { ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_payment-cvv', 'dmrfid_payment-cvv' ); ?>">
						<label for="CVV"><?php _e('Security Code (CVC)', 'paid-memberships-pro' );?></label>
						<input id="CVV" name="CVV" type="text" size="4" value="<?php if(!empty($_REQUEST['CVV'])) { echo esc_attr($_REQUEST['CVV']); }?>" class="<?php echo dmrfid_get_element_class( 'input', 'CVV' ); ?>" />  <small>(<a href="javascript:void(0);" onclick="javascript:window.open('<?php echo dmrfid_https_filter(DMRFID_URL); ?>/pages/popup-cvv.html','cvv','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=600, height=475');"><?php _e("what's this?", 'paid-memberships-pro' );?></a>)</small>
					</div>
				<?php } ?>
				<?php if($dmrfid_show_discount_code) { ?>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field dmrfid_payment-discount-code', 'dmrfid_payment-discount-code' ); ?>">
						<label for="discount_code"><?php _e('Discount Code', 'paid-memberships-pro' );?></label>
						<input class="<?php echo dmrfid_get_element_class( 'input dmrfid_alter_price', 'discount_code' ); ?>" id="discount_code" name="discount_code" type="text" size="10" value="<?php echo esc_attr($discount_code); ?>" />
						<input type="button" id="discount_code_button" name="discount_code_button" value="<?php _e('Apply', 'paid-memberships-pro' );?>" />
						<p id="discount_code_message" class="<?php echo dmrfid_get_element_class( 'dmrfid_message', 'discount_code_message' ); ?>" style="display: none;"></p>
					</div>
				<?php } ?>
			</div> <!-- end dmrfid_checkout-fields -->
			<?php if(!empty($sslseal)) { ?>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields-rightcol dmrfid_sslseal', 'dmrfid_sslseal' ); ?>"><?php echo stripslashes($sslseal); ?></div>
			</div> <!-- end dmrfid_checkout-fields-display-seal -->
			<?php } ?>
		</div> <!-- end dmrfid_payment_information_fields -->
	<?php } ?>

	<?php do_action('dmrfid_checkout_after_payment_information_fields'); ?>

	<?php if($tospage && !$dmrfid_review) { ?>
		<div id="dmrfid_tos_fields" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout', 'dmrfid_tos_fields' ); ?>">
			<hr />
			<h3>
				<span class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-h3-name' ); ?>"><?php echo esc_html( $tospage->post_title );?></span>
			</h3>
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-fields' ); ?>">
				<div id="dmrfid_license" class="<?php echo dmrfid_get_element_class( 'dmrfid_checkout-field', 'dmrfid_license' ); ?>">
<?php 
	/**
	 * Hook to run formatting filters before displaying the content of your "Terms of Service" page at checkout.
	 *
	 * @since 2.4.1
	 *
	 * @param string $dmrfid_tos_content The content of the post assigned as the Terms of Service page.
	 * @param string $tospage The post assigned as the Terms of Service page.
	 *
	 * @return string $dmrfid_tos_content
	 */
	$dmrfid_tos_content = apply_filters( 'dmrfid_tos_content', do_shortcode( $tospage->post_content ), $tospage );
	echo $dmrfid_tos_content;
?>
				</div> <!-- end dmrfid_license -->
				<?php
					if ( isset( $_REQUEST['tos'] ) ) {
						$tos = intval( $_REQUEST['tos'] );
					} else {
						$tos = "";
					}
				?>
				<input type="checkbox" name="tos" value="1" id="tos" <?php checked( 1, $tos ); ?> /> <label class="<?php echo dmrfid_get_element_class( 'dmrfid_label-inline dmrfid_clickable', 'tos' ); ?>" for="tos"><?php printf(__('I agree to the %s', 'paid-memberships-pro' ), $tospage->post_title);?></label>
			</div> <!-- end dmrfid_checkout-fields -->
		</div> <!-- end dmrfid_tos_fields -->
		<?php
		}
	?>

	<?php do_action("dmrfid_checkout_after_tos_fields"); ?>

	<?php do_action("dmrfid_checkout_before_submit_button"); ?>

	<div class="<?php echo dmrfid_get_element_class( 'dmrfid_submit' ); ?>">
		<hr />
		<?php if ( $dmrfid_msg ) { ?>
			<div id="dmrfid_message_bottom" class="<?php echo dmrfid_get_element_class( 'dmrfid_message ' . $dmrfid_msgt, $dmrfid_msgt ); ?>"><?php echo $dmrfid_msg; ?></div>
		<?php } else { ?>
			<div id="dmrfid_message_bottom" class="<?php echo dmrfid_get_element_class( 'dmrfid_message' ); ?>" style="display: none;"></div>
		<?php } ?>

		<?php if($dmrfid_review) { ?>

			<span id="dmrfid_submit_span">
				<input type="hidden" name="confirm" value="1" />
				<input type="hidden" name="token" value="<?php echo esc_attr($dmrfid_paypal_token); ?>" />
				<input type="hidden" name="gateway" value="<?php echo esc_attr($gateway); ?>" />
				<input type="submit" id="dmrfid_btn-submit" class="<?php echo dmrfid_get_element_class( 'dmrfid_btn dmrfid_btn-submit-checkout', 'dmrfid_btn-submit-checkout' ); ?>" value="<?php _e('Complete Payment', 'paid-memberships-pro' );?> &raquo;" />
			</span>

		<?php } else { ?>

			<?php
				$dmrfid_checkout_default_submit_button = apply_filters('dmrfid_checkout_default_submit_button', true);
				if($dmrfid_checkout_default_submit_button)
				{
				?>
				<span id="dmrfid_submit_span">
					<input type="hidden" name="submit-checkout" value="1" />
					<input type="submit"  id="dmrfid_btn-submit" class="<?php echo dmrfid_get_element_class(  'dmrfid_btn dmrfid_btn-submit-checkout', 'dmrfid_btn-submit-checkout' ); ?>" value="<?php if($dmrfid_requirebilling) { _e('Submit and Check Out', 'paid-memberships-pro' ); } else { _e('Submit and Confirm', 'paid-memberships-pro' );}?> &raquo;" />
				</span>
				<?php
				}
			?>

		<?php } ?>

		<span id="dmrfid_processing_message" style="visibility: hidden;">
			<?php
				$processing_message = apply_filters("dmrfid_processing_message", __("Processing...", 'paid-memberships-pro' ));
				echo $processing_message;
			?>
		</span>
	</div>
</form>

<?php do_action('dmrfid_checkout_after_form'); ?>

</div> <!-- end dmrfid_level-ID -->
