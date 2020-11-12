<?php
/*
	Load All Reports
*/
$dmrfid_reports_dir = dirname(__FILE__) . "/../adminpages/reports/";
$cwd = getcwd();
chdir($dmrfid_reports_dir);
foreach (glob("*.php") as $filename) 
{
	require_once($filename);
}
chdir($cwd);

/*
	Load Reports From Theme
*/
$dmrfid_reports_theme_dir = get_stylesheet_directory() . "/digital-members-rfid/reports/";
if(is_dir($dmrfid_reports_theme_dir))
{
	$cwd = getcwd();
	chdir($dmrfid_reports_theme_dir);
	foreach (glob("*.php") as $filename)
	{
		require_once($filename);
	}
	chdir($cwd);
}
