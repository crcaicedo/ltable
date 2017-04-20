<?php
require_once RUTA_LT."ltable_olib.php";
require_once RUTA_LT."cxc_depdup.php";
require_once RUTA_LT.'ltable_olib_val_fn.php';

function lt_local_cid($fl, $fo)
{
	$isok = false;
	if (preg_match("/^[A-Z0-9\-]+$/", $fl->text) == 1) $isok = true;
	else pemx($fl, 'solo se permiten letras y numeros y guiones.', $fo);
	
	return $isok;
}

function lt_locid($fl, $fo)
{
	$isok = false;

	if ($fl->text > 0) $isok = true;
	else pemx($fl, 'c&oacute;digo inv&aacute;lido.', $fo);
	
	return $isok;
}

function lt_juridico($lto, $fo)
{
	$isok = false;
	$ok = array (0 => false, false, false, false, false, false, false, false, false);
	if ($lto->fa['persona']->text == 'j')
	{
		$ok[0] = lt_numletra($lto->fa['razon'], $fo);
		$ok[1] = lt_rif($lto->fa['rif'], $fo);
		$ok[2] = lt_numletra($lto->fa['repre'], $fo);
		$ok[3] = lt_entero($lto->fa['repreci'], 1, 99999999, $fo);
		$ok[4] = lt_numletra($lto->fa['reg_cargo'], $fo);
		$ok[5] = lt_numletra($lto->fa['reg_tal'], $fo);
		$ok[6] = lt_valfecha($lto->fa['reg_fecha'], $fo);
		$ok[7] = lt_entero($lto->fa['reg_numero'], 1, 999, $fo);
		$ok[8] = lt_numletra($lto->fa['reg_tomo'], $fo);
		$isok = ($ok[0] && $ok[1] && $ok[2] && $ok[3] && $ok[4] && $ok[5] && $ok[6] && $ok[7] && $ok[8]);
	}
	else
	{
		$isok = true;
	}
	return $isok;
}

function lt_sociedad($lto, $fo)
{
	$isok = false;
	$ok = array (1 => false, false, false, false);

	if ($lto->fa['sociedad']->text == '1')
	{
		for ($ii = 1; $ii <= 4; $ii++)
		{
			if ($lto->fa["activa$ii"]->text == '1')
			{
				$ok1 = lt_forbidden($lto->fa["nombres$ii"], $fo);
				$ok2 = lt_forbidden($lto->fa["apellidos$ii"], $fo);
				$ok3 = lt_entero($lto->fa["ci$ii"], 1, 99999999, $fo);
				$ok[$ii] = $ok1 && $ok2 && $ok3;
			}
			else $ok[$ii] = true;
		}
		$isok = ($ok[1] && $ok[2] && $ok[3] && $ok[4]);
	}
	else $isok = true;
	
	return $isok;
}

function ltv_clientes($lto, $fo)
{
	$isok = false;
	$ok = array (false, false, false, false, false, false, false, false, false, false);
	
	$ok[0] = lt_forbidden($lto->fa['nombres0'], $fo);
	$ok[1] = lt_forbidden($lto->fa['apellidos0'], $fo);
	$ok[2] = lt_entero($lto->fa['ci0'], 1, 99999999, $fo);
	$ok[3] = lt_telefono($lto->fa['telhab'], true, $fo);
	$ok[4] = lt_telefono($lto->fa['telmovil'], false, $fo);
	$ok[5] = lt_telefono($lto->fa['teltrab'], false, $fo);
	$ok[6] = lt_juridico($lto, $fo);
	$ok[7] = lt_sociedad($lto, $fo);
	$ok[8] = lt_forbidden($lto->fa['observ'], $fo);
	$ok[9] = lt_email($lto->fa['email'], $fo);
	
	$isok = ($ok[0] && $ok[1] && $ok[2] && $ok[3] && $ok[4] && $ok[5] && $ok[6] && $ok[7] && $ok[8] && $ok[9]);

	return $isok;
}

function ltv_cuentas(ltable $lto, lt_form $fo)
{
	$ok[0] = lt_digitos($lto->fa['numero'], 20, true, $fo);
	$ok[1] = lt_forbidden($lto->fa['titular'], $fo);
	$ok[2] = false;
	/*if (aclcheck($fo, 30, 3) >= 1)
	{
		$ok[2] = true;
	}
	else $fo->err("ACLCHK", "Failed");*/

	return $ok[0] && $ok[1];
}

function ltv_locales($lto, $fo)
{
	$isok = false;
	$ok = array (0 => false, false, false, false, false, false, false, false, false, false);

	$ok[0] = lt_local_cid($lto->fa['local_cid'], $fo);
	$ok[1] = lt_numerico($lto->fa['area'], 3, 0, 999, $fo);
	$ok[2] = lt_numerico($lto->fa['tolera'], 2, 0, 2, $fo);
	$ok[3] = lt_numerico($lto->fa['prxm2'], 2, 0, 9999999, $fo);
	$precio = nen($lto->fa['area']->text) * nen($lto->fa['prxm2']->text);
	$inicial = $precio * (nen($lto->fa['inicial_porc']->text) / 100);
	$reserva = $inicial * (nen($lto->fa['reserva_porc']->text) / 100);
	$ok[4] = lt_numerico($lto->fa['precio'], 2, $precio, 99999999, $fo);
	if ($ok[4])
	{
		$elprecio = nen($lto->fa['precio']->text);
		$ok[5] = lt_numerico($lto->fa['inicial'], 2, 0, $elprecio, $fo);
		$ok[6] = lt_numerico($lto->fa['reserva'], 2, 0, $elprecio, $fo);
		$ok[7] = lt_entero($lto->fa['cuo_cant'], 1, 24, $fo);
		if ($ok[5] && $ok[6] && $ok[7])
		{
			$basegiro = nen($lto->fa['inicial']->text) - nen($lto->fa['reserva']->text);
			$cuo_mon = $basegiro / $lto->fa['cuo_cant']->text;
			$ok[8] = lt_numerico($lto->fa['cuo_mon'], 2, $cuo_mon, $basegiro, $fo);
		}
	}
	$ok[9] = lt_forbidden($lto->fa['observ'], $fo);
	
	return ($ok[0] && $ok[1] && $ok[2] && $ok[3] && $ok[4] && $ok[5] && $ok[6] && $ok[7] && $ok[8] && $ok[9]);
}

function ltv_bancos($lto, $fo)
{
	$ok = array(false, false);
	
	if (enblanco($lto->fa['nombre']->text)) 
		pemx($lto->fa['nombre'], "est&aacute; vacio.", $fo);
	else $ok[0] = lt_forbidden($lto->fa['nombre'], $fo);
	
	if (strlen(trim($lto->fa['abrev']->text)) < 3)
		pemx($lto->fa['abrev'], "debe tener 3 o mas caracteres.", $fo);
	else
		$ok[1] = lt_forbidden($lto->fa['abrev'], $fo);
		
	return $ok[0] && $ok[1];
}

function lt_chqmon($fl, $fl2, $rs, $fo)
{
	$isok = false;

	// 26/03/2008: modificado para permitir excedente
	if (lt_numerico($fl, 2, 0, 9999999, $fo))
	{
		if ($fl->text != 0) $isok = lt_cheque($fl2, true, $fo);
		else $isok = true;
	}

	return $isok;
}

function lt_depmon($fl, $fl2, $rs, $fo)
{
	$isok = false;

	// 26/03/2008: modificado para permitir excedente
	if (lt_numerico($fl, 2, 0, 9999999, $fo))
	{
		if ($fl->text != 0) $isok = lt_deposito($fl2, true, $fo);
		else $isok = true;
	}

	return $isok;
}

function lt_resmon($lto, $fo)
{
	$isok = false;
	
	$rs = nen($lto->fa['reserva']->text);
	
	$cond1 = lt_chqmon($lto->fa['cheque_mon0'], $lto->fa['cheque_no0'], $rs, $fo) 
		&& lt_chqmon($lto->fa['cheque_mon1'], $lto->fa['cheque_no1'], $rs, $fo) 
		&& lt_chqmon($lto->fa['cheque_mon2'], $lto->fa['cheque_no2'], $rs, $fo);
	$cond2 = lt_depmon($lto->fa['deposito_mon0'], $lto->fa['deposito_no0'], $rs, $fo) 
		&& lt_depmon($lto->fa['deposito_mon1'], $lto->fa['deposito_no1'], $rs, $fo) 
		&& lt_depmon($lto->fa['deposito_mon2'], $lto->fa['deposito_no2'], $rs, $fo);
	if ($cond1 && $cond2)
	{
		$suma = 0;
		$campa = array('cheque_mon0', 'cheque_mon1', 'cheque_mon2',
			'deposito_mon0', 'deposito_mon1', 'deposito_mon2');
		foreach ($campa as $cn)
		{
			$suma += nen($lto->fa[$cn]->text);
		}
		if ($suma != $rs)
		{
			$fo->parc("La suma de los montos de los cheques y " .
				"los dep&oacute;sitos <b>Bs.F. $suma </b> difiere del " .
				"monto total de la reserva <b>Bs.F. $rs </b>");
		}
		$isok = true;
	}

	return $isok;
}

function ltv_reservaciones($lto, $fo)
{
	$ok = array (0 => false, false, false, false, false, false, false, false, false, false);

	$ok[0] = lt_valfecha($lto->fa['fecha'], $fo);
	$ok[0] = true;
	$ok[1] = lt_cheque($lto->fa['cheque_no0'], false, $fo);
	$ok[2] = lt_cheque($lto->fa['cheque_no1'], false, $fo);
	$ok[3] = lt_cheque($lto->fa['cheque_no2'], false, $fo);
	$ok[4] = lt_deposito($lto->fa['deposito_no0'], false, $fo);
	$ok[5] = lt_deposito($lto->fa['deposito_no1'], false, $fo);
	$ok[6] = lt_deposito($lto->fa['deposito_no2'], false, $fo);
	$ok[7] = !enblanco($lto->fa['cheque_no0']->text) 
		|| !enblanco($lto->fa['cheque_no1']->text) 
		|| !enblanco($lto->fa['cheque_no2']->text) 
		|| !enblanco($lto->fa['deposito_no0']->text) 
		|| !enblanco($lto->fa['deposito_no1']->text) 
		|| !enblanco($lto->fa['deposito_no2']->text);
	$ok[8] = lt_locid($lto->fa['local_id'], $fo);
	$ok[9] = lt_resmon($lto, $fo);

	if (!$ok[7])
	{
		$fo->parc("Especifique un n&uacute;mero de dep&oacute;sito " .
			"o un n&uacute;mero de cheque.");
	}
	
	return ($ok[0] && $ok[1] && $ok[2] && $ok[3] && $ok[4] && $ok[5] && $ok[6] && $ok[7] && $ok[8] && $ok[9]);
}

function lt_resid($fl, $fo)
{
	$isok = false;

	if ($fl->text > 0) $isok = true;
	else pemx($fl, 'c&oacute;digo inv&aacute;lido.', $fo);
	
	return $isok;
}

function ltv_contratos($lto, $fo)
{
	$isok = false;

	$ok[0] = lt_forbidden($lto->fa['observ'], $fo);
	$ok[2] = lt_resid($lto->fa['reservacion_id'], $fo);
	$suma = nen($lto->fa['sumapagos']->text);
	$reserva = nen($lto->fa['reserva']->text);
	$precio = nen($lto->fa['precio']->text);
	if ($reserva < $precio && $suma < -1)
	{
		$fo->parc("El monto pagado (<b>Bs.F. $suma</b>) " .
			"es menor al monto de la reserva (<b>Bs.F. $reserva</b>).");
		$ok[3] = false;
	}
	else
	{
		$ok[3] = true;
	}

	return ($ok[0] && $ok[2] && $ok[3]);
}

function ltv_depositos($lto, $fo)
{
	$isok = false;
	
	$ok[0] = lt_digitos($lto->fa['dep_no'], 12, true, $fo);
	$ok[1] = lt_valfecha($lto->fa['dep_fe'], $fo);
	$ok[2] = lt_forbidden($lto->fa['observ'], $fo);

	if ($ok[0] && $ok[1] && $ok[2])
	{
		if ($lto->nuevo)
		{
			$isok = dep_nodup($fo, $lto->fa['dep_no']->text, 0);
		}
		else
		{
			$isok = dep_nodup($fo, $lto->fa['dep_no']->text, $lto->fa['dep_id']->text);			
		}		
	}
	
	return $isok;
}

function ltv_smslistas($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['descripcion'], $fo);
	$ok[1] = lt_forbidden($lto->fa['observaciones'], $fo);
	
	return $ok[0] && $ok[1];
}

function ltv_smstelefonos($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['nombres'], $fo);
	$ok[1] = lt_forbidden($lto->fa['apellidos'], $fo);
	$ok[2] = lt_telefono($lto->fa['telefono'], true, $fo);

	return $ok[0] && $ok[1] && $ok[2];
}

function ltv_feriados($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['nombre'], $fo);
	$ok[1] = lt_entero($lto->fa['dia'], 1, 31, $fo);
	$ok[2] = lt_entero($lto->fa['mes_id'], 1, 12, $fo);
	$ok[3] = lt_entero($lto->fa['agno_id'], 0, 2032, $fo);
	
	return $ok[0] && $ok[1] && $ok[2] & $ok[3];
}

function ltv_paises($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['nombre'], $fo);
	$ok[1] = lt_forbidden($lto->fa['gentil_m'], $fo);
	$ok[2] = lt_forbidden($lto->fa['gentil_f'], $fo);
	$ok[3] = lt_forbidden($lto->fa['abrev'], $fo);

	return $ok[0] && $ok[1] && $ok[2] & $ok[3];
}

function ltv_ipc($lto, $fo)
{
	$ok[0] = lt_numerico($lto->fa['factor'], 2, 0, 100, $fo);
	
	return $ok[0];
}

function ltv_resret_razones($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['descripcion'], $fo);

	return $ok[0];
}

function ltv_reserva_retiros($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['retraz_deta'], $fo);

	return $ok[0];
}

function ltv_municipios($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['nombre'], $fo);

	return $ok[0];
}

function ltv_parroquias($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['nombre'], $fo);

	return $ok[0];
}

function ltv_dolar($lto, $fo)
{
	$ok[0] = lt_valfecha($lto->fa['fecha'], $fo);
	$ok[1] = lt_numerico($lto->fa['monto'], 2, 0, 99999, $fo);
	$ok[2] = lt_forbidden($lto->fa['fuente'], $fo);
	
	return $ok[0] && $ok[1] && $ok[2];
}

function ltv_mensajeros($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['nombres'], $fo);
	$ok[1] = lt_forbidden($lto->fa['apellidos'], $fo);
	$ok[2] = lt_entero($lto->fa['ci'], 0, 99999999, $fo);
	$ok[3] = lt_telefono($lto->fa['telmovil'], $fo);
	
	return $ok[0] && $ok[1] && $ok[2] && $ok[3];
}

function ltv_cxcentregas($lto, $fo)
{
	$ok[0] = lt_forbidden($lto->fa['doc_no'], $fo);
	$ok[1] = lt_forbidden($lto->fa['observ'], $fo);
	$ok[2] = lt_valfecha($lto->fa['fecha'], $fo);
	$ok[3] = lt_forbidden($lto->fa['otro_nombres'], $fo);
	$ok[4] = lt_forbidden($lto->fa['otro_apellidos'], $fo);
	$ok[5] = lt_entero($lto->fa['otro_ci'], 1, 999999999, $fo);
	
	return $ok[0] && $ok[1] && $ok[2] && $ok[3] && $ok[4] && $ok[5];
}

function ltv_consus($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['local_cid'], $fo);
	return $ok[0];	
}

function ltv_ciudades($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['nombre'], $fo);
	$ok[1] = lt_digitos($lto->fa['codtel'], 4, $fo);
	return $ok[0] && $ok[1];
}

function validar_caracteristica($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);
	$ok[1] = lt_novacio($lto->fa['descripcion_ingles'], $fo);
	return $ok[0] && $ok[1]; 
}

function validar_almacen($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);
	return $ok[0];
}

function validar_almacenes_tipos($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);
	return $ok[0];
}

function  validar_tiendas($lto, $fo)
{
	
	$ok[0] = lt_novacio($lto->fa['nombre'], $fo);
	$ok[1] = lt_novacio($lto->fa['suc_id'], $fo);
	$ok[3] = lt_novacio($lto->fa['direccion'], $fo);
	$ok[4] = lt_entero($lto->fa['suc_id'], 001, 999, $fo);
	return $ok[0] && $ok[1]  && $ok[3] && $ok[4] ;
}

function  validar_productos_familas($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);	
	return $ok[0];
}

function  validar_productos_marcas($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);
	return $ok[0];
}

function  validar_productos_modelos($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);
	//$ok[1] = lt_entero($lto->fa['codigo'], 0, 99, $fo);
	return $ok[0] /*&& $ok[1]*/;
}

function  validar_productos_status($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);
	return $ok[0];
}

function  validar_productos($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['fi_codigo_prod'], $fo);
	
	/*$ok[1] = lt_numerico($lto->fa['peso'], 2, 0, 99999, $fo);
	$ok[2] = lt_numerico($lto->fa['volumen'], 2, 0, 99999, $fo);
	$ok[3] = lt_numerico($lto->fa['alto'], 2, 0, 99999, $fo);
	$ok[4] = lt_numerico($lto->fa['largo'], 2, 0, 99999, $fo);
	$ok[5] = lt_numerico($lto->fa['ancho'], 2, 0, 99999, $fo);*/
	
	$ok[6] = lt_novacio($lto->fa['cod_adux'], $fo);

	$ok[13] = lt_numerico($lto->fa['minimo'], 2, 0, 99999, $fo);
	$ok[14] = lt_numerico($lto->fa['maximo'], 2, 0, 99999, $fo);

	return $ok[6] && $ok[13] &&  $ok[14] ;
}

function  validar_asignaciones_usos($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);

	return $ok[0];
}

function  validar_asignacion($lto, $fo)
{
	/*$ok[0] = lt_novacio($lto->fa['serial'], $fo);
	$ok[1] = lt_novacio($lto->fa['codigo_interno'], $fo);*/
	$ok[2] = lt_entero($lto->fa['cantidad'], 1, 999, $fo);
	$ok[3] = lt_novacio($lto->fa['ubicacion'], $fo);
	$ok[4] = lt_novacio($lto->fa['observ'], $fo);
	
	return /*$ok[0] &&  $ok[1] &&*/  $ok[2] &&  $ok[3] &&  $ok[4];
}

function  validar_color($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);
	return $ok[0];
}

function  validar_variantes($lto, $fo)
{
	$ok[0] = lt_novacio($lto->fa['codigo'], $fo);
	$ok[1] = lt_novacio($lto->fa['descripcion'], $fo);
	return $ok[0] && $ok[1];
}

/*function validar_cajachica($lto, $fo){
	
	$ok[0] = lt_novacio($lto->fa['descripcion'], $fo);
	$ok[1] = lt_novacio($lto->fa['fondo_fijo'], $fo);
	$ok[2] = lt_novacio($lto->fa['cajachica_abrev'], $fo);
	return $ok[0] && $ok[1] && $ok[2];	
}*/

function validar_proveedores($lto, $fo){

	$ok[0] = lt_novacio($lto->fa['nombre'], $fo);
	$ok[1] = lt_novacio($lto->fa['direccion'], $fo);
	$ok[2] = lt_telefono_int($lto->fa['telefono'], false, $fo);
	$ok[3] = lt_telefono_int($lto->fa['telefono2'], false, $fo);
	$ok[4] = lt_novacio($lto->fa['contacto'], $fo);
	$ok[5] = lt_email($lto->fa['email'], $fo);
	$ok[6] = lt_email($lto->fa['email2'], $fo);
	$ok[7] = lt_telefono_int($lto->fa['contacto_telno'], false, $fo);
	$ok[8] = lt_email($lto->fa['contacto_email'], $fo);
	//$ok[9] = lt_novacio($lto->fa['codigo'], $fo);
	//$ok[10] = lt_novacio($lto->fa['direccionMandarin'], $fo);
	//$ok[11] = lt_novacio($lto->fa['idalibaba'], $fo);
	//$ok[12] = lt_novacio($lto->fa['codfinalprov'], $fo);
	
	return $ok[0] && $ok[1];
}

function lto_validate($lto, $fo=false)
{
	$isok = false;

	if ($lto->tabla == 'bancos') $isok = ltv_bancos($lto, $fo);
	if ($lto->tabla == 'ciudades') $isok = ltv_ciudades($lto, $fo);
	if ($lto->tabla == 'clientes') $isok = ltv_clientes($lto, $fo);
	if ($lto->tabla == 'contrato_suscripcion') $isok = ltv_consus($lto, $fo);
	if ($lto->tabla == 'cuentas') $isok = ltv_cuentas($lto, $fo);
	if ($lto->tabla == 'cxc_entregas') $isok = ltv_cxcentregas($lto, $fo);
	if ($lto->tabla == 'dolar') $isok = ltv_dolar($lto, $fo);
	if ($lto->tabla == 'ipc') $isok = ltv_ipc($lto, $fo);
	if ($lto->tabla == 'depositos') $isok = ltv_depositos($lto, $fo);
	if ($lto->tabla == 'feriados') $isok = ltv_feriados($lto, $fo);
	if ($lto->tabla == 'mensajeros') $isok = ltv_mensajeros($lto, $fo);
	if ($lto->tabla == 'municipios') $isok = ltv_municipios($lto, $fo);
	if ($lto->tabla == 'paises') $isok = ltv_paises($lto, $fo);
	if ($lto->tabla == 'parroquias') $isok = ltv_parroquias($lto, $fo);
	if ($lto->tabla == 'resret_razones') $isok = ltv_resret_razones($lto, $fo);
	if ($lto->tabla == 'reserva_retiros') $isok = ltv_reserva_retiros($lto, $fo);
	if ($lto->tabla == 'reservaciones') $isok = ltv_reservaciones($lto, $fo);
	if ($lto->tabla == 'sms_listas') $isok = ltv_sms_listas($lto, $fo);
	if ($lto->tabla == 'locales') $isok = ltv_locales($lto, $fo);
	if ($lto->tabla == 'contratos') $isok = ltv_contratos($lto, $fo);

// TODO: missing validations
	if ($lto->tabla == 'sms_recargas') $isok = true;
	if ($lto->tabla == 'locales_clases') $isok = true;
	if ($lto->tabla == 'locales_pisos') $isok = true;
	if ($lto->tabla == 'locales_status') $isok = true;
	if ($lto->tabla == 'locales_ubicaciones') $isok = true;
	if ($lto->tabla == 'proyectos') $isok = true;
	if ($lto->tabla == 'usuarios') $isok = true;
	if ($lto->tabla == 'prereserva') $isok = true;
	if ($lto->tabla == 'crm_soldes') $isok = true;
	if ($lto->tabla == 'locales_modulos') $isok = true;
	if ($lto->tabla == 'cargo_piso') $isok = true;
	if ($lto->tabla == 'remodel_cargos') $isok = true;
	if ($lto->tabla == 'remodel_tipos') $isok = true;
	if ($lto->tabla == 'remodel_und') $isok = true;
	if ($lto->tabla == 'productos') $isok = validar_productos($lto, $fo);	
	if ($lto->tabla == 'maquinas') $isok = true;
	if ($lto->tabla == 'caracteristicas') $isok = validar_caracteristica($lto, $fo);
	if ($lto->tabla == 'almacenes') $isok = validar_almacen($lto, $fo);
	if ($lto->tabla == 'almacenes_tipos') $isok = validar_almacenes_tipos($lto, $fo);
	if ($lto->tabla == 'tiendas') $isok = validar_tiendas($lto, $fo);
	//if ($lto->tabla == 'productos_barcodes') $isok = true;
	if ($lto->tabla == 'productos_familias') $isok = validar_productos_familas($lto, $fo);
	if ($lto->tabla == 'productos_marcas') $isok = validar_productos_marcas($lto, $fo);
	if ($lto->tabla == 'productos_modelos') $isok = validar_productos_modelos($lto, $fo);
	if ($lto->tabla == 'productos_status') $isok = validar_productos_status($lto, $fo);
	if ($lto->tabla == 'inv_asignaciones_usos') $isok = validar_asignaciones_usos($lto, $fo);
	if ($lto->tabla == 'inv_asignaciones') $isok = validar_asignacion($lto, $fo);
	if ($lto->tabla == 'color') $isok = validar_color($lto, $fo);
	if ($lto->tabla == 'productos_variantes') $isok = validar_variantes($lto, $fo);
	//if ($lto->tabla == 'cajachica') $isok = validar_cajachica($lto, $fo);
	if ($lto->tabla == 'proveedores') $isok = validar_proveedores($lto, $fo);
	
	if (strpos("canales,destinos,operadoras,paises,planes,cl_clientes,extensiones,".
			"inet_usuarios,ventasdesdeusa_pr,vendedores,prefactura_ccmachine,prefactura_series,tiendas_cl,".
			"notificaciones_usrcta,notificaciones_usrcta_tp,prefactura_tpu,vendedores_cp,ml_cuentas,publicaciones,empleados".
			"ml_publicaciones,plantillas,publicaciones_ml,zonas,cond_propietarios", $lto->tabla) !== false) $isok = true;

	if ($isok) $fo->ok("Datos validados");
	else $fo->err("LTVAL-1", "Favor verifique los campos especificados");

	return $isok;
}
?>