<?php
require_once "ltable_olib.php";

if (($fo = lt_form::respuesta(1, 'id,i;ad,c;su,c'))) {
    $p = &$fo->p;

    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: "Sistemas OrionCorp" <sistemas@orioncorp.com.ve>' . "\r\n";
    $qeml = new myquery($fo, sprintf("SELECT buf FROM ltable_emltmp WHERE uid=%d AND rpt_id=%d",
        $fo->uid, $p->id), "LTEML-1");
    if ($qeml->isok) {
        $fo->isok = mail($p->ad, $p->su, $qeml->r->buf, $headers);
    } else $fo->warn("No pude cargar formulario/email desde tabla", TRUE);
}
?>