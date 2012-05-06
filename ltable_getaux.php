<?php
require_once "ltable_olib.php";

nocache();
$tmpstr = '';
$nr = 0;
if (isset($_REQUEST['nq']))
{
    if (mprs_dbcn())
    {
	    $nq = intval($_REQUEST['nq']);
	    for ($ii = 0; $ii < $nq; $ii++)
	    {
		    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'", 
		    	$_REQUEST['c'.$ii], $_REQUEST['t'.$ii], $_REQUEST['k'.$ii], $_REQUEST['v'.$ii]);
		    if (($res = mysql_query($query)) !== false)
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
			    $tmpstr .= "{-1}\"[Error] " . mysql_error() . "\"";
		    }
	    }
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