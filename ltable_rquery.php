<?php
require_once "mprsfn.php";

nocache();
$tmpstr = '';
$nr = 0;
if (isset($_GET['nq']))
{
	if (mprs_dbcn())
	{
		$nq = intval($_GET['nq']);
		for ($ii = 0; $ii < $nq; $ii++)
		{
			$q = $_GET["q$ii"];
			$fw = strtoupper(strtok($q, " "));
			if ($fw == 'SELECT')
			{
				if (($res = mysql_query($q)) !== false)
				{
					$nr = 0;
					$tmpstr3 = '';
					while (($row = mysql_fetch_row($res)) !== false)
					{
						$tmpstr2 = '';
						foreach ($row as $valor)
						{
							$tmpstr2 .= "\"" . $valor . "\"";
						}
						$tmpstr3 .= $tmpstr2;
						$nr++;
					}
					mysql_free_result($res);
					$tmpstr .= '{'.$nr.'}'.$tmpstr3;
				}
				else
				{
					$nr = -1;
					$tmpstr .= "{-1}\"[Error] " . mysql_error() . " [q] ".$q2."\"";
				}
			}
			else
			{
				$nr = -1;
				$tmpstr .= "{-1}\"[Error] Invalid keyword.\"";
			}
		}
		mysql_close();
	}
	else
	{
		$tmpstr .= "{-1}\"[Error] Fallo en la conexion.\"";
	}
}
else
{
	$tmpstr .= "{-1}\"[Error] Parametros insuficientes.\"";
}
echo $tmpstr;
?>