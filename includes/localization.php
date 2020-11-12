<?php
function dmrfid_load_textdomain()
{
    //get the locale
	$locale = apply_filters("plugin_locale", get_locale(), "digital-members-rfid");
	$mofile = "digital-members-rfid-" . $locale . ".mo";

	//paths to local (plugin) and global (WP) language files
	$mofile_local  = dirname(__FILE__)."/../languages/" . $mofile;
	$mofile_global = WP_LANG_DIR . '/dmrfid/' . $mofile;
	$mofile_global2 = WP_LANG_DIR . '/digital-members-rfid/' . $mofile;

	//load global first    
	if(file_exists($mofile_global))
		load_textdomain("digital-members-rfid", $mofile_global);
	elseif(file_exists($mofile_global2))
		load_textdomain("digital-members-rfid", $mofile_global2);
	
	//load local second
	load_textdomain("digital-members-rfid", $mofile_local);
	
	//load via plugin_textdomain/glotpress
	load_plugin_textdomain( 'digital-members-rfid', false, dirname(__FILE__)."/../languages/" );
}
add_action("init", "dmrfid_load_textdomain", 1);

function dmrfid_translate_billing_period($period, $number = 1)
{
	//note as of v1.8, we stopped using _n and split things up to aid in localization
	if($number == 1)
	{
		if($period == "Day")
			return __("Day", 'digital-members-rfid' );
		elseif($period == "Week")
			return __("Week", 'digital-members-rfid' );
		elseif($period == "Month")
			return __("Month", 'digital-members-rfid' );
		elseif($period == "Year")
			return __("Year", 'digital-members-rfid' );
	}
	else
	{
		if($period == "Day")
			return __("Days", 'digital-members-rfid' );
		elseif($period == "Week")
			return __("Weeks", 'digital-members-rfid' );
		elseif($period == "Month")
			return __("Months", 'digital-members-rfid' );
		elseif($period == "Year")
			return __("Years", 'digital-members-rfid' );
	}
}
