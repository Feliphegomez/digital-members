<?php
//only let admins get here
if ( ! function_exists( 'current_user_can' ) || ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'dmrfid_license') ) ) {
	die( __( 'No tienes permisos para realizar esta acción.', 'digital-members-rfid' ) );
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
			<p><?php _e( 'Su clave de licencia ha sido validada.', 'digital-members-rfid' ); ?></p>
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
		<h2><?php _e('Licencia de soporte RFID para miembros digitales', 'digital-members-rfid' );?></h2>

		<div class="about-text">
			<?php if(!dmrfid_license_isValid() && empty($key)) { ?>
				<p class="dmrfid_message dmrfid_error"><strong><?php _e('Ingrese su clave de licencia de soporte. </strong> Su clave de licencia se puede encontrar en el recibo de correo electrónico de su membresía o en su <a href="https://www.managertechnology.com.co/login/?redirect_to=%2Fmembership-account%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-license%26utm_campaign%3Dmembership-account%26utm_content%3Dno-key "target="_blank"> Cuenta de membresía </a>.', 'digital-members-rfid' );?></p>
			<?php } elseif(!dmrfid_license_isValid()) { ?>
				<p class="dmrfid_message dmrfid_error"><strong><?php _e('Su licencia no es válida o venció.', 'digital-members-rfid' );?></strong> <?php _e('Visit the DmRFID <a href="https://www.managertechnology.com.co/login/?redirect_to=%2Fmembership-account%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-license%26utm_campaign%3Dmembership-account%26utm_content%3Dkey-not-valid" target="_blank">Membership Account</a> page to confirm that your account is active and to find your license key.', 'digital-members-rfid' );?></p>
			<?php } else { ?>													
				<p class="dmrfid_message dmrfid_success"><?php printf(__('<strong> ¡Gracias! </strong> Se usó una clave de licencia <strong> %s </strong> válida para activar su licencia de soporte en este sitio.', 'digital-members-rfid' ), ucwords($dmrfid_license_check['license']));?></p>
			<?php } ?>

			<form action="" method="post">
			<table class="form-table">
				<tbody>
					<tr id="dmrfid-settings-key-box">
						<td>
							<input type="password" name="dmrfid-license-key" id="dmrfid-license-key" value="<?php echo esc_attr($key);?>" placeholder="<?php _e('Enter license key here...', 'digital-members-rfid' );?>" size="40"  />
							<?php wp_nonce_field( 'dmrfid-key-nonce', 'dmrfid-key-nonce' ); ?>
							<?php submit_button( __( 'Validar clave', 'digital-members-rfid' ), 'primary', 'dmrfid-verify-submit', false ); ?>
						</td>
					</tr>
				</tbody>
			</table>
			</form>

			<p>
				<?php if ( ! dmrfid_license_isValid() ) { ?>
					<a class="button button-primary button-hero" href="https://www.managertechnology.com.co/membership-checkout/?level=20&utm_source=plugin&utm_medium=dmrfid-license&utm_campaign=plus-checkout&utm_content=buy-plus" target="_blank"><?php echo esc_html( 'Comprar licencia Plus', 'digital-members-rfid' ); ?></a>
					<a class="button button-hero" href="https://www.managertechnology.com.co/pricing/?utm_source=plugin&utm_medium=dmrfid-license&utm_campaign=pricing&utm_content=view-license-options" target="_blank"><?php echo esc_html( 'Ver opciones de licencia de soporte', 'digital-members-rfid' ); ?></a>
				<?php } else { ?>
					<a class="button button-primary button-hero" href="https://www.managertechnology.com.co/login/?redirect_to=%2Fmembership-account%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-license%26utm_campaign%3Dmembership-account%26utm_content%3Dview-account" target="_blank"><?php echo esc_html( 'Administra mi cuenta', 'digital-members-rfid' ); ?></a>
					<a class="button button-hero" href="https://www.managertechnology.com.co/login/?redirect_to=%2Fnew-topic%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-license%26utm_campaign%3Dsupport%26utm_content%3Dnew-support-ticket" target="_blank"><?php echo esc_html( 'Ticket de soporte abierto', 'digital-members-rfid' ); ?></a>
				<?php } ?>
			</p>

			<hr />
			
			<div class="clearfix"></div>

			<img class="dmrfid_icon alignright" src="<?php echo DMRFID_URL?>/images/Digital-Members-RFID_icon.png" border="0" alt="Digital Members RFID(c) - Todos los derechos reservados" />
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
				echo '<p>' . sprintf( wp_kses( __( 'Los miembros digitales RFID y nuestros complementos se distribuyen bajo la <a href="%s" title="GPLv2 license" target="_blank"> licencia GPLv2 </a>. Esto significa, entre otras cosas, que puede utilizar el software en este sitio o en cualquier otro sitio de forma gratuita.', 'digital-members-rfid' ), $allowed_dmrfid_license_strings_html ), 'https://www.managertechnology.com.co/features/digital-members-rfid-is-100-gpl/?utm_source=plugin&utm_medium=dmrfid-license&utm_campaign=documentation&utm_content=gpl' ) . '</p>';
			?>

			<?php
				echo '<p>' . wp_kses( __( '<strong> Digital Members RFID ofrece planes para actualizaciones automáticas de complementos y soporte premium. </strong> Estos planes incluyen una clave de licencia Plus que recomendamos para todos los sitios web públicos que ejecutan Digital Members RFID. Una clave de licencia Plus le permite instalar automáticamente nuevos complementos y actualizar los complementos activos cuando se lanza una nueva seguridad, corrección de errores o mejora de funciones.' ), $allowed_dmrfid_license_strings_html ) . '</p>';
			?>

			<?php
				echo '<p>' . wp_kses( __( '<strong> ¿Necesita ayuda? </strong> Su licencia le permite abrir nuevos tickets en nuestra área de soporte privada. Las compras están respaldadas por una política de reembolso de 30 días, sin preguntas.' ), $allowed_dmrfid_license_strings_html ) . '</p>';
			?>

			<p><a href="https://www.managertechnology.com.co/pricing/?utm_source=plugin&utm_medium=dmrfid-license&utm_campaign=pricing&utm_content=view-license-options" target="_blank"><?php echo esc_html( 'Ver opciones de licencia de soporte &raquo;', 'digital-members-rfid' ); ?></a></p>

		</div> <!-- end about-text -->
	</div> <!-- end about-wrap -->

<?php

require_once(dirname(__FILE__) . "/admin_footer.php");
?>
