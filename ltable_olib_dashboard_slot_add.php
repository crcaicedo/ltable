<?php
require_once "ltable_olib.php";

if (($fo = lt_form::respuesta(1, 'ds,i;sl,i;tp,i;cn,c;ti,c;pl,i')))
{
	$p = &$fo->p;
	$titulo = trim($p->ti);
	$contenido = trim($p->cn);
	if ($p->tp == 1)
	{
		$titulo = $contenido = "";
		if (($r = lt_registro::crear($fo, 'dashboard_plugins', $p->pl)))
		{
			$titulo = $r->v->descripcion;
			$contenido = $r->v->archivo;
		}
	}
	if ($titulo != "" && $contenido != "")
	{
		$fl = new lt_campos('dashboard_id', 'i', $p->ds);
		$fl->add('slot_id', 'i', $p->sl);
		$fl->add('uid', 'i', $fo->uid);
		$fl->add('tipo', 'i', $p->tp);
		$fl->add('titulo', 'c', $titulo);
		$fl->add('contenido', 'c', $contenido);
		$fl->add('alto', 'i', 0);
		$fl->add('ancho', 'i', 0);
		$fl->add('editable', 'i', 1);
		$fl->add('dashplugin_id', 'i', $p->pl);
		$fo->isok = myquery::i($fo, 'dashboard_det', $fl, LT_REPLACE);
	}
	else $fo->parc("Especifique titulo/URL", 3, "cursiva");
}
?>