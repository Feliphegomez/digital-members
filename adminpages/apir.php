<?php 
/**
 * Load the Digital Members RFID devices-area header
 */
require_once( dirname( __FILE__ ) . '/admin_header.php' ); 
/*
use FelipheGomez\PhpCrudApi\Api;
use FelipheGomez\PhpCrudApi\Config;
use FelipheGomez\PhpCrudApi\RequestFactory;
use FelipheGomez\PhpCrudApi\ResponseUtils;
*/


/**
 * Add all the meta boxes for the devices.
 */
add_meta_box(
	'dmrfid_dashboard_welcome',
	__( 'Explorador API', 'digital-members-rfid' ),
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
			<h3><?php echo esc_attr_e( 'Formulario', 'digital-members-rfid' ); ?></h3>
			
    		<hr />
    		<p class="text-center">
    			<?php echo esc_html( __( 'Para obtener orientación comience con estos pasos,', 'digital-members-rfid' ) ); ?>
    		</p>
    	</div>
		<div class="dmrfid-dashboard-welcome-column">
			<?php global $dmrfid_level_ready, $dmrfid_gateway_ready, $dmrfid_pages_ready; ?>
			<h3><?php echo esc_attr_e( 'Resultado', 'digital-members-rfid' ); ?></h3>
    		<hr />
    	</div>
		<div class="dmrfid-dashboard-welcome-column">
			<?php global $dmrfid_level_ready, $dmrfid_gateway_ready, $dmrfid_pages_ready; ?>
			<h3><?php echo esc_attr_e( 'Encabezados', 'digital-members-rfid' ); ?></h3>
    		<hr />
    	</div>
    </div>
	<?php
}

/**
 * Load the Digital Members RFID dashboard-area footer
 */
require_once( dirname( __FILE__ ) . '/admin_footer.php' );
