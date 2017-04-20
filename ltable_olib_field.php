<?php
class lt_field
{
	public $fln = "", $t = 'c', $l = 10, $pd = 0, $df = "", $v = "";
	public $txt = "", $insw = "", $selw = "", $upw = "";
	public $autovar = false, $dt_auto = 0, $tab = 0;
	
	public function __construct($fln = "", $t = 'c', $df = "", $v = "", $l = 10, 
		$pd = 0, $autovar = false, $dt_auto = 0)
	{
		$this->fln = $fln;
		$this->t = $t;
		$this->df = $df;
		$this->v = $v;
		$this->l = $l;
		$this->pd = $pd;
		$this->autovar = $autovar;
		$this->dt_auto = $dt_auto;
		$this->to_text();
	}

	public function autovar()
	{
		if ($this->autovar) $this->v = autovar($this->fln);
	}

	public function to_text()
	{
		switch ($this->t)
		{
			case 'd':
				$this->text = dtoc(fecha_from($this->v));
				break;
			case 'h':
				$this->text = htoc(hora_from($this->v));
				break;
			case 't':
				$this->text = dtoc(fecha_from($this->v))." ".htoc(hora_from($this->v));
				break;
			case 'n':
				$this->text = number_format($this->v, $this->pd, ',', '.');
				break;
			default:
				$this->text = $this->v;
				break;
		}
	}

	public function to_value($es_nuevo = false)
	{
		if ($this->autovar) $this->autovar();
		else
		{
			if (stripos('dht', $this->t) !== false)
			{
				$mdta = $es_nuevo ? 2 : 3;
				if ($this->dt_auto >= $mdta) $this->v = time();
				else
				{
					if ($this->t == 'd') $this->v = fecha2time(ctod($this->text));
					if ($this->t == 'h') $this->v = hora2time(ctoh($this->text));
					if ($this->t == 't') $this->v = $this->text;
				}
			}
			else
			{
				switch ($this->t)
				{
					case 'n':
						$this->v = nen($this->text);
						break;
					default:
						$this->v = $this->text;
					break;
				}
			}
		}
	}

	public function to_select()
	{
		if (strpos("nibcm", $this->t) !== false) $this->selw = $this->fln;
		else $this->selw = "UNIX_TIMESTAMP($this->fln) AS $this->fln";
		return $this->selw;
	}
	
	public function to_update()
	{
		switch ($this->t)
		{
			case 'n':
			case 'i':
			case 'b':
				$this->upw = "$this->fln=$this->v";
				break;
			case 'c':
			case 'm':
				$this->upw = "$this->fln='".mysql_real_escape_string($this->v)."'";
				break;
			case 'd':
			case 'h':
			case 't':
				$this->upw = ",$this->fln=FROM_UNIXTIME($this->v)";
				break;
		}
		return $this->upw;
	}

	public function to_insert()
	{
		switch ($this->t)
		{
			case 'n':
			case 'i':
			case 'b':
				$this->insw = "$this->v";
				break;
			case 'c':
			case 'm':
				$this->insw = "'".mysql_real_escape_string($this->v)."'";
				break;
			case 'd':
			case 'h':
			case 't':
				$this->insw = "FROM_UNIXTIME($fl->v)";
				break;
		}
		return $this->insw;
	}

	public function blank()
	{
		$this->v = $this->df;
		$this->to_text(); 
	}	

	public function assign($newvalue)
	{
		$this->v = $newvalue;
		$this->to_text(); 
	}
	
	public function from_text($txtvalue = "", $es_nuevo = false)
	{
		$this->text = $txtvalue;
		$this->to_value($es_nuevo);
	}	
}

class lt_field_set
{
	public $sz = 0, $fl = array(), $nuevo = true;
	public $tabla="", $keyname="", $keyvalue=0;
	
	public function __construct($tabla="", $keyname="", $keyvalue=0)
	{
		$this->tabla = $tabla;
		$this->keyname = $keyname;
		$this->keyvalue = $keyvalue;
	}
	
	public function add($fln = "", $t = 'c', $df = "", $v = "", $l = 10, $pd = 0, 
		$autovar = false, $dt_auto = 0)
	{
		$this->fl[$fln] = new lt_field($fln, $t, $df, $v, $l, $pd, $autovar, $dt_auto);
		$this->sz++;
	}
	public function to_select()
	{
		$tmps = "";
		foreach ($this->fl as &$fl) $tmps .= sprintf(",%s", $fl->to_select());
		return substr($tmps, 1);
	}

	public function to_insert()
	{
		$tmps = "";
		foreach ($this->fl as &$fl) $tmps .= sprintf(",%s", $fl->to_insert());
		return substr($tmps, 1);
	}

	public function to_update()
	{
		$tmps = "";
		foreach ($this->fl as &$fl) $tmps .= sprintf(",%s", $fl->to_update());
		return substr($tmps, 1);
	}
	
	public function assign(&$row)
	{
		foreach ($this->fl as &$fl) $fl->assign($row[$fl->fln]);
	}
	public function load($fo, $xcond="")
	{
		$isok = false;
		
		if (empty($xcond)) $q = sprintf("SELECT %s FROM %s WHERE %s=%d", 
			$this->to_select(), $this->tabla, $this->keyname, $this->keyvalue);
		else $q = sprintf("SELECT %s FROM %s WHERE %s",
			$this->to_select(), $this->tabla, $this->keyname, $this->keyvalue);
		if (($res = mysql_query($q)) !== false)
		{
			if (($row = mysql_fetch_assoc($res)) !== false)
			{
				$this->nuevo = false;
				$this->assign($row);
			}
			$isok = true;
			mysql_free_result($res);
		}
		else $fo->qerr("FLSETLD-1");
		
		return $isok;
	}
	public function save(lt_form $fo, $xcond="", $tabla="", $force_ins=false)
	{
		$isok = false;
		
		if (empty($tabla)) $latabla = $this->tabla; else $latabla = $tabla;
		if ($this->nuevo || $force_ins)
		{
			$q = sprintf("INSERT INTO %s VALUES (%s)", $latabla, $this->to_insert);
		}
		else
		{
			if (empty($this->xcond))
			{
				$q = sprintf("UPDATE %s SET %s WHERE %s", $latabla, 
					$this->to_update(), $xcond);
			}
			else
			{
				$q = sprintf("UPDATE %s SET %s WHERE %s=%d", $latabla, 
					$this->to_update(), $this->keyname, $this->keyvalue);
			}
		}
		
		if (($res = mysql_query($q)) !== false)
		{
			if ($this->nuevo) $this->keyvalue = mysql_insert_id();
			$isok = true;
		}
		else $fo->qerr("FLSETSV-1");
		
		return $isok;
	}
	public function from_text(&$txta)
	{
		foreach ($this->fl as &$fl) $fl->from_text($txta[$fl->fln], $this->nuevo);
	}
}
?>