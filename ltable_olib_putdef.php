<?php
require_once "ltable_olib.php";

$isok = false;
$reto = array("msg"=>"");
$para = array("cn","cf","cv");
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$form_id = $_REQUEST["cf"];
	$ctrl_name = $_REQUEST["cn"];
	$ctrl_value = $_REQUEST["cv"];
	if ($fo->dbopen())
	{
		if ($fo->usrchk(1,3) !== USUARIO_UNAUTH)
		{		
			$qa = new myquery($fo, sprintf("REPLACE INTO ctrl_default VALUES (%d,%d,'%s','%s')",
				$_SESSION['uid'], $form_id, $ctrl_name, $ctrl_value), "LTPUTDEF-1", true, true);
			$isok = $qa->isok;
		}
		$fo->dbclose();
	}
}
$fo->tojson($isok, $reto);
?>