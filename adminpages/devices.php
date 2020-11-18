<?php 
/**
 * Load the Digital Members RFID devices-area header
 */
require_once( dirname( __FILE__ ) . '/admin_header.php' ); 
use FelipheGomez\PhpCrudApi\Api;
use FelipheGomez\PhpCrudApi\Config;
use FelipheGomez\PhpCrudApi\RequestFactory;
use FelipheGomez\PhpCrudApi\ResponseUtils;



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
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-apir' );?>"><i class="dashicons dashicons-rest-api"></i> <?php echo esc_attr_e( 'API', 'digital-members-rfid' ); ?></a>
    					<?php } else { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-apir' );?>"><i class="dashicons dashicons-rest-api"></i> <?php echo esc_attr_e( 'API', 'digital-members-rfid' ); ?></a>
    					<?php } ?>
    				</li>
    			<?php } ?>

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
		<div class="dmrfid-dashboard-welcome-column">
			<?php global $dmrfid_level_ready, $dmrfid_gateway_ready, $dmrfid_pages_ready; ?>
			<h3><?php echo esc_attr_e( 'Explorador API', 'digital-members-rfid' ); ?></h3>
			<?php 
				/*
				$config = new Config([
					'debug' => true,
					"driver"    => "mysql",
					"address"      => DB_HOST,
					"username"      => DB_USER,
					"password"      => DB_PASSWORD,
					"database"  => DB_NAME,
					// "charset"   => DB_CHARSET,
					'port' => 3306,
					'openApiBase' => '{"info":{"title":"API-REST-DMRFID-'.$table_prefix.'","version":"1.0.0"}}',
					'controllers' => 'records,columns,openapi,geojson', //cache
					'middlewares' => 'cors,authorization,dbAuth,sanitation,validation,multiTenancy,customization', //  ipAddress  joinLimits pageLimits,xsrf,jwtAuth
					'cacheType' => 'NoCache',
					'dbAuth.mode' => 'req',
					'dbAuth.usersTable' => 'users',
					'dbAuth.usernameColumn' => 'username',
					'dbAuth.passwordColumn' => 'password',
					'dbAuth.returnedColumns' => '',
					'customization.beforeHandler' => function ($operation, $tableName, $request, $environment) {
						$environment->start = microtime(true);
					},
					'customization.afterHandler' => function ($operation, $tableName, $response, $environment) {
						return $response->withHeader('X-Time-Taken', microtime(true) - $environment->start);
					},
					'authorization.tableHandler' => function ($operation, $tableName) {
						$finish = (!isset($_SESSION['user']) || !$_SESSION['user']) ? $operation !== 'create' && $operation !== 'update' && $operation !== 'delete' : (!isset($_SESSION['user']) || !$_SESSION['user']) ? $tableName != 'users' : true;
						return $finish;
					},
					'sanitation.handler' => function ($operation, $tableName, $column, $value) {
						if ($column['name'] == 'password'){
							if ($operation == 'create' || $operation == 'update'){
								return is_string($value) ? password_hash($value, PASSWORD_DEFAULT) : password_hash(strip_tags($value), PASSWORD_DEFAULT);
							} else {
								return is_string($value) ? strip_tags($value) : $value;
							}
						} else {
							return is_string($value) ? ($value) : (string) $value;
						}
					},
				]);
				$request = RequestFactory::fromGlobals();
				$api = new Api($config);
				$response = $api->handle($request);
				ResponseUtils::output($response);
				*/
			?>
    		<hr />
    	</div>
    </div>
	<?php
}

/**
 * Load the Digital Members RFID dashboard-area footer
 */
require_once( dirname( __FILE__ ) . '/admin_footer.php' );
