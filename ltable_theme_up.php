<?php
require_once "ltable_olib.php";

if (($fo = lt_form::respuesta(2000, 'nvotema')))
{
	$p = &$fo->p;
	
	$q = sprintf("REPLACE INTO ltable_themes SET uid=%d, tema='%s'", $fo->uid, $p->nvotema);
	if (myquery::q($fo, $q, 'LTABLE-THEMEUP', FALSE, TRUE))
	{
		$_SESSION["tema"] = $p->nvotema;
		$fo->ok("Tema cambiado");
		$fo->isok = TRUE;
	}
}
?>