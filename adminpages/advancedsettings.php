<?php
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("dmrfid_advancedsettings")))
	{
		die(__("You do not have permissions to perform this action.", 'digital-members-rfid' ));
	}

	global $wpdb, $msg, $msgt, $allowedposttags;

	//check nonce for saving settings
	if (!empty($_REQUEST['savesettings']) && (empty($_REQUEST['dmrfid_advancedsettings_nonce']) || !check_admin_referer('savesettings', 'dmrfid_advancedsettings_nonce'))) {
		$msg = -1;
		$msgt = __("Are you sure you want to do that? Try again.", 'digital-members-rfid' );
		unset($_REQUEST['savesettings']);
	}
	
	//get/set settings
	if(!empty($_REQUEST['savesettings']))
	{
		// Dashboard settings.
		dmrfid_setOption( 'hide_toolbar' );
		dmrfid_setOption( 'block_dashboard' );
		
		// Message settings.
		// These use wp_kses for better security handling.
		$nonmembertext = wp_kses(wp_unslash($_POST['nonmembertext']), $allowedposttags);
		update_option('dmrfid_nonmembertext', $nonmembertext);
		
		$notloggedintext = wp_kses(wp_unslash($_POST['notloggedintext']), $allowedposttags);
		update_option('dmrfid_notloggedintext', $notloggedintext);
		
		$rsstext = wp_kses(wp_unslash($_POST['rsstext']), $allowedposttags);
		update_option('dmrfid_rsstext', $rsstext);		
		
		// Content settings.
		dmrfid_setOption("filterqueries");
		dmrfid_setOption("showexcerpts");		

		// Checkout settings.
		dmrfid_setOption("tospage");
		dmrfid_setOption("recaptcha");
		dmrfid_setOption("recaptcha_version");
		dmrfid_setOption("recaptcha_publickey");
		dmrfid_setOption("recaptcha_privatekey");		

		// Communication settings.
		dmrfid_setOption("maxnotificationpriority");
		dmrfid_setOption("activity_email_frequency");

		// Other settings.
		dmrfid_setOption("hideads");
		dmrfid_setOption("hideadslevels");
		dmrfid_setOption("redirecttosubscription");
		dmrfid_setOption("uninstall");

        /**
         * Filter to add custom settings to the advanced settings page.
         * @param array $settings Array of settings, each setting an array with keys field_name, field_type, label, description.
         */
        $custom_settings = apply_filters('dmrfid_custom_advanced_settings', array());
        foreach($custom_settings as $setting) {
        	if(!empty($setting['field_name']))
        		dmrfid_setOption($setting['field_name']);
        }
        
		// Assume success.
		$msg = true;
		$msgt = __("Your advanced settings have been updated.", 'digital-members-rfid' );
	}

	// Dashboard settings.
	$hide_toolbar = dmrfid_getOption( 'hide_toolbar' );
	$block_dashboard = dmrfid_getOption( 'block_dashboard' );
	
	// Message settings.
	$nonmembertext = dmrfid_getOption("nonmembertext");
	$notloggedintext = dmrfid_getOption("notloggedintext");
	$rsstext = dmrfid_getOption("rsstext");
    
	// Content settings.
	$filterqueries = dmrfid_getOption('filterqueries');
	$showexcerpts = dmrfid_getOption("showexcerpts");	

	// Checkout settings.
	$tospage = dmrfid_getOption("tospage");
	$recaptcha = dmrfid_getOption("recaptcha");
	$recaptcha_version = dmrfid_getOption("recaptcha_version");
	$recaptcha_publickey = dmrfid_getOption("recaptcha_publickey");
	$recaptcha_privatekey = dmrfid_getOption("recaptcha_privatekey");

	// Communication settings.
	$maxnotificationpriority = dmrfid_getOption("maxnotificationpriority");
	$activity_email_frequency = dmrfid_getOption("activity_email_frequency");

	// Other settings.
	$hideads = dmrfid_getOption("hideads");
	$hideadslevels = dmrfid_getOption("hideadslevels");
	if( is_multisite() ) {
		$redirecttosubscription = dmrfid_getOption("redirecttosubscription");
	}
	$uninstall = dmrfid_getOption('uninstall');

	// Default settings.
	if(!$nonmembertext)
	{
		$nonmembertext = sprintf( __( 'This content is for !!levels!! members only.<br /><a href="%s">Join Now</a>', 'digital-members-rfid' ), "!!levels_page_url!!" );
		dmrfid_setOption("nonmembertext", $nonmembertext);
	}
	if(!$notloggedintext)
	{
		$notloggedintext = sprintf( __( 'This content is for !!levels!! members only.<br /><a href="%s">Log In</a> <a href="%s">Join Now</a>', 'digital-members-rfid' ), '!!login_url!!', "!!levels_page_url!!" );
		dmrfid_setOption("notloggedintext", $notloggedintext);
	}
	if(!$rsstext)
	{
		$rsstext = __( 'This content is for members only. Visit the site and log in/register to read.', 'digital-members-rfid' );
		dmrfid_setOption("rsstext", $rsstext);
	}

	$levels = $wpdb->get_results( "SELECT * FROM {$wpdb->dmrfid_membership_levels}", OBJECT );

	if ( empty( $activity_email_frequency ) ) {
		$activity_email_frequency = 'week';
	}

	require_once(dirname(__FILE__) . "/admin_header.php");
?>

	<form action="" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('savesettings', 'dmrfid_advancedsettings_nonce');?>
		
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Advanced Settings', 'digital-members-rfid' ); ?></h1>
		<hr class="wp-header-end">
		<div class="dmrfid_admin_section dmrfid_admin_section-restrict-dashboard">
			<h2 class="title"><?php esc_html_e( 'Restrict Dashboard Access', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="block_dashboard"><?php _e('WordPress Dashboard', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<input id="block_dashboard" name="block_dashboard" type="checkbox" value="yes" <?php checked( $block_dashboard, 'yes' ); ?> /> <label for="block_dashboard"><?php _e('Block all users with the Subscriber role from accessing the Dashboard.', 'digital-members-rfid' );?></label>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="hide_toolbar"><?php _e('WordPress Toolbar', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<input id="hide_toolbar" name="hide_toolbar" type="checkbox" value="yes" <?php checked( $hide_toolbar, 'yes' ); ?> /> <label for="hide_toolbar"><?php _e('Hide the Toolbar from all users with the Subscriber role.', 'digital-members-rfid' );?></label>
					</td>
				</tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-restrict-dashboard -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-message-settings">
			<h2 class="title"><?php esc_html_e( 'Message Settings', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="nonmembertext"><?php _e('Message for Logged-in Non-members', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<textarea name="nonmembertext" rows="3" cols="50" class="large-text"><?php echo stripslashes($nonmembertext)?></textarea>
						<p class="description"><?php _e('This message replaces the post content for non-members. Available variables', 'digital-members-rfid' );?>: <code>!!levels!!</code> <code>!!referrer!!</code> <code>!!levels_page_url!!</code></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="notloggedintext"><?php _e('Message for Logged-out Users', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<textarea name="notloggedintext" rows="3" cols="50" class="large-text"><?php echo stripslashes($notloggedintext)?></textarea>
						<p class="description"><?php _e('This message replaces the post content for logged-out visitors.', 'digital-members-rfid' );?> <?php _e('Available variables', 'digital-members-rfid' );?>: <code>!!levels!!</code> <code>!!referrer!!</code> <code>!!login_url!!</code> <code>!!levels_page_url!!</code></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="rsstext"><?php _e('Message for RSS Feed', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<textarea name="rsstext" rows="3" cols="50" class="large-text"><?php echo stripslashes($rsstext)?></textarea>
						<p class="description"><?php _e('This message replaces the post content in RSS feeds.', 'digital-members-rfid' );?> <?php _e('Available variables', 'digital-members-rfid' );?>: <code>!!levels!!</code></p>
					</td>
				</tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-message-settings -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-content-settings">
			<h2 class="title"><?php esc_html_e( 'Content Settings', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="filterqueries"><?php _e("Filter searches and archives?", 'digital-members-rfid' );?></label>
					</th>
					<td>
						<select id="filterqueries" name="filterqueries">
							<option value="0" <?php if(!$filterqueries) { ?>selected="selected"<?php } ?>><?php _e('No - Non-members will see restricted posts/pages in searches and archives.', 'digital-members-rfid' );?></option>
							<option value="1" <?php if($filterqueries == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes - Only members will see restricted posts/pages in searches and archives.', 'digital-members-rfid' );?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="showexcerpts"><?php _e('Show Excerpts to Non-Members?', 'digital-members-rfid' );?></label>
	            </th>
	            <td>
	                <select id="showexcerpts" name="showexcerpts">
	                    <option value="0" <?php if(!$showexcerpts) { ?>selected="selected"<?php } ?>><?php _e('No - Hide excerpts.', 'digital-members-rfid' );?></option>
	                    <option value="1" <?php if($showexcerpts == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes - Show excerpts.', 'digital-members-rfid' );?></option>
	                </select>
	            </td>
	            </tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-content-settings -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-checkout-settings">
			<h2 class="title"><?php esc_html_e( 'Checkout Settings', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="tospage"><?php _e('Require Terms of Service on signups?', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<?php
							wp_dropdown_pages(array("name"=>"tospage", "show_option_none"=>"No", "selected"=>$tospage));
						?>
						<br />
						<p class="description"><?php _e('If yes, create a WordPress page containing your TOS agreement and assign it using the dropdown above.', 'digital-members-rfid' );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="recaptcha"><?php _e('Use reCAPTCHA?', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<select id="recaptcha" name="recaptcha" onchange="dmrfid_updateRecaptchaTRs();">
							<option value="0" <?php if(!$recaptcha) { ?>selected="selected"<?php } ?>><?php _e('No', 'digital-members-rfid' );?></option>
							<option value="1" <?php if($recaptcha == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes - Free memberships only.', 'digital-members-rfid' );?></option>
							<option value="2" <?php if($recaptcha == 2) { ?>selected="selected"<?php } ?>><?php _e('Yes - All memberships.', 'digital-members-rfid' );?></option>
						</select>
						<p class="description"><?php _e('A free reCAPTCHA key is required.', 'digital-members-rfid' );?> <a href="https://www.google.com/recaptcha/admin/create"><?php _e('Click here to signup for reCAPTCHA', 'digital-members-rfid' );?></a>.</p>
					</td>
				</tr>
			</tbody>
			</table>
			<table class="form-table" id="recaptcha_settings" <?php if(!$recaptcha) { ?>style="display: none;"<?php } ?>>
			<tbody>
				<tr>
					<th scope="row" valign="top"><label for="recaptcha_version"><?php _e( 'reCAPTCHA Version', 'digital-members-rfid' );?>:</label></th>
					<td>					
						<select id="recaptcha_version" name="recaptcha_version">
							<option value="2_checkbox" <?php selected( '2_checkbox', $recaptcha_version ); ?>><?php _e( ' v2 - Checkbox', 'digital-members-rfid' ); ?></option>
							<option value="3_invisible" <?php selected( '3_invisible', $recaptcha_version ); ?>><?php _e( 'v3 - Invisible', 'digital-members-rfid' ); ?></option>
						</select>
						<p class="description"><?php _e( 'Changing your version will require new API keys.', 'digital-members-rfid' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="recaptcha_publickey"><?php _e('reCAPTCHA Site Key', 'digital-members-rfid' );?>:</label></th>
					<td>
						<input type="text" id="recaptcha_publickey" name="recaptcha_publickey" value="<?php echo esc_attr($recaptcha_publickey);?>" class="regular-text code" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="recaptcha_privatekey"><?php _e('reCAPTCHA Secret Key', 'digital-members-rfid' );?>:</label></th>
					<td>
						<input type="text" id="recaptcha_privatekey" name="recaptcha_privatekey" value="<?php echo esc_attr($recaptcha_privatekey);?>" class="regular-text code" />
					</td>
				</tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-checkout-settings -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-communication-settings">
			<h2 class="title"><?php esc_html_e( 'Communication Settings', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Notifications', 'digital-members-rfid' ); ?></th>
					<td>
						<select name="maxnotificationpriority">
							<option value="5" <?php selected( $maxnotificationpriority, 5 ); ?>>
								<?php _e( 'Show all notifications.', 'digital-members-rfid' ); ?>
							</option>
							<option value="1" <?php selected( $maxnotificationpriority, 1 ); ?>>
								<?php _e( 'Show only security notifications.', 'digital-members-rfid' ); ?>
							</option>
						</select>
						<br />
						<p class="description"><?php _e('Notifications are occasionally shown on the Digital Members RFID settings pages.', 'digital-members-rfid' );?></p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="activity_email_frequency"><?php _e('Activity Email Frequency', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<select name="activity_email_frequency">
							<option value="day" <?php selected( $activity_email_frequency, 'day' ); ?>>
								<?php _e( 'Daily', 'digital-members-rfid' ); ?>
							</option>
							<option value="week" <?php selected( $activity_email_frequency, 'week' ); ?>>
								<?php _e( 'Weekly', 'digital-members-rfid' ); ?>
							</option>
							<option value="month" <?php selected( $activity_email_frequency, 'month' ); ?>>
								<?php _e( 'Monthly', 'digital-members-rfid' ); ?>
							</option>
							<option value="never" <?php selected( $activity_email_frequency, 'never' ); ?>>
								<?php _e( 'Never', 'digital-members-rfid' ); ?>
							</option>
						</select>
						<br />
						<p class="description"><?php _e( 'Send periodic sales and revenue updates from this site to the administration email address.', 'digital-members-rfid' );?></p>
					</td>
				</tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-communication-settings -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-other-settings">
			<h2 class="title"><?php esc_html_e( 'Other Settings', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="hideads"><?php _e("Hide Ads From Members?", 'digital-members-rfid' );?></label>
					</th>
					<td>
						<select id="hideads" name="hideads" onchange="dmrfid_updateHideAdsTRs();">
							<option value="0" <?php if(!$hideads) { ?>selected="selected"<?php } ?>><?php _e('No', 'digital-members-rfid' );?></option>
							<option value="1" <?php if($hideads == 1) { ?>selected="selected"<?php } ?>><?php _e('Hide Ads From All Members', 'digital-members-rfid' );?></option>
							<option value="2" <?php if($hideads == 2) { ?>selected="selected"<?php } ?>><?php _e('Hide Ads From Certain Members', 'digital-members-rfid' );?></option>
						</select>
					</td>
				</tr>
				<tr id="hideads_explanation" <?php if($hideads < 2) { ?>style="display: none;"<?php } ?>>
					<th scope="row" valign="top">&nbsp;</th>
					<td>
						<p><?php _e('To hide ads in your template code, use code like the following', 'digital-members-rfid' );?>:</p>
					<pre lang="PHP">
if ( function_exists( 'dmrfid_displayAds' ) && dmrfid_displayAds() ) {
	//insert ad code here
}</pre>
					</td>
				</tr>			
				<tr id="hideadslevels_tr" <?php if($hideads != 2) { ?>style="display: none;"<?php } ?>>
					<th scope="row" valign="top">
						<label for="hideadslevels"><?php _e('Choose Levels to Hide Ads From', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<div class="checkbox_box" <?php if(count($levels) > 5) { ?>style="height: 100px; overflow: auto;"<?php } ?>>
							<?php
								$hideadslevels = dmrfid_getOption("hideadslevels");
								if(!is_array($hideadslevels))
									$hideadslevels = explode(",", $hideadslevels);

								$sqlQuery = "SELECT * FROM $wpdb->dmrfid_membership_levels ";
								$levels = $wpdb->get_results($sqlQuery, OBJECT);
								foreach($levels as $level)
								{
							?>
								<div class="clickable"><input type="checkbox" id="hideadslevels_<?php echo $level->id?>" name="hideadslevels[]" value="<?php echo $level->id?>" <?php if(in_array($level->id, $hideadslevels)) { ?>checked="checked"<?php } ?>> <?php echo $level->name?></div>
							<?php
								}
							?>
						</div>
						<script>
							jQuery('.checkbox_box input').click(function(event) {
								event.stopPropagation()
							});

							jQuery('.checkbox_box div.clickable').click(function() {
								var checkbox = jQuery(this).find(':checkbox');
								checkbox.attr('checked', !checkbox.attr('checked'));
							});
						</script>
					</td>
				</tr>
				<?php if(is_multisite()) { ?>
				<tr>
					<th scope="row" valign="top">
						<label for="redirecttosubscription"><?php _e('Redirect all traffic from registration page to /susbcription/?', 'digital-members-rfid' );?>: <em>(<?php _e('multisite only', 'digital-members-rfid' );?>)</em></label>
					</th>
					<td>
						<select id="redirecttosubscription" name="redirecttosubscription">
							<option value="0" <?php if(!$redirecttosubscription) { ?>selected="selected"<?php } ?>><?php _e('No', 'digital-members-rfid' );?></option>
							<option value="1" <?php if($redirecttosubscription == 1) { ?>selected="selected"<?php } ?>><?php _e('Yes', 'digital-members-rfid' );?></option>
						</select>
					</td>
				</tr>
				<?php } ?>			
				<?php
	            // Filter to Add More Advanced Settings for Misc Plugin Options, etc.
	            if (has_action('dmrfid_custom_advanced_settings')) {
		            $custom_fields = apply_filters('dmrfid_custom_advanced_settings', array());
		            foreach ($custom_fields as $field) {
		            ?>
		            <tr>
		                <th valign="top" scope="row">
		                    <label
		                        for="<?php echo esc_attr( $field['field_name'] ); ?>"><?php echo esc_textarea( $field['label'] ); ?></label>
		                </th>
		                <td>
		                    <?php
		                    switch ($field['field_type']) {
		                        case 'select':
		                            ?>
		                            <select id="<?php echo esc_attr( $field['field_name'] ); ?>"
		                                    name="<?php echo esc_attr( $field['field_name'] ); ?>">
		                                <?php 
		                                	//For associative arrays, we use the array keys as values. For numerically indexed arrays, we use the array values.
		                                	$is_associative = (bool)count(array_filter(array_keys($field['options']), 'is_string'));
		                                	foreach ($field['options'] as $key => $option) {
		                                    	if(!$is_associative) $key = $option;
		                                    	?>
		                                    	<option value="<?php echo esc_attr($key); ?>" <?php selected($key, dmrfid_getOption($field['field_name']));?>>
		                                    		<?php echo esc_textarea($option); ?>
		                                    	</option>
		                               			<?php
		                                	} 
		                                ?>
		                            </select>
		                            <?php
		                            break;
		                        case 'text':
		                            ?>
		                            <input id="<?php echo esc_attr( $field['field_name'] ); ?>"
		                                   name="<?php echo esc_attr( $field['field_name'] ); ?>"
		                                   type="<?php echo esc_attr( $field['field_type'] ); ?>"
		                                   value="<?php echo esc_attr(dmrfid_getOption($field['field_name'])); ?> "
		                                   class="regular-text">
		                            <?php
		                            break;
		                        case 'textarea':
		                            ?>
		                            <textarea id="<?php echo esc_attr( $field['field_name'] ); ?>"
		                                      name="<?php echo esc_attr( $field['field_name'] ); ?>"
		                                      class="large-text">
		                                <?php echo esc_textarea(dmrfid_getOption($field['field_name'])); ?>
		                            </textarea>
		                            <?php
		                            break;
		                        default:
		                            break;
		                    }
							if ( ! empty( $field['description'] ) ) {
								$allowed_dmrfid_custom_advanced_settings_html = array (
									'a' => array (
										'href' => array(),
										'target' => array(),
										'title' => array(),
									),
								);
								?>
								<p class="description"><?php echo wp_kses( $field['description'], $allowed_dmrfid_custom_advanced_settings_html ); ?></p>
								<?php } ?>
		                </td>
		            </tr>
		            <?php
		            }
		        } 
		        ?>
				<tr>
					<th scope="row" valign="top">
						<label for="uninstall"><?php _e('Uninstall DmRFID on deletion?', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<select id="uninstall" name="uninstall">
							<option value="0" <?php if ( ! $uninstall ) { ?>selected="selected"<?php } ?>><?php _e( 'No', 'digital-members-rfid' );?></option>
							<option value="1" <?php if ( $uninstall == 1 ) { ?>selected="selected"<?php } ?>><?php _e( 'Yes - Delete all DmRFID Data.', 'digital-members-rfid' );?></option>
						</select>
						<p class="description"><?php esc_html_e( 'To delete all DmRFID data from the database, set to Yes, deactivate DmRFID, and then click to delete DmRFID from the plugins page.' ); ?></p>
					</td>
				</tr>
	        </tbody>
			</table>
			<script>
				function dmrfid_updateHideAdsTRs()
				{
					var hideads = jQuery('#hideads').val();
					if(hideads == 2)
					{
						jQuery('#hideadslevels_tr').show();
					}
					else
					{
						jQuery('#hideadslevels_tr').hide();
					}

					if(hideads > 0)
					{
						jQuery('#hideads_explanation').show();
					}
					else
					{
						jQuery('#hideads_explanation').hide();
					}
				}
				dmrfid_updateHideAdsTRs();

				function dmrfid_updateRecaptchaTRs()
				{
					var recaptcha = jQuery('#recaptcha').val();
					if(recaptcha > 0)
					{
						jQuery('#recaptcha_settings').show();
					}
					else
					{
						jQuery('#recaptcha_settings').hide();
					}
				}
				dmrfid_updateRecaptchaTRs();
			</script>
		</div> <!-- end dmrfid_admin_section-other-settings -->

		<p class="submit">
			<input name="savesettings" type="submit" class="button button-primary" value="<?php _e('Save Settings', 'digital-members-rfid' );?>" />
		</p>
	</form>

<?php
	require_once(dirname(__FILE__) . "/admin_footer.php");
?>
