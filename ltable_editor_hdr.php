<?php
require_once "ltable_olib.php";
require_once "ltable_editor_fn.php";

function lteditor_fl(lt_form $fo)
{
	$oa[0] = new lt_textbox();
	$oa[0]->n = 'n';
	$oa[0]->t = 'c';
	$oa[0]->l = 31;
	$oa[0]->vcols = 20;
	$oa[0]->valid_fn = 'lt_novacio';
	$oa[0]->assign('');
	$oa[0]->label = 'Nombre';
	
	$oa[1] = new lt_listbox();
	$oa[1]->n = 't';
	$oa[1]->t = 'c';
	$oa[1]->rowsource_type = 1;
	$oa[1]->rowsource = array(array('c','Caracter'),array('n','Num&eacute;rico'),
			array('i','Entero'),array('d','Fecha'),array('t','Fecha/Hora'),array('h','Hora'),
			array('b','Byte'), array('m','Memo'));
	$oa[1]->assign('c');
	$oa[1]->label = 'Tipo';
	
	$oa[2] = new lt_textbox();
	$oa[2]->n = 'l';
	$oa[2]->t = 'i';
	$oa[2]->l = 3;
	$oa[2]->vcols = 3;
	$oa[2]->valid_fn = 'lt_entero';
	$oa[2]->valid_parms = '3,1,255';
	$oa[2]->assign(10);
	$oa[2]->label = 'Largo';
	
	$oa[3] = new lt_textbox();
	$oa[3]->n = 'pd';
	$oa[3]->t = 'i';
	$oa[3]->l = 2;
	$oa[3]->vcols = 2;
	$oa[3]->valid_fn = 'lt_entero';
	$oa[3]->valid_parms = '2,0,16';
	$oa[3]->assign(0);
	$oa[3]->label = 'Decimales';
	
	$oa[4] = new lt_textbox();
	$oa[4]->n = 'title';
	$oa[4]->t = 'c';
	$oa[4]->l = 60;
	$oa[4]->vcols = 30;
	$oa[4]->assign('');
	$oa[4]->label = 'T&iacute;tulo';
	
	$oa[5] = new lt_textbox();
	$oa[5]->n = 'df';
	$oa[5]->t = 'c';
	$oa[5]->l = 16;
	$oa[5]->assign('');
	$oa[5]->label = 'Valor por defecto';
	
	$oa[6] = new lt_listbox();
	$oa[6]->n = 'ctrl_type';
	$oa[6]->t = 'i';
	$oa[6]->rowsource_type = 1;
	$oa[6]->rowsource = array(array(0,'Texto'),array(1,'Check'),
			array(2,'Selecci&oacute;n'),array(3,'Edici&oacute;n'),
			array(4,'Separador'),array(5,'Contrase&ntilde;a'));
	$oa[6]->assign(0);
	$oa[6]->label = 'Control';
	
	$oa[7] = new lt_textbox();
	$oa[7]->n = 'ordenv';
	$oa[7]->t = 'i';
	$oa[7]->l = 5;
	$oa[7]->vcols = 5;
	$oa[7]->valid_fn = 'lt_entero';
	$oa[7]->valid_parms = '5,0,99999';
	$oa[7]->label = 'Orden visual';
	$oa[7]->assign(0);
	
	$oa[8] = new lt_textbox();
	$oa[8]->n = 'vcols';
	$oa[8]->t = 'i';
	$oa[8]->l = 3;
	$oa[8]->vcols = 3;
	$oa[8]->valid_fn = 'lt_entero';
	$oa[8]->valid_parms = '3,1,100';
	$oa[8]->label = 'Columnas';
	$oa[8]->assign(10);
	
	$oa[9] = new lt_textbox();
	$oa[9]->n = 'vrows';
	$oa[9]->t = 'i';
	$oa[9]->l = 2;
	$oa[9]->vcols = 2;
	$oa[9]->valid_fn = 'lt_entero';
	$oa[9]->valid_parms = '2,1,10';
	$oa[9]->label = 'Filas';
	$oa[9]->assign(1);
	
	$oa[10] = new lt_checkbox();
	$oa[10]->n = 'enabled';
	$oa[10]->t = 'i';
	$oa[10]->l = 1;
	$oa[10]->assign(1);
	$oa[10]->label = 'Habilitado';
	
	$oa[11] = new lt_checkbox();
	$oa[11]->n = 'ro';
	$oa[11]->t = 'i';
	$oa[11]->l = 1;
	$oa[11]->assign(0);
	$oa[11]->label = 'Solo lectura';
	
	$oa[12] = new lt_checkbox();
	$oa[12]->n = 'hidden';
	$oa[12]->t = 'i';
	$oa[12]->l = 1;
	$oa[12]->assign(0);
	$oa[12]->label = 'Oculto';
	
	$oa[13] = new lt_checkbox();
	$oa[13]->n = 'esdato';
	$oa[13]->t = 'i';
	$oa[13]->l = 1;
	$oa[13]->assign(1);
	$oa[13]->label = 'Es dato';
	
	$oa[14] = new lt_checkbox();
	$oa[14]->n = 'isup';
	$oa[14]->t = 'i';
	$oa[14]->l = 1;
	$oa[14]->assign(1);
	$oa[14]->label = 'Actualizable';
	
	$oa[15] = new lt_checkbox();
	$oa[15]->n = 'dup';
	$oa[15]->t = 'i';
	$oa[15]->l = 1;
	$oa[15]->assign(0);
	$oa[15]->label = 'Actualizar directo';
	
	$oa[16] = new lt_checkbox();
	$oa[16]->n = 'autovar';
	$oa[16]->t = 'i';
	$oa[16]->l = 1;
	$oa[16]->assign(0);
	$oa[16]->label = 'Valor auto';
	
	$oa[17] = new lt_checkbox();
	$oa[17]->n = 'postvar';
	$oa[17]->t = 'i';
	$oa[17]->l = 1;
	$oa[17]->assign(0);
	$oa[17]->label = 'Valor post';
	
	$oa[18] = new lt_listbox();
	$oa[18]->n = 'dt_auto';
	$oa[18]->t = 'i';
	$oa[18]->rowsource_type = 1;
	$oa[18]->rowsource = array(array(0,'No'),array(1,'Sugerir'),
		array(2,'Primera vez'),array(3,'Siempre'));
	$oa[18]->assign(1);
	$oa[18]->label = 'Fecha auto';
	
	$oa[19] = new lt_textbox();
	$oa[19]->n = 'mascara';
	$oa[19]->t = 'c';
	$oa[19]->l = 16;
	$oa[19]->assign('');
	$oa[19]->label = 'M&aacute;scara';
	
	$oa[20] = new lt_textbox();
	$oa[20]->n = 'funcion';
	$oa[20]->t = 'c';
	$oa[20]->l = 10;
	$oa[20]->assign('');
	$oa[20]->label = 'Funci&oacute;n';
	
	$oa[21] = new lt_textbox();
	$oa[21]->n = 'valid_fn';
	$oa[21]->t = 'c';
	$oa[21]->l = 30;
	$oa[21]->assign('');
	$oa[21]->label = 'Valid';
	
	$oa[22] = new lt_textbox();
	$oa[22]->n = 'valid_parms';
	$oa[22]->t = 'c';
	$oa[22]->l = 30;
	$oa[22]->assign('');
	$oa[22]->label = 'Valid Parms.';
	
	$oa[23] = new lt_textbox();
	$oa[23]->n = 'init_fn';
	$oa[23]->t = 'c';
	$oa[23]->l = 30;
	$oa[23]->assign('');
	$oa[23]->label = 'Init';
	
	$oa[24] = new lt_textbox();
	$oa[24]->n = 'init_parms';
	$oa[24]->t = 'c';
	$oa[24]->l = 30;
	$oa[24]->assign('');
	$oa[24]->label = 'Init Parms.';
	
	$oa[25] = new lt_textbox();
	$oa[25]->n = 'onkey_fn';
	$oa[25]->t = 'c';
	$oa[25]->l = 30;
	$oa[25]->assign('');
	$oa[25]->label = 'OnKey';
	
	$oa[26] = new lt_textbox();
	$oa[26]->n = 'onkey_parms';
	$oa[26]->t = 'c';
	$oa[26]->l = 30;
	$oa[26]->assign('');
	$oa[26]->label = 'OnKey Parms.';
	
	$oa[27] = new lt_textbox();
	$oa[27]->n = 'ls_tbl';
	$oa[27]->t = 'c';
	$oa[27]->l = 30;
	$oa[27]->assign('');
	$oa[27]->label = 'Lista:Tabla';
	
	$oa[28] = new lt_textbox();
	$oa[28]->n = 'ls_fl_key';
	$oa[28]->t = 'c';
	$oa[28]->l = 30;
	$oa[28]->assign('');
	$oa[28]->label = 'Lista:Clave';
	
	$oa[29] = new lt_textbox();
	$oa[29]->n = 'ls_fl_desc';
	$oa[29]->t = 'c';
	$oa[29]->l = 30;
	$oa[29]->assign('');
	$oa[29]->label = 'Lista:Descripci&oacute;n';
	
	$oa[30] = new lt_textbox();
	$oa[30]->n = 'ls_fl_order';
	$oa[30]->t = 'c';
	$oa[30]->l = 30;
	$oa[30]->assign('');
	$oa[30]->label = 'Lista:Orden';
	
	$oa[31] = new lt_textbox();
	$oa[31]->n = 'ls_custom';
	$oa[31]->t = 'c';
	$oa[31]->l = 250;
	$oa[31]->vcols = 30;
	$oa[31]->assign('');
	$oa[31]->label = 'Lista:Indagaci&oacute;n';
	
	$oa[32] = new lt_textbox();
	$oa[32]->n = 'ls_custom_new';
	$oa[32]->t = 'c';
	$oa[32]->l = 30;
	$oa[32]->assign('');
	$oa[32]->label = 'Lista:Indagaci&oacute;n (nuevo)';
	
	$fo->hid('orden', 0);
	$fo->tbl(3,1,"2%",'','font-size:8pt;border:1px solid black;border-collapse:collapse;');
	
	for ($ii = 0; $ii < 33; $ii++)
	{
		$fo->tr();
		$fo->th($oa[$ii]->label);
		$fo->td();
		$oa[$ii]->render($fo->buf);
		$fo->trx();
	}

	$fo->tblx();
}

$para = array();
$fo = new lt_form();
$para = array("tabla");
if (parms_isset($para, 2))
{
	$tabla = $_REQUEST['tabla'];
	if ($fo->dbopen())
	{
		if ($fo->usrchk(1, 1) !== USUARIO_UNAUTH)
		{
			$fo->encabezado();
			$fo->js("ltable_editor.js");
			$fo->wait_icon();
			
			$qa = new myquery($fo, sprintf("SELECT tabla, form_id, title, form_ro, form_rw, ".
				"fl0, fl1, fl_order FROM ltable WHERE tabla='%s'", $tabla), "LTEDITOR-10");
			if ($qa->isok)
			{
				$tbl = $qa->r;
				 
				$tx0= new lt_textbox();
				$tx0->n = 'hdr_tabla';
				$tx0->t = 'c';
				$tx0->l = 30;
				$tx0->assign($tbl->tabla);

				$tx1= new lt_textbox();
				$tx1->n = 'hdr_form_id';
				$tx1->t = 'i';
				$tx1->l = 4;
				$tx1->assign($tbl->form_id);

				$tx2 = new lt_textbox();
				$tx2->n = 'hdr_title';
				$tx2->t = 'c';
				$tx2->l = 64;
				$tx2->assign($tbl->title);

				$ls1 = new lt_listbox();
				$ls1->n = 'hdr_form_rw';
				$ls1->t = 'i';
				$ls1->rowsource_type = 1;
				$ls1->rowsource = array(array(1,'Supervisor'),array(2,'Promotor'));
				$ls1->assign($tbl->form_rw);
				
				$ls2 = new lt_listbox();
				$ls2->n = 'hdr_form_ro';
				$ls2->t = 'i';
				$ls2->rowsource_type = 1;
				$ls2->rowsource = array(array(1,'Supervisor'),array(2,'Promotor'));
				$ls2->assign($tbl->form_ro);
				
				$ls3 = new lt_listbox();
				$ls3->n = 'hdr_fl0';
				$ls3->t = 'c';
				$ls3->custom = sprintf("SELECT n,title FROM ltable_fl WHERE tabla='%s'", $tabla);
				$ls3->fl_key = "n";
				$ls3->fl_desc = "title";
				$ls3->assign($tbl->fl0);
				
				$ls4 = new lt_listbox();
				$ls4->n = 'hdr_fl1';
				$ls4->t = 'c';
				$ls4->custom = sprintf("SELECT n,title FROM ltable_fl WHERE tabla='%s'", $tabla);
				$ls4->fl_key = "n";
				$ls4->fl_desc = "title";
				$ls4->assign($tbl->fl1);
				
				$ls5 = new lt_listbox();
				$ls5->n = 'hdr_fl_order';
				$ls5->t = 'c';
				$ls5->custom = sprintf("SELECT n,title FROM ltable_fl WHERE tabla='%s'", $tabla);
				$ls5->fl_key = "n";
				$ls5->fl_desc = "title";
				$ls5->assign($tbl->fl_order);
				
				// tabla,form_id,form_ro,form_rw,title,fl0,fl1,fl_order,
				// sql_custom,sql_expr,sql_ftitles,sql_format,
				// addbuttons,umethod,allowdel,edit_custom,new_custom,es_asunto,allowedit
				$fo->parc("&laquo;Encabezado&raquo;", 3, "negrita");
				$fo->frm("ltable_editor_hdrsv.php");
				$fo->tbl(3,-1,"2%","stdpg");
				$fo->tr();
				$fo->tha(array("Tabla","ID","Nivel RO","Nivel RW","Titulo","Clave","Descripcion","Orden"));
				$fo->tr();
				$fo->td();
				$tx0->render($fo->buf);
				$fo->td();
				$tx1->render($fo->buf);
				$fo->td();
				$ls1->render($fo->buf);
				$fo->td();
				$ls2->render($fo->buf);
				$fo->td();
				$tx2->render($fo->buf);
				$fo->td();
				$ls3->render($fo->buf);
				$fo->td();
				$ls4->render($fo->buf);
				$fo->td();
				$ls5->render($fo->buf);
				$fo->tr();
				$fo->td(3,8);
				$fo->sub("Guardar encabezado");
				$fo->trx();
				$fo->tblx();
				$fo->frmx();

				$vs = '';
				$qc = new myquery($fo, sprintf("SELECT column_name, ordinal_position, data_type, ".
						"character_maximum_length, numeric_precision, numeric_scale, column_default, ".
						"column_comment FROM information_schema.columns WHERE table_name='%s' AND ".
						"column_name NOT IN (SELECT n FROM ltable_fl WHERE tabla='%s') ".
						"ORDER BY ordinal_position", $tabla, $tabla),
						"LTEDITOR-12");
				if ($qc->isok)
				{
					foreach ($qc->a AS $rx)
					{
						$data_type = 'c';
						if ($rx->data_type == 'numeric')
						{
						
						}
						$rg = new lteditor_fl($tabla, $rx->column_name, $rx->column_comment, $data_type, 
							$rx->column_comment, $ctrl_type, $rx->ordinal_position, $rx->ordinal_position*10);
						$vs .= ','.$rg->vs();
						unset($rg);
					}
					$qd = new myquery($fo, sprintf('INSERT INTO ltable_fl VALUES %s', substr($vs, 1)),
						"LTEDITOR-13"); 
				}
				
				$fo->parc("&laquo;Campos&raquo;", 3, "negrita");
				$fo->tbl(3,0,"2%",'','font-size:8pt;');
				$fo->autotdx = $fo->autotrx = $fo->autotblx = false;
				
				$fo->td(0, 0, '', 'vertical-align:top;');
				$fo->tbl(3,0,"2%",'','font-size:8pt;');
				$qb = new myquery($fo, sprintf("SELECT n FROM ltable_fl WHERE tabla='%s' ORDER BY ordenv", $tabla), 
					"LTEDITOR-11");
				foreach ($qb->a AS $rg)
				{
					$fo->tr();
					$fo->td();
					$fo->div('sel'.$rg->n);
					$fo->hid('flsel', '');
					$fo->lnk_js(sprintf("lteditor_fledit('%s','%s')", $tabla, $rg->n), $rg->n,
						'', 'text-decoration:none;');
					$fo->divx();
					$fo->tdx();
					$fo->trx();
				}
				$fo->tblx();
				
				$fo->td(0, 0, '', 'vertical-align:top;');
				lteditor_fl($fo);
				$fo->tdx();
				$fo->trx();
				$fo->tblx();
				$fo->frmx();
			}
		}
	}
}
$fo->footer();
$fo->show();
?>