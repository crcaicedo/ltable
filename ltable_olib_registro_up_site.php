<?php
require_once "ltable_olib.php";

function ltable_registro_validar(lt_form $fo, lt_registro $r)
{
	$isok = true;
	
	return $isok;
}

function ltable_registro_preprocess(lt_form $fo, lt_registro $r)
{
	$isok = true;
	
	return $isok;
}

function ltable_registro_postprocess(lt_form $fo, lt_registro $r)
{
	$isok = true;
	
	if ($r->tabla == 'prefactura_det')
	{
		$isok = false;
		require_once 'prefactura_fn.php';
		if (($fc = lt_registro::crear($fo, 'prefactura', $r->v->prefactura_id)))
		{
			$isok = prefactura_totales($fo, $fc);
		}
	}
	
	return $isok;
}

function ltable_registro_lastprocess(lt_form $fo, lt_registro $r)
{
	$isok = true;
	
	return $isok;
}
?>