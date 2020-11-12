<?php
	global $dmrfid_msg, $dmrfid_msgt, $dmrfid_confirm, $current_user, $wpdb;

	if(isset($_REQUEST['levelstocancel']) && $_REQUEST['levelstocancel'] !== 'all') {
		//convert spaces back to +
		$_REQUEST['levelstocancel'] = str_replace(array(' ', '%20'), '+', $_REQUEST['levelstocancel']);

		//get the ids
		$old_level_ids = array_map('intval', explode("+", preg_replace("/[^0-9al\+]/", "", $_REQUEST['levelstocancel'])));

	} elseif(isset($_REQUEST['levelstocancel']) && $_REQUEST['levelstocancel'] == 'all') {
		$old_level_ids = 'all';
	} else {
		$old_level_ids = false;
	}
?>
<div id="dmrfid_cancel" class="<?php echo dmrfid_get_element_class( 'dmrfid_cancel_wrap', 'dmrfid_cancel' ); ?>">
	<?php
		if($dmrfid_msg)
		{
			?>
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_message ' . $dmrfid_msgt, $dmrfid_msgt ); ?>"><?php echo $dmrfid_msg?></div>
			<?php
		}
	?>
	<?php
		if(!$dmrfid_confirm)
		{
			if($old_level_ids)
			{
				if(!is_array($old_level_ids) && $old_level_ids == "all")
				{
					?>
					<p><?php _e('Are you sure you want to cancel your membership?', 'digital-members-rfid' ); ?></p>
					<?php
				}
				else
				{
					$level_names = $wpdb->get_col("SELECT name FROM $wpdb->dmrfid_membership_levels WHERE id IN('" . implode("','", $old_level_ids) . "')");
					?>
					<p><?php printf(_n('Are you sure you want to cancel your %s membership?', 'Are you sure you want to cancel your %s memberships?', count($level_names), 'digital-members-rfid'), dmrfid_implodeToEnglish($level_names)); ?></p>
					<?php
				}
			?>
			<div class="<?php echo dmrfid_get_element_class( 'dmrfid_actionlinks' ); ?>">
				<a class="<?php echo dmrfid_get_element_class( 'dmrfid_btn dmrfid_btn-submit dmrfid_yeslink yeslink', 'dmrfid_btn-submit' ); ?>" href="<?php echo dmrfid_url("cancel", "?levelstocancel=" . esc_attr($_REQUEST['levelstocancel']) . "&confirm=true")?>"><?php _e('Yes, cancel this membership', 'digital-members-rfid' );?></a>
				<a class="<?php echo dmrfid_get_element_class( 'dmrfid_btn dmrfid_btn-cancel dmrfid_nolink nolink', 'dmrfid_btn-cancel' ); ?>" href="<?php echo dmrfid_url("account")?>"><?php _e('No, keep this membership', 'digital-members-rfid' );?></a>
			</div>
			<?php
			}
			else
			{
				if($current_user->membership_level->ID)
				{
					?>
					<h2><?php _e("My Memberships", 'digital-members-rfid' );?></h2>
					<table class="<?php echo dmrfid_get_element_class( 'dmrfid_table' ); ?>" width="100%" cellpadding="0" cellspacing="0" border="0">
						<thead>
							<tr>
								<th><?php _e("Level", 'digital-members-rfid' );?></th>
								<th><?php _e("Expiration", 'digital-members-rfid' ); ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php
								$current_user->membership_levels = dmrfid_getMembershipLevelsForUser($current_user->ID);
								foreach($current_user->membership_levels as $level) {
								?>
								<tr>
									<td class="<?php echo dmrfid_get_element_class( 'dmrfid_cancel-membership-levelname' ); ?>">
										<?php echo $level->name?>
									</td>
									<td class="<?php echo dmrfid_get_element_class( 'dmrfid_cancel-membership-expiration' ); ?>">
									<?php
										if($level->enddate) {
											$expiration_text = date_i18n( get_option( 'date_format' ), $level->enddate );
   										} else {
   											$expiration_text = "---";
										}
       									 
										echo apply_filters( 'dmrfid_account_membership_expiration_text', $expiration_text, $level );
									?>
									</td>
									<td class="<?php echo dmrfid_get_element_class( 'dmrfid_cancel-membership-cancel' ); ?>">
										<a href="<?php echo dmrfid_url("cancel", "?levelstocancel=" . $level->id)?>"><?php _e("Cancel", 'digital-members-rfid' );?></a>
									</td>
								</tr>
								<?php
								}
							?>
						</tbody>
					</table>
					<div class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav' ); ?>">
						<a href="<?php echo dmrfid_url("cancel", "?levelstocancel=all"); ?>"><?php _e("Cancel All Memberships", 'digital-members-rfid' );?></a>
					</div>
					<?php
				}
			}
		}
		else
		{
			?>
			<p class="<?php echo dmrfid_get_element_class( 'dmrfid_cancel_return_home' ); ?>"><a href="<?php echo get_home_url()?>"><?php _e('Click here to go to the home page.', 'digital-members-rfid' );?></a></p>
			<?php
		}
	?>
</div> <!-- end dmrfid_cancel, dmrfid_cancel_wrap -->
