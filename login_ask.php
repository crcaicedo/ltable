<?php
require_once "ltable_olib.php";
require_once "getbrowser.php";

$fo = new lt_form();

///$browser = new client_browser_info();
///$browser_ok = false;
///if ($browser->nombre = 'Opera' and $browser->mayor >= 9) $browser_ok = true;
///if ($browser->nombre = 'IE' and $browser->mayor >= 6) $browser_ok = true;
///if (strpos($browser->browser, 'Firefox/3') !== false) $browser_ok = true;
///if (strpos($browser->browser, 'Mozilla/5') !== false) $browser_ok = true;
///if (strpos($browser->browser, 'Blackberry') !== false)
$browser_ok = true;
if ($browser_ok)
{
	$fo->encabezado_base();
	$fo->buf .= loguito(false);
	$pwnm = sprintf("pw%07d", mt_rand(0, 9999999));
	$fo->jsi("function primero() { document.all.$pwnm.focus(); " .
		"document.all.$pwnm.select(); } document.onload=primero();");
		
	$tourl = 'menu.php';
	if (isset($_REQUEST['tourl'])) $tourl = $_REQUEST['tourl'];
	
	$txt0 = new lt_textbox();
	$txt0->n = "user";
	$txt0->t = 'c';
	$txt0->l = $txt0->vcols = 32;

	$txt1 = new lt_textbox();
	$txt1->n = $pwnm;
	$txt1->t = 'c';
	$txt1->l = $txt1->vcols = 16;
	$txt1->ctrl_type = LTO_PASSWORD;
	$txt1->funcion = "E";
	
	$fo->frm("login_check.php");
	$fo->hid("pwnm", $pwnm);
	$fo->hid('tourl', $tourl);
	$fo->tbl();

	$fo->tr();
	$fo->th("Usuario",2);
	$fo->td();
	$txt0->render($fo->buf);

	$fo->tr();
	$fo->th("Contrase&ntilde;a",2);
	$fo->td();
	$txt1->render($fo->buf);
	
	$fo->tr();
	$fo->td(3, 2);
	$fo->sub("Entrar al sistema");

	$fo->tblx();
	$fo->frmx();
}
else
{
	$fo->parc("Por favor actualice su browser a la versi&oacute;n m&aacute;s reciente.");
	$fo->parc("Su browser es: ". $browser->browser);
}
$fo->footer();
$fo->show();
?>
