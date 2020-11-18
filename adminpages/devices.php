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
 * Load the Digital Members RFID dashboard-area header
 */
require_once( dirname( __FILE__ ) . '/admin_header.php' ); ?>