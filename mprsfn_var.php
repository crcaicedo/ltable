<?php
/**
 * 
 * Retorna el ID en la tabla CIUDADES, de la ciudad a partir del nombre
 * @param lt_form $fo
 * (contexto)
 * @param string $nombre
 * Nombre de la ciudad
 * @param int $por_defecto
 * (opcional) ID por defecto en caso de no encontrar el nombre
 * @param int $estado_id
 * (opcional) Restringir busqueda a un estado especifico
 * @return int
 */
function ciudad_from_ds(lt_form $fo, $nombre, $por_defecto=1, $estado_id=0)
{
	$ciudad_id = $por_defecto;
	$c = new lt_condicion('UPPER(nombre)', '=', strtoupper($nombre));
	if ($estado_id != 0) $c->add('estado_id', '=', $estado_id);
	if (($r = lt_registro::crear($fo, 'ciudades', 0, FALSE, $c)))
	{
		$ciudad_id = $r->v->ciudad_id;
	}
	return $ciudad_id;
}

/**
 *
 * Retorna el ID en la tabla ESTADOS, del estado (provincia/departamento) a partir del nombre
 * @param lt_form $fo
 * (contexto)
 * @param string $nombre
 * Nombre del estado (provincia/departamento)
 * @param int $por_defecto
 * (opcional) ID por defecto en caso de no encontrar el nombre
 * @return int
 */
function estado_from_ds(lt_form $fo, $nombre, $por_defecto=7)
{
	$estado_id = $por_defecto;
	$c = new lt_condicion('UPPER(nombre)', '=', strtoupper($nombre));
	if (($r = lt_registro::crear($fo, 'estados', 0, FALSE, $c)))
	{
		$estado_id = $r->v->estado_id;
	}
	return $estado_id;
}

/**
 *
 * Retorna el ID en la tabla ZONAS, de la zona a partir del ID de ciudad
 * @param lt_form $fo
 * (contexto)
 * @param int $ciudad_id
 * ID de la ciudad
 * @param int $por_defecto
 * (opcional) ID por defecto en caso de no encontrar el ID de ciudad
 * @return int
 */
function zona_from_ciudad(lt_form $fo, $ciudad_id, $por_defecto=1)
{
	$zona_id = $por_defecto;
	$c = new lt_condicion('ciudad_id', '=', $ciudad_id);
	if (($r = lt_registro::crear($fo, 'zonas', 0, FALSE, $c)))
	{
		$zona_id = $r->v->zona_id;
	}
	return $zona_id;
}

/**
* Reemplaza todos los acentos por sus equivalentes sin ellos
*
* @param $string
*  string la cadena a sanear
*
* @return $string
*  string saneada
*/
function sanear_string($string)
{

	$string = trim($string);

	$string = str_replace(
			array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$string
			);

	$string = str_replace(
			array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$string
			);

	$string = str_replace(
			array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$string
			);

	$string = str_replace(
			array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$string
			);

	$string = str_replace(
			array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$string
			);

	$string = str_replace(
			array('ñ', 'Ñ', 'ç', 'Ç'),
			array('n', 'N', 'c', 'C',),
			$string
			);

	//Esta parte se encarga de eliminar cualquier caracter extraño
	$aa = array("\\", '¨', 'º', '-', '~', '#', '@', '|', '!', "\"",
				'·', '$', '%', '&', '/', '(', ')', '?', "\'", "¡",
				"¿", "[", "^", "<code>", "]", "+", "}", "{", "¨", "´",
				">", "< ", ";", ",", ":", ".");
			
	$string = str_replace($aa, '', $string);

	return $string;
}
?>