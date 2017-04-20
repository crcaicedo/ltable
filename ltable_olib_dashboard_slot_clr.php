<?php
require_once "ltable_olib.php";

if (($fo = lt_form::respuesta(1, 'ds,i;sl,i')))
{
	$p = &$fo->p;
	$c = new lt_condicion('dashboard_id', '=', $p->ds);
	$c->add('slot_id', '=', $p->sl);
	$c->add('uid', '=', $fo->uid);
	$fo->isok = myquery::d($fo, 'dashboard_det', $c);
}
?>