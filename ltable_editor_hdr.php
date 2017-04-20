<?php
require_once "ltable_olib.php";
require_once "ltable_editor_fn.php";

if (($fo = lt_form::proceso(1, 'tabla'))) {
    $tabla = $fo->p->tabla;
    $fo->encabezado_base();
    $fo->js("ltable_editor.js");
    $fo->wait_icon();
    $fo->msg();
    $fo->hdr($tabla);

    if (($rt = lt_registro::crear($fo, 'ltable', $tabla))) {
        $tbl = $rt->v;

        // tabla,form_id,form_ro,form_rw,title,fl0,fl1,fl_order,
        // sql_custom,sql_expr,sql_ftitles,sql_format,
        // addbuttons,umethod,allowdel,edit_custom,new_custom,es_asunto,allowedit
        $ct = lt_ctrl_set::registro($fo, 'ltable', $tabla);
        $ct->t('Titulo', 'htitle', 'c', 60);
        $ct->entero('ID', 'hform_id', 0, 99999);
        $q0 = sprintf("SELECT n,title FROM ltable_fl WHERE tabla='%s'", $tabla);
        $ct->l('Campo clave', 'hfl0', 'c', 'ltable_fl', array('n','title'), $q0);
        $ct->l('Campo descripcion', 'hfl1', 'c', 'ltable_fl', array('n','title'), $q0);
        $ct->l('Campo ordenacion', 'hfl_order', 'c', 'ltable_fl', array('n','title'), $q0);
        $ct->u('Guardar');
        $ct->box_horizontal(TRUE);

        lteditor_fieldsFrom($fo, $tabla);
        $fo->parc("&laquo;Campos&raquo;", 3, "negrita");
        $fo->tbl(3, 0, "2%", '', 'font-size:8pt;');
        $fo->autotdx = $fo->autotrx = $fo->autotblx = false;

        $fo->td(0, 0, '', 'vertical-align:top;');
        $c = new lt_condicion('tabla', '=', $tabla);
        $fl = new lt_campos('columna', 'c', '', 'n');
        if (($t = myquery::t($fo, 'ltable_fl', $c, $fl, FALSE, 'orden'))) {
            $acc = new lt_acciones();
            $acc->boton('lteditor_fledit', array(array($tabla, LT_ACCION_PLT), array('columna')), 'Editar');
            $t->setAcciones($acc);
            $fo->div('lteditorfldiv', 0, '', 'height:500px;width:150px;overflow:auto;');
            $t->box();
            $fo->divx();
        }

        $fo->td(0, 0, '', 'vertical-align:top;');
        $fo->div('fleditoreditdiv');
        lteditor_fieldEdit($fo, $tabla, $tbl->fl0);
        $fo->divx();
        $fo->tdx();
        $fo->trx();
        $fo->tblx();
        $fo->frmx();
    }
}
?>