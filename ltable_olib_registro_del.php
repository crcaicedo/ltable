<?php
require_once "ltable_olib.php";

require_once "ltable_olib.php";
require_once 'ltable_olib_registro_del_site.php';

if (($fo = lt_form::registro_delete($_REQUEST['__tabla'])))
{
	$p = &$fo->p;
	if (($r = lt_registro::by_parametros($fo, $p)))
	{
		myquery::start();
		if (ltable_registro_del_validar($fo, $r))
		{
			if (ltable_registro_del_preprocess($fo, $r))
			{
				if ($r->remover())
				{
					if (ltable_registro_del_postprocess($fo, $r))
					{
						ltable_registro_del_lastprocess($fo, $r);
						$fo->ok('Registro borrado');
						$fo->isok = TRUE;
					}
					else $fo->warn('Error borrando registro (postproceso)');
				}
				else $fo->warn('Error borrando registro');
			}		
			else $fo->warn('Error borrando registro (preproceso)');
		}
		else $fo->warn('Error validando registro');
		myquery::end($fo->isok);
	}
	else $fo->warn('No pude cargar registro');
}
?>