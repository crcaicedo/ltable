<?php
require_once "ltable_olib.php";

$isok = false;
$reto = array('msg'=>'', 'pid'=>0);
$fo = new lt_form();
if (isset($_GET['newpid']))
{
    $proyecto_id = $_GET['newpid']+0;
    if ($fo->dbopen())
    {
    	if ($fo->usrchk(2, 2) != USUARIO_UNAUTH)
	    {
    		$uid = $_SESSION['uid']+0;
	    	$q = new myquery($fo, sprintf("REPLACE INTO prjusr VALUES (%d,%d)", $uid, $proyecto_id),
	    		'PRYCHOOSER-1', false, true);
		    if ($q->isok) $isok = proyecto_choose($fo, $proyecto_id, $uid);
	    }
    }
}
$fo->tojson($isok, $reto);
?>