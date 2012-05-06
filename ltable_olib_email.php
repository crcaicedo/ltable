<?php
require_once "ltable_olib.php";

$isok = false;
$reto = array("msg"=>"");
$para = array("id","ad","su");
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$addrto = $_REQUEST["ad"];
	$rpt_id = $_REQUEST["id"];
	///$tmpfn = sprintf("/tmp/%s", $_REQUEST["fn"]);
	$subj = $_REQUEST["su"];
	if ($fo->dbopen())
	{
		if ($fo->usrchk(1, 2) !== USUARIO_UNAUTH)
		{
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: "Ventas OrionCorp" <carlos.caicedo@orioncorp.com.ve>' . "\r\n";
			$headers .= 'Reply-To: "Ventas OrionCorp" <ventas@multinmuebles.com>' . "\r\n";
			///if (($buffer = file_get_contents($tmpfn)) !== false)
			$qeml = new myquery($fo, sprintf("SELECT buf FROM ltable_emltmp WHERE uid=%d AND rpt_id=%d",
				$_SESSION["uid"], $rpt_id), "LTEML-1");
			if ($qeml->isok)
			{
				$isok = mail($addrto, $subj, $qeml->r->buf, $headers);
			}
			else $fo->err("LTEML-2", "No pude leer archivo temporal");
		}
	}
}
else $fo->menuprinc();
$fo->tojson($isok, $reto);
?>