<?php
function dmrfid_upgrade_1_3_18()
{
	//setting new email settings defaults
	dmrfid_setOption("email_admin_checkout", "1");
	dmrfid_setOption("email_admin_changes", "1");
	dmrfid_setOption("email_admin_cancels", "1");
	dmrfid_setOption("email_admin_billing", "1");

	dmrfid_setOption("db_version", "1.318");
	return 1.318;
}
