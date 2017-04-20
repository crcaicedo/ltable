<?php

/**
 * 
 * Crea automaticamente variables a partir de los parametros
 *
 */
class parametros
{
	public $isok = false, $_n = array(), $_nsz = 0;
	private $unescaped = true, $_ne = array(), $_nesz = 0, $_ex = false, $_hayvacio = false, $_http_verb = 'GET';
	private $_vec = array(), $_isvec = false, $_vecsub = false, $_nm = '', $_nmx = '', $_v = '', $_t = 'c', $_qs = '';
	private function _detect_array($nmx, $tpx, $psx)
	{
		$this->_nmx = $nmx;
		$this->_t = $tpx;
		$this->_ex = false;
		if (strpos($psx, '[') !== false)
		{
			$sinc = split("\[", $psx);
			$this->_nm = $sinc[0];
			$this->_vecsub = str_replace(']', '', $sinc[1]);
			$bh = isset($this->_qs[$this->_nm][$this->_vecsub]);
			if ($this->_t == 'l' || $this->_t == 'b')
			{
				$this->_v = 0;
				if ($bh)
				{
					$vecsub = $this->_qs[$this->_nm][$this->_vecsub];
					$this->_v = ($vecsub == 'on' || $vecsub == '1') ? 1 : 0;
				}
				$this->_ex = true;
			}
			else
			{
				if ($bh)
				{
					$vv = $this->_qs[$this->_nm][$this->_vecsub];
					switch ($this->_t)
					{
						case 'i': $this->_v = intval($vv); break;
						case 'd': $this->_v = new lt_fecha($vv); break;
						case 't': $this->_v = new lt_fecha($vv, LT_FECHAHORA_T); break;
						case 'h': $this->_v = new lt_fecha($vv, LT_HORA_T); break;
						//case 'n': $this->_v = nen($vv); break;
						default:$this->_v = $vv;
					} 
					$this->_ex = true;
				}
			}
			$this->_isvec = true;
		}
		else
		{ 
			$this->_nm = $nmx;
			$this->_isvec = false;
			$bh = isset($this->_qs[$psx]);
			if ($this->_t == 'l' || $this->_t == 'b')
			{
				$this->_v = 0;
				if ($bh)
				{ 
					$vecsub = $this->_qs[$psx];
					$this->_v = ($vecsub == 'on' || $vecsub == '1') ? 1 : 0;
				}
				$this->_ex = true;
			}
			else
			{
				if ($bh)
				{
					$vv = $this->_qs[$psx];
					switch ($this->_t)
					{
						case 'i': $this->_v = intval($vv); break;
						case 'd': $this->_v = new lt_fecha($vv); break;
						case 't': $this->_v = new lt_fecha($vv, LT_FECHAHORA_T); break;
						case 'h': $this->_v = new lt_fecha($vv, LT_HORA_T); break;
						//case 'n': $this->_v = nen($vv); break;
						default:$this->_v = $vv;
					} 
					$this->_ex = true;
				}
			}
		}
		if ($this->_ex) $this->_add(); else $this->_hayvacio = true;
		return $this->_ex;
	}
	private function _add()
	{
		$nm = $this->_nm;
		if ($this->_isvec)
		{
			$this->_vec[$this->_nm][$this->_vecsub] = $this->_v;
			$this->$nm = $this->_vec[$nm];
		}
		else
		{
			$this->$nm = $this->_v;
		}
		$this->_n[$this->_nsz++] = $nm;
		$ppt = $nm . '_t';
		$this->$ppt = $this->_t;
		if ($this->_t == 'c' || $this->_t == 's') $this->_ne[$this->_nesz ++] = $nm;
		// error_log('T='.$ppt);
	}
	public function add($nombre, $valor, $tipo='c')
	{
		$cnt = $nombre.'_t';
		$this->$nombre = $valor;
		$this->$cnt = $tipo;
	}
	public function parametro_crudo($nombre_parametro, $valor_por_defecto)
	{
		$rtv = $valor_por_defecto;
		if (isset($this->_qs[$nombre_parametro])) $rtv = $this->_qs[$nombre_parametro];
		return $rtv; 
	}
	public function build($parms, $rechazar_vacio, $http_verb='GET')
	{
		$this->unescaped = true;
		$this->_n = array();
		$this->_nsz = 0;
		$this->_ne = array();
		$this->_nesz = 0;
		$this->_qs = &$_REQUEST;
//error_log("RQ=".@file_get_contents('php://input'));
		$this->_http_verb = $http_verb;
		if (strpos('PUT,DELETE', $this->_http_verb) !== false)
		{
			$this->_qs = array();
			parse_str(file_get_contents( 'php://input' ), $this->_qs);
		}
		//error_log('MANDARINOZ='.print_r($this->_qs, true));
		if ($parms === false)
		{
			if (isset($this->_qs['_formparms'])) $parms = $this->_qs['_formparms'];
			else
			{
				$parms = ''; 
				$ak = array_keys($this->_qs);
				$parms = implode(';', $ak);
			}
			$rechazar_vacio = false;
		}
		if (($apar = split(';', $parms)) !== false)
		{
			$this->_hayvacio = false;
			foreach ($apar as $pp)
			{
				//error_log(sprintf("pp=%s", $pp));
				if (strpos($pp, ',') !== false)
				{
					$tp = 'c';
					$pa = split(',', $pp);
					$ps = $pa[0];
					if (strpos($pa[1], ':') !== false)
					{
						$ala = split(':', $pa[1]);
						$tp = $ala[0];
						$nm = $ala[1];
					}
					else
					{
						$tp = $pa[1];
						$nm = $ps;
					}
					//error_log(sprintf("nm=%s,tp=%s,ps=%s,pa[]=%s %s", $nm, $tp, $ps, $pa[0], $pa[1]));
					$this->_detect_array($nm, $tp, $ps);
				}
				else $this->_detect_array($pp, 'c', $pp);
			}
			$this->_vec = array();
			if ($this->_hayvacio && $rechazar_vacio) $this->isok = false; else $this->isok = true;
		}
	}
	public function escape()
	{
		if ($this->unescaped)
		{
			foreach ($this->_ne as $n)
			{
				$nm = $n.'_e';
				$this->$nm = mysql_real_escape_string($this->$n);
				//error_log($this->$nm);
			}
			$this->unescaped = false;
		}
	}
	public static function crear($parms=false, $rechazar_vacio=true, $http_verb='GET')
	{
		$tmpp = new self($parms, $rechazar_vacio, $http_verb);
		if ($tmpp->isok) return $tmpp; else return false;
	}
	private function _from_ctrl_set(lt_ctrl_set $ctset)
	{
		foreach ($ctset->a as $ct)
		{
			if (strpos('s,a,j,b,u', $ct->t) === false)
			{
				$nm = $ct->c->n;
				switch ($ct->c->t)
				{
					case 'i': $v = intval($ct->c->v); break;
					case 'n': $v = doubleval($ct->c->v); break;
					case 'd': $v = new lt_fecha(intval($ct->c->v)); break;
					case 't': $v = new lt_fecha(intval($ct->c->v), LT_FECHAHORA_T); break;
					case 'h': $v = new lt_fecha(intval($ct->c->v), LT_HORA_T); break;
					default: $v = $ct->c->v; break;
				}
				$this->$nm = $v;
			}
		}
	}
	function __construct($parms=false, $rechazar_vacio=true,$http_verb = 'GET')
	{
		if (gettype($parms) == 'string' || gettype($parms) == 'boolean') 
			$this->build($parms, $rechazar_vacio,$http_verb);
		if (gettype($parms) == 'object')
		{
			if (get_class($parms) == 'lt_ctrl_set') $this->_from_ctrl_set($parms);
		}
	}
}
?>