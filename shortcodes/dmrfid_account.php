<?php
/*
	Shortcode to show membership account information
*/
function dmrfid_shortcode_account($atts, $content=null, $code="")
{
	global $wpdb, $dmrfid_msg, $dmrfid_msgt, $dmrfid_levels, $current_user, $levels;

	// $atts    ::= array of attributes
	// $content ::= text within enclosing form of shortcode element
	// $code    ::= the shortcode found, when == callback name
	// examples: [dmrfid_account] [dmrfid_account sections="membership,profile"/]

	extract(shortcode_atts(array(
		'section' => '',
		'sections' => 'membership,profile,invoices,links'
	), $atts));

	//did they use 'section' instead of 'sections'?
	if(!empty($section))
		$sections = $section;

	//Extract the user-defined sections for the shortcode
	$sections = array_map('trim',explode(",",$sections));
	ob_start();

	//if a member is logged in, show them some info here (1. past invoices. 2. billing information with button to update.)
	$order = new MemberOrder();
	$order->getLastMemberOrder();
	$mylevels = dmrfid_getMembershipLevelsForUser();
	$dmrfid_levels = dmrfid_getAllLevels(false, true); // just to be sure - include only the ones that allow signups
	$invoices = $wpdb->get_results("SELECT *, UNIX_TIMESTAMP(CONVERT_TZ(timestamp, '+00:00', @@global.time_zone)) as timestamp FROM $wpdb->dmrfid_membership_orders WHERE user_id = '$current_user->ID' AND status NOT IN('review', 'token', 'error') ORDER BY timestamp DESC LIMIT 6");
	?>
	<div id="dmrfid_account">
		<?php if(in_array('membership', $sections) || in_array('memberships', $sections)) { ?>
			<div id="dmrfid_account-membership" class="<?php echo dmrfid_get_element_class( 'dmrfid_box', 'dmrfid_account-membership' ); ?>">

				<h3><?php _e("My Memberships", 'paid-memberships-pro' );?></h3>
				<table class="<?php echo dmrfid_get_element_class( 'dmrfid_table' ); ?>" width="100%" cellpadding="0" cellspacing="0" border="0">
					<thead>
						<tr>
							<th><?php _e("Level", 'paid-memberships-pro' );?></th>
							<th><?php _e("Billing", 'paid-memberships-pro' ); ?></th>
							<th><?php _e("Expiration", 'paid-memberships-pro' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $mylevels ) ) { ?>
						<tr>
							<td colspan="3">
							<?php
							// Check to see if the user has a cancelled order
							$order = new MemberOrder();
							$order->getLastMemberOrder( $current_user->ID, array( 'cancelled', 'expired', 'admin_cancelled' ) );

							if ( isset( $order->membership_id ) && ! empty( $order->membership_id ) && empty( $level->id ) ) {
								$level = dmrfid_getLevel( $order->membership_id );
							}

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
							?>
							</td>
						</tr>
							<?php } else { ?>
							<?php
								foreach($mylevels as $level) {
							?>
							<tr>
								<td class="<?php echo dmrfid_get_element_class( 'dmrfid_account-membership-levelname' ); ?>">
									<?php echo $level->name?>
									<div class="<?php echo dmrfid_get_element_class( 'dmrfid_actionlinks' ); ?>">
										<?php do_action("dmrfid_member_action_links_before"); ?>

										<?php
										// Build the links to return.
										$dmrfid_member_action_links = array();

										if( array_key_exists($level->id, $dmrfid_levels) && dmrfid_isLevelExpiringSoon( $level ) ) {
											$dmrfid_member_action_links['renew'] = sprintf( '<a id="dmrfid_actionlink-renew" href="%s">%s</a>', esc_url( add_query_arg( 'level', $level->id, dmrfid_url( 'checkout', '', 'https' ) ) ), esc_html__( 'Renew', 'paid-memberships-pro' ) );
										}

										if((isset($order->status) && $order->status == "success") && (isset($order->gateway) && in_array($order->gateway, array("authorizenet", "paypal", "stripe", "braintree", "payflow", "cybersource"))) && dmrfid_isLevelRecurring($level)) {
											$dmrfid_member_action_links['update-billing'] = sprintf( '<a id="dmrfid_actionlink-update-billing" href="%s">%s</a>', dmrfid_url( 'billing', '', 'https' ), esc_html__( 'Update Billing Info', 'paid-memberships-pro' ) );
										}

										//To do: Only show CHANGE link if this level is in a group that has upgrade/downgrade rules
										if(count($dmrfid_levels) > 1 && !defined("DMRFID_DEFAULT_LEVEL")) {
											$dmrfid_member_action_links['change'] = sprintf( '<a id="dmrfid_actionlink-change" href="%s">%s</a>', dmrfid_url( 'levels' ), esc_html__( 'Change', 'paid-memberships-pro' ) );
										}

										$dmrfid_member_action_links['cancel'] = sprintf( '<a id="dmrfid_actionlink-cancel" href="%s">%s</a>', esc_url( add_query_arg( 'levelstocancel', $level->id, dmrfid_url( 'cancel' ) ) ), esc_html__( 'Cancel', 'paid-memberships-pro' ) );

										$dmrfid_member_action_links = apply_filters( 'dmrfid_member_action_links', $dmrfid_member_action_links );

										$allowed_html = array(
											'a' => array (
												'class' => array(),
												'href' => array(),
												'id' => array(),
												'target' => array(),
												'title' => array(),
											),
										);
										echo wp_kses( implode( dmrfid_actions_nav_separator(), $dmrfid_member_action_links ), $allowed_html );
										?>

										<?php do_action("dmrfid_member_action_links_after"); ?>
									</div> <!-- end dmrfid_actionlinks -->
								</td>
								<td class="<?php echo dmrfid_get_element_class( 'dmrfid_account-membership-levelfee' ); ?>">
									<p><?php echo dmrfid_getLevelCost($level, true, true);?></p>
								</td>
								<td class="<?php echo dmrfid_get_element_class( 'dmrfid_account-membership-expiration' ); ?>">
								<?php
									if($level->enddate)
										$expiration_text = date_i18n( get_option( 'date_format' ), $level->enddate );
									else
										$expiration_text = "---";

								    	echo apply_filters( 'dmrfid_account_membership_expiration_text', $expiration_text, $level );
								?>
								</td>
							</tr>
							<?php } ?>
						<?php } ?>
					</tbody>
				</table>
				<?php //Todo: If there are multiple levels defined that aren't all in the same group defined as upgrades/downgrades ?>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_actionlinks' ); ?>">
					<a id="dmrfid_actionlink-levels" href="<?php echo dmrfid_url("levels")?>"><?php _e("View all Membership Options", 'paid-memberships-pro' );?></a>
				</div>

			</div> <!-- end dmrfid_account-membership -->
		<?php } ?>

		<?php if(in_array('profile', $sections)) { ?>
			<div id="dmrfid_account-profile" class="<?php echo dmrfid_get_element_class( 'dmrfid_box', 'dmrfid_account-profile' ); ?>">
				<?php wp_get_current_user(); ?>
				<h3><?php _e("My Account", 'paid-memberships-pro' );?></h3>
				<?php if($current_user->user_firstname) { ?>
					<p><?php echo $current_user->user_firstname?> <?php echo $current_user->user_lastname?></p>
				<?php } ?>
				<ul>
					<?php do_action('dmrfid_account_bullets_top');?>
					<li><strong><?php _e("Username", 'paid-memberships-pro' );?>:</strong> <?php echo $current_user->user_login?></li>
					<li><strong><?php _e("Email", 'paid-memberships-pro' );?>:</strong> <?php echo $current_user->user_email?></li>
					<?php do_action('dmrfid_account_bullets_bottom');?>
				</ul>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_actionlinks' ); ?>">
					<?php
						// Get the edit profile and change password links if 'Member Profile Edit Page' is set.
						if ( ! empty( dmrfid_getOption( 'member_profile_edit_page_id' ) ) ) {
							$edit_profile_url = dmrfid_url( 'member_profile_edit' );
							$change_password_url = add_query_arg( 'view', 'change-password', dmrfid_url( 'member_profile_edit' ) );
						} elseif ( ! dmrfid_block_dashboard() ) {
							$edit_profile_url = admin_url( 'profile.php' );
							$change_password_url = admin_url( 'profile.php' );
						}

						// Build the links to return.
						$dmrfid_profile_action_links = array();
						if ( ! empty( $edit_profile_url) ) {
							$dmrfid_profile_action_links['edit-profile'] = sprintf( '<a id="dmrfid_actionlink-profile" href="%s">%s</a>', esc_url( $edit_profile_url ), esc_html__( 'Edit Profile', 'paid-memberships-pro' ) );
						}

						if ( ! empty( $change_password_url ) ) {
							$dmrfid_profile_action_links['change-password'] = sprintf( '<a id="dmrfid_actionlink-change-password" href="%s">%s</a>', esc_url( $change_password_url ), esc_html__( 'Change Password', 'paid-memberships-pro' ) );
						}

						$dmrfid_profile_action_links['logout'] = sprintf( '<a id="dmrfid_actionlink-logout" href="%s">%s</a>', esc_url( wp_logout_url() ), esc_html__( 'Log Out', 'paid-memberships-pro' ) );

						$dmrfid_profile_action_links = apply_filters( 'dmrfid_account_profile_action_links', $dmrfid_profile_action_links );

						$allowed_html = array(
							'a' => array (
								'class' => array(),
								'href' => array(),
								'id' => array(),
								'target' => array(),
								'title' => array(),
							),
						);
						echo wp_kses( implode( dmrfid_actions_nav_separator(), $dmrfid_profile_action_links ), $allowed_html );
					?>
				</div>
			</div> <!-- end dmrfid_account-profile -->
		<?php } ?>

		<?php if(in_array('invoices', $sections) && !empty($invoices)) { ?>
		<div id="dmrfid_account-invoices" class="<?php echo dmrfid_get_element_class( 'dmrfid_box', 'dmrfid_account-invoices' ); ?>">
			<h3><?php _e("Past Invoices", 'paid-memberships-pro' );?></h3>
			<table class="<?php echo dmrfid_get_element_class( 'dmrfid_table' ); ?>" width="100%" cellpadding="0" cellspacing="0" border="0">
				<thead>
					<tr>
						<th><?php _e("Date", 'paid-memberships-pro' ); ?></th>
						<th><?php _e("Level", 'paid-memberships-pro' ); ?></th>
						<th><?php _e("Amount", 'paid-memberships-pro' ); ?></th>
						<th><?php _e("Status", 'paid-memberships-pro'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
					$count = 0;
					foreach($invoices as $invoice)
					{
						if($count++ > 4)
							break;

						//get an member order object
						$invoice_id = $invoice->id;
						$invoice = new MemberOrder;
						$invoice->getMemberOrderByID($invoice_id);
						$invoice->getMembershipLevel();

						if ( in_array( $invoice->status, array( '', 'success', 'cancelled' ) ) ) {
						    $display_status = __( 'Paid', 'paid-memberships-pro' );
						} elseif ( $invoice->status == 'pending' ) {
						    // Some Add Ons set status to pending.
						    $display_status = __( 'Pending', 'paid-memberships-pro' );
						} elseif ( $invoice->status == 'refunded' ) {
						    $display_status = __( 'Refunded', 'paid-memberships-pro' );
						}
						?>
						<tr id="dmrfid_account-invoice-<?php echo $invoice->code; ?>">
							<td><a href="<?php echo dmrfid_url("invoice", "?invoice=" . $invoice->code)?>"><?php echo date_i18n(get_option("date_format"), $invoice->getTimestamp())?></a></td>
							<td><?php if(!empty($invoice->membership_level)) echo $invoice->membership_level->name; else echo __("N/A", 'paid-memberships-pro' );?></td>
							<td><?php echo dmrfid_formatPrice($invoice->total)?></td>
							<td><?php echo $display_status; ?></td>
						</tr>
						<?php
					}
				?>
				</tbody>
			</table>
			<?php if($count == 6) { ?>
				<div class="<?php echo dmrfid_get_element_class( 'dmrfid_actionlinks' ); ?>"><a id="dmrfid_actionlink-invoices" href="<?php echo dmrfid_url("invoice"); ?>"><?php _e("View All Invoices", 'paid-memberships-pro' );?></a></div>
			<?php } ?>
		</div> <!-- end dmrfid_account-invoices -->
		<?php } ?>

		<?php if(in_array('links', $sections) && (has_filter('dmrfid_member_links_top') || has_filter('dmrfid_member_links_bottom'))) { ?>
		<div id="dmrfid_account-links" class="<?php echo dmrfid_get_element_class( 'dmrfid_box', 'dmrfid_account-links' ); ?>">
			<h3><?php _e("Member Links", 'paid-memberships-pro' );?></h3>
			<ul>
				<?php
					do_action("dmrfid_member_links_top");
				?>

				<?php
					do_action("dmrfid_member_links_bottom");
				?>
			</ul>
		</div> <!-- end dmrfid_account-links -->
		<?php } ?>
	</div> <!-- end dmrfid_account -->
	<?php

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}
add_shortcode('dmrfid_account', 'dmrfid_shortcode_account');
