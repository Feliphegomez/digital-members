<div class="<?php echo dmrfid_get_element_class( 'dmrfid_confirmation_wrap' ); ?>">
<?php
	global $wpdb, $current_user, $dmrfid_invoice, $dmrfid_msg, $dmrfid_msgt;

	if($dmrfid_msg)
	{
	?>
		<div class="<?php echo dmrfid_get_element_class( 'dmrfid_message ' . $dmrfid_msgt, $dmrfid_msgt ); ?>"><?php echo wp_kses_post( $dmrfid_msg );?></div>
	<?php
	}

	if(empty($current_user->membership_level))
		$confirmation_message = "<p>" . __('Your payment has been submitted. Your membership will be activated shortly.', 'paid-memberships-pro' ) . "</p>";
	else
		$confirmation_message = "<p>" . sprintf(__('Thank you for your membership to %s. Your %s membership is now active.', 'paid-memberships-pro' ), get_bloginfo("name"), $current_user->membership_level->name) . "</p>";

	//confirmation message for this level
	$level_message = $wpdb->get_var("SELECT l.confirmation FROM $wpdb->dmrfid_membership_levels l LEFT JOIN $wpdb->dmrfid_memberships_users mu ON l.id = mu.membership_id WHERE mu.status = 'active' AND mu.user_id = '" . $current_user->ID . "' LIMIT 1");
	if(!empty($level_message))
		$confirmation_message .= "\n" . stripslashes($level_message) . "\n";
?>

<?php if(!empty($dmrfid_invoice) && !empty($dmrfid_invoice->id)) { ?>

	<?php
		$dmrfid_invoice->getUser();
		$dmrfid_invoice->getMembershipLevel();

		$confirmation_message .= "<p>" . sprintf(__('Below are details about your membership account and a receipt for your initial membership invoice. A welcome email with a copy of your initial membership invoice has been sent to %s.', 'paid-memberships-pro' ), $dmrfid_invoice->user->user_email) . "</p>";

		// Check instructions
		if ( $dmrfid_invoice->gateway == "check" && ! dmrfid_isLevelFree( $dmrfid_invoice->membership_level ) ) {
			$confirmation_message .= '<div class="' . dmrfid_get_element_class( 'dmrfid_payment_instructions' ) . '">' . wpautop( wp_unslash( dmrfid_getOption("instructions") ) ) . '</div>';
		}

		/**
		 * All devs to filter the confirmation message.
		 * We also have a function in includes/filters.php that applies the the_content filters to this message.
		 * @param string $confirmation_message The confirmation message.
		 * @param object $dmrfid_invoice The DmRFID Invoice/Order object.
		 */
		$confirmation_message = apply_filters("dmrfid_confirmation_message", $confirmation_message, $dmrfid_invoice);

		echo wp_kses_post( $confirmation_message );
	?>
	<h3>
		<?php printf(__('Invoice #%s on %s', 'paid-memberships-pro' ), $dmrfid_invoice->code, date_i18n(get_option('date_format'), $dmrfid_invoice->getTimestamp()));?>
	</h3>
	<a class="<?php echo dmrfid_get_element_class( 'dmrfid_a-print' ); ?>" href="javascript:window.print()"><?php _e('Print', 'paid-memberships-pro' );?></a>
	<ul>
		<?php do_action("dmrfid_invoice_bullets_top", $dmrfid_invoice); ?>
		<li><strong><?php _e('Account', 'paid-memberships-pro' );?>:</strong> <?php echo esc_html( $current_user->display_name );?> (<?php echo esc_html( $current_user->user_email );?>)</li>
		<li><strong><?php _e('Membership Level', 'paid-memberships-pro' );?>:</strong> <?php echo esc_html( $current_user->membership_level->name);?></li>
		<?php if($current_user->membership_level->enddate) { ?>
			<li><strong><?php _e('Membership Expires', 'paid-memberships-pro' );?>:</strong> <?php echo date_i18n(get_option('date_format'), $current_user->membership_level->enddate)?></li>
		<?php } ?>
		<?php if($dmrfid_invoice->getDiscountCode()) { ?>
			<li><strong><?php _e('Discount Code', 'paid-memberships-pro' );?>:</strong> <?php echo esc_html( $dmrfid_invoice->discount_code->code );?></li>
		<?php } ?>
		<?php do_action("dmrfid_invoice_bullets_bottom", $dmrfid_invoice); ?>
	</ul>
	<hr />
	<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice_details' ); ?>">
		<?php if(!empty($dmrfid_invoice->billing->name)) { ?>
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice-billing-address' ); ?>">
				<strong><?php _e('Billing Address', 'paid-memberships-pro' );?></strong>
				<p><?php echo esc_html( $dmrfid_invoice->billing->name );?><br />
				<?php echo esc_html( $dmrfid_invoice->billing->street );?><br />
				<?php if($dmrfid_invoice->billing->city && $dmrfid_invoice->billing->state) { ?>
					<?php echo esc_html( $dmrfid_invoice->billing->city );?>, <?php echo esc_html( $dmrfid_invoice->billing->state );?> <?php echo esc_html( $dmrfid_invoice->billing->zip );?> <?php echo esc_html( $dmrfid_invoice->billing->country );?><br />
				<?php } ?>
				<?php echo formatPhone($dmrfid_invoice->billing->phone)?>
				</p>
			</div> <!-- end dmrfid_invoice-billing-address -->
		<?php } ?>

		<?php if ( ! empty( $dmrfid_invoice->accountnumber ) || ! empty( $dmrfid_invoice->payment_type ) ) { ?>
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice-payment-method' ); ?>">
				<strong><?php _e('Payment Method', 'paid-memberships-pro' );?></strong>
				<?php if($dmrfid_invoice->accountnumber) { ?>
					<p><?php echo esc_html( ucwords( $dmrfid_invoice->cardtype ) ); ?> <?php _e('ending in', 'paid-memberships-pro' );?> <?php echo esc_html( last4($dmrfid_invoice->accountnumber ) );?>
					<br />
					<?php _e('Expiration', 'paid-memberships-pro' );?>: <?php echo esc_html( $dmrfid_invoice->expirationmonth );?>/<?php echo esc_html( $dmrfid_invoice->expirationyear );?></p>
				<?php } else { ?>
					<p><?php echo esc_html( $dmrfid_invoice->payment_type ); ?></p>
				<?php } ?>
			</div> <!-- end dmrfid_invoice-payment-method -->
		<?php } ?>

		<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice-total' ); ?>">
			<strong><?php _e('Total Billed', 'paid-memberships-pro' );?></strong>
			<p><?php if($dmrfid_invoice->total != '0.00') { ?>
				<?php if(!empty($dmrfid_invoice->tax)) { ?>
					<?php _e('Subtotal', 'paid-memberships-pro' );?>: <?php echo dmrfid_formatPrice($dmrfid_invoice->subtotal);?><br />
					<?php _e('Tax', 'paid-memberships-pro' );?>: <?php echo dmrfid_formatPrice($dmrfid_invoice->tax);?><br />
					<?php if(!empty($dmrfid_invoice->couponamount)) { ?>
						<?php _e('Coupon', 'paid-memberships-pro' );?>: (<?php echo dmrfid_formatPrice($dmrfid_invoice->couponamount);?>)<br />
					<?php } ?>
					<strong><?php _e('Total', 'paid-memberships-pro' );?>: <?php echo dmrfid_formatPrice($dmrfid_invoice->total);?></strong>
				<?php } else { ?>
					<?php echo dmrfid_formatPrice($dmrfid_invoice->total);?>
				<?php } ?>
			<?php } else { ?>
				<small class="<?php echo dmrfid_get_element_class( 'dmrfid_grey' ); ?>"><?php echo esc_html( dmrfid_formatPrice(0) );?></small>
			<?php } ?></p>
		</div> <!-- end dmrfid_invoice-total -->

	</div> <!-- end dmrfid_invoice -->
	<hr />
<?php
	}
	else
	{
		$confirmation_message .= "<p>" . sprintf(__('Below are details about your membership account. A welcome email has been sent to %s.', 'paid-memberships-pro' ), $current_user->user_email) . "</p>";

		/**
		 * All devs to filter the confirmation message.
		 * Documented above.
		 * We also have a function in includes/filters.php that applies the the_content filters to this message.
		 */
		$confirmation_message = apply_filters("dmrfid_confirmation_message", $confirmation_message, false);

		echo wp_kses_post( $confirmation_message );
	?>
	<ul>
		<li><strong><?php _e('Account', 'paid-memberships-pro' );?>:</strong> <?php echo esc_html( $current_user->display_name );?> (<?php echo esc_html( $current_user->user_email );?>)</li>
		<li><strong><?php _e('Membership Level', 'paid-memberships-pro' );?>:</strong> <?php if(!empty($current_user->membership_level)) echo esc_html( $current_user->membership_level->name ); else _e("Pending", 'paid-memberships-pro' );?></li>
	</ul>
<?php
	}
?>
<p class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav' ); ?>">
	<?php if ( ! empty( $current_user->membership_level ) ) { ?>
		<a href="<?php echo dmrfid_url( 'account' ); ?>"><?php _e( 'View Your Membership Account &rarr;', 'paid-memberships-pro' ); ?></a>
	<?php } else { ?>
		<?php _e( 'If your account is not activated within a few minutes, please contact the site owner.', 'paid-memberships-pro' ); ?>
	<?php } ?>
</p> <!-- end dmrfid_actions_nav -->
</div> <!-- end dmrfid_confirmation_wrap -->
