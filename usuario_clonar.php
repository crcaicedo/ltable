<?php
require_once "ltable_olib.php";

$para = array("uid_src","uid_dst");
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$uid_src = $_REQUEST['uid_src']+0;
	$uid_dst = $_REQUEST['uid_dst']+0;
	if ($fo->dbopen())
	{
		if ($fo->usrchk(142, 2) !== USUARIO_UNAUTH)
		{
			$fo->encabezado();
			$fo->hdr('Usuario: Clonar', 4);
			
			$isok = false;
			mysql_query('SET autocommit=0');
			mysql_query('START TRANSACTION');
			$qa = new myquery($fo, sprintf("DELETE FROM acceso WHERE uid=%d", $uid_dst), 
				"USRCLONE-1", false, true);
			if ($qa->isok)
			{
				$qb = new myquery($fo, sprintf("INSERT INTO acceso ".
					"(SELECT %d,modulo_id,proyecto_id,usertype_id FROM acceso ".
					"WHERE uid=%d)", $uid_dst, $uid_src), "USRCLONE-2", false, true);
				{
					$qa = new myquery($fo, sprintf("DELETE FROM favoritas_usr WHERE uid=%d", $uid_dst),
						"USRCLONE-3", false, true);
					if ($qa->isok)
					{
						$qd = new myquery($fo, sprintf("INSERT INTO favoritas_usr ".
							"(SELECT %d,favorita_id,orden FROM favoritas_usr ".
							"WHERE uid=%d)", $uid_dst, $uid_src), "USRCLONE-4", false, true);
						if ($qd->isok)
						{
							$fo->ok("Usuario clonado");
							$isok = true;
						}
					}
				}
			}
			if ($isok) mysql_query("COMMIT");
			else
			{
				mysql_query("ROLLBACK");
				$fo->err("USRCLONE-10", "Transacci&oacute;n reversada");
			}
			mysql_query('SET autocommit=1');
		}
	}
}
$fo->par(3);
$fo->lnk(sprintf("usuario_acceso.php?valor=%d", $uid_dst), "Volver al formulario anterior");
$fo->parx();
$fo->footer();
$fo->show();
?>