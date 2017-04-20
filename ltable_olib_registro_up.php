<?php
require_once "ltable_olib.php";
require_once 'ltable_olib_registro_up_site.php';

if (($fo = lt_form::registro_update($_REQUEST['__tabla'])))
{
	$p = &$fo->p;
	if (($r = lt_registro::by_parametros($fo, $p)))
	{
		myquery::start();
		if (ltable_registro_validar($fo, $r))
		{
			if (ltable_registro_preprocess($fo, $r))
			{
				if ($r->guardar())
				{
					if (ltable_registro_postprocess($fo, $r))
					{
						ltable_registro_lastprocess($fo, $r);
						$fo->ok('Registro actualizado');
						$fo->isok = TRUE;
					}
				}
				else $fo->warn('Error en postproceso registro', TRUE);
			}		
			else $fo->warn('Error guardando registro', TRUE);
		}
		else $fo->warn('Error validando registro', TRUE);
		myquery::end($fo->isok);
	}
	else $fo->warn('No pude cargar registro', TRUE);
}
?>