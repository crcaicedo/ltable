<?php
require_once 'getbrowser.php';

/**
 * Constante para indicar que la fuente de un listbox es un arreglo
 */
define("LT_LIST_SRC_ARRAY", 1);
/**
 * Constante para indicar que la fuente de un listbox viene de una consulta
 */
define("LT_LIST_SRC_TABLE",0);

define('LT_BR', '<br>');
define('LT_SP', '&nbsp;');

class lt_ctrl
{
	/**
	 * 
	 * Atributos del ctrl n = nombre, t= tipo, l=long ctrl, pd= nro decimales permitidos, df=,v= valor real en php,text=valor visual del ctrl
	 * @var String/Number/bool
	 */
	public $n = '', $t = 'c', $l = 10, $pd = 0, $df = '', $v = '', $text = '', $no = '', $htipo = true;
	/**
	 * 
	 * Atributos del ctrl esdato=si el ctrl es una columna d la tabla, autovar= si es una variable de sistema, postvar= si es una variable post, isup= si es un update lo que se esta consultando, fln= cuando el nombre del campo no coincide con el de la tabla
	 * @var String/bool
	 */
	public $esdato = true, $autovar = false, $postvar = '', $isup = true, $fln = '', $sufijo='';
	public $dt_auto = 0, $autosize = false, $tabindex=0, $ctrlndx = -1, $autocomplete = '';

	public $ctrl_type = LTO_TEXTBOX;
	/**
	 * 
	 * Otros atributos del control, relativos a textboxes y text areas. title es el tooltip del control, vcols es el ancho del contrl, vrows es el alto del control 
	 * @var String/int es el tooltip del control
	 */
	public $title = '', $vcols = 10, $vrows = 1;
	public $hidden = false, $ro = false, $enabled = true;
	/**
	 * 
	 * Atributos de css de los controles, $css_class es alguna clase ya existente, $style para escribir un estilo a pie, $rojo es para poner el texto en rojo
	 * @var unknown_type
	 */
	public $css_class = '', $style = '', $rojo = false;
	/**
	 * 
	 * Atributos del ctrl relativos a validaciones js, valid_fn = nombre fn js que valida el campo valid_parms = parametros a pasar a valid_fn
	 * @var  $valid_fn String 
	 * @var  $valid_params String
	 */
	public $valid_fn = '', $valid_parms = '';
	public $init_fn = '', $init_parms = '';
	public $onkey_fn = '', $onkey_parms = '';
	public $onkeyup_fn = '', $onkeyup_parms = '';
	public $onfocus_fn = '', $onfocus_parms = '';
	public $onkeydown_fn = '', $onkeydown_parms = '';
	public $onclick_fn = '', $onclick_parms = '';
	public $funcion = '', $form = '', $ltform = false;
	/**
	 * 
	 * Parametros de las funciones JavaScript de los controles
	 * @var String/bool/int $update_fn es el nombre de la funcion, update_params son los parametros de la funcion adicionales al objeto sobre el que se ejectua el evento
	 */
	public $update_fn = "", $update_parms = "", $save_default = false, $form_id = 0;
	public $htp='', $primary_key = false;
	protected $_autofocus = false;
	
	protected $_tabla = '', $_valor_clave = '', $_nombre_clave = '', $_nuevo = FALSE, $_info_registro = FALSE;
	
	public function setRegistroInfo($tabla, $nombre_clave, $valor_clave, $nuevo)
	{
		$this->_tabla = $tabla;
		$this->_valor_clave = $valor_clave;
		$this->_nombre_clave = $nombre_clave;
		$this->_nuevo = $nuevo;
		$this->_info_registro = TRUE;
	}
	
	public function basicup($fln, $t, $df, $ctrl_type=LTO_TEXTBOX)
	{
		$this->fln = $this->n = $fln;
		$this->t = $t;
		$this->df = $df;
		$this->ctrl_type = $ctrl_type;
	}
	
	/**
	 * 
	 * Indica si el control se auto enfoca
	 * @param boolean $bSet
	 * TRUE si se autoenfoca
	 */
	public function setAutoFocus($bSet)
	{
		$this->_autofocus = $bSet;
	}
	
	public function render_onclick()
	{
		$fn = '';
		if (!enblanco($this->onclick_fn))
		{
			if (enblanco($this->onclick_parms))
			{
				$fn = sprintf(" onclick=\"%s(this);\"", $this->onclick_fn);
			}
			else
			{
				$fn = sprintf(" onclick=\"%s(this,%s);\"", $this->onclick_fn, $this->onclick_parms);
			}
		}
		return $fn;
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
	public function render_onkeyup()
	{
		$fn = '';
		$fn2 = '';
		if (!enblanco($this->onkeyup_fn))
		{
			if (enblanco($this->onkeyup_parms))
			{
				$fn2 = sprintf("%s(this,event);", $this->onkeyup_fn);
			}
			else
			{
				$fn2 = sprintf("%s(this,event,%s);", $this->onkeyup_fn, $this->onkeyup_parms);
			}
		}
		$fn = " onkeyup=\"$fn2\"";
		
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
	/**
	 * 
	 * Setea los parametros adicionales de la funcion de validacion
	 * @param array $pa
	 * Parametros adicionales
	 */
	public function valid_parms_from(array $pa)
	{
		$this->valid_parms = implode(',', $pa);
	}
	public function render_valid($explicit_name=false, $notag=false)
	{
		$sval = '';
		$sfn = '';
		$saux2 = '';
		if (gettype($this->valid_parms) == 'array') $this->valid_parms = implode(',', $this->valid_parms);
		
		if ($explicit_name) $onm = sprintf("\$('%s')", $this->n); else $onm = "this";

		$edup = '';
		if ($this->_info_registro && !$this->_nuevo) $edup = sprintf(" && ltup_enable('%s')", $this->n);
		
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
			else
			{
				if ($svdef != '') $sval = sprintf(" onchange=\"%s%s\"", substr($svdef, 3), $edup);
				else
				{
					$sval = sprintf(" onchange=\"true%s\"", $edup);
					//error_log($sval);
				}
			}
		}
		return $sval;
	}
	
	public function autovar()
	{
		if ($this->autovar) $this->v = autovar($this->fln);
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
				$fe = lt_fecha::fromT($this->v);
				$this->text = $fe->s();
				break;
			case 'h':
				$hr = lt_fecha::fromT($this->v, LT_HORA_T);
				$this->text = $hr->s();
				break;
			// TODO: formateo datetime
			//case 't':
			//	$dt = lt_fecha::fromT($this->v, LT_FECHAHORA_T);
			//	$this->text = $dt->s();
			//	break;
			case 'n':
				$this->text = number_format($this->v, $this->pd, ',', '.');
				break;
			default:
				$this->text = $this->v;
				break;
		}
	}
	/**
	 * 
	 * Asignacion de valor por defecto del ctrl
	 * @param Any $valor 
	 * es el valor del elemento a seleccionar dependera del tipo de control que valor se acepta
	 */
	public function assign($valor)
	{
		if (gettype($valor) == 'object' && get_class($valor) == 'lt_fecha') $this->v = $valor->to_time();
		elseif (gettype($valor) == 'string' && ($this->t == 'd' || $this->t == 'h' || $this->t == 't'))
        {
            $tipo = LT_FECHA_T;
            if ($this->t == 'h') $tipo = LT_HORA_T;
            if ($this->t == 't') $tipo = LT_FECHAHORA_T;
            $tmpfe = new lt_fecha(0, $tipo);
            $tmpfe->from_string($valor);
            $this->v = $tmpfe->timestamp;
        }
        else $this->v = $valor;
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
	
	public function render_dup(&$buf)
	{
		if (!$this->_nuevo)
		{
			if ($this->_tabla != '')
			{
				$vfn = 'true';
				if ($this->valid_fn != '')
				{
					if ($this->valid_parms != '')
					{
						$vfn = sprintf("%s(\$('%s'),%s)", $this->valid_fn, $this->n, $this->valid_parms);
					}
					else $vfn = sprintf("%s(\$('%s'))", $this->valid_fn, $this->n);
				}
				$buf .= sprintf("&nbsp;<img name=\"%s_ic\" id=\"%s_ic\" src=\"disk.png\" ".
					"onclick=\"%s && ltup_do('%s','%s','%s','%s');\" style=\"visibility:hidden\">",
					$this->n, $this->n, $vfn, $this->_tabla, $this->n, $this->_nombre_clave, $this->_valor_clave);
			}
		}
	}
	
	public function render_visible(&$buf, $nuevo=false)
	{
		$css_class = $this->css_class != '' ? sprintf(" class=\"%s\"", $this->css_class): '';
		$buf .= sprintf("<input type=\"hidden\" name=\"%s\" id=\"%s\" value=\"%s\"%s>%s",
			$this->n, $this->n, $this->text, $css_class, $this->htp);
	}
	/**
	 * 
	 * Agrega al buffer el ctrl que llama la funcion
	 * @param variant $canvas
	 * es el buffer donde se van almacenando el html generado
	 * @param bool $nuevo 
	 * Si es el registro que se esta ingresando es nuevo
	 */
	public function render(&$canvas=false, $nuevo=false)
	{
		$css_class = $this->css_class != '' ? sprintf(" class=\"%s\"", $this->css_class): '';
		if ($this->htipo)
		{
			$this->htp = sprintf("<input type=\"hidden\" name=\"%s__t\" id=\"%s__t\" value=\"%s\"%s>",
				$this->n, $this->n, $this->t, $css_class);
		}
		if (gettype($canvas) == 'boolean' && $this->form !== false) $canvas = $this->form;		
		if (gettype($canvas) == 'string')
		{ 
			if ($this->hidden)
			{
				$canvas .= sprintf("<input type=\"hidden\" name=\"%s\" id=\"%s\" value=\"%s\"%s>%s",
						$this->n, $this->n, $this->text, $css_class, $this->htp);
			}
			else
			{
				$this->render_visible($canvas, $nuevo);
				$this->render_dup($canvas, $nuevo);
			}
		}
		if (gettype($canvas) == 'object')
		{ 
			if ($this->hidden)
			{
				$canvas->buf .= sprintf("<input type=\"hidden\" name=\"%s\" id=\"%s\" value=\"%s\"%s>%s",
						$this->n, $this->n, $this->text, $css_class, $this->htp);
			}
			else
			{ 
				if ($this->tabindex == 0)
				{
					$canvas->tabindex++;
					$this->tabindex = $canvas->tabindex;  
				}
				$this->render_visible($canvas->buf, $nuevo);
				$this->render_dup($canvas->buf, $nuevo);
			}
			if ($canvas->form_autoparms) $canvas->form_parms .= sprintf(";%s,%s", $this->n, $this->t);
		}
	}
	function __construct(&$form=false)
	{
		if (gettype($form) == 'object')
		{ 
			$this->ctrlndx = $form->ctrl_c;
			$form->ctrl[$form->ctrl_c++] = $this;
		}
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
		$sctrl = "text";
		$spin_params = '';
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
				$this->css_class .= $this->css_class == '' ? 'datepicker':' datepicker';				
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
		if ($this->tabindex > 0) $stabindex = sprintf(" tabindex=\"%d\"", $this->tabindex); 

		$spin_params = '';
		$customsp = true;
		$br = new client_browser_info();
		if ($br->nombre == 'Chrome' || $br->nombre == 'Opera') $customsp = false;
		if ($this->spinner && !$this->ro && $this->enabled)
		{
			if ($customsp) $buf .= "<table border=\"0\" cellpadding=\"0\" " .
					"cellspacing=\"0\" class=\"noborde\"><tr><td rowspan=\"2\">";
		}
			
		$autofocus = "";
		if ($this->_autofocus) $autofocus = " autofocus=\"autofocus\"";

		$sdisa = $this->enabled ? '': ' disabled';
		$css_class = $this->css_class != '' ? sprintf(" class=\"%s\"", $this->css_class): '';
		$autocomplete = $this->autocomplete == '' ? '': sprintf(" autocomplete=\"%s\"", $this->autocomplete);
		
		if ($this->spinner && !$this->ro && $this->enabled)
		{
			if (!$customsp)
			{
				$spin_params = "max = \"{$this->spinner_max}\" min =\"{$this->spinner_min}\" step = \"{$this->spinner_step}\"";
				$sctrl = 'number';
			}
		}	
		
		
		$buf .= sprintf("%s<input type=\"%s\" name=\"%s\" id=\"%s\" value=\"%s\" " .
			"maxlength=\"%d\" size=\"%d\"%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s />",
			$this->htp, $sctrl, $this->n, $this->n, $this->text, 
			$maxlen, $vcols, 
			$stabindex, $sro, $sty,
			$sdisa, $this->render_valid(), $this->render_onkey(), $this->render_onkeyup(), $this->render_onkeyup(), 
			$this->render_onfocus($ofcl),  $onblur, $autofocus, $this->render_onclick(), 
			$css_class, $autocomplete,$spin_params);
			
		if ($this->_autofocus) $buf .= '<script>if(!("autofocus" in document.createElement("input"))){document.getElementById("'.$this->n.'").focus();}</script>';

		if ($this->spinner && !$this->ro && $this->enabled)
		{
			if ($customsp)
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
}

class lt_checkbox extends lt_ctrl
{
	public $ctrl_type = LTO_CHECKBOX, $t = 'l', $caption = '', $caption_align = LT_ALIGN_RIGHT;
	
	public function render_visible(&$buf, $nuevo=false)
	{
		$sro = $this->ro ? ' readonly': '';
		$sdisa = $this->enabled ? '': ' disabled';
		$schk = $this->v ? ' checked': '';
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		if ($this->css_class != '') $css = " class=\"$this->css_class\""; else $css = '';
		
		if ($this->v == -1)
		{
			$this->get_default(0);
			$this->save_default = true;
		}
		if ($this->v == -2)
		{
			$this->get_default(1);
			$this->save_default = true;
		}
		
		$scap = $lcap = $rcap = '';
		if ($this->caption != '') $scap = sprintf("<span%s%s>%s</span>", $sty, $css, $this->caption);
		if ($this->caption_align == LT_ALIGN_LEFT) $lcap = $scap.'&nbsp;';
		if ($this->caption_align == LT_ALIGN_RIGHT) $rcap = '&nbsp;'.$scap;
		
		$buf .= sprintf("%s%s<input type=\"checkbox\" name=\"%s\" id=\"%s\"%s%s%s%s%s%s%s />%s",
			$this->htp, $lcap, $this->n, $this->n, $schk, $sro, $sdisa, $this->render_valid(), $sty, $css, 
			$this->render_onclick(), $rcap);
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
		$stabindex = "";
		$maxl = '';
		if ($this->tabindex > 0) $stabindex = sprintf(" tabindex=\"%d\"", $this->tabindex); 
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		if ($this->l != 10) $maxl = sprintf(" maxlength=\"%d\"", $this->l);
	    $buf .= sprintf("%s<textarea name=\"%s\" id=\"%s\" cols=\"%d\" rows=\"%d\"%s " .
	    	"%s%s%s%s%s%s%s%s>%s</textarea>",
	    	$this->htp, $this->n, $this->n, $this->vcols, $this->vrows, $maxl,
	    	$sro, $sdisa, $this->render_valid(), $this->render_onkeydown(), $this->render_onkeyup(), $sty, $stabindex, 
	    	$this->render_onclick(), $this->text);
	}	
}

class lt_listbox extends lt_ctrl
{
	public $ctrl_type = LTO_LISTBOX;
	/**
	 * 
	 * Atributos sobre fuente de informacion del listbox
	 * @var Array/String/int row_source es la fuente de la lista si rowsource_type = 1, 
	 * $rowsource_type indica de donde se tomara la info para llenar el listbox, 0 es para usar el custom, 1 es para usar el row_source
	 */
	public $rowsource = array(), $rowsource_type = 0, $row_count = 0, $column_count = 1;
	public $tbl = '', $custom = '', $custom_new = '';
	public $fl_key = '', $fl_desc = '', $fl_order = '', $multiple=false;
	private $t_from = false, $t_n = '', $t_c = false, $t_fl = false, $t_ord = false, $t_grp = false, $t_lim = false;
	private $t_fo = false, $t_joins = false; 

	public function t(lt_form $fo, $tabla, $condicion=false, $campos=false, $joins=false, $ordenar=false, $agrupar=false, $limites=false)
	{
		$this->t_from = true;
		$this->t_n = $tabla;
		$this->t_c = $condicion;
		$this->t_fl = $campos;
		$this->t_ord = $ordenar;
		$this->t_grp = $agrupar;
		$this->t_lim = $limites;
		$this->t_fo = &$fo;
		$this->t_joins = false;
	}
	private function _from_table_do()
	{
		if (($tq = myquery::t($this->t_fo, $this->t_n, $this->t_c, $this->t_fl, $this->t_joins, $this->t_ord, 
				$this->t_grp, $this->t_lim, MYROW)))
		{
			$this->rowsource = array();
			$this->row_count = 0;
			$this->column_count = count($tq->r) - 1;
			foreach ($tq->a as $rg)
			{
				$this->rowsource[$this->row_count++] = $rg;
			}
		}
	}
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

		$pid = isset($_SESSION['pid']) ? $_SESSION['pid']: 0;
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid']: 47;
		
		$datoq = array('uid'=>$uid, 'proyecto_id'=>$pid, 'key_value'=>$this->v);
		$qp = plantilla_parse($query, $datoq);
		//error_log('LSQ:'.$qp);
		if (($res = mysql_query($qp)) !== false)
		{
			while (($row = mysql_fetch_row($res)) !== false)
			{
				foreach ($row as &$it) $it = mb_convert_encoding($it, 'UTF8');
				$this->rowsource[$this->row_count++] = $row;
			}
			mysql_free_result($res);
		}
		else $buf .= '<b>' . mysql_error() . '</b>';			
	}
	
	public function render_visible(&$buf, $nuevo=false)
	{
		$lati = '';
		if ($this->rowsource_type == LT_LISTBOX_ROWSOURCE_QUERY)
		{ 
			if ($this->t_from) $this->_from_table_do(); else $this->query($buf, $nuevo);
		}
		
		$stabindex = "";
		if ($this->tabindex > 0) $stabindex = sprintf(" tabindex=\"%d\"", $this->tabindex);
		
		$sdisa = $this->enabled ? '': ' disabled';
		$ssize = $this->vrows > 0 ? sprintf(" size=%d", $this->vrows): '';
		$smulti = $this->multiple ? ' multiple="multiple"': '';
		$smul = ($this->multiple && $this->t == 'a') ? '[]': ''; // si es array
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		$lati = sprintf("%s<select name=\"%s%s\" id=\"%s\" %s%s%s%s%s%s%s%s>",
			$this->htp, $this->n, $smul, $this->n, $sdisa, $this->render_valid(), 
			$this->render_onkey(), $sty, $stabindex, $smulti, $ssize, $this->render_onclick());
		//error_log($lati); 
		$valor_multi = is_array($this->v);
		$hay_valor = FALSE;
		///$va = $this->v; else $va = array($this->v);
		foreach ($this->rowsource as $rec)
		{
			$issel = '';
			if ($valor_multi) $issel = (array_search($rec[0], $this->v) !== FALSE) ? ' selected': '';
			else
			{
				if ($this->v == $rec[0])
				{
					if (!$hay_valor)
					{
						$issel = ' selected';
						$hay_valor = TRUE;
					}
				}
			}
				
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

		$vhidden = $this->v;
		if (is_array($this->v)) $vhidden = implode(',', $this->v);
		$lati .= "<input type=\"hidden\" id=\"HDDN_$this->n\" name=\"HDDN_$this->n\" " ."value= \"$vhidden\" >";
	    $buf .= $lati;
	    $buf .= "<input type=\"hidden\" id=\"COLS_$this->n\" value=\"$this->column_count\">";
	}
}

define('LT_BUTTON_NORMAL', 0);
define('LT_BUTTON_SUBMIT', 1);
define('LT_BUTTON_RESET', 2);
class lt_button extends lt_ctrl
{
	public $tipo = LT_BUTTON_NORMAL, $caption = '';
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
		$disa = $this->enabled ? '': ' disabled';
		$stipo = " type=\"button\"";
		if ($this->tipo == LT_BUTTON_SUBMIT) $stipo = " type=\"submit\"";
		if ($this->tipo == LT_BUTTON_RESET) $stipo = " type=\"reset\"";
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		$buf .= sprintf("<button id=\"%s\" name=\"%s\" value=\"%s\"%s%s%s%s%s>%s</button>", 
				$this->n, $this->n, $this->v, $stipo,  
				$disa, $sty, $this->render_onclick(), $this->render_valid(), $this->caption);
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
				$this->ba[$this->ba_sz]->caption = $row['caption'];
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
			"src=\"%s\"%s%s%s%s%s%s%s%s%s%s%s />", $this->htp, $this->n, $this->n, $this->src, 
			$sval, $sty, $sc, $sh, $sw, $sa, $sm, $su, $this->render_onclick());
	}
}

/**
 * 
 * Control para subir archivos
 *
 */
class lt_file_upload extends lt_ctrl
{
	public $accept = "", $almacenamiento = LTO_FILE_LINK; 
	public function render_visible(&$buf, $nuevo=false)
	{
		$disa = $this->enabled ? '': ' disabled';
		$sro = $this->ro ? " readonly": "";
		$saccept = $this->accept ? sprintf(" accept=\"%s\"", $this->accept): "";
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		if ($this->css_class != '') $sc = " class=\"$this->ccs_class\""; else $sc = "";

		$prw = '';
		if (($this->_tabla != '') && (!$this->_nuevo) && (strlen($this->v) > 0))
		{
			if ($this->almacenamiento == LTO_FILE) // ver archivo empotrado
			{
				$prw .= sprintf("<a href=\"javascript:void(0);\" ".
					"onclick=\"lt_file_view('%s','%s','%s',1);\">Ver</a>",
					$this->_tabla, $this->n, $this->_valor_clave);
			}
			if ($this->almacenamiento == LTO_FILE_LINK) // ver archivo enlazado
			{
				$ext = pathinfo('archivos/'.$this->v, PATHINFO_EXTENSION);
				$prw = sprintf("<p>&nbsp;<a href=\"archivos/%s\" target=\"_blank\">%s</a>", $this->v, $this->v);
				if (array_search($ext, array('pdf','docx','doc','xls','xlsx')) !== FALSE) 
					$prw .= sprintf("&nbsp;<a href=\"javascript:void(0);\" onclick=\"lt_filelink_view('%s');\">Visor</a>", $this->v);
				$prw .= sprintf("&nbsp;<input type=\"checkbox\" id=\"_FILE_DELETE_%s\" name=\"_FILE_DELETE_%s\">".
					"<label for\"_FILE_DELETE_%s\">Eliminar</label>".
					"</p>", $this->n, $this->n, $this->n);
			}			
		}
		
		$buf .= sprintf("<input type=\"file\" id=\"%s\" name=\"%s\" value=\"%s\"%s%s%s%s%s%s%s%s />%s", 
			$this->n, $this->n, $this->title, $sro, $disa, $sty, $sc, $this->render_valid(), 
			$this->render_onkey(), $saccept, $this->render_onclick(), $prw);
	}
} 

/**
 * 
 * Crear un input tipo boton de radio
 * @author carlos.caicedo
 *
 */
class lt_radiobutton extends lt_ctrl
{
	public $options = array();
	public $enumerar = false;
	public function render_visible(&$buf, $nuevo=false)
	{
		$disa = $this->enabled ? '': ' disabled';
		$sro = $this->ro ? " readonly": "";
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		if ($this->css_class != '') $sc = " class=\"$this->ccs_class\""; else $sc = "";
		$i = 0;
		foreach ($this->options as $opt)
		{
			if ($this->v == $opt[0]) $chk = ' checked'; else $chk = '';
			if ($this->enumerar)
				$buf .= sprintf("<input type=\"radio\" id=\"%s{$i}\" name=\"%s\" value=\"%s\"%s%s%s%s".
					"%s%s%s%s><label for = \"{$this->n}{$i}\">%s</label>",
					$this->n, $this->n, $opt[0], $sro, $disa, $sty, $sc,
					$this->render_valid(), $this->render_onkey(), $this->render_onclick(), $chk, $opt[1]);
			else
				$buf .= sprintf("<input type=\"radio\" id=\"%s\" name=\"%s\" value=\"%s\"%s%s%s%s".
					"%s%s%s%s><label for=\"%s\">%s</label>",
					$this->n, $this->n, $opt[0], $sro, $disa, $sty, $sc,
					$this->render_valid(), $this->render_onkey(), $this->render_onclick(), $chk, $this->n, $opt[1]);
			$i++;		
		}
	}
}

define('LT_LINK_STD', 1);
define('LT_LINK_JS', 2);
class lt_link extends lt_ctrl
{
	public $link = '', $caption = '', $tooltip = '', $destino = '_blank', $tipo = LT_LINK_STD, $download_as = '';
	public static function a($url, $caption, $destino='_blank', $tooltip='', $style='', $class='', $download_as='')
	{
		$tmpo = new self();
		$tmpo->link = $url;
		$tmpo->caption = $caption;
		$tmpo->destino = $destino;
		$tmpo->tooltip = $tooltip;
		$tmpo->tipo = LT_LINK_STD;
		$tmpo->style = $style;
		$tmpo->css_class = $class;
		$tmpo->download_as = $download_as;
		$tmpo->t = 'c';
		return $tmpo;
	}
	public static function js($jscode, $caption, $tooltip='', $style='', $class='')
	{
		$tmpo = new self();
		$tmpo->link = $jscode;
		$tmpo->caption = $caption;
		$tmpo->destino = '';
		$tmpo->tooltip = $tooltip;
		$tmpo->tipo = LT_LINK_JS;
		$tmpo->style = $style;
		$tmpo->css_class = $class;
		$tmpo->t = 'c';
		return $tmpo;
	}
	public function render_visible(&$buf, $nuevo=false)
	{
		$disa = $this->enabled ? '': ' disabled';
		$sro = $this->ro ? " readonly": "";
		if ($this->style != '') $sty = " style=\"$this->style\""; else $sty = '';
		if ($this->css_class != '') $sc = " class=\"$this->ccs_class\""; else $sc = "";
		if ($this->download_as != '') $dw = " download=\"$this->download_as\""; else $dw = "";
		if ($this->destino != '') $dst = sprintf(" target=\"%s\"", $this->destino); else $dst = '';
		if ($this->tooltip != '') $tip = sprintf(" title=\"%s\"", $this->tooltip); else $tip = '';
		if ($this->n != '') $sn = sprintf(" id=\"%s\" name=\"%s\"", $this->n, $this->n); else $sn = '';
		if ($this->tipo == LT_LINK_STD)
		{
			$buf .= sprintf("<a href=\"%s\"%s %s%s%s%s%s%s%s%s>%s</a>", 
				$this->link, $sn, $sro, $disa, $sty, $sc, $dst, $tip, $dw, $this->render_onclick(), $this->caption);
		}
		if ($this->tipo == LT_LINK_JS)
		{
			$buf .= sprintf("<a href=\"javascript:void(0);\" onclick=\"%s\"%s %s%s%s%s%s>%s</a>", 
				$this->link, $sn, $sro, $disa, $sty, $sc, $tip, $this->caption);
		}
	}
}
?>