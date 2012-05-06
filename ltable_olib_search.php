<?php
require_once "mprsfn.php";
require_once "ltable_olib.php";

$fo = new lt_form();
$para = array('tabla', 'valor', 'flkey', 'flaux', 'tblaux', 'orden');
if (parms_isset($para,2))
{
	$tabla = $_REQUEST['tabla'];
	$flkey = $_REQUEST['flkey'];
	$flaux = $_REQUEST['flaux'];
	$tblaux = $_REQUEST['tblaux'];
	$filtro = lt_macros($_REQUEST['filtro']);
	$orden = $_REQUEST['orden'];
	$valor = strtoupper($_REQUEST['valor']);
	if (mprs_dbcn())
	{
		$lto = new ltable();
		if ($lto->load($tabla))
		{
			if ($fo->usrchk($lto->form_id, $lto->form_ro) != USUARIO_UNAUTH)
			{
				$fo->encabezado();
				$fo->hdr(sprintf("Buscar %s - \"%s\"", strtoupper($tabla), $valor));

				$flax = array ();
				$flax_c = 0;
				$q = "SELECT * FROM ltable_search_fl WHERE tabla='$tabla' ORDER BY orden";
				if (($res = mysql_query($q)) !== false)
				{
					while (($row = mysql_fetch_assoc($res)) !== false)
					{
						$cn = $row['n'];
						$flax[$cn] = $row;
						$flax_c++;
					}
					mysql_free_result($res);
				}
				else $fo->qerr("LTSEA-1");

				$tblx = array ();
				$tblx_c = 0;
				$q = "SELECT * FROM ltable_search_src WHERE tabla='$tabla' ORDER BY orden";
				if (($res = mysql_query($q)) !== false)
				{
					while (($row = mysql_fetch_assoc($res)) !== false)
					{
						$tblx[$tblx_c++] = $row;
					}
					mysql_free_result($res);
				}
				else $fo->qerr("LTSEA-2");

				if ($flax_c > 0 && $tblx_c > 0)
				{
					$campos = '';
					foreach ($flax as $fl)
					{
						if (enblanco($fl['src'])) $fl['src'] = $fl['n'];
						if (enblanco($fl['tbl'])) $fl['tbl'] = $tabla;
						if ($fl['src'] != $fl['n'])
							$campos .= sprintf(",%s AS %s", $fl['src'], $fl['n']);
						else
							$campos .= sprintf(",%s.%s", $fl['tbl'], $fl['n']);
					}
					$campos = substr($campos, 1);

					$joins = '';
					foreach ($tblx as $tbl)
					{
						if (enblanco($tbl['alias']))
						{
							$joins .= sprintf(" LEFT JOIN %s ON %s.%s=%s.%s",
								$tbl['tblaux'], 
								$tbl['tblaux'], $tbl['flaux'],
								$tbl['tblmain'], $tbl['flmain']);
						}
						else
						{
							$joins .= sprintf(" LEFT JOIN %s AS %s ON %s.%s=%s.%s",
								$tbl['tblaux'], $tbl['alias'], 
								$tbl['alias'], $tbl['flaux'],
								$tbl['tblmain'], $tbl['flmain']);
						}
					}

					$addbt = new lt_addbuttons($fo, $tabla, $flkey);

					if (!empty($filtro))
					{ 
						$filtro = sprintf("WHERE %s", lt_macros($filtro));
					}
					$q = sprintf("SELECT %s FROM %s %s %s " .
						"HAVING UPPER(%s) REGEXP \"%s\" ORDER BY %s",
						$campos, $tabla, $joins, $filtro, $flaux, $valor, $orden);
					//$fo->parc($q);
					if (($res = mysql_query($q)) !== false)
					{
						if (mysql_num_rows($res) > 0)
						{
							$fo->tbl(3, 0, '2%', 'stdpg4');
							
							$fo->tr();
							foreach ($flax as $fl)
							{
								if (!$fl['hidden']) $fo->th($fl['title']);
							}
							$fo->th('-');
							$addbt->render_titles();
							$fo->trx();
							
							$recno = 0;
							while (($row = mysql_fetch_assoc($res)) !== false)
							{
								$impar = $recno % 2;
								$tdcl = "tdmain$impar";
								
								$fo->tr();
								$haykey = false;
								
								foreach ($flax as $fl)
								{
									$cn = $fl['n'];
									$vv = $row[$cn];
									if (!$fl['hidden'])
									{
										switch ($fl['t'])
										{
											case 'c':
												$fo->tdc($vv, 0, 0, $tdcl);
												break;
											case 'i':
												$fo->tdc(number_format($vv, 0, ',', '.'), 2, 0, $tdcl);
												break;
											case 'n':
												$fo->tdc(nes($vv), 2, 0, $tdcl);
												break;
											case 'd':
												$fo->tdc(dtoc(fecha_from($vv)), 3, 0, $tdcl);
										}
									}
									if ($cn == $flkey)
									{
										$keyvalue = $vv;
										$haykey = true;
									}
								}
								
								if ($haykey)
								{
									$fo->td(3, 0, $tdcl);
									$fo->frm($lto->edit_action);
									$fo->hid("tabla", $tabla);
									$fo->hid("campo", $flkey);
									$fo->hid("valor", $keyvalue);
									$fo->hid("nuevo", "0");
									$fo->sub("Editar");
									$fo->frmx();
									$fo->tdx();
									$addbt->render($keyvalue);
								}
								$fo->trx();
								$recno++;
							}
							
							$fo->tblx();
						}
						else $fo->parc("&laquo;No se han encontrado registros coincidentes.&raquo;");
						mysql_free_result($res);
					}
					else $fo->qerr("LTSEA-3");
				}
				else $fo->parc("&laquo;No se han definido p&aacute;rametros de b&uacute;squeda&raquo;");
			}
		}
		mysql_close();

		$fo->hr();
		$fo->par();
		$fo->lnk("ltable_olib_main.php?tabla=$tabla", "Volver al formulario principal");
		$fo->parx();
	}
}
else $fo->menuprinc();
$fo->footer();
$fo->show();
?>