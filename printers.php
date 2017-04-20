<?php
define("PRNUSO_NONE", 0);
define("PRNUSO_RECIBO", 1);
define("PRNUSO_FACSIMIL", 2);

function printer_load($uid, $uso=0, &$prna, $fo=false)
{
	$isok = false;
	
	$q = sprintf("SELECT impr_usr.impresora_id, cupsname, mleft, mright, mtop, mbottom," .
		"mediatype " .
		"FROM impr_usr " .
		"LEFT JOIN impresoras ON impresoras.impresora_id=impr_usr.impresora_id " .
		"WHERE uid=%d AND uso=%d", $uid, $uso);
	if (($res = mysql_query($q)) !== false)
	{
		if (mysql_num_rows($res) > 0)
		{
			if (($row = mysql_fetch_assoc($res)) !== false)
			{
				$prna = $row;
				$isok = true;
			}
		}
		mysql_free_result($res);
	}
	else
	{
		if ($fo !== false) $fo->qerr(20000);
	}
	
	return $isok;
}
?>
