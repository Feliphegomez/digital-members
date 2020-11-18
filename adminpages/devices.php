<?php 
	//only admins can get this
	if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("dmrfid_advancedsettings")))
	{
		die(__("No tienes permisos para realizar esta acción.", 'digital-members-rfid' ));
	}
	
	echo "\nDEVICES\n";
	echo "\nDEVICES\n";
	echo "\nDEVICES\n";
	echo "\nDEVICES\n";
	echo "\nDEVICES\n";
	echo "\nDEVICES\n";
/**
 * Load the Digital Members RFID devices-area header
 */
require_once( dirname( __FILE__ ) . '/admin_header.php' ); 


/**
 * Add all the meta boxes for the devices.
 */
add_meta_box(
	'dmrfid_dashboard_welcome',
	__( 'Dispositivos', 'digital-members-rfid' ),
	'dmrfid_devices_welcome_callback',
	'toplevel_page_dmrfid-devices',
	'normal'
);
?>
<form id="dmrfid-dashboard-form" method="post" action="admin-post.php">

	<div class="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">

			<?php do_meta_boxes( 'toplevel_page_dmrfid-devices', 'normal', '' ); ?>

			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( 'toplevel_page_dmrfid-devices', 'advanced', '' ); ?>
			</div>

			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( 'toplevel_page_dmrfid-devices', 'side', '' ); ?>
			</div>

        <br class="clear">

    	</div> <!-- end devices-widgets -->

		<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>

	</div> <!-- end devices-widgets-wrap -->
</form>

<?php

/**
 * Callback function for dmrfid_dashboard_welcome meta box.
 */
function dmrfid_devices_welcome_callback() { ?>
	<div class="dmrfid-dashboard-welcome-columns">
        <div class="dmrfid-dashboard-welcome-column">
    		<?php global $dmrfid_level_ready, $dmrfid_gateway_ready, $dmrfid_pages_ready; ?>
    		<h3><?php echo esc_attr_e( 'Configuración inicial', 'digital-members-rfid' ); ?></h3>
    		<ul>

    			<?php if ( current_user_can( 'dmrfid_pagesettings' ) ) { ?>
    				<li>
    					<?php if ( empty( $dmrfid_gateway_ready ) ) { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-paymentsettings' );?>"><i class="dashicons dashicons-cart"></i> <?php echo esc_attr_e( 'Dispositivos de ingreso', 'digital-members-rfid' ); ?></a>
    					<?php } else { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-paymentsettings' );?>"><i class="dashicons dashicons-cart"></i> <?php echo esc_attr_e( 'Dispositivos de ingreso', 'digital-members-rfid' ); ?></a>
    					<?php } ?>
    				</li>
    			<?php } ?>
    		</ul>
    		<hr />
    		<p class="text-center">
    			<?php echo esc_html( __( 'Para obtener orientación comience con estos pasos,', 'digital-members-rfid' ) ); ?>
    			<a href="https://www.managertechnology.com.co/documentation/initial-plugin-setup/" target="_blank"><?php echo esc_attr_e( 'ver el video de configuración inicial y los documentos.', 'digital-members-rfid' ); ?></a>
    		</p>
    	</div>
    </div> <!-- end dmrfid-dashboard-welcome-columns -->
	<?php
}

/**
 * Load the Digital Members RFID dashboard-area footer
 */
require_once( dirname( __FILE__ ) . '/admin_footer.php' );
