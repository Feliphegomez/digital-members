<?php
/**
 * Get all Digital Members RFID pages.
 *
 * @since 2.3
 * @return array
 */
function dmrfid_get_dmrfid_pages() {
	$dmrfid_pages = array(
		'account' => intval( dmrfid_getOption( 'account_page_id' ) ),
		'billing' => intval( dmrfid_getOption( 'billing_page_id' ) ),
		'cancel' => intval( dmrfid_getOption( 'cancel_page_id' ) ),
		'checkout' => intval( dmrfid_getOption( 'checkout_page_id' ) ),
		'confirmation' => intval( dmrfid_getOption( 'confirmation_page_id' ) ),
		'invoice' => intval( dmrfid_getOption( 'invoice_page_id' ) ),
		'levels' => intval( dmrfid_getOption( 'levels_page_id' ) ),
		'member_profile_edit' => intval( dmrfid_getOption( 'member_profile_edit_page_id' ) ),
	);

	$dmrfid_page_names = array();
	foreach ( $dmrfid_pages as $dmrfid_page_id => $dmrfid_page ) {
		$dmrfid_page_names[$dmrfid_page_id] = get_the_title( $dmrfid_page_id );
	}

	return apply_filters( 'dmrfid_get_dmrfid_pages', $dmrfid_pages, $dmrfid_page_names );
}

/**
 * Add Digital Members RFID nav menu meta box.
 *
 * @since 2.3
 */
function dmrfid_nav_menu_meta_box() {
	add_meta_box( 'add-dmrfid-pages', __( 'Digital Members RFID', 'digital-members-rfid' ),'dmrfid_pages_metabox_nav_links', 'nav-menus', 'side', 'low' );
}
add_action( 'admin_head-nav-menus.php', 'dmrfid_nav_menu_meta_box' );

/**
 * Add links to Digital Members RFID nav menu meta box.
 *
 * @since 2.3
 */
function dmrfid_pages_metabox_nav_links() {

	global $nav_menu_selected_id;

	// Get all the page settings.
	$dmrfid_page_ids = dmrfid_get_dmrfid_pages();

	// Allow custom plugins to filter the page IDs.
	$dmrfid_page_ids = apply_filters( 'dmrfid_custom_nav_menu_items', $dmrfid_page_ids );

	// Get the page data for these IDs.
	$dmrfid_pages = get_pages( array( 'include' => $dmrfid_page_ids ) );
	?>
	<div id="dmrfid-page-items" class="posttypediv">
		<div class="tabs-panel tabs-panel-active">
			<ul class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $dmrfid_pages ), 0, (object) array(
					'walker' => new Walker_Nav_Menu_Checklist(),
				) ); ?>

				<?php // Include the custom Log In and Log Out menu items. ?>
				<li>
					<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"> <?php _e( 'Iniciar sesión', 'digital-members-rfid'); ?>
					</label>
					<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-type-name" name="menu-item[-1][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php _e( 'Iniciar sesión', 'digital-members-rfid'); ?>">
					<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="#">
					<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="menu-item-type-dmrfid-login">
				</li>
				<li>
					<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-2][menu-item-object-id]" value="-2"> <?php _e( 'Cerrar sesión', 'digital-members-rfid'); ?>
					</label>
					<input type="hidden" class="menu-item-type" name="menu-item[-2][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-title" name="menu-item[-2][menu-item-title]" value="<?php _e( 'Cerrar sesión', 'digital-members-rfid'); ?>">
					<input type="hidden" class="menu-item-url" name="menu-item[-2][menu-item-url]" value="#">
					<input type="hidden" class="menu-item-classes" name="menu-item[-2][menu-item-classes]" value="menu-item-type-dmrfid-logout">
				</li>
			</ul>
		</div>
		<p class="button-controls wp-clearfix">
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Agregar al menú' ); ?>" name="add-dmrfid-page-items" id="submit-dmrfid-page-items" />
				<span class="spinner"></span>
			</span>
		</p>
	</div>
<?php
}

/**
 * Register Digital Members RFID nav menu item types in Customizer.
 *
 * @since  2.3
 * @param  array $item_types Menu item types.
 * @return array
 */
function dmrfid_customize_nav_menu_available_item_types( $item_types ) {
	$item_types[] = array(
		'title'      => __( 'Digital Members RFID', 'digital-members-rfid' ),
		'type_label' => __( 'Digital Members RFID Page', 'digital-members-rfid' ),
		'type'       => 'dmrfid_nav',
		'object'     => 'dmrfid_pages',
	);
	return $item_types;
}
add_filter( 'customize_nav_menu_available_item_types', 'dmrfid_customize_nav_menu_available_item_types' );

/**
 * Register Digital Members RFID pages to customize nav menu items.
 *
 * @since  2.3
 * @param  array   $items  List of nav menu items.
 * @param  string  $type   Nav menu type.
 * @param  string  $object Nav menu object.
 * @param  integer $page   Page number.
 * @return array
 */
function dmrfid_customize_nav_menu_available_items( $items, $type, $object, $page ) {
	// Only add items to our new item type ('dmrfid_pages' object).
	if ( $object !== 'dmrfid_pages' ) {
		return $items;
	}

	// Don't allow pagination since all items are loaded at once.
	if ( 0 < $page ) {
		return $items;
	}

	// Get all the page settings.
	$dmrfid_page_ids = dmrfid_get_dmrfid_pages();

	// Allow custom plugins to filter the page IDs.
	$dmrfid_page_ids = apply_filters( 'dmrfid_custom_nav_menu_items', $dmrfid_page_ids );

	// Get the page data for these IDs.
	$dmrfid_pages = get_pages( array( 'include' => $dmrfid_page_ids ) );

	// Include conditional log in / log out menu item.
	$dmrfid_pages['login-out'] = __( 'Iniciar / Cerrar sesión condicional', 'digital-members-rfid' );

	foreach ( $dmrfid_pages as $dmrfid_page ) {
		$items[] = array(
			'id'         => 'post-' . $dmrfid_page->ID,
			'title'      => html_entity_decode( $dmrfid_page->post_title, ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'type_label' => get_post_type_object( $dmrfid_page->post_type )->labels->singular_name,
			'object'     => $dmrfid_page->post_type,
			'object_id'  => intval( $dmrfid_page->ID ),
			'url'        => get_permalink( intval( $dmrfid_page->ID ) ),
		);
	}

	// Include the custom Log In and Log Out menu items.
	$items[] = array(
		'id'         => 'dmrfid-login',
		'title'      => __( 'Iniciar sesión', 'digital-members-rfid'),
		'type'       => 'dmrfid-login',
		'type_label' => __( 'Page', 'digital-members-rfid'),
		'object'     => 'page',
		'url'        => '#',
	);

	$items[] = array(
		'id'         => 'dmrfid-logout',
		'title'      => __( 'Cerrar sesión', 'digital-members-rfid'),
		'type'       => 'dmrfid-logout',
		'type_label' => __( 'Page', 'digital-members-rfid'),
		'object'     => 'page',
		'url'        => '#',
	);

	return $items;
}
add_filter( 'customize_nav_menu_available_items', 'dmrfid_customize_nav_menu_available_items', 10, 4 );

/**
 * Filter nav menus with our custom Log In or Log Out links.
 * Remove the appropriate link based on logged in status.
 *
 * @since 2.3
 */
function dmrfid_swap_log_in_log_out_menu_link( $sorted_menu_items, $args ) {

	foreach ( $sorted_menu_items as $key => $item ) {

		// Hide or Show the Log In link and filter the URL.
		if ( in_array( 'menu-item-type-dmrfid-login', $item->classes ) ) {
			if ( is_user_logged_in() ) {
				unset( $sorted_menu_items[$key] );
			} else {
				$sorted_menu_items[$key]->url = dmrfid_login_url();
				//$remove_key = array_search( 'menu-item-dmrfid-login', $item->classes );
				$remove_key2 = array_search( 'menu-item-object-', $item->classes );
				//unset($sorted_menu_items[$key]->classes[$remove_key]);
				unset($sorted_menu_items[$key]->classes[$remove_key2]);
			}
		}

		// Hide or Show the Log Our link and filter the URL.
		if ( in_array( 'menu-item-type-dmrfid-logout', $item->classes ) ) {
			if ( ! is_user_logged_in() ) {
				unset( $sorted_menu_items[$key] );
			} else {
				$sorted_menu_items[$key]->url = wp_logout_url();
				//$remove_key = array_search( 'menu-item-dmrfid-logout', $item->classes );
				$remove_key2 = array_search( 'menu-item-object-', $item->classes );
				//unset($sorted_menu_items[$key]->classes[$remove_key]);
				unset($sorted_menu_items[$key]->classes[$remove_key2]);
			}
		}

	}

	return $sorted_menu_items;
}
add_filter( 'wp_nav_menu_objects', 'dmrfid_swap_log_in_log_out_menu_link', 10, 2 );

/**
 * Custom menu functions for Digital Members RFID
 *
 * @since 2.3
 */
function dmrfid_register_menus() {
	// Register DmRFID menu areas.
	register_nav_menus(
		array(
			'dmrfid-login-widget' => __( 'Widget de inicio de sesión - DmRFID', 'digital-members-rfid' ),
		)
	);
}
add_action( 'after_setup_theme', 'dmrfid_register_menus' );

/**
 * Hide the WordPress Toolbar from Subscribers.
 *
 * @since 2.3
 */
function dmrfid_hide_toolbar() {
	global $current_user;
	$hide_toolbar = dmrfid_getOption( 'hide_toolbar' );
	if ( ! empty( $hide_toolbar ) && is_user_logged_in() && in_array( 'subscriber', (array) $current_user->roles ) ) {
		$hide = true;
	} else {
		$hide = false;
	}	
	$hide = apply_filters( 'dmrfid_hide_toolbar', $hide );
	if ( $hide ) {
		add_filter( 'show_admin_bar', '__return_false' );
	}
}
add_action( 'init', 'dmrfid_hide_toolbar', 9 );