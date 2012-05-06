<?php
require_once "ltable_olib.php";
require_once "ltable_rptfn.php";

$fo = new lt_form();
$para = array("rpt");
if (parms_isset($para, 2))
{
	$rpt_name = $_REQUEST['rpt'];
	if (mprs_dbcn())
	{
		$rpt = null;
		if (ltrpt_load($fo, $rpt_name, $rpt))
		{
			if ($fo->usrchk($rpt['rpt_id'], $rpt['usertype_id']) != USUARIO_UNAUTH)
			{
				$fo->encabezado();
				
				ltable_blank($rpt, true);
				ltable_editar($rpt, true, false, 'ltrptfrm', 'ltable_rpt_do.php', 'ltable_edit.js', true, $fo->buf);
			}
		}
		mysql_close();
	}
	
	$fo->hr();
	$fo->par(3);
	$fo->lnk("Volver al menu principal", "menu.php");
	$fo->parx();
}
else $fo->menuprinc();
$fo->footer();
$fo->show();
?>