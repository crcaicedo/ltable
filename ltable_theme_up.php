<?php
require_once "mprsfn.php";

nocache();
if (isset($_REQUEST['nvotema']))
{
	if (mprs_dbcn())
	{
		$q = sprintf("REPLACE INTO ltable_themes SET uid=%d, tema='%s'", $_COOKIE['uid'], 
			$_REQUEST['nvotema']);
		if (mysql_query($q) !== false)
		{
			setcookie("tema", $_REQUEST['nvotema'], 0, "/");
			echo "OK";
		}
		else echo "ERROR";
		mysql_close();
	}
}
?>