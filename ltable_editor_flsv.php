<?php
require_once "ltable_olib.php";

$isok = false;
$reto = array('msg'=>'');
$para = array('tbl','fl','k','v','t');
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$tabla = mysql_real_escape_string($_REQUEST['tbl']);
	$fl = mysql_real_escape_string($_REQUEST['fl']);
	$flk = mysql_real_escape_string($_REQUEST['k']);
	$tipo = $_REQUEST['t'];
	if ($fo->dbopen())
	{
		if ($fo->usrchk(1,1) !== USUARIO_UNAUTH)
		{
			switch ($tipo)
			{
				case 'c':
				case 'm':
				case 'd':
				case 't':
				case 'h':
					$v = mysql_real_escape_string($_REQUEST['v']);
					$qa = new myquery($fo, sprintf("UPDATE %s SET %s='%s' WHERE n='%s'",
						$tabla, $fl, $v, $flk), "LTEDITOR-FLSV-1", false, true);
					$isok = $qa->isok;
					break;
				case 'n':
				case 'i':
				case 'b':
					$v = $_REQUEST['v']+0;
					$qa = new myquery($fo, sprintf("UPDATE %s SET %s=%s WHERE n='%s'",
						$tabla, $fl, $v, $flk), "LTEDITOR-FLSV-1", false, true);
					$isok = $qa->isok;
					break;
			}
		}
	}
}
$fo->tojson($isok, $reto);
?>