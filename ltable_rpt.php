<?php
require_once "ltable_olib.php";
require_once "ltable_rptfn.php";

$fo = new lt_form();
$p = new parametros("rpt,s:rpt_name");
if ($p->isok)
{
	if ($fo->dbopen())
	{
		$rpt = null;
		if (ltrpt_load($fo, $p->rpt_name, $rpt))
		{
			if ($fo->usrchk($rpt['rpt_id'], $rpt['usertype_id']) != USUARIO_UNAUTH)
			{
				$fo->encabezado();
				
				ltable_blank($rpt, true);
				ltable_editar($rpt, true, false, 'ltrptfrm', 'ltable_rpt_do.php', 
						'ltable_edit.js', true, $fo->buf);
			}
		}
	}
	$fo->volver("menu.php", "Volver al menu principal");
}
$fo->footer();
$fo->show();
?>