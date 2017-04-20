<?php
require_once "ltable_olib.php";

if ($fo = lt_form::principal(1))
{
	$fo->hdr("Editor de tablas", 4);
    $ct = lt_ctrl_set::form($fo, 'ltable_editor_hdr.php');
    $ct->l('Tabla', 'tabla', 'c', 'ltable', array("tabla","Title"));
    $ct->u("Editar");
    $ct->box_vertical();
}
?>