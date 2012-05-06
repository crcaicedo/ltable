<?php
define('SOFTNAME', 'MPRS 2.7');
define('COPYRIGHT', 'OrionCorp (C) 2007-2012');
define('STD_RETURN', true);
define('LT_FULL_HEADER', true);

function lt_global()
{
	$_SESSION['useini'] = true;
	$_SESSION['inifile'] = "/etc/mprs.conf";
	$_SESSION['gps_dbexterna'] = false;
	$_SESSION['gps_dbhost'] = '172.16.2.127';
	$_SESSION['gps_dbuser'] = 'gpsconsulta';
	$_SESSION['gps_dbpwd'] = 'gps349278';
}
?>
