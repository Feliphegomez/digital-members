<?php
/**
 * The Memberships Dashboard admin page for Digital Members RFID
 * @since 2.0
 */

/**
 * Add all the meta boxes for the dashboard.
 */
add_meta_box(
	'dmrfid_dashboard_welcome',
	__( 'Bienvenido a Digital Members RFID', 'digital-members-rfid' ),
	'dmrfid_dashboard_welcome_callback',
	'toplevel_page_dmrfid-dashboard',
	'normal'
);
add_meta_box(
	'dmrfid_dashboard_report_sales',
	__( 'Ventas e ingresos', 'digital-members-rfid' ),
	'dmrfid_report_sales_widget',
	'toplevel_page_dmrfid-dashboard',
	'advanced'
);
add_meta_box(
	'dmrfid_dashboard_report_membership_stats',
	__( 'Estadísticas de membresía', 'digital-members-rfid' ),
	'dmrfid_report_memberships_widget',
	'toplevel_page_dmrfid-dashboard',
	'advanced'
);
add_meta_box(
	'dmrfid_dashboard_report_logins',
	__( 'Visitas, vistas e inicios de sesión', 'digital-members-rfid' ),
	'dmrfid_report_login_widget',
	'toplevel_page_dmrfid-dashboard',
	'advanced'
);
add_meta_box(
	'dmrfid_dashboard_report_recent_members',
	__( 'Miembros recientes', 'digital-members-rfid' ),
	'dmrfid_dashboard_report_recent_members_callback',
	'toplevel_page_dmrfid-dashboard',
	'side'
);
add_meta_box(
	'dmrfid_dashboard_report_recent_orders',
	__( 'Órdenes recientes', 'digital-members-rfid' ),
	'dmrfid_dashboard_report_recent_orders_callback',
	'toplevel_page_dmrfid-dashboard',
	'side'
);
add_meta_box(
	'dmrfid_dashboard_news_updates',
	__( 'Noticias y actualizaciones de RFID para miembros digitales', 'digital-members-rfid' ),
	'dmrfid_dashboard_news_updates_callback',
	'toplevel_page_dmrfid-dashboard',
	'side'
);

/**
 * Load the Digital Members RFID dashboard-area header
 */
require_once( dirname( __FILE__ ) . '/admin_header.php' ); ?>

<form id="dmrfid-dashboard-form" method="post" action="admin-post.php">

	<div class="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">

			<?php do_meta_boxes( 'toplevel_page_dmrfid-dashboard', 'normal', '' ); ?>

			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( 'toplevel_page_dmrfid-dashboard', 'advanced', '' ); ?>
			</div>

			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( 'toplevel_page_dmrfid-dashboard', 'side', '' ); ?>
			</div>

        <br class="clear">

    	</div> <!-- end dashboard-widgets -->

		<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>

	</div> <!-- end dashboard-widgets-wrap -->
</form>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('toplevel_page_dmrfid-dashboard');
	});
	//]]>
</script>
<?php

/**
 * Callback function for dmrfid_dashboard_welcome meta box.
 */
function dmrfid_dashboard_welcome_callback() { ?>
	<div class="dmrfid-dashboard-welcome-columns">
        <div class="dmrfid-dashboard-welcome-column">
    		<?php global $dmrfid_level_ready, $dmrfid_gateway_ready, $dmrfid_pages_ready; ?>
    		<h3><?php echo esc_attr_e( 'Configuración inicial', 'digital-members-rfid' ); ?></h3>
    		<ul>
    			<?php if ( current_user_can( 'dmrfid_membershiplevels' ) ) { ?>
    				<li>
    					<?php if ( empty( $dmrfid_level_ready ) ) { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-membershiplevels&edit=-1' );?>"><i class="dashicons dashicons-admin-users"></i> <?php echo esc_attr_e( 'Crear un nivel de membresía', 'digital-members-rfid' ); ?></a>
    					<?php } else { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-membershiplevels' );?>"><i class="dashicons dashicons-admin-users"></i> <?php echo esc_attr_e( 'Ver niveles de membresía', 'digital-members-rfid' ); ?></a>
    					<?php } ?>
    				</li>
    			<?php } ?>

    			<?php if ( current_user_can( 'dmrfid_pagesettings' ) ) { ?>
    				<li>
    					<?php if ( empty( $dmrfid_pages_ready ) ) { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-pagesettings' );?>"><i class="dashicons dashicons-welcome-add-page"></i> <?php echo esc_attr_e( 'Generar páginas de membresía', 'digital-members-rfid' ); ?></a>
    					<?php } else { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-pagesettings' );?>"><i class="dashicons dashicons-welcome-add-page"></i> <?php echo esc_attr_e( 'Administrar páginas de membresía', 'digital-members-rfid' ); ?>
    					<?php } ?>
    				</li>
    			<?php } ?>

    			<?php if ( current_user_can( 'dmrfid_pagesettings' ) ) { ?>
    				<li>
    					<?php if ( empty( $dmrfid_gateway_ready ) ) { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-paymentsettings' );?>"><i class="dashicons dashicons-cart"></i> <?php echo esc_attr_e( 'Configurar los ajustes de pago', 'digital-members-rfid' ); ?></a>
    					<?php } else { ?>
    						<a href="<?php echo admin_url( 'admin.php?page=dmrfid-paymentsettings' );?>"><i class="dashicons dashicons-cart"></i> <?php echo esc_attr_e( 'Configurar los ajustes de pago', 'digital-members-rfid' ); ?></a>
    					<?php } ?>
    				</li>
    			<?php } ?>
    		</ul>
    		<h3><?php echo esc_attr_e( 'Other Settings', 'digital-members-rfid' ); ?></h3>
    		<ul>
    			<?php if ( current_user_can( 'dmrfid_emailsettings' ) ) { ?>
    				<li><a href="<?php echo admin_url( 'admin.php?page=dmrfid-emailsettings' );?>"><i class="dashicons dashicons-email"></i> <?php echo esc_attr_e( 'Confirmar la configuración de correo electrónico', 'digital-members-rfid' );?></a></li>
    			<?php } ?>

    			<?php if ( current_user_can( 'dmrfid_advancedsettings' ) ) { ?>
    				<li><a href="<?php echo admin_url( 'admin.php?page=dmrfid-advancedsettings' );?>"><i class="dashicons dashicons-admin-settings"></i> <?php echo esc_attr_e( 'Ver configuración avanzada', 'digital-members-rfid' ); ?></a></li>
    			<?php } ?>

    			<?php if ( current_user_can( 'dmrfid_addons' ) ) { ?>
    				<li><a href="<?php echo admin_url( 'admin.php?page=dmrfid-addons' );?>"><i class="dashicons dashicons-admin-plugins"></i> <?php echo esc_attr_e( 'Explore los complementos para obtener características adicionales', 'digital-members-rfid' ); ?></a></li>
    			<?php } ?>
    		</ul>
    		<hr />
    		<p class="text-center">
    			<?php echo esc_html( __( 'Para obtener orientación comience con estos pasos,', 'digital-members-rfid' ) ); ?>
    			<a href="https://www.managertechnology.com.co/documentation/initial-plugin-setup/?utm_source=plugin&utm_medium=dmrfid-dashboard&utm_campaign=documentation&utm_content=initial-plugin-setup" target="_blank"><?php echo esc_attr_e( 'ver el video de configuración inicial y los documentos.', 'digital-members-rfid' ); ?></a>
    		</p>
    	</div> <!-- end dmrfid-dashboard-welcome-column -->
    	<div class="dmrfid-dashboard-welcome-column">
    		<h3><?php echo esc_attr_e( 'Licencia de soporte', 'digital-members-rfid' ); ?></h3>
    		<?php
    			// Get saved license.
    			$key = get_option( 'dmrfid_license_key', '' );
    			$dmrfid_license_check = get_option( 'dmrfid_license_check', array( 'license' => false, 'enddate' => 0 ) );
    		?>
    		<?php if ( ! dmrfid_license_isValid() && empty( $key ) ) { ?>
    			<p class="dmrfid_message dmrfid_error">
    				<strong><?php echo esc_html_e( 'No se encontró ninguna clave de licencia de soporte.', 'digital-members-rfid' ); ?></strong><br />
    				<?php printf(__( '<a href="%s">Ingrese su clave aquí &raquo;</a>', 'digital-members-rfid' ), admin_url( 'admin.php?page=dmrfid-license' ) );?>
    			</p>
    		<?php } elseif ( ! dmrfid_license_isValid() ) { ?>
    			<p class="dmrfid_message dmrfid_alert">
    				<strong><?php echo esc_html_e( 'Su licencia no es válida o venció.', 'digital-members-rfid' ); ?></strong><br />
					<?php printf(__( '<a href="%s">Ver su cuenta de membresía</a> para verificar su clave de licencia.', 'digital-members-rfid' ), 'https://www.managertechnology.com.co/login/?redirect_to=%2Fmembership-account%2F%3Futm_source%3Dplugin%26utm_medium%3Ddmrfid-dashboard%26utm_campaign%3Dmembership-account%26utm_content%3Dverify-license-key' );?>
    		<?php } else { ?>
    			<p class="dmrfid_message dmrfid_success"><?php printf(__( '<strong>Gracias!</strong> <strong>%s</strong> Se ha utilizado una clave de licencia válida para activar su licencia de soporte en este sitio.', 'digital-members-rfid' ), ucwords($dmrfid_license_check['license']));?></p>
    		<?php } ?>

    		<?php if ( ! dmrfid_license_isValid() ) { ?>
    			<p><?php esc_html_e( 'Se recomienda una licencia de soporte anual para los sitios web que ejecutan Digital Members RFID.', 'digital-members-rfid' ); ?><br /><a href="https://www.managertechnology.com.co/digital-members-rfid/pricing/" target="_blank"><?php esc_html_e( 'Ver precios&raquo;' , 'digital-members-rfid' ); ?></a></p>
    			<p><a href="https://www.managertechnology.com.co/membership-checkout/?level=20&utm_source=plugin&utm_medium=dmrfid-dashboard&utm_campaign=plus-checkout&utm_content=upgrade" target="_blank" class="button button-action button-hero"><?php esc_attr_e( 'Mejorar', 'digital-members-rfid' ); ?></a>
    		<?php } ?>
    		<hr />
    		<p><?php echo wp_kses_post( sprintf( __( 'Los miembros digitales RFID y nuestros complementos se distribuyen bajo la <a target="_blank" href="%s"> licencia GPLv2 </a>. Esto significa, entre otras cosas, que puede utilizar el software en este sitio o en cualquier otro sitio de forma gratuita.', 'digital-members-rfid' ), 'http://www.gnu.org/licenses/gpl-2.0.html' ) ); ?></p>
    	</div> <!-- end dmrfid-dashboard-welcome-column -->
    	<div class="dmrfid-dashboard-welcome-column">
    		<h3><?php esc_html_e( 'Involucrarse', 'digital-members-rfid' ); ?></h3>
    		<p><?php esc_html_e( 'Hay muchas formas en las que puede ayudar a respaldar la RFID de Digital Members.', 'digital-members-rfid' ); ?></p>
    		<p><?php esc_html_e( 'Participe en el desarrollo de nuestro complemento a través de GitHub.', 'digital-members-rfid' ); ?> <a href="https://github.com/strangerstudios/digital-members-rfid" target="_blank"><?php esc_html_e( 'View on GitHub', 'digital-members-rfid' ); ?></a></p>
    		<ul>
				<li><a href="https://www.youtube.com/channel/UCFtMIeYJ4_YVidi1aq9kl5g/" target="_blank"><i class="dashicons dashicons-format-video"></i> <?php esc_html_e( 'Suscríbete a nuestro canal de YouTube.', 'digital-members-rfid' ); ?></a></li>
				<li><a href="https://www.facebook.com/DigitalMembersRFID" target="_blank"><i class="dashicons dashicons-facebook"></i> <?php esc_html_e( 'Síguenos en Facebook.', 'digital-members-rfid' ); ?></a></li>
				<li><a href="https://twitter.com/dmrfidplugin" target="_blank"><i class="dashicons dashicons-twitter"></i> <?php esc_html_e( 'Siga a @dmrfidplugin en Twitter.', 'digital-members-rfid' ); ?></a></li>
				<li><a href="https://wordpress.org/plugins/digital-members-rfid/#reviews" target="_blank"><i class="dashicons dashicons-wordpress"></i> <?php esc_html_e( 'Comparta una revisión honesta en WordPress.org.', 'digital-members-rfid' ); ?></a></li>
			</ul>
    		<hr />
    		<p><?php esc_html_e( 'Ayude a traducir Digital Members RFID a su idioma.', 'digital-members-rfid' ); ?> <a href="https://translate.wordpress.org/projects/wp-plugins/digital-members-rfid" target="_blank"><?php esc_html_e( 'Panel de traducción', 'digital-members-rfid' ); ?></a></p>
    	</div> <!-- end dmrfid-dashboard-welcome-column -->
    </div> <!-- end dmrfid-dashboard-welcome-columns -->
	<?php
}

/*
 * Callback function for dmrfid_dashboard_report_recent_members meta box to show last 5 recent members and a link to the Members List.
 */
function dmrfid_dashboard_report_recent_members_callback() {
	global $wpdb;

	$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(CONVERT_TZ(u.user_registered, '+00:00', @@global.time_zone)) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP( CONVERT_TZ(mu.startdate, '+00:00', @@global.time_zone) ) as startdate, UNIX_TIMESTAMP( CONVERT_TZ(mu.enddate, '+00:00', @@global.time_zone) ) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->dmrfid_memberships_users mu ON u.ID = mu.user_id LEFT JOIN $wpdb->dmrfid_membership_levels m ON mu.membership_id = m.id WHERE mu.membership_id > 0 AND mu.status = 'active' GROUP BY u.ID ORDER BY u.user_registered DESC LIMIT 5";

	$sqlQuery = apply_filters( 'dmrfid_members_list_sql', $sqlQuery );

	$theusers = $wpdb->get_results( $sqlQuery ); ?>
    <span id="dmrfid_report_members" class="dmrfid_report-holder">
    	<table class="wp-list-table widefat fixed striped">
    		<thead>
    			<tr>
    				<th><?php _e( 'Nombre de usuario', 'digital-members-rfid' );?></th>
    				<th><?php _e( 'Rol', 'digital-members-rfid' );?></th>
    				<th><?php _e( 'Fecha de Registro', 'digital-members-rfid' );?></th>
    				<th><?php _e( 'Expiración', 'digital-members-rfid' ); ?></th>
    			</tr>
    		</thead>
    		<tbody>
    		<?php if ( empty( $theusers ) ) { ?>
                <tr>
                    <td colspan="4"><p><?php _e( 'No se encontraron miembros.', 'digital-members-rfid' ); ?></p></td>
                </tr>
            <?php } else {
    			foreach ( $theusers as $auser ) {
    				$auser = apply_filters( 'dmrfid_members_list_user', $auser );
    				//get meta
    				$theuser = get_userdata( $auser->ID ); ?>
    				<tr>
    					<td class="username column-username">
    						<?php echo get_avatar($theuser->ID, 32)?>
    						<strong>
    							<?php
    								$userlink = '<a href="' . get_edit_user_link( $theuser->ID ) . '">' . esc_attr( $theuser->user_login ) . '</a>';
    								$userlink = apply_filters( 'dmrfid_members_list_user_link', $userlink, $theuser );
    								echo $userlink;
    							?>
    						</strong>
    					</td>
    					<td><?php esc_attr_e( $auser->membership ); ?></td>
    					<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $theuser->user_registered, current_time( 'timestamp' ) ) ); ?></td>
    					<td>
    						<?php
    							if($auser->enddate)
    								echo apply_filters("dmrfid_memberslist_expires_column", date_i18n(get_option('date_format'), $auser->enddate), $auser);
    							else
    								echo __(apply_filters("dmrfid_memberslist_expires_column", "Never", $auser), "dmrfid");
    						?>
    					</td>
    				</tr>
    				<?php
    			}
            }
    		?>
    		</tbody>
    	</table>
    </span>
    <?php if ( ! empty( $theusers ) ) { ?>
        <p class="text-center"><a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=dmrfid-memberslist' ); ?>"><?php esc_attr_e( 'Ver todos los miembros ', 'digital-members-rfid' ); ?></a></p>
    <?php } ?>
	<?php
}

/*
 * Callback function for dmrfid_dashboard_report_recent_orders meta box to show last 5 recent orders and a link to view all Orders.
 */
function dmrfid_dashboard_report_recent_orders_callback() {
	global $wpdb;

	$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS id FROM $wpdb->dmrfid_membership_orders ORDER BY id DESC, timestamp DESC LIMIT 5";

	$order_ids = $wpdb->get_col( $sqlQuery );

	$totalrows = $wpdb->get_var( 'SELECT FOUND_ROWS() as found_rows' );
	?>
    <span id="dmrfid_report_orders" class="dmrfid_report-holder">
    	<table class="wp-list-table widefat fixed striped">
    	<thead>
    		<tr class="thead">
    			<th><?php _e( 'Código', 'digital-members-rfid' ); ?></th>
    			<th><?php _e( 'Usuario', 'digital-members-rfid' ); ?></th>
    			<th><?php _e( 'Rol', 'digital-members-rfid' ); ?></th>
    			<th><?php _e( 'Total', 'digital-members-rfid' ); ?></th>
    			<th><?php _e( 'Estado', 'digital-members-rfid' ); ?></th>
    			<th><?php _e( 'Fecha', 'digital-members-rfid' ); ?></th>
    		</tr>
    		</thead>
    		<tbody id="orders" class="orders-list">
        	<?php
                if ( empty( $order_ids ) ) { ?>
                    <tr>
                        <td colspan="8"><p><?php _e( 'No se encontraron pedidos.', 'digital-members-rfid' ); ?></p></td>
                    </tr>
                <?php } else {
                    foreach ( $order_ids as $order_id ) {
        			$order            = new MemberOrder();
        			$order->nogateway = true;
        			$order->getMemberOrderByID( $order_id );
        			?>
        			<tr>
        				<td>
        					<a href="admin.php?page=dmrfid-orders&order=<?php echo $order->id; ?>"><?php echo $order->code; ?></a>
        				</td>
        				<td class="username column-username">
        					<?php $order->getUser(); ?>
        					<?php if ( ! empty( $order->user ) ) { ?>
        						<a href="user-edit.php?user_id=<?php echo $order->user->ID; ?>"><?php echo $order->user->user_login; ?></a>
        					<?php } elseif ( $order->user_id > 0 ) { ?>
        						[<?php _e( 'deleted', 'digital-members-rfid' ); ?>]
        					<?php } else { ?>
        						[<?php _e( 'none', 'digital-members-rfid' ); ?>]
        					<?php } ?>
                            
                            <?php if ( ! empty( $order->billing->name ) ) { ?>
                                <br /><?php echo $order->billing->name; ?>
                            <?php } ?>
        				</td>
                        <td>
							<?php
								$level = dmrfid_getLevel( $order->membership_id );
								if ( ! empty( $level ) ) {
									echo $level->name;
								} elseif ( $order->membership_id > 0 ) { ?>
									[<?php _e( 'deleted', 'digital-members-rfid' ); ?>]
								<?php } else { ?>
									[<?php _e( 'none', 'digital-members-rfid' ); ?>]
								<?php }
							?>
                        </td>
        				<td><?php echo dmrfid_formatPrice( $order->total ); ?></td>
        				<td>
                            <?php echo $order->gateway; ?>
                            <?php if ( $order->gateway_environment == 'test' ) {
                                echo '(test)';
                            } ?>
                            <?php if ( ! empty( $order->status ) ) {
                                echo '<br />(' . $order->status . ')'; 
                            } ?>
                        </td>
                        <td><?php echo date_i18n( get_option( 'date_format' ), $order->getTimestamp() ); ?></td>
        			</tr>
                    <?php
                }
            }
        	?>
    		</tbody>
    	</table>
    </span>
    <?php if ( ! empty( $order_ids ) ) { ?>
        <p class="text-center"><a class="button button-primary" href="<?php echo admin_url( 'admin.php?page=dmrfid-orders' ); ?>"><?php esc_attr_e( 'Ver todos los pedidos ', 'digital-members-rfid' ); ?></a></p>
    <?php } ?>
	<?php
}

/*
 * Callback function for dmrfid_dashboard_news_updates meta box to show RSS Feed from Digital Members RFID blog.
 */
function dmrfid_dashboard_news_updates_callback() {

	// Get RSS Feed(s)
	include_once( ABSPATH . WPINC . '/feed.php' );

	// Get a SimplePie feed object from the specified feed source.
	$rss = fetch_feed( 'https://www.managertechnology.com.co/feed/' );

	$maxitems = 0;

	if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly

	    // Figure out how many total items there are, but limit it to 5.
	    $maxitems = $rss->get_item_quantity( 5 );

	    // Build an array of all the items, starting with element 0 (first element).
	    $rss_items = $rss->get_items( 0, $maxitems );

	endif;
	?>

	<ul>
	    <?php if ( $maxitems == 0 ) : ?>
	        <li><?php _e( 'No se encontraron noticias.', 'digital-members-rfid' ); ?></li>
	    <?php else : ?>
	        <?php // Loop through each feed item and display each item as a hyperlink. ?>
	        <?php foreach ( $rss_items as $item ) : ?>
	            <li>
	                <a href="<?php echo esc_url( $item->get_permalink() ); ?>"
	                    title="<?php printf( __( 'Posted %s', 'digital-members-rfid' ), $item->get_date( get_option( 'date_format' ) ) ); ?>">
	                    <?php echo esc_html( $item->get_title() ); ?>
	                </a>
					<?php echo esc_html( $item->get_date( get_option( 'date_format' ) ) ); ?>
	            </li>
	        <?php endforeach; ?>
	    <?php endif; ?>
	</ul>
	<p class="text-center"><a class="button button-primary" href="<?php echo esc_url( 'https://www.managertechnology.com.co/blog/?utm_source=plugin&utm_medium=dmrfid-dashboard&utm_campaign=blog&utm_content=news-updates-metabox' ); ?>"><?php esc_attr_e( 'Ver más', 'digital-members-rfid' ); ?></a></p>
	<?php
}

/**
 * Load the Digital Members RFID dashboard-area footer
 */
require_once( dirname( __FILE__ ) . '/admin_footer.php' );
