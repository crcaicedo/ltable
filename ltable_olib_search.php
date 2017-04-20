<?php
require_once "ltable_olib.php";

if (($fo = lt_form::procesar($_REQUEST['tabla'], 'tabla,c;valor,c;flkey,c;flaux,c;tblaux,c;orden,c', TRUE, LT_FORM_PROC, LT_MODOFF_INICIO)))
{
	$p = &$fo->p;
	$filtro = lt_macros($_REQUEST['filtro']);

	$lto = new ltable();
	if ($lto->load($p->tabla))
	{
		$fo->encabezado();
		$fo->hdr(sprintf("Buscar %s - \"%s\"", strtoupper($p->tabla), $p->valor));

		$flax = array ();
		$flax_c = 0;
		$q = "SELECT * FROM ltable_search_fl WHERE tabla='$p->tabla' ORDER BY orden";
		if (($qa = myquery::q($fo, $q, 'LTSEARCH-1', TRUE, FALSE, MYASSOC)))
		{
			foreach ($qa->a as $row)
			{
				$cn = $row['n'];
				$flax[$cn] = $row;
				$flax_c++;
			}
		}

		$tblx = array ();
		$tblx_c = 0;
		$q = "SELECT * FROM ltable_search_src WHERE tabla='$p->tabla' ORDER BY orden";
		if (($qb = myquery::q($fo, $q, 'LTSEARCH-2', TRUE, FALSE, MYASSOC)))
		{
			foreach ($qb->a as $row)
			{
				$tblx[$tblx_c++] = $row;
			}
		}

		if ($flax_c > 0 && $tblx_c > 0)
		{
			$campos = '';
			foreach ($flax as $fl)
			{
				if (enblanco($fl['src'])) $fl['src'] = $fl['n'];
				if (enblanco($fl['tbl'])) $fl['tbl'] = $p->tabla;
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

			$addbt = new lt_addbuttons($fo, $lto);

			if (!empty($filtro))
			{ 
				$filtro = sprintf("WHERE %s", lt_macros($filtro));
			}

			$q = sprintf("SELECT %s FROM %s %s %s " .
				"HAVING UPPER(%s) REGEXP \"%s\" ORDER BY %s",
				$campos, $p->tabla, $joins, $filtro, $p->flaux, $p->valor, $p->orden);
			//$fo->parc($q);
			if (($qc = myquery::q($fo, $q, 'LTSEARCH-3', FALSE, FALSE, MYASSOC)))
			{
				if ($qc->sz > 0)
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
					foreach ($qc->a as $row)
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
							if ($cn == $p->flkey)
							{
								$keyvalue = $vv;
								$haykey = true;
							}
						}
						
						if ($haykey)
						{
							$fo->td(3, 0, $tdcl);
							$fo->frm($lto->edit_action);
							$fo->hid("tabla", $p->tabla);
							$fo->hid("campo", $p->flkey);
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
			}
			else $fo->qerr("LTSEA-3");
		}
		else $fo->parc("&laquo;No se han definido p&aacute;rametros de b&uacute;squeda&raquo;");
	}

	$fo->hr();
	$fo->par();
	$fo->lnk("ltable_olib_main.php?tabla=$p->tabla", "Volver al formulario principal");
	$fo->parx();
}
?>