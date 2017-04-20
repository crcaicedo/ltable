<?php
require_once "ltable_olib.php";
require_once 'ltable_olib_edit_site.php';

$para = array('tabla', 'campo', 'valor');
$fo = new lt_form();
if (parms_isset($para,2))
{
	$tabla = $_REQUEST['tabla'];
	if (isset($_REQUEST['ro'])) $ro = $_REQUEST['ro'] == 1; else $ro = false;
	$urlret = "ltable_olib_main.php?tabla=".$tabla;
	if (isset($_REQUEST["cret"])) $urlret = $_REQUEST["cret"];
	$nodelete = $noheader = false;
	if (isset($_REQUEST["nodel"])) $nodelete = true;
	if (isset($_REQUEST["nohdr"])) $noheader = true;

	if ($fo->dbopen())
	{
		$lto = new ltable();
		if ($lto->load($tabla))
		{
			$tipoac = $ro ? $lto->form_ro: $lto->form_rw;
			
			if ($fo->usrchk($lto->form_id + 1, $tipoac) != USUARIO_UNAUTH)
			{
				if ($noheader) $fo->encabezado_base();
				else
				{
					if (LT_FULL_HEADER) $fo->encabezado(true); else $fo->encabezado_base();
				}
				$fo->js($tabla . ".js");
				if ($lto->load_record('', $_REQUEST['valor'], false))
				{
					if (ltedit_preprocess($fo, $lto))
					{
						if ($ro) $lto->rdonly = true;
						if ($nodelete) $lto->allowdel = false;
						$fo->buf .= $lto->editar($urlret);
						ltedit_postprocess($fo, $lto);
					}
				}
				else $fo->buf .= $lto->render_error();
			}
			
			if (isset($_REQUEST['popup']))
			{
				if ($_REQUEST['popup'] == 1)
				{
					$fo->par();
					$fo->lnk("javascript:window.close();", "Cerrar ventana");
					$fo->parx();					
				}
			}
			else
			{
				$fo->hr();
				$fo->par();
				$fo->lnk($urlret, "Volver al formulario anterior");
				$fo->parx();
			}
		}
		else $fo->buf .= $lto->render_error();
	}
}
else $fo->menuprinc();
$fo->footer();
$fo->show();
?>
