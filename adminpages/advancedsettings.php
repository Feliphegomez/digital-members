<?php
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("dmrfid_advancedsettings")))
	{
		die(__("No tienes permisos para realizar esta acción.", 'digital-members-rfid' ));
	}

	global $wpdb, $msg, $msgt, $allowedposttags;

	//check nonce for saving settings
	if (!empty($_REQUEST['savesettings']) && (empty($_REQUEST['dmrfid_advancedsettings_nonce']) || !check_admin_referer('savesettings', 'dmrfid_advancedsettings_nonce'))) {
		$msg = -1;
		$msgt = __("¿Seguro que quieres hacer eso? Inténtalo de nuevo.", 'digital-members-rfid' );
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
		$msgt = __("Tu configuración avanzada se ha actualizado.", 'digital-members-rfid' );
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
		$nonmembertext = sprintf( __( 'Este contenido es para !! niveles !! solo miembros.<br /><a href="%s">Únete ahora</a>', 'digital-members-rfid' ), "!!levels_page_url!!" );
		dmrfid_setOption("nonmembertext", $nonmembertext);
	}
	if(!$notloggedintext)
	{
		$notloggedintext = sprintf( __( 'Este contenido es para !! niveles !! solo miembros.<br /><a href="%s">Iniciar sesión</a> <a href="%s">Únete ahora</a>', 'digital-members-rfid' ), '!!login_url!!', "!!levels_page_url!!" );
		dmrfid_setOption("notloggedintext", $notloggedintext);
	}
	if(!$rsstext)
	{
		$rsstext = __( 'Este contenido es solo para miembros. Visite el sitio e inicie sesión / regístrese para leer.', 'digital-members-rfid' );
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
		
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Ajustes avanzados', 'digital-members-rfid' ); ?></h1>
		<hr class="wp-header-end">
		<div class="dmrfid_admin_section dmrfid_admin_section-restrict-dashboard">
			<h2 class="title"><?php esc_html_e( 'Restringir el acceso al panel', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="block_dashboard"><?php _e('Panel de WordPress', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<input id="block_dashboard" name="block_dashboard" type="checkbox" value="yes" <?php checked( $block_dashboard, 'yes' ); ?> /> <label for="block_dashboard"><?php _e('Bloquear a todos los usuarios con el rol de suscriptor para que no accedan al panel.', 'digital-members-rfid' );?></label>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="hide_toolbar"><?php _e('Barra de herramientas de WordPress', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<input id="hide_toolbar" name="hide_toolbar" type="checkbox" value="yes" <?php checked( $hide_toolbar, 'yes' ); ?> /> <label for="hide_toolbar"><?php _e('Ocultar la barra de herramientas a todos los usuarios con el rol de suscriptor.', 'digital-members-rfid' );?></label>
					</td>
				</tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-restrict-dashboard -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-message-settings">
			<h2 class="title"><?php esc_html_e( 'Configuración de mensajes', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="nonmembertext"><?php _e('Mensaje para los no miembros registrados', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<textarea name="nonmembertext" rows="3" cols="50" class="large-text"><?php echo stripslashes($nonmembertext)?></textarea>
						<p class="description"><?php _e('Este mensaje reemplaza el contenido de la publicación para los no miembros. Variables disponibles', 'digital-members-rfid' );?>: <code>!!levels!!</code> <code>!!referrer!!</code> <code>!!levels_page_url!!</code></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="notloggedintext"><?php _e('Mensaje para usuarios desconectados', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<textarea name="notloggedintext" rows="3" cols="50" class="large-text"><?php echo stripslashes($notloggedintext)?></textarea>
						<p class="description"><?php _e('Este mensaje reemplaza el contenido de la publicación para los visitantes desconectados.', 'digital-members-rfid' );?> <?php _e('Available variables', 'digital-members-rfid' );?>: <code>!!levels!!</code> <code>!!referrer!!</code> <code>!!login_url!!</code> <code>!!levels_page_url!!</code></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="rsstext"><?php _e('Mensaje para RSS', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<textarea name="rsstext" rows="3" cols="50" class="large-text"><?php echo stripslashes($rsstext)?></textarea>
						<p class="description"><?php _e('Este mensaje reemplaza el contenido de la publicación en las fuentes RSS.', 'digital-members-rfid' );?> <?php _e('Available variables', 'digital-members-rfid' );?>: <code>!!levels!!</code></p>
					</td>
				</tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-message-settings -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-content-settings">
			<h2 class="title"><?php esc_html_e( 'Configuración de contenido', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="filterqueries"><?php _e("¿Filtrar búsquedas y archivos?", 'digital-members-rfid' );?></label>
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
						<label for="showexcerpts"><?php _e('¿Mostrar extractos a los no miembros?', 'digital-members-rfid' );?></label>
	            </th>
	            <td>
	                <select id="showexcerpts" name="showexcerpts">
	                    <option value="0" <?php if(!$showexcerpts) { ?>selected="selected"<?php } ?>><?php _e('No - Ocultar extractos.', 'digital-members-rfid' );?></option>
	                    <option value="1" <?php if($showexcerpts == 1) { ?>selected="selected"<?php } ?>><?php _e('Sí - Mostrar extractos.', 'digital-members-rfid' );?></option>
	                </select>
	            </td>
	            </tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-content-settings -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-checkout-settings">
			<h2 class="title"><?php esc_html_e( 'Configuración de pago', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="tospage"><?php _e('¿Requerir condiciones de servicio en los registros?', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<?php
							wp_dropdown_pages(array("name"=>"tospage", "show_option_none"=>"No", "selected"=>$tospage));
						?>
						<br />
						<p class="description"><?php _e('En caso afirmativo, cree una página de WordPress que contenga su acuerdo de TOS y asígnelo usando el menú desplegable de arriba.', 'digital-members-rfid' );?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="recaptcha"><?php _e('¿Usar reCAPTCHA?', 'digital-members-rfid' );?>:</label>
					</th>
					<td>
						<select id="recaptcha" name="recaptcha" onchange="dmrfid_updateRecaptchaTRs();">
							<option value="0" <?php if(!$recaptcha) { ?>selected="selected"<?php } ?>><?php _e('No', 'digital-members-rfid' );?></option>
							<option value="1" <?php if($recaptcha == 1) { ?>selected="selected"<?php } ?>><?php _e('Si - Free memberships only.', 'digital-members-rfid' );?></option>
							<option value="2" <?php if($recaptcha == 2) { ?>selected="selected"<?php } ?>><?php _e('Si - All memberships.', 'digital-members-rfid' );?></option>
						</select>
						<p class="description"><?php _e('Se requiere una clave reCAPTCHA gratuita.', 'digital-members-rfid' );?> <a href="https://www.google.com/recaptcha/admin/create"><?php _e('Haga clic aquí para registrarse en reCAPTCHA', 'digital-members-rfid' );?></a>.</p>
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
						<p class="description"><?php _e( 'Cambiar su versión requerirá nuevas claves API.', 'digital-members-rfid' ); ?></p>
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
			<h2 class="title"><?php esc_html_e( 'Configuración de comunicación', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Notificaciones', 'digital-members-rfid' ); ?></th>
					<td>
						<select name="maxnotificationpriority">
							<option value="5" <?php selected( $maxnotificationpriority, 5 ); ?>>
								<?php _e( 'Mostrar todas las notificaciones.', 'digital-members-rfid' ); ?>
							</option>
							<option value="1" <?php selected( $maxnotificationpriority, 1 ); ?>>
								<?php _e( 'Mostrar solo notificaciones de seguridad.', 'digital-members-rfid' ); ?>
							</option>
						</select>
						<br />
						<p class="description"><?php _e('Las notificaciones se muestran ocasionalmente en las páginas de configuración de RFID de Digital Members.', 'digital-members-rfid' );?></p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="activity_email_frequency"><?php _e('Frecuencia de correo electrónico de actividad', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<select name="activity_email_frequency">
							<option value="day" <?php selected( $activity_email_frequency, 'day' ); ?>>
								<?php _e( 'Diario', 'digital-members-rfid' ); ?>
							</option>
							<option value="week" <?php selected( $activity_email_frequency, 'week' ); ?>>
								<?php _e( 'Semanal', 'digital-members-rfid' ); ?>
							</option>
							<option value="month" <?php selected( $activity_email_frequency, 'month' ); ?>>
								<?php _e( 'Mensual', 'digital-members-rfid' ); ?>
							</option>
							<option value="never" <?php selected( $activity_email_frequency, 'never' ); ?>>
								<?php _e( 'Nunca', 'digital-members-rfid' ); ?>
							</option>
						</select>
						<br />
						<p class="description"><?php _e( 'Envíe actualizaciones periódicas de ventas e ingresos desde este sitio a la dirección de correo electrónico de administración.', 'digital-members-rfid' );?></p>
					</td>
				</tr>
			</tbody>
			</table>
		</div> <!-- end dmrfid_admin_section-communication-settings -->
		<hr />
		<div class="dmrfid_admin_section dmrfid_admin_section-other-settings">
			<h2 class="title"><?php esc_html_e( 'Otros ajustes', 'digital-members-rfid' ); ?></h2>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="hideads"><?php _e("¿Ocultar anuncios a los miembros?", 'digital-members-rfid' );?></label>
					</th>
					<td>
						<select id="hideads" name="hideads" onchange="dmrfid_updateHideAdsTRs();">
							<option value="0" <?php if(!$hideads) { ?>selected="selected"<?php } ?>><?php _e('No', 'digital-members-rfid' );?></option>
							<option value="1" <?php if($hideads == 1) { ?>selected="selected"<?php } ?>><?php _e('Ocultar anuncios de todos los miembros', 'digital-members-rfid' );?></option>
							<option value="2" <?php if($hideads == 2) { ?>selected="selected"<?php } ?>><?php _e('Ocultar anuncios de ciertos miembros', 'digital-members-rfid' );?></option>
						</select>
					</td>
				</tr>
				<tr id="hideads_explanation" <?php if($hideads < 2) { ?>style="display: none;"<?php } ?>>
					<th scope="row" valign="top">&nbsp;</th>
					<td>
						<p><?php _e('Para ocultar anuncios en el código de su plantilla, use un código como el siguiente', 'digital-members-rfid' );?>:</p>
						<pre lang="PHP">
							if ( function_exists( 'dmrfid_displayAds' ) && dmrfid_displayAds() ) {
								//insert ad code here
							}
						</pre>
					</td>
				</tr>			
				<tr id="hideadslevels_tr" <?php if($hideads != 2) { ?>style="display: none;"<?php } ?>>
					<th scope="row" valign="top">
						<label for="hideadslevels"><?php _e('Elija niveles de los que ocultar anuncios', 'digital-members-rfid' );?>:</label>
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
						<label for="redirecttosubscription"><?php _e('Redirigir todo el tráfico de la página de registro a /susbcription/?', 'digital-members-rfid' );?>: <em>(<?php _e('multisite only', 'digital-members-rfid' );?>)</em></label>
					</th>
					<td>
						<select id="redirecttosubscription" name="redirecttosubscription">
							<option value="0" <?php if(!$redirecttosubscription) { ?>selected="selected"<?php } ?>><?php _e('No', 'digital-members-rfid' );?></option>
							<option value="1" <?php if($redirecttosubscription == 1) { ?>selected="selected"<?php } ?>><?php _e('Si', 'digital-members-rfid' );?></option>
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
						<label for="uninstall"><?php _e('¿Desinstalar DmRFID al eliminarlo?', 'digital-members-rfid' );?></label>
					</th>
					<td>
						<select id="uninstall" name="uninstall">
							<option value="0" <?php if ( ! $uninstall ) { ?>selected="selected"<?php } ?>><?php _e( 'No', 'digital-members-rfid' );?></option>
							<option value="1" <?php if ( $uninstall == 1 ) { ?>selected="selected"<?php } ?>><?php _e( 'Si - Delete all DmRFID Data.', 'digital-members-rfid' );?></option>
						</select>
						<p class="description"><?php esc_html_e( 'Para eliminar todos los datos DmRFID de la base de datos, establezca Sí, desactive DmRFID y luego haga clic para eliminar DmRFID de la página de complementos.' ); ?></p>
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
			<input name="savesettings" type="submit" class="button button-primary" value="<?php _e('Guardar ajustes', 'digital-members-rfid' );?>" />
		</p>
	</form>

<?php
	require_once(dirname(__FILE__) . "/admin_footer.php");
?>
