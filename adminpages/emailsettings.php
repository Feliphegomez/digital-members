<?php
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("dmrfid_emailsettings")))
	{
		die(__("No tienes permisos para realizar esta acción.", 'digital-members-rfid' ));
	}	
	
	global $wpdb, $msg, $msgt;
	
	//get/set settings
	global $dmrfid_pages;
	
	//check nonce for saving settings
	if (!empty($_REQUEST['savesettings']) && (empty($_REQUEST['dmrfid_emailsettings_nonce']) || !check_admin_referer('savesettings', 'dmrfid_emailsettings_nonce'))) {
		$msg = -1;
		$msgt = __("¿Seguro que quieres hacer eso? Inténtalo de nuevo.", 'digital-members-rfid' );
		unset($_REQUEST['savesettings']);
	}	
	
	if(!empty($_REQUEST['savesettings']))
	{                   		
		//email options
		dmrfid_setOption("from_email");
		dmrfid_setOption("from_name");
		dmrfid_setOption("only_filter_dmrfid_emails");
		
		dmrfid_setOption("email_admin_checkout");
		dmrfid_setOption("email_admin_changes");
		dmrfid_setOption("email_admin_cancels");
		dmrfid_setOption("email_admin_billing");
		
		dmrfid_setOption("email_member_notification");
		
		//assume success
		$msg = true;
		$msgt = "Your email settings have been updated.";		
	}
	
	$from_email = dmrfid_getOption("from_email");
	$from_name = dmrfid_getOption("from_name");
	$only_filter_dmrfid_emails = dmrfid_getOption("only_filter_dmrfid_emails");
	
	$email_admin_checkout = dmrfid_getOption("email_admin_checkout");
	$email_admin_changes = dmrfid_getOption("email_admin_changes");
	$email_admin_cancels = dmrfid_getOption("email_admin_cancels");
	$email_admin_billing = dmrfid_getOption("email_admin_billing");	
	
	$email_member_notification = dmrfid_getOption("email_member_notification");
	
	if(empty($from_email))
	{
		$parsed = parse_url(home_url()); 
		$hostname = $parsed["host"];
		$host_parts = explode(".", $hostname);
		if ( count( $host_parts ) > 1 ) {
			$email_domain = $host_parts[count($host_parts) - 2] . "." . $host_parts[count($host_parts) - 1];
		} else {
			$email_domain = $parsed['host'];
		}		
		$from_email = "wordpress@" . $email_domain;
		dmrfid_setOption("from_email", $from_email);
	}
	
	if(empty($from_name))
	{		
		$from_name = "WordPress";
		dmrfid_setOption("from_name", $from_name);
	}
	
	// default from email wordpress@sitename
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) == 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}
	$default_from_email = 'wordpress@' . $sitename;
				
	require_once(dirname(__FILE__) . "/admin_header.php");		
?>

	<form action="" method="post" enctype="multipart/form-data"> 
		<?php wp_nonce_field('savesettings', 'dmrfid_emailsettings_nonce');?>
		
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Ajustes del correo electrónico', 'digital-members-rfid' ); ?></h1>
		<hr class="wp-header-end">
		<h2><?php _e( 'Enviar correos electrónicos desde', 'digital-members-rfid' ); ?></h2>
		<p><?php _e('De forma predeterminada, los mensajes de correo electrónico generados por el sistema se envían desde <em><b>wordpress@yourdomain.com </p> Puede actualizar esto desde la dirección utilizando los campos a continuación.', 'digital-members-rfid' );?></p>

		<table class="form-table">
		<tbody>                
			<tr>
				<th scope="row" valign="top">
					<label for="from_email"><?php _e('Desde el e-mail', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<input type="text" name="from_email" value="<?php echo esc_attr($from_email);?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="from_name"><?php _e('From Name', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<input type="text" name="from_name" value="<?php echo esc_attr($from_name);?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top">
					<label for="only_filter_dmrfid_emails"><?php _e('¿Solo filtrar correos electrónicos DmRFID?', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<input type="checkbox" id="only_filter_dmrfid_emails" name="only_filter_dmrfid_emails" value="1" <?php if(!empty($only_filter_dmrfid_emails)) { ?>checked="checked"<?php } ?> />
					<label for="only_filter_dmrfid_emails"><?php printf( __('Si no se marca, todos los correos electrónicos de "WordPress &lt;%s&gt;" se filtrarán para utilizar la configuración anterior.', 'digital-members-rfid' ),  $default_from_email );?></label>
				</td>
			</tr>
		</tbody>
		</table>
		<p class="submit"><input name="savesettings" type="submit" class="button-primary" value="<?php esc_attr_e( 'Guardar todas las configuraciones', 'digital-members-rfid' ); ?>" /></p>
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-email-content">
			<h2><?php _e( 'Personalización del contenido del correo electrónico', 'digital-members-rfid' ); ?></h2>
			<p><?php
			$allowed_email_customizing_html = array (
				'a' => array (
					'href' => array(),
					'target' => array(),
					'title' => array(),
				),
			);
			echo sprintf( wp_kses( __( 'Hay varias formas de modificar la apariencia de sus correos electrónicos RFID de Digital Members. Recomendamos usar el <a href="%s" title="Digital Members RFID-Email Templates Admin Editor Add On" target="_blank"> Add On del editor de administración de plantillas de correo electrónico </a>, que le permite modificar el encabezado, pie de página, asunto y cuerpo del correo electrónico para todas las comunicaciones de miembros y administradores. <a title="Digital Members RFID-Member Communications" target="_blank" href="%s"> Haga clic aquí para obtener más información sobre los correos electrónicos RFID de Digital Members </a>.', 'digital-members-rfid' ), $allowed_email_customizing_html ), 'https://www.managertechnology.com.co/add-ons/email-templates-admin-editor/?utm_source=plugin&utm_medium=dmrfid-emailsettings&utm_campaign=add-ons&utm_content=email-templates-admin-editor', 'http://www.managertechnology.com.co/documentation/member-communications/?utm_source=plugin&utm_medium=dmrfid-emailsettings&utm_campaign=documentation&utm_content=member-communications' );
		?></p>
		</div> <!-- end dmrfid_admin_section-email-content -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-email-deliverability">
			<h2><?php _e( 'Capacidad de entrega del correo electrónico', 'digital-members-rfid' ); ?></h2>

			<p><?php
				$allowed_email_troubleshooting_html = array (
					'a' => array (
						'href' => array(),
						'target' => array(),
						'title' => array(),
					),
					'em' => array(),
				);
				echo sprintf( wp_kses( __( 'Si tiene problemas con la entrega de correo electrónico desde su servidor, <a href="%s" title="Retrasos de suscripción de RFID para miembros digitales se agregan" target="_blank"> por favor lea nuestra guía de solución de problemas de correo electrónico </a>. Como alternativa, Digital Members RFID ofrece integración incorporada para SendWP. <em> Opcional: SendWP es un servicio de terceros para correo electrónico transaccional en WordPress. <a href="%s" title="Documentation on SendWP y Digital Members RFID" target="_blank"> Haga clic aquí para obtener más información sobre SendWP y Digital Members RFID </a> </em>.', 'digital-members-rfid' ), $allowed_email_troubleshooting_html ), 'https://www.managertechnology.com.co/troubleshooting-email-issues-sending-sent-spam-delivery-delays/?utm_source=plugin&utm_medium=dmrfid-emailsettings&utm_campaign=blog&utm_content=email-troubleshooting', 'https://www.managertechnology.com.co/documentation/member-communications/email-delivery-sendwp/?utm_source=plugin&utm_medium=dmrfid-emailsettings&utm_campaign=documentation&utm_content=sendwp' );
			?></p>

			<?php
				// Check to see if connected or not.
				$sendwp_connected = function_exists( 'sendwp_client_connected' ) && sendwp_client_connected() ? true : false;

				if ( ! $sendwp_connected ) { ?>
					<p><button id="dmrfid-sendwp-connect" class="button"><?php esc_html_e( 'Conectarse a SendWP', 'digital-members-rfid' ); ?></button></p>
				<?php } else { ?>
					<p><button id="dmrfid-sendwp-disconnect" class="button-primary"><?php esc_html_e( 'Desconectarse de SendWP', 'digital-members-rfid' ); ?></button></p>
					<?php
					// Update SendWP status to see if email forwarding is enabled or not.
					$sendwp_email_forwarding = function_exists( 'sendwp_forwarding_enabled' ) && sendwp_forwarding_enabled() ? true : false;
					
					// Messages for connected or not.
					$connected = __( 'Su sitio está conectado a SendWP.', 'digital-members-rfid' ) . " <a href='https://sendwp.com/account/' target='_blank' rel='nofollow'>" . __( 'View Your SendWP Account', 'digital-members-rfid' ) . "</a>";
					$disconnected = ' ' . sprintf( __( 'Habilite el envío de correo electrónico dentro de %s.', 'digital-members-rfid' ), '<a href="' . admin_url('/tools.php?page=sendwp') . '">SendWP Settings</a>' );
					?>
					<p class="description" id="dmrfid-sendwp-description"><?php echo $sendwp_email_forwarding ? $connected : $disconnected; ?></p>
				<?php }
			?>
		</div> <!-- end dmrfid_admin_section-email-deliverability -->
		<hr />
		<h2 class="title"><?php esc_html_e( 'Otras configuraciones de correo electrónico', 'digital-members-rfid' ); ?></h2>
		<table class="form-table">
		<tbody>                
			<tr>
				<th scope="row" valign="top">
					<label for="email_admin"><?php _e('Enviar los correos electrónicos de administrador del sitio', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<input type="checkbox" id="email_admin_checkout" name="email_admin_checkout" value="1" <?php if(!empty($email_admin_checkout)) { ?>checked="checked"<?php } ?> />
					<label for="email_admin_checkout"><?php _e('Cuando un miembro sale.', 'digital-members-rfid' );?></label>
					<br />
					<input type="checkbox" id="email_admin_changes" name="email_admin_changes" value="1" <?php if(!empty($email_admin_changes)) { ?>checked="checked"<?php } ?> />
					<label for="email_admin_changes"><?php _e('cuando un administrador cambia el nivel de membresía de un usuario a través del panel de control.', 'digital-members-rfid' );?></label>
					<br />
					<input type="checkbox" id="email_admin_cancels" name="email_admin_cancels" value="1" <?php if(!empty($email_admin_cancels)) { ?>checked="checked"<?php } ?> />
					<label for="email_admin_cancels"><?php _e('cuando un usuario cancela su cuenta.', 'digital-members-rfid' );?></label>
					<br />
					<input type="checkbox" id="email_admin_billing" name="email_admin_billing" value="1" <?php if(!empty($email_admin_billing)) { ?>checked="checked"<?php } ?> />
					<label for="email_admin_billing"><?php _e('cuando un usuario actualiza su información de facturación.', 'digital-members-rfid' );?></label>
				</td>
			</tr>               
			<tr>
				<th scope="row" valign="top">
					<label for="email_member_notification"><?php _e('Enviar correos electrónicos a los miembros', 'digital-members-rfid' );?>:</label>
				</th>
				<td>
					<input type="checkbox" id="email_member_notification" name="email_member_notification" value="1" <?php if(!empty($email_member_notification)) { ?>checked="checked"<?php } ?> />
					<label for="email_member_notification"><?php _e('Correo electrónico de notificación de WP predeterminado.', 'digital-members-rfid' );?></label>
					<p class="description"><?php _e( 'Recomendado: déjelo sin marcar. Los miembros seguirán recibiendo una confirmación por correo electrónico de DmRFID después del pago.', 'digital-members-rfid' ); ?></p>
				</td>
			</tr>
		</tbody>
		</table>
		
		<p class="submit">            
			<input name="savesettings" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save All Settings', 'digital-members-rfid' ); ?>" />
		</p> 
	</form>

<?php
	require_once(dirname(__FILE__) . "/admin_footer.php");	
?>
