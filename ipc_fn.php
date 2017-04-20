<?php
require_once "ltable_olib.php";

function ipc_historico(lt_form $fo, $ac)
{
	$isok = false;
	if ($ac->inicio == 0)
	{
		$qa = new myquery($fo, "SELECT ipc_id, CONCAT(agno_id,'-',LPAD(mes_id,2,'0')) AS fe FROM ipc ORDER BY fe LIMIT 1",
			"IPCHIST-1");
		if ($qa->isok) $ac->inicio = $qa->r->ipc_id;
	}	
	if ($ac->fin == 0)
	{
		$qb = new myquery($fo, "SELECT ipc_id, CONCAT(agno_id,'-',LPAD(mes_id,2,'0')) AS fe FROM ipc ORDER BY fe DESC LIMIT 1",
			"IPCHIST-2");
		if ($qb->isok) $ac->fin = $qb->r->ipc_id;
	}
	if ($ac->inicio == 0 && $ac->fin == 0)
	{	
		$qc = new myquery($fo, "SELECT SUM(factor) AS ac_tt, AVG(factor) AS ac_prom FROM ipc", "IPCHIST-3");
		if ($qc->isok)
		{
			$ac->tt = $qc->r->ac_tt;
			$ac->prom = $qc->r->ac_prom;
		}
		$isok = $qc->isok;
	}
	else
	{
		$qa = new myquery($fo, sprintf("SELECT ipc_id, CONCAT(agno_id, '-', LPAD(mes_id, 2, '0'), '-01') AS fe ". 
			"FROM ipc WHERE ipc_id=%d", $ac->inicio), "IPCHIST-1");
		if ($qa->isok)
		{
			///$ac->inicio = $qa->r->ipc_id;
			$qb = new myquery($fo, sprintf("SELECT ipc_id, CONCAT(agno_id, '-', LPAD(mes_id, 2, '0'), '-01') AS fe ".
				"FROM ipc WHERE ipc_id=%d", $ac->fin), "IPCHIST-2");
			if ($qb->isok)
			{
				///$ac->fin = $qb->r->ipc_id;
				$qc = new myquery($fo, sprintf("SELECT SUM(factor) AS ac_tt, AVG(factor) AS ac_prom FROM (".
					"SELECT factor, CONCAT(agno_id, '-', LPAD(mes_id, 2, '0'), '-01') AS fe FROM ipc ". 
					"HAVING fe BETWEEN '%s' AND '%s' ORDER BY fe) AS qq", $qa->r->fe, $qb->r->fe), "IPCMAIN-3");
				///$fo->parc($qc->q);
				if ($qc->isok)
				{
					$ac->tt = $qc->r->ac_tt;
					$ac->prom = $qc->r->ac_prom;
					$isok = true;
				}
			}
		}
	}
	return $isok;
}
function ipc_resumen(lt_form $fo, $verorigen=true, $inipry=false, $caption=false)
{
	$svv = $fo->autotblx;
	$fo->autotblx = $fo->autotrx = $fo->autotdx = false;
	$fo->js("ipc.js");
	$ac->inicio = $ac->fin = 0;
	$ac->tt = $ac->prom = 0;
	if ($inipry)
	{
		$inife = fecha_inicial_pry($fo, 0, 1);
		$qp = new myquery($fo, sprintf("SELECT ipc_id FROM ipc WHERE mes_id=%d AND agno_id=%d",
			$inife['m'], $inife['a']), "IPCRES-1");
		if ($qp->isok) $ac->inicio = $qp->r->ipc_id;
	}
	ipc_historico($fo, $ac);
	
	$fo->tbl(3,-1,"2%","","border:none;font-size:9pt;border-collapse:collapse;");
	if ($caption)
	{
		$fo->tr();
		$fo->th('IPC', 3, 4);
	}
	$fo->tr();
	$fo->tha(array("Inicio","Fin","Acumulado","Promedio"));
	if ($verorigen) $fo->th("Origen");
	$fo->trx();

	$fo->tr();
	$fo->td(3);
	$ls0 = new lt_listbox();
	$ls0->n = "ac_inicio";
	$ls0->t = 'i';
	$ls0->custom = "SELECT ipc_id, nombre_es, agno_id, ipc.mes_id FROM ipc " .
		"LEFT JOIN meses ON meses.mes_id=ipc.mes_id ORDER BY agno_id, mes_id";
	$ls0->fl_key = "ipc_id";
	$ls0->fl_desc = "nombre_es,agno_id";
	$ls0->assign($ac->inicio);
	$ls0->valid_fn = "ipc_histo";
	$ls0->render($fo->buf);
	$fo->tdx();
	
	$fo->td(3);
	$ls1 = new lt_listbox();
	$ls1->n = "ac_fin";
	$ls1->t = 'i';
	$ls1->custom = "SELECT ipc_id, nombre_es, agno_id, ipc.mes_id FROM ipc " .
		"LEFT JOIN meses ON meses.mes_id=ipc.mes_id ORDER BY agno_id, mes_id DESC";
	$ls1->fl_key = "ipc_id";
	$ls1->fl_desc = "nombre_es,agno_id";
	$ls1->assign($ac->fin);
	$ls1->valid_fn = "ipc_histo";
	$ls1->render($fo->buf);
	$fo->tdx();
	
	$fo->td(3);
	$tx0 = new lt_textbox();
	$tx0->n = "ac_tt";
	$tx0->t = 'n';
	$tx0->l = 4;
	$tx0->pd = 2;
	$tx0->ro = true;
	$tx0->assign($ac->tt);
	$tx0->render($fo->buf);
	$fo->tdx();
	
	$fo->td(3);
	$tx1 = new lt_textbox();
	$tx1->n = "ac_prom";
	$tx1->t = 'n';
	$tx1->l = 4;
	$tx1->pd = 2;
	$tx1->ro = true;
	$tx1->assign($ac->prom);
	$tx1->render($fo->buf);
	$fo->tdx();
	
	if ($verorigen)
	{
		$fo->td(3);
		$fo->lnk("http://www.bcv.org.ve/excel/4_1_15.xls", "IPC (BCV)", "", "", "_blank");
		$fo->sp();
		$fo->lnk("http://www.bcv.org.ve/excel/4_5_1.xls", "INPC (BCV)", "", "", "_blank");
		$fo->tdx();
	}
	$fo->trx();
	$fo->tblx();

	$fo->autotblx = $fo->autotrx = $fo->autotdx = $svv;
}
function ipc_poragno(lt_form $fo, $proyecto_id, $caption=false)
{
	$tt = $cc = 0;
	$inife = fecha_inicial_pry($fo, $proyecto_id, 1);
	$qa = new myquery($fo, sprintf("SELECT agno_id, SUM(factor) AS tt, AVG(factor) AS md ".
		"FROM ipc WHERE agno_id >= %d ".
		"GROUP BY agno_id ORDER BY agno_id", $inife['a']), "IPAGNO-1");
	if ($qa->isok)
	{
		$svv = $fo->autotblx;
		$fo->autotblx = $fo->autotrx = $fo->autotdx = false;
		$fo->tbl(1, -1, "2%", "stdpgl", "", true);
		if ($caption)
		{
			$fo->tr();
			$fo->th('IPC acumulado',3,3);
		}
		$fo->tr();
		$fo->tha(array("A&ntilde;o","Anual","Promedio"));
		$fo->trx();
		foreach ($qa->a as $rg)
		{
			$fo->tr();
			$fo->tdc($rg->agno_id, 2);
			$fo->tdc(nes($rg->tt), 2);
			$fo->tdc(nes($rg->md), 2);
			$fo->trx();
			$cc++;
			$tt+=$rg->tt;
		}
		$fo->tr();
		$fo->tdc("TOTALES...", 2, 0, 'negrita');
		$fo->tdc(nes($tt), 2, 0, 'negrita');
		$fo->tdc(nes($tt/$cc), 2, 0, 'negrita');
		$fo->trx();
		$fo->tblx();
		$fo->autotblx = $fo->autotrx = $fo->autotdx = $svv;
	}
}
?>
