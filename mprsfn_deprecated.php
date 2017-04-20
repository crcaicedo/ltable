<?php
/**
 *
 * Obsoleta, no usar
 */
class mprs_inifile
{
	public $user = "", $passwd = "", $fn = "";
	public $dbname = "mprs", $dburl = "localhost:/var/lib/mysql/mysql.sock";

	public function __construct($fn="/etc/mprs.conf")
	{
		$this->fn = $fn;
	}

	public function load()
	{
		$isok = false;
		$lineas = file($this->fn);
		if ($lineas !== false)
		{
			foreach ($lineas as $ln)
			{
				$ln = str_replace("\n", "", $ln);
				$va = explode("=", $ln);
				if ($va[0] == "usr") $this->user = $va[1];
				if ($va[0] == "pw") $this->passwd = base64_decode($va[1]);
				if ($va[0] == "db") $this->dbname = $va[1];
				if ($va[0] == "dburl") $this->dburl = $va[1];
			}
			$isok = true;
		}
		return $isok;
	}
}

/**
 *
 * Deprecated, no usar
 */
function nocache()
{
	header("Expires: Mon, 27 Oct 1977 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	session_start();
	$_SESSION['buggy'] = rand(1,100000);
}

/**
 *
 * Deprecated, no usar
 */
function squeryerror($qryid=0)
{
	return "<p align=\"center\"><i>Error ejecutando indagaci&oacute;n</i> " .
			"<b>[". $qryid ."]</b>: <code>". mysql_error() ."</code></p>";
}

/**
 *
 * Deprecated, no usar
 */
function queryerror($qryid=0)
{
	echo squeryerror($qryid);
}

/**
 *
 * Deprecated, no usar
 */
function copy_stru_toarray(lt_form $fo, $tblnm, &$ast)
{
	$nc = 0;

	if (($res = myquery::q($fo, "DESCRIBE $tblnm", '17600', TRUE, FALSE, MYASSOC)))
	{
		foreach ($res->a as $row)
		{
			$ast[$nc++] = $row;
		}
	}

	return $nc;
}

/**
 *
 * Deprecated, no usar
 */
function ltable_delall($fo, &$tbla, $keyname, $keyval, $zerocount=true, $customcond="")
{
	$delto = count($tbla);
	$delok = 0;
	foreach ($tbla as $tbl)
	{
		if ($customcond == "") $qdel = "DELETE FROM $tbl WHERE $keyname=$keyval";
		else $qdel = "DELETE FROM $tbl WHERE $customcond";
		if (($q = myquery::q($fo, $qdel, 'DELALL:'.strtoupper($tbl), FALSE, TRUE)))
		{
			if ($zerocount) $delok++;
			else
			{
				if ($q->sz > 0) $delok++;
			}
		}
	}

	return $delto == $delok;
}

/**
 *
 * Deprecated, no usar
 */
function ltable_del($fo, $tabla, $campo="", $valor=0, $op="D", $condicion="")
{
	$isok = false;
	if (ltable_histo($fo, $tabla, $campo, $valor, $op, $condicion))
	{
		if ($condicion == "" && $campo != "") $condicion = sprintf("WHERE %s=%s", $campo, $valor);
		if ($condicion != "")
		{
			$qb = sprintf("DELETE FROM %s %s", $tabla, $condicion);
			if (myquery::q($fo, $qb, 'LTABLE-DEL-1', FALSE, TRUE)) $isok = true;
		}
		else $fo->err("LTABLE_DEL-2", "Condicion incorrecta");
	}
	return $isok;
}
?>