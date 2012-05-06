<?php
require_once "ltable_olib.php";

$reto = array("msg"=>"");
$isok = false;
$fo = new lt_form();
$para = array("ds","sl");
if (parms_isset($para,2))
{
	if ($fo->dbopen())
	{
		if ($fo->usrchk(1,3) !== USUARIO_UNAUTH)
		{
			$dashboard_id = $_REQUEST["ds"]+0;
			$slot_id = $_REQUEST["sl"]+0;
			$uid = $_SESSION["uid"];
			$qa = new myquery($fo, sprintf("DELETE FROM dashboard_det ".
				"WHERE dashboard_id=%d AND slot_id=%d AND uid=%d",
				$dashboard_id, $slot_id, $uid), "DASHDEL-1", true, true);
			//$fo->parc($qa->q);
			$isok = $qa->isok;
		}
	}
}
$fo->tojson($isok, $reto, LTMSG_HIDE);
?>
