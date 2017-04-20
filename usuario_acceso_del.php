<?php
require_once "ltable_olib.php";

$fo = new lt_form();
$para = array("uidx", "pidx", "modx");
if (parms_isset($para,2))
{
	$uid = $_REQUEST['uidx']+0;
	$prjid = $_REQUEST['pidx']+0;
	$modid = $_REQUEST['modx']+0;
	if ($fo->dbopen())
	{
		if ($fo->usrchk(143, 0) != USUARIO_UNAUTH)
		{
			$q = new myquery($fo, sprintf("DELETE FROM acceso " .
				"WHERE uid=%d AND proyecto_id=%d AND modulo_id=%d",
				$uid, $prjid, $modid), "USRACCDL-1", true, true);
			$fo->parc($q->q);
			if ($q->isok)
			{
				$fo->ok("Acceso borrado");
			}
		}
	}
}
$fo->seguir();
$fo->show();
?>