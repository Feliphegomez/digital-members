<?php
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("dmrfid_paymentsettings")))
	{
		die(__("You do not have permissions to perform this action.", 'digital-members-rfid' ));
	}

	global $wpdb, $dmrfid_currency_symbol, $msg, $msgt;

	/*
		Since 2.0, we let each gateway define what options they have in the class files
	*/
	//define options
	$payment_options = array_unique(apply_filters("dmrfid_payment_options", array('gateway')));

	//check nonce for saving settings
	if (!empty($_REQUEST['savesettings']) && (empty($_REQUEST['dmrfid_paymentsettings_nonce']) || !check_admin_referer('savesettings', 'dmrfid_paymentsettings_nonce'))) {
		$msg = -1;
		$msgt = __("Are you sure you want to do that? Try again.", 'digital-members-rfid' );
		unset($_REQUEST['savesettings']);
	}

	//get/set settings
	if(!empty($_REQUEST['savesettings']))
	{
		/*
			Save any value that might have been passed in
		*/
		foreach($payment_options as $option) {
			//for now we make a special case for sslseal, but we need a way to specify sanitize functions for other fields
			if( in_array( $option, array( 'sslseal', 'instructions' ) ) ) {
				global $allowedposttags;
				$html = wp_kses(wp_unslash($_POST[$option]), $allowedposttags);
				update_option("dmrfid_{$option}", $html);
            } else {
				dmrfid_setOption($option);
			}
		}

		do_action( 'dmrfid_after_saved_payment_options', $payment_options );

		/*
			Some special case options still worked out here
		*/
		//credit cards
		$dmrfid_accepted_credit_cards = array();
		if(!empty($_REQUEST['creditcards_visa']))
			$dmrfid_accepted_credit_cards[] = "Visa";
		if(!empty($_REQUEST['creditcards_mastercard']))
			$dmrfid_accepted_credit_cards[] = "Mastercard";
		if(!empty($_REQUEST['creditcards_amex']))
			$dmrfid_accepted_credit_cards[] = "American Express";
		if(!empty($_REQUEST['creditcards_discover']))
			$dmrfid_accepted_credit_cards[] = "Discover";
		if(!empty($_REQUEST['creditcards_dinersclub']))
			$dmrfid_accepted_credit_cards[] = "Diners Club";
		if(!empty($_REQUEST['creditcards_enroute']))
			$dmrfid_accepted_credit_cards[] = "EnRoute";
		if(!empty($_REQUEST['creditcards_jcb']))
			$dmrfid_accepted_credit_cards[] = "JCB";

		dmrfid_setOption("accepted_credit_cards", implode(",", $dmrfid_accepted_credit_cards));

		//assume success
		$msg = true;
		$msgt = __("Your payment settings have been updated.", 'digital-members-rfid' );
	}

	/*
		Extract values for use later
	*/
	$payment_option_values = array();
	foreach($payment_options as $option)
		$payment_option_values[$option] = dmrfid_getOption($option);
	extract($payment_option_values);

	/*
		Some special cases that get worked out here.
	*/
	//make sure the tax rate is not > 1
	$tax_state = dmrfid_getOption("tax_state");
	$tax_rate = dmrfid_getOption("tax_rate");
	if((double)$tax_rate > 1)
	{
		//assume the entered X%
		$tax_rate = $tax_rate / 100;
		dmrfid_setOption("tax_rate", $tax_rate);
	}

	//accepted credit cards
	$dmrfid_accepted_credit_cards = $payment_option_values['accepted_credit_cards'];	//this var has the dmrfid_ prefix

	//default settings
	if(empty($gateway_environment))
	{
		$gateway_environment = "sandbox";
		dmrfid_setOption("gateway_environment", $gateway_environment);
	}
	if(empty($dmrfid_accepted_credit_cards))
	{
		$dmrfid_accepted_credit_cards = "Visa,Mastercard,American Express,Discover";
		dmrfid_setOption("accepted_credit_cards", $dmrfid_accepted_credit_cards);
	}
	$dmrfid_accepted_credit_cards = explode(",", $dmrfid_accepted_credit_cards);

	require_once(dirname(__FILE__) . "/admin_header.php");
?>

	<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('savesettings', 'dmrfid_paymentsettings_nonce');?>

        <h1 class="wp-heading-inline"><?php esc_html_e( 'Payment Gateway', 'digital-members-rfid' );?> &amp; <?php esc_html_e( 'SSL Settings', 'digital-members-rfid' ); ?></h1>
        <hr class="wp-header-end">

		<p><?php _e('Learn more about <a title="Digital Members RFID - SSL Settings" target="_blank" href="https://www.paidmembershipspro.com/documentation/initial-plugin-setup/ssl/?utm_source=plugin&utm_medium=dmrfid-paymentsettings&utm_campaign=documentation&utm_content=ssl&utm_term=link1">SSL</a> or <a title="Digital Members RFID - Payment Gateway Settings" target="_blank" href="https://www.paidmembershipspro.com/documentation/initial-plugin-setup/step-3-payment-gateway-security/?utm_source=plugin&utm_medium=dmrfid-paymentsettings&utm_campaign=documentation&utm_content=step-3-payment-gateway-security">Payment Gateway Settings</a>.', 'digital-members-rfid' ); ?></p>

		<table class="form-table">
		<tbody>
			<tr class="dmrfid_settings_divider">
				<td colspan="2">
					<hr />
					<h3><?php _e('Choose a Gateway', 'digital-members-rfid' ); ?></h3>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="gateway"><?php _e('Payment Gateway', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<select id="gateway" name="gateway" onchange="dmrfid_changeGateway(jQuery(this).val());">
						<?php
							$dmrfid_gateways = dmrfid_gateways();
							foreach($dmrfid_gateways as $dmrfid_gateway_name => $dmrfid_gateway_label)
							{
							?>
							<option value="<?php echo esc_attr($dmrfid_gateway_name);?>" <?php selected($gateway, $dmrfid_gateway_name);?>><?php echo $dmrfid_gateway_label;?></option>
							<?php
							}
						?>
					</select>
					<?php if( dmrfid_onlyFreeLevels() ) { ?>
						<div id="dmrfid-default-gateway-message" style="display:none;"><p class="description"><?php echo __( 'This gateway is for membership sites with Free levels or for sites that accept payment offline.', 'digital-members-rfid' ) . '<br/>' . __( 'It is not connected to a live gateway environment and cannot accept payments.', 'digital-members-rfid' ); ?></p></div>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="gateway_environment"><?php _e('Gateway Environment', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<select name="gateway_environment">
						<option value="sandbox" <?php selected( $gateway_environment, "sandbox" ); ?>><?php _e('Sandbox/Testing', 'digital-members-rfid' );?></option>
						<option value="live" <?php selected( $gateway_environment, "live" ); ?>><?php _e('Live/Production', 'digital-members-rfid' );?></option>
					</select>
					<script>
						function dmrfid_changeGateway(gateway)
						{
							//hide all gateway options
							jQuery('tr.gateway').hide();
							jQuery('tr.gateway_'+gateway).show();
							
							//hide sub settings and toggle them on based on triggers
							jQuery('tr.dmrfid_toggle_target').hide();
							jQuery( 'input[dmrfid_toggle_trigger_for]' ).each( function() {										
								if ( jQuery( this ).is( ':visible' ) ) {
									dmrfid_toggle_elements_by_selector( jQuery( this ).attr( 'dmrfid_toggle_trigger_for' ), jQuery( this ).prop( 'checked' ) );
								}
							});							

							if ( jQuery('#gateway').val() === '' ) {
								jQuery('#dmrfid-default-gateway-message').show();
							} else {
								jQuery('#dmrfid-default-gateway-message').hide();
							}
						}
						dmrfid_changeGateway(jQuery('#gateway').val());
					</script>
				</td>
			</tr>

			<?php /* Gateway Specific Settings */ ?>
			<?php do_action('dmrfid_payment_option_fields', $payment_option_values, $gateway); ?>

			<tr class="dmrfid_settings_divider">
				<td colspan="2">
					<hr />
					<h3><?php _e('Currency and Tax Settings', 'digital-members-rfid' ); ?></h3>
				</td>
			</tr>
			<tr class="gateway gateway_ <?php echo esc_attr(dmrfid_getClassesForPaymentSettingsField("currency"));?>" <?php if(!empty($gateway) && $gateway != "paypal" && $gateway != "paypalexpress" && $gateway != "check" && $gateway != "paypalstandard" && $gateway != "braintree" && $gateway != "twocheckout" && $gateway != "cybersource" && $gateway != "payflowpro" && $gateway != "stripe" && $gateway != "authorizenet" && $gateway != "gourl") { ?>style="display: none;"<?php } ?>>
				<th scope="row" valign="top">
					<label for="currency"><?php _e('Currency', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<select name="currency">
					<?php
						global $dmrfid_currencies;
						foreach($dmrfid_currencies as $ccode => $cdescription)
						{
							if(is_array($cdescription))
								$cdescription = $cdescription['name'];
						?>
						<option value="<?php echo $ccode?>" <?php if($currency == $ccode) { ?>selected="selected"<?php } ?>><?php echo $cdescription?></option>
						<?php
						}
					?>
					</select>
					<p class="description"><?php _e( 'Not all currencies will be supported by every gateway. Please check with your gateway.', 'digital-members-rfid' ); ?></p>
				</td>
			</tr>
			<tr class="gateway gateway_ <?php echo esc_attr(dmrfid_getClassesForPaymentSettingsField("accepted_credit_cards"));?>" <?php if(!empty($gateway) && $gateway != "authorizenet" && $gateway != "paypal" && $gateway != "stripe" && $gateway != "payflowpro" && $gateway != "braintree" && $gateway != "twocheckout" && $gateway != "cybersource") { ?>style="display: none;"<?php } ?>>
				<th scope="row" valign="top">
					<label for="creditcards"><?php _e('Accepted Credit Card Types', 'digital-members-rfid' );?></label>
				</th>
				<td>
					<input type="checkbox" id="creditcards_visa" name="creditcards_visa" value="1" <?php if(in_array("Visa", $dmrfid_accepted_credit_cards)) { ?>checked="checked"<?php } ?> /> <label for="creditcards_visa">Visa</label><br />
					<input type="checkbox" id="creditcards_mastercard" name="creditcards_mastercard" value="1" <?php if(in_array("Mastercard", $dmrfid_accepted_credit_cards)) { ?>checked="checked"<?php } ?> /> <label for="creditcards_mastercard">Mastercard</label><br />
					<input type="checkbox" id="creditcards_amex" name="creditcards_amex" value="1" <?php if(in_array("American Express", $dmrfid_accepted_credit_cards)) { ?>checked="checked"<?php } ?> /> <label for="creditcards_amex">American Express</label><br />
					<input type="checkbox" id="creditcards_discover" name="creditcards_discover" value="1" <?php if(in_array("Discover", $dmrfid_accepted_credit_cards)) { ?>checked="checked"<?php } ?> /> <label for="creditcards_discover">Discover</label><br />
					<input type="checkbox" id="creditcards_dinersclub" name="creditcards_dinersclub" value="1" <?php if(in_array("Diners Club", $dmrfid_accepted_credit_cards)) {?>checked="checked"<?php } ?> /> <label for="creditcards_dinersclub">Diner's Club</label><br />
					<input type="checkbox" id="creditcards_enroute" name="creditcards_enroute" value="1" <?php if(in_array("EnRoute", $dmrfid_accepted_credit_cards)) {?>checked="checked"<?php } ?> /> <label for="creditcards_enroute">EnRoute</label><br />
					<input type="checkbox" id="creditcards_jcb" name="creditcards_jcb" value="1" <?php if(in_array("JCB", $dmrfid_accepted_credit_cards)) {?>checked="checked"<?php } ?> /> <label for="creditcards_jcb">JCB</label><br />
				</td>
			</tr>
			<tr class="gateway gateway_ <?php echo esc_attr(dmrfid_getClassesForPaymentSettingsField("tax_rate"));?>" <?php if(!empty($gateway) && $gateway != "stripe" && $gateway != "authorizenet" && $gateway != "paypal" && $gateway != "paypalexpress" && $gateway != "check" && $gateway != "paypalstandard" && $gateway != "payflowpro" && $gateway != "braintree" && $gateway != "twocheckout" && $gateway != "cybersource") { ?>style="display: none;"<?php } ?>>
				<th scope="row" valign="top">
					<label for="tax"><?php _e('Sales Tax', 'digital-members-rfid' );?> (<?php _e('optional', 'digital-members-rfid' );?>)</label>
				</th>
				<td>
					<?php _e('Tax State', 'digital-members-rfid' );?>:
					<input type="text" id="tax_state" name="tax_state" value="<?php echo esc_attr($tax_state)?>" class="small-text" /> (<?php _e('abbreviation, e.g. "PA"', 'digital-members-rfid' );?>)
					&nbsp; <?php _e('Tax Rate', 'digital-members-rfid' ); ?>:
					<input type="text" id="tax_rate" name="tax_rate" size="10" value="<?php echo esc_attr($tax_rate)?>" class="small-text" /> (<?php _e('decimal, e.g. "0.06"', 'digital-members-rfid' );?>)
					<p class="description"><?php _e('US only. If values are given, tax will be applied for any members ordering from the selected state.<br />For non-US or more complex tax rules, use the <a target="_blank" href="https://www.paidmembershipspro.com/non-us-taxes-digital-members-rfid/?utm_source=plugin&utm_medium=dmrfid-paymentsettings&utm_campaign=blog&utm_content=non-us-taxes-digital-members-rfid">dmrfid_tax filter</a>.', 'digital-members-rfid' );?></p>
				</td>
			</tr>

			<tr class="dmrfid_settings_divider">
				<td colspan="2">
					<hr />
					<h3><?php _e('SSL Settings', 'digital-members-rfid' ); ?></h3>
				</td>
			</tr>
			<tr class="gateway gateway_ <?php echo esc_attr(dmrfid_getClassesForPaymentSettingsField("use_ssl"));?>">
				<th scope="row" valign="top">
					<label for="use_ssl"><?php _e('Force SSL', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<?php
						if( dmrfid_check_site_url_for_https() ) {
							//entire site is over HTTPS
							?>
							<p class="description"><?php _e( 'Your Site URL starts with https:// and so DmRFID will allow your entire site to be served over HTTPS.', 'digital-members-rfid' ); ?></p>
							<?php
						} else {
							//site is not over HTTPS, show setting
							?>
							<select id="use_ssl" name="use_ssl">
								<option value="0" <?php if(empty($use_ssl)) { ?>selected="selected"<?php } ?>><?php _e('No', 'digital-members-rfid' );?></option>
								<option value="1" <?php if(!empty($use_ssl) && $use_ssl == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes', 'digital-members-rfid' );?></option>
								<option value="2" <?php if(!empty($use_ssl) && $use_ssl == 2) { ?>selected="selected"<?php } ?>><?php _e('Yes (with JavaScript redirects)', 'digital-members-rfid' );?></option>
							</select>
							<p class="description"><?php _e('Recommended: Yes. Try the JavaScript redirects setting if you are having issues with infinite redirect loops.', 'digital-members-rfid' ); ?></p>
							<?php
						}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="sslseal"><?php _e('SSL Seal Code', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<textarea id="sslseal" name="sslseal" rows="3" cols="50" class="large-text"><?php echo stripslashes(esc_textarea($sslseal))?></textarea>
					<p class="description"><?php _e('Your <strong><a target="_blank" href="https://www.paidmembershipspro.com/documentation/initial-plugin-setup/ssl/?utm_source=plugin&utm_medium=dmrfid-paymentsettings&utm_campaign=documentation&utm_content=ssl&utm_term=link2">SSL Certificate</a></strong> must be installed by your web host. Use this field to display your seal or other trusted merchant images. This field does not accept JavaScript.', 'digital-members-rfid' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="nuclear_HTTPS"><?php _e('Extra HTTPS URL Filter', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<input type="checkbox" id="nuclear_HTTPS" name="nuclear_HTTPS" value="1" <?php if(!empty($nuclear_HTTPS)) { ?>checked="checked"<?php } ?> /> <label for="nuclear_HTTPS"><?php _e('Pass all generated HTML through a URL filter to add HTTPS to URLs used on secure pages. Check this if you are using SSL and have warnings on your checkout pages.', 'digital-members-rfid' );?></label>
				</td>
			</tr>

		</tbody>
		</table>
		<p class="submit">
			<input name="savesettings" type="submit" class="button button-primary" value="<?php _e('Save Settings', 'digital-members-rfid' );?>" />
		</p>
	</form>

<?php
	require_once(dirname(__FILE__) . "/admin_footer.php");
?>
