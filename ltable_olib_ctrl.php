<?php
class lt_ctrl
{
	public $n = '', $t = 'c', $l = 10, $pd = 0, $df = '', $v = '', $text = '';
	public $esdato = true, $autovar = false, $postvar = '', $isup = true, $fln = '';
	public $dt_auto = 0, $autosize = false, $tab = 0;

	public $ctrl_type = LTO_TEXTBOX;
	public $title = '', $vcols = 10, $vrows = 1;
	public $hidden = false, $ro = false, $enabled = true;
	public $css_class = '', $style = '', $rojo = false;

	public $valid_fn = '', $valid_parms = '';
	public $init_fn = '', $init_parms = '';
	public $onkey_fn = '', $onkey_parms = '';
	public $onfocus_fn = '', $onfocus_parms = '';
	public $onkeydown_fn = '', $onkeydown_parms = '';
	public $funcion = '', $form = '';
	public $update_fn = "", $update_parms = "", $save_default = false, $form_id = 0;
	public $autofocus = false, $htp='';
	
	public function basicup($fln, $t, $df, $ctrl_type=LTO_TEXTBOX)
	{
		$this->fln = $this->n = $fln;
		$this->t = $t;
		$this->df = $df;
		$this->ctrl_type = $ctrl_type;
	}
	
	public function render_onkey()
	{
		$fn = '';
		$fn2 = '';
		if (!enblanco($this->onkey_fn))
		{
			if (enblanco($this->onkey_parms))
			{
				$fn2 = sprintf("%s(this,event);", $this->onkey_fn);
			}
			else
			{
				$fn2 = sprintf("%s(this,event,%s);", $this->onkey_fn, $this->onkey_parms);
			}
		}
		if (stripos($this->funcion, 'E') === false)
		{
			$fn = " onkeypress=\"var key; if (window.event) { key = window.event.keyCode; } ".
				"else { key = event.which; } if (key == 13) { $fn2 return false; } ".
				"else { return true; }\"";
		}
		else $fn = " onkeypress=\"$fn2\"";
		
		return $fn;
	}
	public function render_onfocus($ofcl='#0000ff')
	{
		$fn = '';
		$sfun = '';
		$fn2 = '';
		$high = sprintf("lt_highlight(this,'%s')", $ofcl);
		if (!enblanco($this->onfocus_fn))
		{
			if ($this->funcion != "") $sfun = sprintf(" && lt_execfn(this,'%s')", $this->funcion);
			if (enblanco($this->onfocus_parms))
			{
				$fn2 = sprintf("%s(this,event)%s;", $this->onfocus_fn, $sfun);
			}
			else
			{
				$fn2 = sprintf("%s(this,event,%s)%s;", $this->onfocus_fn, $this->onfocus_parms, $sfun);
			}
		}
		elseif ($this->funcion != "") $fn2 = sprintf("lt_execfn(this,'%s')", $this->funcion);
		if ($fn2 != "") $fn = sprintf(" onfocus=\"%s && %s\"", $high, $fn2);
		else $fn = sprintf(" onfocus=\"%s\"", $high);
		return $fn;
	}
	public function render_onkeydown()
	{
		$fn = "";
		$fn3 = "";
		if (!enblanco($this->onkeydown_fn))
		{
			if (enblanco($this->onkeydown_parms))
			{
				$fn3 = sprintf("%s(this);", $this->onkeydown_fn);
			}
			else
			{
				$fn3 = sprintf("%s(this,%s);", $this->onkeydown_fn, $this->onkeydown_parms);
			}
			$fn .= " onkeydown=\"$fn3\"";
		}
		return $fn;
	}
	
	public function render_valid($explicit_name=false, $notag=false)
	{
		$sval = '';
		$sfn = '';
		$saux2 = '';
		if ($explicit_name) $onm = sprintf("\$('%s')", $this->n);
		else $onm = "this";
		$edup = sprintf("&& ltup_enable('%s')", $this->n);
		if ($this->save_default) $svdef = sprintf(" && lt_put_default(this,'%s',%d,%d);",
			$this->n, $this->form_id, $this->ctrl_type); else $svdef = "";
		if (!enblanco($this->valid_fn))
		{
			if (enblanco($this->valid_parms))
			{
				$sfn = sprintf("%s(%s)%s%s", $this->valid_fn, $onm, $edup, $svdef);
			}
			else
			{
				$sfn = sprintf("%s(%s,%s)%s%s", $this->valid_fn, $onm, $this->valid_parms, $edup, $svdef);
			}
			if ($notag)
			{
				if (enblanco($this->funcion))
				{
					$sval = sprintf(" %s", $sfn);
				}
				else
				{
					$sval = sprintf(" %s && lt_execfn(%s,'%s')",
						$sfn, $onm, $this->funcion);
				}				
			}
			else
			{
				if (enblanco($this->funcion))
				{
					$sval = sprintf(" onchange=\"%s\"", $sfn);
				}
				else
				{
					$sval = sprintf(" onchange=\"%s && lt_execfn(%s,'%s')\"",
						$sfn, $onm, $this->funcion);
				}
			}
		}
		else
		{
			if (!enblanco($this->funcion))
			{
				if ($notag) $sval = sprintf(" lt_execfn(%s,'%s')%s%s", $onm, $this->funcion, $edup, $svdef);
				else $sval = sprintf(" onchange=\"lt_execfn(%s,'%s')%s%s\"", $onm, $this->funcion, $edup, $svdef);
			}
			else $sval = sprintf(" onchange=\"%s\"", substr($svdef, 3));
		}
		return $sval;
	}
	
	public function autovar()
	{
		if ($this->autovar)
		{
			if ($this->fln == 'uid') 
				$this->v = isset($_SESSION['uid']) ? $_SESSION['uid']: $_COOKIE['uid'];
			if ($this->fln == 'proyecto_id') 
				$this->v = isset($_SESSION['pid']) ? $_SESSION['pid']: $_COOKIE['pid'];
			if ($this->fln == 'ipaddr') $this->v = $_SERVER['REMOTE_ADDR'];
			if ($this->fln == 'cnf_serial') $this->v = sprintf("%06d", rand(1,999999));
		}
		if ($this->postvar == 1)
		{
			if (isset($_REQUEST[$this->n])) $this->v = $_REQUEST[$this->n];
		}
	}
	
	public function format()
	{
		switch ($this->t)
		{
			case 'd':
				$this->text = dtoc(fecha_from($this->v));
				break;
			case 'h':
				$this->text = htoc(hora_from($this->v));
				break;
			case 'n':
				$this->text = number_format($this->v, $this->pd, ',', '.');
				break;
			default:
				$this->text = $this->v;
				break;
		}
	}
	
	public function assign($valor)
	{
		$this->v = $valor;
		$this->format();
	}
	
	public function get_default($falloff_value, $form_id=0)
	{
		$isok = false;
		
		if ($this->form_id == 0) $this->form_id = $form_id;
		$q = sprintf("SELECT ctrl_value FROM ctrl_default " .
			"WHERE uid=%d AND form_id=%d AND ctrl_name='%s'",
			$_SESSION['uid'], $this->form_id, $this->n);
		//echo $q;
		if (($res = mysql_query($q)) !== false)
		{
			if (($ox = mysql_fetch_object($res)) !== false)
			{
				$this->assign($ox->ctrl_value);
				$isok = true;
			}
			mysql_free_result($res);
		}
		
		if (!$isok) $this->assign($falloff_value);
		
		return $isok;
	}
	
	public function render_visible(&$buf, $nuevo=false)
	{
		$buf .= sprintf("<input type=\"hidden\" name=\"%s\" id=\"%s\" value=\"%s\">%s",
			$this->n, $this->n, $this->text, $this->htp);
	}
	
	public function render(&$buf, $nuevo=false)
	{
		$this->htp = sprintf("<input type=\"hidden\" name=\"%s__t\" id=\"%s__t\" value=\"%s\">",
			$this->n, $this->n, $this->t);		
		if ($this->hidden)
		{
			$buf .= sprintf("<input type=\"hidden\" name=\"%s\" id=\"%s\" value=\"%s\">%s",
				$this->n, $this->n, $this->text, $this->htp);
		}
		else $this->render_visible($buf, $nuevo);
	}
}

class lt_textbox extends lt_ctrl
{
	public $ctrl_type = LTO_TEXTBOX;
	public $spinner = false, $spinner_max = 0, $spinner_min = 1, $spinner_step = 1;
	public $ispwd = false;
	public $mascara = '', $funcion = '', $fecha_selector=true;
	
	public function render_visible(&$buf, $nuevo=false)
	{	
		$sro = $this->ro ? " readonly": "";
		$sdisa = $this->enabled ? "": " disabled";
		$sctrl = "text";
		if ($this->ctrl_type == LTO_PASSWORD)
		{
	    	$sctrl =  'password';
	    	$this->assign('');
		}

		if ($this->t == 'n')
		{
			$maxlen = $this->l + $this->pd + 1 + round($this->l/3,0);
			$vcols = $maxlen + 1;
		}
		else
		{
			if ($this->t == 'd')
			{
				$maxlen = $vcols = 10;				
				$this->maxlen = $this->vcols = 10;				
			}
			else
			{
				$maxlen = $this->l;
				$vcols = $this->vcols;
			}
		}

		if ($this->autosize)
		{
			$vcols = strlen($this->text);
			$this->vcols = $vcols;
		}
		
		$sty = '';
		$obcl = '#000000';
		$ofcl = '#0000ff';
		if (stripos('nbi', $this->t) !== false)
		{
			if ($this->rojo && $this->v < 0)
			{
				$rojo = "color:red;";
				$obcl = $ofcl = '#ff0000';
			}
			else $rojo = "";
			$sty = sprintf(" style=\"%stext-align:right;%s\"", $this->style, $rojo);
		}
		else
		{
			if ($this->style != '') $sty = " style=\"$this->style\"";
		}
		
		///$onfocus = sprintf(" onfocus=\"lt_highlight(this,'%s');\"", $ofcl);
		$onblur = sprintf(" onblur=\"lt_downlight(this,'%s');\"", $obcl);
		
		$stabindex = "";
		if ($this->tab > 0) $stabindex = sprintf(" tabindex=\"%d\"", $this->tab); 

		if ($this->spinner && !$this->ro && $this->enabled)
			$buf .= "<table border=\"0\" cellpadding=\"0\" " .
			"cellspacing=\"0\" class=\"noborde\"><tr><td rowspan=\"2\">";
	
		$autofocus = "";
		if ($this->autofocus) $autofocus = " autofocus";

		$buf .= sprintf("%s<input type=\"%s\" name=\"%s\" id=\"%s\" value=\"%s\" " .
			"maxlength=\"%d\" size=\"%d\"%s%s%s%s%s%s%s%s%s />",
			$this->htp, $sctrl, $this->n, $this->n, $this->text, 
			$maxlen, $vcols, 
			$stabindex, $sro, $sty,
			$sdisa, $this->render_valid(), $this->render_onkey(), $this->render_onfocus($ofcl), $onblur, $autofocus);
			
		if ($this->t == 'd' && !$this->ro && $this->enabled && $this->fecha_selector)
		{
			$buf .= sprintf("<script language=\"JavaScript\">addCalendar(\"%s_cal\",\"Seleccionar fecha\", \"%s\", \"%s\");</script>", 
				$this->n, $this->n, $this->form);
			$buf .= sprintf("&nbsp;<small><a href=\"javascript:showCal('%s_cal')\">Ver</a>&nbsp;</small>",
				$this->n);
		}
		if ($this->autofocus) $buf .= '<script>if(!("autofocus" in document.createElement("input"))){document.getElementById("'.$this->n.'").focus();}</script>';

		if ($this->spinner && !$this->ro && $this->enabled)
		{
			$buf .= "</td>";
			$sty = "font-size:8px;margin:0;padding:0;width:20px;height:16px;";
			$buf .= sprintf("<td><input type=\"button\" value=\"&and;\" " .
				"onclick=\"lt_spinner_up('%s', %d, %d, %d, '%s'); %s;\" style=\"%s\"/></td></tr>",
				$this->n, $this->spinner_max, $this->spinner_min, $this->spinner_step, 
				$this->t, $this->render_valid(true, true), $sty);
			$buf .= sprintf("<tr><td><input type=\"button\" value=\"&or;\" " .
				"onclick=\"lt_spinner_down('%s', %d, %d, %d, '%s'); %s\" style=\"%s\"/></td>",
				$this->n, $this->spinner_max, $this->spinner_min, $this->spinner_step, 
				$this->t, $this->render_valid(true, true), $sty);
			$buf .= "</tr></table>";
		}		
	}
}

class lt_checkbox extends lt_ctrl
{
	public $ctrl_type = LTO_CHECKBOX;
	
	public function render_visible(&$buf, $nuevo=false)
	{
		$sro = $this->ro ? ' readonly': '';
		$sdisa = $this->enabled ? '': ' disabled';
		$schk = $this->v ? ' checked': '';
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		$buf .= sprintf("%s<input type=\"checkbox\" name=\"%s\" id=\"%s\"%s%s%s%s%s />",
			$this->htp, $this->n, $this->n, $schk, $sro, $sdisa, $this->render_valid(), $sty);
	}
}

class lt_separator extends lt_ctrl
{
	public $ctrl_type = LTO_SEPARATOR;
	
	public function render_visible(&$buf, $nuevo=false)
	{
		$buf .= "&laquo;&nbsp;$this->title&nbsp;&raquo;";
	}
}

class lt_editbox extends lt_ctrl
{
	public $ctrl_type = LTO_EDITBOX;
	
	public function render_visible(&$buf, $nuevo=false)
	{
		$sro = $this->ro ? ' readonly': '';
		$sdisa = $this->enabled ? '': ' disabled';
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
	    $buf .= sprintf("%s<textarea name=\"%s\" id=\"%s\" cols=\"%d\" rows=\"%d\"" .
	    	"%s%s%s%s%s>%s</textarea>",
	    	$this->htp, $this->n, $this->n, $this->vcols, $this->vrows,
	    	$sro, $sdisa, $this->render_valid(), $this->render_onkeydown(), $sty, $this->text);
	}	
}

class lt_listbox extends lt_ctrl
{
	public $ctrl_type = LTO_LISTBOX;
	public $rowsource = array(), $rowsource_type = 0, $row_count = 0, $column_count = 1;
	public $tbl = '', $custom = '', $custom_new = '';
	public $fl_key = '', $fl_desc = '', $fl_order = '';

	public function query(&$buf, $nuevo=false)
	{
		$this->rowsource = array();
		$this->row_count = 0;
		
		if ($nuevo)
		{
			$this->custom = empty($this->custom_new)?$this->custom:$this->custom_new;
		}
		if (!enblanco($this->custom)) $query = $this->custom;
		else
		{
			if ($this->autovar || $this->ro)
			{
				if ($this->t == 'c')
				{
					$query = sprintf("SELECT %s,%s FROM %s WHERE %s='%s'",
						$this->fl_key, $this->fl_desc, $this->tbl, $this->fl_key, $this->v);
				}
				else
				{
					$query = sprintf("SELECT %s,%s FROM %s WHERE %s=%s",
						$this->fl_key, $this->fl_desc, $this->tbl, $this->fl_key, $this->v);	    			
				}	    		
			}
			else
			{
				$orden = $this->fl_order;
				if (enblanco($orden)) $orden = $this->fl_desc;
				$query = sprintf("SELECT %s,%s FROM %s ORDER BY %s",
					$this->fl_key, $this->fl_desc, $this->tbl, $orden);
			}
		}
		
		$flda = explode(",", $this->fl_desc);
		$this->column_count = count($flda);
				
		$flkey = $this->fl_key;

		$pid = isset($_SESSION['pid']) ? $_SESSION['pid']: $_COOKIE['pid'];
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid']: $_COOKIE['uid'];
		
		$datoq = array('uid'=>$uid, 'proyecto_id'=>$pid, 'key_value'=>$this->v);
		$qp = plantilla_parse($query, $datoq);
		if (($res = mysql_query($qp)) !== false)
		{
			while (($row = mysql_fetch_row($res)) !== false)
			{
				$this->rowsource[$this->row_count++] = $row;
			}
			mysql_free_result($res);
		}
		else $buf .= '<b>' . mysql_error() . '</b>';			
	}
	
	public function render_visible(&$buf, $nuevo=false)
	{
		$lati = '';
		if ($this->rowsource_type == 0) $this->query($buf, $nuevo);
		
		$sdisa = $this->enabled ? '': ' disabled';
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		$lati = sprintf("%s<select name=\"%s\" id=\"%s\" %s%s%s%s>",
			$this->htp, $this->n, $this->n, $sdisa, $this->render_valid(), 
			$this->render_onkey(), $sty); 
		foreach ($this->rowsource as $rec)
		{
			$issel = ($this->v == $rec[0]) ? ' selected': '';
			
			///for ($xx = 0; $xx < $this->column_count; $xx++) $rek[$xx] = $rec[$xx+1];
			$ndx = 0;
			$xx = 0;
			foreach ($rec as $valor)
			{
				if ($ndx > 0)
				{
					$rek[$xx] = $rec[$ndx];
					$xx++;
					if ($xx > $this->column_count - 1) break;
				}
				$ndx++;
			}
			$sdesc = implode(" ", $rek);
			
			if ($this->autovar || $this->ro)
			{
				if ($this->v == $rec[0])
				{
					$lati .= sprintf("<option value=\"%s\"%s>%s</option>",
						$rec[0], $issel, $sdesc);
				}
			}
			else
			{
				$lati .= sprintf("<option value=\"%s\"%s>%s</option>",
					$rec[0], $issel, $sdesc);
			}
		}			
		$lati .= "</select>";

		$lati .= "<input type=\"hidden\" id=\"HDDN_$this->n\" name=\"HDDN_$this->n\" " .
			"value=\"$this->v\">"; 
	    $buf .= $lati;
	    $buf .= "<input type=\"hidden\" id=\"COLS_$this->n\" value=\"$this->column_count\">";
	}
}

class lt_button extends lt_ctrl
{
	public function render_valid($explicit_name=false, $notag=false)
	{
		$sval = '';
		$sfn = '';
		if ($explicit_name) $onm = sprintf("\$('%s')", $this->n);
		else $onm = "this";
		if (!enblanco($this->valid_fn))
		{
			if (enblanco($this->valid_parms))
			{
				$sfn = sprintf("%s(%s)", $this->valid_fn, $onm);
			}
			else
			{
				$sfn = sprintf("%s(%s,%s)", $this->valid_fn, $onm, $this->valid_parms);
			}
			if ($notag) $sval = sprintf(" %s", $sfn);
			else $sval = sprintf(" onclick=\"%s\"", $sfn);
		}
		return $sval;
	}
	public function render_visible(&$buf, $nuevo=false)
	{
		$sval = $this->render_valid();
		$disa = $this->enabled ? '': ' disabled';
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		$buf .= sprintf("%s<input type=\"button\" id=\"%s\" name=\"%s\" " .
			"value=\"%s\"%s%s%s>", $this->htp, $this->n, $this->n, $this->text, $sval, $disa, $sty);
	}
}

class lt_button_grp extends lt_ctrl
{
	public $ba = array();
	public $ba_sz = 0;
	
	public function load($tabla)
	{
		$q = "SELECT * FROM ltable_btngrp WHERE tabla='$tabla' and btngrp_id=$this->v " .
			"ORDER BY orden";
		if (($res = mysql_query($q)) !== false)
		{
			while (($row = mysql_fetch_assoc($res)) !== false)
			{
				$this->ba[$this->ba_sz] = new lt_button();
				$this->ba[$this->ba_sz]->n = $row['button_id'];
				$this->ba[$this->ba_sz]->valid_fn = $row['click_fn'];
				$this->ba[$this->ba_sz]->valid_parms = $row['click_parms'];
				$this->ba[$this->ba_sz]->text = $row['caption'];
				$this->ba[$this->ba_sz]->esdato = false;
				$this->ba_sz++;
			}
			mysql_free_result($res);
		}
	}
	
	public function render_visible(&$buf, $nuevo=false)
	{
		foreach ($this->ba as $btt)
		{
			$btt->render_visible($buf);
			$buf .= '&nbsp;';
		}
	}
}

class lt_img extends lt_ctrl
{
	public $height=-1, $width=-1, $ismap="", $usemap="", $alt="", $src="";
	public function render_visible(&$buf, $nuevo=false)
	{
		$sval = $this->render_valid();
		$disa = $this->enabled ? '': ' disabled';
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		if ($this->css_class != '') $sc = " class=\"$this->ccs_class\""; else $sc = "";
		if ($this->height != -1) $sh = " height=\"$this->height\""; else $sh = "";
		if ($this->width != -1) $sw = " width=\"$this->width\""; else $sw = "";
		if ($this->alt != '') $sa = " alt=\"$this->alt\""; else $sa = "";
		if ($this->ismap != '') $sm = " ismap=\"$this->ismap\""; else $sm = "";
		if ($this->usemap != '') $su = " usemap=\"$this->usemap\""; else $su = "";
		$buf .= sprintf("%s<img id=\"%s\" name=\"%s\" " .
			"src=\"%s\"%s%s%s%s%s%s%s%s%s%s />", $this->htp, $this->n, $this->n, $this->src, 
			$sval, $sty, $sc, $sh, $sw, $sa, $sm, $su);
	}
}
?>