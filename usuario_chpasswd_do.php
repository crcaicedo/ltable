<?php
require_once "ltable_olib.php";
require_once "login_fn.php";

$fo = new lt_form();
$para = array('p0','p1','p2');
if (parms_isset($para,2))
{
	if ($fo->dbopen())
	{
		if ($fo->usrchk(2001, 3) !== USUARIO_UNAUTH)
		{
			$uid = $_SESSION['uid'];
			$p0 = $_REQUEST['p0'];
			$p1 = $_REQUEST['p1'];
			$p2 = $_REQUEST['p2'];
			
			if ($p1 == $p2)
			{
				$lg = new mprs_login($_SESSION["unm"], $p0);
				if ($lg->auth_check($fo))
				{
					if ($lg->set_password($fo, $p1)) $fo->ok("Contrase&ntilde;a cambiada");
				}
			}
			else $fo->err("CHPWD-1", "Nueva contrase&ntilde;a no confirmada");			
		}
	}
}
$fo->seguir();
$fo->show();
?>