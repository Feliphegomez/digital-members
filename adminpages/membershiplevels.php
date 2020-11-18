<?php
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("dmrfid_membershiplevels")))
	{
		die(__("No tienes permisos para realizar esta acción.", 'digital-members-rfid' ));
	}

	global $wpdb, $msg, $msgt, $dmrfid_currency_symbol, $allowedposttags;

	//some vars
	$gateway = dmrfid_getOption("gateway");
    $dmrfid_level_order = dmrfid_getOption('level_order');

	global $dmrfid_stripe_error, $dmrfid_braintree_error, $dmrfid_payflow_error, $dmrfid_twocheckout_error, $wp_version;

	if(isset($_REQUEST['edit']))
		$edit = intval($_REQUEST['edit']);
	else
		$edit = false;
	if(isset($_REQUEST['copy']))
		$copy = intval($_REQUEST['copy']);
	if(isset($_REQUEST['s']))
		$s = sanitize_text_field($_REQUEST['s']);
	else
		$s = "";

	if(isset($_REQUEST['action']))
		$action = sanitize_text_field($_REQUEST['action']);
	else
		$action = false;

	if(isset($_REQUEST['saveandnext']))
		$saveandnext = intval($_REQUEST['saveandnext']);

	if(isset($_REQUEST['saveid']))
		$saveid = intval($_REQUEST['saveid']);
	if(isset($_REQUEST['deleteid']))
		$deleteid = intval($_REQUEST['deleteid']);

	//check nonce
	if(!empty($action) && (empty($_REQUEST['dmrfid_membershiplevels_nonce']) || !check_admin_referer($action, 'dmrfid_membershiplevels_nonce'))) {
		$msg = -1;
		$msgt = __("¿Seguro que quieres hacer eso? Inténtalo de nuevo.", 'digital-members-rfid' );
		$action = false;
	}

	if($action == "save_membershiplevel") {

		$ml_name = wp_kses(wp_unslash($_REQUEST['name']), $allowedposttags);
		$ml_description = wp_kses(wp_unslash($_REQUEST['description']), $allowedposttags);
		$ml_confirmation = wp_kses(wp_unslash($_REQUEST['confirmation']), $allowedposttags);
		if(!empty($_REQUEST['confirmation_in_email']))
			$ml_confirmation_in_email = 1;
		else
			$ml_confirmation_in_email = 0;

		$ml_initial_payment = sanitize_text_field($_REQUEST['initial_payment']);
		if(!empty($_REQUEST['recurring']))
			$ml_recurring = 1;
		else
			$ml_recurring = 0;
		$ml_billing_amount = sanitize_text_field($_REQUEST['billing_amount']);
		$ml_cycle_number = intval($_REQUEST['cycle_number']);
		$ml_cycle_period = sanitize_text_field($_REQUEST['cycle_period']);
		$ml_billing_limit = intval($_REQUEST['billing_limit']);
		if(!empty($_REQUEST['custom_trial']))
			$ml_custom_trial = 1;
		else
			$ml_custom_trial = 0;
		$ml_trial_amount = sanitize_text_field($_REQUEST['trial_amount']);
		$ml_trial_limit = intval($_REQUEST['trial_limit']);
		if(!empty($_REQUEST['expiration']))
			$ml_expiration = 1;
		else
			$ml_expiration = 0;
		$ml_expiration_number = intval($_REQUEST['expiration_number']);
		$ml_expiration_period = sanitize_text_field($_REQUEST['expiration_period']);
		$ml_categories = array();

		//reversing disable to allow here
		if(empty($_REQUEST['disable_signups']))
			$ml_allow_signups = 1;
		else
			$ml_allow_signups = 0;

		foreach ( $_REQUEST as $key => $value ) {
			if ( $value == 'yes' && preg_match( '/^membershipcategory_(\d+)$/i', $key, $matches ) ) {
				$ml_categories[] = $matches[1];
			}
		}

		//clearing out values if checkboxes aren't checked
		if(empty($ml_recurring)) {
			$ml_billing_amount = $ml_cycle_number = $ml_cycle_period = $ml_billing_limit = $ml_trial_amount = $ml_trial_limit = 0;
		} elseif(empty($ml_custom_trial)) {
			$ml_trial_amount = $ml_trial_limit = 0;
		}
		if(empty($ml_expiration)) {
			$ml_expiration_number = $ml_expiration_period = 0;
		}

		dmrfid_insert_or_replace(
			$wpdb->dmrfid_membership_levels,
			array(
				'id'=>max($saveid, 0),
				'name' => $ml_name,
				'description' => $ml_description,
				'confirmation' => $ml_confirmation,
				'initial_payment' => $ml_initial_payment,
				'billing_amount' => $ml_billing_amount,
				'cycle_number' => $ml_cycle_number,
				'cycle_period' => $ml_cycle_period,
				'billing_limit' => $ml_billing_limit,
				'trial_amount' => $ml_trial_amount,
				'trial_limit' => $ml_trial_limit,
				'expiration_number' => $ml_expiration_number,
				'expiration_period' => $ml_expiration_period,
				'allow_signups' => $ml_allow_signups
			),
			array(
				'%d',		//id
				'%s',		//name
				'%s',		//description
				'%s',		//confirmation
				'%f',		//initial_payment
				'%f',		//billing_amount
				'%d',		//cycle_number
				'%s',		//cycle_period
				'%d',		//billing_limit
				'%f',		//trial_amount
				'%d',		//trial_limit
				'%d',		//expiration_number
				'%s',		//expiration_period
				'%d',		//allow_signups
			)
		);
				
		if($saveid < 1) {
			//added a level
			$saveid = $wpdb->insert_id;

			dmrfid_updateMembershipCategories( $saveid, $ml_categories );

			if(empty($wpdb->last_error)) {
				$saveid = $wpdb->insert_id;
				dmrfid_updateMembershipCategories( $saveid, $ml_categories );

				$edit = false;
				$msg = 1;
				$msgt = __("Nivel de membresía agregado correctamente.", 'digital-members-rfid' );
			} else {
				$msg = -1;
				$msgt = __("Error al agregar el nivel de membresía.", 'digital-members-rfid' );
			}
		} else {
			dmrfid_updateMembershipCategories( $saveid, $ml_categories );

			if(empty($wpdb->last_error)) {
				$edit = false;
				$msg = 2;
				$msgt = __("Nivel de membresía actualizado correctamente.", 'digital-members-rfid' );
			} else {
				$msg = -2;
				$msgt = __("Error al actualizar el nivel de membresía.", 'digital-members-rfid' );
			}
		}
		
		if( ! empty( $msgt ) && $ml_recurring && $ml_expiration ) {
			$msgt .= ' <strong class="red">' . sprintf( __( 'ADVERTENCIA: Se estableció un nivel con un monto de facturación recurrente y una fecha de vencimiento. Solo necesita configurar uno de estos a menos que realmente desee que esta membresía caduque después de un período de tiempo específico. Para obtener más información, <a target="_blank" href="%s"> consulte nuestra publicación aquí </a>.', 'digital-members-rfid' ), 'https://www.managertechnology.com.co/important-notes-on-recurring-billing-and-expiration-dates-for-membership-levels/?utm_source=plugin&utm_medium=dmrfid-membershiplevels&utm_campaign=blog&utm_content=important-notes-on-recurring-billing-and-expiration-dates-for-membership-levels' ) . '</strong>';
				
			// turn success to errors
			if( $msg > 0 ) {
				$msg = 0 - $msg;
			}
		}

		// Update the Level Meta to Add Confirmation Message to Email.
		if ( isset( $ml_confirmation_in_email ) ) {
			update_dmrfid_membership_level_meta( $saveid, 'confirmation_in_email', $ml_confirmation_in_email );
		}
		
		do_action("dmrfid_save_membership_level", $saveid);
	}
	elseif($action == "delete_membership_level")
	{
		global $wpdb;

		$ml_id = intval($_REQUEST['deleteid']);

		if($ml_id > 0) {
			do_action("dmrfid_delete_membership_level", $ml_id);

			//remove any categories from the ml
			$sqlQuery = $wpdb->prepare("
				DELETE FROM $wpdb->dmrfid_memberships_categories
				WHERE membership_id = %d",
				$ml_id
			);

			$r1 = $wpdb->query($sqlQuery);

			//cancel any subscriptions to the ml
			$r2 = true;
			$user_ids = $wpdb->get_col( $wpdb->prepare( "
				SELECT user_id FROM $wpdb->dmrfid_memberships_users
				WHERE membership_id = %d
				AND status = 'active'",
			 	$ml_id
			) );

			foreach($user_ids as $user_id) {
				//change there membership level to none. that will handle the cancel
				if(dmrfid_changeMembershipLevel(0, $user_id)) {
					//okay
				} else {
					//couldn't delete the subscription
					//we should probably notify the admin
					$dmrfidemail = new DmRFIDEmail();
					$dmrfidemail->data = array("body"=>"<p>" . sprintf(__("Se produjo un error al cancelar la suscripción para el usuario con ID=%d. Querrá verificar su pasarela de pago para ver si su suscripción aún está activa.", 'digital-members-rfid' ), $user_id) . "</p>");
					$last_order = $wpdb->get_row( $wpdb->prepare( "
						SELECT * FROM $wpdb->dmrfid_membership_orders
						WHERE user_id = %d
						ORDER BY timestamp DESC LIMIT 1",
						$user_id
					) );
					if($last_order)
						$dmrfidemail->data["body"] .= "<p>" . __("Última factura", 'digital-members-rfid' ) . ":<br />" . nl2br(var_export($last_order, true)) . "</p>";
					$dmrfidemail->sendEmail(get_bloginfo("admin_email"));

					$r2 = false;
				}
			}

			//delete the ml
			$sqlQuery = $wpdb->prepare( "
				DELETE FROM $wpdb->dmrfid_membership_levels
				WHERE id = %d LIMIT 1",
				$ml_id
			);
			$r3 = $wpdb->query($sqlQuery);

			if($r1 !== FALSE && $r2 !== FALSE && $r3 !== FALSE) {
				$msg = 3;
				$msgt = __("Nivel de membresía eliminado correctamente.", 'digital-members-rfid' );
			} else {
				$msg = -3;
				$msgt = __("Error al eliminar el nivel de membresía.", 'digital-members-rfid' );
			}
		}
		else {
			$msg = -3;
			$msgt = __("Error al eliminar el nivel de membresía.", 'digital-members-rfid' );
		}
	}

	require_once(dirname(__FILE__) . "/admin_header.php");
?>

<?php
	if($edit) {
	?>

	<h1 class="wp-heading-inline">
		<?php
			if($edit > 0)
				echo __("Editar nivel de membresía", 'digital-members-rfid' );
			else
				echo __("Agregar nuevo nivel de membresía", 'digital-members-rfid' );
		?>
	</h1>
	<hr class="wp-header-end">
	
	<div>
		<?php
			// get the level...
			if(!empty($edit) && $edit > 0) {
				$level = $wpdb->get_row( $wpdb->prepare( "
					SELECT * FROM $wpdb->dmrfid_membership_levels
					WHERE id = %d LIMIT 1",
					$edit
				),
					OBJECT
				);
				$temp_id = $level->id;
			} elseif(!empty($copy) && $copy > 0) {
				$level = $wpdb->get_row( $wpdb->prepare( "
					SELECT * FROM $wpdb->dmrfid_membership_levels
					WHERE id = %d LIMIT 1",
					$copy
				),
					OBJECT
				);
				$temp_id = $level->id;
				$level->id = NULL;
			}
			else

			// didn't find a membership level, let's add a new one...
			if(empty($level)) {
				$level = new stdClass();
				$level->id = NULL;
				$level->name = NULL;
				$level->description = NULL;
				$level->confirmation = NULL;
				$level->billing_amount = NULL;
				$level->trial_amount = NULL;
				$level->initial_payment = NULL;
				$level->billing_limit = NULL;
				$level->trial_limit = NULL;
				$level->expiration_number = NULL;
				$level->expiration_period = NULL;
				$edit = -1;
			}

			//defaults for new levels
			if(empty($copy) && $edit == -1) {
				$level->cycle_number = 1;
				$level->cycle_period = "Month";
			}

			// grab the categories for the given level...
			if(!empty($temp_id))
				$level->categories = $wpdb->get_col( $wpdb->prepare( "
					SELECT c.category_id
					FROM $wpdb->dmrfid_memberships_categories c
					WHERE c.membership_id = %d",
					$temp_id
				) );
			if(empty($level->categories))
				$level->categories = array();
			
			// grab the meta for the given level...
			if ( ! empty( $temp_id ) ) {
				$confirmation_in_email = get_dmrfid_membership_level_meta( $temp_id, 'confirmation_in_email', true );
			} else {
				$confirmation_in_email = 0;
			}

		?>
		<form action="" method="post" enctype="multipart/form-data">
			<input name="saveid" type="hidden" value="<?php echo esc_attr($edit); ?>" />
			<input type="hidden" name="action" value="save_membershiplevel" />
			<?php wp_nonce_field('save_membershiplevel', 'dmrfid_membershiplevels_nonce'); ?>
			<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e('ID', 'digital-members-rfid' );?>:</label></th>
					<td>
						<?php echo $level->id?>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top"><label for="name"><?php _e('Nombre', 'digital-members-rfid' );?>:</label></th>
					<td><input name="name" type="text" value="<?php echo esc_attr($level->name);?>" class="regular-text" /></td>
				</tr>

				<tr>
					<th scope="row" valign="top"><label for="description"><?php _e('Descripcion', 'digital-members-rfid' );?>:</label></th>
					<td>
						<div id="poststuff" class="dmrfid_description">
						<?php
							if(version_compare($wp_version, "3.3") >= 0)
								wp_editor($level->description, "description", array("textarea_rows"=>5));
							else
							{
							?>
							<textarea rows="10" name="description" id="description" class="large-text"><?php echo esc_textarea($level->description);?></textarea>
							<?php
							}
						?>
						</div>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top"><label for="confirmation"><?php _e('Mensaje de confirmacion', 'digital-members-rfid' );?>:</label></th>
					<td>
						<div class="dmrfid_confirmation">
						<?php
							if(version_compare($wp_version, "3.3") >= 0)
								wp_editor($level->confirmation, "confirmation", array("textarea_rows"=>5));
							else
							{
							?>
							<textarea rows="10" name="confirmation" id="confirmation" class="large-text"><?php echo esc_textarea($level->confirmation);?></textarea>
							<?php
							}
						?>
						</div>
						<input id="confirmation_in_email" name="confirmation_in_email" type="checkbox" value="yes" <?php checked( $confirmation_in_email, 1); ?> /> <label for="confirmation_in_email"><?php _e('Marque para incluir este mensaje en el correo electrónico de confirmación de membresía.', 'digital-members-rfid' );?></label>
					</td>
				</tr>
			</tbody>
		</table>
		<hr />
		<h2 class="title"><?php _e('Detalles de facturación', 'digital-members-rfid' );?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label for="initial_payment"><?php _e('Pago inicial', 'digital-members-rfid' );?>:</label></th>
					<td>
						<?php
						if(dmrfid_getCurrencyPosition() == "left")
							echo $dmrfid_currency_symbol;
						?>
						<input name="initial_payment" type="text" value="<?php echo esc_attr( dmrfid_filter_price_for_text_field( $level->initial_payment ) );?>" class="regular-text" />
						<?php
						if(dmrfid_getCurrencyPosition() == "right")
							echo $dmrfid_currency_symbol;
						?>
						<p class="description"><?php _e('La cantidad inicial cobrada en el registro.', 'digital-members-rfid' );?></p>
					</td>
				</tr>

				<tr>
					<th scope="row" valign="top"><label><?php _e('Subscripción recurrente', 'digital-members-rfid' );?>:</label></th>
					<td><input id="recurring" name="recurring" type="checkbox" value="yes" <?php if(dmrfid_isLevelRecurring($level)) { echo "checked='checked'"; } ?> onclick="if(jQuery('#recurring').is(':checked')) { jQuery('.recurring_info').show(); if(jQuery('#custom_trial').is(':checked')) {jQuery('.trial_info').show();} else {jQuery('.trial_info').hide();} } else { jQuery('.recurring_info').hide();}" /> <label for="recurring"><?php _e('Verifique si este nivel tiene un pago de suscripción recurrente.', 'digital-members-rfid' );?></label></td>
				</tr>

				<tr class="recurring_info" <?php if(!dmrfid_isLevelRecurring($level)) {?>style="display: none;"<?php } ?>>
					<th scope="row" valign="top"><label for="billing_amount"><?php _e('Importe de facturación', 'digital-members-rfid' );?>:</label></th>
					<td>
						<?php
						if(dmrfid_getCurrencyPosition() == "left")
							echo $dmrfid_currency_symbol;
						?>
						<input name="billing_amount" type="text" value="<?php echo esc_attr( dmrfid_filter_price_for_text_field( $level->billing_amount ) );?>"  class="regular-text" />
						<?php
						if(dmrfid_getCurrencyPosition() == "right")
							echo $dmrfid_currency_symbol;
						?>
						<?php _e('per', 'digital-members-rfid' );?>
						<input id="cycle_number" name="cycle_number" type="text" value="<?php echo esc_attr($level->cycle_number);?>" class="small-text" />
						<select id="cycle_period" name="cycle_period">
						  <?php
							$cycles = array( __('Dia(s)', 'digital-members-rfid' ) => 'Day', __('Semana(s)', 'digital-members-rfid' ) => 'Week', __('Mes(es)', 'digital-members-rfid' ) => 'Month', __('Año(s)', 'digital-members-rfid' ) => 'Year' );
							foreach ( $cycles as $name => $value ) {
							  echo "<option value='$value'";
							  if ( $level->cycle_period == $value ) echo " selected='selected'";
							  echo ">$name</option>";
							}
						  ?>
						</select>
						<p class="description">
							<?php _e('La cantidad que se facturará un ciclo después del pago inicial.', 'digital-members-rfid' );?>
							<?php if($gateway == "braintree") { ?>
								<strong <?php if(!empty($dmrfid_braintree_error)) { ?>class="dmrfid_red"<?php } ?>><?php _e('La integración de Braintree actualmente solo admite períodos de facturación de "Mes" o "Año".', 'digital-members-rfid' );?></strong>
							<?php } elseif($gateway == "stripe") { ?>
								<p class="description"><strong <?php if(!empty($dmrfid_stripe_error)) { ?>class="dmrfid_red"<?php } ?>><?php _e('La integración de Stripe no permite períodos de facturación superiores a 1 año.', 'digital-members-rfid' );?></strong></p>
							<?php }?>
						</p>
						<?php if($gateway == "braintree" && $edit < 0) { ?>
							<p class="dmrfid_message"><strong><?php _e('Note', 'digital-members-rfid' );?>:</strong> <?php _e('Después de guardar este nivel, tome nota del ID y cree un "Plan" en su panel de Braintree con la misma configuración y el "Plan ID" configurado en <em> dmrfid_ # </em>, donde # es el ID del nivel.', 'digital-members-rfid' );?></p>
						<?php } elseif($gateway == "braintree") {
						    $has_bt_plan = DmRFIDGateway_braintree::checkLevelForPlan( $level->id );
							?>
							<p class="dmrfid_message <?php if ( ! $has_bt_plan ) {?>dmrfid_error<?php } ?>">
                                <strong><?php _e('Notas: ', 'digital-members-rfid' );?>:</strong> <?php printf( __('Deberá crear un "Plan" en su panel de Braintree con la misma configuración y el "ID del plan" establecido en %s.', 'digital-members-rfid' ), DmRFIDGateway_braintree::get_plan_id( $level->id ) ); ?></p>
						<?php } ?>
					</td>
				</tr>

				<tr class="recurring_info" <?php if(!dmrfid_isLevelRecurring($level)) {?>style="display: none;"<?php } ?>>
					<th scope="row" valign="top"><label for="billing_limit"><?php _e('Límite del ciclo de facturación', 'digital-members-rfid' );?>:</label></th>
					<td>
						<input name="billing_limit" type="text" value="<?php echo $level->billing_limit?>" class="small-text" />
						<p class="description">
							<?php _e('La cantidad <strong> total </strong> de ciclos de facturación recurrentes para este nivel, incluido el período de prueba (si corresponde), pero sin incluir el pago inicial. Establecer en cero si la membresía es indefinida.', 'digital-members-rfid' );?>
							<?php if ( ( $gateway == "stripe" ) && ! function_exists( 'dmrfidsbl_plugin_row_meta' ) ) { ?>
								<br /><strong <?php if(!empty($dmrfid_stripe_error)) { ?>class="dmrfid_red"<?php } ?>><?php _e('Actualmente, la integración de Stripe no admite límites de facturación. Aún puede establecer una fecha de vencimiento a continuación.', 'digital-members-rfid' );?></strong>
								<?php if ( ! function_exists( 'dmrfidsd_dmrfid_membership_level_after_other_settings' ) ) {
										$allowed_sbl_html = array (
											'a' => array (
												'href' => array(),
												'target' => array(),
												'title' => array(),
											),
										);
										echo '<br />' . sprintf( wp_kses( __( 'Opcional: Permita límites de facturación con Stripe mediante el <a href="%s" title="Agregar límites de facturación de Stripe RFID para miembros digitales" target="_blank"> Complemento de límites de facturación de Stripe </a>.', 'digital-members-rfid' ), $allowed_sbl_html ), 'https://www.managertechnology.com.co/add-ons/dmrfid-stripe-billing-limits/?utm_source=plugin&utm_medium=dmrfid-membershiplevels&utm_campaign=add-ons&utm_content=stripe-billing-limits' ) . '</em></td></tr>';
								} ?>
							<?php } ?>
						</p>
					</td>
				</tr>

				<tr class="recurring_info" <?php if (!dmrfid_isLevelRecurring($level)) echo "style='display:none;'";?>>
					<th scope="row" valign="top"><label><?php _e('Prueba personalizada', 'digital-members-rfid' );?>:</label></th>
					<td>
						<input id="custom_trial" name="custom_trial" type="checkbox" value="yes" <?php if ( dmrfid_isLevelTrial($level) ) { echo "checked='checked'"; } ?> onclick="jQuery('.trial_info').toggle();" /> <label for="custom_trial"><?php _e('Marque para agregar un período de prueba personalizado.', 'digital-members-rfid' );?></label>

						<?php if($gateway == "twocheckout") { ?>
							<p class="description"><strong <?php if(!empty($dmrfid_twocheckout_error)) { ?>class="dmrfid_red"<?php } ?>><?php _e('2Checkout integration does not support custom trials. You can do one period trials by setting an initial payment different from the billing amount.', 'digital-members-rfid' );?></strong></p>
						<?php } ?>
					</td>
				</tr>

				<?php if ( ! function_exists( 'dmrfidsd_dmrfid_membership_level_after_other_settings' ) ) {
						$allowed_sd_html = array (
							'a' => array (
								'href' => array(),
								'target' => array(),
								'title' => array(),
							),
						);
						echo '<tr><th>&nbsp;</th><td><p class="description">' . sprintf( wp_kses( __( 'Opcional: Permita períodos de prueba y fechas de renovación más personalizables mediante el <a href="%s" title="Retrasos de suscripción RFID de miembros digitales complementarios" target="_blank"> Retrasos de suscripción complementarios </a>.', 'digital-members-rfid' ), $allowed_sd_html ), 'https://www.managertechnology.com.co/add-ons/subscription-delays/?utm_source=plugin&utm_medium=dmrfid-membershiplevels&utm_campaign=add-ons&utm_content=subscription-delays' ) . '</p></td></tr>';
				} ?>

				<tr class="trial_info recurring_info" <?php if (!dmrfid_isLevelTrial($level)) echo "style='display:none;'";?>>
					<th scope="row" valign="top"><label for="trial_amount"><?php _e('Monto de facturación de prueba', 'digital-members-rfid' );?>:</label></th>
					<td>
						<?php
						if(dmrfid_getCurrencyPosition() == "left")
							echo $dmrfid_currency_symbol;
						?>
						<input name="trial_amount" type="text" value="<?php echo esc_attr( dmrfid_filter_price_for_text_field( $level->trial_amount ) );?>" class="regular-text" />
						<?php
						if(dmrfid_getCurrencyPosition() == "right")
							echo $dmrfid_currency_symbol;
						?>
						<?php _e('Por el primero', 'digital-members-rfid' );?>
						<input name="trial_limit" type="text" value="<?php echo esc_attr($level->trial_limit);?>" class="small-text" />
						<?php _e('pagos de suscripción', 'digital-members-rfid' );?>.
						<?php if($gateway == "stripe") { ?>
							<p class="description"><strong <?php if(!empty($dmrfid_stripe_error)) { ?>class="dmrfid_red"<?php } ?>><?php _e('Actualmente, la integración de Stripe no admite montos de prueba superiores a $0.', 'digital-members-rfid' );?></strong></p>
						<?php } elseif($gateway == "braintree") { ?>
							<p class="description"><strong <?php if(!empty($dmrfid_braintree_error)) { ?>class="dmrfid_red"<?php } ?>><?php _e('Actualmente, la integración de Braintree no admite montos de prueba superiores a $0.', 'digital-members-rfid' );?></strong></p>
						<?php } elseif($gateway == "payflowpro") { ?>
							<p class="description"><strong <?php if(!empty($dmrfid_payflow_error)) { ?>class="dmrfid_red"<?php } ?>><?php _e('Actualmente, la integración de flujo de pago no admite montos de prueba superiores a $0.', 'digital-members-rfid' );?></strong></p>
						<?php } ?>
					</td>
				</tr>

			</tbody>
		</table>
		<hr />
		<h2 class="title"><?php esc_html_e( 'Otros ajustes', 'digital-members-rfid' ); ?></h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e('Deshabilitar nuevos registros', 'digital-members-rfid' );?>:</label></th>
					<td><input id="disable_signups" name="disable_signups" type="checkbox" value="yes" <?php if($level->id && !$level->allow_signups) { ?>checked="checked"<?php } ?> /> <label for="disable_signups"><?php _e('Marque para ocultar este nivel de la página de niveles de membresía y deshabilite el registro.', 'digital-members-rfid' );?></label></td>
				</tr>

				<tr>
					<th scope="row" valign="top"><label><?php _e('Caducidad de la membresía', 'digital-members-rfid' );?>:</label></th>
					<td><input id="expiration" name="expiration" type="checkbox" value="yes" <?php if(dmrfid_isLevelExpiring($level)) { echo "checked='checked'"; } ?> onclick="if(jQuery('#expiration').is(':checked')) { jQuery('.expiration_info').show(); } else { jQuery('.expiration_info').hide();}" /> <label for="expiration"><?php _e('Marque esto para establecer cuándo expira el acceso a la membresía.', 'digital-members-rfid' );?></label></a></td>
				</tr>

				<?php if ( ! function_exists( 'dmrfidsed_dmrfid_membership_level_after_other_settings' ) ) {
						$allowed_sed_html = array (
							'a' => array (
								'href' => array(),
								'target' => array(),
								'title' => array(),
							),
						);
						echo '<tr><th>&nbsp;</th><td><p class="description">' . sprintf( wp_kses( __( 'Opcional: Permita fechas de vencimiento más personalizables usando el <a href="%s" title="Digital Members RFID-Set Expiration Date Add On" target="_blank"> Establecer fecha de vencimiento adicional </a>.', 'digital-members-rfid' ), $allowed_sed_html ), 'https://www.managertechnology.com.co/add-ons/dmrfid-expiration-date/?utm_source=plugin&utm_medium=dmrfid-membershiplevels&utm_campaign=add-ons&utm_content=dmrfid-expiration-date' ) . '</p></td></tr>';
				} ?>

				<tr class="expiration_info" <?php if(!dmrfid_isLevelExpiring($level)) {?>style="display: none;"<?php } ?>>					
					<th scope="row" valign="top"><label for="billing_amount"><?php _e('Expira en', 'digital-members-rfid' );?>:</label></th>
					<td>
						<input id="expiration_number" name="expiration_number" type="text" value="<?php echo esc_attr($level->expiration_number);?>" class="small-text" />
						<select id="expiration_period" name="expiration_period">
						  <?php
							$cycles = array( __('Día(s)', 'digital-members-rfid' ) => 'Day', __('Semana(s)', 'digital-members-rfid' ) => 'Week', __('Mes(es)', 'digital-members-rfid' ) => 'Month', __('Año(s)', 'digital-members-rfid' ) => 'Year' );
							foreach ( $cycles as $name => $value ) {
							  echo "<option value='$value'";
							  if ( $level->expiration_period == $value ) echo " selected='selected'";
							  echo ">$name</option>";
							}
						  ?>
						</select>
						<p class="description"><?php _e('Establezca la duración del acceso a la membresía. Tenga en cuenta que los pagos futuros (suscripción recurrente, si corresponde) se cancelarán cuando expire la membresía.', 'digital-members-rfid' );?></p>
						
						<div id="dmrfid_expiration_warning" style="display: none;" class="notice error inline">
							<p><?php printf( __( 'ADVERTENCIA: Este nivel se establece con un monto de facturación recurrente y una fecha de vencimiento. Solo necesita configurar uno de estos a menos que realmente desee que esta membresía caduque después de una cierta cantidad de pagos. Para obtener más información, <a target="_blank" href="%s"> consulte nuestra publicación aquí </a>.', 'digital-members-rfid' ), 'https://www.managertechnology.com.co/important-notes-on-recurring-billing-and-expiration-dates-for-membership-levels/?utm_source=plugin&utm_medium=dmrfid-membershiplevels&utm_campaign=blog&utm_content=important-notes-on-recurring-billing-and-expiration-dates-for-membership-levels' ); ?></p>
						</div>
						<script>
							jQuery(document).ready(function() {
								function dmrfid_expirationWarningCheck() {
									if( jQuery('#recurring:checked').length && jQuery('#expiration:checked').length) {
										jQuery('#dmrfid_expiration_warning').show();
									} else {
										jQuery('#dmrfid_expiration_warning').hide();
									}
								}
								
								dmrfid_expirationWarningCheck();
								
								jQuery('#recurring,#expiration').change(function() { dmrfid_expirationWarningCheck(); });
							});
						</script>
					</td>
				</tr>
			</tbody>
		</table>

		<?php do_action("dmrfid_membership_level_after_other_settings"); ?>

		<hr />

		<h2 class="title"><?php esc_html_e( 'Configuración de contenido', 'digital-members-rfid' ); ?></h2>
		<?php
			// Get the Advanced Settings for filtering queries and showing excerpts.
			$filterqueries = dmrfid_getOption('filterqueries');
			$showexcerpts = dmrfid_getOption("showexcerpts");

			$allowed_html = array (
				'a' => array (
					'href' => array(),
					'target' => array(),
					'title' => array(),
				),
			);

			if ( $filterqueries == 1 ) {
				// Show a message that posts in these categories are hidden.
				echo '<p>' . sprintf( wp_kses( __( 'Los no miembros no verán publicaciones en estas categorías. Puede <a href="%s" title="Advanced Settings" target="_blank"> actualizar esta configuración aquí </a>.', 'digital-members-rfid' ), $allowed_html ), admin_url( 'admin.php?page=dmrfid-advancedsettings' ) ) . '</p>';
			} else {
				if ( $showexcerpts == 1 ) {
					// Show a message that posts in these categories will show title and excerpt.
					echo '<p>' . sprintf( wp_kses( __( 'Los no miembros verán el título y el extracto de las publicaciones en estas categorías. Puede <a href="%s" title="Advanced Settings" target="_blank"> actualizar esta configuración aquí </a>.', 'digital-members-rfid' ), $allowed_html ), admin_url( 'admin.php?page=dmrfid-advancedsettings' ) ) . '</p>';
				} else {
					// Show a message that posts in these categories will show only the title.
					echo '<p>' . sprintf( wp_kses( __( 'Los no miembros verán el título solo para las publicaciones en estas categorías. Puede <a href="%s" title="Advanced Settings" target="_blank"> actualizar esta configuración aquí </a>.', 'digital-members-rfid' ), $allowed_html ), admin_url( 'admin.php?page=dmrfid-advancedsettings' ) ) . '</p>';
				}
			}
		?>
		<table class="form-table">
			<tbody>
				<tr class="membership_categories">
					<th scope="row" valign="top"><label><?php _e('Categorías', 'digital-members-rfid' );?>:</label></th>
					<td>
						<?php dmrfid_listCategories(0, $level->categories); ?>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit topborder">
			<input name="save" type="submit" class="button button-primary" value="<?php _e('Guardar nivel', 'digital-members-rfid' ); ?>" />
			<input name="cancel" type="button" class="button" value="<?php _e('Cancelar', 'digital-members-rfid' ); ?>" onclick="location.href='<?php echo add_query_arg( 'page', 'dmrfid-membershiplevels' , get_admin_url(NULL, '/admin.php') ); ?>';" />
		</p>
	</form>
	</div>

	<?php
	}
	else
	{
		$sqlQuery = "SELECT * FROM $wpdb->dmrfid_membership_levels ";
		if($s)
			$sqlQuery .= "WHERE name LIKE '%$s%' ";
			$sqlQuery .= "ORDER BY id ASC";

			$levels = $wpdb->get_results($sqlQuery, OBJECT);

        if(empty($_REQUEST['s']) && !empty($dmrfid_level_order)) {
            //reorder levels
            $order = explode(',', $dmrfid_level_order);

			//put level ids in their own array
			$level_ids = array();
			foreach($levels as $level)
				$level_ids[] = $level->id;

			//remove levels from order if they are gone
			foreach($order as $key => $level_id)
				if(!in_array($level_id, $level_ids))
					unset($order[$key]);

			//add levels to the end if they aren't in the order array
			foreach($level_ids as $level_id)
				if(!in_array($level_id, $order))
					$order[] = $level_id;

			//remove dupes
			$order = array_unique($order);

			//save the level order
			dmrfid_setOption('level_order', implode(',', $order));

			//reorder levels here
            $reordered_levels = array();
            foreach ($order as $level_id) {
                foreach ($levels as $level) {
                    if ($level_id == $level->id)
                        $reordered_levels[] = $level;
                }
            }
        }
		else
			$reordered_levels = $levels;

		if(empty($_REQUEST['s']) && count($reordered_levels) > 1)
		{
			?>
		    <script>
		        jQuery(document).ready(function($) {

		            // Return a helper with preserved width of cells
		            // from http://www.foliotek.com/devblog/make-table-rows-sortable-using-jquery-ui-sortable/
		            var fixHelper = function(e, ui) {
		                ui.children().each(function() {
		                    $(this).width($(this).width());
		                });
		                return ui;
		            };

		            $("table.membership-levels tbody").sortable({
		                helper: fixHelper,
		                placeholder: 'testclass',
		                forcePlaceholderSize: true,
		                update: update_level_order
		            });

		            function update_level_order(event, ui) {
		                level_order = [];
		                $("table.membership-levels tbody tr").each(function() {
		                    $(this).removeClass('alternate');
		                    level_order.push(parseInt( $("td:first", this).text()));
		                });

		                //update styles
		                $("table.membership-levels tbody tr:odd").each(function() {
		                    $(this).addClass('alternate');
		                });

		                data = {
		                    action: 'dmrfid_update_level_order',
		                    level_order: level_order
		                };

		                $.post(ajaxurl, data, function(response) {
		                });
		            }
		        });
		    </script>
			<?php
			}
		?>

		<?php if( empty( $s ) && count( $reordered_levels ) === 0 ) { ?>
			<div class="dmrfid-new-install">
				<h2><?php echo esc_attr_e( 'No se encontraron niveles de membresía', 'digital-members-rfid' ); ?></h2>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=dmrfid-membershiplevels&edit=-1' ) ); ?>" class="button-primary"><?php echo esc_attr_e( 'Crear un nivel de membresía', 'digital-members-rfid' ); ?></a>
				<a href="<?php echo esc_url( 'https://www.managertechnology.com.co/documentation/initial-plugin-setup/step-1-add-new-membership-level/?utm_source=plugin&utm_medium=dmrfid-membershiplevels&utm_campaign=documentation&utm_content=step-1-add-new-membership-level' ); ?>" target="_blank" class="button"><?php echo esc_attr_e( 'Video: Niveles de membresía', 'digital-members-rfid' ); ?></a>
			</div> <!-- end dmrfid-new-install -->
		<?php } else { ?>

		<form id="posts-filter" method="get" action="">
			<p class="search-box">
				<label class="screen-reader-text" for="post-search-input"><?php _e('Buscar nivel', 'digital-members-rfid' );?>:</label>
				<input type="hidden" name="page" value="dmrfid-membershiplevels" />
				<input id="post-search-input" type="text" value="<?php echo esc_attr($s); ?>" name="s" size="30" />
				<input class="button" type="submit" value="<?php _e('Buscar nivel', 'digital-members-rfid' );?>" id="search-submit" />
			</p>
		</form>
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Niveles de membresía', 'digital-members-rfid' ); ?></h1>
		<a href="<?php echo add_query_arg( array( 'page' => 'dmrfid-membershiplevels', 'edit' => -1 ), get_admin_url(null, 'admin.php' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Añadir nuevo nivel', 'digital-members-rfid' ); ?></a>
		<hr class="wp-header-end">

		<?php if(empty($_REQUEST['s']) && count($reordered_levels) > 1) { ?>
		    <p><?php _e('Arrastre y suelte los niveles de membresía para reordenarlos en la página Niveles.', 'digital-members-rfid' ); ?></p>
	    <?php } ?>

	    <?php
	    	//going to capture the output of this table so we can filter it
	    	ob_start();
	    ?>
	    <table class="widefat membership-levels">
		<thead>
			<tr>
				<th><?php _e('ID', 'digital-members-rfid' );?></th>
				<th><?php _e('Nombre', 'digital-members-rfid' );?></th>
				<th><?php _e('Detalles de facturación', 'digital-members-rfid' );?></th>
				<th><?php _e('Vencimiento', 'digital-members-rfid' );?></th>
				<th><?php _e('Permitir registros', 'digital-members-rfid' );?></th>
				<?php do_action( 'dmrfid_membership_levels_table_extra_cols_header', $reordered_levels ); ?>
			</tr>
		</thead>
		<tbody>
			<?php if ( !empty( $s ) && empty( $reordered_levels ) ) { ?>
			<tr class="alternate">
				<td colspan="5">
					<?php echo esc_attr_e( 'No se encontraron niveles de membresía', 'digital-members-rfid' ); ?>
				</td>
			</tr> 
			<?php } ?>
			<?php
				$count = 0;
				foreach($reordered_levels as $level)
				{
			?>
			<tr class="<?php if($count++ % 2 == 1) { ?>alternate<?php } ?> <?php if(!$level->allow_signups) { ?>dmrfid_gray<?php } ?> <?php if(!dmrfid_checkLevelForStripeCompatibility($level) || !dmrfid_checkLevelForBraintreeCompatibility($level) || !dmrfid_checkLevelForPayflowCompatibility($level) || !dmrfid_checkLevelForTwoCheckoutCompatibility($level)) { ?>dmrfid_error<?php } ?>">
				<td><?php echo $level->id?></td>
				<td class="level_name has-row-actions">
					<span class="level-name"><a href="<?php echo add_query_arg( array( 'page' => 'dmrfid-membershiplevels', 'edit' => $level->id ), admin_url( 'admin.php' ) ); ?>"><?php esc_attr_e( $level->name ); ?></a></span>
					<div class="row-actions">
						<span class="edit"><a title="<?php _e('Editar', 'digital-members-rfid' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'dmrfid-membershiplevels', 'edit' => $level->id ), admin_url('admin.php' ) ); ?>"><?php _e('Editar', 'digital-members-rfid' ); ?></a></span> |
						<span class="copy"><a title="<?php _e('Copiar', 'digital-members-rfid' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'dmrfid-membershiplevels', 'edit' => -1, 'copy' => $level->id ), admin_url( 'admin.php' ) ); ?>"><?php _e('Copiar', 'digital-members-rfid' ); ?></a></span> |
						<span class="delete"><a title="<?php _e('Eliminar', 'digital-members-rfid' ); ?>" href="javascript:dmrfid_askfirst('<?php echo str_replace("'", "\'", sprintf(__("¿Está seguro de que desea eliminar el nivel de membresía %s? Se cancelarán todas las suscripciones.", 'digital-members-rfid' ), $level->name));?>', '<?php echo wp_nonce_url(add_query_arg( array( 'page' => 'dmrfid-membershiplevels', 'action' => 'delete_membership_level', 'deleteid' => $level->id ), admin_url( 'admin.php' ) ), 'delete_membership_level', 'dmrfid_membershiplevels_nonce'); ?>'); void(0);"><?php _e('Delete', 'digital-members-rfid' ); ?></a></span>
					</div>
				</td>
				<td>
					<?php if(dmrfid_isLevelFree($level)) { ?>
						<?php _e('GRATIS', 'digital-members-rfid' );?>
					<?php } else { ?>
						<?php echo str_replace( 'El precio de la membresía es', '', dmrfid_getLevelCost($level)); ?>
					<?php } ?>
				</td>
				<td>
					<?php if(!dmrfid_isLevelExpiring($level)) { ?>
						--
					<?php } else { ?>
						<?php _e('Después', 'digital-members-rfid' );?> <?php echo $level->expiration_number?> <?php echo sornot($level->expiration_period,$level->expiration_number)?>
					<?php } ?>
				</td>
				<td><?php if($level->allow_signups) { ?><a target="_blank" href="<?php echo add_query_arg( 'level', $level->id, dmrfid_url("checkout") );?>"><?php _e('Si', 'digital-members-rfid' );?></a><?php } else { ?><?php _e('No', 'digital-members-rfid' );?><?php } ?></td>
				<?php do_action( 'dmrfid_membership_levels_table_extra_cols_body', $level ); ?>
			</tr>
			<?php
				}
			?>
		</tbody>
		</table>

	<?php
		$table_html = ob_get_clean();

		/**
		 * Filter to change the Membership Levels table
		 * @since 1.8.10
		 *
		 * @param string $table_html HTML of the membership levels table
		 * @param array $reordered_levels Array of membership levels
		 */
		$table_html = apply_filters('dmrfid_membership_levels_table', $table_html, $reordered_levels);

		echo $table_html;
	}
	?>

	<?php } ?>

<?php
	require_once(dirname(__FILE__) . "/admin_footer.php");
