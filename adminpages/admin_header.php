<?php
	require_once(dirname(__FILE__) . "/functions.php");

	if(isset($_REQUEST['page']))
		$view = sanitize_text_field($_REQUEST['page']);
	else
		$view = "";

	global $dmrfid_ready, $msg, $msgt;
	///$dmrfid_ready = dmrfid_is_ready();
	if(!$dmrfid_ready){
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
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-membershiplevels&edit=-1') . "\">" . __("Agregue un nivel de membresía para comenzar.", 'digital-members-rfid' ) . "</a>";
		elseif($dmrfid_level_ready && !$dmrfid_pages_ready && $view != "dmrfid-pagesettings")
			$msgt .= " <strong>" . __( 'Próximo paso:', 'digital-members-rfid' ) . "</strong> <a href=\"" . admin_url('admin.php?page=dmrfid-pagesettings') . "\">" . __("Configurar las páginas de membresía", 'digital-members-rfid' ) . "</a>.";
		elseif($dmrfid_level_ready && $dmrfid_pages_ready && !$dmrfid_gateway_ready && $view != "dmrfid-paymentsettings" && ! dmrfid_onlyFreeLevels())
			$msgt .= " <strong>" . __( 'Próximo paso:', 'digital-members-rfid' ) . "</strong> <a href=\"" . admin_url('admin.php?page=dmrfid-paymentsettings') . "\">" . __("Configure su certificado SSL y pasarela de pago", 'digital-members-rfid' ) . "</a>.";

		if(empty($msgt))
			$msg = false;
	}

	//check level compatibility
	if(!dmrfid_checkLevelForStripeCompatibility())
	{
		$msg = -1;
		$msgt = __("Stripe no admite los detalles de facturación de algunos de sus niveles de membresía.", 'digital-members-rfid' );
		if($view == "dmrfid-membershiplevels" && !empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0)
		{
			if(!dmrfid_checkLevelForStripeCompatibility($_REQUEST['edit']))
			{
				global $dmrfid_stripe_error;
				$dmrfid_stripe_error = true;
				$msg = -1;
				$msgt = __("Stripe no admite los detalles de facturación para este nivel. Revise las notas en la sección Detalles de facturación a continuación.", 'digital-members-rfid' );
			}
		}
		elseif($view == "dmrfid-membershiplevels")
			$msgt .= " " . __("Los niveles con problemas se destacan a continuación.", 'digital-members-rfid' );
		else
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-membershiplevels') . "\">" . __("Edita tus niveles", 'digital-members-rfid' ) . "</a>.";
	}

	if(!dmrfid_checkLevelForPayflowCompatibility())
	{
		$msg = -1;
		$msgt = __("Payflow no admite los detalles de facturación de algunos de sus niveles de membresía.", 'digital-members-rfid' );
		if($view == "dmrfid-membershiplevels" && !empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0)
		{
			if(!dmrfid_checkLevelForPayflowCompatibility($_REQUEST['edit']))
			{
				global $dmrfid_payflow_error;
				$dmrfid_payflow_error = true;
				$msg = -1;
				$msgt = __("Payflow no admite los detalles de facturación para este nivel. Revise las notas en la sección Detalles de facturación a continuación.", 'digital-members-rfid' );
			}
		}
		elseif($view == "dmrfid-membershiplevels")
			$msgt .= " " . __("Los niveles con problemas se destacan a continuación.", 'digital-members-rfid' );
		else
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-membershiplevels') . "\">" . __("Edita tus niveles", 'digital-members-rfid' ) . "</a>.";
	}

	if(!dmrfid_checkLevelForBraintreeCompatibility())
	{
		global $dmrfid_braintree_error;

		if ( false == $dmrfid_braintree_error ) {
			$msg  = - 1;
			$msgt = __( "Braintree no admite los detalles de facturación de algunos de sus niveles de membresía.", 'digital-members-rfid' );
		}
		if($view == "dmrfid-membershiplevels" && !empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0)
		{
			if(!dmrfid_checkLevelForBraintreeCompatibility($_REQUEST['edit']))
			{

				// Don't overwrite existing messages
				if ( false == $dmrfid_braintree_error  ) {
					$dmrfid_braintree_error = true;
					$msg                   = - 1;
					$msgt                  = __( "Braintree no admite los detalles de facturación para este nivel. Revise las notas en la sección Detalles de facturación a continuación.", 'digital-members-rfid' );
				}
			}
		}
		elseif($view == "dmrfid-membershiplevels")
			$msgt .= " " . __("Los niveles con problemas se destacan a continuación.", 'digital-members-rfid' );
		else {
			if ( false === $dmrfid_braintree_error  ) {
				$msgt .= " <a href=\"" . admin_url( 'admin.php?page=dmrfid-membershiplevels' ) . "\">" . __( "Edita tus niveles", 'digital-members-rfid' ) . "</a>.";
			}
		}
	}

	if(!dmrfid_checkLevelForTwoCheckoutCompatibility())
	{
		$msg = -1;
		$msgt = __("TwoCheckout no admite los detalles de facturación de algunos de sus niveles de membresía.", 'digital-members-rfid' );
		if($view == "dmrfid-membershiplevels" && !empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0)
		{
			if(!dmrfid_checkLevelForTwoCheckoutCompatibility($_REQUEST['edit']))
			{
				global $dmrfid_twocheckout_error;
				$dmrfid_twocheckout_error = true;

				$msg = -1;
				$msgt = __("2Checkout no admite los detalles de facturación para este nivel. Revise las notas en la sección Detalles de facturación a continuación.", 'digital-members-rfid' );
			}
		}
		elseif($view == "dmrfid-membershiplevels")
			$msgt .= " " . __("Los niveles con problemas se destacan a continuación.", 'digital-members-rfid' );
		else
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-membershiplevels') . "\">" . __("Edita tus niveles", 'digital-members-rfid' ) . "</a>.";
	}

	if ( ! dmrfid_check_discount_code_for_gateway_compatibility() ) {
		$msg = -1;
		$msgt = __( 'Su puerta de enlace no admite los detalles de facturación de algunos de sus códigos de descuento.', 'digital-members-rfid' );
		if ( $view == 'dmrfid-discountcodes' && ! empty($_REQUEST['edit']) && $_REQUEST['edit'] > 0 ) {
			if ( ! dmrfid_check_discount_code_for_gateway_compatibility( $_REQUEST['edit'] ) ) {
				$msg = -1;
				$msgt = __( 'Su puerta de enlace no admite los detalles de facturación de este código de descuento.', 'digital-members-rfid' );
			}
		} elseif ( $view == 'dmrfid-discountcodes' ) {
			$msg = -1;
			$msgt .= " " . __("Los códigos de descuento con problemas se destacan a continuación.", 'digital-members-rfid' );
		} else {
			$msgt .= " <a href=\"" . admin_url('admin.php?page=dmrfid-discountcodes') . "\">" . __("Edite sus códigos de descuento", 'digital-members-rfid' ) . "</a>.";

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
        $msgt = sprintf(__("Stripe Gateway requiere PHP 5.3.29 o superior. Recomendamos actualizar a PHP% s o superior. Pídale a su anfitrión que actualice.", "digital-members-rfid" ), DMRFID_MIN_PHP_VERSION );
    } elseif($gateway == "braintree" && version_compare( PHP_VERSION, '5.4.45', '<' ) ) {
        $msg = -1;
        $msgt = sprintf(__("Stripe Gateway requiere PHP 5.3.29 o superior. Recomendamos actualizar a PHP% s o superior. Pídale a su anfitrión que actualice.", "digital-members-rfid" ), DMRFID_MIN_PHP_VERSION );
    }

	//if no errors yet, let's check and bug them if < our DMRFID_PHP_MIN_VERSION
	if( empty($msgt) && version_compare( PHP_VERSION, DMRFID_MIN_PHP_VERSION, '<' ) ) {
		$msg = 1;
		$msgt = sprintf(__("Recomendamos actualizar a PHP% s o superior. Pídale a su anfitrión que actualice.", "digital-members-rfid" ), DMRFID_MIN_PHP_VERSION );
	}

	if( ! empty( $msg ) && $view != 'dmrfid-dashboard' ) { ?>
		<div id="message" class="<?php if($msg > 0) echo "updated fade"; else echo "error"; ?>"><p><?php echo $msgt?></p></div>
	<?php } ?>

<div class="wrap dmrfid_admin">
	<div class="dmrfid_banner">
		<a class="dmrfid_logo" title="Digital Members RFID - Membership Plugin for WordPress" target="_blank" href="<?php echo dmrfid_https_filter("https://www.managertechnology.com.co/?utm_source=plugin&utm_medium=dmrfid-admin-header&utm_campaign=homepage")?>"><img src="<?php echo DMRFID_URL?>/images/Digital-Members-RFID.png" width="350" height="75" border="0" alt="Digital Members RFID(c) - All Rights Reserved" /></a>
		<div class="dmrfid_meta">
			<span class="dmrfid_version">v<?php echo DMRFID_VERSION?></span>
			<a target="_blank" href="<?php echo dmrfid_https_filter("https://www.managertechnology.com.co/documentation/?utm_source=plugin&utm_medium=dmrfid-admin-header&utm_campaign=documentation")?>"><?php _e('Documentación', 'digital-members-rfid' );?></a>
			<a target="_blank" href="https://www.managertechnology.com.co/pricing/?utm_source=plugin&utm_medium=dmrfid-admin-header&utm_campaign=pricing&utm_content=get-support"><?php _e('Obtener apoyo', 'digital-members-rfid' );?></a>

			<?php if ( dmrfid_license_isValid() ) { ?>
				<?php printf(__( '<a class="dmrfid_license_tag dmrfid_license_tag-valid" href="%s">Licencia válida</a>', 'digital-members-rfid' ), admin_url( 'admin.php?page=dmrfid-license' ) ); ?>				
			<?php } elseif ( ! defined( 'DMRFID_LICENSE_NAG' ) || DMRFID_LICENSE_NAG == true ) { ?>
				<?php printf(__( '<a class="dmrfid_license_tag dmrfid_license_tag-invalid" href="%s">Sin licencia</a>', 'digital-members-rfid' ), admin_url( 'admin.php?page=dmrfid-license' ) ); ?>
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
			'dmrfid-devices',
			'dmrfid-apir',
			'dmrfid-license'
		);
		if( in_array( $view, $settings_tabs ) ) { ?>
		<nav class="nav-tab-wrapper">
			<?php if(current_user_can('dmrfid_dashboard')) { ?>
				<a href="<?php echo admin_url('admin.php?page=dmrfid-dashboard');?>" class="nav-tab<?php if($view == 'dmrfid-dashboard') { ?> nav-tab-active<?php } ?>"><?php _e('Tablero', 'digital-members-rfid' );?></a>
			<?php } ?>

			<?php if(current_user_can('dmrfid_memberslist')) { ?>
				<a href="<?php echo admin_url('admin.php?page=dmrfid-memberslist');?>" class="nav-tab<?php if($view == 'dmrfid-memberslist') { ?> nav-tab-active<?php } ?>"><?php _e('Miembros', 'digital-members-rfid' );?></a>
			<?php } ?>

			<?php if(current_user_can('dmrfid_orders')) { ?>
				<a href="<?php echo admin_url('admin.php?page=dmrfid-orders');?>" class="nav-tab<?php if($view == 'dmrfid-orders') { ?> nav-tab-active<?php } ?>"><?php _e('Pedidos', 'digital-members-rfid' );?></a>
			<?php } ?>

			<?php if(current_user_can('dmrfid_reports')) { ?>
				<a href="<?php echo admin_url('admin.php?page=dmrfid-reports');?>" class="nav-tab<?php if($view == 'dmrfid-reports') { ?> nav-tab-active<?php } ?>"><?php _e('Informes', 'digital-members-rfid' );?></a>
			<?php } ?>

			<?php if(current_user_can('dmrfid_membershiplevels')) { ?>
				<a href="<?php echo admin_url('admin.php?page=dmrfid-membershiplevels');?>" class="nav-tab<?php if( in_array( $view, array( 'dmrfid-membershiplevels', 'dmrfid-discountcodes', 'dmrfid-pagesettings', 'dmrfid-paymentsettings', 'dmrfid-emailsettings', 'dmrfid-advancedsettings' ) ) ) { ?> nav-tab-active<?php } ?>"><?php _e('Configuraciones', 'digital-members-rfid' );?></a>
			<?php } ?>

			<?php if(current_user_can('dmrfid_addons')) { ?>
				<a href="<?php echo admin_url('admin.php?page=dmrfid-addons');?>" class="nav-tab<?php if($view == 'dmrfid-addons') { ?> nav-tab-active<?php } ?>"><?php _e('Complementos', 'digital-members-rfid' );?></a>
			<?php } ?>

			<?php if(current_user_can('manage_options')) { ?>
				<a href="<?php echo admin_url('admin.php?page=dmrfid-devices');?>" class="nav-tab<?php if($view == 'dmrfid-devices') { ?> nav-tab-active<?php } ?>"><?php _e('Dispositivos', 'digital-members-rfid' );?></a>
			<?php } ?>

			<?php if(current_user_can('manage_options')) { ?>
				<a href="<?php echo admin_url('admin.php?page=dmrfid-license');?>" class="nav-tab<?php if($view == 'dmrfid-license') { ?> nav-tab-active<?php } ?>"><?php _e('Licencia', 'digital-members-rfid' );?></a>
			<?php } ?>

			<?php if(current_user_can('manage_options')) { ?>
				<a href="/wp-content/plugins/digital-members-rfid/api.php" class="nav-tab" target="_blank">API</a>
			<?php } ?>
		</nav>

		<?php if( $view == 'dmrfid-membershiplevels' || $view == 'dmrfid-discountcodes' || $view == 'dmrfid-pagesettings' || $view == 'dmrfid-paymentsettings' || $view == 'dmrfid-emailsettings' || $view == 'dmrfid-advancedsettings' ) { ?>
			<ul class="subsubsub">
				<?php if(current_user_can('dmrfid_membershiplevels')) { ?>
					<li><a href="<?php echo admin_url('admin.php?page=dmrfid-membershiplevels');?>" title="<?php _e('Niveles de membresía', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-membershiplevels') { ?>current<?php } ?>"><?php _e('Niveles', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
				<?php } ?>

				<?php if(current_user_can('dmrfid_discountcodes')) { ?>
					<li><a href="<?php echo admin_url('admin.php?page=dmrfid-discountcodes');?>" title="<?php _e('Códigos de descuento', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-discountcodes') { ?>current<?php } ?>"><?php _e('Códigos de descuento', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
				<?php } ?>

				<?php if(current_user_can('dmrfid_pagesettings')) { ?>
					<li><a href="<?php echo admin_url('admin.php?page=dmrfid-pagesettings');?>" title="<?php _e('Configuración de página', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-pagesettings') { ?>current<?php } ?>"><?php _e('Páginas', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
				<?php } ?>

				<?php if(current_user_can('dmrfid_paymentsettings')) { ?>
					<li><a href="<?php echo admin_url('admin.php?page=dmrfid-paymentsettings');?>" title="<?php _e('Pasarela de pago &amp; configuración de SSL', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-paymentsettings') { ?>current<?php } ?>"><?php _e('Pasarela de pago & SSL', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
				<?php } ?>

				<?php if(current_user_can('dmrfid_emailsettings')) { ?>
					<li><a href="<?php echo admin_url('admin.php?page=dmrfid-emailsettings');?>" title="<?php _e('Ajustes del correo electrónico', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-emailsettings') { ?>current<?php } ?>"><?php _e('E-Mail', 'digital-members-rfid' );?></a>&nbsp;|&nbsp;</li>
				<?php } ?>

				<?php if(current_user_can('dmrfid_advancedsettings')) { ?>
					<li><a href="<?php echo admin_url('admin.php?page=dmrfid-advancedsettings');?>" title="<?php _e('Ajustes avanzados', 'digital-members-rfid' );?>" class="<?php if($view == 'dmrfid-advancedsettings') { ?>current<?php } ?>"><?php _e('Avanzada', 'digital-members-rfid' );?></a></li>
				<?php } ?>
			</ul>
			<br class="clear" />
		<?php } ?>
	<?php } ?>
