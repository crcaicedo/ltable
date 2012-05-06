<?php
require_once "ltable_olib.php";

$fo = new lt_form();
if (mprs_dbcn())
{
	if ($fo->usrchk(2000, 3) != USUARIO_UNAUTH)
	{
		$fo->encabezado();
		$tema = 'default';
		if (isset($_COOKIE['tema'])) $tema = $_COOKIE['tema'];

		$fo->hdr("Escoger tema visual");
		$fo->tbl();
		$fo->tr();
		$fo->td();
		
		$ls0 = new lt_listbox();
		$ls0->n = 'nvotema';
		$ls0->t = 'c';
		$ls0->rowsource_type = 1;
		$ls0->rowsource = array(array('default','Original'),array('rosa','Rosa'),
			array('azul','Azul'), array('verde','Verde'));
		$ls0->assign($tema);
		$ls0->render($fo->buf);
		
		$fo->td();
		$fo->butt('Cambiar tema', 'lt_theme_change(this)');
		
		$fo->trx();
		$fo->tblx();		
	}
	mysql_close();
}
$fo->footer();
$fo->show();
?>