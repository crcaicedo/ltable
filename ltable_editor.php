<?php
require_once "ltable_olib.php";

$para = array();
$fo = new lt_form();
if (parms_isset($para, 2))
{
	if ($fo->dbopen())
	{
		if ($fo->usrchk(1, 1) !== USUARIO_UNAUTH)
		{
			$fo->encabezado();
			$fo->js("ltable_editor.js");
			$fo->hdr("Editor de tablas", 4);

			$ls0 = new lt_listbox();
			$ls0->tbl = "ltable";
			$ls0->fl_key = "tabla";
			$ls0->fl_desc = "title";
			$ls0->t = 'c';
			$ls0->n = 'tabla';
			
			$fo->frm("ltable_editor_hdr.php");
			$fo->tbl(3,-1,"2%","stdpg");
			$fo->tr();
			$fo->th("Tabla");
			$fo->td();
			$ls0->render($fo->buf);
			$fo->td();
			$fo->sub("Editar");
			$fo->trx();
			$fo->tblx();
			$fo->frmx();
		}
	}
}
else $fo->menuprinc();
$fo->footer();
$fo->show();
?>