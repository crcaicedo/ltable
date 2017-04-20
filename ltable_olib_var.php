<?php
class columna
{
	public $n = '', $t = 'c', $title = '', $esdato = false, $visible = true;
	public $fn = '', $ctrl = LISTON_NONE, $recno = 0, $v, $caption = '', $fv = "";
	public $align = 0, $style = "";

	public function assign($recno, &$row)
	{
		switch ($this->ctrl)
		{
			case LISTON_NONE:
			case LISTON_TEXTBOX:
				$this->v = $row[$this->n];
				$this->caption = $this->v;
				break;
			case LISTON_LINK:
				$this->caption = $row[$this->n];
				$this->v = $row[$this->fv];
				break;
		}
		$this->recno = $recno;
	}

	public function render(&$buf)
	{
		if ($this->esdato)
		{
			$buf .= sprintf("<input type=\"hidden\" name=\"%s%d\" id=\"%s%d\" " .
					"value=\"%s\">", $this->n, $this->recno, $this->n, $this->recno,
					$this->v);
		}
		if ($this->visible)
		{
			$salign = "";
			$sty = "border:1px solid black;font-size:8pt;";
			switch ($this->t)
			{
				case 'n': $salign = " align=\"right\""; break;
				case 'd': $salign = " align=\"center\""; break;
			}
			switch ($this->align)
			{
				case 1: $salign = " align=\"left\""; break;
				case 2: $salign = " align=\"right\""; break;
				case 3: $salign = " align=\"center\""; break;
			}
			$buf .= sprintf("<td%s style=\"%s\">", $salign, $sty);
				
			switch ($this->ctrl)
			{
				case LISTON_BUTTON:
					$buf .= sprintf("<input type=\"button\" value=\"%s\" " .
							"onclick=\"%s(%s)\">", $this->caption, $this->fn, $this->recno);
					break;
				case LISTON_LINK:
				    $fn = sprintf("%s(%d,'%s')", $this->fn, $this->recno, $this->v);
					$buf .= sprintf("<a href=\"javascript:void(0);\" onclick=\"%s\">%s</a>",
					    $fn, $this->caption);
					break;
				case LISTON_TEXTBOX:
					$txx = new lt_textbox();
					$txx->n = $this->n.$this->recno;
					$txx->t = $this->t;
					$txx->l = 7;
					$txx->pd = 2;
					$txx->valid_fn = $this->fn;
					$txx->valid_parms = $this->recno;
					$txx->assign($this->v);
					$txx->render($buf);
					break;
				default:
                    if ($this->t == 'n') $v = nes(doubleval($this->caption)); else $v = $this->caption;
					$buf .= $v;
				break;
			}
			$buf .= "</td>";
		}
	}

	public function __construct($n='', $visible=true, $esdato=false, $fn='', $title='')
	{
		$this->n = $this->fv = $n;
		$this->esdato = $esdato;
		$this->visible = $visible;
		$this->fn = $fn;
		$this->title = $title;
	}
}

class liston
{
	public $n = '', $buf = '', $style = '';
	public $colsz = 0, $rowsz = 0;
	public $col = array(), $row = array();

	public function __construct($name='', $style='')
	{
		$this->n = $name;
		$this->style = $style;
	}
	public function addcol($n='', $visible=true, $esdato=false)
	{
		$this->col[$this->colsz++] = new columna($n, $visible, $esdato);
	}
	public function addcols($anames)
	{
		foreach ($anames as $nm) $this->col[$this->colsz++] = new columna($nm);
	}
	public function addtitles($atitles)
	{
		for ($ii = 0; $ii < $this->colsz; $ii++)
			$this->col[$ii]->title = $atitles[$ii];
	}
	public function a2cols($acols)
	{
		foreach ($acols as $col)
		{
			$this->col[$this->colsz++] = new columna($col[0], $col[1], $col[2]);
		}
	}
	public function render(&$buf)
	{
		$recno = 0;
		$this->buf .= "<div";
		if ($this->n != '') $this->buf .= " name=\"$this->n\" id=\"$this->n\"";
		if ($this->style != '') $this->buf .= " style=\"$this->style\"";
		$this->buf .= ">";
		if ($this->rowsz > 0)
		{
			$this->buf .= "<table style=\"border-collapse: collapse; border: 1px solid black;\">";

			$th0 = "<th style=\"border:1px solid black;font-size:8pt;\">";
			$this->buf .= "<tr>";
			foreach ($this->col as $col)
			{
				if ($col->visible) $this->buf .= sprintf("%s%s</th>", $th0, $col->title);
			}
			$this->buf .= "</tr>";
				
			foreach ($this->row as $row)
			{
				$this->buf .= "<tr>";
				foreach ($this->col as &$colx)
				{
					$colx->assign($recno, $row);
					$colx->render($this->buf);
				}
				$recno++;
				$this->buf .= "</tr>";
			}
			$this->buf .= "</table>";
		}
		else
		{
			$this->buf .= '&laquo;Vacio&raquo;';
		}
		$this->buf .= "</div>";

		$buf .= $this->buf;
	}
	public function query($q, lt_form $fo)
    {
        $this->rowsz = 0;
        if (($qq = myquery::q($fo, $q, 'LISTONQUERY', TRUE, FALSE, MYASSOC))) {
            foreach ($qq->a as $row) {
                $this->row[$this->rowsz++] = $row;
            }
        }
    }
}

function casilla(lt_form $fo, $caption='', $inputo, $colspan=0, $inputo2=false, $rowspan=0)
{
	$fo->td(0, $colspan, '', '', $rowspan);
	$fo->buf .= "<p class=\"casilla\">$caption</p>";
	if ($inputo2 !== false)
	{
		$autotrx_old = $fo->autotrx;
		$autotdx_old = $fo->autotdx;
		$fo->autotrx = false;
		$fo->autotdx = false;
		$fo->tbl(0, 0, "0%", "noborde");
		$fo->tr();
		$fo->td();
		$inputo->render($fo->buf);
		$fo->td();
		$inputo2->render($fo->buf);
		$fo->trx();
		$fo->tblx();
		///$fo->autotrx = true;
		///$fo->autotdx = true;
		$fo->autotrx = $autotrx_old;
		$fo->autotdx = $autotdx_old;
	}
	else $inputo->render($fo->buf);
	$fo->tdx();
}

function login_popup(lt_form $fo, $laurl)
{
	$fo->jsi(sprintf("document.location.replace('%slogin_ask.php?tourl=%s')", RUTA, $laurl));
}

function xxxlogin_popup(lt_form $fo, $laurl)
{
		$fo->encabezado_base();
	//$fo->msg();
	$fo->js("login_popup.js");

	///$fo->div("loginpopupdiv", 0, "", "position:absolute;z-index:6;top:50px;left:50px;" .
	///		"visibility:visible;background:white;text-align:center;border:5px solid black;");
	////$fo->div("loginpopupdiv_p", 0, "", "text-align:center;margin:10px;background:white;");

	$fo->hdr("Sistema MPRS : Identificaci&oacute;n");
	///$fo->wait_icon();

	$txt0 = new lt_textbox();
	$txt0->n = "user";
	$txt0->t = 'c';
	$txt0->l = $txt0->vcols = 32;

	$txt1 = new lt_textbox();
	$txt1->n = "passwd";
	$txt1->t = 'c';
	$txt1->l = $txt1->vcols = 16;
	$txt1->ctrl_type = LTO_PASSWORD;
	$txt1->funcion = "E";
	$txt1->onkey_fn = "login_popup_enter";

	$fo->tbl(3, -1, "2%", "stdpg4");

	$fo->tr();
	$fo->th("Usuario",2);
	$fo->td();
	$txt0->render($fo->buf);

	$fo->tr();
	$fo->th("Contrase&ntilde;a",2);
	$fo->td();
	$txt1->render($fo->buf);

	$fo->tr();
	$fo->td(3,2);
	$fo->butt("Identificarse", sprintf("login_popup_chk('%s');", $laurl));
	
	$fo->trx();
	$fo->tblx();
	
	$fo->divc('', 'loginpopupmsgdiv');
	///$fo->divx();
	///$fo->divx();
}

function wlog_show($fo, $tabla, $valor)
{
	$fo->div($tabla."_wlogshowdiv");
	$q = sprintf("SELECT msg, CONCAT(utes(ltlog.creado), ' ', TIME(ltlog.creado)) AS fecha, name " .
			"FROM ltlog " .
			"LEFT JOIN usuarios AS usr ON usr.uid=ltlog.uid " .
			"WHERE tabla='%s' AND valor='%s'", $tabla, $valor);
	if (($res = mysql_query($q)) !== false)
	{
		if (mysql_num_rows($res) > 0)
		{
			$fo->tbl(3, -1, "2%", "stdpg");
			$fo->tr();
			$fo->tha(array("Usuario", "Fecha", "Operaci&oacute;n"));
			while (($ox = mysql_fetch_object($res)) !== false)
			{
				$fo->tr();
				$fo->tdc($ox->name);
				$fo->tdc($ox->fecha);
				$fo->tdc($ox->msg, 0, 0, "cursiva");
			}
			$fo->trx();
			$fo->tblx();
		}
		else $fo->parc("&laquo;No hay registro de operaciones&raquo;", 3, "nohay");
	}
	else $fo->qerr("LTLOG-1");
	$fo->divx();
}

define("MYASSOC", 1);
define("MYOBJECT",2);
define("MYROW", 3);
define('LT_INSERT', 1);
define('LT_REPLACE', 2);

define('SIN_PARENTESIS', 0);
define('ABRIR_PARENTESIS', 1);
define('CERRAR_PARENTESIS', 2);

class lt_condicion_item
{
	public $campo = '', $operador = '', $valor = '', $conjuncion = '', $negar = false;
	private $_agrupar = SIN_PARENTESIS;
	/**
	 * 
	 * Condicion para filtro
	 * @param string $campo
	 * Nombre del campo, calificado si es necesario
	 * @param string $operador
	 * Operador de comparacion
	 * @param variant $valor
	 * Valor a comparar
	 * @param string $conjuncion
	 * (opcional) Conjuncion entre campos, por defecto 'Y'
	 * @param bool $negar
	 * (opcional) Si esta en TRUE, niega la condicion
	 * @param int $agrupar
	 * (opcional) 0:ningun grupo, 1:abrir grupo, 2:cerrar grupo
	 */
	function __construct($campo, $operador, $valor, $conjuncion='', $negar=false, $agrupar=SIN_PARENTESIS)
	{
		$this->campo = $campo;
		$this->operador = $operador;
		$this->valor = $valor;
		$this->negar = $negar;
		$this->_agrupar = $agrupar;
		$this->conjuncion($conjuncion);
	}	
	public function conjuncion($conjuncion)
	{
		$conjuncion = strtoupper(trim($conjuncion));
		if ($conjuncion == 'Y' || $conjuncion == 'AND') $conjuncion = ' AND ';
		if ($conjuncion == 'O' || $conjuncion == 'OR') $conjuncion = ' OR ';
		$this->conjuncion = $conjuncion;
	}
	private function _dsval($valor, $t)
	{
		$rv = $valor;
		if ((strpos('t,h,d', $t) !== false) && gettype($valor) == 'object') $rv = $valor->to_sql();
		return $rv;
	}
	public function render(array $campos)
	{
		$cond = '';
		$cn = $this->campo;
		$ns = $this->negar ? ' NOT ' : '';
		if ($this->_agrupar == ABRIR_PARENTESIS) $cond = '(';
		if ($this->_agrupar == CERRAR_PARENTESIS) $this->conjuncion = '';
		
		$t = 'c';
		if (key_exists($cn, $campos)) $t = $campos[$cn]->t;
		if (strpos('>,<,>=,<=,=,!=', $this->operador) !== false)
		{
			if (strpos('t,h,d,c', $t) !== false) $cond .= sprintf("(%s%s%s'%s')%s", $ns, $cn, $this->operador, 
					$this->_dsval($this->valor, $t), $this->conjuncion);
			else $cond .= sprintf("(%s%s%s%s)%s", $ns, $cn, $this->operador, $this->valor, $this->conjuncion);
		}
		if ('BETWEEN' == $this->operador)
		{
			if (strpos('t,h,d,c', $t) !== false) $cond .= sprintf("(%s%s BETWEEN '%s' AND '%s')%s", 
					$ns, $cn, $this->_dsval($this->valor[0], $t), $this->_dsval($this->valor[1], $t), 
					$this->conjuncion);
			else $cond .= sprintf("(%s%s BETWEEN %s AND %s)%s", $ns, $cn, $this->valor[0], $this->valor[1], 
					$this->conjuncion);
		}
		if (strpos('REGEXP,LIKE', $this->operador) !== false)
		{
			$cond .= sprintf("(%s%s %s '%s')%s", $ns, $cn, $this->operador, $this->valor, $this->conjuncion);
		}
		if ($this->operador == 'IN')
		{
			// TODO: detectar si es array e iterar por cada elemento
			if (gettype($this->valor) == 'string')
				$cond .= sprintf("(%s %sIN (%s))%s", $cn, $ns, $this->valor, $this->conjuncion);
			if (gettype($this->valor) == 'array')
			{
				foreach ($this->valor as &$aaaaa)
				{
					if (gettype($aaaaa) == 'string') $aaaaa = sprintf("'%s'", $aaaaa);
				}
				$cond .= sprintf("(%s %sIN (%s))%s", $cn, $ns, 
						implode(',', $this->valor), $this->conjuncion);
			}
		}
		if ($this->operador == 'INSET')
		{
			// TODO: detectar si es array e iterar por cada elemento
			$cond .= sprintf("(%sFIND_IN_SET(%s, '%s'))%s", $ns, $cn, $this->valor, $this->conjuncion);
		}
		if ($this->operador == 'ISNULL')
		{
			$cond .= sprintf("(%sISNULL(%s))%s", $ns, $cn, $this->conjuncion);
		}
		if ($this->_agrupar == CERRAR_PARENTESIS) $cond .= ')';
		//error_log($cond);
		
		return $cond;
	}
}

class lt_condicion
{
	public $a = array(), $sz = 0, $ndx = -1;
	private $_agrupar = false;
	/**
	 * 
	 * Crea una nueva condicion
	 * @param string $campo
	 * Nombre del campo
	 * @param string $operador
	 * Operador a usar
	 * @param variant $valor
	 * Valor a comparar
	 * @param string $conjuncion
	 * (opcional) 'Y', 'O', por defecto 'Y'
	 * @param bool $negar
	 * (opcional) TRUE para negar la expresion
	 * @param int $agrupar
	 * (opcional) ABRIR_PARENTESIS, CERRAR_PARENTESIS, SIN_PARENTESIS
	 */
	public function add($campo, $operador, $valor, $conjuncion='', $negar=false, $agrupar=SIN_PARENTESIS)
	{
		if ($this->ndx > -1) if ($this->a[$this->ndx]->conjuncion == '') $this->a[$this->ndx]->conjuncion('Y');
		$this->a[$this->sz] = new lt_condicion_item($campo, $operador, $valor, $conjuncion, $negar, $agrupar);
		$this->ndx = $this->sz;
		$this->sz++;
	}
	public function del($index)
	{
		if ($index < $this->sz)
		{
			for ($ii=$index+1; $ii<$this->sz; $ii++)
			{ 
				$this->a[$ii-1] = $this->a[$ii];
			}
			unset($this->a[$index]);
			$this->sz--;
			$this->a[$this->sz - 1]->conjuncion = '';
			$this->ndx = 0;
			//error_log(print_r($this, true));
		}
	}
	function __construct($campo, $operador, $valor, $conjuncion='', $negar=false, $agrupar=SIN_PARENTESIS)
	{
		$this->add($campo, $operador, $valor, $conjuncion, $negar, $agrupar);
	}
	public function render(array $campos)
	{
		$cond = '';
		foreach ($this->a as $it) $cond .= $it->render($campos);
		//error_log($cond);
		return $cond;
	}
}

/**
 *
 * Campo individual de un lt_registro
 * @author carlos.caicedo
 *
 */
class lt_campo
{
	public $n = '', $t = '', $actualizar = 0, $fecha_auto = 0, $origen = '';
	public $v = '', $vd = '', $vt = '', $autovar = 0, $clave = false, $pd = 2, $is_nullable = false;
	public $ocultar = FALSE, $align = LT_ALIGN_DEFAULT, $estilo = '', $clase = '', $style = '';
	public $operacion = 0;
	private $_from = 'r', $_literal = FALSE;
	//private $_fo = NULL;
	/**
	 *
	 * Funcion constructora
	 * @param string $n
	 * Nombre del campo
	 * @param string $t
	 * Tipo del campo (i:entero, c:string, d:fecha, t:fechahora, h:hora, n:numerico)
	 * @param variant $vd
	 * Valor por defecto
	 * @param string $from
	 * Origen del valor del campo. (r: registro, p:parametros)
	 * @param string $origen
	 * (opcional) Expresion SQL que origina el valor del campo, por defecto el propio campo
	 */
	function __construct($n, $t, $vd, $from='r', $origen=false, $pd=2)
	{
		$this->n = $n;
		if (gettype($origen) == 'string') $this->origen = $origen; else $this->origen = $n;
		$this->t = $t;
		$this->vd = $this->v = $vd;
		$this->pd = $pd;
		$this->_from = $from;
		if ($from == 'r') $this->from_record($vd);
		if ($from == 'p') $this->from_form($vd);
	}
	/**
	 * 
	 * @param bool $es_literal
	 * Asigna si el campo es un literal SQL
	 */
	public function setLiteral($es_literal)
	{
		$this->_literal = $es_literal;
	}
	/**
	 * Retorna si el campo es un literal SQL
	 * @return boolean
	 */
	public function getLiteral()
	{
		return $this->_literal;
	}
	public function from_record($valor)
	{
		$this->v = $valor;
		$this->vt  = $valor;
		if ($this->t == 'n') $this->vt = nes($valor, $this->pd);
		if ($this->t == 'i') $this->v = intval($valor);
		if (strpos('d,h,t', $this->t) !== false)
		{
			$tipo = LT_FECHA_T;
			if ($this->t == 't') $tipo = LT_FECHAHORA_T;
			if ($this->t == 'h') $tipo = LT_HORA_T;
			$this->v = new lt_fecha($valor, $tipo);
			$this->vt = $this->v->to_string();
		}
		$this->actualizar = 0;
	}
	/**
	 *
	 * Asignar valor del campo a partir de los parametros
	 * @param parametros $p
	 */
	public function from_form(parametros $p)
	{
		$n = $this->n;
		if (isset($p->$n))
		{
			if ($this->t == 'c') $n = $this->n.'_e';
			$valor = $p->$n;
			$nt = $this->n.'_t';
			if ($p->$nt == 'l' || $p->$nt == 'b') $valor = $valor ? 1:0;
			$this->vt = $valor;

			if ($this->t == 'c') $this->v = $valor;
			if ($this->t == 'n') $this->v = nen($valor);
			if ($this->t == 'i') $this->v = intval($valor);
			if (strpos('d,h,t', $this->t) !== false)
			{
				$tipo = LT_FECHA_T;
				if ($this->t == 't') $tipo = LT_FECHAHORA_T;
				if ($this->t == 'h') $tipo = LT_HORA_T;
				$this->v = new lt_fecha($valor, $tipo);
				$this->vt = $this->v->to_string();
				//error_log('n='.$this->n.' v='.$this->v->to_sql().' tipo='.$tipo);
			}
			$this->actualizar = 1;
			//error_log(print_r($this, true));
		}
		//else error_log('NOEX:'.$n);
		if ($this->autovar) $this->autovar();
	}
	
	public function autovar()
	{
		$this->v = autovar($this->n, $this->v);
		$this->actualizar = 1;
	}
	/**
	 *
	 * Asignar un valor directamente
	 * @param variant $valor
	 */
	public function from_value($valor)
	{
		if ($this->t == 'i')
		{ 
			$this->v = intval($valor);
			$this->vt = $this->v;
		}
		elseif ($this->t == 'n')
		{
			$this->v = doubleval($valor);
			$this->vt = nes($this->v, $this->pd);
		}
		elseif ($this->t == 'c')
		{ 
			$this->v = mysql_real_escape_string(strval($valor));
			$this->vt = $this->v;
		}
		elseif (strpos('d,h,t', $this->t) !== false)
		{ 
			$this->v = $valor;
			$this->vt = $this->v->to_string();
		}
		else
		{
			$this->v = $valor;
			$this->vt = $this->v;
		}
		$this->actualizar = 1;
	}
	/**
	 * 
	 * Construye string de asignacion
	 */
	public function ass()
	{
		$par = '';
		if ($this->t == 'i') $par = sprintf("`%s`=%d", $this->n, $this->v);
		if ($this->t == 'n') $par = sprintf("`%s`=%f", $this->n, $this->v);
		if ($this->t == 'c') $par = sprintf("`%s`='%s'", $this->n, $this->v);
		if (strpos('d,t,h', $this->t) !== false) $par= sprintf("`%s`=FROM_UNIXTIME(%d)",
				$this->n, $this->v->timestamp);
		return $par;
	}
	/**
	 * 
	 * String para VALUES/INSERT
	 * @return string
	 */
	public function vs()
	{
		$par = '';
		if ($this->t == 'i') $par = sprintf("%d", $this->v);
		if ($this->t == 'n') $par = sprintf("%f", $this->v);
		if ($this->t == 'c') $par = sprintf("'%s'", $this->v);
		if (strpos('d,t,h', $this->t) !== false) $par= sprintf("'%s'", $this->v->to_sql());
		return $par;
	}	
}

class lt_campos
{
	public $a = array(), $sz =0, $ndx = -1, $c, $from = 'r';
	private $_nn = array();
	/**
	 * 
	 * Agregar campo a la lista
	 * @param string $nombre
	 * Nombre o alias a usar
	 * @param string $tipo
	 * Tipo de campo: 'c','t','d', etc. Por defecto 'c'
	 * @param string $valor
	 * Valor por defecto
	 * @param string $fuente
	 * (opcional) Expresion SQL a usar en la consulta
	 */
	public function add($nombre, $tipo='c', $valor='', $fuente=false, $pd=2)
	{
		if ($valor === '' || $valor === false)
		{
			switch ($tipo) {
				case 'i':  case 'n': $valor = 0; break;
				case 'd': $valor = new lt_fecha(0, LT_FECHA_T); break;
				case 'h': $valor = new lt_fecha(0, LT_HORA_T); break;
				case 't': $valor = new lt_fecha(0, LT_FECHAHORA_T); break;
			}
		}
		$this->a[$this->sz] = new lt_campo($nombre, $tipo, $valor, $this->from, $fuente == '@' ? FALSE: $fuente, $pd);
		$this->c = &$this->a[$this->sz];
		$this->_nn[$nombre] = $this->sz;
		$this->ndx = $this->sz;
		if ($fuente == '@') $this->c->setLiteral(TRUE);
		$this->sz++;
	}
	
	public function setProp($nombre, $sPropiedad, $valor)
	{
		if (isset($this->_nn[$nombre]))
		{
			$ndx = $this->_nn[$nombre];
			if (isset($this->a[$ndx]->$sPropiedad)) 
				$this->a[$ndx]->$sPropiedad = $valor; 
		}
	}
	
	/**
	 * 
	 * Asignar valor a campo especificado
	 * @param string $nombre
	 * Nombre del campo
	 * @param variant $valor
	 * Valor del campo
	 */
	public function av($nombre, $valor)
	{
		if (isset($this->_nn[$nombre]))
		{
			$ndx = $this->_nn[$nombre];
			$this->a[$ndx]->from_value($valor);
		}
	}
	
	/**
	 * 
	 * Asignar registro a campos
	 * @param stdClass $r
	 * Registro
	 */
	public function ar(stdClass $r)
	{
		$pro = get_object_vars($r);
		foreach ($pro as $n => $v)
		{
			$this->av($n, $v);
		}
	}
	/**
	 * 
	 * Agregar campo a la lista
	 * @param string $nombre
	 * Nombre o alias a usar
	 * @param string $tipo
	 * Tipo de campo: 'c','i','n','t','d','h'
	 * @param string $valor
	 * Valor por defecto
	 * @param string $fuente
	 * (opcional) Expresion SQL a usar en la consulta
	 */
	function __construct($nombre, $tipo='c', $valor='', $fuente=false, $from='r', $pd=2)
	{
		$this->from = $from;
		$this->add($nombre, $tipo, $valor, $fuente, $pd);
	}
	/**
	 * 
	 * Construye cadena de actualizacion
	 */
	public function upset()
	{
		$vs = '';
		$i = 0;
		foreach ($this->a as $fl)
		{ 
			$vs .= sprintf("%s%s", $i > 0 ? ',':'', $fl->ass());
			$i++;
		}
		return $vs;
	}
	public function lista()
	{
		$lista = '';
		foreach ($this->a as $fl) $lista .= ','.$fl->n;
		if ($lista != '') $lista = substr($lista, 1);
		return $lista;
	}
	public function vs()
	{
		$vs = '';
		foreach ($this->a as $fl)
		{
			$vs .= ','.$fl->vs();
		}
		if ($vs != '') $vs = sprintf("(%s)", substr($vs, 1));
		return $vs;
	}
}

define('LTJOIN_LEFT', 1);
define('LTJOIN_RIGHT', 2);
define('LTJOIN_INNER', 3);
class lt_join
{
	private $alias_l, $tabla_r, $tipo, $campos_r, $campos_l, $alias_r;
	/**
	 * 
	 * Enter description here ...
	 * @param const $tipo LTJOIN_*
	 * @param String_type $alias_l tabla a unirse con (izquierda)
	 * @param String $tabla_r tabla que vas a unir (derecha)
	 * @param String $alias_r alias de la tabla con que se une (derecha)
	 * @param String $campos_l campo de comparacion (izquierda)
	 * @param String $campos_r campo de comparacion (derecha)
	 */
	function __construct($tipo, $alias_l, $tabla_r, $alias_r, $campos_l, $campos_r=false)
	{
		if (gettype($campos_r) == 'boolean') $campos_r = $campos_l;
		$this->tipo = $tipo;
		$this->alias_l = $alias_l;
		$this->tabla_r = $tabla_r;
		$this->alias_r = $alias_r;
		$this->campos_l = $campos_l;
		$this->campos_r = $campos_r;
	}
	public function s()
	{
		switch ($this->tipo)
		{
			case LTJOIN_LEFT: $tj = 'LEFT'; break;
			case LTJOIN_RIGHT: $tj = 'RIGHT'; break;
			case LTJOIN_INNER: $tj = 'INNER'; break;
		}
		$ons = '';
		if (gettype($this->campos_l) == 'string')
			$ons = sprintf("%s.%s=%s.%s", $this->alias_l, $this->campos_l, $this->alias_r, $this->campos_r);
		if (gettype($this->campos_l) == 'array')
		{
			$nc = count($this->campos_l);
			for ($i = 0; $i < $nc; $i++)
			{
				$suf = ($i == 0) ? '':' AND ';
				$ons .= sprintf("%s%s.%s=%s.%s", $suf, $this->alias_l, $this->campos_l[$i],
					$this->alias_r, $this->campos_r[$i]);
			}
		}
		
		return sprintf(" %s JOIN %s AS %s ON %s", $tj, $this->tabla_r, $this->alias_r, $ons);
	}
}

class lt_joins
{
	protected $a = array(), $sz = 0;
	private $last_alias = 98;
	function __get($property)
	{
		if ($property == 'a') return $this->a;
		if ($property == 'sz') return $this->sz;
	}
	function __construct($tipo, $alias_l, $tabla_r, $campos_l, $campos_r=false)
	{
		$this->_add($tipo, $alias_l, $tabla_r, $campos_l, $campos_r);
	}
	private function _add($tipo, $alias_l, $tabla_r, $campos_l, $campos_r=false)
	{
		$alias_r = chr($this->last_alias++);
		$this->a[$this->sz++] = new lt_join($tipo, $alias_l, $tabla_r, $alias_r, $campos_l, $campos_r);
	}
	public function left($alias_l, $tabla_r, $campos_l, $campos_r=false)
	{
		$this->_add(LTJOIN_LEFT, $alias_l, $tabla_r, $campos_l, $campos_r);
	}
	public function right($alias_l, $tabla_r, $campos_l, $campos_r=false)
	{
		$this->_add(LTJOIN_RIGHT, $alias_l, $tabla_r, $campos_l, $campos_r);
	}
	public function inner($alias_l, $tabla_r, $campos_l, $campos_r=false)
	{
		$this->_add(LTJOIN_INNER, $alias_l, $tabla_r, $campos_l, $campos_r);
	}
}

/**
 *
 * Clase que encapsula una operacion de consulta SQL/mysql
 * @author carlos.caicedo
 *
 */
class myquery
{
	/**
	 *
	 * Miembros publicos de la clase
	 * @var variant $r
	 * Registro actual
	 * @var integer $sz
	 * Cantidad de registros devueltos en una consulta o afectados en una modificacion
	 * @var array $a
	 * Array de registros si es consulta
	 * @var boolean $isok
	 * Indica si el query fue exitoso
	 * @var string $q
	 * Guarda el texto del query ejecutado
	 * @var integer $id
	 * Guarda el ID de registro autogenerado en caso de ejecutar un INSERT en una tabla con campo clave AUTOINCREMENT
	 */
	public $r = false, $sz = 0, $asz = 0, $a = array(), $isok = false;
	public $q = '', $id = 0, $hayerror = false, $errno = 0;
	public $campos = array(), $campos_c = 0, $campos_q = array();
	private $_formato = MYOBJECT, $_have_join = false, $_join, $_fo, $_dbhandler, $_verbose = LT_VERBOSE_DEBUG;
	private $_inmediato = true, $_vacio_no_aceptado = true, $_altera_datos = false, $_pista = '', $_error_string = '';
	private $_acciones = FALSE;
	/**
	 *
	 * Ejecutar query SQL
	 * @param lt_form $fo
	 * @param string $consulta
	 * Consulta SQL
	 * @param string $pista_de_error
	 * Mensaje de ubicacion del error
	 * @param boolean $vacio_no_aceptado
	 * (opcional) Indica si retorna falla si obtiene un conjunto vacio o no afecta registros, por defecto TRUE
	 * @param boolean $altera_datos
	 * (opcional) Indica si es una consulta (FALSE), o una modificacion (TRUE), por defecto FALSE
	 * @param integer $tipo_de_retorno
	 * (opcional) MYOBJECT (por defecto) retorna un array de objetos, MYASSOC retorna un array asociativo, MYROW retorna un array numerico
	 * @param bool $inmediato
	 * (opcional) Indica si se ejecuta inmediatamente el query
	 * @param int $verbosidad
	 * (opcional) Indica si muestra mensaje de error (2=por pantalla, 1=solo errorlog, 0=silencioso)
	 */
	function __construct(lt_form $fo, $consulta, $pista_de_error="", $vacio_no_aceptado=true,
			$altera_datos=false, $tipo_de_retorno=MYOBJECT, $inmediato=true, $verbosidad=LT_VERBOSE_DEBUG)
	{
		$this->_fo = $fo;
		$this->r = false;
		$this->a = array();
		$this->asz = 0;
		$this->q = $consulta;
		$this->_formato = $tipo_de_retorno;
		$this->_inmediato = $inmediato;
		$this->_vacio_no_aceptado = $vacio_no_aceptado;
		$this->_altera_datos = $altera_datos;
		$this->_pista = $pista_de_error;
		$this->_dbhandler = $fo->get_dbhandler();
		$this->_verbose = $verbosidad;
		if ($this->_inmediato) $this->exec(); else $this->isok = TRUE;
	}

	public final function exec()
	{
		if ($this->q == '') $this->_errmsg('Consulta vacia');
		if (($res = mysql_query($this->q)) !== false)
		{
			if ($this->_altera_datos)
			{
				$this->sz = mysql_affected_rows();
				$this->id = mysql_insert_id();
			}
			else
			{
				$this->sz = mysql_num_rows($res);
				if ($this->_formato == MYOBJECT)
				{
					while (($otmp = mysql_fetch_object($res)) !== false)
					{
						$this->a[$this->asz] = clone $otmp;
						$this->r = $this->a[$this->asz];
						$this->asz++;
					}
				}
				if ($this->_formato == MYASSOC)
				{
					while (($row = mysql_fetch_assoc($res)) !== false)
					{
						$this->a[$this->asz] = $row;
						$this->r = $this->a[$this->asz];
						$this->asz++;
					}
				}
				if ($this->_formato == MYROW)
				{
					while (($row = mysql_fetch_row($res)) !== false)
					{
						$this->a[$this->asz] = $row;
						$this->r = $this->a[$this->asz];
						$this->asz++;
					}
				}
			}
			if ($this->_vacio_no_aceptado) $this->isok = $this->sz > 0; else $this->isok = true;
			if (!$this->_altera_datos) mysql_free_result($res);
		}
		else
		{
			$this->hayerror = true;
			if ($this->_verbose > LT_VERBOSE_MUTE)
			{
				$this->errno = mysql_errno($this->_dbhandler);
				$this->_errmsg('Error en consulta', mysql_error($this->_dbhandler));
			}
		}
	}
	
	/**
	 * 
	 * Ejecuta una consulta SQL
	 * @param lt_form $fo
	 * Contexto
	 * @param string $consulta
	 * Codigo SQL a ejecutar
	 * @param string $pista_de_error
	 * Pista de depuracion en caso de error
	 * @param bool $vacio_no_aceptado
	 * Indica si retorna FALSE en caso de que la consulta devuelva un conjunto vacio, por defecto TRUE
	 * @param bool $altera_datos
	 * Indica si la consulta modifica tablas
	 * @param int $tipo_de_retorno
	 * Tipo de resultado a devolver, por defecto MYOBJECT (array de objetos) 
	 * @return myquery|boolean
	 * Devuelve un objecto myquery si la consulta es exitosa, sino FALSE
	 */
	public static function q(lt_form $fo, $consulta, $pista_de_error="", $vacio_no_aceptado=TRUE,
			$altera_datos=FALSE, $tipo_de_retorno=MYOBJECT)
	{
		$tmpqq = new self($fo, $consulta, $pista_de_error, $vacio_no_aceptado, $altera_datos, $tipo_de_retorno);
		if ($tmpqq->isok) return $tmpqq; else return false;
	}

	private static function _build_condicion($condicion, array $campos=array(), array $campos_src=array(), $tipo='WHERE')
	{
		$co = '';
		if (gettype($condicion) == 'string') $co = $condicion;
		if (gettype($condicion) == 'array')
		{
			$cn = $condicion[0];
			if (key_exists($cn, $campos)) $t = $campos[$cn]->t; else $t = 'c';
			if (strpos('t,h,d,c', $t) !== false)
				$co = sprintf("%s %s '%s'", $cn, $condicion[1], $condicion[2]);
			else $co = sprintf("%s %s %s", $cn, $condicion[1], $condicion[2]);
		}
		if (gettype($condicion) == 'object') $co = $condicion->render($campos_src);
		if ($co !== '') $co = sprintf(" %s %s ", $tipo, $co);
		return $co;
	}
	
	/**
	 * 
	 * Consulta una tabla de acuerdo a los parametros
	 * @param lt_form $fo
	 * @param string $tabla
	 * Nombre de la tabla a consultar
	 * @param int $formato
	 * (opcional) Formato del array a retornar. MYOBJECT (array numerico) por defecto
	 * @param bool $presentacion
	 * (opcional) Indica si los datos retornados seran formateados para presentacion en pantalla
	 * @param string $condicion
	 * (opcional) Condicion personalizada en el WHERE
	 * @param variant $campos
	 * (opcional) Lista de campos a retornar. La lista puede ser un array o un string 
	 * @param variant $ordenar
	 * (opcional) Lista de campos para ordenar la consulta. La lista puede ser un array o un string
	 * @param variant $agrupar
	 * (opcional) Lista de campos para agrupar la consulta. La lista puede ser un array o un string
	 * @param variant $limites
	 * (opcional) Cantidad de registros a retornar. Puede ser un array o un string
	 * @param string $bd
	 * (opcional) Nombre de la base de datos
	 * @param variant $joins
	 * (opcional) Objeto lt_joins o FALSE si no consulta no tiene joins.
	 * @param boolean $inmediato
	 * (opcional) Indica si la consulta se ejecuta de inmediato, por defecto TRUE
	 * @param variant $having
	 * (opcional) Clausula HAVING de la consulta, por defecto FALSE
	 * @return myquery
	 * Retorna un objeto myquery con recordset tipo MYOBJECT o false si falla la consulta
	 */
	public static function tabla(lt_form $fo, $tabla, $formato=MYOBJECT, $presentacion=true, $condicion='',
			$campos=false, $ordenar=false, $agrupar=false, $limites=false, $bd=FALSE, $joins=false, 
			$inmediato=TRUE, $having=FALSE)
	{
		if (($r = lt_registro::estructura($fo, $tabla, $bd)) === FALSE)
		{
			$fo->err('TABLA:'.$tabla.':Error leyendo estructura');
			return false;
		}
		
		$have_join = false;
		if (gettype($joins) == 'object')
		{
			if (get_class($joins) == 'lt_joins') $have_join = true;
		}
		
		$have_having = false;
		if (gettype($having) == 'object')
		{
			if (get_class($having) == 'lt_condicion') $have_having = true;
		}
		elseif (gettype($having) == 'string') $have_having = true;
		
		if ($have_having || $have_join)
		{
			if (gettype($campos) == 'boolean')
			{
				$fo->err('TABLA:'.$tabla.': Hay JOIN o HAVING, debe especificar campos');
				return false;
			}
		}
		
		$ncp = 0;
		$cpndx = array();
		$xcampos = $r->campos;
		
		if (gettype($campos) == 'array')
		{
			if (count($campos) > 0)
			{
				$xcp = 0;
				$xcampos = array();
				foreach ($campos as $co)
				{
					if (gettype($co) == 'array')
					{
						$cn = $co[0];
						if (array_key_exists($cn, $r->campos))
						{
							$xcampos[$cn] = clone $r->campos[$cn]; 
						}
						else
						{
							$xcampos[$cn] = new lt_campo($cn, 'c', '');
						}
						if (isset($co[1])) $xcampos[$cn]->t = $co[1];
						if (isset($co[2])) $xcampos[$cn]->origen = $co[2];
						$xcp++;
					}
					if (gettype($co) == 'string')
					{
						if (array_key_exists($co, $r->campos)) $xcampos[$co] = clone $r->campos[$co];
						else $xcampos[$cn] = new lt_campo($co, 'c', ''); 
					}
				}
			}
		}
		
		if (gettype($campos) == 'string')
		{
			if ($campos !== '')
			{
				$xcampos = array();
				$sa = explode(',', $campos);
				foreach ($sa as $sp)
				{
					if (key_exists($sp, $r->campos) !== false) $xcampos[$sp] = clone $r->campos[$sp];
				}
			}
		}

		if (gettype($campos) == 'object')
		{
			$xcampos = array();
			foreach ($campos->a as $fl) $xcampos[$fl->n] = clone $fl; 
		}
		
		// construir lista de campos
		$sc = '';
		$ncp = 0;
		$cpndx = array();
		//if ($fo->uid == 1) error_log(print_r($xcampos, true));
		if ($presentacion)
		{
			foreach ($xcampos as $cp)
			{
				if ($cp->getLiteral())
				{
					// TODO: formato segun tipo
					$sc .= sprintf(",'%s' AS %s", $cp->v, $cp->n);
				}
				else
				{
					$als = $cp->origen === $cp->n ? '': sprintf(" AS %s", $cp->n);
					if ($cp->t == 'd') $sc .= sprintf(",utes(%s) AS %s", $cp->origen, $cp->n);
					elseif ($cp->t == 't') $sc .= sprintf(",CONCAT(utes(%s),' ',TIME(%s)) AS %s",
						$cp->origen, $cp->origen, $cp->n);
					else $sc .= sprintf(",%s%s", $cp->origen, $als);
				}
				$cpndx[$cp->n] = $ncp++;
			}
		}
		else
		{
			foreach ($xcampos as $cp)
			{
				if ($cp->getLiteral())
				{
					// TODO: formato segun tipo
					$sc .= sprintf(",'%s' AS %s", $cp->v, $cp->n);
				}
				else
				{
					$als = $cp->origen === $cp->n ? '': sprintf(" AS %s", $cp->n);
					if ($cp->t == 'd') $sc .= sprintf(",utes(%s) AS %s", $cp->origen, $cp->n); 
					elseif ($cp->t == 't') $sc .= sprintf(",UNIX_TIMESTAMP(%s) AS %s", $cp->origen, $cp->n);
					else $sc .= sprintf(",%s%s", $cp->origen, $als);
				}
				$cpndx[$cp->n] = $ncp++;
			}
		}
		
		$so = $sg = $sl = '';
		
		// condicion general
		$co = self::_build_condicion($condicion, $r->campos, $r->campos);
		
		// condicion having
		$hv = self::_build_condicion($having, $r->campos, $r->campos, 'HAVING');
		
		// agrupacion
		if (gettype($agrupar) == 'array')
		{
			if (count($agrupar) > 0)
			{
				$sg = ' GROUP BY '.implode(',', $agrupar);
			}
		}
		if (gettype($agrupar) == 'string')
		{
			if ($agrupar !== '') $sg = ' GROUP BY '.$agrupar;
		}
		
		// ordenacion
		if (gettype($ordenar) == 'array')
		{ 
			if (count($ordenar) > 0)
			{ 
				$sor = '';
				foreach ($ordenar as $ord)
				{
					if (gettype($ord) == 'array')
					{
						 $sor .= sprintf(",%s %s", $ord[0], isset($ord[1]) ? $ord[1]: '');
					}
					if (gettype($ord) == 'string') $sor .= sprintf(",%s", $ord);
				}
				$so = ' ORDER BY '.substr($sor, 1);
			}
		}
		if (gettype($ordenar) == 'string')
		{ 
			if ($ordenar !== '') $so = ' ORDER BY '.$ordenar;
		}
		
		// limites
		if (gettype($limites) == 'array')
		{ 
			if (count($limites) > 1) $sl = sprintf(" LIMIT ", $limites[0], $limites[1]);
		}
		if (gettype($limites) == 'string')
		{
			if ($limites !== '') $sl = sprintf(" LIMIT %s", $limites);
		}
		
		$sj = '';
		$ala = '';
		if ($have_join)
		{
			$ala = ' AS a';
			foreach ($joins->a as $jn) $sj .= $jn->s();
		}
		
		$qq = new self($fo, sprintf("SELECT %s FROM %s%s%s %s%s%s%s%s%s", substr($sc, 1), $r->sbd, $tabla, $ala, 
			$sj, $co, $sg, $hv, $so, $sl), 'SELECT:' . $tabla, true, false, $formato, $inmediato);

		$qq->campos = $r->campos;
		$qq->campos_c = $r->campos_c;
		$qq->campos_q = $xcampos;
		if ($fo->verbose_level >= LT_VERBOSE_DEBUG) error_log($qq->q);
		if ($qq->isok)
		{
			if (!$inmediato) return $qq;
			
			if ($presentacion)
			{
				foreach ($xcampos as $cp)
				{
					if ($cp->t == 'n')
					{
						$cn = $cp->n;
						$ndx = $cpndx[$cn];
						for($i = 0; $i < $qq->sz; $i ++)
						{
							if ($formato == MYROW) $qq->a[$i][$ndx] = nes($qq->a[$i][$ndx]);
							if ($formato == MYOBJECT) $qq->a[$i]->$cn = nes($qq->a[$i]->$cn);
							if ($formato == MYASSOC) $qq->a[$i][$cn] = nes($qq->a[$i][$cn]);
						}
					}
				}
			}
			else
			{
				foreach ($xcampos as $cp)
				{
					if ($cp->t == 'd' || $cp->t == 'h')
					{
						if ($cp->t == 'd') $dtp = LT_FECHA_T;
						if ($cp->t == 'h') $dtp = LT_HORA_T;
						$cn = $cp->n;
						$ndx = $cpndx[$cn];
						for($i = 0; $i < $qq->sz; $i ++)
						{
							//error_log('T='.$qq->a[$i]->$cn);
							if ($formato == MYROW) $qq->a[$i][$ndx] = new lt_fecha($qq->a[$i][$ndx], $dtp);
							if ($formato == MYOBJECT) $qq->a[$i]->$cn = new lt_fecha($qq->a[$i]->$cn, $dtp);
							if ($formato == MYASSOC) $qq->a[$i][$cn] = new lt_fecha($qq->a[$i][$cn], $dtp);
						}
					}
					if ($cp->t == 't')
					{
						if ($cp->t == 't') $dtp = LT_FECHAHORA_T;
						$cn = $cp->n;
						$ndx = $cpndx[$cn];
						for($i = 0; $i < $qq->sz; $i ++)
						{
							//error_log('T='.$qq->a[$i]->$cn);
							if ($formato == MYROW) $qq->a[$i][$ndx] = new lt_fecha(intval($qq->a[$i][$ndx]), $dtp);
							if ($formato == MYOBJECT) $qq->a[$i]->$cn = new lt_fecha(intval($qq->a[$i]->$cn), $dtp);
							if ($formato == MYASSOC) $qq->a[$i][$cn] = new lt_fecha(intval($qq->a[$i][$cn]), $dtp);
						}
					}
				}
			}
			return $qq;
		}
		return false;
	}
	
	/**
	 * 
	 * Consulta una tabla
	 * @param lt_form $fo
	 * @param string $tabla
	 * Nombre de la tabla a consultar
	 * @param variant $condicion
	 * (opcional) Condicion personalizada en el WHERE. Un string SQL o un objeto lt_condicion.
	 * @param variant $campos
	 * (opcional) Lista de campos a retornar. La lista puede ser un array o un string 
	 * @param lt_joins $joins
	 * (opcional) Indica los joins a ejecutar
	 * @param variant $ordenar
	 * (opcional) Lista de campos para ordenar la consulta. La lista puede ser un array o un string
	 * @param variant $agrupar
	 * (opcional) Lista de campos para agrupar la consulta. La lista puede ser un array o un string
	 * @param variant $limites
	 * (opcional) Cantidad de registros a retornar. Puede ser un array o un string
	 * @param int $formato  
	 * (opcional) Por defecto MYOBJECT
	 * @param string $bd
	 * (opcional) Nombre de la db, por defecto FALSE (usar $DEFAULT_SCHEMA)
	 * @param variant $having
	 * (opcional) Clausula HAVING de la consulta
	 * @return myquery
	 * Retorna un objeto myquery con recordset tipo MYOBJECT o false si falla la consulta
	 */
	public static function t(lt_form $fo, $tabla, $condicion='', $campos=false, $joins=false, $ordenar=false, 
			$agrupar=false, $limites=false, $formato=MYOBJECT, $bd=FALSE, $having=FALSE)
	{
		return self::tabla($fo, $tabla, $formato, false, $condicion, $campos, $ordenar, $agrupar, 
				$limites, $bd, $joins, TRUE, $having);
	}

	public static function bqt(lt_form $fo, $tabla, $condicion='', $campos=false, $joins=false, $ordenar=false, 
			$agrupar=false, $limites=false, $bd=FALSE, $having=FALSE)
	{
		$tmpt = self::tabla($fo, $tabla, MYOBJECT, false, $condicion, $campos, $ordenar, $agrupar, 
				$limites, $bd, $joins, FALSE, $having);
		return $tmpt;
	}
		
	/**
	 * 
	 * Borrar un conjunto de registros
	 * @param lt_form $fo
	 * @param string $tabla
	 * Nombre de la tabla
	 * @param variant $condicion
	 * Objecto LT_CONDICION o string SQL
	 * @param string $bd
	 * (opcional) Base de datos
	 */
	public static function d(lt_form $fo, $tabla, $condicion, $bd=FALSE)
	{
		if (($r = lt_registro::estructura($fo, $tabla, $bd)))
		{
			$co = self::_build_condicion($condicion, $r->campos, $r->campos);
			$consulta = sprintf("DELETE FROM %s%s %s", $r->sbd, $tabla, $co);
			//error_log($consulta);
			return self::q($fo, $consulta, 'DELETE:'.$tabla, false, true);
		}
		else $fo->err('DELETE:'.$tabla.":No puede leer estructura");
		return false;
	}

	/**
	 * 
	 * Ejecuta un SQL UPDATE
	 * @param lt_form $fo
	 * @param string $tabla
	 * @param lt_campos $update_set
	 * @param lt_condicion $condicion
	 * @return variant <boolean, myquery>
	 */
	public static function u(lt_form $fo, $tabla, lt_campos $update_set, $condicion, $bd=FALSE)
	{
		if (($r = lt_registro::estructura($fo, $tabla, $bd)))
		{
			$co = self::_build_condicion($condicion, $r->campos, $r->campos);
			$consulta = sprintf("UPDATE %s%s SET %s %s", $r->sbd, $tabla, $update_set->upset(), $co);
			if ($fo->verbose_level >= LT_VERBOSE_DEBUG) error_log($consulta);
			return self::q($fo, $consulta, 'UPDATE:'.$tabla, false, true);
		}
		else $fo->err('DELETE:'.$tabla.":No puede leer estructura");
		return false;
	}

	/**
	 * 
	 * Ejecuta un SQL INSERT o REPLACE
	 * @param lt_form $fo
	 * @param string $tabla
	 * Nombre de la tabla
	 * @param variant $insert_set
	 * Objeto LT_CAMPOS si es un solo registro, array de arrays para multiples registros o string
	 * @param int $tipo
	 * (opcional) Indica si es un INSERT o REPLACE. Por defecto, LT_INSERT
	 * @param variant $campos
	 * (opcional) Lista separada por comas o array de los campos a insertar 
	 * @param string $bd
	 * (opcional) Nombre de la base de datos, por defecto FALSE -> DEFAULT_SCHEMA
	 * @return variant <boolean, myquery>
	 * Retorna un objeto MYQUERY si fue exitoso, o FALSE si falla
	 */
	public static function i(lt_form $fo, $tabla, $insert_set, $tipo=LT_INSERT, $campos=FALSE, $bd=FALSE)
	{
		$ni = 0;
		$lista_campos = '';
		$es = lt_registro::estructura($fo, $tabla, $bd);
		if (gettype($insert_set) == 'array')
		{
			$vs = '';
			foreach ($insert_set as $it)
			{
				if (gettype($it) == 'array')
				{
					$vvs = '';
					foreach ($it as $v)
					{
						$vvs .= sprintf("%s'%s'", $vvs == '' ? '': ',', $v);
					}
					$vs .= sprintf(",(%s)", $vvs);
					$ni++;
				}
				else $vs .= sprintf(",'%s'", $it);
			}
			if ($ni == 0) $vs = sprintf("(%s)", substr($vs, 1)); else $vs = substr($vs, 1);
		}
		if (gettype($insert_set) == 'object')
		{
			$lista_campos = sprintf(" (%s) ", $insert_set->lista());
			$vs = $insert_set->vs();
		}
		if (gettype($insert_set) == 'string')
		{
			if ($insert_set[0] == '(') $vs = $insert_set; else $vs = sprintf("(%s)", $insert_set);
		}
		$cmd = $tipo == LT_INSERT ? 'INSERT':'REPLACE';
		if (gettype($campos) == 'string') $lista_campos = '('.$campos.')';
		if (gettype($campos) == 'array') $lista_campos = '('.implode(',', $campos).')';
		$consulta = sprintf("%s INTO %s%s%s VALUES %s", $cmd, $es->sbd, $tabla, $lista_campos, $vs);
		//error_log($consulta);
		return self::q($fo, $consulta, $cmd.':'.$tabla, false, true);
	}
	/**
	 * 
	 * Ejecuta un SQL REPLACE
	 * @param lt_form $fo
	 * @param string $tabla
	 * Nombre de la tabla
	 * @param variant $replace_set
	 * LT_CAMPOS si es un registro, array de arrays para multiples registros, o string
	 * @return Ambigous <variant, boolean, myquery>
	 */
	public static function r(lt_form $fo, $tabla, $replace_set, $bd=FALSE)
	{
		return self::i($fo, $tabla, $replace_set, $bd, LT_REPLACE);
	}

	/**
	 *
	 * Devuelve un array para usar como rowsource de un lt_listbox
	 * @param array $exset
	 * (opcional) Posiciones a descartar en la lista
	 * @return array
	 * Array numerico de dimensiones N,2
	 */
	public function toListbox($exset=array())
	{
		$als = array();
		$nls = 0;
		foreach ($this->a as $row)
		{
			if ($this->_formato == MYASSOC) $fila = array_values($row);
			if ($this->_formato == MYOBJECT) $fila = array_values(get_object_vars($row));
			if ($this->_formato == MYROW) $fila = &$row;
			$als[$nls][0] = $fila[0];
			unset($fila[0]);
			foreach ($exset as $ex) unset($fila[$ex]);
			$als[$nls][1] = implode(' ', $fila);
			$nls++;
		}
		return $als;
	}
	
	/**
	 *
	 * Crea una variable para usar con lt_listbox_fromarray()
	 * @param string $nombre
	 * (opcional) Prefijo con que se nombraran los elementos
	 * @param variant $variable
	 * (opcional) Otra variable donde poner el resultado, por defecto lo pone en lt_form asociado al query
	 * @param array $exclude_set
	 * (opcional) Posiciones a excluir, por defecto, ninguna
	 */
	public function toRowSourceAjax($nombre='lista', $variable=false, array $exclude_set=array())
	{
		$als = array();
		$nls = 0;
	
		if (gettype($variable) == 'boolean') $variable = &$this->_fo->re;
		if (!isset($variable[$nombre])) $variable[$nombre] = new stdClass();
		$variable[$nombre]->sz = $this->sz;
	
		foreach ($this->a as $row)
		{
			if ($this->_formato == MYASSOC) $fila = array_values($row);
			if ($this->_formato == MYOBJECT) $fila = array_values(get_object_vars($row));
			if ($this->_formato == MYROW) $fila = &$row;
			$als[$nls]['v'] = $fila[0];
			unset($fila[0]);
			foreach ($exclude_set as $pos) unset($fila[$pos]);
			$als[$nls]['ds'] = implode(' ', $fila);
			$nls++;
		}
		$variable[$nombre]->a = $als;
	}
	
	/**
	 * 
	 * Muestra un lt_ctrl_set::box con el contenido del query 
	 * @param string $tipo
	 * (opcional) Por defecto, LT_INFOBOX_HORIZONTAL
	 * @param number $columnas
	 * (opcional) Numero de pares de columnas si $tipo es LT_INFOBOX_VERTICAL
	 * @param string $clasecss
	 * (opcional) Clase CSS a aplicar
	 * @param string $con_etiquetas
	 * (opcional) Indica si se muestran etiquetas de cada campo
	 */
	public function box($tipo=LT_INFOBOX_HORIZONTAL, $columnas=1, 
		$clasecss=LT_TABLE_CLASS_DEFAULT, $con_etiquetas=true)
	{
		lt_ctrl_set::qbox($this->_fo, $this, $tipo, $columnas, $clasecss, $con_etiquetas);
	}
	
	public function setAcciones($acciones)
	{
		$this->_acciones = $acciones;
	}
	
	public function acciones($r)
	{
		return $this->_acciones->s($r);
	}
	
	public function tieneAcciones()
	{
		$tiene = FALSE;
		if (gettype($this->_acciones) == 'object')
		{
			if (get_class($this->_acciones) == 'lt_acciones') $tiene = TRUE;
		}
		return $tiene;
	}
		
	/**
	 * 
	 * Muestra el contenido del query en un solo renglon
	 */
	public function renglon()
	{
		$autocierre = $this->_fo->tbl_getAutoCierre();
		$this->_fo->tbl_setAutoCierre(FALSE);
		$this->_fo->tbl();
		foreach ($this->a as $rg)
		{
			foreach ($this->campos_q as $fl)
			{
				$al = LT_ALIGN_CENTER;
				if ($fl->t == 'n' || $fl->t == 'i') $al = LT_ALIGN_LEFT;
				$cn = $fl->n;
				$this->_fo->tdc($rg->$cn, $al);
			}
		}
		$this->_fo->tblx();
		$this->_fo->tbl_setAutoCierre($autocierre);
	}
		
	/**
	 * 
	 * Guarda el resultado del query en un archivo Excel
	 * @param string $fn
	 * Nombre de archivo
	 * @param string $titulo
	 * Titulo de la hoja de calculo
	 */
	public function saveExcel($fn, $titulo)
	{
		$isok = false;
		
		include 'Classes/PHPExcel.php';
		include 'Classes/PHPExcel/Writer/Excel5.php';
		
		try
		{
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator(SOFTNAME);
			$objPHPExcel->getProperties()->setLastModifiedBy(SOFTNAME);
			$objPHPExcel->getProperties()->setTitle($titulo);
			$objPHPExcel->getProperties()->setSubject($titulo);
			$objPHPExcel->getProperties()->setDescription($titulo);
			$objPHPExcel->setActiveSheetIndex(0);
		
			// encabezados
			$nletra0 = 65;
			$nletra1 = 64;
			$xcp = array();
			foreach ($this->campos_q as $cp)
			{
				if ($nletra1 > 64) $letra = sprintf("%s%s", chr($nletra1), chr($nletra0));
				else $letra = chr($nletra0);
				$xcp[] = array('l'=>$letra, 'n'=>$cp->n, 't'=>$cp->t);
				$objPHPExcel->getActiveSheet()->SetCellValue($letra.'1', $cp->n);
				$nletra0++;
				if ($nletra0 > 90)
				{
					$nletra0 = 65;
					$nletra1++;
				}
			}
			//error_log(print_r($xcp));
		
			// agregar registros
			$xn = 2;
			foreach ($this->a as $rg)
			{
				foreach ($xcp as $xc)
				{
					$cn = $xc['n'];
					$xref = sprintf("%s%d", $xc['l'], $xn);
					error_log($xref.'='.$xc['t']);
					$vv = $rg->$cn;
					if (strpos('d,t,h', $xc['t']) !== false) $vv = $rg->$cn->s();
					$objPHPExcel->getActiveSheet()->SetCellValue($xref, $vv);
				}
				$xn++;
			}
		
			// save
			$objPHPExcel->getActiveSheet()->setTitle($titulo);
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save($fn);
			
			$isok = true;
		}
		catch (Exception $ex)
		{
			$this->_fo->warn($ex->getMessage());
		}
		
		return $isok;
	}
	
	public static function start(){
		mysql_query("SET autocommit=0");
		mysql_query('START TRANSACTION');
	}
	
	public static function end($isok)
	{
		if($isok) mysql_query('COMMIT');
		else mysql_query('ROLLBACK');
		mysql_query("SET autocommit=1");
	}

	private final function _errmsg($sext, $sint=false)
	{
		if ($sint === false) $sint = $sext;
		$this->_error_string = $sint;
		if ($this->_verbose >= LT_VERBOSE_ERROR) error_log($this->_pista.':'.$sint);
		if ($this->_verbose >= LT_VERBOSE_DEBUG) $this->_fo->warn($this->_pista.':'.$sext);
	}
	public function error()
	{
		return $this->_error_string;
	}
}

define('LT_DETECTAR', 'D');
/**
 *
 * Ejecuta operaciones CRUD con un solo registro de una tabla
 * @author carlos.caicedo
 *
 */
class lt_registro
{
	public $isok = false, $id = 0, $usar_transaccion = false, $nuevo = false;
	public $campos_c = 0, $campos = array(), $actualizar_todo = false, $bd = DEFAULT_SCHEMA, $sbd = '';
	public $tabla = '', $valor = array(), $v, $condicion = '', $linea = -1, $upset = '';
	private $fo, $pkc = 0, $custom_cond = false, $_autovar = true, $_autovar_set, $_campo_linea = 'linea';
	private function _begin()
	{
		mysql_query("SET autocommit=0");
		mysql_query('START TRANSACTION');
	}
	private function _commit()
	{
		return mysql_query('COMMIT');
	}
	private function _rollback()
	{
		return mysql_query('ROLLBACK');
	}
	private function _end()
	{
		if ($this->isok) $this->commit(); else $this->rollback();
		mysql_query("SET autocommit=1");
	}
	/**
	 *
	 * Constructor de la clase
	 * @param lt_form $fo
	 * @param string $tabla
	 * Nombre de la tabla
	 * @param array|variant $valor
	 * Valor del campo clave. Cero si es nuevo. Array si es clave multiple.
	 * @param variant $nuevo
	 * (opcional) Indica si es un nuevo registro o ya existe en la tabla. Por defecto FALSE. 
	 * LT_DETECTAR si desea detectar si es nuevo.
	 * @param $condicion
	 * (opcional) Condicion personalizada
	 * @param string $bd
	 * (opcional) Nombre de la base de datos a la que pertenece la tabla
	 * @param variant $autovar_ex
	 * (opcional) Lista o array de campos autovar a excluir, por defecto FALSE (no excluir ninguno)
	 * @param string $campo_linea
	 * (opcional) Campo de ordenacion en detalle, por defecto 'linea'
	 */
	function __construct(lt_form $fo, $tabla, $valor, $nuevo=false, $condicion=false, 
			$bd=false, $autovar_ex=false, $campo_linea='linea')
	{
		$this->isok = false;
		
		$this->_autovar_set = array('uid'=>true, 'creado'=>true, 'modificado'=>true, 'ipaddr'=>true);
		$autovarexa = array();
		if (gettype($autovar_ex) == 'string') $autovarexa = explode(',', $autovar_ex);
		if (gettype($autovar_ex) == 'array') $autovarexa = $autovar_ex;
		foreach ($autovarexa as $ex) $this->_autovar_set[$ex] = false;
		
		$this->v = new stdClass();
		$this->tabla = $tabla;
		if (gettype($valor) == 'array') $this->valor = $valor;
		else $this->valor[0] = $valor;
		$this->nuevo = $nuevo;
		$this->fo = $fo;
		$this->custom_cond = $condicion;
		if (gettype($bd) == 'boolean') $this->bd = $this->fo->dbname();
		if (gettype($bd) == 'string') $this->bd = $bd;
		if ($this->bd !== $this->fo->dbname()) $this->sbd = sprintf("%s.", $this->bd);
		$this->_campo_linea = $campo_linea;
		if ($this->_cargar_estructura())
		{
			if (gettype($nuevo) == 'string')
			{ 
				if ($nuevo === LT_DETECTAR)
				{
					$nuevo = !$this->cargar();
					$this->nuevo = $nuevo;
					$this->isok = true;
				}
			} 
			else
			{
				if (!$nuevo) $this->isok = $this->cargar(); else $this->isok = true;
			}
		}
	}
	public static function borrar(lt_form $fo, $tabla, $valor, $condicion=false, $bd=false, $autovar_ex=FALSE)
	{
		$isok = false;
		$tr = new self($fo, $tabla, $valor, true, $condicion, $bd, $autovar_ex);
		if ($tr->isok)
		{
			$isok = $tr->remover();
		}
		return $isok;
	}
	/**
	 * 
	 * Crear un nuevo $lt_registro
	 * @param lt_form $fo
	 * Formulario
	 * @param string $tabla
	 * Nombre de la tabla
	 * @param variant $valor
	 * Valor de los campos clave, un valor solo o un array
	 * @param variant $nuevo
	 * (opcional) Indica si es un nuevo registro o ya existe en la tabla. Por defecto FALSE. 
	 * LT_DETECTAR si desea detectar si es nuevo.
	 * @param variant $condicion
	 * (opcional) Condicion personalizada, un string o un lt_condicion
	 * @param string $bd
	 * (opcional) Base de datos a consultar
	 * @param variant $autovar_ex
	 * (opcional) Lista o array de campos autovar a excluir, por defecto FALSE (no excluir ninguno)
	 * @param string $campo_linea
	 * (opcional) Campo de ordenacion en detalle, por defecto 'linea'
	 * @return lt_registro|boolean
	 * Devuelve el lt_registro o FALSE si no fallo
	 */
	public static function crear(lt_form $fo, $tabla, $valor, $nuevo=false, $condicion=false, 
			$bd=false, $autovar_ex=false, $campo_linea='linea')
	{
		$tr = new self($fo, $tabla, $valor, $nuevo, $condicion, $bd, $autovar_ex, $campo_linea);
		if ($tr->isok) return $tr;
		return false;
	}
	
	public static function campo(lt_form $fo, $tabla, $valor, $campo, $por_defecto='')
	{
		$tr = new self($fo, $tabla, $valor, FALSE, FALSE, FALSE, FALSE);
		if ($tr->isok)
		{ 
			return $tr->v($campo, $por_defecto);
		}
		return false;
	}
	
	public static function campos(lt_form $fo, $tabla, $valor, array $campo, $por_defecto='')
	{
		$rv = array();
		$tr = new self($fo, $tabla, $valor, FALSE, FALSE, FALSE, FALSE);
		if ($tr->isok)
		{ 
			foreach ($campo as $cp) $rv[] = $tr->v($cp, $por_defecto);
		}
		return $rv;
	}
	/**
	 * 
	 * Insertar nuevo registro a partir de array lineal campo/valor
	 * @param lt_form $fo
	 * contexto
	 * @param string $tabla
	 * Nombre de tabla
	 * @param array $valores
	 * Array de campos/valores
	 * @return boolean
	 */
	public static function insertar_array(lt_form $fo, $tabla, array $valores)
	{
		$tr = new self($fo, $tabla, 0, true);
		if ($tr->isok)
		{
			$tr->aval($valores);
			if ($tr->insertar()) return $tr;
		}
		return false;
	}
	
	/**
	 * 
	 * Devuelve los nombres de cada columna/campo de la tabla a la que pertenece el registro
	 * @param number $devolver_tipo
	 * 1=Lista separada por comas, 2=array
	 * @param string $conjunto
	 * (opcional) Conjunto de campos a incluir o excluir, separado por comans
	 * @param bool $exclusivo
	 * (opcional) Indica si el conjunto anterior es exclusivo o inclusivo, por defecto TRUE 
	 * @return Ambigous <string, multitype >
	 * Lista o array con los nombres de los campos
	 */
	public function campos_nombres($devolver_tipo=1, $conjunto='', $exclusivo=TRUE)
	{
		if ($devolver_tipo == 1) $rset = '';
		if ($devolver_tipo == 2) $rset = array();
		$ii = 0;
		foreach ($this->campos as $cp)
		{
			if ($conjunto != '')
			{
				if ($exclusivo) $pco = strpos($conjunto, $cp->n) === false;
				else $pco = strpos($conjunto, $cp->n) !== false; 
			}
			else $pco = true;
			if ($pco)
			{
				if ($devolver_tipo == 1)
				{
					$sep = $ii == 0 ? '': ',';
					$rset .= $sep.$cp->n;
					$ii++;
				}
				if ($devolver_tipo == 2) $rset[] = $cp->n;
			}
		}
		return $rset;
	}
	
	/**
	 * 
	 * Copiar todos los valores del registro a $destino
	 * @param lt_registro $destino
	 * Registro donde seran copiados los valores
	 * @param string $conjunto
	 * (opcional) Conjunto de campos a incluir o excluir, separado por comans
	 * @param bool $exclusivo
	 * (opcional) Indica si el conjunto anterior es exclusivo o inclusivo, por defecto TRUE 
	 */
	public function copy_to(lt_registro $destino, $conjunto='', $exclusivo=true)
	{
		$tmpcp = $this->campos_nombres(2, $conjunto, $exclusivo);
		foreach ($tmpcp as $cn) $destino->av($cn, $this->v->$cn);
	}

	/**
	 * 
	 * Factory para leer la estructura de una tabla
	 * @param lt_form $fo
	 * Contexto
	 * @param string $tabla
	 * Nombre de la tabla
	 * @param string $bd
	 * (opcional) Nombre de la base de datos
	 * @return lt_registro|boolean
	 * Devuelve un lt_registro si es exitoso, sino FALSE 
	 */
	public static function estructura(lt_form $fo, $tabla, $bd=FALSE)
	{
		$es = new self($fo, $tabla, 0, true, false, $bd);
		if ($es->isok) return $es;
		return false;
	}
	
	public static function by_parametros(lt_form $fo, parametros $p, $bd=FALSE)
	{
		if (isset($p->__tabla))
		{
			$es = new self($fo, $p->__tabla, 0, true, false, $bd);
			if ($es->isok)
			{
				$es->valor = array();
				foreach ($es->campos as &$fl)
				{
					if ($fl->clave)
					{
						$cn = $fl->n;
						if (isset($p->$cn))
						{ 
							$es->valor[] = $p->$cn;
							$es->campos[$cn]->from_value($p->$cn);
						}
					}
				}
				//$fo->dump($es->valor);
				if (!empty($es->valor))
				{
					if ($es->cargar()) $es->nuevo = FALSE;
					$es->asignar($p);
					return $es;
					/*if ($es->cargar())
					{
						$es->asignar($p); 
						return $es;
					}*/
				}
				else $fo->warn('Parametros no incluyen campo(s) clave');
			}
			else $fo->warn('Error cargando estructura');
		}
		else $fo->warn('Tabla no especificada en parametros');
		return false;
	}
	
	/**
	 * 
	 * Devuelve el o los campo(s) clave
	 * @param number $tipoRetorno
	 * 0 devuelve string, 1 devuelve array  
	 */
	public function campo_clave($tipoRetorno=0)
	{
		$rv = FALSE;
		$aclv = array();
		
		foreach ($this->campos as $fl)
		{
			if ($fl->clave) $aclv[] = $fl->n;
		}
		if ($tipoRetorno == 0) $rv = implode(',', $aclv);
		if ($tipoRetorno == 1) $rv = $aclv;
		
		return $rv;
	}
	
	/**
	 *
	 * Carga estructura de la tabla
	 * @return bool
	 */
	private function _cargar_estructura()
	{
		$isok = false;
		$this->campos_c = 0;
		$q = sprintf(
				"SELECT COLUMN_NAME, COLUMN_DEFAULT, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, ".
				"COLUMN_COMMENT, NUMERIC_PRECISION, NUMERIC_SCALE, IS_NULLABLE, COLUMN_KEY ".
				"FROM INFORMATION_SCHEMA.COLUMNS ".
				"WHERE TABLE_NAME='%s' AND TABLE_SCHEMA='%s' ".
				"ORDER BY ORDINAL_POSITION", $this->tabla, $this->bd);
		if ($this->fo->verbose_level == LT_VERBOSE_DEBUG) error_log($q);
		if (($res = mysql_query($q)) !== false)
		{
			while (($ox = mysql_fetch_object($res)) !== false)
			{
				$t = 'c';
				if (strpos('char,varchar,text,mediumtext,blob,mediumblob', $ox->DATA_TYPE) !== false) $t = 'c';
				if (strpos('int,smallint,tinyint', $ox->DATA_TYPE) !== false) $t = 'i';
				if (strpos('float,double,decimal', $ox->DATA_TYPE) !== false) $t = 'n';
				if ($ox->DATA_TYPE == 'date') $t = 'd';
				if ($ox->DATA_TYPE == 'datetime') $t = 't';
				if ($ox->DATA_TYPE == 'time') $t = 'h';
				$vd = $ox->COLUMN_DEFAULT;
				$cn = $ox->COLUMN_NAME;
				$this->campos[$cn] = new lt_campo($cn, $t, $vd, '', false, $ox->NUMERIC_SCALE);
				//error_log(print_r($this->campos[$cn], TRUE));
				if ($ox->COLUMN_KEY == 'PRI')
				{
					if (isset($this->valor[$this->pkc])) 
						$this->campos[$cn]->from_value($this->valor[$this->pkc]);
					$this->campos[$cn]->clave = true;
					$this->pkc++;
				}
				
				// campos de auditoria / autovariables
				if ($ox->COLUMN_NAME == 'creado')
				{ 
					if ($this->_autovar_set['creado']) $this->campos[$cn]->fecha_auto = 2;
				}
				if ($ox->COLUMN_NAME == 'modificado')
				{ 
					if ($this->_autovar_set['modificado']) $this->campos[$cn]->fecha_auto = 3;
				}
				if ($ox->COLUMN_NAME == 'uid')
				{ 
					if ($this->_autovar_set['uid'])
					{
						$this->campos[$cn]->autovar = 1;
						if ($this->nuevo) $this->campos[$cn]->autovar();
					}
				}
				if ($ox->COLUMN_NAME == 'ipaddr')
				{ 
					if ($this->_autovar_set['ipaddr'])
					{
						$this->campos[$cn]->autovar = 1;
						if ($this->nuevo) $this->campos[$cn]->autovar();
					}
				}
				
				if ($ox->IS_NULLABLE == 'YES') $this->campos[$cn]->is_nullable = true;
				$this->campos_c++;
			}
			// load defaults
			foreach ($this->campos as $fl)
			{
				//error_log(print_r($fl, true));
				$cn = $fl->n;
				$this->v->$cn = $fl->v;
			}
			$isok = true;
		}
		else $this->fo->qerr($this->tabla.'-ST');

		// TODO: cargar otros detalles estructurales
		// TODO: si existe, cargar detalles adicionales de ltable_fl
		return $isok;
	}
		
	private function _pkey_cond($ex_set='')
	{
		$clave = '';
		if (gettype($this->custom_cond) == 'string')
		{
			if ($this->custom_cond != '') $clave = $this->custom_cond;
		}
		if (gettype($this->custom_cond) == 'object') $clave = $this->custom_cond->render($this->campos);
		if ($clave == '')
		{
			foreach ($this->campos as $fl)
			{
				if ($fl->clave)
				{
					if ($ex_set == '')
					{
						if ($fl->t == 'c') $clave .= sprintf(" AND `%s`='%s'", $fl->n, $fl->v);
						elseif ($fl->t == 'i') $clave .= sprintf(" AND `%s`=%d", $fl->n, $fl->v);
						elseif ($fl->t == 'n') $clave .= sprintf(" AND %s=%f", $fl->n, $fl->v);
						elseif (strpos('d,t,h', $fl->t) !== false) $clave .= sprintf(" AND `%s`='%s'", 
								$fl->n, $fl->v->to_sql());
					}
					else 
					{
						if (strpos($ex_set, $fl->n) === false)
						{
							if ($fl->t == 'c') $clave .= sprintf(" AND `%s`='%s'", $fl->n, $fl->v);
							elseif ($fl->t == 'i') $clave .= sprintf(" AND `%s`=%d", $fl->n, $fl->v);
							elseif ($fl->t == 'n') $clave .= sprintf(" AND `%s`=%f", $fl->n, $fl->v);
							elseif (strpos('d,t,h', $fl->t) !== false) 
								$clave .= sprintf(" AND `%s`='%s'", $fl->n, $fl->v->to_sql());
						}
					}
				}
			}
			if ($clave != '') $clave = substr($clave, 4);
		}
		return $clave;
	}
	
	/**
	 *
	 * Cargar valores del registro a partir de la tabla
	 * @return bool
	 */
	public function cargar()
	{
		$cargar_ok = false;
		$set = '';
		foreach ($this->campos as $fl)
		{
			if ($fl->n === $fl->origen)	$set .= sprintf(",`%s`", $fl->n);
			else $set .= sprintf(",`%s` AS %s", $fl->origen, $fl->n);
		}
		if ($set != '')
		{
			$set = substr($set, 1);
			$this->condicion = $this->_pkey_cond();
			$q = sprintf("SELECT %s FROM %s%s WHERE %s", $set, $this->sbd, $this->tabla, $this->condicion);
			//error_log($q);
			if (($res = mysql_query($q)) !== false)
			{
				if (($ox = mysql_fetch_object($res)) !== false)
				{
					foreach ($this->campos as &$flx)
					{
						$cn = $flx->n;
						$flx->from_record($ox->$cn);
						$this->v->$cn = $flx->v;
					}
					$cargar_ok = true;
				}
				mysql_free_result($res);
			}
			else $this->fo->qerr($this->tabla.'-LD');
		}
		return $cargar_ok;
	}
	/**
	 *
	 * Asignar valores de los campos a partir de un objeto
	 * @param variant $p
	 * Objeto con los valores a asignar
	 */
	public function asignar($p)
	{
		if (gettype($p) == 'object')
		{
			if (get_class($p) == 'parametros')
			{
				$p->escape();
				foreach ($this->campos as &$fl) $fl->from_form($p);
			}
			elseif (get_class($p) == 'myquery')
			{
				foreach ($this->campos as &$fl)
				{
					///$flx = new lt_campo($n, $t, $vd);
					$cn = $fl->n;
					if (isset($p->r->$cn)) $fl->from_value($p->r->$cn);
				}
			}
			else
			{
				foreach ($this->campos as &$fl)
				{
					$cn = $fl->n;
					if (isset($p->$cn)) $fl->from_value($p->$cn);
				}
			}
		}
		if (gettype($p) == 'array')
		{
			foreach ($this->campos as &$fl)
			{
				$cn = $fl->n;
				if (isset($p[$cn])) $fl->from_value($p[$cn]);
			}
		}
	}
	/**
	 *
	 * Asignar valor a un campo especifico directamente
	 * @param string $campo
	 * Nombre del campo a actualizar
	 * @param variant $valor
	 * Valor a asignar
	 * @return bool
	 */
	public function av($campo, $valor)
	{
		$isok = false;
		if (isset($this->campos[$campo]))
		{ 
			//if ($campo == 'entrega_observ') error_log('C='.$campo.' T='.$this->campos[$campo]->t.' V='.$valor);
			$this->campos[$campo]->from_value($valor);
			$this->v->$campo = $this->campos[$campo]->v;
            //if ($campo == 'entrega_observ') error_log($campo.'="'.print_r($valor,true).'"');
            //if ($campo == 'entrega_observ') error_log($campo.'=="'.print_r($this->campos[$campo]->v,true).'"');
			$isok = true;
		}
		else error_log('Campo no existe:'.$campo);
		return $isok;
	}
	/**
	 * 
	 * Asignar valores a un conjunto de campos
	 * @param array $pares
	 * Array de arrays de dos elementos, campo/valor
	 */
	public function ava(array $pares)
	{
		foreach ($pares as $par) $this->av($par[0], $par[1]);
	}
	/**
	 * 
	 * Asignar valores a un conjunto de campos
	 * @param array $pares
	 * Array lineal/numerico de pares campo/valor
	 */
	public function aval(array $lineal)
	{
		$ktmp = array_keys($lineal);
		foreach ($ktmp as $key) $this->av($key, $lineal[$key]);
	}
	/**
	 * 
	 * Obtener nueva linea
	 */
	private function _nueva_linea()
	{
		$linea = 0;
		$ql = sprintf("SELECT IF(ISNULL(mx), 0, mx) mx FROM (SELECT MAX(%s) mx FROM %s%s WHERE %s) qq",
			$this->_campo_linea, $this->sbd, $this->tabla, $this->_pkey_cond($this->_campo_linea));
		if (($res = mysql_query($ql)) !== false)
		{
			if (($ox = mysql_fetch_object($res)) !== false) $linea = $ox->mx + 1;
		}
		else $this->fo->qerr('LTREG-NVLN-1');
		return $linea;
	}
	/**
	 *
	 * Devolver el valor de un campo especifico
	 * @param string $campo
	 * Nombre del campo a consultar
	 * @param variant $valor_def
	 * (opcional) Valor por defecto en caso de no encontrar campo
	 */
	public function v($campo, $valor_def=false)
	{
		$valor = $valor_def;
		if (isset($this->campos[$campo])) $valor = $this->campos[$campo]->v;
		//else error_log('NEX:'.$campo);
		return $valor;
	}
	/**
	 *
	 * Insertar nuevo registro en tabla
	 * @return bool
	 */
	public function insertar()
	{
		$this->isok = false;
		// build insert values
		$set = '';
		$nset = '';
		$claven = '';
		foreach ($this->campos as &$fl)
		{
			if ($fl->clave)
			{
				if ($claven == '') $claven = $fl->n;
				if ($fl->n == $this->_campo_linea) $this->av($this->_campo_linea, $this->_nueva_linea());
			}
			if (($fl->fecha_auto == 2 && $this->nuevo) || $fl->fecha_auto == 3)
			{
				if ($fl->t == 't') $fl->v = new lt_fecha(time(), LT_FECHAHORA_T);
				if ($fl->t == 'd') $fl->v = new lt_fecha(time(), LT_FECHA_T);
				if ($fl->t == 'h') $fl->v = new lt_fecha(time(), LT_HORA_T);
				$fl->actualizar = 1;
			}
			if ($fl->autovar) $fl->autovar();
			///if (!$fl->is_nullable) $fl->actualizar = 1;
			if ($fl->actualizar)
			{
				$nset .= sprintf(",`%s`", $fl->n);
				if ('i' == $fl->t) $set .= sprintf(",%d", $fl->v);
				if ('n' == $fl->t) $set .= sprintf(",%f", $fl->v);
				if ($fl->t == 'c') $set .= sprintf(",'%s'", $fl->v);
				if (strpos('d,t,h', $fl->t) !== false) $set .= sprintf(",FROM_UNIXTIME(%d)", $fl->v->timestamp);
			}
		}
		
		// do insert query
		if ($set != '')
		{
			$q = sprintf("INSERT INTO %s%s (%s) VALUES (%s)", $this->sbd, $this->tabla, substr($nset, 1), substr($set, 1));
			//error_log($q);
			if (mysql_query($q) !== false)
			{
				$this->id = mysql_insert_id();
				if ($this->id > 0) $this->av($claven, $this->id); // si se autoincremento, reemplazar campo clave
				$this->isok = true;
			}
			else $this->fo->qerr($this->tabla.'-INS: '.$q);
		}
		$this->upset = $set;
		return $this->isok;
	}
	/**
	 *
	 * Actualizar registro en tabla
	 * @return bool
	 */
	public function actualizar()
	{
		$this->isok = false;
		$this->condicion = $this->_pkey_cond();
		// build update set
		$set = '';
		foreach ($this->campos as &$fl)
		{
			if (($fl->fecha_auto == 2 && $this->nuevo) || $fl->fecha_auto == 3)
			{
				if ($fl->t == 't') $fl->v = new lt_fecha(time(), LT_FECHAHORA_T);
				if ($fl->t == 'd') $fl->v = new lt_fecha(time(), LT_FECHA_T);
				if ($fl->t == 'h') $fl->v = new lt_fecha(time(), LT_HORA_T);
				$fl->actualizar = 1;
			}
			
			if ($fl->autovar) $fl->autovar();
			
			$toup = $this->actualizar_todo ? 1 : $fl->actualizar;
			//error_log(print_r($fl, true));
			if ($toup && !$fl->clave)
			{
				$par = '';
				if ($fl->t == 'i') $par = sprintf(",`%s`=%d", $fl->n, $fl->v);
				if ($fl->t == 'n') $par = sprintf(",`%s`=%f", $fl->n, $fl->v);
				if (strpos('c,s', $fl->t) !== false) $par = sprintf(",`%s`='%s'", $fl->n, $fl->v);
				if (strpos('d,t,h', $fl->t) !== false) $par= sprintf(",`%s`=FROM_UNIXTIME(%d)",
						$fl->n, $fl->v->timestamp);
				$set .= $par;
			}
		}
		// do update
		if ($set != '')
		{
			$set = substr($set, 1);
			$q = sprintf("UPDATE %s%s SET %s WHERE %s",
					$this->sbd, $this->tabla, $set, $this->condicion);
			if ($this->fo->verbose_level >= LT_VERBOSE_DEBUG) error_log($q);
			if (mysql_query($q) !== false)
			{
				$this->isok = true;
			}
			else $this->fo->qerr($this->tabla.'-UP');
		}
		else $this->isok = true;
		$this->upset = $set;
		return $this->isok;
	}
	/**
	 *
	 * Guardar registro en tabla. Si es nuevo inserta, si no actualiza.
	 * @return bool
	 */
	public function guardar()
	{
		$this->isok = false;
		if ($this->usar_transaccion) $this->_begin();
		if ($this->nuevo) $this->insertar(); else $this->actualizar();
		if ($this->usar_transaccion) $this->_end();
		return $this->isok;
	}
	public function intoArray(array &$dst)
	{
		foreach ($this->campos as $cp) $dst[$cp->n] = $cp->v;
	}
	public function remover()
	{
		$isok = false;
		$qdel = new myquery($this->fo, sprintf("DELETE FROM %s%s WHERE %s",
				$this->sbd, $this->tabla, $this->_pkey_cond()), 'LTREGDEL-'.$this->tabla, false, true);
		$isok = $qdel->isok;
		return $isok;
	}
	/**
	 *
	 * Devuelve un array para usar como rowsource de un lt_listbox
	 * @return array
	 * Array numerico de dimensiones N,2
	 */
	public function toListbox()
	{
		$als = array();
		
		$nls = 0;
		$row = array();
		$this->intoArray($row);
		$fila = array_values($row);
		$als[$nls][0] = $fila[0];
		unset($fila[0]);
		$als[$nls][1] = implode(' ', $fila);
		$nls++;

		return $als;
	}
}
?>