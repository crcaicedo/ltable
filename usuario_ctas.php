<?php
require_once "ltable_olib.php";

$para = array("valor");
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$pid = $_SESSION['pid'];
	$xuid = $_REQUEST['valor']+0;
	if ($fo->dbopen())
	{
		if ($fo->usrchk(142, 2) !== USUARIO_UNAUTH)
		{
			$fo->encabezado();
			$fo->hdr("Cuentas asignadas a usuario", 4);
			$qa = new myquery($fo, sprintf("SELECT nombres, apellidos, name ".
				"FROM usuarios a ".
				"WHERE uid=%d", $xuid), "USRCTAS-1");
			if ($qa->isok)
			{
				$fo->tbl(3,-1,"2%","stdpg4");
				$fo->tr();
				$fo->tdc(sprintf("%s %s", $qa->r->nombres, $qa->r->apellidos));
				$fo->tdc($qa->r->name, 0, 0, "negrita");
				$fo->trx();
				$fo->tblx();
				$fo->br();
				
				$ctaa = array();
				$qb = new myquery($fo, sprintf("SELECT GROUP_CONCAT(cta_id SEPARATOR ',') ctals ".
					"FROM cuentas_usr WHERE uid=%d", $xuid), "USRCTAS-2");
				if ($qb->isok)
				{
					$ctaa = split(",",$qb->r->ctals);
				}
				
				$qc = new myquery($fo, sprintf("SELECT cta_id, RIGHT(numero, 4) no, b.nombre, titular ".
						"FROM cuentas a ".
						"LEFT JOIN bancos b ON a.banco_id=b.banco_id ".
						"WHERE estatus!=0 AND proyecto_id=%d ".
						"ORDER BY titular,nombre,no", $pid), "USRCTAS-3");
				if ($qc->isok)
				{
					$nr = 0;
					$fo->parc("Seleccione cuentas a asignar");
					$fo->frm("usuario_ctas_do.php");
					$fo->tbl(3,0);
					foreach ($qc->a as $cta)
					{
						$isa = 0;
						if (array_search($cta->cta_id, $ctaa, true) !== false) $isa = 1;
						$fo->tr();
						$fo->td();
						$fo->hid(sprintf("cta_id%d", $nr), $cta->cta_id);
						$fo->chk(sprintf("chk%d", $nr), $isa, '', sprintf("%s <b>%s</b> %s", 
							$cta->nombre, $cta->no, $cta->titular));
						$nr++;
					}
					$fo->tr();
					$fo->td(3,2);
					$fo->hid("nr", $nr);
					$fo->hid("xuid", $xuid);
					$fo->hid("xpid", $pid);
					$fo->sub("Guardar cambios");
					$fo->trx();
					$fo->tblx();
					$fo->frmx();
				}
			}
		}
	}
}
$fo->par(3);
$fo->lnk("ltable_olib_main.php?tabla=usuarios","Volver al formulario principal");
$fo->parx();
$fo->footer();
$fo->show();
?>