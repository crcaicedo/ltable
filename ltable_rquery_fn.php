<?php
require_once "mprsfn.php";

function rquery_result($q)
{
	$tmpstr = '';
	
	if (($res = mysql_query($q)) !== false)
	{
		$nr = 0;
		$tmpstr3 = '';
		while (($row = mysql_fetch_row($res)) !== false)
		{
			$tmpstr2 = '';
			foreach ($row as $valor)
			{
				$tmpstr2 .= "\"$valor\"";
			}
			$tmpstr3 .= $tmpstr2;
			$nr++;
		}
		mysql_free_result($res);

		$tmpstr = sprintf("{%d}%s", $nr, $tmpstr3);
	}
	else
	{
		$nr = -1;
		$tmpstr = "{-1}\"[E1002] " . mysql_error() . "\"";
	}
	
	return $tmpstr;
}
?>
