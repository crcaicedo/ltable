<?php
require_once "ltable_olib.php";
require_once "reserv_hist.php";
require_once "contrat_gengr.php";
require_once "cxcfn.php";
require_once "sms_promofn.php";
require_once "local_estado.php";
require_once "reserva_retiros_fn.php";
require_once "usuario_deps.php";
require_once "adux_fn.php";
require_once "notificaciones.php";

function lto_lastprocess($lto, $campo, $valor, $fo)
{
	$tabla = $lto->tabla;
	if ($lto->forzar_valor) $recid = $valor;
	else $recid = $lto->nuevo ? $lto->insert_id : $valor;
	
	if ($tabla == 'crm_soldes')
	{
		$fo->par();
		$fo->frm("crm_soldes_rec.php", true);
		$fo->hid("valor", $recid);
		$fo->sub("Recibo imprimible");
		$fo->frmx();
		$fo->parx();

		$fo->par();
		$fo->frm("retiros_relacion.php", true);
		$fo->hid("valor", $recid);
		$fo->sub("Relacion de pagos");
		$fo->frmx();
		$fo->parx();
	}

	if ($tabla == 'reserva_retiros')
	{
		$fo->par();
		$fo->frm("retiros_recibo.php", true);
		$fo->hid("valor", $recid);
		$fo->sub("Recibo imprimible");
		$fo->frmx();
		$fo->parx();
	}

	if ($tabla == 'reservaciones')
	{
		$fo->par();
		$fo->frm("reserv_impr.php", true);
		$fo->hid("valor", $recid);
		$fo->hid("nuevo", $lto->nuevo);
		$fo->sub("Reservaci&oacute;n imprimible");
		$fo->parx();
	}

	$hemail_op = $lto->nuevo ? 'C' : 'U';
	//hemail($tabla, $campo, $recid, $hemail_op);
	if ($tabla == 'contrato_suscripcion')
		hemail($tabla, $campo, $recid, $hemail_op, 'catire78@gmail.com');
}

function retiros_post(lt_form $fo, ltable $lto)
{
	$isok = false;
	$reintegro = $lto->fa["reintegro"]->v;
	$lcupok = false;
	if ($reintegro == 1 || $reintegro == 2)
	{
		if (local_estado_set($fo, $lto->fa['local_id']->v, LOCSTATUS_DEVUELTO))
		{
			if (local_freshprice($fo, $lto->fa['local_id']->v)) $lcupok = true;
			else $fo->err("LTPOST-RET-7", "No pude actualizar precio local"); 
		}
		else $fo->err("LTPOST-RET-6", "No pude actualizar info local"); 
	}
	if ($reintegro == 3) $lcupok = local_estado_set($fo, $lto->fa['local_id']->v, LOCSTATUS_VENDIDO);
	
	if ($lcupok)
	{
		if (reservacion_anular($fo, $lto))
		{
			$soldes_id = $lto->fa['soldes_id']->v;
			if ($soldes_id != 0)
			{
				$qa = new myquery($fo, sprintf("UPDATE crm_soldes SET estatus=2, modificado=NOW() ".
					"WHERE soldes_id=%d", $soldes_id), "LTUP-RET-5", true, true);
				if ($qa->isok)
				{ 
					$isok = true;
					$fo->ok("Post-proceso de retiro");
				}
				else $fo->err("LTUP-RET-9", "No pude actualizar estado solicitud");
			}
			else $fo->err("LTUP-RET-4", "Solicitud de desistimiento no registrada.");
		}
		else $fo->err("LTPOST-RET-8", "No pude anular reservacion"); 
	}
	else $fo->err("LTPOST-RET-5", "No pude actualizar estado local"); 
	
	return $isok;
}

function contratos_acuint($fo, $lto, $contrato_id)
{
	$isok = true;

	//if ($lto->nuevo)
	if (false)
	{
		$isok = false;
		$q = sprintf("INSERT INTO pgacuint_deuda " . 
			"(SELECT reservacion_id, fecha, precio * 0.03, %d, local_cid " .
			"FROM reservaciones " .  
			"LEFT JOIN locales ON locales.local_id=reservaciones.local_id " .
			"WHERE reservacion_id=%d)", $contrato_id, $lto->fa['reservacion_id']->v);
		if (mysql_query($q) !== false)
		{
			$isok = true;
		}
		else $fo->qerr("PGACUINTCREAR-1");
	}
	return $isok;
}

function contratos_post($fo, $lto, $contrato_id)
{
	$isok = false;
	
	$local_id = $lto->fa['local_id']->v;
	$local_cid = $lto->fa['local_cid']->v;

	if ($lto->fa['sinipc']->v == 1)
	{
		$seguir = false;
		$q = sprintf("SELECT area*prxm2 AS precio_chk, precio, reserva " .
			"FROM locales WHERE local_id=%d", $local_id);
		if (($res = mysql_query($q)) !== false)
		{
			if (($o = mysql_fetch_object($res)) !== false)
			{
				if (round($o->precio, 0) == round($o->precio_chk, 0))
				{
					$precio = $inicial = $o->precio;
					$cuo_base = ($inicial - $o->reserva) / $lto->fa['no_giros']->v;
					$q = sprintf("UPDATE locales SET inicial=precio, cuo_cant=%d, " .
						"cuo_mon=%f, modificado=NOW(), uid=%d ". 
						"WHERE local_id=%d", $lto->fa['no_giros']->v,
						$cuo_base, $_SESSION['uid'], $local_id);
					if (mysql_query($q) !== false)
					{
						if (mysql_affected_rows() == 1) $seguir = true;
					}
					else $fo->qerr("COUP-2");
				}
				else $fo->err("COUP-3", "Inconsistencia en precio de local");
			}
			mysql_free_result($res);
		}
		else $fo->qerr("COUP-1");
	}
	else
	{
		$precio = nen($_REQUEST['precio']);
		$inicial = nen($_REQUEST['inicial']);
		$seguir = true;
	}

	if ($seguir)
	{
		if (local_estado_set($fo, $local_id, LOCSTATUS_VENDIDO))
		{
			$gx = array();
			if (giros_generate($gx, $_REQUEST['reservacion_id'], $contrato_id))
			{
				if (giros_save($gx, $contrato_id, $local_cid, $fo))
				{
					$unm = $_SESSION['unm'];
					$codoc = sprintf("CO%010d", $contrato_id);
					$concepto = sprintf("Contrato #%d local %s", $contrato_id, $local_cid);
					$adux = new adux_transaction(0, "CXC", "CO", $codoc, $gx['emision'], 
						$concepto, $unm, false, 0, "CONEW", $contrato_id);
					foreach ($gx['a'] as $gr)
					{
						$grdoc = sprintf("GR%010d%02d", $contrato_id, $gr['correlativo']);
						$adux->detalle_add($fo, "CO", $codoc, $gr['neto'], "GR", $grdoc, 
							$local_cid, "");
					}
					$remonto = $precio - $inicial;
					if ($remonto > 0)
					{
						$doc_vence = fecha_rotar($gx['a'][$gx['sz'] - 1]['vencimiento'], 1);
						$doc_vence['d'] = fecha_ultimo($doc_vence);
						$redoc = sprintf("RE%010d", $contrato_id);
						
						$cxc = new cxc_lote();
						$cxc->add();
						$cxc->a[$cxc->ndx]->doc_tipo = 'RE';
						$cxc->a[$cxc->ndx]->doc_no = $redoc;
						$cxc->a[$cxc->ndx]->doc_monto = $remonto * -1;
						$cxc->a[$cxc->ndx]->doc_fecha = fecha2time($gx['emision']);
						$cxc->a[$cxc->ndx]->doc_vence = fecha2time($doc_vence);
						$cxc->a[$cxc->ndx]->concepto = sprintf("Resto Contrato #%d Local %s", $contrato_id, $local_cid);
						$cxc->a[$cxc->ndx]->cliente_id = $_REQUEST['cliente_id'];
						$cxc->a[$cxc->ndx]->local_id = $local_id;
						$cxc->a[$cxc->ndx]->origen = "CON";
						if ($cxc->insertar($fo))
						{
							if (contratos_acuint($fo, $lto, $contrato_id))
							{
								$adux->detalle_add($fo, "CO", $codoc, $remonto, "RE", $redoc,
								$local_cid, "");
								$isok = $adux->send($fo);
							}
						}
					}
					else $isok = $adux->send($fo);
				}
			}
		}
	}

	return $isok;
}

function lto_postprocess(ltable $lto, $campo, $valor, lt_form $fo)
{
	$isok = true;
	
	$tabla = $lto->tabla;
	
	if ($lto->forzar_valor) $recid = $valor;
	else $recid = $lto->nuevo ? $lto->insert_id : $valor;
	
	if ($tabla == 'locales')
	{
		if ($lto->nuevo) $isok = local_estado_set($fo, $recid, LOCSTATUS_DISP);
	}

	if ($tabla == 'cxc_entregas')
	{
		if ($lto->retlnk != '')
		{
			$surl = $lto->retlnk;
			if ($surl== "ltable_olib_main.php?tabla=contratos")
			{
				$fo->par();
				$fo->frm('contrat_entrega_rec.php');
				$fo->hid('valor', $lto->fa['cxc_id']->v);
				$fo->sub('Recibo imprimible');
				$fo->parx();
			}
		}
		else
		{
			$surl = sprintf("pagos_editar.php?local_id=%d&cxc_id=%d&ac=1",
				$lto->fa['local_id']->v, $lto->fa['cxc_id']->v);			
		}
		$fo->par();
		$fo->lnk($surl, "Volver al formulario anterior");
		$fo->parx();
	}
	
	if ($tabla == 'contrato_suscripcion')
	{
		// TODO: auto-regreso -> contrat_suscr.php
		$fo->par();
		$fo->lnk("contrat_suscr.php", "Regresar");
		$fo->parx();
	}
	
	if ($tabla == 'clientes')
	{
		if ($lto->nuevo)
		{
			sms_telf_add(1, $lto->fa['nombres0']->v,  $lto->fa['apellidos0']->v, 
				$lto->fa['telmovil']->v);
		}
		else
		{
			sms_telf_change(1, $lto->fa['nombres0']->v,  $lto->fa['apellidos0']->v, 
				$lto->fa['telmovil']->v);
		}
		
		// actualizar smartrt.directorio
		$scond = sprintf("UPPER(TRIM(nombres))=UPPER(TRIM('%s')) AND UPPER(TRIM(apellidos))=UPPER(TRIM('%s'))",
			$lto->fa['nombres0']->v, $lto->fa['apellidos0']->v);
		if ($lto->fa["persona"]->v == 'j') $scond = sprintf("UPPER(TRIM(nombres))=UPPER(TRIM('%s'))", $lto->fa['razon']->v);
		$qd1 = new myquery($fo, sprintf("SELECT dirtel_id FROM smartrt.directorio WHERE %s", $scond), "LTPOST-DIRUP-1", false);
		if ($qd1->isok)
		{
			$nm = array($lto->fa['nombres0']->v, $lto->fa['apellidos0']->v);
			if ($lto->fa["persona"]->v == 'j') $nm = array($lto->fa['razon']->v, "");
			if ($qd1->sz > 0) $q = sprintf("UPDATE smartrt.directorio SET no1='%s',no2='%s',no3='%s',".
				"email='%s',direccion='%s' WHERE dirtel_id=%d",
				$lto->fa['telhab']->v, $lto->fa['teltrab']->v, $lto->fa['telmovil']->v, $lto->fa['email']->v, 
				$lto->fa['direccion']->v, $qd1->r->dirtel_id);
			else $q = sprintf("INSERT INTO smartrt.directorio VALUES(0,'%s',1,'%s',2,'%s',3,'',8,'',8,'%s','%s','%s','%s')", 
				$lto->fa['telhab']->v, $lto->fa['teltrab']->v, $lto->fa['telmovil']->v, $lto->fa['email']->v, 
				$nm[0], $nm[1], $lto->fa['direccion']->v);
			$qd2 = new myquery($fo, $q, "LTPOST-DIRUP-2", true, true); 
		} 
	}

	if ($tabla == 'crm_soldes')
	{
		$isok = local_estado_set($fo, $lto->fa['local_id']->v, LOCSTATUS_SOLDESIST);
		// TODO: asuntos
	}
	
	if ($tabla == 'reserva_retiros')
	{
		if ($lto->nuevo) $lto->fa['retiro_id']->v = $recid;
		//$fo->parc("retiro_id=". $lto->fa['retiro_id']->v." -".$recid);
		$isok = retiros_post($fo, $lto);
	}

	if ($tabla == 'prereserva')
	{
		$isok = local_estado_set($fo, $lto->fa['local_id']->v, LOCSTATUS_APARTADO);
	}
	
	if ($tabla == 'usuarios')
	{
		if ($lto->nuevo)
		{
			usuario_deps_new($fo, $recid, $_REQUEST['usertype_id']);
			sms_telf_add(2, $_REQUEST['nombres'], $_REQUEST['apellidos'], 
				$_REQUEST['telmovil']);
			$qud = new myquery($fo, sprintf("INSERT INTO dashboard_det VALUES ".
				"(1,1,%d,1,'Tareas favoritas','favoritas_show.php',0,0,0)",
				$recid),"USRDASH-1",true,true);
		}
		else
		{
			sms_telf_change(2, $_REQUEST['nombres'], $_REQUEST['apellidos'], 
				$_REQUEST['telmovil']);
		}
	}
	
	if ($tabla == 'contratos')
	{
		$isok = contratos_post($fo, $lto, $recid);
	}
	
	return $isok;
}

function ltup_depcorre($fo, $lto)
{
	$isok = false;
	
	$cta_no = "0000";
	$q = sprintf("SELECT RIGHT(numero, 4) AS no FROM cuentas WHERE cta_id=%d", $lto->fa['cta_id']->v);
	if (($res = mysql_query($q)) !== false)
	{
		if (($oc = mysql_fetch_object($res)) !== false)
		{
			$lafe = fecha_from($lto->fa['dep_fe']->v);
			$dpcorre = depcaja_correlativo($fo, $oc->no, $lafe['a'], $lafe['m'], false); 
			if ($dpcorre !== "")
			{
				$lto->fa['correlativo']->v = $dpcorre;
				$isok = true;
			}
			else
			{ 
				$fo->err("LTUP-DEPCORRE-1", "No pude obtener correlativo");
			}
		}
		mysql_free_result($res);
	}
	else $fo->qerr("LTUP-DEPCORRE-1");
	
	return $isok;	
}

function lto_preprocess($lto, $campo, $valor, $fo)
{
	$isok = true;

	if ($lto->tabla == 'contratos')
	{
		if ($lto->nuevo)
		{
			$isok = true;
		}
		else
		{
			$fo->err("LTUPCON-1", "Contrato ya generado, no se puede modificar"); 
			$isok = false;
		}
	}
	
	if ($lto->tabla == 'reservaciones')
	{
		$isok = false;
		$rsid = $lto->fa['reservacion_id']->v;
		if (ltable_histo($fo, "reservaciones", "reservacion_id", $rsid, 'E'))
		{
			if (reservaciones_chklocal($rsid, $_REQUEST['local_id']))
			{
				$docno = sprintf("RS%010d", $rsid);
				$isok = cxc_del_bydoc($docno, $fo);
				// TODO: CXC->reversar($docno)
			}
		}
	}
	
	if (strpos("locales,locales_clases,prereserva,crm_soldes,cargo_piso,remodel_cargos," .
		"remodel_tipos,remodel_und", $lto->tabla) !== false)
	{
		if (!$lto->nuevo) $isok = ltable_histo($fo, $lto->tabla, $campo, $valor);
	}

	if ($lto->tabla == "depositos")
	{
		if ($lto->nuevo) $isok = ltup_depcorre($fo, $lto);
		else
		{
			if (ltable_histo($fo, $lto->tabla, "dep_id", $valor))
			{
				if (ltable_histo($fo, "dep_detalle", "dep_id", $valor))
				{
					$fe = fecha_from($lto->fa['dep_fe']->v);
					$q = "SELECT cta_id, YEAR(dep_fe) AS agno, MONTH(dep_fe) AS mes " .
						"FROM depositos WHERE dep_id=".$valor;
					if (($res = mysql_query($q)) !== false)
					{
						if (($ox = mysql_fetch_object($res)) !== false)
						{
							if ($lto->fa['cta_id']->v != $ox->cta_id || 
								($fe['a'] != $ox->agno) || ($fe['m'] != $ox->mes))
							{
								$isok = ltup_depcorre($fo, $lto);
							}
							else $isok = true;
						}
						mysql_free_result($res);
					}
					else $fo->qerr("LTUP-DEP-1");
				}
			}
		}
	}

	if ($lto->tabla == 'reserva_retiros')
	{
		$isok = false;
		$reservacion_id = $lto->fa['reservacion_id']->v;
		if (ltable_histo($fo, "descuentos_minamb", "reservacion_id", $reservacion_id, 'D'))
		{
			$qa = new myquery($fo, sprintf("DELETE FROM descuentos_minamb ".
				"WHERE reservacion_id=%d", $reservacion_id), "RESRETMIN-1", false, true);
			$isok = $qa->isok;
		}
	}
	
	return $isok;
}
?>