<?php
require_once "ltable_olib.php";
require_once 'login_fn.php';

$fo = new lt_form();
if (isset($_SESSION['uid']))
{
	login_logout($fo);
}
$fo->hdr("Sesi&oacute;n terminada", 2);
$fo->hr();
$fo->par();
$fo->lnk("login_ask.php", "Abrir nueva sesi&oacute;n");
$fo->parx();
$fo->footer();
$fo->show();
?>