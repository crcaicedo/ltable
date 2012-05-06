<?php
require_once "mprsfn.php";
require_once "ltable_rptfn.php";
///require_once "cxcfn.php";
include 'Classes/PHPExcel.php';
include 'Classes/PHPExcel/Writer/Excel5.php';

define("F_BLANKRPT", "blank_repeat");

function ltrpt_fltitles(&$rpt, &$buf)
{
	foreach ($rpt['campa'] as $cp)
	{
		if ($cp['hidden'] == 0)
		{
			if ($cp['style_th'] != "") $style = sprintf(" style=\"%s\"", $cp['style_th']);
			else $style = "";
			$buf .= sprintf("<th class=\"ltrptbody\"%s>%s</th>", $style, $cp['title']);
		}
	}
	$buf .= "</tr>";	
}

function ltrpt_header(&$rpt)
{
	$rpt['saltar'] = $rpt['pag_no'] < $rpt['pag_max'];
	
	$pade = $rpt['sparm'];
	$ordss = $rpt['sorden'];

	$ou = "<table class=\"ltrpthdr\">";
	$prnm = $rpt['pnm'];
	if (!enblanco($prnm)) $prnms = " - Proyecto: $prnm"; else $prnms = ''; 
	if ($rpt['pag_no'] == 1 || $rpt['header_inall'])
	{
		$ou .= "<tr><td colspan=3><b>" . SOFTNAME . " Orion Corp$prnms</b></td><tr>";
	}
	$ou .= sprintf("<tr><td>%s</td><td>Emitido: %s %s</td><td>Pag. %d/%d</td></tr>", 
		$rpt['title'], dtoc($rpt['fe']), htoc($rpt['hr']), 
		$rpt['pag_no'], $rpt['pag_max']);
		
	
	if (!enblanco($pade)) $ou .= "<tr><td colspan=3>Parametros: $pade</td></tr>";
	if (!enblanco($ordss)) $ou .= "<tr><td colspan=3>Ordenado por: $ordss</td></tr>";
	$ou .= "</table>";
	
	$ou .= "<table class=\"ltrptbody\"><tr>";
	if ($rpt['grpcount'] == 0) ltrpt_fltitles($rpt, $ou);
	
	return $ou;
}

function ltrpt_footer(&$rpt)
{
	$ou = '';
	if ($rpt['totalize'] == 1)
	{
		if ($rpt['foc'] > 0)
		{
			$ou = "<tr>";
			foreach ($rpt['campa'] as $cp)
			{
				if ($cp['style_th'] != "") $sty = sprintf(" style=\"%s\"", $cp['style_th']); else $sty = "";
				$tds = sprintf("<td class=\"ltrptfooter\"%s>", $sty);
				if ($cp['footer_op'] == 0 && !$cp['hidden'])
				{
					$ou .= "<td></td>";
					continue;
				}
				if ($cp['footer_op'] == 1)
					$ou .= $tds . number_format($cp['footer_cnt'], 0, ',', '.') . "</td>";
				if ($cp['t'] == 'i' || $cp['t'] == 'b')
				{
					if ($cp['footer_op'] == 2)
						$ou .= $tds . number_format($cp['footer_sum'], 0, ',', '.') . "</td>";
					if ($cp['footer_op'] == 3)
						$ou .= $tds . number_format($cp['footer_sum'] / $cp['footer_cnt'], 2, ',', '.') . "</td>";
					if ($cp['footer_op'] == 4)
					{
						$dva = $cp['dividendo'];
						$dvb = $cp['divisor'];
						$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty, 
							number_format(($rpt['campa'][$dva]['footer_sum'] * 100) / 
							$rpt['campa'][$dvb]['footer_sum'], 0, ',', '.'));
					}
				}
				else
				{
					if ($cp['footer_op'] == 2)
						$ou .= $tds . number_format($cp['footer_sum'], $cp['pd'], ',', '.') . "</td>";
					if ($cp['footer_op'] == 3)
						$ou .= $tds . number_format($cp['footer_sum'] / $cp['footer_cnt'], $cp['pd'], ',', '.') . "</td>";
					if ($cp['footer_op'] == 4)
					{
						$dva = $cp['dividendo'];
						$dvb = $cp['divisor'];
						$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty,
							number_format(($rpt['campa'][$dva]['footer_sum'] * 100) / 
							$rpt['campa'][$dvb]['footer_sum'], $cp['pd'], ',', '.'));
					}
				}
			}
			$ou .= "</tr>";
			if ($rpt['pag_no'] == $rpt['pag_max'])
			{
				// totales x rpt
			}
		}
	}
	$ou .= "</table>";
	if ($rpt['saltar']) $ou .= "<h5 class=\"ltrpt\"></h5>";
	
	return $ou;
}

function ltrpt_blank(&$rpt, $lnxpag = 44, $sparm = '', $sorden = '', 
	$uid=0, $pid=0, $pnm='')
{
	$rpt['title'] = '';
	$rpt['header_inall'] = false;
	$rpt['have_hlines'] = false;
	$rpt['sparm'] = $sparm;
	$rpt['sorden'] = $sorden;
	
	$rpt['lnxpag'] = $lnxpag;
	$rpt['reccount'] = 0;
	$rpt['recrem'] = 0;
	$rpt['lncount'] = 0;
	
	$rpt['campa'] = array ();
	$rpt['colc'] = 0;
	
	$rpt['grp'] = array ();
	$rpt['grpcount'] = 0;
	$rpt['grpndx'] = 0;
	$rpt['totalize'] = 1;
	
	$rpt['uid'] = $uid == 0 ? $_COOKIE['uid']: $uid;
	$rpt['pid'] = $pid == 0 ? $_COOKIE['pid']: $pid;
	$rpt['pnm'] = $pnm == '' ? $_COOKIE['pnm']: $pnm;

	$rpt['pag_no'] = 1;
	$rpt['pag_max'] = 0;
	settype($rpt['pag_no'], 'int');
	settype($rpt['pag_max'], 'int');

	$rpt['genxls'] = true;
	$rpt['gencsv'] = true;
}

function ltrpt_addfl(&$rpt, $n, $t, $l, $pd=0, $title='', $footer_op=0, $hidden=0, 
	$src='', $style='', $subtotal=false, $style_th="")
{
	$defval = $t == 'c' ? '': 0; 
	$rpt['campa'][$n] = array ('n'=>$n, 't'=>$t, 'l'=>$l, 'pd'=>$pd,
		'style'=>$style, 'title'=>$title, 'src'=>$src, 'hidden'=>$hidden,
		'footer_op'=>$footer_op, 'subtotal'=>$subtotal,
		'lastvalue'=>null, 'blank_repeat'=>false, 'blanquear'=>false, 'bzero'=>false, 
		'blank_reset'=>"", 'style_th'=>$style_th,'checkbox'=>0);
	if (!$hidden) $rpt['colc']++;
}

function ltrpt_grp_header(&$rpt)
{
	///ltrpt_grp_total_rst($rpt);
	
	$ndx = $rpt['grpndx'];
	///$rpt['grp'][$ndx]['lncount'] = 0;
	///$rpt['grp'][$ndx]['reccount'] = 0;
	$buf = sprintf("<tr><td colspan=\"%d\" style=\"font-weight: bold; border: 1px solid;\">%s</td>" .
		"</tr>", $rpt['colc'], $rpt['grp'][$ndx]['title']);
	$rpt['lncount']++;
	ltrpt_fltitles($rpt, $buf);
	$rpt['lncount']++;
	
	return $buf;
}

function ltrpt_grp_footer(&$rpt)
{
	$ou = '';
	if ($rpt['foc'] > 0) {
		$grp = $rpt['grp'][$rpt['grpndx']];
		$ou = "<tr>";
		foreach ($rpt['campa'] as $cp)
		{
			$cn = $cp['n'];
			if ($cp['style_th'] != "") $sty = sprintf(" style=\"%s\"", $cp['style_th']); else $sty = "";
			///$tds = sprintf("<td class=\"ltrptfooter\"%s>", $sty);
			if ($cp['footer_op'] == 0 && !$cp['hidden'])
			{
				$ou .= "<td></td>";
				continue;
			}
			if ($cp['footer_op'] == 1)
				$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty, number_format($grp[$cn]['footer_cnt'], 0, ',', '.'));
			if ($cp['t'] == 'i' || $cp['t'] == 'b')
			{
				if ($cp['footer_op'] == 2)
					$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty, number_format($grp[$cn]['footer_sum'], 0, ',', '.'));
				if ($cp['footer_op'] == 3)
					$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty, number_format($grp[$cn]['footer_sum'] / $grp[$cn]['footer_cnt'], 0, ',', '.'));
				if ($cp['footer_op'] == 4)
				{
					$dva = $cp['dividendo'];
					$dvb = $cp['divisor'];
					$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty,
					number_format(($grp[$dva]['footer_sum'] * 100) / $grp[$dvb]['footer_sum'], 
					0, ',', '.'));
				}
			}
			else
			{
				if ($cp['footer_op'] == 2)
					$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty,
					number_format($grp[$cn]['footer_sum'], $cp['pd'], ',', '.'));
				if ($cp['footer_op'] == 3)
					$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty,
					number_format($grp[$cn]['footer_sum'] / $grp[$cn]['footer_cnt'], 
					$cp['pd'], ',', '.'));
				if ($cp['footer_op'] == 4)
				{
					$dva = $cp['dividendo'];
					$dvb = $cp['divisor'];
					$ou .= sprintf("<td class=\"ltrptfooter\"%s>%s</td>", $sty,
					number_format(($grp[$dva]['footer_sum'] * 100) / $grp[$dvb]['footer_sum'], 
					$cp['pd'], ',', '.'));
				}
			}
		}
		$ou .= "</tr>";
		$rpt['lncount']++;
	}
	$ou .= "<tr style=\"border-left: none; height: 10px;\"></tr>";
	$rpt['lncount']++;
	
	return $ou;
}

function ltrpt_subtotal_show(&$rpt)
{
	$ou = "<tr>";
	$tds = "<td class=\"ltrptfooter\">";
	foreach ($rpt['campa'] as &$cp)
	{
		if ($cp['footer_op'] == 0 && !$cp['hidden'])
		{
			$ou .= "<td></td>";
			continue;
		}
		
		$cn = $cp["n"];
		$cnt = $rpt["subtotal_cnt"][$cn];
		$suma = $rpt["subtotal_sum"][$cn];
		
		if ($cp['footer_op'] == 1)
			$ou .= $tds . number_format($cnt, 0, ',', '.') . "</td>";
		if ($cp['t'] == 'i' || $cp['t'] == 'b')
		{
			if ($cp['footer_op'] == 2)
				$ou .= $tds.number_format($suma, 0, ',', '.')."</td>";
			if ($cp['footer_op'] == 3) $ou .= $tds.nes($suma/$cnt)."</td>";
		}
		else
		{
			if ($cp['footer_op'] == 2)
				$ou .= $tds.number_format($suma, $cp['pd'], ',', '.')."</td>";
			if ($cp['footer_op'] == 3)
				$ou .= $tds.number_format($suma/$cnt, $cp['pd'], ',', '.')."</td>";
		}
	}
	$ou .= "</tr>";
	// TODO: $rpt['lncount']++;
	
	return $ou;
}

function ltrpt_subtotal_init(&$rpt)
{
	foreach ($rpt['subtotal_fl'] as $cn)
	{
		$rpt["subtotal_cnt"][$cn] = 0;
		$rpt["subtotal_sum"][$cn] = 0;
	}
}

function ltrpt_subtotal_acum(&$rpt, &$row)
{
	foreach ($rpt['subtotal_fl'] as $cn)
	{
		$rpt["subtotal_cnt"][$cn]++;
		$rpt["subtotal_sum"][$cn] += $row[$cn];
	}
}

function ltrpt_subtotal_check(&$rpt, &$buf)
{
	$dosubtotal = false;
	if ($rpt["subtotal_cc"] > 0)
	{
		foreach ($rpt["subtotal_fl"] as $cn)
		{
			if ($rpt["subtotal_cnt"][$cn] > 0) $dosubtotal = true;
		}
		if ($dosubtotal) $buf .= ltrpt_subtotal_show($rpt);
		//else $buf .= "<p>dosub=0</p>";
	}
	//else $buf .= "<p>cc<=0</p>";
}

function ltrpt_body_ln(&$rpt, $row)
{
	$ou = "";
	$hl = $rpt['hl'];
	$ndx = $rpt['grpndx'];

	if ($rpt['genxls']) ltrpt_excel_add($rpt, $row);
	
	$dosubtotal = false;
	$tmpcva = array();
	foreach ($rpt['campa'] as &$cpx)
	{	
		if ($cpx['hidden'] == 1) continue;		
		$cn = $cpx['n'];
		$vv = $row[$cn];

		if ($cpx['subtotal'] || $cpx['blank_repeat'])
		{
			$cpx['blanquear'] = false;
			if ($cpx['lastvalue'] !== $vv)
			{
				if ($cpx['subtotal'] && $cpx['lastvalue'] !== null) $dosubtotal = true;
				$cpx['lastvalue'] = $vv;
			}
			else $cpx['blanquear'] = true;
		}

		switch ($cpx['t'])
		{
			case 'c': if (ctype_digit($vv)) $vv = "\"$vv\""; break;
			case 'd':
			{
				if ($vv != 0) $vv = sprintf("%s", dtoc(fecha_from($vv)));
				else $vv = "";
			}
			break;
			case 'h': $vv = sprintf("%s", htoc(hora_from($vv))); break;
			case 't': $vv = sprintf("%s %s", dtoc(fecha_from($vv)),
				htoc(hora_from($vv))); break;
		}
		$tmpcva[$cn] = $vv;
	}
	fputcsv($rpt['ff'], $tmpcva);

	foreach ($rpt['campa'] as &$cpk)
	{
		$npk = $cpk['blank_reset']; 
		if ($npk !== "")
		{
			if (!$rpt['campa'][$npk]['blanquear']) $cpk['blanquear'] = false;
		}	
	}
	
	if ($dosubtotal)
	{
		$ou .= ltrpt_subtotal_show($rpt);
		ltrpt_subtotal_init($rpt);
	}
	ltrpt_subtotal_acum($rpt, $row);

	$ou .= "<tr>";
	if (isset($row['_highlight'])) $hl .= "style=\"border-top:1px solid black;\""; 
	foreach ($rpt['campa'] as &$cp)
	{
		if ($cp['hidden'] == 1) continue;

		$cn = $cp['n'];
		$vv = $row[$cn];
		if ($cp['footer_op'] != 0) {
			$rpt['campa'][$cn]['footer_cnt']++;
			$rpt['campa'][$cn]['footer_sum'] += $vv;
			
			if ($rpt['grpcount'] > 0) {
				$rpt['grp'][$ndx][$cn]['footer_cnt']++;
				$rpt['grp'][$ndx][$cn]['footer_sum'] += $vv;
			}
		}
		$t = $cp['t'];
		if ($t == 'i')
			$vv = number_format($vv, 0, ',', '.');
		if ($t == 'n')
		{
			if ($cp['bzero'] && $vv == 0) $vv = ""; 
			else $vv = number_format($vv, $cp['pd'], ',', '.');
		}
		if ($t == 'd')
		{
			if ($vv != 0) $vv = sprintf("%s", dtoc(fecha_from($vv)));
			else $vv = str_repeat(" ", 8);
		}
		if ($t == 'h')
			$vv = htoc(hora_from($vv));
		if ($t == 't')
			$vv = dtoc(fecha_from($vv)) . ' ' . htoc(hora_from($vv));

		if ($cp["t"] == 'i' && $cp["checkbox"] == 1) $vv = $vv == 0 ? "":"&radic;"; 
		if ($cp['blanquear']) $vv = "";
			
		if (enblanco($cp['style']))
		{
			if (strstr('nibdth', $t) !== false)
				$ou .= "<td class=\"ltrptder\"$hl>$vv</td>";
			if (strstr('cm', $t) !== false)
				$ou .= "<td class=\"ltrptizq\"$hl>$vv</td>";
		}
		else
		{			
			$ou .= "<td style=\"" . $cp['style'] . "\">$vv</td>";
		}
	}
	$ou .= "</tr>";

	$rpt['lncount']++;
	$rpt['recrem']--;
	
	if ($rpt['lncount'] > $rpt['lnxpag']) {
		$ou .= ltrpt_footer($rpt);
		$rpt['pag_no']++;
		$rpt['lncount'] = 0;
		if ($rpt['recrem'] > 0) {
			$ou .= ltrpt_header($rpt);
			if ($rpt['grpcount'] > 0)
				$ou .= ltrpt_grp_header($rpt);
		}
	}
	return $ou;
}

function ltrpt_total_rst(&$rpt) {
	$rpt['foc'] = 0;
	foreach ($rpt['campa'] as $cp) {
		$cn = $cp['n'];
		if ($cp['footer_op'] != 0) {
			$rpt['campa'][$cn]['footer_cnt'] = 0;
			$rpt['campa'][$cn]['footer_sum'] = 0;
			$rpt['foc']++;
		}
	}
}

function ltrpt_grp_total_rst(&$rpt)
{
	if ($rpt['grpcount'] > 0)
	{
		$ndx = $rpt['grpndx'];
		foreach ($rpt['campa'] as $cp)
		{
			$cn = $cp['n'];
			if ($cp['footer_op'] != 0)
			{
				$rpt['grp'][$ndx][$cn]['footer_cnt'] = 0;
				$rpt['grp'][$ndx][$cn]['footer_sum'] = 0;
			}
		}
	}
}

function ltrpt_body_init(&$rpt)
{
	$buf = '';
	
	$rpt['fe'] = fecha();
	$rpt['hr'] = hora();

	$rpt["subtotal_cc"] = 0;
	$rpt["subtotal_fl"] = array();
	$rpt["subtotal_cnt"] = array();
	$rpt["subtotal_sum"] = array();
	$subtotal_fsz = 0;
	foreach ($rpt['campa'] as $fl)
	{
		$cn = $fl['n'];
		if ($fl["footer_op"] > 0)
		{
			$rpt["subtotal_fl"][$subtotal_fsz++] = $cn;
			$rpt["subtotal_cnt"][$cn] = 0;
			$rpt["subtotal_sum"][$cn] = 0;
		}
		if ($fl["subtotal"]) $rpt["subtotal_cc"]++;
	}
	//$buf .= print_r($rpt, true);
	
	$lnrem = $rpt['recrem'] + ($rpt['grpcount'] * 2);
	
	if ($rpt['recrem'] > 0)
	{
		$rpt['pag_max'] = ceil($lnrem / $rpt['lnxpag']);
		//if ($lnrem % $rpt['lnxpag'] > 0) $rpt['pag_max']++;
		$rpt['pag_no'] = 1;
		$rpt['lncount'] = 0;
		
		$buf .= ltrpt_header($rpt);
	}
	else
	{
		$buf .= "<p align=\"center\"><i>&laquo;No hay datos que coincidan con los par&aacute;metros especificados.&raquo;</i></p>";
	}
	
	if ($rpt['have_hlines'])
		$rpt['hl'] = " style=\"border-bottom: 1px solid;\"";
	else
		$rpt['hl'] = '';
	
	ltrpt_total_rst($rpt);
	ltrpt_subtotal_init($rpt);
		
	return $buf;
}

function ltrpt_grp_query_rst(&$rpt)
{
	$rpt['grpcount'] = 0;
	foreach ($rpt['grp'] as $grp)
	{
		$ndx = $rpt['grpcount'];
		$rpt['grp'][$ndx]['lncount'] = 0;
		$rpt['grp'][$ndx]['reccount'] = 0;
		$rpt['grp'][$ndx]['recrem'] = 0;
		$rpt['grpcount']++;

		$rpt['grpndx'] = $ndx;
		ltrpt_grp_total_rst($rpt);
	}
}

function ltrpt_grp_query(&$rpt)
{
	$buf = '';

	$ndx = $rpt['grpndx'];
	$q = lt_macros($rpt['grp'][$ndx]['query']);
	if (($tmpres = mysql_query($q)) !== false)
	{
		$rpt['grp'][$ndx]['reccount'] = mysql_num_rows($tmpres);
		$rpt['grp'][$ndx]['recrem'] = $rpt['grp'][$ndx]['reccount'];
		$rpt['recrem'] += $rpt['grp'][$ndx]['reccount'];
		
		$nr = 0;
		$rpt['grp'][$ndx]['row'] = array ();
		while (($row = mysql_fetch_assoc($tmpres)) !== false)
		{
			$rpt['grp'][$ndx]['row'][$nr++] = $row;
		}
		
		mysql_free_result($tmpres);
	}
	else $buf = squeryerror(17220);

	return $buf;
}

function ltrpt_excel_init(&$rpt)
{
	$ffn = str_replace("/", "_", $rpt['title']);
	if ($rpt['genxls'])
	{
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("MPRS 1.7");
		$objPHPExcel->getProperties()->setLastModifiedBy("MPRS 1.7");
		$objPHPExcel->getProperties()->setTitle($rpt['title']);
		$objPHPExcel->getProperties()->setSubject($rpt['title']);
		$objPHPExcel->getProperties()->setDescription($rpt['title']);
		
		$objPHPExcel->setActiveSheetIndex(0);
		$nletra = 65;
		$xcpsz = 0;
		$rpt['xcp'] = array();
		foreach ($rpt['campa'] as $cp)
		{ 
			if ($cp['hidden'] == 0)
			{
				$letra = chr($nletra);
				$rpt['xcp'][$xcpsz]['l'] = $letra;
				$rpt['xcp'][$xcpsz]['n'] = $cp['n'];
				$rpt['xcp'][$xcpsz]['t'] = $cp['t'];
				$objPHPExcel->getActiveSheet()->SetCellValue($letra.'1', $cp['title']);
				$nletra++;
				$xcpsz++;
			}
		}
		$rpt['xo'] = $objPHPExcel;
		$rpt['xn'] = 2;
		$rpt['xfn'] = sprintf("rpt/%s%d.xls", $ffn, $rpt['uid']);
	}

	$rpt['csvfn'] = sprintf("rpt/%s%d.csv", $ffn, $rpt['uid']);
	$rpt['ff'] = fopen($rpt['csvfn'], "w");

	$tita = array();
	$titac = 0;
	foreach ($rpt['campa'] as $cp)
	{ 
		if ($cp['hidden'] == 0) $tita[$titac++] = $cp['title'];
	}
	if ($titac > 0) fputcsv($rpt['ff'], $tita);
}

function ltrpt_excel_save(&$rpt)
{
	if ($rpt['genxls'])
	{
		$rpt['xo']->getActiveSheet()->setTitle(substr($rpt['title'], 0, 31));
		$objWriter = new PHPExcel_Writer_Excel5($rpt['xo']);
		$objWriter->save($rpt['xfn']);
	}

	fclose($rpt['ff']);

	$linkyx = "";
	$linky1 = $rpt['genxls'] ? sprintf("<a href=\"%s\">Archivo Excel</a>&nbsp;", $rpt['xfn']): "";
	$linky2 = $rpt['gencsv'] ? sprintf("<a href=\"%s\">Archivo TXT/CSV</a>", $rpt['csvfn']): "";
	if ($rpt['genxls'] || $rpt['gencsv']) $linkyx = sprintf("<p style=\"font-size: 7pt;\">%s%s</p>", $linky1, $linky2);
	return $linkyx;
}

function ltrpt_excel_add(&$rpt, &$row)
{
	foreach ($rpt['xcp'] as $xcp)
	{
		$cn = $xcp['n'];
		$vv = $row[$cn];
		if ($xcp['t'] == 'd') $vv = dtoc(fecha_from($vv));
		if ($xcp['t'] == 'h') $vv = htoc(hora_from($vv));
		if ($xcp['t'] == 't') $vv = dtoc(fecha_from($vv)).' '.htoc(hora_from($vv));
		$xref = sprintf("%s%d", $xcp['l'], $rpt['xn']);
		$rpt['xo']->getActiveSheet()->SetCellValue($xref, $vv);
	}
	$rpt['xn']++;
}

function ltrpt_grp_byarray(&$rpt)
{
	$buf = '';

	ltrpt_excel_init($rpt);
	
	$buf .= ltrpt_body_init($rpt);
	for ($ii = 0; $ii < $rpt['grpcount']; $ii++)
	{
		$rpt['grpndx'] = $ii;
		if ($rpt['grp'][$ii]['reccount'] > 0)
		{
			$buf .= ltrpt_grp_header($rpt);
			foreach ($rpt['grp'][$ii]['row'] as $registro)
			{
				$buf .= ltrpt_body_ln($rpt, $registro);
			}
			$buf .= ltrpt_grp_footer($rpt);
		}
	}
	ltrpt_subtotal_check($rpt, $buf);
	if ($rpt['lncount'] > 0) $buf .= ltrpt_footer($rpt);

	$buf .= ltrpt_excel_save($rpt);
	
	return $buf;
}

function ltrpt_grp_byquery(&$rpt, $sparm = '', $sorden = '')
{
	ltrpt_excel_init($rpt);
	
	$rpt['sparm'] = $sparm;
	$rpt['sorden'] = $sorden;
	$buf = '';
	$buf .= ltrpt_grp_query_rst($rpt);
	for ($rpt['grpndx'] = 0; $rpt['grpndx'] < $rpt['grpcount']; $rpt['grpndx']++)
		$buf .= ltrpt_grp_query($rpt);
	$buf .= ltrpt_grp_byarray($rpt);

	$buf .= ltrpt_excel_save($rpt);
		
	return $buf;
}

function ltrpt_byarray(&$rpt)
{
	$buf = '';

	ltrpt_excel_init($rpt);

	$rpt['recrem'] = $rpt['reccount'];
	$buf .= ltrpt_body_init($rpt);
	foreach ($rpt['row'] as $registro)
	{
		$buf .= ltrpt_body_ln($rpt, $registro);
	}
	ltrpt_subtotal_check($rpt, $buf);
	if ($rpt['lncount'] > 0) $buf .= ltrpt_footer($rpt);

	$buf .= ltrpt_excel_save($rpt);
				
	return $buf;
}

function ltrpt_byquery(&$rpt, $query)
{
	$buf = '';
	ltrpt_excel_init($rpt);
		
	$query = lt_macros($query);
	if (($res = mysql_query($query)) !== false)
	{
		$rpt['reccount'] = mysql_num_rows($res);
		$rpt['recrem'] = $rpt['reccount'];
		$buf .= ltrpt_body_init($rpt);
		while (($row = mysql_fetch_assoc($res)) !== false)
		{
			$buf .= ltrpt_body_ln($rpt, $row);
		}
		ltrpt_subtotal_check($rpt, $buf);	
		if ($rpt['lncount'] > 0) $buf .= ltrpt_footer($rpt);
		mysql_free_result($res);
	}
	else $buf .= squeryerror("RPTBYQ-1");

	$buf .= ltrpt_excel_save($rpt);
			
	return $buf;
}

function ltrpt_query(&$rpt, $query, $sparm = '', $sorden = '')
{
	$rpt['sparm'] = $sparm;
	$rpt['sorden'] = $sorden;	
	echo ltrpt_byquery($rpt, $query);
}

function ltrpt_funcs($fl, &$row)
{
	$cn = $fl['n'];
	///if ($fl['src'] == '@ltrpt_rspagado')
	///{
	///	$doc_no = sprintf("RS%010d", $row['reservacion_id']);
	///	$row[$cn] = cxc_aplicados_bydoc($doc_no, false);
	///}
	if ($fl['src'] == '@ltrpt_esapartado')
	{
		$row[$cn] = $row['pagado'] < $row['reserva'] ? '*': ' ';
	}
}

function ltrpt_emitir(&$rpt, $fo)
{
	$isok = false;
	
	$rpt['fe'] = fecha();
	$rpt['hr'] = hora();
	$rpt['uid'] = $_COOKIE['uid'];
	$rpt['pnm'] = $_COOKIE['pnm'];
	$rpt_name = $rpt['rpt_name'];
	ltrpt_excel_init($rpt);
	$rpt['grpcount'] = 0;
	
	// data source
	$tablas = $rpt['tabla'];
	$cond = '';
	$condc = 0;
	$tblc = 0;
	
	$joins = "";
	$q = sprintf("SELECT * FROM ltable_rpt_src WHERE rpt_name='%s' ORDER BY orden",
		$rpt_name);
	if (($res = mysql_query($q)) !== false)
	{
		/*
		while (($row = mysql_fetch_assoc($res)) !== false)
		{
			if (enblanco($row['alias']))
			{
				$tablas .= ',' . $row['tabla2'];
				$cond .= sprintf(" AND %s.%s=%s.%s", $row['tabla1'], $row['key1'], $row['tabla2'], $row['key2']);
			}
			else
			{
				$tablas .= sprintf(",%s AS %s", $row['tabla2'], $row['alias']);
				$cond .= sprintf(" AND %s.%s=%s.%s", $row['tabla1'], $row['key1'], $row['alias'], $row['key2']);
			}
			$condc++;
		}
		*/
		while (($row = mysql_fetch_assoc($res)) !== false)
		{
			if (enblanco($row['alias'])) 
			{
				$joins .= sprintf(" LEFT JOIN %s ON %s.%s=%s.%s", $row['tabla2'], 
					$row['tabla2'], $row['key2'], $row['tabla1'], $row['key1']);
			}
			else
			{
				$joins .= sprintf(" LEFT JOIN %s AS %s ON %s.%s=%s.%s",
					$row['tabla2'], $row['alias'], $row['alias'], $row['key2'], 
					$row['tabla1'], $row['key1']);
			}
		}
		mysql_free_result($res);
	}
	else $fo->qerr("LTRPT-EMITIR-1");
	
	// initial condition(s)
	if (!enblanco($rpt['initcond']))
	{
		$cond .= ' AND ' . $rpt['initcond'];
		$condc++;
	}
	if ($condc > 0) $cond = 'WHERE ' . substr($cond, 4);
	
	// additional parameters/constraints
	$opn = array('>', '<', '=', '>=', '<=', '!='	);
	$opns = array('>', '<', '=', '&ge;', '&le;', '&ne;');
	$ops = array('=', 'REGEXP', '!=', 'NOT REGEXP');
	$opss = array('=', '&isin;', '&ne;', '&notin;');
	$opl = array('=', '!=');
	$opls = array('=', '&ne;');
	$aop = array ('Y' => 'AND', 'O' => 'OR');
	$aops = array ('Y' => "&and;", 'O' => "&or;");
	$pade = '';
	$parmc = 0;
	$parma = $rpt['fla'];
	$cond2 = '';
	foreach ($parma as $pa)
	{
		if (!$pa['up']) continue;
		$t = $pa['t'];
		$op = $pa['op'];
		if ($parmc == 0) $pa['lp'] = 'Y';
		$lp = $aop[$pa['lp']];
		if ($parmc == 0) $lps = ''; else $lps = $aops[$pa['lp']];
		if ($pa['ctrl_type'] == LTO_TEXTBOX)
		{
			if (strpos('ib', $t) !== false)
				$cond2 .= sprintf(" %s %s %s %d", $lp, $pa['n'], $opn[$op], $pa['v']);
			if ($t == 'n')
				$cond2 .= sprintf(" %s %s %s %f", $lp, $pa['n'], $opn[$op], $pa['v']);
			if (strpos('cm', $t) !== false)
				$cond2 .= sprintf(" %s %s %s '%s'", $lp, $pa['n'], $ops[$op], $pa['v']);
			
			if ('d' == $t)
				$cond2 .= sprintf(" %s %s %s %d", $lp, $pa['n'], $opn[$op], fecha2time(ctod($pa['v'])));
			if ('h' == $t)
				$cond2 .= sprintf(" %s %s %s %d", $lp, $pa['n'], $opn[$op], hora2time(ctoh($pa['v'])));
			if ('t' == $t)
				$cond2 .= sprintf(" %s %s %s %d", $lp, $pa['n'], $opn[$op], $pa['v']);
			
			if (strpos('dht', $t) !== false)
				$pade .= sprintf(" %s %s%s%s", $lps, $pa['title'], $opns[$op], $pa['v']);
			if (strpos('cm', $t) !== false)
				$pade .= sprintf(" %s %s%s'%s'", $lps, $pa['title'], $opss[$op], $pa['v']);
			if (strpos('ib', $t) !== false)
				$pade .= sprintf(" %s %s%s%s", $lps, $pa['title'], $opns[$op], number_format($pa['v'], 0, ',', '.'));
			if ('n' == $t)
				$pade .= sprintf(" %s %s%s%s", $lps, $pa['title'], $opns[$op], number_format($pa['v'], $pa['pd'], ',', '.'));
			
			$parmc++;
		}
		if ($pa['ctrl_type'] == LTO_LISTBOX)
		{
			if (strpos('nib', $t) !== false)
				$q = sprintf("SELECT %s FROM %s WHERE %s=%d", $pa['ls_fl_desc'], $pa['ls_tbl'], $pa['ls_fl_key'], $pa['v']);
			else
				$q = sprintf("SELECT %s FROM %s WHERE %s='%s'", $pa['ls_fl_desc'], $pa['ls_tbl'], $pa['ls_fl_key'], $pa['v']);
			if (($res = mysql_query($q)) !== false)
			{
				if (($row = mysql_fetch_row($res)) !== false)
				{
					$desp = '';
					foreach ($row as $sss)
					$desp .= ' ' . trim($sss);
					$desp = substr($desp, 1);
					
					if (strpos('nib', $t) !== false)
						$cond2 .= sprintf(" %s %s %s %d", $lp, $pa['n'], $opl[$op], $pa['v']);
					if (strpos('cm', $t) !== false)
						$cond2 .= sprintf(" %s %s %s '%s'", $lp, $pa['n'], $opl[$op], $pa['v']);
					
					$pade .= sprintf(" %s %s%s\"%s\"", $lps, $pa['title'], $opls[$op], $desp);
					
					$parmc++;
				}
				mysql_free_result($res);
			}
			else $fo->qerr("LTRPT-EMITIR-2");
		}
		if ($pa['ctrl_type'] == LTO_CHECKBOX)
		{
			if ($pa['t'] == 'l')
			{
				$cond2 .= sprintf(" %s %s%s", $lp, $pa['v'] == 0 ? 'NOT ' : '', $pa['n']);
				$parmc++;
			}
		}
	}
	
	if ($parmc > 0) $cond2 = 'HAVING ' . substr($cond2, 4);
	
	// campos
	$rpt['foc'] = 0;
	$campos = '';
	$campa = $rpt['campa'];
	$csvnmfl = array();
	foreach ($campa as $cp)
	{
		$cn = $cp['n'];
		$csvnmfl[$cn] = $cn;
		if (substr($cp['src'], 0, 1) != '@')
		{
			if (enblanco($cp['src'])) $cp['src'] = $cp['n'];
			if (strstr('dth', $cp['t']) !== false) 
				$cp['src'] = "UNIX_TIMESTAMP(" . $cp['src'] . ")";
			if (strcmp($cp['src'], $cp['n']) != 0) 
				$campos .= ',' . $cp['src'] . ' AS ' . $cp['n'];
			else $campos .= ',' . $cp['src'];
		}
		else $campos .= ",0 AS " . $cp['n'];
		if ($cp['footer_op'] != 0)
		{
			$rpt['campa'][$cn]['footer_cnt'] = 0;
			$rpt['campa'][$cn]['footer_sum'] = 0;
			$rpt['foc']++;
		}
	}
	$campos = substr($campos, 1);
	
	fputcsv($rpt['ff'], $csvnmfl);
	
	$orden = '';
	$ordss = '';
	$ords = '';
	$ordc = 0;
	foreach ($rpt['orda'] as $ord)
	{
		$sinv = $rpt['inva'][$ord] == 's' ? ' DESC' : '';
		$ords .= ",$ord$sinv";
		$ssinv = $rpt['inva'][$ord] == 's' ? ' (inv)' : '';
		$ordss .= ', ' . $rpt['campa'][$ord]['title'] . $ssinv;
		$ordc++;
	}
	if ($ordc > 0) $orden = 'ORDER BY ' . substr($ords, 1);
	if ($ordc > 0) $ordss = substr($ordss, 2);
	
	if ($rpt['have_hlines']) $hl = " style=\"{border-bottom: 1px,solid;}\"";
	else $hl = '';
	
	// build query
	$q = "SELECT $campos FROM $tablas $joins $cond $cond2 $orden";
	$q = lt_macros($q);
	$ou = '';
	///echo "<p><b>".$q."</b></p>";
	if (($res = mysql_query($q)) !== false)
	{
		$rpt['lnrem'] = mysql_num_rows($res);
		if ($rpt['lnrem'] > 0)
		{
			$rpt['pag_max'] = ceil($rpt['lnrem'] / $rpt['lnxpag']);
			///if ($rpt['lnrem'] % $rpt['lnxpag'] > 0) $rpt['pag_max']++;
			$rpt['pag_no'] = 1;
			$lnc = 0;
			
			$rpt['sparm'] = $pade;
			$rpt['sorden'] = $ordss;
			$fo->buf .= ltrpt_header($rpt);
			
			// show every record
			while (($row = mysql_fetch_assoc($res)) !== false)
			{
				// show every field
				if ($rpt['genxls']) ltrpt_excel_add($rpt, $row);
				
				$ou = "<tr>";
				foreach ($campa as $cp)
				{
					ltrpt_funcs($cp, $row);
					if ($cp['hidden'] == 1)
						continue;
					
					$cn = $cp['n'];
					$vv = $row[$cn];
					
					if ($cp['footer_op'] != 0) {
						$rpt['campa'][$cn]['footer_cnt']++;
						$rpt['campa'][$cn]['footer_sum'] += $vv;
					}
					$t = $cp['t'];
					if ($t == 'i')
						$vv = number_format($vv, 0, ',', '.');
					if ($t == 'n')
						$vv = number_format($vv, $cp['pd'], ',', '.');
					if ($t == 'd')
					{
						if ($vv != 0) $vv = dtoc(fecha_from($vv)); else $vv = "";
					}
					if ($t == 'h')
					{
						if ($vv != 0) $vv = htoc(hora_from($vv)); else $vv = "";
					}
					if ($t == 't')
					{
						if ($vv != 0) $vv = dtoc(fecha_from($vv)) . ' ' . htoc(hora_from($vv));
						else $vv = "";
					}
					if (enblanco($cp['style'])) {
						if (strstr('nibdth', $t) !== false)
							$ou .= "<td class=\"ltrptder\"$hl>$vv</td>";
						if (strstr('cm', $t) !== false)
							$ou .= "<td class=\"ltrptizq\"$hl>$vv</td>";
					} else
						$ou .= "<td style=\"" . $cp['style'] . "\"</td>";
				}
				$fo->buf .= ("</tr>".$ou);
				fputcsv($rpt['ff'], $row);
				$lnc++;
				$rpt['lnrem']--;
				if ($lnc > $rpt['lnxpag']) {
					$fo->buf .= ltrpt_footer($rpt);
					$rpt['pag_no']++;
					$lnc = 0;
					if ($rpt['lnrem'] > 0) $fo->buf .= ltrpt_header($rpt);
				}
			}
			if ($lnc > 0) $fo->buf .= ltrpt_footer($rpt);
		}
		else
		{
			$fo->buf .= "<p align=center>No hay datos que coincidan con los par&aacute;metros especificados.</p>";
			$fo->buf .= "<p align=center>Par&aacute;metros: " . $pade . "</p>";
		}
		mysql_free_result($res);
	}
	else $fo->qerr("LTRPT-EMITIR-5") ;
	
	$fo->buf .= ltrpt_excel_save($rpt);
		
	return $isok;
}

function ltrpt_load($fo, $rpt_name, & $lt)
{
	$ok = array (false, false, false, false, false);
	
	$query = sprintf("SELECT * FROM ltable_rpt WHERE rpt_name='%s'", $rpt_name);
	if (($res = mysql_query($query)) !== false)
	{
		if (($row = mysql_fetch_assoc($res)) !== false)
		{
			$ok[0] = true;
			
			$lt = $row;
			$lt['fl0'] = 'rpt_name';
			$lt['totalize'] = 1;
			$lt['genxls'] = true;
			
			// parametros del reporte
			$query = sprintf("SELECT n,t,l,pd,title,dfc,dfn,dfd,dft,dfh,dfi,dfb,dfl,dfm,ctrl_type," .
				"ls_tbl,ls_fl_key,ls_fl_desc,ls_fl_order,ls_custom,vcols,vrows,dt_auto,ro,hidden," .
				"valid_fn,valid_parms,mascara,funcion,esdato,enabled,init_fn,init_parms," .
				"autovar,postvar,defop,dt_lapso,onkey_fn,onkey_parms " .
				"FROM ltable_rpt_parms " .
				"WHERE rpt_name='%s' ORDER BY orden", $rpt_name);
			if (($res2 = mysql_query($query)) !== false)
			{
				$cnt = 0;
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
					$fl['ls_fl_key'] = $row['ls_fl_key'];
					$fl['ls_fl_desc'] = $row['ls_fl_desc'];
					$fl['ls_fl_order'] = $row['ls_fl_order'];
					$fl['ls_custom'] = $row['ls_custom'];
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
					$fl['defop'] = $row['defop'];
					$fl['dt_lapso'] = $row['dt_lapso'];
					$fl['isup'] = 1;
					
					$lt['fla'][$fl['n']] = $fl;
					$cnt++;
				}
				mysql_free_result($res2);
				if ($cnt > 0) $ok[1] = true;
			}
			else $fo->qerr("LTRPT-LOAD-1");
			
			// orden visual
			$query = sprintf("SELECT n FROM ltable_rpt_parms WHERE rpt_name='%s' ORDER BY ordenv", 
				$rpt_name);
			if (($res2 = mysql_query($query)) !== false)
			{
				$cnt = 0;
				while (($row = mysql_fetch_row($res2)) !== false)
				{
					$lt['ordenv'][$cnt++] = $row[0];
				}
				mysql_free_result($res2);
				if ($cnt > 0) $ok[2] = true;
			}
			else $fo->qerr("LTRPT-LOAD-2");
			
			// campos del reporte
			$q = "SELECT * FROM ltable_rpt_fl WHERE rpt_name='$rpt_name' ORDER BY ordenv";
			if (($res3 = mysql_query($q)) !== false)
			{
				$cpc = 0;
				while (($row = mysql_fetch_assoc($res3)) !== false)
				{
					$cn = $row['n'];
					$row['style_th'] = "";
					$lt['campa'][$cn] = $row;
					$cpc++;
				}
				mysql_free_result($res3);
				if ($cpc > 0) $ok[3] = true;
				$lt['cpc'] = $cpc;
			}
			else $fo->qerr("LTRPT-LOAD-3");
		}
		mysql_free_result($res);
	}
	else $fo->qerr("LTRPT-LOAD-4");
	
	return $ok[0] && $ok[1] && $ok[2] && $ok[3];
}

function ltrpt_getorden()
{
	$elorden = "";
	
	$norden = $_REQUEST['rpt_orden_max'];
	for ($ndx = 0; $ndx < $norden; $ndx++)
	{
		$sord = $_REQUEST['rpt_orden'.$ndx];
		$sdesc = isset($_REQUEST['rpt_orden_desc'.$ndx]) ? " DESC": "";
		$elorden .= sprintf(",%s%s", $sord, $sdesc);
	}
	if ($norden > 0) $elorden = substr($elorden, 1);

	return $elorden;
}

function ltrpt_getfiltro()
{
	$elfiltro = "";
	
	$nfiltro = $_REQUEST['rpt_filtro_max'];
	for ($ndx = 0; $ndx < $nfiltro; $ndx++)
	{
		if (isset($_REQUEST['rpt_filtro_use'.$ndx]))
		{
			$tp = $_REQUEST['rpt_filtro_tipo'.$ndx];
			$fnm = $_REQUEST['rpt_filtro_campo'.$ndx];
			
			if ($tp == 'ri')
			{
				$nm = explode(",", $_REQUEST['rpt_filtro_ctrl'.$ndx]);
				$elfiltro .= sprintf(" AND %s BETWEEN %d AND %d", 
					$fnm, $_REQUEST[$nm[0]], $_REQUEST[$nm[1]]);
			}
			if ($tp == 'rd')
			{
				$nm = explode(",", $_REQUEST['rpt_filtro_ctrl'.$ndx]);
				$elfiltro .= sprintf(" AND %s BETWEEN '%s' AND '%s'", 
					$fnm, dtoms(ctod($_REQUEST[$nm[0]])), dtoms(ctod($_REQUEST[$nm[1]])));
			}
		}
	}
	if ($nfiltro > 0) $elfiltro = "HAVING" . substr($elfiltro, 4);

	return $elfiltro;
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
		if (!enblanco($vfn) && ($ctrl == LTO_TEXTBOX || $ctrl == LTO_EDITBOX))
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
				if ($fl['ctrl_type'] == LTO_SEPARATOR)
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
				if  ($fl['ctrl_type'] == LTO_CHECKBOX)
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
				if  ($fl['ctrl_type'] == LTO_LISTBOX)
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
				if  ($fl['ctrl_type'] == LTO_EDITBOX)
				{
					$sval = '';
					$sval = lt_validfn($fl);
					$trx .= "<textarea name=\"$cn\" id=\"$cn\" cols=\"". $fl['vcols'] ."\" rows=\"". $fl['vrows'] ."\"".
						$sro . $sdisa . $sval .">". $fl['v'] ."</textarea>";
				}
				if  ($fl['ctrl_type'] == LTO_TEXTBOX || $fl['ctrl_type'] == LTO_PASSWORD)
				{
					$sctrl = $fl['ctrl_type'] == LTO_PASSWORD ? 'password': 'text';
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
					if ($fl['ctrl_type'] != LTO_SEPARATOR) $trx .= "</td></tr>";
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

function lt_operador($fl, $nop)
{
	$lns = '';
	$ii = 0;
	$sdisa = $fl['autovar'] == 0 ? 'disabled' : '';
	$t = $fl['t'];
	if (strstr('nibdth', $t) !== false) $opls = array('Mayor que', 'Menor que', 'Igual a', 'Mayor igual que', 'Menor igual que','Diferente a');
	if (strstr('cm', $t) !== false) $opls = array('Igual a','Contiene','Diferente a','No contiene');
	if ($fl['ctrl_type'] == LTO_LISTBOX) $opls = array('Igual a','Diferente a');
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
?>
