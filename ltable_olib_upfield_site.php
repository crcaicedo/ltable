<?php
require_once "ltable_olib.php";

/**
 * 
 * Validar campos en UPDATE de campo
 * @param lt_form $fo
 * @param ltable $lto
 * @param string $campo
 * Nombre del campo a validar
 * @param variant $valor
 * Valor a validar
 * @param string $campo_clave
 * Nombre del campo clave de la tabla
 * @param string $valor_clave
 * Valor del campo clave de la tabla
 * @return bool
 * Indica si la validacion fue exitosa
 */
function ltable_upfield_val(lt_form $fo, ltable $lto, $campo, $valor, $campo_clave, $valor_clave)
{
	$isok = TRUE;
	
	if ($lto->tabla == 'clientes')
	{
		if (substr($campo, 0, 7) == 'nombres' || substr($campo, 0, 8) == 'apellido')
		{
			$isok = FALSE;
			if (($r = lt_registro::crear($fo, $lto->tabla, $valor_clave)))
			{
				$perc = 0;
				$ovalor = $r->v->$campo;
				similar_text($ovalor, $valor, $perc);
				if ($perc >= 50) $isok = TRUE; 
				else $fo->warn(sprintf("Es diferente")); 
			}
		}
	}
	
	return $isok;
}

function ltable_upfield_preprocess(lt_form $fo, ltable $lto, $campo, $valor, $campo_clave, $valor_clave)
{
	$isok = true;
	
	return $isok;
}

function ltable_upfield_postprocess(lt_form $fo, ltable $lto, $campo, $valor, $campo_clave, $valor_clave)
{
	$isok = true;
	
	return $isok;
}
?>