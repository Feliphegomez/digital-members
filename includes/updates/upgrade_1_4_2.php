<?php
function dmrfid_upgrade_1_4_2()
{
	/*
		Setting the new use_ssl setting.
		PayPal Website Payments Pro, Authorize.net, and Stripe will default to use ssl.
		PayPal Express and the test gateway (no gateway) will default to not use ssl.
	*/
	$gateway = dmrfid_getOption("gateway");
	if($gateway == "paypal" || $gateway == "authorizenet" || $gateway == "stripe")
		dmrfid_setOption("use_ssl", 1);
	else
		dmrfid_setOption("use_ssl", 0);

	dmrfid_setOption("db_version", "1.42");
	return 1.42;
}
