<?php
require_once "ltable_olib.php";

$para = array("nr","xuid","xpid");
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$uid = $_REQUEST['xuid']+0;
	$pid = $_REQUEST['xpid']+0;
	$nr = $_REQUEST['nr']+0;
	if ($fo->dbopen())
	{
		if ($fo->usrchk(142, 2) !== USUARIO_UNAUTH)
		{
			$fo->encabezado();
			
			$vs = "";
			for ($ii = 0; $ii < $nr; $ii++)
			{
				if (isset($_REQUEST['chk'.$ii]))
				{
					$cta_id = $_REQUEST['cta_id'.$ii]+0;
					$vs .= sprintf(",(%d,%d,%d)", $cta_id, $uid, $pid);
				}
			}
			if ($vs != "")
			{
				$isok = false;
				mysql_query("SET autocommit=0");
				mysql_query("START TRANSACTION");
				$qa = new myquery($fo, sprintf("DELETE FROM cuentas_usr ".
					"WHERE uid=%d AND proyecto_id=%d", $uid, $pid), "USRCTASDO-1", false, true);
				if ($qa->isok)
				{
					$qb = new myquery($fo, "INSERT INTO cuentas_usr VALUES ".substr($vs,1),
						"USRCTASDO-2", false, true);
					//$fo->parc($qb->q);
					if ($qb->isok)
					{
						$fo->ok("Cambios guardados");
						$isok = true;
					}
				}
				if ($isok) mysql_query("COMMIT"); else mysql_query("ROLLBACK");
				mysql_query("SET autocommit=1");
			}
			else $fo->err("USRCTASDO-3", "Lista vacia");
		}
	}
}
$fo->par(3);
$fo->lnk("ltable_olib_main.php?tabla=usuarios","Volver al formulario principal");
$fo->parx();
$fo->footer();
$fo->show();
?>