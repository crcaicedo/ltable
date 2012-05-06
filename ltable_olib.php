<?php
require_once "mprsfn.php";
require_once "printers.php";

define('LTO_TEXTBOX', 0);
define('LTO_CHECKBOX', 1);
define('LTO_LISTBOX', 2);
define('LTO_EDITBOX', 3);
define('LTO_SEPARATOR', 4);
define('LTO_PASSWORD', 5);
define('LTO_BUTTON', 6);
define('LTO_BUTTON_GRP', 7);
define('LTO_DATALIST', 8);

define('LISTON_NONE', 0);
define('LISTON_BUTTON', 1);
define('LISTON_LINK', 2);
define('LISTON_TEXTBOX', 3);

define('LTMSG_HIDE', 'javascript:ltform_msg_hide(0);');
define('LTMSG_RELOAD', 'javascript:document.location.reload();');
define('LTMSG_CLOSE', 'javascript:window.close();');

require_once "ltable_olib_field.php";
require_once "ltable_olib_ctrl.php";
require_once "ltable_olib_form.php";
require_once "ltable_olib_var.php";

class lt_addbuttons
{
	private $a = array(), $sz = 0, $tabla='', $clave='', $fo = null;
	public function __construct(lt_form $fo, $tabla, $campoclave)
	{
		$this->fo = $fo;
		$this->tabla = $tabla;
		$this->clave = $campoclave;
		$this->load();
	}
	private function load()
	{
		$this->sz = 0;
		$this->a = array();
		$qa = new myquery($this->fo, sprintf("SELECT app, newwnd, caption, app_parms FROM ltable_addbuttons ".
			"WHERE tabla='%s' ORDER BY orden", $this->tabla), "LTADDBT-1", false);
		foreach ($qa->a as $bt) $this->a[$this->sz++] = clone $bt; 
		return $qa->isok;
	}
	public function render_titles($style="")
	{
		foreach ($this->a as $bt) $this->fo->th("-", 3, 0, "", $style);
	}
	public function render($valor, $td_class="")
	{
		if ($this->sz > 0)
		{
			foreach ($this->a as $bt)
			{
				$this->fo->td(3, 0, $td_class);
				if (empty($bt->app_parms))
				{
					$this->fo->frm($bt->app, $bt->newwnd == 1 ? true: false);
					$this->fo->hid("tabla", $this->tabla);
					$this->fo->hid("campo", $this->clave);
					$this->fo->hid("valor", $valor);
					$this->fo->sub($bt->caption);
					$this->fo->frmx();
				}
				else
				{
					$hoy = fecha();
					$app_parms = $bt->app_parms;
					$app_parms = str_replace("{valor}", $valor, $app_parms);
					$app_parms = str_replace("{hoy.mes}", $hoy['m'], $app_parms);
					$app_parms = str_replace("{hoy.agno}", $hoy['a'], $app_parms);

					$urlbt = sprintf("%s?%s", $bt->app, $app_parms);
					$this->fo->lnk($urlbt, $bt->caption, "", "", $bt->newwnd == 1 ? "_blank": "");
				}
				$this->fo->tdx();
			}
		}
	}
}

class ltable_dp
{
	public $tbl = '', $tbl_fields, $tbl_titles, $fl_n = '', $fl_t = '';
	
	public function __construct(array $row)
	{
		$this->tbl = $row['tbl'];
		$this->tbl_fields = $row['tbl_fields'];
		$this->tbl_titles = $row['tbl_titles'];
		$this->fl_n = $row['fl_n'];
		$this->fl_t = $row['fl_t'];
	}
}

class ltable
{
	public $tabla = '';
	public $form_id = 0, $form_ro = 2, $form_rw = 1, $title = '';
	public $form_name = '', $form_action = '', $retlnk = '';
	public $sql_custom = false, $sql_expr = '', $sql_ftitles = '', $sql_format = '';
	public $addbtt_count = 0, $addbtt = array();
	public $umethod = 0, $nuevo = true;
	public $allowdel = true, $rdonly = false, $stdret=true, $allowedit = true;

	public $jsfn = array();
	public $onload_custom = ''; 
	public $onunload_custom = '';
	public $edit_action = 'ltable_olib_edit.php'; 
	public $new_action = 'ltable_olib_new.php'; 
	
	public $fl0 = '', $fl1 = '', $fl_order = '', $insert_id = 0, $forzar_valor = false;
	public $cols = 0, $rows = 0, $fa = array(), $ordenv = array();
	public $dp = array(), $dp_count = 0;

	public $error_code = 0, $error_msg = '';
	
	public $es_asunto = false;
		
	public function render_error()
	{
		return sprintf("<p align=\"center\"><i>Error </i> <b>[%s]</b>: <code>%s</code></p>",
			$this->error_code, $this->error_msg);	
	}
	
	private function load_fl($row)
	{
		$valido = false;
		$nc = $row['n'];
		switch ($row['ctrl_type'])
		{
			case LTO_TEXTBOX: $this->fa[$nc] = new lt_textbox(); $valido = true; break;
			case LTO_PASSWORD: $this->fa[$nc] = new lt_textbox(); 
				$this->fa[$nc]->ctrl_type = LTO_PASSWORD; $valido = true; break;
			case LTO_LISTBOX: $this->fa[$nc] = new lt_listbox(); $valido = true; break;
			case LTO_EDITBOX: $this->fa[$nc] = new lt_editbox(); $valido = true; break;
			case LTO_CHECKBOX: $this->fa[$nc] = new lt_checkbox(); $valido = true; break;
			case LTO_SEPARATOR: $this->fa[$nc] = new lt_separator(); $valido = true; break;
			case LTO_BUTTON: $this->fa[$nc] = new lt_button(); $valido = true; break;
			case LTO_BUTTON_GRP: $this->fa[$nc] = new lt_button_grp(); $valido = true; break;
		}
		if ($valido)
		{
			$dfnm = 'df' . $row['t'];
			$this->fa[$nc]->form = $this->form_name;
			$this->fa[$nc]->n = $row['n'];
			$this->fa[$nc]->fln = $row['n'];
			$this->fa[$nc]->t = $row['t'];
			$this->fa[$nc]->l = $row['l'];
			$this->fa[$nc]->pd = $row['pd'];
			$this->fa[$nc]->df = $row[$dfnm];
			$this->fa[$nc]->esdato = $row['esdato'];
			$this->fa[$nc]->isup = $row['isup'];
			$this->fa[$nc]->autovar = $row['autovar'];
			$this->fa[$nc]->postvar = $row['postvar'];
			$this->fa[$nc]->v = $this->fa[$nc]->df;
			$this->fa[$nc]->text = $this->fa[$nc]->v;

			$this->fa[$nc]->title = $row['title'];
			$this->fa[$nc]->vcols = $row['vcols'];
			$this->fa[$nc]->vrows = $row['vrows'];
			$this->fa[$nc]->hidden = $row['hidden'];
			$this->fa[$nc]->ro = $row['ro'];
			$this->fa[$nc]->enabled = $row['enabled'];

			$this->fa[$nc]->valid_fn = $row['valid_fn'];
			$this->fa[$nc]->valid_parms = $row['valid_parms'];
			$this->fa[$nc]->init_fn = $row['init_fn'];
			$this->fa[$nc]->init_parms = $row['init_parms'];
			$this->fa[$nc]->onkey_fn = $row['onkey_fn'];
			$this->fa[$nc]->onkey_parms = $row['onkey_parms'];
			$this->fa[$nc]->funcion = $row['funcion'];	
			$this->fa[$nc]->dup = $row['dup'];	
			
			$this->fa[$nc]->ctrl_type = $row['ctrl_type'];

			if ($row['ctrl_type'] == LTO_TEXTBOX)
			{
				$this->fa[$nc]->dt_auto = $row['dt_auto'];
			}

			if ($row['ctrl_type'] == LTO_LISTBOX)
			{
				$this->fa[$nc]->tbl = $row['ls_tbl'];
				$this->fa[$nc]->custom = $row['ls_custom'];
				$this->fa[$nc]->custom_new = $row['ls_custom_new'];
				$this->fa[$nc]->fl_key = $row['ls_fl_key'];
				$this->fa[$nc]->fl_desc = $row['ls_fl_desc'];
				$this->fa[$nc]->fl_order = $row['ls_fl_order'];
			}

			if ($row['ctrl_type'] == LTO_BUTTON_GRP)
			{
				$this->fa[$nc]->load($this->tabla);
			}

			$this->cols++;
		}
		else
		{
			$this->error_code = 10020;
			$this->error_msg = "Tipo de control inv&aacute;lido.";
		}
	}
	
	public function load($tabla, $action='ltable_olib_up.php', $allow_partial=false, $byfield="")
	{
		$isok = array(false, false, false, false);
		
		$query = sprintf("SELECT * FROM ltable WHERE tabla='%s'", $tabla);
		if (($res = mysql_query($query)) !== false)
		{
			if (($row = mysql_fetch_assoc($res)) !== false)
			{
				$this->tabla = $tabla;
				$this->form_name = sprintf("frm%s%03d", $tabla, rand(1,999));
				$this->form_action = $action;
				$this->umethod = $row['umethod'];
				$this->allowdel = $row['allowdel'];
				$this->fl0 = $row['fl0'];
				$this->fl1 = $row['fl1'];

				$this->form_id = $row['form_id'];
				$this->form_ro = $row['form_ro'];
				$this->form_rw = $row['form_rw'];
				$this->title = $row['title'];

				$this->sql_custom = $row['sql_custom'];
				$this->sql_expr = $row['sql_expr'];
				$this->sql_ftitles = $row['sql_ftitles'];
				$this->sql_format = $row['sql_format'];
				
				if (!enblanco($row['new_custom'])) $this->new_action = $row['new_custom'];
				if (!enblanco($row['edit_custom'])) $this->edit_action = $row['edit_custom'];
								
				$this->addbt_count = $row['addbuttons'];
				
				$this->es_asunto = $row['es_asunto'] == 1;
				$this->allowedit = $row['allowedit'] == 1;
				
				$isok[0] = true;
			}
			else
			{
				$this->error_code = 10011;
				$this->error_msg = "Tabla <b>$tabla</b> no definida.";
			}
			mysql_free_result($res);
			
		}
		else
		{
			$this->error_code = 10010;
			$this->error_msg = mysql_error();
		}

		if ($isok[0])
		{
			if ($byfield == "")
			{
				$query = sprintf("SELECT n,t,l,pd,title,dfc,dfn,dfd,dft,dfh,dfi,dfb,dfm,ctrl_type,".
					"ls_tbl,ls_fl_key,ls_fl_desc,ls_fl_order,ls_custom,ls_custom_new,vcols,vrows,".
					"dt_auto,ro,hidden,valid_fn,valid_parms,mascara,funcion,esdato,enabled,".
					"init_fn,init_parms,autovar,postvar,onkey_fn,onkey_parms,isup,dup ".
					"FROM ltable_fl WHERE tabla='%s' ORDER BY orden", $tabla);
			}
			else
			{
				$query = sprintf("SELECT n,t,l,pd,title,dfc,dfn,dfd,dft,dfh,dfi,dfb,dfm,ctrl_type,".
					"ls_tbl,ls_fl_key,ls_fl_desc,ls_fl_order,ls_custom,ls_custom_new,vcols,vrows,".
					"dt_auto,ro,hidden,valid_fn,valid_parms,mascara,funcion,esdato,enabled,".
					"init_fn,init_parms,autovar,postvar,onkey_fn,onkey_parms,isup,dup ".
					"FROM ltable_fl WHERE tabla='%s' AND n='%s'", $tabla, $byfield);
			}
			if (($res = mysql_query($query))!==false)
			{
				if (mysql_num_rows($res) > 0)
				{
					while (($row = mysql_fetch_assoc($res)) !== false)
					{				
						$this->load_fl($row);
					}
					if ($this->cols > 0) $isok[1] = true;
				}
				else
				{
					if (!$allow_partial)
					{
						$this->error_code = 10013;
						$this->error_msg = "Campos de tabla <b>$tabla</b>no definidos.";
					}
					else $isok[1] = true;
				}
				mysql_free_result($res);
			}
			else
			{
				$this->error_code = 10012;
				$this->error_msg = mysql_error();
			}
			
			if ($byfield == "")
			{ 
				$q = sprintf("SELECT n FROM ltable_fl WHERE tabla='%s' ORDER BY ordenv", $tabla);
				if (($res = mysql_query($q)) !== false)
				{
					$cnt = 0;
					if (mysql_num_rows($res) > 0)
					{
						while (($row = mysql_fetch_row($res)) !== false)
						{
							$this->ordenv[$cnt++] = $row[0];
						}
						if ($cnt > 0) $isok[2] = true;
					}
					else
					{
						if (!$allow_partial)
						{
							$this->error_code = 10015;
							$this->error_msg = "Orden de tabla <b>$tabla</b> no definido.";
						}
						else $isok[2] = true;
					}
					mysql_free_result($res);
				}
				else
				{
					$this->error_code = 10014;
					$this->error_msg = mysql_error();
				}
			}
			else
			{
				$this->ordenv[0] = $this->fa[$byfield]->n;
				$isok[2] = true;
			}	
			
			$qq = sprintf("SELECT tbl,fl_n,fl_t,tbl_fields,tbl_titles FROM ltable_dp WHERE tabla='%s'", $tabla);
			if (($res = mysql_query($qq))!==false)
			{
				while (($row = mysql_fetch_assoc($res)) !== false)
				{
					$this->dp[$this->dp_count] = new ltable_dp($row);
					$this->dp_count++;
				}
				$isok[3] = true;
				mysql_free_result($res);
			}
			else
			{
				$this->error_code = 10016;
				$this->error_msg = mysql_error();
			}			
		}
		
		return $isok[0] && $isok[1] && $isok[2] && $isok[3];
	}
	
	private function render_update($ctrl)
	{
		$sval = "";
		if ($ctrl->dup == 1 && $this->nuevo == 0)
		{
			$valfn = "";
			if (!enblanco($ctrl->valid_fn))
			{
				if (enblanco($ctrl->valid_parms))
				{
					$valfn = sprintf("if (%s(\$('%s'))) ", $ctrl->valid_fn, $ctrl->n);
				}
				else
				{
					$valfn = sprintf("if (%s(\$('%s'),%s)) ", $ctrl->valid_fn, $ctrl->n, $ctrl->valid_parms);
				}
			}
			$fko = &$this->fa[$this->fl0];
			$sval = sprintf("&nbsp;<img name=\"%s_ic\" id=\"%s_ic\" src=\"disk.png\" ".
				"onclick=\"%sltup_do('%s','%s','%s','%s');\" style=\"visibility:hidden\">", 
				$ctrl->n, $ctrl->n, $valfn, $this->tabla, $ctrl->n, $fko->n, $fko->v);
		}
		return $sval;
	}
	
	public function editar($urlret="")
	{
		$trx = '';
		//print_r($this->fa);
		if ($urlret == "") $urlret = sprintf("ltable_olib_main.php?tabla=%s", $lto->tabla);
		$cv0 = $this->fa[$this->fl0]->v;
		if ($this->nuevo && $cv0 == 0) $nrec = "<u>(nuevo)</u>"; else $nrec = "<i>$cv0</i>";
		
		$trx .= "<script language=\"JavaScript\" src=\"prototype.js\"></script>";
		$trx .= "<script language=\"JavaScript\" src=\"sprintf.js\"></script>";
		$trx .= "<script language=\"JavaScript\" src=\"cal2.js\"></script>";
		$trx .= "<script language=\"JavaScript\" src=\"ltable_edit.js\"></script>";
		foreach ($this->jsfn as $jsfn)
		{
			$trx .= "<script language=\"JavaScript\" src=\"$jsfn\"></script>";
		}

		$trx .= "<script language=\"JavaScript\">\n";

		$initfn = "function lt_init_all() {\n var quetal = false;\n";
		//$initfn .= " alert('Begin InitAll');\n";
		$valfn = "function validar_todo() {\n var todook = true;\n";
		$primo = '';
		foreach ($this->ordenv as $nord)
		{
			$fl = &$this->fa[$nord];
			if ($primo == '')
			{
				if (!$fl->ro && !$fl->hidden && $fl->ctrl_type != LTO_SEPARATOR && $fl->n != $this->fl0)	$primo = $nord;
			}
			if (!enblanco($fl->valid_fn) 
				&& ($fl->ctrl_type == LTO_TEXTBOX || $fl->ctrl_type == LTO_EDITBOX))
			{
				if (enblanco($fl->valid_parms))
				{
					$valfn .= sprintf(" if (todook) { todook = " .
						"%s(\$('%s')); }\n",
						$fl->valid_fn, $fl->n);
				}
				else
				{
					$valfn .= sprintf(" if (todook) { todook = " .
						"%s(\$('%s'), %s); }\n",
						$fl->valid_fn, $fl->n, $fl->valid_parms);
				}
				///$valfn .= sprintf(" alert(\"%s=\"+todook);\n", $this->fa[$nord]->valid_fn);
			}
			
			if (!enblanco($fl->init_fn))
			{
				if (enblanco($fl->init_parms))
				{
					$initfn .= sprintf(" quetal = %s(\$('%s'));\n",
						$fl->init_fn, $fl->n);
				}
				else
				{
					$initfn .= sprintf(" quetal = %s(\$('%s'),%s);\n",
						$fl->init_fn, $fl->n, $fl->init_parms);
				}
			}
		}
		$valfn .= " return todook;\n}\n";

		if ($primo != '') $initfn .= " \$(\"$primo\").focus();\n";
		if (!enblanco($this->onload_custom)) $initfn .= " " . $this->onload_custom . "\n";
		$initfn .= "}\n";
		
		$unload_fn = "function lt_unload_all() { ";
		if (!enblanco($this->onunload_custom)) $unload_fn .= $this->onunload_custom;
		$unload_fn .= "}\n";
		
		$trx .= $valfn;
		$trx .= $initfn;
		$trx .= $unload_fn;

		$trx .= "function lt_submit(elbutt) {\n" .
			" var dale = false;\n" .
			" \$(\"validando\").value=1;\n" .
			" dale = validar_todo();\n" .
			" \$(\"validando\").value = 0;\n" .
			" if (dale) {\n" .
			"  \$(\"have_js\").value = 1;\n" .
			"  elbutt.form.submit();\n }\n}\n";

		$trx .= "function lt_conf_del() {\n" .
			" return confirm(\"Realmente desea borrar este registro?\");\n}\n" ;
	
		$trx .= "</script>\n";
		
		$trx .= "<h3 align=\"center\">$this->title</h3>";
		$trx .= "<h4 align=\"center\">Registro ID: $nrec</h4>";
		$trx .= "<p style=\"text-align:center;margin:0px;\"><img src=\"wait.gif\" name=\"waiticon\" id=\"waiticon\" " .
			"style=\"visibility:hidden;\" align=\"center\"></img></p>";
		$trx .= "<div name=\"ltform_msg\" id=\"ltform_msg\" " .
			"style=\"position:absolute;z-index:6;top:100px;left:300px;visibility:hidden;background:white;".
			"text-align:center;border:5px solid black;\">".
			"<div name=\"ltform_msg_p\" id=\"ltform_msg_p\" ".
			"style=\"text-align:center;margin:10px;background:white;\"></div></div>";
		$trx .= "<div name=\"ltable_msg\" class=\"stdpg\"></div>";
		$trx .= "<form name=\"$this->form_name\" action=\"$this->form_action\" method=\"post\">";
		
		$trx .= "<input type=\"hidden\" name=\"tabla\" id=\"tabla\" value=\"$this->tabla\">";
		$trx .= "<input type=\"hidden\" name=\"campo\" id=\"campo\" value=\"$this->fl0\">";
		$trx .= "<input type=\"hidden\" name=\"valor\" id=\"valor\" value=\"$cv0\">";
		$trx .= "<input type=\"hidden\" name=\"$this->fl0\" id=\"$this->fl0\" value=\"$cv0\">";
		
		$trx .= "<input type=\"hidden\" name=\"validando\" id=\"validando\" value=\"0\">";
		$trx .= "<input type=\"hidden\" name=\"have_js\" id=\"have_js\" value=\"0\">";
		$trx .= sprintf("<input type=\"hidden\" name=\"nuevo\" id=\"nuevo\" value=\"%d\">",
			$this->nuevo);
		$trx .= "<input type=\"hidden\" name=\"stdret\" id=\"stdret\" value=\"$this->stdret\">";
		$trx .= "<input type=\"hidden\" name=\"forzar_valor\" id=\"forzar_valor\" value=\"$this->forzar_valor\">";
		$trx .= sprintf("<input type=\"hidden\" name=\"cret\" id=\"cret\" value=\"%s\">", $urlret);

		$nop = 0;
		$trx .= "<table border=\"1\" align=\"center\" cellpadding=\"2%\" class=\"stdpg4\">";
		
		$initbuf = '';
		
		foreach ($this->ordenv as $nord)
		{
			$fx = &$this->fa[$nord];
			if (!$fx->hidden)
			{
				$trx .= "<tr>";
				if (strcmp($fx->n, $this->fl0) != 0)
				{
					if ($fx->ctrl_type == LTO_SEPARATOR)
					{
						$trx .= sprintf("<td colspan=\"2\" style=\"height:40px;font-weight:bold;".
							"text-align:center;\">&laquo;&nbsp;%s&nbsp;&raquo;</td>", $fx->title);
					}
					else
					{
						$trx .= sprintf("<th>%s</th><td>", $fx->title);
						$fx->render($trx, $this->nuevo);
						$trx .= $this->render_update($fx);
						$trx .= "</td>";
					}
				}
				$trx .= "</tr>";
			}
		}
		
		$trx .= "<div name=\"hiddenflds_div\" id=\"hddflds_div\" style=\"display:none;\">";
		foreach ($this->ordenv as $nord)
		{
			$fx = &$this->fa[$nord];
			if ($fx->hidden) $fx->render($trx, $this->nuevo);
		}
		$trx .= "</div>";
		
		if (!$this->rdonly)
		{
			if ($this->allowedit || $this->nuevo) // TODO: this->allownew
			{
				$trx .= "<tr><td colspan=\"2\" align=\"center\" style=\"height:50px;\">";
				$trx .= "<input type=\"button\" name=\"btn_guardar\" value=\"Guardar cambios\" " .
					"onclick=\"lt_submit(this);\"></td></tr>";
			}
		}
		$trx .= "</table></form>";

		if (!$this->nuevo && !$this->rdonly && $this->allowdel)
		{
			$scode = substr(sprintf("%d", time()), 4);
			$trx .= "<hr><form action=\"ltable_olib_del.php\" method=\"post\" " .
				"onsubmit=\"return lt_conf_del();\">" .
				"<input type=\"hidden\" name=\"tabla\" value=\"$this->tabla\">" .
				"<input type=\"hidden\" name=\"campo\" value=\"$this->fl0\">" .
				"<input type=\"hidden\" name=\"valor\" value=\"$cv0\">" .
				sprintf("<input type=\"hidden\" name=\"cret\" id=\"cret\" value=\"%s\">", $urlret).
				"<table align=\"center\" cellpadding=\"2%\" class=\"stdpg4\">".
				"<tr><td>C&oacute;digo de seguridad</td><td>$scode</td></tr>".
				"<tr><td>Escriba el c&oacute;digo de seguridad</td><td>".
				"<input type=\"hidden\" name=\"codsec1\" value=\"$scode\">".
				"<input type=\"text\" name=\"codsec2\" value=\"0\"></td></tr>".
				"<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" " .
				"value=\"Borrar registro\"></td></tr>".
				"</table></form>";
		}

		$trx .= "<script language=\"JavaScript\">" .
			"window.onload=lt_init_all;" .
			"windows.onunload=lt_unload_all;" .
			"</script>";
		
		return $trx;
	}
	
	public function blank()
	{
		$this->nuevo = true;
		foreach ($this->fa as $fl)
		{
			$this->fa[$fl->n]->v = $fl->df;
			$this->fa[$fl->n]->autovar();
			if (stripos('dht', $fl->t) !== false) $this->fa[$fl->n]->v = time();  
			$this->fa[$fl->n]->format();
		}
	}
	
	public function load_record($condicion='', $valor=0, $allownew=true)
	{
		$isok = false;
		
		$campos = '';
		foreach ($this->fa as $fl)
		{
			if ($fl->esdato && $fl->ctrl_type != LTO_SEPARATOR)
			{
				if (stripos('dht', $fl->t) === false) $ctmp = ",$fl->n";
				else $ctmp = ",UNIX_TIMESTAMP($fl->n) AS $fl->n";
				$campos .= $ctmp;
			}
		}
		$campos = substr($campos, 1);
		
		if (enblanco($condicion))
		{
			$query = sprintf("SELECT %s FROM %s WHERE %s=%d",
				$campos, $this->tabla, $this->fl0, $valor);
		}
		else
		{
			$query = sprintf("SELECT %s FROM %s WHERE %s",
				$campos, $this->tabla, $condicion);			
		}
		//echo "<p>Q=<b>$query</b></p>";
		
		if (($res = mysql_query($query)) !== false)
		{
			if (mysql_num_rows($res) > 0)
			{
				if (($row = mysql_fetch_assoc($res)) !== false)
				{
					$this->nuevo = false;					
					foreach ($this->fa as $fl)
					{
						$cn = $fl->n;
						$this->fa[$cn]->v = $fl->df;
						$this->fa[$cn]->nuevo = 0;
						if ($fl->ctrl_type == LTO_SEPARATOR) continue;
						if ($fl->esdato && isset($row[$cn]))
						{
							$this->fa[$cn]->v = $row[$cn];
						}
						if ($fl->ctrl_type == LTO_PASSWORD) $this->fa[$cn]->v = $fl->df;
						$this->fa[$cn]->format();
					}
					$isok = true;
				}
			}
			else
			{
				if ($allownew)
				{
					$this->blank();
					$isok = true;
				}
				else
				{
					$this->error_code = 10035;
					$this->error_msg = "Registro no encontrado.";
				}
			}
			mysql_free_result($res);
		}
		else
		{
			$this->error_code = 10014;
			$this->error_msg = mysql_error();
		}
		
		$ap = new ltable_addparms();
		$ap->reemplazar($this);

		return $isok;
	}	

	public function load_post()
	{
		foreach ($this->fa as $fl)
		{
			//printf("n=%s<br>", $fl->n);
			if (isset($_POST[$fl->n]))
			{
				$fl->text = $_POST[$fl->n];
				if (stripos($fl->funcion, 'U') !== false) $fl->text = strtoupper($fl->text);
				if (stripos($fl->funcion, 'L') !== false) $fl->text = strtolower($fl->text);
				if ($fl->ctrl_type == LTO_CHECKBOX) $fl->text = '1';
			}
			else
			{
				if ($fl->ctrl_type ==  LTO_CHECKBOX) $fl->text = '0';
				if ($fl->ctrl_type == LTO_LISTBOX)
				{
					$hnm = "HDDN_$fl->n";
					if (isset($_POST[$hnm])) $fl->text = $_POST[$hnm];
				}
			}
			$this->fa[$fl->fln]->text = $fl->text;
		}
	}
	
	public function values_from_text()
	{
		foreach ($this->fa as $fl)
		{
			if (stripos('dht', $fl->t) !== false)
			{
				$mdta = $this->nuevo ? 2 : 3;
				if ($fl->dt_auto >= $mdta) $fl->v = time();
				else
				{
					if ($fl->t == 'd') $fl->v = fecha2time(ctod($fl->text));
					if ($fl->t == 'h') $fl->v = hora2time(ctoh($fl->text));
					if ($fl->t == 't') $fl->v = $fl->text;
				}
			}
			else
			{
				switch ($fl->t)
				{
					case 'n':
						$fl->v = nen($fl->text);
						break;
					default:
						$fl->v = $fl->text;
						break;
				}
			}
			$fl->autovar();
			$this->fa[$fl->fln]->v = $fl->v;
		}
	}
	
	public function build_update($campo, $valor, $condicion='')
	{
		$ups = $upss = $this->update_set = '';

		if ($this->nuevo || $this->umethod == 0)
		{
			foreach ($this->fa as $fl)
			{
				if (!$fl->esdato) continue;
				if ($fl->ctrl_type == LTO_SEPARATOR) continue;
				if ($fl->ctrl_type == LTO_PASSWORD) $ups .= sprintf(",'*%s'", strtoupper(sha1(sha1($fl->v,true))));
				else
				{
					switch ($fl->t)
					{
						case 'n':
						case 'i':
						case 'b':
							$ups .= ",$fl->v";
							break;
						case 'c':
						case 'm':
							$ups .= ",'". mysql_real_escape_string($fl->v) ."'";
							///$ups .= ",'$fl->v'";
							break;
						case 'd':
						case 'h':
						case 't':
							$ups .= ",FROM_UNIXTIME($fl->v)";
							break;
					}					
				}
			}
			if ($this->nuevo) $cmd = "INSERT"; else $cmd = "REPLACE";
			$upss = "$cmd INTO $this->tabla VALUES (" . substr($ups, 1) . ")";
		}

		if (!$this->nuevo && $this->umethod == 1)
		{
			foreach ($this->fa as $fl)
			{
				if (!$fl->esdato) continue;
				if ($fl->ctrl_type == LTO_SEPARATOR) continue;
				if ($fl->n == $this->fl0 || !$fl->isup) continue;
				switch ($fl->t)
				{
					case 'n':
					case 'i':
					case 'b':
						$ups .= ",$fl->fln=$fl->v";
						break;
					case 'c':
					case 'm':
						$ups .= ",$fl->fln='". mysql_real_escape_string($fl->v) ."'";
						break;
					case 'd':
					case 'h':
					case 't':
						$ups .= ",$fl->fln=FROM_UNIXTIME($fl->v)";
						break;
				}
			}
			if ($condicion == '') $condicion = sprintf("%s=%d", $campo, $valor);
			$this->update_set = substr($ups, 1);
			$upss = "UPDATE $this->tabla SET ".$this->update_set." WHERE $condicion";
		}
		
		return $upss;	
	}
	
	public function update($campo, $valor, $condicion='')
	{
		$isok = false;
		
		$this->load_post();
		$query = $this->build_update($campo, $valor, $condicion);
		//echo "<p>Q=<b>$query</b></p>";
		if (mysql_query($query) !== false)
		{
			$this->insert_id = mysql_insert_id();
			$isok = true;
		}
		else
		{
			$this->error_code = 10030;
			$this->error_msg = mysql_error();
		}
		
		return $isok;
	}
	
	function bogus()
	{
		foreach ($this->fa as $fl) print_r($fl);
	}
	
	function deps($valor, $recop='U', &$buf)
	{
		$isok = true;
		///$buf = '';
		
		if ($this->dp_count > 0)
		{
			$isok = false;
			$ndep = 0;
			$allok = true;
			$umethod = $this->umethod;
			
			$fl0 = $this->fa[$this->fl0];
			$fl1 = $this->fa[$this->fl1];
			
			$nupd = 0;
			if ($umethod == 1)
			{
				foreach ($this->fa as $fl)
				{
					if ($fl->isup && ($fl->n != $fl0->n)) $nupd++;
				}
			}
			
			$buf .= "<p align=\"center\">Chequeando dependencias...</p>";
			
			$query = sprintf("SELECT %s,%s FROM %s WHERE %s=%d",
				$fl0->n, $fl1->n, $this->tabla, $fl0->n, $valor);
			if (($res = mysql_query($query))!==false)
			{
				$buf .= "<table border=\"1\" align=\"center\" cellpadding=\"2%\">".
					"<tr><th>$fl0->title</th><th>$fl1->title</th></tr>";
				if (($row = mysql_fetch_row($res))!== false)
				{
					$buf .= sprintf("<tr><td>%s</td><td>%s</td></tr>",
						$row[0], $row[1]);
				}
				$buf .= "</table>";
				mysql_free_result($res);
			}
			else
			{
				$this->error_code = 10030;
				$this->error_msg = mysql_error();
			}
			
			foreach ($this->dp as $dp)
			{
				$query = sprintf("SELECT %s FROM %s ", $dp->tbl_fields, $dp->tbl);
				if (stripos('nib', $dp->fl_t) !== false)
				{
					$query .= sprintf("WHERE %s=%d", $dp->fl_n, $valor);
				}
				else
				{
					$query .= sprintf("WHERE %s='%s'", $dp->fl_n, $valor);
				}
				//echo $query;
				if (($res = mysql_query($query)) !== false)
				{
					$nr = mysql_num_rows($res);
					if ($nr > 0)
					{
						$buf .= "<p align=\"center\">Tabla relacionada <b>$dp->tbl</b>" .
							" tiene <b>$nr</b> registro(s) dependiente(s) " .
							"de este registro.</p>";
						$ndep++;
						$buf .= "<table border=\"1\" align=\"center\" cellpadding=\"2%\">" .
								"<tr>";
						$stit = strtok($dp->tbl_titles, ",");
						while ($stit !== false)
						{
							$buf .= "<th>$stit</th>";
							$stit = strtok(",");
						}
						$buf .= "</tr>";
						while (($row = mysql_fetch_row($res)) !== false)
						{
							$buf .= "<tr>";
							foreach ($row as $rv)
							{
								$buf .= "<td>$rv</td>";
							}
							$buf .= "</tr>";
						}
						$buf .= "</table>";
					}
					mysql_free_result($res);
				}
				else
				{
					$this->error_code = 10035;
					$this->error_msg = mysql_error();					
					$allok = false;
				}
			}
			
			if ($allok)
			{
				if ($ndep == 0)
				{
					$buf .= "<p align=\"center\">Dependencias OK.</p>";
					$isok = true;
				}
				else
				{
					if ($recop == 'U')
					{
						$buf .= "<p align=\"center\"><i>Advertencia:</i> <b>$ndep</b> " .
							"dependencias.</p>";
						if ($umethod == 0)
						{
							$buf .= "<p align=\"center\"><b>IMPOSIBLE ACTUALIZAR ESTE " .
								"REGISTRO.</b><br><i>Elimine manualmente todos los " .
								"registros dependientes en las tablas relacionadas, " .
								"antes actualizar este registro.</i></p>";
						}
						if ($umethod == 1)
						{
							if ($nupd > 0) $isok = true;
							else $buf .= "<p align=\"center\">Error: Esta tabla no posee " .
								"campos actualizables.</p>";
						}
					}
					if ($recop == 'D')
					{
						$buf .= "<p align=\"center\"><i>Advertencia:</i> <b>$ndep</b> dependencias." .
							"<p align=\"center\"><b>IMPOSIBLE BORRAR ESTE REGISTRO.</b>" .
							"<br><i>Elimine manualmente todos los registros dependientes en " .
							"las tablas relacionadas a esta, antes de borrar este registro.</i></p>";
					}
				}
			}
		}
		else $buf .= "<p class=\"nohay\" align=\"center\">&laquo;Tabla <b>$this->tabla</b> " .
			"no posee dependencias registradas&raquo;</p>";
		
		return $isok;
	}	
}

class ltable_addparms
{
	public $a=array(),$sz=0;
	function __construct()
	{
		$this->sz = 0;
		if (isset($_REQUEST["pc"]))
		{
			$this->sz = $_REQUEST["pc"]+0;
			for ($ii=0; $ii<$this->sz; $ii++)
			{
				$this->a[$ii]->n = $_REQUEST["pn".$ii];
				$this->a[$ii]->v = $_REQUEST["pv".$ii];
			}
		}
	}
	public function reemplazar(ltable $lto)
	{
		if ($this->sz > 0)
		{
			foreach ($this->a as $pp) $lto->fa[$pp->n]->v = $pp->v;
		}
	}
}
?>
