<?php
require_once "ltable_olib.php";

$tmpstr = '';
$nr = 0;
$fo = new lt_form();
if (isset($_REQUEST['nq']))
{
    if ($fo->dbopen())
    {
	    $nq = intval($_REQUEST['nq']);
	    for ($ii = 0; $ii < $nq; $ii++)
	    {
		    $query = sprintf("SELECT %s FROM %s WHERE %s='%s'", 
		    	$_REQUEST['c'.$ii], $_REQUEST['t'.$ii], $_REQUEST['k'.$ii], $_REQUEST['v'.$ii]);
		    if (($qq = myquery::q($query, LTABLE-GETAUX, FALSE, FALSE, MYROW)))
		    {
			    $nr = 0;
			    $tmpstr3 = '';
			    foreach ($qq->a as $row)
			    {
				    $tmpstr2 = '';
				    foreach ($row as $valor)
					{
					    $tmpstr2 .= "\"" . $valor . "\"";
					}
				    $tmpstr3 .= $tmpstr2;
				    $nr++;
			    }
			    $tmpstr .= '{'.$nr.'}'.$tmpstr3;
		    }
		    else
		    {
			    $nr = -1;
			    $tmpstr .= "{-1}\"[Error] Error ejecutando query\"";
		    }
	    }
	    $fo->dbclose();
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