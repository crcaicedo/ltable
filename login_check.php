<?php
require_once "ltable_olib.php";
require_once "login_fn.php";

$fo = new lt_form();
$fo->encabezado_base();
$para = array("user", "pwnm", 'tourl');
if (parms_isset($para))
{
    if ($fo->dbopen())
    {
	    $pwnm = $_POST['pwnm'];
	    if (login_check($fo, $_POST['user'], $_POST[$pwnm]))
	    {
		    $fo->jsi(sprintf("function almenu(){document.location.replace(\"%s\")} document.onload=almenu()",
				$_REQUEST['tourl']));
		    $fo->par();
		    $fo->lnk($_REQUEST['tourl'], "Pulse aqui para continuar");
		    $fo->parx();
	    }
	    else
    	{
    		$fo->par();
    		$fo->lnk("login_ask.php", "Abrir nueva sesi&oacute;n");
    		$fo->parx();
    	}	    	
     }
}
else
{
	$fo->jsi("document.onload=function(){document.location.replace(\"login_ask.php\")}");
	$fo->par();
	$fo->lnk("login_ask.php", "Pulse aqui para iniciar sesi&oacute;n");
	$fo->parx();
}
$fo->footer();
$fo->show();
?>