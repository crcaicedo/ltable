<?php
require_once "ltable_olib.php";
require_once "ltable_rptfn.php";

$fo = new lt_form();
$para = array("rpt_name");
if (parms_isset($para, 2))
{
	$rpt_name = $_REQUEST['rpt_name'];
	if (mprs_dbcn())
	{
		$rpt = null;
		if (ltrpt_load($fo, $rpt_name, $rpt))
		{
			if ($fo->usrchk($rpt['rpt_id'], $rpt['usertype_id']) != USUARIO_UNAUTH)
			{
				$fo->encabezado_base();
				
				$cc = 0;
				$fla = $rpt['fla'];
				foreach ($fla as $pa)
				{
					$cn = $pa['n'];
					$ct = $pa['t'];
					$rpt['fla'][$cn]['up'] = isset($_POST['up'.$cc]);
					if ($rpt['fla'][$cn]['up'])
					{
						$rpt['fla'][$cn]['lp'] = $_POST["lp$cc"];
						if ($ct != 'l')
						{
							$rpt['fla'][$cn]['v'] = $_POST[$cn];
							if (strpos('nib', $pa['t']) !== false)
							{
								$rpt['fla'][$cn]['v'] = str_replace('.', '', $rpt['fla'][$cn]['v']);
								$rpt['fla'][$cn]['v'] = str_replace(',', '.', $rpt['fla'][$cn]['v']);
							}
							$rpt['fla'][$cn]['op'] = $_POST["op$cc"];
						}
						else
						{
							$rpt['fla'][$cn]['v'] = isset($_POST[$cn]);
							$rpt['fla'][$cn]['op'] = 0;
						}
					}
					$cc++;
				}
				
				$rpt['orda'] = array();
				$rpt['inva'] = array();
				$ordsz = $_POST['ordsz'];
				for ($nord = $ordsz - 1; $nord >= 0; $nord--)
				{
					if (isset($_POST["ord$nord"]))
					{
						$nc = $_POST["ord$nord"];
						$rpt['orda'][$nc] = $nc;
						if (isset($_POST["ordinv$nord"])) $rpt['inva'][$nc] = 's'; else $rpt['inva'][$nc] = 'n';
					}
				}
				
				if (isset($_POST['have_hlines'])) $rpt['have_hlines'] = true; else $rpt['have_hlines'] = false;
				
				ltrpt_emitir($rpt, $fo);
			}
		}
		else $fo->volver("ltable_rpt.php?rpt=$rpt_name");

		mysql_close();
	}
	else $fo->volver("ltable_rpt.php?rpt=$rpt_name");
}
else $fo->menuprinc();
$fo->footer();
$fo->show();
?>