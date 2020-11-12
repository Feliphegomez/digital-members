<?php
function dmrfid_upgrade_1_7()
{
	dmrfid_db_delta();	//just a db delta

	dmrfid_setOption("db_version", "1.7");
	return 1.7;
}
