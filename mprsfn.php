<?php   
define('USUARIO_UNAUTH', 5);
define('USR_SYS', 47);
define('VIDA_GALLETA', 7200); // 2hr

require_once RUTA_LT.'mprsfn_deprecated.php';
require_once RUTA_LT.'mprsfn_fecha.php';
require_once RUTA_LT.'parametros_class.php';
require_once RUTA_LT.'mprsfn_var.php';

/**
 * 
 * Determina si el string $thestr esta vacio 
 * @param string $thestr
 * @return bool
 * Indica si el string pasado esta vacio o no
 */
function enblanco($thestr)
{
    $vacia = false;
    if ($thestr === NULL || $thestr === FALSE) $vacia = true;
    else
    {
	if (strlen(trim($thestr)) <= 0) $vacia = true;
    }
    return $vacia;
}

/** 
 * Convierte un numero de formato ingles a espanol
 * @param number $numen
 * Numero a formatear
 * @param int $pd
 * (opcional) Posiciones decimales
 * @return string
 * Numero en formato ES
 */
function nes($numen, $pd=2)
{
    return number_format($numen, $pd, ',', '.');
}

/** 
 * Convierte un numero en ingles a formato espanol, y en blanco si es cero
 * @param number $numen
 * @return string
 * Numero en formato ES
 */
function znes($numen)
{
	return $numen != 0 ? number_format($numen, 2, ',', '.'):'';
}

/**
 * Convierte un numero en espanol a formato ingles
 * @param number $numes
 * @return string
 * Numero en formato EN
 */
function nen($numes)
{
    return str_replace(',', '.', str_replace('.', '', $numes));
}

/**
 * 
 * Determinar si los parametros establecidos en el arreglo $para estan siendo enviados mediante el metodo $tpga
 * @param array $para los parametros que se estan pasando
 * @param int $tpga metodo de envio 0 = $POST, 1 = $GET y 2 = $REQUEST
 * @return boolean true si todos los parametros de $para estan siendo pasados de la manera indicado en $tpga, false en caso contrario
 */
function parms_isset($para, $tpga=0)
{
    $isok = true;
    if ($tpga == 0)
    {
    	foreach ($para as $pn)
    	{
			if (!isset($_POST[$pn])) $isok = false;
    	}
    }
    if ($tpga == 1)
    {
    	foreach ($para as $pn)
    	{
			if (!isset($_GET[$pn])) $isok = false;
    	}
    }
    if ($tpga == 2)
    {
    	foreach ($para as $pn)
    	{
			if (!isset($_REQUEST[$pn])) $isok = false;
    	}
    }
    return $isok;
}

function nin($thestr,$suff)
{
    $rstr = $thestr;
    if (enblanco($thestr)) $rstr = '(ningun'.$suff.')';
    return $rstr;
}

function userlevel($nivel=6)
{
	$slevel = array(0=>'Administrador',1=>'Supervisor', 2=>'Promotor',
			3=>'Cliente', 4=>'Invitado', 5=>'No-autorizado', 6=>'(DESCONOCIDO)');
	return (($nivel >= 0) and ($nivel <=6)) ? $slevel[$nivel]: 'Inv&aacute;lido';
}

function proyecto_chooser(&$buf, $ro=false)
{
	$pid = isset($_SESSION['pid']) ? $_SESSION['pid']+0: 0;
	$uid = isset($_SESSION['uid']) ? $_SESSION['uid']+0: 47;
	$tid = isset($_SESSION['tid']) ? $_SESSION['tid']+0: 99;

	$btnd = false;
	$ssmorado = "font-family:Arial,sans-serif;font-size:14px;color:#8000FF;font-weight:bold;";
	if ($ro)
	{
		$q = sprintf("SELECT proyecto_id, nombre, have_tiendas FROM proyectos " .
			"WHERE proyecto_id=%d", $pid);
	}
	else
	{
		$q = sprintf("SELECT proyecto_id, nombre, have_tiendas FROM proyectos " .
			"WHERE proyecto_id IN (SELECT proyecto_id FROM acceso " .
			"WHERE acceso.uid=%d) ORDER BY nombre", $uid);
	}
	if (($res = mysql_query($q)) !== false)
	{
		$sst2 = "font-family:Arial,sans-serif;font-size:11px;";
		if (mysql_num_rows($res) > 1)
		{
			$buf .= sprintf("<td style=\"%s\">Proyecto:</td>", $sst2);
			if ($ro)
			{
				$buf .= sprintf("<td><select name=\"mprsglobalpid\" id=\"mprsglobalpid\" " .
					"style=\"%s\">",
					$ssmorado);			
			}
			else
			{
				$buf .= sprintf("<td><select name=\"mprsglobalpid\" id=\"mprsglobalpid\" " .
					"style=\"%s\" onchange=\"ltpry_choose(this)\">",
					$ssmorado);
			}
			while (($row = mysql_fetch_assoc($res)) !== false)
			{
				$issel = '';
				if ($row['proyecto_id'] == $pid)
				{
					$issel = ' selected';
					if ($row['have_tiendas'] == 1) $btnd = true;
				}
				$buf .= sprintf("<option value=\"%d\"%s>%s</option>",
					$row['proyecto_id'], $issel, $row['nombre']);
			}
			$buf .= "</select>";
			$buf .= "</td>";
			
		}
		else
		{
			if (($row = mysql_fetch_assoc($res)) !== false)
			{
				$buf .= sprintf("<td><input type=\"hidden\" id=\"mprsglobalpid\" ".
					"name=\"mprsglobalpid\" value=\"%d\"></td>", $row['proyecto_id']);
				if ($row['have_tiendas'] == 1) $btnd = true;
			}
		}
		mysql_free_result($res);

		// si proyecto posee tiendas, mostrar selector
		if ($btnd)
		{
			$buf .= '<td>Tienda:</td>';
			if ($ro) $q = sprintf(
				"SELECT tienda_id,nombre,status FROM tiendas ".
				"WHERE tienda_id=%d", $tid);
			else $q = sprintf(
				"SELECT b.tienda_id,nombre,status FROM tiendas a ".
				"LEFT JOIN vendedores b ON a.tienda_id=b.tienda_id ".
				"WHERE b.uid=%d AND vend_estatus=1", $uid);
			if (($qt = mysql_query($q)) !== false)
			{
				$nr = mysql_num_rows($qt);
				if ($nr > 0)
				{
					$onc = $nr > 1 ? " onchange=\"lttnd_choose(this)\"":'';
					$buf .= sprintf("<td><select name=\"mprsglobaltid\" id=\"mprsglobaltid\"%s style=\"%s\">", 
						$onc, $ssmorado);
					while (($ot = mysql_fetch_object($qt)) !== false)
					{
						$issel = $ot->tienda_id == $tid ? ' selected': '';
						$buf .= sprintf("<option value=\"%d\"%s>%s%s</option>",
							$ot->tienda_id, $issel, $ot->status == 0 ? '(inactiva)':'', $ot->nombre);
					}
					$buf .= '</select></td>';
				}
				else $buf .= '<td><i>No asignada</i></td>';
			}
			else $buf .= '<td>'.squeryerror(12106).'</td>';
		} 
	}
	else $buf .= squeryerror(12105);
}

function loguito($bmenu=true, $usrnm='(DESCONOCIDO)', $usrlv=6, $prjnm=null, $pryro=false)
{
	$tirix = '';
	$sst1 = "font-family: Arial; text-align:center;font-size:11px;font-weight:bold;border-bottom:1px solid black;";
	$sst2 = "font-family: Arial; text-align:center;font-size:11px;";
    $tirix .= sprintf("<p style=\"%s\" align=\"center\">%s - %s", $sst1, SOFTNAME, COPYRIGHT);
    if ($bmenu)
    {
	    $tirix = $tirix . "<table align=\"center\" style=\"$sst2\"><tr>";
	    $tirix .= sprintf("<td>Usuario: <i>%s</i></td>", $usrnm);
	    $tirix .= sprintf("<td>Nivel de acceso: <i>%s</i></td>", userlevel($usrlv));

	    proyecto_chooser($tirix, $pryro);

	    $tirix .= "<td><a href=\"logout.php\">Terminar sesi&oacute;n</a></td>";
		$tirix .= "<td><a href=\"menu.php\">Men&uacute; principal</a></td>";
		$tirix .= "</tr></table>";
    }
    $tirix .= "</p>";

    return $tirix;
}


/**
 * 
 * Crea registro(s) historico de la tabla
 * @param lt_form $fo
 * Formulario
 * @param string $tabla
 * Nombre de la tabla
 * @param string $campo
 * Nombre del campo clave
 * @param integer $valor
 * Valor del campo clave
 * @param string $op
 * Operacion (D->borrado,E->edicion,A->anulacion)
 * @param string $condicion
 * Condicion en formato "WHERE <condicion>". Si se especifica, no se toman en cuenta ni campo ni valor.
 * @param string $db
 * Especifica una base de datos alterna de origen de la tabla. Por defecto, vacio
 * @return bool
 * Indica si la operacion fue exitosa
 */
function ltable_histo($fo, $tabla, $campo='', $valor=0, $op='E', $condicion='', $db=false)
{
	$isok = false;
	if ($db === false && defined('DEFAULT_SCHEMA')) $db = DEFAULT_SCHEMA;
	$ssdb = $db !== false ? sprintf(" AND TABLE_SCHEMA='%s'", $db): '';
	$qa = new myquery($fo, sprintf("SELECT GROUP_CONCAT(column_name SEPARATOR ',') AS flds ".
		"FROM information_schema.columns WHERE TABLE_NAME='%s'%s", $tabla, $ssdb),'LTH-1::'.$tabla);
	if ($qa->isok)
	{
		if (gettype($condicion) == 'string')
		{
			if ($condicion == '')
			{
				if (gettype($valor) == 'string') $condicion = sprintf("WHERE %s='%s'", $campo, $valor);
				if (gettype($valor) == 'integer') $condicion = sprintf("WHERE %s=%d", $campo, $valor);
				if (gettype($valor) == 'double') $condicion = sprintf("WHERE %s=%f", $campo, $valor);
			}
			elseif (strtoupper(substr($condicion, 0, 5)) !== 'WHERE') $condicion = 'WHERE '.$condicion;
		}
		$sdb = $db !== false ? $db.'.': '';
		$qb = new myquery($fo, sprintf("INSERT INTO %sh%s (SELECT %s,'%s',NOW(),%d,'%s' FROM %s%s %s)",
				$sdb, $tabla, $qa->r->flds, $op, $_SESSION['uid'], $_SERVER['REMOTE_ADDR'],
				$sdb, $tabla, $condicion),
				'LTH-2::'.$tabla,false,true);
		//$fo->parc($qb->q);
		$isok = $qb->isok;
	}
	return $isok;
}

/**
 * 
 * Envia un (01) registro a tabla historica
 * @param lt_form $fo
 * contexto
 * @param string $tabla
 * Nombre de tabla
 * @param string $valor
 * Valor(es) clave
 * @param string $op
 * (opcional) Operacion, por defecto 'E'
 * @param variant $condicion
 * (opcional) Condicion personalizada. String o lt_condicion
 * @param string $bd
 * (opcional) Especifica una base de datos diferente a la por defecto
 * @return boolean
 * Indica si fue exitosa la operacion
 */
function ltable_histor(lt_form $fo, $tabla, $valor=false, $op='E', $condicion=false, $bd=false)
{
	$isok = false;
	if (($ro = lt_registro::crear($fo, $tabla, $valor, false, $condicion, $bd)))
	{
		$set = '';
		foreach ($ro->campos as $fl)
		{ 
			if ('i' == $fl->t) $set .= sprintf("%d,", $fl->v);
			if ('n' == $fl->t) $set .= sprintf("%f,", $fl->v);
			if ($fl->t == 'c') $set .= sprintf("'%s',", mysql_real_escape_string($fl->v));
			if (strpos('d,t,h', $fl->t) !== false) $set .= sprintf("FROM_UNIXTIME(%d),", $fl->v->timestamp);				
		}
		$set .= sprintf("'%s',NOW(),%d,'%s'", $op, $fo->uid, $fo->ipaddr);
		if (myquery::i($fo, 'h'.$tabla, $set, $bd)) $isok = true;
	}	
	return $isok;
}

/**
 * 
 * Reemplaza el macro con el valor de la variable de session
 */
function lt_macros($rawq)
{
	$inpq = $rawq;
	$pid = $uid = $tid = 0;
	if (isset($_SESSION['pid'])) $pid = $_SESSION['pid']+0; else $pid = 0;
	if (isset($_SESSION['uid'])) $uid = $_SESSION['uid']+0; else $uid = 47;
	if (isset($_SESSION['tid'])) $tid = $_SESSION['uid']+0; else $tid = 99;
	$inpq = str_replace("{proyecto_id}", $pid, $inpq);
	$inpq = str_replace("{pid}", $pid, $inpq);
	$inpq = str_replace("{uid}", $uid, $inpq);
	$inpq = str_replace("{tid}", $tid, $inpq);
	$inpq = str_replace("{tienda_id}", $tid, $inpq);
	// TODO: metodo generico con variables de sesion
	return $inpq;
}

/**
 * 
 * Verifica si el usuario actual tiene acceso a una caracteristica especial
 * @param lt_form $fo
 * @param integer $modulo_id
 * ID del modulo, se obtiene de la tabla 'modulos'
 * @param integer $feature_id
 * ID de la caracteristica, se obtiene de la tabla 'features'
 * @param integer $uid
 * ID del usuario, por defecto toma su valor de la variable de sesion
 * @param integer $pid
 * ID del proyecto, por defecto toma su valor de la variable de sesion
 * @return int
 * Indica el nivel de autorizacion, 0 si no esta autorizado
 */
function aclcheck(lt_form $fo, $modulo_id, $feature_id, $uid=0, $pid=0)
{
    $nivel = 0; // no autorizado

    if ($uid == 0) $uid = $fo->uid;
    if ($pid == 0) $pid = $fo->pid;
	
	$q = sprintf("SELECT level FROM acls " .
		"WHERE modulo_id=%d AND feature_id=%d AND uid=%d AND proyecto_id=%d",
		$modulo_id, $feature_id, $uid, $pid);
	if (($res = mysql_query($q)) !== false)
	{
		if (($row = mysql_fetch_assoc($res)) !== false)
		{
			$nivel = $row['level'];
		}
		mysql_free_result($res);
	}
	else $fo->qerr("ACL-1");
	
	return $nivel;
}

/**
 * Generar un string a partir de una plantilla
 * @param string $plantilla
 * Cadena con el formato de la plantilla
 * @param array $datos
 * Array asociativo con los datos para la generacion
 * @return string
 * Retorna un string generado a partir de la mezcla de plantilla y datos
 */
function plantilla_parse($plantilla, &$datos)
{
	$fmt = "";
	$str_out = "";
	
	$ndx = 0;
	$lns = strlen($plantilla);
	while ($ndx < $lns)
	{
		$c = $plantilla[$ndx];
		if ($c == '{')
		{
			$vaal = '';
			$vaar = '';
			$ndx++;
			while (($ndx < $lns) && ($plantilla[$ndx] != '}'))
			{
				$c = $plantilla[$ndx];
				if ($c == ':')
				{
					$ndx++;
					while (($ndx < $lns) && ($plantilla[$ndx] != '}'))
					{
						$vaal .= $plantilla[$ndx];
						$ndx++;
					}
				}
				else
				{
					$vaar .= $c;
					$ndx++;
				}
			}
			if ($vaal > 0) $fmt = sprintf("%%0%dd", $vaal); else $fmt = "%s";
			$str_out .= sprintf($fmt, isset($datos[$vaar]) ? $datos[$vaar]:'');
		}
		else $str_out .= $c;
		$ndx++;
	}
		
	return $str_out;
}

/**
 * 
 * Devuelve la fecha inicial de proyecto
 * @param lt_form $fo
 * Contexto
 * @param integer $proyecto_id
 * (opcional) ID del proyecto. Por defecto, valor de variable de sesion
 * @param integer $fecha_fmt
 * (opcional) Formato de la variable a retornar. 0=>timestamp (por defecto), 1=>array, 2=>lt_fecha
 * @return variant
 * Fecha de inicio del proyecto
 */
function fecha_inicial_pry(lt_form $fo, $proyecto_id=0, $fecha_fmt=0)
{
	if ($proyecto_id == 0) $proyecto_id = $fo->pid;
	$elval = FALSE;
	if (($r = lt_registro::crear($fo, 'proyectos', $proyecto_id)))
	{
		switch ($fecha_fmt)
		{
			case 0: $elval = $r->v->inicio->timestamp; break;
			case 1: $elval = $r->v->inicio->to_array(); break;
			default: $elval = $r->v->inicio; break;
		}
	}
	return $elval;
}

/** 
 * 
 * Formatea el string de numero telefonico $telno para agregarle el codigo de area local
 * @param string $telno
 * Numero telefonico
 * @return string
 * Numero telefonico con el codigo de area
 */
function telf($telno)
{
	$telno = trim($telno);
	if (strlen($telno) == 7) $telno = sprintf("(0241) %s", $telno);
	elseif (strlen($telno) == 11) $telno = sprintf("(%s) %s", substr($telno,0,4), substr($telno,-7));
	return $telno;
}

function rqget($rqnm, $defval)
{
	$rqvar = $defval;
	if (isset($_REQUEST[$rqnm])) $rqvar = $_REQUEST[$rqnm];
	return $rqvar;
}


/**
 * 
 * Retorna el valor de una variable de configuracion global
 * Toma su valor de la tabla 'mprs_settings'
 * @param lt_form $fo
 * @param string $campo
 * Cadena con el nombre de la variable a retornar
 * @param integer $proyecto_id
 * Por defecto toma su valor de la variable de sesion
 * @return string
 * Valor de la variable
 */
function mprs_setting_get($fo, $campo, $proyecto_id=0)
{
	$valor = '';
	if ($proyecto_id == 0) $proyecto_id = $_SESSION['pid'];
	$qa = new myquery($fo, sprintf("SELECT valor FROM mprs_settings ".
			"WHERE proyecto_id=%d AND campo='%s'", $proyecto_id, $fo->dbe($campo)),
			'INMSETGET-1');
	if ($qa->isok) $valor = $qa->r->valor;
	return $valor;
}

/**
 * @author lvillalobos
 * Funcion para generar codigos especiales de autorizacion
 * @var $digits es la cantidad de digitos del codigo
 * @return el codigo generado
 */
function generar_codigo($digits){
	$num_str = sprintf("%0d", mt_rand(1, pow(10,$digits)-1));
	return $num_str;
}


/**
 * 
 * Obtener direccion IP real
 * @author Yang Yang
 * @return string
 * Direccion IP segun encabezados HTTP
 */
function get_ip_address() {
	foreach (array('HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
		if (array_key_exists($key, $_SERVER) === true) {
			foreach (explode(',', $_SERVER[$key]) as $ip) {
				if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
					return $ip;
				}
			}
		}
	}
}

/**
 * 
 * Escoge proyecto y tienda en uso
 * @param lt_form $fo
 * @param int $proyecto_id
 * @param int $uid
 * @return bool
 * Indica si la operacion fue exitosa
 */
function proyecto_choose($fo, $proyecto_id, $uid)
{
	$isok = false;
	$qb = new myquery($fo, sprintf("SELECT nombre, have_tiendas FROM proyectos WHERE proyecto_id=%d",
		$proyecto_id), 'PRYCHOOSE-1');
	if ($qb->isok)
	{
		$prynm = $qb->r->nombre;
		$tiempo = time() + VIDA_GALLETA;
		///setcookie('pid', $proyecto_id, 0, '/');
		///setcookie('pnm', $prynm, 0, '/');
		$_SESSION['pid'] = $proyecto_id;
		$_SESSION['proyecto_id'] = $proyecto_id;
		$_SESSION['pnm'] = $prynm;
		$reto['pid'] = $proyecto_id;
		
		$tienda_id = 99;
		if ($qb->r->have_tiendas)
		{
			$qtu = new myquery($fo, sprintf("SELECT tienda_id FROM tndusr ".
					"WHERE uid=%d", $uid),'LOGIN-20');
			if ($qtu->isok)
			{
				$tienda_id = $qtu->r->tienda_id;
			}
			else
			{
				$qtv = new myquery($fo, sprintf("SELECT tienda_id FROM vendedores ".
						"WHERE uid=%d ORDER BY tienda_id LIMIT 1", $uid),'LOGIN-21');
				if ($qtv->isok)
				{
					$tienda_id = $qtv->r->tienda_id;
					$qtus = new myquery($fo, sprintf("INSERT INTO tndusr VALUES (%d,%d)",
							$tienda_id, $uid),'LOGIN-22',true,true);
				}
			}
		}
		$_SESSION['tid'] = $tienda_id;
		
		$isok = true;
	}
	return $isok;
}

function check_netmask($mask, $ip)
{
	@list($net, $bits) = explode('/', $mask);
	$bits = isset($bits) ? $bits : 32;
	$bitmask = -pow(2, 32-$bits) & 0x00000000FFFFFFFF;
	$netmask = ip2long($net) & $bitmask;
	$ip_bits = ip2long($ip)  & $bitmask;
	return (($netmask ^ $ip_bits) == 0);
}

class profiler
{
	private $t0 = 0, $t1 = 0, $lapso =0, $part = '';

	function start($part)
	{
		$this->t0 = time();
		$this->part = $part;
		error_log('**** '.$this->part.': start');
	}
	function stop()
	{
		$this->t1 = time();
		$this->lapso = $this->t1 - $this->t0;
		error_log(sprintf("**** %s: stop -> %dseg", $this->part, $this->lapso));
	}
}

function url_exists( $url = NULL )
{
	$isok = FALSE;
	
	if(!empty( $url ) )
	{
		try {
			$ch = curl_init( $url );
			
			//Establecer un tiempo de espera
			curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			
			//establecer NOBODY en true para hacer una solicitud tipo HEAD
			curl_setopt( $ch, CURLOPT_NOBODY, true );
			//Permitir seguir redireccionamientos
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
			//recibir la respuesta como string, no output
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			
			$data = curl_exec( $ch );
			
			//Obtener el código de respuesta
			$httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			//cerrar conexión
			curl_close( $ch );	
		}
		catch (Exception $ex)
		{
			error_log($ex->getMessage());
		}
		
		// Aceptar solo respuesta 200 (Ok), 301 (redirección permanente) o 302 (redirección temporal)
		$accepted_response = array( 200, 301, 302 );
		if (in_array( $httpcode, $accepted_response ) ) $isok = TRUE;
	}
	return $isok;
}
?>