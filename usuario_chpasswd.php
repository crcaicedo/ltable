<?php
require_once "ltable_olib.php";

$s = '';
if (($fo = lt_form::principal(2001)))
{
    	$fo->js("usuario_chpasswd.js");
	    $uid = $_SESSION['uid'];
	    
	    $fo->hdr("Cambiar contrase&ntilde;a de usuario");
	    $fo->divc("", "msg");
	    
	    $fo->tbl(3, -1, "2%", "stdpg");

	    $fo->tr();	    
	    $fo->th("Contrase&ntilde;a actual");
	    $fo->td();
	    $txt0 = new lt_textbox();
	    $txt0->n = "p0";
	    $txt0->l = $txt0->vcols = 16;
	    $txt0->ctrl_type = LTO_PASSWORD;
	    $txt0->valid_fn = "lt_chpwd0";
	    $txt0->render($fo->buf);
	    
	    $fo->tr();
	    $fo->th("Contrase&ntilde;a nueva");
	    $fo->td();
	    $txt1 = new lt_textbox();
	    $txt1->n = "p1";
	    $txt1->l = $txt1->vcols = 16;
	    $txt1->ctrl_type = LTO_PASSWORD;
	    $txt1->valid_fn = "lt_chpwd1";
	    $txt1->render($fo->buf);

	    $fo->tr();
	    $fo->th("Confirme contrase&ntilde;a nueva");
	    $fo->td();
	    $txt2 = new lt_textbox();
	    $txt2->n = "p2";
	    $txt2->l = $txt2->vcols = 16;
	    $txt2->ctrl_type = LTO_PASSWORD;
	    $txt2->valid_fn = "lt_chpwd2";
	    $txt2->render($fo->buf);
	    
	    $fo->tr();
	    $fo->td(3,2);
	    $fo->butt("Cambiar contrase&ntilde;a", "lt_chpwd_valid(this.form)");
	    
	    $fo->trx(); 
	    $fo->tblx();	    
}
?>