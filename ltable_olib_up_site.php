<?php
require_once "ltable_olib.php";
require_once "smartrt_cmd.php";
require_once 'radius_fn.php';

function lto_lastprocess(ltable $lto, $campo, $valor, lt_form $fo)
{
	if ($lto->tabla == 'inet_usuarios')
	{
		if ($lto->fa['estatus']->v == 0)
		{
			$rq = new radius_query($fo);
			{
				// send packet-of-disconnect
				if ($rq->disconnect($fo, $lto->fa['username']->v))
				{
					// change to random key
					/*$randomkey = '';
					for ($ii=0; $ii<4; $ii++) $randomkey .= mt_rand(1,9);
					$rq->put($fo, sprintf("UPDATE radcheck ".
						"SET value='%s' WHERE username='%s' AND attribute='Cleartext-Password'",
						$randomkey, $lto->fa['username']->v));*/
				}
			}
		}
	}
	if ($lto->tabla == 'extensiones')
	{
		if ($lto->nuevo)
		{
			// INSERT INTO asterisk.users
		}
		else
		{
			$qa = new myquery($fo, sprintf("UPDATE asterisk.users SET name='%s' WHERE extension='%s'",
				$lto->fa['descripcion']->v, $lto->fa['extension']->v), "LTUPEXT-1", false, true);
			if ($qa->isok) $fo->ok("Extension FreePBX actualizada");
		}
	}
}

function lto_postprocess(ltable $lto, $campo, $valor, lt_form $fo)
{
	$isok = true;
	
	if ($lto->nuevo) $valor = $lto->insert_id;
	if ($lto->tabla == 'canales')
	{
		$isok = false;
		$qz = sprintf("DELETE FROM canales_uso WHERE canal_id=%d", $valor);
		//$fo->parc($qz);
		if (mysql_query($qz) !== false)
		{
			$qy = sprintf("INSERT INTO canales_uso " .
				"(SELECT %d, destino_id, 0, 0, 0 FROM planes_dest " .
				"WHERE plan_id=%d)",
				$valor, $lto->fa['plan_id']->v);
			$fo->parc($qy);
			if (mysql_query($qy) !== false)
			{
				//smrtcmd_send($fo, "5", 0);
				$isok = true;
			}
			else $fo->qerr("CANALES-POST-2");		
		}
		else $fo->qerr("CANALES-POST-1");
	}
	
	if ($lto->tabla == 'inet_usuarios')
	{
		$mkgrp = 'plan2020';
		$qc = new myquery($fo, sprintf("SELECT mikrotik_group FROM cl_plandatos ".
			"WHERE plandatos_id=%d", $lto->fa['plandatos_id']->v), "INETUP-11");
		if ($qc->isok) $mkgrp = $qc->r->mikrotik_group;
		
		$rq  = new radius_query($fo);
		if ($rq->dbok)
		{
			if ($lto->nuevo)
			{
				// id, username, attribute, op, value
				$q1 = sprintf("INSERT INTO radcheck VALUES (0,'%s','Cleartext-Password',':=','%s')", 
						$lto->fa['username']->v, $lto->fa['clave']->v);
				if ($rq->put($fo, $q1))
				{
					// id, username, attribute, op, value
					$q2 = sprintf("INSERT INTO radreply VALUES (0,'%s','Mikrotik-Group',':=','%s')", 
							$lto->fa['username']->v, $mkgrp);
					$isok = $rq->put($fo, $q2);
				}
			}
			else
			{
				$q1 = sprintf("UPDATE radcheck SET value='%s' WHERE username='%s' AND attribute='Cleartext-Password'",
						$lto->fa['clave']->v, $lto->fa['username']->v);
				if ($rq->put($fo, $q1))
				{
					$q2 = sprintf("UPDATE radreply SET value='%s' WHERE username='%s' AND attribute='Mikrotik-Group'",
							$mkgrp, $lto->fa['username']->v);
					$isok = $rq->put($fo, $q2);
				}
			}
		}
	}
	
	if ($lto->tabla == 'extensiones')
	{
		$isok = ltable_histo($fo, $lto->tabla, $campo, $valor, 'E', '', 'smartrt');
	}
	
	return $isok;
}

function lto_preprocess(ltable $lto, $campo, $valor, lt_form $fo)
{
	$isok = true;

	if ($lto->tabla == 'inet_usuarios')
	{
		$isok = ltable_histo($fo, 'inet_usuarios', $campo, $valor, 'E', '', 'smartrt');
	}
	
	return $isok;
}
?>