<?php
	require_once(dirname(__FILE__) . "/functions.php");

	if(isset($_REQUEST['page']))
		$view = sanitize_text_field($_REQUEST['page']);
	else
		$view = "";

	global $dmrfid_ready, $msg, $msgt;
	///$dmrfid_ready = dmrfid_is_ready();
	if(!$dmrfid_ready)
	{
		global $dmrfid_level_ready, $dmrfid_gateway_ready, $dmrfid_pages_ready;
		if(!isset($edit))
		{
			if(isset($_REQUEST['edit']))
				$edit = intval($_REQUEST['edit']);
			else
				$edit = false;
		}

		if(empty($msg))
			$msg = -1;
		if(empty($dmrfid_level_ready) && empty($edit) && $view != "dmrfid-membershiplevels")
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-membershiplevels&edit=-1') . "\">" . __("Add a membership level to get started.", 'digital-members-rfid' ) . "</a>";
		elseif($dmrfid_level_ready && !$dmrfid_pages_ready && $view != "dmrfid-pagesettings")
			$msgt .= " <strong>" . __( 'Next step:', 'digital-members-rfid' ) . "</strong> <a href=\"" . admin_url('admin.php?page=dmrfid-pagesettings') . "\">" . __("Set up the membership pages", 'digital-members-rfid' ) . "</a>.";
		elseif($dmrfid_level_ready && $dmrfid_pages_ready && !$dmrfid_gateway_ready && $view != "dmrfid-paymentsettings" && ! dmrfid_onlyFreeLevels())
			$msgt .= " <strong>" . __( 'Next step:', 'digital-members-rfid' ) . "</strong> <a href=\"" . admin_url('admin.php?page=dmrfid-paymentsettings') . "\">" . __("Set up your SSL certificate and payment gateway", 'digital-members-rfid' ) . "</a>.";

		if(empty($msgt))
			$msg = false;
	}

	//check level compatibility
	if(!dmrfid_checkLevelForStripeCompatibility())
	{
		$msg = -1;
		$msgt = __("The billing details for some of your membership levels is not supported by Stripe.", 'digital-members-rfid' );
		if($view == "dmrfid-membershiplevels" && !empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0)
		{
			if(!dmrfid_checkLevelForStripeCompatibility($_REQUEST['edit']))
			{
				global $dmrfid_stripe_error;
				$dmrfid_stripe_error = true;
				$msg = -1;
				$msgt = __("The billing details for this level are not supported by Stripe. Please review the notes in the Billing Details section below.", 'digital-members-rfid' );
			}
		}
		elseif($view == "dmrfid-membershiplevels")
			$msgt .= " " . __("The levels with issues are highlighted below.", 'digital-members-rfid' );
		else
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-membershiplevels') . "\">" . __("Please edit your levels", 'digital-members-rfid' ) . "</a>.";
	}

	if(!dmrfid_checkLevelForPayflowCompatibility())
	{
		$msg = -1;
		$msgt = __("The billing details for some of your membership levels is not supported by Payflow.", 'digital-members-rfid' );
		if($view == "dmrfid-membershiplevels" && !empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0)
		{
			if(!dmrfid_checkLevelForPayflowCompatibility($_REQUEST['edit']))
			{
				global $dmrfid_payflow_error;
				$dmrfid_payflow_error = true;
				$msg = -1;
				$msgt = __("The billing details for this level are not supported by Payflow. Please review the notes in the Billing Details section below.", 'digital-members-rfid' );
			}
		}
		elseif($view == "dmrfid-membershiplevels")
			$msgt .= " " . __("The levels with issues are highlighted below.", 'digital-members-rfid' );
		else
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-membershiplevels') . "\">" . __("Please edit your levels", 'digital-members-rfid' ) . "</a>.";
	}

	if(!dmrfid_checkLevelForBraintreeCompatibility())
	{
		global $dmrfid_braintree_error;

		if ( false == $dmrfid_braintree_error ) {
			$msg  = - 1;
			$msgt = __( "The billing details for some of your membership levels is not supported by Braintree.", 'digital-members-rfid' );
		}
		if($view == "dmrfid-membershiplevels" && !empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0)
		{
			if(!dmrfid_checkLevelForBraintreeCompatibility($_REQUEST['edit']))
			{

				// Don't overwrite existing messages
				if ( false == $dmrfid_braintree_error  ) {
					$dmrfid_braintree_error = true;
					$msg                   = - 1;
					$msgt                  = __( "The billing details for this level are not supported by Braintree. Please review the notes in the Billing Details section below.", 'digital-members-rfid' );
				}
			}
		}
		elseif($view == "dmrfid-membershiplevels")
			$msgt .= " " . __("The levels with issues are highlighted below.", 'digital-members-rfid' );
		else {
			if ( false === $dmrfid_braintree_error  ) {
				$msgt .= " <a href=\"" . admin_url( 'admin.php?page=dmrfid-membershiplevels' ) . "\">" . __( "Please edit your levels", 'digital-members-rfid' ) . "</a>.";
			}
		}
	}

	if(!dmrfid_checkLevelForTwoCheckoutCompatibility())
	{
		$msg = -1;
		$msgt = __("The billing details for some of your membership levels is not supported by TwoCheckout.", 'digital-members-rfid' );
		if($view == "dmrfid-membershiplevels" && !empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0)
		{
			if(!dmrfid_checkLevelForTwoCheckoutCompatibility($_REQUEST['edit']))
			{
				global $dmrfid_twocheckout_error;
				$dmrfid_twocheckout_error = true;

				$msg = -1;
				$msgt = __("The billing details for this level are not supported by 2Checkout. Please review the notes in the Billing Details section below.", 'digital-members-rfid' );
			}
		}
		elseif($view == "dmrfid-membershiplevels")
			$msgt .= " " . __("The levels with issues are highlighted below.", 'digital-members-rfid' );
		else
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-membershiplevels') . "\">" . __("Please edit your levels", 'digital-members-rfid' ) . "</a>.";
	}

	if ( ! dmrfid_check_discount_code_for_gateway_compatibility() ) {
		$msg = -1;
		$msgt = __( 'The billing details for some of your discount codes are not supported by your gateway.', 'digital-members-rfid' );
		if ( $view == 'dmrfid-discountcodes' && ! empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0 ) {
			if ( ! dmrfid_check_discount_code_for_gateway_compatibility( $_REQUEST['edit'] ) ) {
				$msg = -1;
				$msgt = __( 'The billing details for this discount code are not supported by your gateway.', 'digital-members-rfid' );
			}
		} elseif ( $view == 'dmrfid-discountcodes' ) {
			$msg = -1;
			$msgt .= " " . __("The discount codes with issues are highlighted below.", 'digital-members-rfid' );
		} else {
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-discountcodes') . "\">" . __("Please edit your discount codes", 'digital-members-rfid' ) . "</a>.";

		}
	}

	//check gateway dependencies
	$gateway = dmrfid_getOption('gateway');
	if($gateway == "stripe" && version_compare( PHP_VERSION, '5.3.29', '>=' ) ) {
		DmRFIDGateway_stripe::dependencies();
	} elseif($gateway == "braintree" && version_compare( PHP_VERSION, '5.4.45', '>=' ) ) {
		DmRFIDGateway_braintree::dependencies();
	} elseif($gateway == "stripe" && version_compare( PHP_VERSION, '5.3.29', '<' ) ) {
        $msg = -1;
        $msgt = sprintf(__("The Stripe Gateway requires PHP 5.3.29 or greater. We recommend upgrading to PHP %s or greater. Ask your host to upgrade.", "digital-members-rfid" ), DMRFID_MIN_PHP_VERSION );
    } elseif($gateway == "braintree" && version_compare( PHP_VERSION, '5.4.45', '<' ) ) {
        $msg = -1;
        $msgt = sprintf(__("The Braintree Gateway requires PHP 5.4.45 or greater. We recommend upgrading to PHP %s or greater. Ask your host to upgrade.", "digital-members-rfid" ), DMRFID_MIN_PHP_VERSION );
    }

	//if no errors yet, let's check and bug them if < our DMRFID_PHP_MIN_VERSION
	if( empty($msgt) && version_compare( PHP_VERSION, DMRFID_MIN_PHP_VERSION, '<' ) ) {
		$msg = 1;
		$msgt = sprintf(__("We recommend upgrading to PHP %s or greater. Ask your host to upgrade.", "digital-members-rfid" ), DMRFID_MIN_PHP_VERSION );
	}

	if( ! empty( $msg ) && $view != 'dmrfid-dashboard' ) { ?>
		<div id="message" class="<?php if($msg > 0) echo "updated fade"; else echo "error"; ?>"><p><?php echo $msgt?></p></div>
	<?php } ?>

<div class="wrap dmrfid_admin">
	<div class="dmrfid_banner">
		<a class="dmrfid_logo" title="Digital Members RFID - Membership Plugin for WordPress" target="_blank" href="<?php echo dmrfid_https_filter("https://www.paidmembershipspro.com/?utm_source=plugin&utm_medium=dmrfid-admin-header&utm_campaign=homepage")?>"><img src="<?php echo DMRFID_URL?>/images/Digital-Members-RFID.png" width="350" height="75" border="0" alt="Digital Members RFID(c) - All Rights Reserved" /></a>
		<div class="dmrfid_meta">
			<span class="dmrfid_version">v<?php echo DMRFID_VERSION?></span>
			<a target="_blank" href="<?php echo dmrfid_https_filter("https://www.paidmembershipspro.com/documentation/?utm_source=plugin&utm_medium=dmrfid-admin-header&utm_campaign=documentation")?>"><?php _e('Documentation', 'digital-members-rfid' );?></a>
			<a target="_blank" href="https://www.paidmembershipspro.com/pricing/?utm_source=plugin&utm_medium=dmrfid-admin-header&utm_campaign=pricing&utm_content=get-support"><?php _e('Get Support', 'digital-members-rfid' );?></a>

			<?php if ( dmrfid_license_isValid() ) { ?>
				<?php printf(__( '<a class="dmrfid_license_tag dmrfid_license_tag-valid" href="%s">Valid License</a>', 'digital-members-rfid' ), admin_url( 'admin.php?page=dmrfid-license' ) ); ?>				
			<?php } elseif ( ! defined( 'DMRFID_LICENSE_NAG' ) || DMRFID_LICENSE_NAG == true ) { ?>
				<?php printf(__( '<a class="dmrfid_license_tag dmrfid_license_tag-invalid" href="%s">No License</a>', 'digital-members-rfid' ), admin_url( 'admin.php?page=dmrfid-license' ) ); ?>
			<?php } ?>

		</div>
	</div>
	<div id="dmrfid_notifications">
	</div>
	<?php
		// To debug a specific notification.
		if ( !empty( $_REQUEST['dmrfid_notification'] ) ) {
			$specific_notification = '&dmrfid_notification=' . intval( $_REQUEST['dmrfid_notification'] );
		} else {	
			$specific_notification = '';
		}
	?>
	<script>
		jQuery(document).ready(function() {
			jQuery.get('<?php echo get_admin_url(NULL, "/admin-ajax.php?action=dmrfid_notifications" . $specific_notification ); ?>', function(data) {
				if(data && data != 'NULL')
					jQuery('#dmrfid_notifications').html(data);
			});
		});
	</script>
	<h2 class="dmrfid_wp-notice-fix">&nbsp;</h2>
	<?php
		$settings_tabs = array(
			'dmrfid-dashboard',
			'dmrfid-membershiplevels',
			'dmrfid-memberslist',
			'dmrfid-reports',
			'dmrfid-orders',
			'dmrfid-discountcodes',
			'dmrfid-pagesettings',
			'dmrfid-paymentsettings',
			'dmrfid-emailsettings',
			'dmrfid-advancedsettings',
			'dmrfid-addons',
			'dmrfid-license'
		);
		if( in_array( $view, $settings_tabs ) ) { ?>
	<nav class="nav-tab-wrapper">
		<?php if(current_user_can('dmrfid_dashboard')) { ?>
			<a href="<?php echo admin_url('admin.php?page=dmrfid-dashboard');?>" class="nav-tab<?php if($view == 'dmrfid-dashboard') { ?> nav-tab-active<?php } ?>"><?php _e('Dashboard', 'digital-members-rfid' );?></a>
		<?php } ?>

		<?php if(current_user_can('dmrfid_memberslist')) { ?>
			<a href="<?php echo admin_url('admin.php?page=dmrfid-memberslist');?>" class="nav-tab<?php if($view == 'dmrfid-memberslist') { ?> nav-tab-active<?php } ?>"><?php _e('Members', 'digital-members-rfid' );?></a>
		<?php } ?>

		<?php if(current_user_can('dmrfid_orders')) { ?>
			<a href="<?php echo admin_url('admin.php?page=dmrfid-orders');?>" class="nav-tab<?php if($view == 'dmrfid-orders') { ?> nav-tab-active<?php } ?>"><?php _e('Orders', 'digital-members-rfid' );?></a>
		<?php } ?>

		<?php if(current_user_can('dmrfid_reports')) { ?>
			<a href="<?php echo admin_url('admin.php?page=dmrfid-reports');?>" class="nav-tab<?php if($view == 'dmrfid-reports') { ?> nav-tab-active<?php } ?>"><?php _e('Reports', 'digital-members-rfid' );?></a>
		<?php } ?>

		<?php if(current_user_can('dmrfid_membershiplevels')) { ?>
			<a href="<?php echo admin_url('admin.php?page=dmrfid-membershiplevels');?>" class="nav-tab<?php if( in_array( $view, array( 'dmrfid-membershiplevels', 'dmrfid-discountcodes', 'dmrfid-pagesettings', 'dmrfid-paymentsettings', 'dmrfid-emailsettings', 'dmrfid-advancedsettings' ) ) ) { ?> nav-tab-active<?php } ?>"><?php _e('Settings', 'digital-members-rfid' );?></a>
		<?php } ?>

		<?php if(current_user_can('dmrfid_addons')) { ?>
			<a href="<?php echo admin_url('admin.php?page=dmrfid-addons');?>" class="nav-tab<?php if($view == 'dmrfid-addons') { ?> nav-tab-active<?php } ?>"><?php _e('Add Ons', 'digital-members-rfid' );?></a>
		<?php } ?>

		<?php if(current_user_can('manage_options')) { ?>
			<a href="<?php echo admin_url('admin.php?page=dmrfid-license');?>" class="nav-tab<?php if($view == 'dmrfid-license') { ?> nav-tab-active<?php } ?>"><?php _e('License', 'digital-members-rfid' );?></a>
		<?php } ?>
	</nav>

	<?php if( $view == 'dmrfid-membershiplevels' || $view == 'dmrfid-discountcodes' || $view == 'dmrfid-pagesettings' || $view == 'dmrfid-paymentsettings' || $view == 'dmrfid-emailsettings' || $view == 'dmrfid-advancedsettings' ) { ?>
		<ul class="subsubsub">
			<?php if(current_user_can('dmrfid_membershiplevels')) { ?>
				<li><a href="<?php echo admin_url('admin.php?page=dmrfid-membershiplevels');?>" title="<?php _e('Membership Levels', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-membershiplevels') { ?>current<?php } ?>"><?php _e('Levels', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
			<?php } ?>

			<?php if(current_user_can('dmrfid_discountcodes')) { ?>
				<li><a href="<?php echo admin_url('admin.php?page=dmrfid-discountcodes');?>" title="<?php _e('Discount Codes', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-discountcodes') { ?>current<?php } ?>"><?php _e('Discount Codes', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
			<?php } ?>

			<?php if(current_user_can('dmrfid_pagesettings')) { ?>
				<li><a href="<?php echo admin_url('admin.php?page=dmrfid-pagesettings');?>" title="<?php _e('Page Settings', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-pagesettings') { ?>current<?php } ?>"><?php _e('Pages', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
			<?php } ?>

			<?php if(current_user_can('dmrfid_paymentsettings')) { ?>
				<li><a href="<?php echo admin_url('admin.php?page=dmrfid-paymentsettings');?>" title="<?php _e('Payment Gateway &amp; SSL Settings', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-paymentsettings') { ?>current<?php } ?>"><?php _e('Payment Gateway &amp; SSL', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
			<?php } ?>

			<?php if(current_user_can('dmrfid_emailsettings')) { ?>
				<li><a href="<?php echo admin_url('admin.php?page=dmrfid-emailsettings');?>" title="<?php _e('Email Settings', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-emailsettings') { ?>current<?php } ?>"><?php _e('Email', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
			<?php } ?>

			<?php if(current_user_can('dmrfid_advancedsettings')) { ?>
				<li><a href="<?php echo admin_url('admin.php?page=dmrfid-advancedsettings');?>" title="<?php _e('Advanced Settings', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-advancedsettings') { ?>current<?php } ?>"><?php _e('Advanced', 'digital-members-rfid' );?></a></li>
			<?php } ?>
		</ul>
		<br class="clear" />
	<?php } ?>

	<?php } ?>
