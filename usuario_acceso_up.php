<?php
require_once "ltable_olib.php";

if (($fo = lt_form::respuesta(142, 'uidx,i;pidx,i;modulo,i;nivel,i')))
{
	$p = &$fo->p;
	$query = sprintf("INSERT INTO acceso VALUES (%d, %d, %d, %d)",
		$p->uidx, $p->modulo, $p->pidx, $p->nivel);
    if (myquery::q($fo, $query, 'USRACCESOUP', TRUE, TRUE))
    {
    	$fo->isok = true;
    }
}
?>