<?php
require_once "ltable_olib.php";

function lteditor_fieldEdit(lt_form $fo, $tabla, $columna)
{
    $tpa = array(array('c','Caracter'),array('n','Num&eacute;rico'),
        array('i','Entero'),array('d','Fecha'),array('t','Fecha/Hora'),array('h','Hora'),
        array('b','Byte'), array('m','Memo'));
    $cta = array(array(0,'TextBox'),array(1,'CheckBox'),
        array(2,'ListBox'),array(3,'EditBox'),
        array(4,'Separador'),array(5,'Contrase&ntilde;a'));
    $dta = array(array(0,'No'),array(1,'Sugerir'),array(2,'Primera vez'),array(3,'Siempre'));
    $fna = array(array(' ','Ninguna'),array('U','Cambiar a mayusculas'),array('E','Quitar espacios'),array('K','Deshabilitar Enter'));

    $ct = lt_ctrl_set::registro($fo, 'ltable_fl', array($tabla, $columna), 'ltable_editor_flsv.php');
    $ct->t('Nombre', 'n', 'c', 31, 'lt_novacio');
    $ct->la('Tipo', 't', 'c', $tpa);
    $ct->entero('Largo','l', 1, 99999);
    $ct->entero('Decimales', 'pd', 0, 16);
    $ct->t('Titulo', 'title', 'c', 60, 'lt_novacio');
    $ct->t('Valor por defecto', 'df', 'c', 16);
    $ct->la('Control', 'ctrl_type', 'i', $cta);
    $ct->numero('Orden visual', 'ordenv', 0, 999999);
    $ct->entero('Columnas (visuales)', 'vcols', 0, 1000);
    $ct->entero('Filas (visuales)', 'vrows', 0, 100);
    $ct->chk('Habilitado', 'enabled', 1);
    $ct->chk('Solo lectura', 'ro', 0);
    $ct->chk('Oculto', 'hidden', 0);
    $ct->chk('Es dato', 'esdato', 1);
    $ct->chk('Actualizable', 'isup', 1);
    $ct->chk('Actualizacion directa', 'dup', 1);
    $ct->chk('Valor automatico', 'autovar', 0);
    $ct->chk('Valor POST', 'postvar', 0);
    $ct->la('Fecha automatica', 'dt_auto', 'i', $dta, 1);
    // mascara
    $ct->la('Funcion', 'funcion', 'c', $fna, ' ');
    $ct->t('Funcion JS Valid', 'valid_fn', 'c', 60);
    $ct->e('Funcion JS Valid (params.)', 'valid_parms', 'c', 60, 2);
    $ct->t('Funcion JS Init', 'init_fn', 'c', 60);
    $ct->e('Funcion JS Init (params.)', 'init_parms', 'c', 60, 2);
    $ct->t('Funcion JS OnKey', 'onkey_fn', 'c', 60);
    $ct->e('Funcion JS Onkey (params.)', 'onkey_parms', 'c', 60, 2);
    //
    $ct->t('Lista:Tabla', 'ls_tbl', 'c', 100);
    $ct->c->vcols = 40;
    $ct->t('Lista:Campo Clave', 'ls_fl_key', 'c', 100);
    $ct->c->vcols = 40;
    $ct->t('Lista:Campo Descripcion', 'ls_fl_desc', 'c', 100);
    $ct->c->vcols = 40;
    $ct->t('Lista:Campo Orden', 'ls_fl_desc', 'c', 100);
    $ct->c->vcols = 40;
    $ct->e('Lista:Consulta SQL', 'ls_custom', 'c', 60, 3);
    $ct->e('Lista:Consulta SQL (nuevo)', 'ls_custom_new', 'c', 60, 3);

    $ct->h('orden', 'i', 0);

    $ct->u('Guardar');
    $ct->box_vertical();
}

function lteditor_fieldsFrom(lt_form $fo, $tabla)
{
    // crear nuevas columnas en ltable_fl
    $qc = new myquery($fo, sprintf("SELECT column_name, ordinal_position, data_type, " .
        "character_maximum_length, numeric_precision, numeric_scale, column_default, " .
        "column_comment ".
        "FROM information_schema.COLUMNS ".
        "WHERE TABLE_NAME='%s' AND TABLE_SCHEMA='%s' AND " .
        "COLUMN_NAME NOT IN (SELECT n FROM ltable_fl WHERE tabla='%s') " .
        "ORDER BY ORDINAL_POSITION", $tabla, $fo->dbname(), $tabla),
        "LTEDITOR-12");
    if ($qc->isok) {
        foreach ($qc->a AS $rx) {
            $tipo = 'c';
            $l = 10;
            $pd = 0;
            $vrows = 1;
            $vcols = 10;
            $n = $rx->column_name;
            $title = ($rx->column_comment != "") ? $rx->column_comment : $n;
            $control = 0;
            if (array_search($rx->data_type, array('decimal','tinyint','smallint','mediumint','int','bigint')) !== FALSE) {
                if ($rx->data_type == 'decimal') $tipo = 'n'; else $tipo = 'i';
                $l = $rx->numeric_precision;
                $pd = $rx->numeric_scale;
            } elseif ($rx->data_type == 'double' || $rx->data_type == 'float') {
                $tipo = 'n';
                $l = 10;
                $pd = 2;
            } elseif ($rx->data_type == 'date') {
                $tipo = 'd';
            } elseif ($rx->data_type == 'datetime') {
                $tipo = 't';
            } elseif ($rx->data_type == 'time') {
                $tipo = 'h';
            } elseif (array_search($rx->data_type, array('text','mediumtext'))  !== FALSE) {
                $control = 3;
                $tipo = 'm';
                $vcols = 40;
                $vrows = 3;
                $l = 4096;
            } elseif (array_search($rx->data_type, array('char','varchar')) !== FALSE) {
                $l = $rx->character_maximum_length;
            }

            //tabla,n,t,l,pd,title,dfc,dfn,dfd,dft,dfh,dfi,dfb,dfm,orden,ctrl_type,ls_tbl,ls_fl_key,ls_fl_desc,
            //vcols,vrows,ro,hidden,dt_auto,valid_fn,valid_parms,mascara,funcion,ordenv,esdato,enabled,init_fn,
            //init_parms,autovar,postvar,ls_fl_order,ls_custom,onkey_fn,onkey_parms,isup,ls_custom_new,dup
            if (($rfl = lt_registro::crear($fo, 'ltable_fl', array($tabla, $rx->column_name), TRUE))) {
                $rfl->av('tabla', $tabla);
                $rfl->av('n', $rx->column_name);
                $rfl->av('t', $tipo);
                $rfl->av('l', $l);
                $rfl->av('pd', $pd);
                $rfl->av('title', $title);
                $rfl->av('orden', $rx->ordinal_position);
                $rfl->av('ordenv', $rx->ordinal_position * 10);
                $rfl->av('esdato', 1);
                $rfl->av('dup', 1);
                $rfl->av('vcols', $vcols);
                $rfl->av('vrows', $vrows);
                if (strpos('uid,ipaddr', $n) !== FALSE) $rfl->av('autovar', 1);
                if ($n == 'creado') $rfl->av('dt_auto', 2);
                if ($n == 'modificado') $rfl->av('dt_auto', 3);
                if (!is_null($rx->column_default)) {
                    $df = $rx->column_default;
                    if ($tipo == 'd') $df = new lt_fecha($df, LT_FECHA_T);
                    elseif ($tipo == 'h') $df = new lt_fecha($df, LT_HORA_T);
                    elseif ($tipo == 't') $df = new lt_fecha($df, LT_FECHAHORA_T);
                    else $rfl->av('df' . $tipo, $df);
                }
                $rfl->av('ctrl_type', $control);
                $rfl->guardar();
            }
        }
    }
}
?>