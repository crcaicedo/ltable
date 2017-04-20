<?php
require_once "consumo_fn.php";

function ltable_main_preprocess(lt_form $fo, ltable $lto)
{
	$isok = true;

	if ($lto->tabla == 'extensiones') 
	{
		if (aclcheck($fo, 80, 3))
		{
			$ct = lt_ctrl_set::form($fo, 'extensiones_imp.php', '', FALSE);
			$ct->u('Importar extensiones');
			$ct->box_vertical();
		}
	}
	return $isok;
}

function ltable_main_postprocess(lt_form $fo, ltable $lto)
{
	$isok = true;

	return $isok;
}
?>
