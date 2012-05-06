<?php
require_once "ltable_olib.php";

$isok = false;
$reto = array('msg'=>'');
$para = array('tbl','fl');
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$tabla = mysql_real_escape_string($_REQUEST['tbl']);
	$campo = mysql_real_escape_string($_REQUEST['fl']);
	if ($fo->dbopen())
	{
		if ($fo->usrchk(1, 1) !== USUARIO_UNAUTH)
		{
			$qa = new myquery($fo, sprintf("SELECT * FROM ltable_fl WHERE tabla='%s' AND n='%s'",
				$tabla, $campo), "LTEDITFL-1", true, false, MYASSOC);
			if ($qa->isok)
			{
				$tp = $qa->a[0]['t'];
				$reto['df'] = $qa->a[0]['df'.$tp];
				
				$afl = array('n','t','l','pd','title','ctrl_type','orden','ordenv','vcols','vrows','enabled','ro',
					'hidden','esdato','isup','dup','autovar','postvar','dt_auto','mascara','funcion',
					'valid_fn','valid_parms','init_fn','init_parms','onkey_fn','onkey_parms','ls_tbl',
					'ls_fl_key','ls_fl_desc','ls_fl_order','ls_custom','ls_custom_new');
				foreach ($afl as $fl)
				{
					$reto[$fl] = $qa->a[0][$fl];
				}
				
				$isok = true;
			}
		}
	}
}
$fo->tojson($isok, $reto);
?>