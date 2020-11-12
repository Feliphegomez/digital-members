<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice_wrap' ); ?>">
	<?php
	global $wpdb, $dmrfid_invoice, $dmrfid_msg, $dmrfid_msgt, $current_user;

	if($dmrfid_msg)
	{
	?>
	<div class="<?php echo dmrfid_get_element_class( 'dmrfid_message ' . $dmrfid_msgt, $dmrfid_msgt ); ?>"><?php echo $dmrfid_msg?></div>
	<?php
	}
?>

<?php
	if($dmrfid_invoice)
	{
		?>
		<?php
			$dmrfid_invoice->getUser();
			$dmrfid_invoice->getMembershipLevel();
		?>
		<h3><?php printf(__('Invoice #%s on %s', 'digital-members-rfid' ), $dmrfid_invoice->code, date_i18n(get_option('date_format'), $dmrfid_invoice->getTimestamp()));?></h3>
		<a class="<?php echo dmrfid_get_element_class( 'dmrfid_a-print' ); ?>" href="javascript:window.print()"><?php _e('Print', 'digital-members-rfid' ); ?></a>
		<ul>
			<?php do_action("dmrfid_invoice_bullets_top", $dmrfid_invoice); ?>
			<li><strong><?php _e('Account', 'digital-members-rfid' );?>:</strong> <?php echo $dmrfid_invoice->user->display_name?> (<?php echo $dmrfid_invoice->user->user_email?>)</li>
			<li><strong><?php _e('Membership Level', 'digital-members-rfid' );?>:</strong> <?php echo $dmrfid_invoice->membership_level->name?></li>
			<?php if ( ! empty( $dmrfid_invoice->status ) ) { ?>
				<li><strong><?php _e('Status', 'digital-members-rfid' ); ?>:</strong>
				<?php
					if ( in_array( $dmrfid_invoice->status, array( '', 'success', 'cancelled' ) ) ) {
						$display_status = __( 'Paid', 'digital-members-rfid' );
					} else {
						$display_status = ucwords( $dmrfid_invoice->status );
					}
					esc_html_e( $display_status );
				?>
				</li>
			<?php } ?>
			<?php if($dmrfid_invoice->getDiscountCode()) { ?>
				<li><strong><?php _e('Discount Code', 'digital-members-rfid' );?>:</strong> <?php echo $dmrfid_invoice->discount_code->code?></li>
			<?php } ?>
			<?php do_action("dmrfid_invoice_bullets_bottom", $dmrfid_invoice); ?>
		</ul>

		<?php
			// Check instructions
			if ( $dmrfid_invoice->gateway == "check" && ! dmrfid_isLevelFree( $dmrfid_invoice->membership_level ) ) {
				echo '<div class="' . dmrfid_get_element_class( 'dmrfid_payment_instructions' ) . '">' . wpautop( wp_unslash( dmrfid_getOption("instructions") ) ) . '</div>';
			}
		?>

		<hr />
		<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice_details' ); ?>">
			<?php if(!empty($dmrfid_invoice->billing->name)) { ?>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice-billing-address' ); ?>">
					<strong><?php _e('Billing Address', 'digital-members-rfid' );?></strong>
					<p><?php echo $dmrfid_invoice->billing->name?><br />
					<?php echo $dmrfid_invoice->billing->street?><br />
					<?php if($dmrfid_invoice->billing->city && $dmrfid_invoice->billing->state) { ?>
						<?php echo $dmrfid_invoice->billing->city?>, <?php echo $dmrfid_invoice->billing->state?> <?php echo $dmrfid_invoice->billing->zip?> <?php echo $dmrfid_invoice->billing->country?><br />
					<?php } ?>
					<?php echo formatPhone($dmrfid_invoice->billing->phone)?>
					</p>
				</div> <!-- end dmrfid_invoice-billing-address -->
			<?php } ?>

			<?php if ( ! empty( $dmrfid_invoice->accountnumber ) || ! empty( $dmrfid_invoice->payment_type ) ) { ?>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice-payment-method' ); ?>">
					<strong><?php _e('Payment Method', 'digital-members-rfid' );?></strong>
					<?php if($dmrfid_invoice->accountnumber) { ?>
						<p><?php echo ucwords( $dmrfid_invoice->cardtype ); ?> <?php _e('ending in', 'digital-members-rfid' );?> <?php echo last4($dmrfid_invoice->accountnumber)?>
						<br />
						<?php _e('Expiration', 'digital-members-rfid' );?>: <?php echo $dmrfid_invoice->expirationmonth?>/<?php echo $dmrfid_invoice->expirationyear?></p>
					<?php } else { ?>
						<p><?php echo $dmrfid_invoice->payment_type; ?></p>
					<?php } ?>
				</div> <!-- end dmrfid_invoice-payment-method -->
			<?php } ?>

			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_invoice-total' ); ?>">
				<strong><?php _e('Total Billed', 'digital-members-rfid' );?></strong>
				<p><?php if($dmrfid_invoice->total != '0.00') { ?>
					<?php if(!empty($dmrfid_invoice->tax)) { ?>
						<?php _e('Subtotal', 'digital-members-rfid' );?>: <?php echo dmrfid_formatPrice($dmrfid_invoice->subtotal);?><br />
						<?php _e('Tax', 'digital-members-rfid' );?>: <?php echo dmrfid_formatPrice($dmrfid_invoice->tax);?><br />
						<?php if(!empty($dmrfid_invoice->couponamount)) { ?>
							<?php _e('Coupon', 'digital-members-rfid' );?>: (<?php echo dmrfid_formatPrice($dmrfid_invoice->couponamount);?>)<br />
						<?php } ?>
						<strong><?php _e('Total', 'digital-members-rfid' );?>: <?php echo dmrfid_formatPrice($dmrfid_invoice->total);?></strong>
					<?php } else { ?>
						<?php echo dmrfid_formatPrice($dmrfid_invoice->total);?>
					<?php } ?>
				<?php } else { ?>
					<small class="<?php echo dmrfid_get_element_class( 'dmrfid_grey' ); ?>"><?php echo dmrfid_formatPrice(0);?></small>
				<?php } ?></p>
			</div> <!-- end dmrfid_invoice-total -->
		</div> <!-- end dmrfid_invoice_details -->
		<hr />
		<?php
	}
	else
	{
		//Show all invoices for user if no invoice ID is passed
		$invoices = $wpdb->get_results("SELECT o.*, UNIX_TIMESTAMP(CONVERT_TZ(o.timestamp, '+00:00', @@global.time_zone)) as timestamp, l.name as membership_level_name FROM $wpdb->dmrfid_membership_orders o LEFT JOIN $wpdb->dmrfid_membership_levels l ON o.membership_id = l.id WHERE o.user_id = '$current_user->ID' AND o.status NOT IN('review', 'token', 'error') ORDER BY timestamp DESC");
		if($invoices)
		{
			?>
			<table id="dmrfid_invoices_table" class="<?php echo dmrfid_get_element_class( 'dmrfid_table dmrfid_invoice', 'dmrfid_invoices_table' ); ?>" width="100%" cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<th><?php _e('Date', 'digital-members-rfid' ); ?></th>
					<th><?php _e('Invoice #', 'digital-members-rfid' ); ?></th>
					<th><?php _e('Level', 'digital-members-rfid' ); ?></th>
					<th><?php _e('Total Billed', 'digital-members-rfid' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach($invoices as $invoice)
				{
					?>
					<tr>
						<td><a href="<?php echo dmrfid_url("invoice", "?invoice=" . $invoice->code)?>"><?php echo date_i18n( get_option("date_format"), strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $invoice->timestamp ) ) ) )?></a></td>
						<td><a href="<?php echo dmrfid_url("invoice", "?invoice=" . $invoice->code)?>"><?php echo $invoice->code; ?></a></td>
						<td><?php echo $invoice->membership_level_name;?></td>
						<td><?php echo dmrfid_formatPrice($invoice->total);?></td>
					</tr>
					<?php
				}
			?>
			</tbody>
			</table>
			<?php
		}
		else
		{
			?>
			<p><?php _e('No invoices found.', 'digital-members-rfid' );?></p>
			<?php
		}
	}
?>
<p class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav' ); ?>">
	<span class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav-right' ); ?>"><a href="<?php echo dmrfid_url("account")?>"><?php _e('View Your Membership Account &rarr;', 'digital-members-rfid' );?></a></span>
	<?php if ( $dmrfid_invoice ) { ?>
		<span class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav-left' ); ?>"><a href="<?php echo dmrfid_url("invoice")?>"><?php _e('&larr; View All Invoices', 'digital-members-rfid' );?></a></span>
	<?php } ?>
</p> <!-- end dmrfid_actions_nav -->
</div> <!-- end dmrfid_invoice_wrap -->
