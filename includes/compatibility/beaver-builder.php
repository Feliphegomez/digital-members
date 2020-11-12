<?php 
/** 
 * Beaver Builder Compatibility
 */
function dmrfid_beaver_builder_compatibility() {
	// Filter members-only content later so that the builder's filters run before DmRFID.
	remove_filter('the_content', 'dmrfid_membership_content_filter', 5);
	add_filter('the_content', 'dmrfid_membership_content_filter', 15);
}
add_action( 'init', 'dmrfid_beaver_builder_compatibility' );

/**
 * Add DmRFID to row settings.
 *
 * @param array  $form Row form settings.
 * @param string $id The node/row ID.
 *
 * @return array Updated form settings.
 */
function dmrfid_beaver_builder_settings_form( $form, $id ) {
	if ( 'row' !== $id ) {
		return $form;
	}
	if ( ! defined( 'DMRFID_VERSION' ) ) {
		return $form;
	}
	global $membership_levels;
	$levels = array();
	foreach ( $membership_levels as $level ) {
		$levels[ $level->id ] = $level->name;
	}
	$row_settings_dmrfid = array(
		'title'    => __( 'DmRFID', 'digital-members-rfid' ),
		'sections' => array(
			'digital-members-rfid' => array(
				'title'  => __( 'General', 'digital-members-rfid' ),
				'fields' => array(
					'dmrfid_enable'      => array(
						'type'    => 'select',
						'label'   => __( 'Enable Digital Members RFID module visibility?', 'digital-members-rfid' ),
						'options' => array(
							'yes' => __( 'Yes', 'digital-members-rfid' ),
							'no'  => __( 'No', 'digital-members-rfid' ),
						),
						'default' => 'no',
						'toggle'  => array(
							'yes' => array(
								'fields' => array(
									'dmrfid_memberships',
								),
							),
						),
					),
					'dmrfid_memberships' => array(
						'label'        => __( 'Select a level for module access', 'digital-members-rfid' ),
						'type'         => 'select',
						'options'      => $levels,
						'multi-select' => true,
					),
				),
			),
		),
	);

	$form['tabs'] = array_merge(
		array_slice( $form['tabs'], 0, 2 ),
		array( 'DmRFID' => $row_settings_dmrfid ),
		array_slice( $form['tabs'], 2 )
	);
	return $form;
}
add_filter( 'fl_builder_register_settings_form', 'dmrfid_beaver_builder_settings_form', 10, 2 );

/**
 * Determine if the node (row/module) should be visible based on membership level.
 *
 * @param bool   $is_visible Whether the module/row is visible.
 * @param object $node The node type.
 *
 * @return bool True if visible, false if not.
 */
function dmrfid_beaver_builder_check_field_connections( $is_visible, $node ) {
	if ( ! defined( 'DMRFID_VERSION' ) ) {
		return $is_visible;
	}
	if ( 'row' === $node->type ) {
		if ( isset( $node->settings->dmrfid_enable ) && 'yes' === $node->settings->dmrfid_enable ) {
			if ( dmrfid_hasMembershipLevel( $node->settings->dmrfid_memberships ) || empty( $node->settings->dmrfid_memberships ) ) {
				return $is_visible;
			} else {
				return false;
			}
		}
	}
	if ( isset( $node->settings->dmrfid_enable ) && 'yes' === $node->settings->dmrfid_enable ) {
		if ( dmrfid_hasMembershipLevel( $node->settings->dmrfid_memberships ) || empty( $node->settings->dmrfid_memberships ) ) {
			return $is_visible;
		} else {
			return false;
		}
	}
	return $is_visible;
}
add_filter( 'fl_builder_is_node_visible', 'dmrfid_beaver_builder_check_field_connections', 200, 2 );

/**
 * Add DmRFID to all modules in Beaver Builder
 *
 * @param array  $form The form to add a custom tab for.
 * @param string $slug The module slug.
 *
 * @return array The updated form array.
 */
function dmrfid_beaver_builder_add_custom_tab_all_modules( $form, $slug ) {
	if ( ! defined( 'DMRFID_VERSION' ) ) {
		return $form;
	}
	$modules = FLBuilderModel::get_enabled_modules(); // * getting all active modules slug

	if ( in_array( $slug, $modules, true ) ) {
		global $membership_levels;
		$levels = array();
		foreach ( $membership_levels as $level ) {
			$levels[ $level->id ] = $level->name;
		}
		$form['dmrfid-bb'] = array(
			'title'    => __( 'DmRFID', 'digital-members-rfid' ),
			'sections' => array(
				'memberships' => array(
					'title'  => __( 'Membership Levels', 'digital-members-rfid' ),
					'fields' => array(
						'dmrfid_enable'      => array(
							'type'    => 'select',
							'label'   => __( 'Enable Digital Members RFID module visibility?', 'digital-members-rfid' ),
							'options' => array(
								'yes' => __( 'Yes', 'digital-members-rfid' ),
								'no'  => __( 'No', 'digital-members-rfid' ),
							),
							'default' => 'no',
							'toggle'  => array(
								'yes' => array(
									'fields' => array(
										'dmrfid_memberships',
									),
								),
							),
						),
						'dmrfid_memberships' => array(
							'label'        => __( 'Select a level for module access', 'digital-members-rfid' ),
							'type'         => 'select',
							'options'      => $levels,
							'multi-select' => true,
						),
					),
				),
			),
		);
	}

	return $form;
}
add_filter( 'fl_builder_register_settings_form', 'dmrfid_beaver_builder_add_custom_tab_all_modules', 10, 2 );
