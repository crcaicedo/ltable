<?php
require_once "ltable_olib.php";
require_once "crm_fn.php";
require_once "ipc_fn.php";

function ltable_main_preprocess(lt_form $fo, ltable $lto)
{
	$isok = true;
	
	if ($lto->tabla == 'ipc') ipc_resumen($fo);
	
	return $isok;
}

function ltable_main_postprocess(lt_form $fo, ltable $lto)
{
	$isok = true;
	
	if ($lto->tabla == 'sms_listas')
	{
		$fo->par(3);
		$fo->lnk("sms_promo_import.php","Importar...");
		$fo->parx();
	}
	
	return $isok;
}

function ltable_search(ltable $lto, lt_form $fo)
{
	$st1 = 'border:1px solid black;';
	$qa = new myquery($fo, sprintf("SELECT title, tblaux, flaux, flkey, orden, filtro " .
		"FROM ltable_search WHERE tabla='%s' ORDER BY ordenv", $lto->tabla), "LTMSE-1", false);
	if ($qa->sz > 0)
	{
		$txt0 = new lt_textbox();
		$txt0->n = "valor";
		$txt0->t = 'c';
		$txt0->l = 100;
		$txt0->vcols = 30;
		$txt0->funcion = "UE";
			
		$fo->tbl(3, -1, "2%", "stdpg4");
		foreach ($qa->a as $ox)
		{
			$fo->frm("ltable_olib_search.php");
			$fo->tr();
			$fo->th($ox->title);
			$fo->td(3);
			$fo->hid('tabla', $lto->tabla);
			$fo->hid('tblaux', $ox->tblaux);
			$fo->hid('flaux', $ox->flaux);
			$fo->hid('flkey', $ox->flkey);
			$fo->hid('orden', $ox->orden);
			$fo->hid('filtro', $ox->filtro);
			$txt0->render($fo->buf);
			$fo->sp();
			$fo->sub("Buscar");
			$fo->trx();
			$fo->frmx();
		}
		$fo->tblx();
	}
}

function lt_main_query(ltable $lto, lt_form $mf)
{
	$q = plantilla_parse($lto->sql_expr, $_SESSION);
	//echo $q;
	
	$abto = new lt_addbuttons($mf, $lto->tabla, $lto->fl0);
	
	$st1 = 'border:1px solid black;';
	if (($res = mysql_query($q)) !== false)
	{
		$mf->tbl(3, -1, '2%', '', 'border-collapse:collapse;'.$st1);

		$mf->tr();
		$tha = explode(",", $lto->sql_ftitles);
		foreach ($tha as $stmp) $mf->th($stmp, 3, 0, "", $st1);
		$mf->th('-', 3, 0, '', $st1);
		$abto->render_titles($st1);
		///foreach ($abto->a as $bt) $mf->th('-', 3, 0, '', $st1);
		
		// TODO: if ($lto->es_asunto) $mf->th("Asunto");
		$mf->trx();
	
		$recc = 0;
		while (($row = mysql_fetch_assoc($res)) !== false)
		{
			$impar = $recc % 2;
			$tdcl = "tdmain$impar";
			$mf->tr();

			$flc = 0;
			foreach ($row as $fv)
			{
				$sfl = '';
				$fmt = substr($lto->sql_format, $flc, 1);
				//echo "<p>$fmt,$fv</p>";
				switch ($fmt)
				{
					case 'n':
						$vv = nes($fv);
						$ali = 2;
						break; 
					case 'i':
						$vv = number_format($fv, 0, ',', '.');
						$ali = 2;
						break;
					case 's':
					case 'c':
					case 'd':
						$vv = $fv;
						$ali = 3;
						break;
				}
				if (strpos("n,i,s,c,d", $fmt) !== false)
				{
					if ($flc == 0)
					{
						$lnk = sprintf("<a href=\"javascript:ltlog_view('%s','%s');\">%s</a>",
							$lto->tabla, $fv, $vv);
						$mf->tdc($lnk, $ali, 0, $tdcl);
					}
					else $mf->tdc($vv, $ali, 0, $tdcl);
				}
				$flc++;
			}
			
			$valor = $row[$lto->fl0];
			
			$mf->td(3, 0, $tdcl);
			$mf->frm($lto->edit_action);
			$mf->hid('tabla', $lto->tabla);
			$mf->hid('campo', $lto->fl0);
			$mf->hid('valor', $valor);
			$mf->hid('nuevo', '0');
			$mf->sub('Editar');
			$mf->frmx();
			$mf->tdx();
			
			$abto->render($valor, $tdcl);
			
			if ($lto->es_asunto)
			{
				// TODO:
				//$asunt = new asunto(0, false, $lto->tabla, $lto->fl0, $valor);
				//$mf->td();
				//$asunt->menu($mf);
				//$mf->tdx();
			}
			
			$mf->trx();
			$recc++;
		}
		$mf->tblx();
		mysql_free_result($res);
	}
	else $mf->qerr("LTMAIN-2");	
}

$para = array("tabla");
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$tabla = $_REQUEST['tabla'];
	if ($fo->dbopen())
	{
		$lto = new ltable();
		if ($lto->load($tabla))
		{
			if ($fo->usrchk($lto->form_id, $lto->form_ro) != USUARIO_UNAUTH)
			{
				$fo->encabezado();
				if ($lto->es_asunto) $fo->js("crm_fn.js");
				
				$fl0 = $lto->fa[$lto->fl0];
				$fl1 = $lto->fa[$lto->fl1];

				$fo->hdr($lto->title);

				ltable_search($lto, $fo);
				
				if (ltable_main_preprocess($fo, $lto))
				{
					$fo->tbl();
					$fo->frm($lto->new_action);
					$fo->hid('tabla', $lto->tabla);
					$fo->hid('campo', $fl0->n);
					$fo->hid('valor', '0');
					$fo->hid('nuevo', '1');
					$fo->td();
					$fo->sub('Nuevo registro');
					$fo->tdx();
					$fo->frmx();
					$fo->tblx();
					
					if ($lto->sql_custom == 1)
					{
						lt_main_query($lto, $fo);
					}
					else
					{
						$sor = enblanco($lto->fl_order) ? '': " ORDER BY $lto->fl_order";
						$lto->sql_expr = "SELECT $fl0->n,$fl1->n FROM  $tabla $sor";
						$lto->sql_ftitles = sprintf("%s,%s", $fl0->title, $fl1->title);
						$lto->sql_format = sprintf("%s%s", $fl0->t, $fl1->t);			
						
						lt_main_query($lto, $fo);
					}
					ltable_main_postprocess($fo, $lto);
				}
			}
		}
		else $fo->parc("&laquo;Revisar definici&oacute;n de tabla&raquo;", 3, "cursiva");
	}
}
else $fo->menuprinc();
$fo->footer();
$fo->show();
?>
