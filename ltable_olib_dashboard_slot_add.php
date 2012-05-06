<?php
require_once "ltable_olib.php";

$reto = array("msg"=>"");
$isok = false;
$fo = new lt_form();
$para = array("ds","sl","tp","cn","ti","pl");
if (parms_isset($para,2))
{
	if ($fo->dbopen())
	{
		if ($fo->usrchk(1,3) !== USUARIO_UNAUTH)
		{
			$dashboard_id = $_REQUEST["ds"]+0;
			$slot_id = $_REQUEST["sl"]+0;
			$uid = $_SESSION["uid"];
			$tipo = $_REQUEST["tp"]+0;
			$dashplugin_id = $_REQUEST["pl"]+0;
			$titulo = mysql_real_escape_string($_REQUEST["ti"]);
			$contenido = mysql_real_escape_string($_REQUEST["cn"]);
			if ($tipo == 1)
			{
				$titulo=$contenido="";
				$qb = new myquery($fo, sprintf("SELECT archivo,descripcion ".
					"FROM dashboard_plugins WHERE dashplugin_id=%d", 
					$dashplugin_id), "DASHADD-2");
				if ($qb->isok)
				{
					$titulo = $qb->r->descripcion;
					$contenido = $qb->r->archivo;
				}
			}
			if ($titulo!="" && $contenido!="")
			{
				$qa = new myquery($fo, sprintf("REPLACE INTO dashboard_det VALUES ".
					"(%d,%d,%d,%d,'%s','%s',0,0,1)",
					$dashboard_id, $slot_id, $uid, $tipo, $titulo, $contenido), 
					"DASHADD-1", true, true);
				$isok=$qa->isok;
				/*if ($qa->isok)
				{
					$isok = true;
					$reto=array('msg'=>'','ds'=>$dashboard_id,'sl'=>$slot_id,
						'tp'=>$tipo,'cn'=>$contenido);
				}*/
			}
			else $fo->parc("Especifique titulo/URL", 3, "cursiva");
		}
	}
}
$fo->tojson($isok, $reto, LTMSG_HIDE);
?>
