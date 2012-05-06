<?php
require_once "ltable_olib.php";
require_once "local_estado.php";
require_once "reserv_hist.php";
require_once "usuario_deps.php";
require_once "cxcfn.php";
require_once "contrat_gengr.php";
require_once "adux_fn.php";

function reservdel_adux($fo, $reservacion_id)
{
	$isok = false;
	
	$q = sprintf("SELECT utes(fecha) AS fecha, reserva, local_cid, empresa2, rs.reservacion_id " .
		"FROM reservaciones AS rs " .
		"LEFT JOIN locales AS lc ON lc.local_id=rs.local_id " .
		"LEFT JOIN proyectos AS pry ON pry.proyecto_id=lc.proyecto_id " .
		"WHERE reservacion_id=%d", $reservacion_id);
	if (($res = mysql_query($q)) !== false)
	{
		if (($o = mysql_fetch_object($res)) !== false)
		{
			$rsdoc = sprintf("RS%010d", $reservacion_id);
			$concepto = sprintf("Reverso de reservacion #%d", $reservacion_id); 
			$unm = $_SESSION['unm'];
			$adux = new adux_transaction(0, "CXC", "RSA", $rsdoc, ctod($o->fecha), 
				$concepto, $unm, true, 0, "RSDEL", $reservacion_id);
			$adux->detalle_add($fo, "RS", $rsdoc, $o->reserva, "RS", $rsdoc, $o->local_cid, "");
			if ($adux->send($fo))
			{
				$vcok = false;
				$q = sprintf("SELECT doc_no, doc_monto, utes(doc_fecha) AS doc_fecha, doc_monto, " .
					"concepto " .
					"FROM cxc WHERE doc_no REGEXP '^EMISION%06d'",
					$o->reservacion_id);
				//$fo->parc($q);
				if (($reh = mysql_query($q)) !== false)
				{
					if (mysql_num_rows($reh) > 0)
					{
						while (($oh = mysql_fetch_object($reh)) !== false)
						{
							$vcadux = new adux_transaction(0, "CXC", "VC", $oh->doc_no, 
								$oh->doc_fecha, $oh->concepto, $unm, true, $o->empresa2, "RSDELVC", $reservacion_id);
							$vcadux->detalle_add($fo, "VC", $oh->doc_no, $oh->doc_monto * -1, 
								"VC", $oh->doc_no, $o->local_cid, "");
							$vcok = $vcadux->send($fo);
							unset($vcadux);
						}
					}
					else $vcok = true;
					mysql_free_result($reh);
				}
				else $fo->qerr("RSDEL-ADUX-2");
				
				// TODO: asociar/indexar pgr_id,reservacion_id
				$nok = 0; 
				$nto = -1;
				$qx = sprintf("SELECT pgr_docs.pgr_id, SUM(doc_monto) AS doc_monto, origen " .
					"FROM pgr_docs " .
					"LEFT JOIN pgr ON pgr_docs.pgr_id=pgr.pgr_id " .
					"WHERE doc_no='%s' " .
					"GROUP BY pgr_id", $rsdoc);
				//$fo->parc($qx);
				if (($resx = mysql_query($qx)) !== false)
				{
					$nto = mysql_num_rows($resx);
					if ($nto > 0)
					{
						while (($ox = mysql_fetch_object($resx)) !== false)
						{ 
							$qy = sprintf("SELECT COUNT(*) AS cnt FROM pgr_pagos " .
								"WHERE pgr_id=%d ", $ox->pgr_id);
							if (($rey = mysql_query($qy)) !== false)
							{
								if (($oy = mysql_fetch_object($rey)) !== false)
								{
									if ($oy->cnt > 0)
									{
										if (pgrdel_adux($fo, $ox->pgr_id))
										{
											if (pgr_deldo($fo, $ox->pgr_id, $ox->origen))
											{
												$nok++;
											}
										}
									}
									else $nok++;
								}
								mysql_free_result($rey);
							}
						}
					}
					$isok = $nto == $nok;
					mysql_free_result($resx);
				}
				else $fo->qerr("RSDEL-ADUX-3");
			}
			
			if ($isok)
			{
				if (cxc_hist_bydoc($rsdoc, 'D', $fo))
				{
					if (cxc_del_bydoc($rsdoc, $fo))
					{
						$isok = true;
					}
				}
			}
		}
		mysql_free_result($res);
	}
	else $fo->qerr("RSDEL-ADUX-1");
	
	return $isok;
}

function depdel_pre($fo, $dep_id)
{
	$isok = false;

	$q = "SELECT dep_no, bancos.nombre AS cta_bco, numero, utes(dep_fe) AS dep_fe, " .
		"dep_mon, RIGHT(numero, 4) AS ucta, dep_origen, depositos.estatus " .
		"FROM depositos " .
		"LEFT JOIN cuentas ON cuentas.cta_id=depositos.cta_id " .
		"LEFT JOIN bancos ON bancos.banco_id=cuentas.banco_id " .
		"WHERE depositos.dep_id=$dep_id";
	if (($res = mysql_query($q)) !== false)
	{
		if (($o = mysql_fetch_object($res)) !== false)
		{
			if (($o->dep_origen == 'CAJ') || 
				(aclcheck($fo, 160, 18, $_SESSION['uid'], $_SESSION['pid']) == 1))
			{
				if (ltable_histo($fo, "depositos", "dep_id", $dep_id, 'D'))
				{
					if (ltable_histo($fo, "dep_detalle", "dep_id", $dep_id, 'D'))
					{
						$seguir = true;
						if ($o->estatus == 0)
						{
							$unm = $_SESSION['unm'];
							$concepto = sprintf("Deposito caja: planilla %s [%d] %s %s %s",
								$o->dep_no, $dep_id, $o->cta_bco, $o->ucta, $o->dep_fe);
							$adpg = new adux_transaction(0, "CAJ", "DPA", $o->dep_no, 
								ctod($o->dep_fe), $concepto, $unm, true, 0, "DPA", $dep_id);
							$adpg->detalle_add($fo, "DP", $o->dep_no, $o->dep_mon, "DP", 
								$o->dep_no, "COB01", $o->numero);
							$seguir = $adpg->send($fo);
						}
						if ($seguir)
						{ 
							$qx = sprintf("DELETE FROM dep_detalle WHERE dep_id=%d", $dep_id);
							if (mysql_query($qx) !== false)
							{
								$isok = true;
							}
						}
					}
				}
			}
			else $fo->err("DEPDELPRE-2", "No es un dep&oacute;sito de caja");
		}
		mysql_free_result($res);	
	}
	else $fo->qerr("DEPDELPRE-1");
	
	return $isok;
}

function contratosdel_reverso($fo, $co)
{
	$isok = false;

	$unm = $_SESSION['unm'];
	$codoc = sprintf("CO%010d", $co->contrato_id);
	$concepto = sprintf("Contrato #%d local %s", $co->contrato_id, $co->local_cid);
	$adux = new adux_transaction(0, "CXC", "CO", $codoc, ctod($co->fecha), $concepto, $unm, true, 0, "CODEL", $contrato_id);

	$nto = $nok = 0;
	$q = sprintf("SELECT correlativo, neto " .
		"FROM giros WHERE contrato_id=%d " .
		"ORDER BY correlativo", $co->contrato_id);
	if (($res = mysql_query($q)) !== false)
	{
		$nto = mysql_num_rows($res);
		while (($gr = mysql_fetch_object($res)) !== false)
		{
			$grdoc = sprintf("GR%010d%02d", $co->contrato_id, $gr->correlativo);
			if (cxc_hist_bydoc($grdoc, 'D'))
			{
				if (cxc_del_bydoc($grdoc, $fo))
				{
					$adux->detalle_add($fo, "CO", $codoc, $gr->neto, "GR", $grdoc, $co->local_cid, "");
					$nok++;
				}
			}
		}

		if ($nto == $nok)
		{
			if ($co->sinipc == 1) $isok = true;
			else
			{
				$redoc = sprintf("RE%010d", $co->contrato_id);
				if (($reo = cxc_geto($fo, $redoc)) !== false)
				{
					if (cxc_hist_bydoc($redoc, 'D', $fo))
					{
						if (cxc_del_bydoc($redoc, $fo))
						{
							$adux->detalle_add($fo, "CO", $codoc, $reo->doc_monto * -1, 
								"RE", $redoc, $co->local_cid, "");
								$isok = $adux->send($fo);
						}
					}
				}
				else
				{ 
					$fo->err("CONDEL-13", "Error cargando documento: $redoc");
					$isok = true;
				}
			}
			
			if ($isok)
			{
				$query = sprintf("DELETE FROM giros WHERE contrato_id=%d", $co->contrato_id);
				if (mysql_query($query) !== false)
				{
					$isok = true;
				}
				else $fo->qerr("CONDEL-11");
			}
		}
		else $fo->err("CODEL-12", "Error borrando d&eacute;bitos: N=$nto,OK=$ok");
				
		mysql_free_result($res);
	}
	else $fo->qerr("CODEL-10");
	
	return $isok;
}

function contratosdel_pre($fo, $contrato_id)
{
	$isok = false;
	
	$q = sprintf("SELECT COUNT(*) AS conteo " .
		"FROM cxc " .
		"WHERE (aplicar_a REGEXP 'GR%010d' OR aplicar_a = 'RE%010d') " .
		"AND FIND_IN_SET(doc_tipo, 'MR,IP') = 0",
		$contrato_id, $contrato_id);
	//$fo->parc($q);
	if (($res = mysql_query($q)) !== false)
	{
		if (($o = mysql_fetch_object($res)) !== false)
		{
			if ($o->conteo == 0)
			{
				$q = sprintf("SELECT resv.local_id, utes(resv.fecha) AS fecha, contrato_id, " .
					"local_cid, sinipc " .
					"FROM contratos AS cntr " .
					"LEFT JOIN reservaciones AS resv ON cntr.reservacion_id=resv.reservacion_id " .
					"LEFT JOIN locales ON locales.local_id=resv.local_id " .
					"WHERE contrato_id=%d", $contrato_id);
				if (($res2 = mysql_query($q)) !== false)
				{
					if (($co = mysql_fetch_object($res2)) !== false)
					{
						if (ltable_histo($fo, "contratos", "contrato_id", $contrato_id, 'D'))
						{
							if (ltable_histo($fo, 'giros', 'contrato_id', $contrato_id, 'D'))
							{
								if (contratosdel_reverso($fo, $co))
								{
									if (local_estado_set($fo, $co->local_id, LOCSTATUS_RESERV))
									{
										@unlink(sprintf("giros/giros%010d.pdf", $contrato_id));
										@unlink(sprintf("contratos/contrato%010d.rtf", $contrato_id));
										
										$q = sprintf("DELETE FROM cxc WHERE doc_no REGEXP 'IP%010d'", 
											$contrato_id);
										//$fo->parc($q);
										if (mysql_query($q) !== false)
										{
											$isok = true;
										}
										else $fo->qerr("LTDELCO-5");
									}
								}
								else $fo->err("LTDELCO-3", "Error reversando");
							}
						}
					}
					else $fo->err("LTDELCO-6", "Error cargando contrato");
					mysql_free_result($res2);
				}
				else $fo->qerr("LTDELCO-4");
			}
			else $fo->err("LTDELCO-2", "Contrato posee pagos, utilize desistimiento");
		}
		mysql_free_result($res);
	}
	else $fo->qerr("LTDELCO-1");
	
	return $isok;	
}

function ltable_del_val($fo, $lto, $tabla, $campo, $valor)
{
	$isok = true;
	$uid = $_SESSION['uid'];
	
	if ($lto->deps($valor, 'D', $fo->buf))
	{		
		if ($tabla == 'reservaciones')
		{
			$isok = false;
			$q = "SELECT local_id, uid FROM reservaciones WHERE reservacion_id=$valor";
			if (($res2 = mysql_query($q)) !== false)
			{
				if (($row = mysql_fetch_assoc($res2)) !== false)
				{
					$delsup = aclcheck($fo, 120, 12, $uid, $_SESSION['pid']);
					if ($uid == $row['uid'] || $delsup)
					{
						if (ltable_histo($fo, "reservaciones", "reservacion_id", $valor, 'D'))
						{
							if (local_estado_set($fo, $row['local_id'], LOCSTATUS_DISP))
							{
								$isok = reservdel_adux($fo, $valor);							
							}
						}
						mysql_free_result($res2);
					}
					else $fo->err("RSDEL-2", "Usuario no registr&oacute; esta reservaci&oacute;n");
				}
			}
			else $fo->qerr("LTDEL-1");
		}
		
		if ($tabla == 'contratos')
		{
			$isok = contratosdel_pre($fo, $valor);
		}
		
		if ($tabla == 'locales')
		{
			$isok = false;
			if (ltable_histo($tabla, $campo, $valor, 'D'))
			{
				$isok = local_estado_del($valor);
			}
		}
		
		if ($tabla == 'usuarios')
		{
			$isok = usuario_deps_del($fo, $valor);
		}
		
		if (strpos("prereserva,crm_soldes,cargo_piso,reserva_retiros," .
			"remodel_cargos,remodel_tipos,remodel_und", $tabla) !== false)
		{
			$isok = ltable_histo($fo, $tabla, $campo, $valor, 'D');
		}

		if ($tabla == "depositos")
		{
			$isok = depdel_pre($fo, $valor);			
		}
	}
	else $isok = false;
	
	if ($isok) hemail($tabla, $campo, $valor, 'D');
	
	return $isok;	
}
?>