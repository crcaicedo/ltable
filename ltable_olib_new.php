<?php
require_once "ltable_olib.php";

function ltnew_preprocess($fo, $lto)
{
	$isok = true;
	
	if ($lto->tabla == 'crm_soldes')
	{
		$lto->fa['cnfno']->assign(rand(100000,999999));
		$lto->fa['locales']->assign("(ninguno)"); 
	}
	if ($lto->tabla == 'reserva_retiros')
	{
		$lto->fa['cnfno']->assign(rand(100000,999999));
		
		$isok = false;
		$q = "SELECT COUNT(*) AS cnt FROM crm_soldes WHERE estatus=1";
		if (($res = mysql_query($q)) !== false)
		{
			if (($row = mysql_fetch_assoc($res)) !== false)
			{
				if ($row['cnt'] > 0) $isok = true;
				else $fo->parc("No hay solicitudes de desistimiento que posean " .
					"estatus <u>abierto</u>.");
			}
			mysql_free_result($res);
		}
		else $fo->qerr("LTNEW-10");
	}
	
	return $isok;
}


$fo = new lt_form();
$para = array('tabla', 'campo', 'valor');
if (parms_isset($para, 2))
{
	if ($fo->dbopen())
	{
		$lto = new ltable();
		$tabla = $_REQUEST['tabla'];
		$valor = $_REQUEST['valor'];
		$campo = $_REQUEST['campo'];
		$urlret = sprintf("ltable_olib_main.php?tabla=%s", $tabla);
		if (isset($_REQUEST['cret'])) $urlret = $_REQUEST['cret'];
		if ($lto->load($tabla))
		{
			if ($fo->usrchk($lto->form_id + 4, $lto->form_rw) != USUARIO_UNAUTH)
			{
				if (LT_FULL_HEADER) $fo->encabezado(true); else $fo->encabezado_base();
				$fo->js($tabla . ".js");
				if ($lto->load_record('', 0, true))
				{
					if ($valor != 0) $lto->fa[$campo]->v = $valor;

					if (ltnew_preprocess($fo, $lto))
					{
						$fo->buf .= $lto->editar($urlret);
					}
					$fo->hr();
					$fo->par(3);
					$fo->lnk($urlret, "Volver al formulario anterior");
					$fo->parx();
				}
				else $fo->buf .= $lto->render_error();
			}
		}
	}
}
$fo->footer();
$fo->show();
?>
