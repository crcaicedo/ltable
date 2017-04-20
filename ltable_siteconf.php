<?php
define('SOFTNAME', 'Asterisk Smart Routing 1.9');
define('COPYRIGHT', 'OrionCorp (C) 2009-2017');
define('STD_RETURN', true);
define('LT_FULL_HEADER', true);
define('ABSOLUTE_PATH', 'https://orioncorp.com.ve/smartrt/');
define('DEFAULT_SCHEMA', 'smartrt');
define('RUTA', '/smartrt/');
define('RUTA_CSS', '/smartrt/');
define('RUTA_JS', '/smartrt/');
define('RUTA_IMG', '/smartrt/images/');
define('RUTA_HOST', 'https://orioncorp.com.ve');
define('RUTA_LT', './'); // ruta absoluta a los archivos ltable_*

function lt_global()
{
	//$_SESSION['inifile'] = "/etc/smartrt.conf";
	$_SESSION['useini'] = FALSE;
	$_SESSION['dbname'] = 'smartrt';
	$_SESSION['dburl'] = 'localhost';
	$_SESSION['dbuser'] = 'smartrt';
	$_SESSION['dbpasswd'] = 'Mr783416';
}
?>
