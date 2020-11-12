<?php 
global $wpdb, $dmrfid_msg, $dmrfid_msgt, $current_user;

$dmrfid_levels = dmrfid_getAllLevels(false, true);
$dmrfid_level_order = dmrfid_getOption('level_order');

if(!empty($dmrfid_level_order))
{
	$order = explode(',',$dmrfid_level_order);

	//reorder array
	$reordered_levels = array();
	foreach($order as $level_id) {
		foreach($dmrfid_levels as $key=>$level) {
			if($level_id == $level->id)
				$reordered_levels[] = $dmrfid_levels[$key];
		}
	}

	$dmrfid_levels = $reordered_levels;
}

$dmrfid_levels = apply_filters("dmrfid_levels_array", $dmrfid_levels);

if($dmrfid_msg)
{
?>
<div class="<?php echo dmrfid_get_element_class( 'dmrfid_message ' . $dmrfid_msgt, $dmrfid_msgt ); ?>"><?php echo $dmrfid_msg?></div>
<?php
}
?>
<table id="dmrfid_levels_table" class="<?php echo dmrfid_get_element_class( 'dmrfid_table dmrfid_checkout', 'dmrfid_levels_table' ); ?>">
<thead>
  <tr>
	<th><?php _e('Level', 'digital-members-rfid' );?></th>
	<th><?php _e('Price', 'digital-members-rfid' );?></th>	
	<th>&nbsp;</th>
  </tr>
</thead>
<tbody>
	<?php	
	$count = 0;
	foreach($dmrfid_levels as $level)
	{
	  if(isset($current_user->membership_level->ID))
		  $current_level = ($current_user->membership_level->ID == $level->id);
	  else
		  $current_level = false;
	?>
	<tr class="<?php if($count++ % 2 == 0) { ?>odd<?php } ?><?php if($current_level == $level) { ?> active<?php } ?>">
		<td><?php echo $current_level ? "<strong>{$level->name}</strong>" : $level->name?></td>
		<td>
			<?php
				$cost_text = dmrfid_getLevelCost($level, true, true); 
				$expiration_text = dmrfid_getLevelExpiration($level);
				if(!empty($cost_text) && !empty($expiration_text))
					echo $cost_text . "<br />" . $expiration_text;
				elseif(!empty($cost_text))
					echo $cost_text;
				elseif(!empty($expiration_text))
					echo $expiration_text;
			?>
		</td>
		<td>
		<?php if(empty($current_user->membership_level->ID)) { ?>
			<a class="<?php echo dmrfid_get_element_class( 'dmrfid_btn dmrfid_btn-select', 'dmrfid_btn-select' ); ?>" href="<?php echo dmrfid_url("checkout", "?level=" . $level->id, "https")?>"><?php _e('Select', 'digital-members-rfid' );?></a>
		<?php } elseif ( !$current_level ) { ?>                	
			<a class="<?php echo dmrfid_get_element_class( 'dmrfid_btn dmrfid_btn-select', 'dmrfid_btn-select' ); ?>" href="<?php echo dmrfid_url("checkout", "?level=" . $level->id, "https")?>"><?php _e('Select', 'digital-members-rfid' );?></a>
		<?php } elseif($current_level) { ?>      
			
			<?php
				//if it's a one-time-payment level, offer a link to renew				
				if( dmrfid_isLevelExpiringSoon( $current_user->membership_level) && $current_user->membership_level->allow_signups ) {
					?>
						<a class="<?php echo dmrfid_get_element_class( 'dmrfid_btn dmrfid_btn-select', 'dmrfid_btn-select' ); ?>" href="<?php echo dmrfid_url("checkout", "?level=" . $level->id, "https")?>"><?php _e('Renew', 'digital-members-rfid' );?></a>
					<?php
				} else {
					?>
						<a class="<?php echo dmrfid_get_element_class( 'dmrfid_btn disabled', 'dmrfid_btn' ); ?>" href="<?php echo dmrfid_url("account")?>"><?php _e('Your&nbsp;Level', 'digital-members-rfid' );?></a>
					<?php
				}
			?>
			
		<?php } ?>
		</td>
	</tr>
	<?php
	}
	?>
</tbody>
</table>
<p class="<?php echo dmrfid_get_element_class( 'dmrfid_actions_nav' ); ?>">
	<?php if(!empty($current_user->membership_level->ID)) { ?>
		<a href="<?php echo dmrfid_url("account")?>" id="dmrfid_levels-return-account"><?php _e('&larr; Return to Your Account', 'digital-members-rfid' );?></a>
	<?php } else { ?>
		<a href="<?php echo home_url()?>" id="dmrfid_levels-return-home"><?php _e('&larr; Return to Home', 'digital-members-rfid' );?></a>
	<?php } ?>
</p> <!-- end dmrfid_actions_nav -->
