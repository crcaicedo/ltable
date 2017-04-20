<?php
require_once "ltable_olib.php";

if (($fo = lt_form::respuesta(1)))
{
    $p = &$fo->p;
    if (($r = lt_registro::crear($fo, 'ltable_fl', array($p->tabla, $p->n))))
    {
        // TODO: validar
        $r->asignar($p);
        //error_log(print_r($p,TRUE));
        if ($r->guardar())
        {
            $fo->ok('Registro guardado');
            $fo->isok = TRUE;
        }
    }
    else $fo->warn('No encontrado: '.$p->tabla.','.$p->n);
    $fo->volver(LTMSG_HIDE, 'Continuar');
}
?>