<?php

class DmRFIDDivi{

	function __construct(){

		if ( empty( $_GET['page'] ) || 'et_divi_role_editor' !== $_GET['page'] ) {
			add_filter( 'et_builder_get_parent_modules', array( $this, 'toggle' ) );
			add_filter( 'et_pb_module_content', array( $this, 'restrict_content' ), 10, 4 );
			add_filter( 'et_pb_all_fields_unprocessed_et_pb_row', array( $this, 'row_settings' ) );
			add_filter( 'et_pb_all_fields_unprocessed_et_pb_section', array( $this, 'section_settings' ) );			
		}

	}

	public function toggle( $modules ) {

		if ( ! empty( $modules ) && is_object( $modules['et_pb_row'] ) ) {
			$modules['et_pb_row']->settings_modal_toggles['custom_css']['toggles']['digital-members-rfid'] = __( 'Digital Members RFID', 'digital-members-rfid' );
		}

		if ( ! empty( $modules ) && is_object( $modules['et_pb_section'] ) ) {
			$modules['et_pb_section']->settings_modal_toggles['custom_css']['toggles']['digital-members-rfid'] = __( 'Digital Members RFID', 'digital-members-rfid' );
		}

		return $modules;

	}

	public function row_settings( $settings ) {

		$settings['digital-members-rfid'] = array(
			'tab_slug' => 'custom_css',
			'label' => __( 'Restrict Row by Level', 'digital-members-rfid' ),
			'description' => __( 'Enter comma-separated level IDs.', 'digital-members-rfid' ),
			'type' => 'text',
			'default' => '',
			'option_category' => 'configuration',
			'toggle_slug' => 'digital-members-rfid',
	    );

		return $settings;

	}

	public function section_settings( $settings ) {

	    $settings['digital-members-rfid'] = array(
			'tab_slug' => 'custom_css',
			'label' => __( 'Restrict Section by Level', 'digital-members-rfid' ),
			'description' => __( 'Enter comma-separated level IDs.', 'digital-members-rfid' ),
			'type' => 'text',
			'default' => '',
			'option_category' => 'configuration',
			'toggle_slug' => 'digital-members-rfid',
	    );

		return $settings;

	}
  
  	public function restrict_content( $output, $props, $attrs, $slug ) {

	    if ( et_fb_is_enabled() ) {
			return $output;
	    }

	    if( !isset( $props['digital-members-rfid'] ) ){
	    	return $output;
	    }
		
		$level = $props['digital-members-rfid'];
		
		if ( empty( trim( $level ) ) || trim( $level ) === '0' ) {
			return $output;
		}
		
		if( strpos( $level, "," ) ) {
		   //they specified many levels
		   $levels = explode( ",", $level );
		} else {
		   //they specified just one level
		   $levels = array( $level );
		}

	    if( dmrfid_hasMembershipLevel( $levels ) ){
	    	return $output;
	    } else {
	    	return '';
	    }
	}
}
new DmRFIDDivi();