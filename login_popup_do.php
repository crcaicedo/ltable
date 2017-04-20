<?php
require_once "ltable_olib.php";
require_once "login_fn.php";

$isok = false;
$fo = new lt_form();
$jso = array("msg"=>"");
$para = array("us", "pw");
if (parms_isset($para, 2))
{
	if ($fo->dbopen())
	{
		if (login_check($fo, $_REQUEST['us'], $_REQUEST['pw'])) $isok = true;	
	}
}
else $fo->parc("<i>No especifico usuario/contrase&ntilde;a</i>");
$fo->tojson($isok, $reto, '');
?>
