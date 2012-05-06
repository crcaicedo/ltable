<?php
require_once "ltable_olib.php";

$isok = false;
$fo = new lt_form();
$para = array("fm", "ctrl", "valor");
if (parms_isset($para, 2))
{
	if (mprs_dbcn())
	{
		if ($fo->usrchk(2, 2) !== USUARIO_UNAUTH)
		{
			$q = sprintf("REPLACE INTO ctrl_default VALUES (%d, %d, '%s', '%s')",
				$_SESSION['uid'], $_REQUEST['fm'], $_REQUEST['ctrl'], $_REQUEST['valor']);
			if (mysql_query($q) !== false)
			{
				$isok = true;
			}
			else $fo->qerr("LTSVDEF-1");
		}
		mysql_close();
	}
}
else $fo->err("LTSVDEF-0", "Parametros incorrectos");
$reto = array('msg'=>'');
if (!$isok) $fo->seguir("Seguir", "javascript:ltform_msg_hide(0);");
$reto['msg'] = $fo->buf;
$fo->http_header();
header("Content-type: application/json");
if (!$isok) header("HTTP/1.0 419 Peticion incorrecta.");
echo json_encode($reto);
?>