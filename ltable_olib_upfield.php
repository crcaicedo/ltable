<?php
require_once "ltable_olib.php";

$isok = false;
$reto = array("msg"=>"");
$para = array("tbl","fn","fv", "kn", "kv");
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$tabla = $_REQUEST["tbl"];
	$fn = $_REQUEST["fn"];
	$fv = $_REQUEST["fv"];
	$kn = $_REQUEST["kn"];
	$kv = $_REQUEST["kv"];
	$uid = $_SESSION["uid"];
	$ipaddr = $_SERVER["REMOTE_ADDR"];
	
	if (mprs_dbcn())
	{
		$lto = new ltable();
		if ($lto->load($tabla, "", false, $fn))
		{
			if ($fo->usrchk($lto->form_id + 2, $lto->form_rw) !== USUARIO_UNAUTH)
			{
				$lto->nuevo = 0;
				$lto->umethod = 1;
				$lto->fa[$fn]->text = $fv;
				$lto->values_from_text();
				$fo->parc($lto->build_update($kn, $kv, ""));
				$qa = new myquery($fo, sprintf("UPDATE %s SET %s,modificado=NOW(),uid=%d,ipaddr='%s' WHERE %s=%s",
					$tabla, $lto->update_set, $uid, $ipaddr, $kn, $kv), "LTUPFL-1", true, true);
				//$fo->parc($qa->q);
				$isok = $qa->isok;
			}
		}
		mysql_close();
	}
}
else $fo->menuprinc();
$fo->tojson($isok, $reto, LTMSG_HIDE);
?>