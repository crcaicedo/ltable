<?php

/* Rutinas para manejo de formularios
 * (solo tablas pequeñas)
 *
 * crcr, 2007-09-20
 *
 * */

/* 
 * FLA = {FL  .. FL }
 *          0      n
 *
 * FL = { N:name, DF:default value, V:actual value, T:data type, TITLE: descriptive title }
 *
 * DPA = {DP  .. DP }
 *          0      n
 *
 * DP = { TBL:tabla asociada, FL_N: nombre campo clave, FL_T: tipo campo clave }
 *
 * LTA: array de descripcion de tabla
 *
 * LTA = { TABLA:nombre de la tabla, FORM_ID:id numerico del formulario asociado (para chequear privilegios)
 * FLA: array de descripcion de campos,
 * DPA: array de tablas dependientes (para chequear claves foraneas)
 * }
 * */

define('LT_TEXTBOX', 0);
define('LT_CHECKBOX', 1);
define('LT_LISTBOX', 2);
define('LT_EDITBOX', 3);
define('LT_SEPARATOR', 4);
define('LT_PASSWORD', 5);
define('LT_BUTTON', 6);

function ltable_addbt_load(&$addbt, $tabla, $clave)
{
	$isok = false;
	$addbt['sz'] = 0;
	$addbt['a'] = array();
	$addbt['tabla'] = $tabla;
	$addbt['clave'] = $clave;
	$query = sprintf("SELECT * FROM ltable_addbuttons WHERE tabla='%s' ORDER BY orden", $tabla);
	if (($res = mysql_query($query)) !== false)
	{
		while (($row = mysql_fetch_assoc($res)) !== false)
		{
			$addbt['a'][$addbt['sz']] = array('app'=>$row['app'], 'caption'=>$row['caption']);
			$addbt['sz']++;
		}
		mysql_free_result($res);
		$isok = true;
	}
	return $isok;
}

function ltable_addbt_show(&$addbt, $valor)
{
	$slna = '';
	if ($addbt['sz'] > 0)
	{
		foreach ($addbt['a'] as $butt)
		{
			$slna .= "<td align=center>";
			$slna .= sprintf("<form action=\"%s\" method=\"post\">", $butt['app']);
			$slna .= sprintf("<input type=\"hidden\" name=\"tabla\" value=\"%s\">", $addbt['tabla']);
			$slna .= sprintf("<input type=\"hidden\" name=\"campo\" value=\"%s\">", $addbt['clave']);
			$slna .= sprintf("<input type=\"hidden\" name=\"valor\" value=\"%s\">", $valor);
			$slna .= sprintf("<input type=\"submit\" value=\"%s\">", $butt['caption']);
			$slna .= "</form>";
			$slna .= "</td>";
		}
	}
	return $slna;
}

function ltable_autovar($fl, &$vv)
{
	$nc = $fl['n'];
	if ($fl['autovar'])
	{
		if ($nc == 'uid')
		{
			$vv = $_COOKIE['uid'];
		}
		if ($nc == 'proyecto_id')
		{
			$vv = $_COOKIE['pid'];
		}
		if ($nc == 'ipaddr')
		{
			$vv = $_SERVER['REMOTE_ADDR'];
		}
	}
	if ($fl['postvar'] == 1)
	{
		if (isset($_POST[$nc])) $vv = $_POST[$nc];
	}
}

function ltable_info($tabla)
{
	$lt = null;
	$query = sprintf("SELECT * FROM ltable WHERE tabla='%s'", $tabla);
	if (($res = mysql_query($query))!==false)
	{
		if (($row = mysql_fetch_assoc($res)) !== false)
		{
			$lt = $row;
			$lt['fla'] = array();
			// info. campos
			$query = sprintf("SELECT n,t,l,pd,title,dfc,dfn,dfd,dft,dfh,dfi,dfb,dfm,ctrl_type,ls_tbl,ls_fl_key,ls_fl_desc,ls_fl_order,ls_custom,ls_custom_new,vcols,vrows,dt_auto,ro,hidden,valid_fn,valid_parms,mascara,funcion,esdato,enabled,init_fn,init_parms,autovar,postvar,onkey_fn,onkey_parms,isup FROM ltable_fl WHERE tabla='%s' ORDER BY orden", $tabla);
			if (($res2 = mysql_query($query))!==false)
			{
				while (($row = mysql_fetch_assoc($res2)) !== false)
				{
					// data source properties
					$fl['n'] = $row['n'];
					$fl['t'] = strtolower($row['t']);
					$fl['l'] = $row['l'];
					$fl['pd'] = $row['pd'];
					$dfnm = 'df' . $fl['t'];
					$fl['df'] = $row[$dfnm];
					$fl['v'] = $fl['df'];
					// layout properties
					$fl['title'] = $row['title'];
					$fl['vcols'] = $row['vcols'];
					$fl['vrows'] = $row['vrows'];
					$fl['hidden'] = $row['hidden'];
					$fl['ro'] = $row['ro'];
					// data properties
					$fl['ctrl_type'] = $row['ctrl_type'];
					$fl['ls_tbl'] = $row['ls_tbl'];
					$fl['ls_custom'] = $row['ls_custom'];
					$fl['ls_custom_new'] = $row['ls_custom_new'];
					$fl['ls_fl_key'] = $row['ls_fl_key'];
					$fl['ls_fl_desc'] = $row['ls_fl_desc'];
					$fl['ls_fl_order'] = $row['ls_fl_order'];
					$fl['dt_auto'] = $row['dt_auto'];
					$fl['valid_fn'] = $row['valid_fn'];
					$fl['valid_parms'] = $row['valid_parms'];
					$fl['mascara'] = $row['mascara'];
					$fl['funcion'] = $row['funcion'];
					$fl['esdato'] = $row['esdato'];
					$fl['enabled'] = $row['enabled'];
					$fl['init_fn'] = $row['init_fn'];
					$fl['init_parms'] = $row['init_parms'];
					$fl['onkey_fn'] = $row['onkey_fn'];
					$fl['onkey_parms'] = $row['onkey_parms'];
					$fl['autovar'] = $row['autovar'];
					$fl['postvar'] = $row['postvar'];
					$fl['isup'] = $row['isup'];
					
					$lt['fla'][$fl['n']] = $fl;
				}
				mysql_free_result($res2);
			}
			else queryerror(10001);
			
			// orden visual
			$cnt = 0;
			$lt['ordenv'] = array();
			if (($res2 = mysql_query("SELECT n FROM ltable_fl WHERE tabla='$tabla' ORDER BY ordenv"))!==false)
			{
				while (($row = mysql_fetch_row($res2)) !== false)
				{
					$lt['ordenv'][$cnt++] = $row[0];
				}
				mysql_free_result($res2);
			}
			else queryerror(10011);
			
			// info. dependencias
			$lt['ndeps'] = 0;
			$lt['dpa'] = array();
			if (($res2 = mysql_query("SELECT tbl,fl_n,fl_t,tbl_fields,tbl_titles FROM ltable_dp WHERE tabla='$tabla'"))!==false)
			{
				$ndx = 0;
				while (($row = mysql_fetch_assoc($res2)) !== false)
				{
					//$dp = array('tbl'=>$row[0], 'fl_n'=>$row[1], 'fl_t'=>$row[2]);
					$lt['dpa'][$ndx++] = $row;
				}
				$lt['ndeps'] = $ndx;
				mysql_free_result($res2);
			}
			else queryerror(10002);
		}
		mysql_free_result($res);
	}
	else queryerror(10000);
	
	return $lt;
}

function ltable_deps($lta, $valor, $recop='U')
{
	if ($lta['ndeps'] > 0)
	{
		$ndep = 0;
		$isok = false;
		$allok = true;
		$dpa = $lta['dpa'];
		$umethod = $lta['umethod'];
		
		$fla = $lta['fla'];
		$fl0 = $fla[$lta['fl0']];
		$fl1 = $fla[$lta['fl1']];
		
		$nupd = 0;
		if ($umethod == 1)
		{
			foreach ($fla as $fl)
			{
				if ($fl['isup'] && ($fl['n'] != $fl0['n'])) $nupd++;
			}
		}
		
		// tracking dependencies (foreign keys constraints) and warn about (user have to manually delete all them)
		echo "<p align=\"center\">Chequeando dependencias...</p>";
		
		$query = sprintf("SELECT %s,%s FROM %s WHERE %s=%d", $fl0['n'], $fl1['n'], $lta['tabla'], $fl0['n'], $valor);
		if (($res = mysql_query($query))!==false)
		{
			echo "<table border=1 align=center cellpadding=2%>".
			"<tr><th>". $fl0['title'] ."</th><th>". $fl1['title'] ."</th></tr>";
			if (($row = mysql_fetch_row($res))!== false)
			{
				echo "<tr><td>". $row[0] ."</td><td>". $row[1] ."</td></tr>";
			}
			echo "</table>";
			mysql_free_result($res);
		}
		else queryerror(11100);
		
		foreach ($dpa as $dp)
		{
			$query = sprintf("SELECT %s FROM %s ", $dp['tbl_fields'], $dp['tbl']);
			if (strpos('nib', $dp['fl_t']) !== false)
			{
				$query .= sprintf("WHERE %s=%d", $dp['fl_n'], $valor);
			}
			else
			{
				$query .= sprintf("WHERE %s='%s'", $dp['fl_n'], $valor);
			}
			//echo $query;
			if (!($res = mysql_query($query)))
			{
				echo "<p align=\"center\"><i>Error chequeando dependencias:</i> <code>". mysql_error() ."</code></p>";
				$allok = false;
			}
			else
			{
				$nr = mysql_num_rows($res);
				if ($nr > 0)
				{
					$lin = "<p align=\"center\">Tabla relacionada <b>". $dp['tbl'] ."</b>".
						" tiene <b>". $nr ."</b> registro(s) dependiente(s) de este registro.</p>";
					$ndep++;
					$lin .= "<table border=\"1\" align=\"center\" cellpadding=\"2%\"><tr>";
					$stit = strtok($dp['tbl_titles'], ",");
					while ($stit !== false)
					{
						$lin .= "<th>". $stit ."</th>";
						$stit = strtok(",");
					}
					$lin .= "</tr>";
					while (($row = mysql_fetch_row($res)) !== false)
					{
						$lin .= "<tr>";
						foreach ($row as $rv)
						{
							$lin .= "<td>". $rv ."</td>";
						}
						$lin .= "</tr>";
					}
					$lin .= "</table>";
					echo $lin;
				}
				mysql_free_result($res);
			}
		}
		
		if ($allok)
		{
			if ($ndep == 0)
			{
				echo "<p align=\"center\">Dependencias OK.</p>";
				$isok = true;
			}
			else
			{
				if ($recop == 'U')
				{
					if ($umethod == 0)
					{
						echo "<p align=\"center\"><i>Advertencia:</i> <b>". $ndep ."</b> dependencias.";
						echo "<p align=\"center\"><b>IMPOSIBLE ACTUALIZAR ESTE REGISTRO.</b><br><i>Elimine manualmente todos los registros dependientes en ";
						echo "las tablas relacionadas a esta, antes actualizar este registro.</i></p>";
					}
					if ($umethod == 1)
					{
						echo "<p align=\"center\"><i>Advertencia:</i> Registro tiene <b>". $ndep ."</b> dependencias.</p>";
						if ($nupd > 0) $isok = true; else echo "<p align=\"center\">Error:  No existen campos actualizables.</p>";
					}
				}
				if ($recop == 'D')
				{
					echo "<p align=\"center\"><i>Advertencia:</i> <b>". $ndep ."</b> dependencias.";
					echo "<p align=\"center\"><b>IMPOSIBLE BORRAR ESTE REGISTRO.</b><br><i>Elimine manualmente todos los registros dependientes en ";
					echo "las tablas relacionadas a esta, antes de borrar este registro.</i></p>";
				}
			}
		}
	}
	else $isok = true;
	
	return $isok;
}

function lt_validfn($fl)
{
	$sval = '';
	if (!enblanco($fl['valid_fn']))
	{
		$sval = " onchange=\"". $fl['valid_fn'] .'(this'; /* originalmente uso onblur() */
		if (!enblanco($fl['valid_parms'])) $sval .= ','. $fl['valid_parms'];
		if (!enblanco($fl['funcion'])) $sval .= ") && lt_execfn(this,'". $fl['funcion']."'";
		$sval .= ")\"";
	}
	return $sval;
}

function lt_onkeyfn(&$fl)
{
	$fn2 = "";
	if (!enblanco($fl['onkey_fn']))
	{
		if (enblanco($fl['onkey_parms']))
		{
			$fn2 = sprintf("%s(this,event);", $fl['onkey_fn']);
		}
		else
		{
			$fn2 = sprintf("%s(this,event,%s);", $fl['onkey_fn'], $fl['onkey_parms']);
		}
	}
	$fn = " onkeypress=\"var key; if (window.event) { key = window.event.keyCode; } else { key = event.which; } if (key == 13) { $fn2 return false; } else { return true; }\"";
	return $fn;
}

function ltable_edit_ls(&$fl)
{
	$lati = '';
	if (!enblanco($fl['ls_custom']))
	{
		$query = $fl['ls_custom'];
	}
	else
	{
		$orden = $fl['ls_fl_order'];
		if (enblanco($orden)) $orden = $fl['ls_fl_desc'];
		$query = sprintf("SELECT %s,%s FROM %s ORDER BY %s", $fl['ls_fl_key'], $fl['ls_fl_desc'], $fl['ls_tbl'], $orden);
	}
	
	$flda = array();
	$flda_c = 0;
	$ss = strtok($fl['ls_fl_desc'], ',');
	while ($ss !== false)
	{
		$flda[$flda_c++] = $ss;
		$ss = strtok(',');
	}
	
	$flkey = $fl['ls_fl_key'];

	$query = lt_macros($query);	
	if (($res = mysql_query($query)) != false)
	{
		$sonch = '';
		if (!enblanco($fl['valid_fn']))
		{
			$sonch = " onchange=\"". $fl['valid_fn'] .'(this';
			if (enblanco($fl['valid_parms'])) $sonch .= ")\""; else $sonch .= ','. $fl['valid_parms'] .")\"";
		}
		$sdisa = $fl['enabled'] ? '': ' disabled';
		$noent = lt_onkeyfn($fl);
		$lati = "<select name=\"". $fl['n'] ."\" id=\"". $fl['n'] ."\"". $sonch . $sdisa . $noent .">";
		while (($row = mysql_fetch_assoc($res)) !== false)
		{
			$issel = ($fl['v'] == $row[$flkey]) ? ' selected': '';
			$sdesc = '';
			foreach ($flda as $fld) $sdesc .= sprintf(" %s", $row[$fld]);
			$sdesc = substr($sdesc, 1);
			if ($fl['autovar'])
			{
				if ($fl['v'] == $row[$flkey]) $lati .= sprintf("<option value=\"%s\"%s>%s</option>", $row[$flkey], $issel, $sdesc);
			}
			else $lati .= sprintf("<option value=\"%s\"%s>%s</option>", $row[$flkey], $issel, $sdesc);
		}
		$lati .= "</select>";
		mysql_free_result($res);
	}
	else $lati='<b>'.mysql_error().'</b>';
	return $lati;
}

function lt_operador($fl, $nop)
{
	$lns = '';
	$ii = 0;
	$sdisa = $fl['autovar'] == 0 ? 'disabled' : '';
	$t = $fl['t'];
	if (strstr('nibdth', $t) !== false) $opls = array('Mayor que', 'Menor que', 'Igual a', 'Mayor igual que', 'Menor igual que','Diferente a');
	if (strstr('cm', $t) !== false) $opls = array('Igual a','Contiene','Diferente a','No contiene');
	if ($fl['ctrl_type'] == LT_LISTBOX) $opls = array('Igual a','Diferente a');
	if (isset($opls))
	{
		$lns .= sprintf("<select name=\"op%s\" id=\"op%s\"%s>", $nop, $nop, $sdisa);
		foreach ($opls as $op)
		{
			$issel = $ii == $fl['defop'] ? ' selected': '';
			$lns .= sprintf("<option value=\"%s\"%s>%s</option>", $ii, $issel, $op);
			$ii++;
		}
		$lns .= "</select>";
	}
	return $lns;
}

function ltrpt_orden_add($corre, $campo, $descr)
{
    $buf = '';

    $buf .= "<tr><td align=\"center\">";
    $buf .= sprintf("<input type=\"hidden\" id=\"ord%d\" name=\"ord%d\" value=\"%s\">", $corre, $corre, $campo);
    $buf .= sprintf("<input type=\"button\" value=\"<<\" onclick=\"ltrpt_orddel(this,'%s','%s')\"></td><td>%s</td>", $campo, $descr, $descr);
    $buf .= "<td align=\"center\">";
    $buf .= sprintf("<input type=\"checkbox\" id=\"ordinv%d\" name=\"ordinv%d\"></td>", $corre, $corre);
    $buf .= "</tr>";

    return $buf;
}

function ltrpt_disp_add($corre, $campo, $descr)
{
    $buf = "<tr>";
    $buf .= "<td>$descr</td>";
    $buf .= "<td align=\"center\">";
    $buf .= sprintf("<input type=\"button\" value=\">>\" onclick=\"ltrpt_ordadd(this,'%s','%s');\"></td>", $campo, $descr);
    $buf .= "</tr>";

    return $buf;
}

function ltable_editar($lta, $nuevo, $rdonly, $frmname, $accion, $jsfuncs, $esrpt, &$trx)
{
	///$trx = '';
		
	$fla = $lta['fla'];
	$idfl = $lta['fl0'];
	$lta['esrpt'] = $esrpt;
	if (!$esrpt)
	{
		$fl0 = $fla[$idfl];
		$nrec = $nuevo ? '(nuevo)': '<i>'. $fl0['v'] .'</i>';
	}
	else
	{
		$fl0['n'] = '';
		$fl0['v'] = 0;
	}
	
	$cn0 = $fl0['n'];
	$cv0 = $fl0['v'];

	$trx .= "<script language=\"JavaScript\" src=\"sprintf.js\"></script>";
	$trx .= "<script language=\"JavaScript\" src=\"$jsfuncs\"></script>";
	
	$trx .= "<script language=\"JavaScript\">function validar_todo(elfrm){\nvar todook = true;\n";
	foreach ($lta['ordenv'] as $nord)
	{
		$fl = $fla[$nord];
		$ctrl = $fl['ctrl_type'];
		$vfn = $fl['valid_fn'];
		$vfp = $fl['valid_parms'];
		if (!enblanco($vfn) && ($ctrl == LT_TEXTBOX || $ctrl == LT_EDITBOX))
		{
			if (enblanco($vfp)) $trx .= sprintf("if (todook) { todook = %s(document.getElementById('%s')); }\n", $vfn, $fl['n']);
			else $trx .= sprintf("if (todook) { todook = %s(document.getElementById('%s'), %s); }\n", $vfn, $fl['n'], $vfp);
			//$trx .= sprintf("alert(\"%s=\"+todook);", $vfn);
		}
	}
	$trx .= "return todook;}</script>";
	
	$trx .= "<h3 align=center>".$lta['title']."</h3>";
	if (!$esrpt) $trx .= "<h4 align=center>Registro ID: $nrec</h4>";
	if ($esrpt)
		$trx .= "<form name=\"$frmname\" action=\"$accion\" method=\"post\" target=\"_blank\">";
	else
		$trx .= "<form name=\"$frmname\" action=\"$accion\" method=\"post\">";
	
	if (!$esrpt)
	{
		$trx .= "<input type=\"hidden\" name=\"tabla\" id=\"tabla\" value=\"". $lta['tabla'] ."\">".
			"<input type=\"hidden\" name=\"campo\" id=\"campo\" value=\"$cn0\">".
			"<input type=\"hidden\" name=\"valor\" id=\"valor\" value=\"$cv0\">".
			"<input type=\"hidden\" name=\"$cn0\" id=\"$cn0\" value=\"$cv0\">";
	}
	else
	{
		$trx .= "<input type=\"hidden\" name=\"rpt_name\" id=\"rpt_name\" value=\"". $lta['rpt_name'] ."\">";
	}
	
	$trx .= "<input type=\"hidden\" name=\"validando\" id=\"validando\" value=\"0\">";
	$trx .= "<input type=\"hidden\" name=\"have_js\" id=\"have_js\" value=\"0\">";
	$trx .= sprintf("<input type=\"hidden\" name=\"nuevo\" id=\"nuevo\" value=\"%d\">", $nuevo);
	$nop = 0;
	$trx .= "<table border=\"1\" align=\"center\" cellpadding=\"2%\">";
	foreach ($lta['ordenv'] as $nord)
	{
		$fl = $fla[$nord];
		$cn = $fl['n'];
		$ct = $fl['t'];
		if ((strcmp($cn, $fl0['n']) != 0))
		{
			if ($fl['hidden'])
			{
				$trx .= "<input type=\"hidden\" name=\"$cn\" id=\"$cn\" value=\"". $fl['v'] ."\">";
			}
			else
			{
				$sro = $fl['ro'] ? ' readonly':'';
				if ($esrpt) $fl['enabled'] = $fl['autovar'] != 0;
				$sdisa = $fl['enabled'] ? '': ' disabled';
				if ($fl['ctrl_type'] == LT_SEPARATOR)
				{
					$trx .= "<tr><th colspan=\"2\" style=\"{height:40px;}\">&laquo;&nbsp;". $fl['title'] ."&nbsp;&raquo;</th></tr>";
				}
				else
				{
					if ($esrpt)
					{
						$opdis = $fl['autovar'] == 0 ? ' disabled': '';
						$opchk = $fl['autovar'] == 0 ? '': ' checked';
						$trx .= sprintf("<tr>" .
							"<td><input type=\"checkbox\" name=\"up%s\" id=\"up%s\" " .
							"onchange=\"ltrpt_up(this, %s, '%s')\" alt=\"%s\"%s></td>",
							$nop, $nop, $nop, $ct, $cn, $opchk);
						$trx .= sprintf("<td><select name=\"lp%s\" id=\"lp%s\"%s>".
							"<option value=\"Y\" selected>Y</option>" .
							"<option value=\"O\">O</option></select></td>".
							"<th>%s</th>",
							$nop, $nop, $opdis, $fl['title']);
						if ($ct != 'l') $trx .= '<td>'; else $trx .= "<td colspan=\"2\">";
					}
					else $trx .= sprintf("<tr><th>%s</th><td>", $fl['title']);
				}
				if ($esrpt)
				{
					if ($ct != 'l') $trx .= lt_operador($fl,$nop) .'</td><td>';
					$nop++;
				}
				if  ($fl['ctrl_type'] == LT_CHECKBOX)
				{
					$sval = '';
					if (!enblanco($fl['valid_fn']))
					{
						$sval = " onchange=\"". $fl['valid_fn'] .'(this';
						if (!enblanco($fl['valid_parms'])) $sval .= ','. $fl['valid_parms'];
						$sval .= ")\"";
					}
					if ($fl['v']) $schk = ' checked'; else $schk = '';
					$trx .= "<input type=\"checkbox\" name=\"$cn\" id=\"$cn\"". $schk . $sro . $sdisa . $sval .">";
				}
				if  ($fl['ctrl_type'] == LT_LISTBOX)
				{
					if (!$esrpt)
					{
						if (!enblanco($fl['ls_custom']))
						{
							if ($nuevo && !enblanco($fl['ls_custom_new'])) $fl['ls_custom'] = $fl['ls_custom_new'];
						}
					}
					$trx .= ltable_edit_ls($fl);
				}
				if  ($fl['ctrl_type'] == LT_EDITBOX)
				{
					$sval = '';
					$sval = lt_validfn($fl);
					$trx .= "<textarea name=\"$cn\" id=\"$cn\" cols=\"". $fl['vcols'] ."\" rows=\"". $fl['vrows'] ."\"".
						$sro . $sdisa . $sval .">". $fl['v'] ."</textarea>";
				}
				if  ($fl['ctrl_type'] == LT_TEXTBOX || $fl['ctrl_type'] == LT_PASSWORD)
				{
					$sctrl = $fl['ctrl_type'] == LT_PASSWORD ? 'password': 'text';
					if ($ct == 'n')
					{
						$maxlen = $fl['l'] + $fl['pd'] + 1 + round($fl['l']/3,0);
						$vcols = $maxlen + 1;
					}
					else
					{
						$maxlen = $fl['l'];
						$vcols = $fl['vcols'];
					}
					$noent = lt_onkeyfn($fl);
					$sval = lt_validfn($fl);
					$trx .= "<input type=\"". $sctrl . "\" name=\"$cn\" id=\"$cn\" value=\"". $fl['v'] ."\" ".
						"maxlength=\"". $maxlen ."\" size=\"". $vcols ."\"". $sro . $sdisa . $sval . $noent .">";
				}
				else
				{
					if ($fl['ctrl_type'] != LT_SEPARATOR) $trx .= "</td></tr>";
				}
			}
		}
	}
	$trx .= "<script language=\"JavaScript\">function init_all(){ var quetal = false;";
	foreach ($lta['ordenv'] as $cn)
	{
		$fl = $fla[$cn];
		if (!enblanco($fl['init_fn']))
		{
			if (enblanco($fl['init_parms']))
			{
				$trx .= sprintf("quetal = %s(document.getElementById('%s'));\n", $fl['init_fn'], $cn);
			}
			else
			{
				$trx .= sprintf("quetal = %s(document.getElementById('%s'),%s);\n", $fl['init_fn'], $cn, $fl['init_parms']);
			}
		}
	}
	$trx .= "} document.body.onload=init_all();</script>";
	
	if (!$rdonly)
	{
		if (!$esrpt)
		{
			$scs = 2;
			$query = sprintf("DELETE FROM ltable_edit WHERE usuario_id=%d AND junky='%s' AND tabla='%s'",
				$_COOKIE['uid'], $_COOKIE['junk'], $lta['tabla']);
			if (mysql_query($query) !== false)
			{
				foreach ($fla as $fl)
				{
					$query = sprintf("INSERT INTO ltable_edit SET usuario_id=%d,junky='%s',tabla='%s',n='%s',t='%s',ctrl_type=%d,v='%s'",
						$_COOKIE['uid'], $_COOKIE['junk'], $lta['tabla'], $fl['n'], $fl['t'], $fl['ctrl_type'], $fl['t'], $fl['v']);
					if (!mysql_query($query))
					{
						$trx .= squeryerror(20002);
					}
				}
			}
			else $trx .= squeryerror(20001);
		}
		else $scs = 5;
		$nmsub = $esrpt ? 'Emitir reporte': 'Guardar cambios';
		$trx .= "<script language=\"JavaScript\">function lt_submit(){var dale=false;document.getElementById(\"validando\").value=1;dale=validar_todo(document.$frmname);document.getElementById(\"validando\").value=0;if (dale){document.getElementById(\"have_js\").value=1;document.$frmname.submit();}}</script>";
		if ($esrpt)
		{
			$trx .= "<tr><td colspan=\"$scs\"><input type=\"checkbox\" name=\"have_hlines\">&nbsp;Imprimir lineas horizontales</td></tr>";
		}
		$trx .= "<tr><td colspan=\"$scs\" align=\"center\" style=\"{height:50px;}\">";
		$trx .= "<input type=\"button\" name=\"btn_guardar\" value=\"$nmsub\" onclick=\"lt_submit()\"></td></tr>";
	}
	if ($esrpt)
	{
		$trx .= "</table></br>";
		if (($res3 = mysql_query(sprintf("SELECT orden FROM ltable_rpt WHERE rpt_name='%s'", $lta['rpt_name'])))!==false)
		{
			$deford = array();
			$defordc = 0;
			if (($row3 = mysql_fetch_row($res3))!==false)
			{
				$ss = strtok($row3[0], ",");
				while ($ss !== false)
				{
					$deford[$defordc++] = $ss;
					$ss = strtok(",");
				}
			}
			$q = sprintf("SELECT n,title FROM ltable_rpt_fl WHERE rpt_name='%s' ORDER BY ordenv", $lta['rpt_name']);
			if (($res2 = mysql_query($q))!==false)
			{
				$ndisp = mysql_num_rows($res2);
				$aord = array();
				$aordsz = 0;
				while (($row2 = mysql_fetch_assoc($res2)) !== false)
				{
					$nc = $row2['n'];
					$aord[$nc] = $row2;
					$aordsz++;
				}
				mysql_free_result($res2);
				
				for ($kk = 0; $kk < $defordc; $kk++)
				{
					$defordtit[$kk] = $aord[$deford[$kk]]['title'];
				}
				
				$trx .= "<table align=\"center\">";
				$trx .= "<tr><th colspan=\"2\" style=\"{text-align: center; border-bottom: 1px,solid; border-top: 1px,solid; height: 30px;\">Ordenaci&oacute;n</th></tr>";
				$trx .= "<tr><td valign=\"top\">";
				
				$trx .= "<table align=\"center\" border=\"1\" cellpadding=\"2%\" name=\"rpttbldisp\" id=\"rpttbldisp\">";
				$trx .= "<tr><th>Campos disponibles</th><th>-</th></tr>";
				$dispsz = 0;
				foreach ($aord as $item)
				{
					$noesta = true;
					foreach ($deford as $defo)
					{
						if (strcmp($item['n'], $defo) == 0)
						{
							$noesta = false;
						}
					}
					if ($noesta)
					{
						$trx .= ltrpt_disp_add($dispsz, $item['n'], $item['title']);
						$dispsz++;
					}
				}
				$trx .= "</table>";
				
				$trx .= "</td><td valign=\"top\">";
				
				$trx .= "<table align=\"center\" border=\"1\" cellpadding=\"2%\" name=\"rpttblord\" id=\"rpttblord\">";
				$trx .= "<tr><th>-</th><th>Ordenar por</th><th>Inverso</th></tr>";
				$ii  = $defordc - 1;
				for ($kk = 0; $kk < $defordc; $kk++)
				{
					$trx .= ltrpt_orden_add($ii--, $deford[$kk], $defordtit[$kk]);
				}
				$trx .= "<input type=\"hidden\" name=\"ordsz\" id=\"ordsz\" value=\"$defordc\">";
				$trx .= "</table>";
				$trx .= "</td></tr>";
				$trx .= "</table>";
				$trx .= "</form>";
			}
			else $trx .= squeryerror(20100) . "</form>";
			mysql_free_result($res3);
		}
		else $trx .= squeryerror(20101) . "</form>";
	}
	else $trx .= "</table></form>";
	///echo $trx;
	$trx .= "<script language=\"JavaScript\">init_all();</script>";
	
	if (!$esrpt && !$nuevo && !$rdonly && $lta['allowdel'])
	{
		$scode = substr(sprintf("%d", time()), 4);
		$trx = "<script language=\"JavaScript\">function lt_conf_del(){return confirm(\"Realmente desea borrar este registro?\");}</script>";
		$trx .= "<hr><form action=\"ltable_del.php\" method=\"post\" onsubmit=\"return lt_conf_del();\">".
			"<input type=\"hidden\" name=\"tabla\" value=\"". $lta['tabla'] ."\">".
			"<input type=\"hidden\" name=\"campo\" value=\"". $fl0['n'] ."\">".
			"<input type=\"hidden\" name=\"valor\" value=\"". $fl0['v'] ."\">".
			"<table border=1 align=center cellpadding=2%>".
			"<tr><td>C&oacute;digo de seguridad</td><td>". $scode ."</td></tr>".
			"<tr><td>Escriba el c&oacute;digo de seguridad</td><td>".
			"<input type=\"hidden\" name=\"codsec1\" value=\"". $scode ."\">".
			"<input type=\"text\" name=\"codsec2\" value=\"0\"></td></tr>".
			"<tr><td colspan=2 align=center><input type=\"submit\" value=\"Borrar registro\"></td></tr>".
			"</table></form>";
		///echo $trx;
	}
}

function ltable_edit($lta, $nuevo, $rdonly)
{
    ltable_editar($lta, $nuevo, $rdonly, 'lteditfrm', 'ltable_update.php', 'ltable_edit.js', false);
}

function ltable_blank(&$lta, $esrpt=false) // registro en blanco
{
	$fla = $lta['fla'];
	foreach ($fla as $fl)
	{
		$vv = $fl['df'];
		ltable_autovar($fl, $vv);
		if ($fl['t'] == 'd' && $fl['dt_auto'] >= 1)
		{
			$vv = strftime("%d/%m/%Y", time());
			if ($esrpt)
			{
				$tmpfe = fecha();
				if ($fl['dt_lapso'] == 3 || $fl['dt_lapso'] == 4)
				{
					$tmpfe['m']--;
					if ($tmpfe['m'] < 1)
					{
						$tmpfe['m'] = 12;
						$tmpfe['a']--;
					}
				}
				
				if ($fl['dt_lapso'] == 1 || $fl['dt_lapso'] == 3) $tmpfe['d'] = 1;
				if ($fl['dt_lapso'] == 2 || $fl['dt_lapso'] == 4) $tmpfe['d'] = fecha_ultimo($tmpfe);
				$vv = dtoc($tmpfe);
			}
		}
		if ($fl['t'] == 'h' && $fl['dt_auto'] >= 1) $vv = strftime("%H:%M:%S", time());
		if ($fl['t'] == 't' && $fl['dt_auto'] >= 1) $vv = strftime("%d/%m/%Y %H:%M:%S", time());
		if ($fl['t'] == 'n') $vv = number_format($vv, $fl['pd'], ',', '.');
		$lta['fla'][$fl['n']]['v'] = $vv;
	}
}

function ltable_find(&$lta, $campo, $valor)
{
	$isok = false;
	$fla = $lta['fla'];
	$query = "SELECT ";
	foreach ($fla as $fl)
	{
		if ($fl['esdato'] && $fl['ctrl_type'] != LT_SEPARATOR)
		{
			if (strcmp($fl['n'], $campo) != 0) $query .= $fl['n'] . ',';
		}
	}
	$query .= $campo ." FROM ". $lta['tabla']. " WHERE ". $campo ."=". $valor;
	if (!($res = mysql_query($query)))
	{
		echo "<p align=center><i>Error indagando:</i><code> " . mysql_error(). "</code></p>";
	}
	else
	{
		if (($row = mysql_fetch_assoc($res)) !== false)
		{
			$isok = true;
			foreach ($fla as $fl)
			{
				$nc = $fl['n'];
				if ($fl['ctrl_type'] == LT_SEPARATOR) continue;
				if (!$fl['esdato'])
				{
					$vv = $fl['df'];
					ltable_autovar($fl, $vv);
					$lta['fla'][$nc]['v'] = $vv;
					continue;
				}
				$vv = $row[$nc];
				if ($fl['ctrl_type'] == LT_PASSWORD) $vv = str_repeat(' ', $fl['l']);
				if ($fl['t'] == 'd')
				{
					$ts = strtotime($vv);
					$vv = strftime("%d/%m/%Y", $ts);
				}
				if ($fl['t'] == 'h' && $fl['dt_auto'] >= 1)
				{
					$ts = strtotime($vv);
					$vv = strftime("%H:%M:%S", $ts);
				}
				if ($fl['t'] == 'n') $vv = number_format($vv, $fl['pd'], ',', '');
				$lta['fla'][$nc]['v'] = $vv;
			}
		}
		else
		{
			echo "<p align=center><i>Registro no encontrado.</i></p>";
		}
		mysql_free_result($res);
	}
	return $isok;
}
?>
