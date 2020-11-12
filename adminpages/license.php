<?php
//only let admins get here
if ( ! function_exists( 'current_user_can' ) || ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'dmrfid_license') ) ) {
	die( __( 'You do not have permissions to perform this action.', 'digital-members-rfid' ) );
}

//updating license?
if ( ! empty( $_REQUEST['dmrfid-verify-submit'] ) ) {
	$key = preg_replace("/[^a-zA-Z0-9]/", "", $_REQUEST['dmrfid-license-key']);
				
	//erase the old key
	delete_option('dmrfid_license_key');
	
	//check key
	$valid = dmrfid_license_isValid($key, NULL, true);
	
	if ( $valid ) { ?>
		<div id="message" class="updated fade">
			<p><?php _e( 'Your license key has been validated.', 'digital-members-rfid' ); ?></p>
		</div>
	<?php } else {
		global $dmrfid_license_error;
		if ( ! empty( $dmrfid_license_error ) ) { ?>
			<div id="message" class="error">
				<p><?php echo $dmrfid_license_error; ?></p>
			</div>
		<?php }
	}
	
	//update key
	update_option( 'dmrfid_license_key', $key, 'no' );
}	

//get saved license
$key = get_option( 'dmrfid_license_key', '' );
$dmrfid_license_check = get_option( 'dmrfid_license_check', array( 'license' => false, 'enddate' => 0 ) );

//html for license settings page
if ( defined( 'DMRFID_DIR' ) ) {
	require_once( DMRFID_DIR . '/adminpages/admin_header.php' );
} ?>
	<div class="about-wrap">
		<h2><?php _e('Digital Members RFID Support License', 'digital-members-rfid' );?></h2>

		<div class="about-text">
			<?php if(!dmrfid_license_isValid() && empty($key)) { ?>
				<p class="dmrfid_message dmrfid_error"><strong><?php _e('Enter your support license key.</strong> Your license key can be found in your membership email receipt or in your <a href="https://www.paidmembershipspro.com/login/?redirect_to=%2Fmembership-account%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-license%26utm_campaign%3Dmembership-account%26utm_content%3Dno-key" target="_blank">Membership Account</a>.', 'digital-members-rfid' );?></p>
			<?php } elseif(!dmrfid_license_isValid()) { ?>
				<p class="dmrfid_message dmrfid_error"><strong><?php _e('Your license is invalid or expired.', 'digital-members-rfid' );?></strong> <?php _e('Visit the DmRFID <a href="https://www.paidmembershipspro.com/login/?redirect_to=%2Fmembership-account%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-license%26utm_campaign%3Dmembership-account%26utm_content%3Dkey-not-valid" target="_blank">Membership Account</a> page to confirm that your account is active and to find your license key.', 'digital-members-rfid' );?></p>
			<?php } else { ?>													
				<p class="dmrfid_message dmrfid_success"><?php printf(__('<strong>Thank you!</strong> A valid <strong>%s</strong> license key has been used to activate your support license on this site.', 'digital-members-rfid' ), ucwords($dmrfid_license_check['license']));?></p>
			<?php } ?>

			<form action="" method="post">
			<table class="form-table">
				<tbody>
					<tr id="dmrfid-settings-key-box">
						<td>
							<input type="password" name="dmrfid-license-key" id="dmrfid-license-key" value="<?php echo esc_attr($key);?>" placeholder="<?php _e('Enter license key here...', 'digital-members-rfid' );?>" size="40"  />
							<?php wp_nonce_field( 'dmrfid-key-nonce', 'dmrfid-key-nonce' ); ?>
							<?php submit_button( __( 'Validate Key', 'digital-members-rfid' ), 'primary', 'dmrfid-verify-submit', false ); ?>
						</td>
					</tr>
				</tbody>
			</table>
			</form>

			<p>
				<?php if ( ! dmrfid_license_isValid() ) { ?>
					<a class="button button-primary button-hero" href="https://www.paidmembershipspro.com/membership-checkout/?level=20&utm_source=plugin&utm_medium=dmrfid-license&utm_campaign=plus-checkout&utm_content=buy-plus" target="_blank"><?php echo esc_html( 'Buy Plus License', 'digital-members-rfid' ); ?></a>
					<a class="button button-hero" href="https://www.paidmembershipspro.com/pricing/?utm_source=plugin&utm_medium=dmrfid-license&utm_campaign=pricing&utm_content=view-license-options" target="_blank"><?php echo esc_html( 'View Support License Options', 'digital-members-rfid' ); ?></a>
				<?php } else { ?>
					<a class="button button-primary button-hero" href="https://www.paidmembershipspro.com/login/?redirect_to=%2Fmembership-account%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-license%26utm_campaign%3Dmembership-account%26utm_content%3Dview-account" target="_blank"><?php echo esc_html( 'Manage My Account', 'digital-members-rfid' ); ?></a>
					<a class="button button-hero" href="https://www.paidmembershipspro.com/login/?redirect_to=%2Fnew-topic%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-license%26utm_campaign%3Dsupport%26utm_content%3Dnew-support-ticket" target="_blank"><?php echo esc_html( 'Open Support Ticket', 'digital-members-rfid' ); ?></a>
				<?php } ?>
			</p>

			<hr />
			
			<div class="clearfix"></div>

			<img class="dmrfid_icon alignright" src="<?php echo DMRFID_URL?>/images/Digital-Members-RFID_icon.png" border="0" alt="Digital Members RFID(c) - All Rights Reserved" />
			<?php
				$allowed_dmrfid_license_strings_html = array (
					'a' => array (
						'href' => array(),
						'target' => array(),
						'title' => array(),
					),
					'strong' => array(),
					'em' => array(),		);
			?>

			<?php
				echo '<p>' . sprintf( wp_kses( __( 'Digital Members RFID and our Add Ons are distributed under the <a href="%s" title="GPLv2 license" target="_blank">GPLv2 license</a>. This means, among other things, that you may use the software on this site or any other site free of charge.', 'digital-members-rfid' ), $allowed_dmrfid_license_strings_html ), 'https://www.paidmembershipspro.com/features/digital-members-rfid-is-100-gpl/?utm_source=plugin&utm_medium=dmrfid-license&utm_campaign=documentation&utm_content=gpl' ) . '</p>';
			?>

			<?php
				echo '<p>' . wp_kses( __( '<strong>Digital Members RFID offers plans for automatic updates of Add Ons and premium support.</strong> These plans include a Plus license key which we recommend for all public websites running Digital Members RFID. A Plus license key allows you to automatically install new Add Ons and update  active Add Ons when a new security, bug fix, or feature enhancement is released.' ), $allowed_dmrfid_license_strings_html ) . '</p>';
			?>

			<?php
				echo '<p>' . wp_kses( __( '<strong>Need help?</strong> Your license allows you to open new tickets in our private support area. Purchases are backed by a 30 day, no questions asked refund policy.' ), $allowed_dmrfid_license_strings_html ) . '</p>';
			?>

			<p><a href="https://www.paidmembershipspro.com/pricing/?utm_source=plugin&utm_medium=dmrfid-license&utm_campaign=pricing&utm_content=view-license-options" target="_blank"><?php echo esc_html( 'View Support License Options &raquo;', 'digital-members-rfid' ); ?></a></p>

		</div> <!-- end about-text -->
	</div> <!-- end about-wrap -->

<?php

require_once(dirname(__FILE__) . "/admin_footer.php");
?>
