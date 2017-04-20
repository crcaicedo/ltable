<?php
require_once 'radius_fn.php';

function ltable_del_val(lt_form $fo, ltable $lto, $tabla, $campo, $valor)
{
	$isok = false;
	$uid = $_SESSION['uid'];
	
	if ($lto->deps($valor, 'D', $fo->buf))
	{
		$isok = true;
		if ($lto->tabla == 'inet_usuarios')
		{
			$isok = false;
			if (ltable_histo($fo, $tabla, $campo, $valor, 'D'))
			{
				$qc = new myquery($fo, sprintf("SELECT username FROM inet_usuarios ".
					"WHERE inetusr_id=%d", $valor), "INETDEL-3");
				if ($qc->isok)
				{
					$rq = new radius_query($fo);
					if ($rq->disconnect($fo, $qc->r->username))
					{
						$q1 = sprintf("DELETE FROM radreply WHERE username='%s'", $qc->r->username);
						if ($rq->put($fo, $q1))
						{
							$q2 = sprintf("DELETE FROM radcheck WHERE username='%s'", $qc->r->username);
							$isok = $rq->put($fo, $q2);
						}
					}
				}
			}
		}
		if ($lto->tabla == 'extensiones')
		{
			$isok = ltable_histo($fo, $tabla, $campo, $valor, 'D');
		}
	}
	
	return $isok;
}	
?>