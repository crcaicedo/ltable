<?php
define("LT_ACCION_PFL", 0); // valor de campo registro actual
define("LT_ACCION_PLT", 1); // valor literal 
define("LT_ACCION_URL", 0);
define("LT_ACCION_BOTON", 1);

class lt_accion_param
{
	private $_accion_tipo, $_tipo_valor, $_nombre, $_valor, $_tipo_dato;
	
	function __construct($nombre, $valor, $tipo_valor=LT_ACCION_PFL, 
			$accion_tipo=LT_ACCION_URL, $tipo_dato='c')
	{
		$this->_nombre = $nombre;
		$this->_valor = $valor;
		$this->_tipo_valor = $tipo_valor;
		$this->_accion_tipo = $accion_tipo;
		$this->_tipo_dato = $tipo_dato;
	}
	
	/**
	 * 
	 * Devuelve el valor del parametro
	 * @param variant $r
	 * Objeto o array asociativo
	 * @return string
	 */
	private function _ov($r)
	{
		$v = '';
		//error_log('TV='.$this->_tipo_valor);
		switch ($this->_tipo_valor)
		{
			case LT_ACCION_PLT: $v = $this->_valor; break;
			case LT_ACCION_PFL:
				if (gettype($r) == 'object') { $sv = $this->_valor; $v = $r->$sv; }
				if (gettype($r) == 'array') $v = $r[$this->_valor];
				break;
		}
		return $v;
	}
	/**
	 * 
	 * Devuelve string de el parametro
	 * @param variant $r
	 * Object o array asociativo
	 * @return string
	 */
	public function s($r)
	{
		$s = '';
		// TODO: tomar en cuenta tipo dato
		if ($this->_accion_tipo == LT_ACCION_URL) $s = sprintf("%s=%s", $this->_nombre, $this->_ov($r)); 
		if ($this->_accion_tipo == LT_ACCION_BOTON) $s = sprintf("%s", $this->_ov($r)); 
		return $s;
	}
}

class lt_accion
{
	private $_tipo = LT_ACCION_URL, $_modulo = '', $_p = array(), $_titulo;

	private function _sp_url($r)
	{
		$sp = '';
		$amp = '';
		$que = '';
		foreach ($this->_p as $p)
		{
			$sp .= sprintf("%s%s", $amp, $p->s($r));
			$amp = '&';
			$que = '?';
		}
		return $que.$sp;
	}
	
	private function _sp_boton($r)
	{
		$sp = '';
		$coma = '';
		foreach ($this->_p as $p)
		{
			$sp .= sprintf("%s'%s'", $coma, $p->s($r));
			$coma = ',';
		}
		return $sp;
	}
	
	public function s($r)
	{
		$ss = '';
		if ($this->_tipo == LT_ACCION_URL)
		{
			$ss = sprintf("<a href=\"%s%s\" target=\"_blank\">%s</a>", 
				$this->_modulo, $this->_sp_url($r), $this->_titulo);
		}
		
		if ($this->_tipo == LT_ACCION_BOTON)
		{
			$ss = sprintf("<input type=\"button\" onclick=\"%s(%s);\" value=\"%s\"></input>", 
				$this->_modulo, $this->_sp_boton($r), $this->_titulo);
		}
		return $ss;
	}
	
	function __construct($tipo, $modulo, array $params, $titulo)
	{
		$this->_tipo = $tipo;
		$this->_modulo = $modulo;
		$this->_titulo = $titulo;
		
		foreach ($params as $pa)
		{
			$ptipo = LT_ACCION_PFL;
			if ($this->_tipo == LT_ACCION_URL)
			{
				if (isset($pa[2])) $ptipo = $pa[2];
				$this->_p[] = new lt_accion_param($pa[0], $pa[1], $ptipo, $this->_tipo);
			}
			if ($this->_tipo == LT_ACCION_BOTON)
			{ 
				//error_log(print_r($pa, TRUE));
				if (isset($pa[1])) $ptipo = $pa[1];
				$this->_p[] = new lt_accion_param('', $pa[0], $ptipo, $this->_tipo);
			}
		}
	}
}

class lt_acciones
{
	private $_a = array();
	
	private function _add($tipo, $modulo, array $param, $titulo)
	{
		$this->_a[] = new lt_accion($tipo, $modulo, $param, $titulo);
	}
	public function URL($modulo, array $param, $titulo)
	{
		$this->_add(LT_ACCION_URL, $modulo, $param, $titulo);
	}
	public function boton($modulo, array $param, $titulo)
	{
		$this->_add(LT_ACCION_BOTON, $modulo, $param, $titulo);
	}
	public function s($r)
	{
		$s = '';
		$sp = '';
		foreach ($this->_a as $it)
		{
			$s .= $sp.$it->s($r);
			$sp = '&nbsp';
		}
		return $s;
	}
}
?>