<?php
require_once "ltable_olib.php";

function ltedit_preprocess(lt_form $fo, ltable $lto)
{
	$isok = true;

	if ($lto->tabla == 'depositos')
	{
		if ($lto->fa['estatus']->v == 0)
		{
			if (aclcheck($fo, 160, 18, $_SESSION['uid'], $_SESSION['pid']) < 1)
			{
				foreach ($lto->fa as $fl) $fl->ro = true;
				if ($lto->fa['dep_origen']->v != 'CAJ') $lto->rdonly = true;
			}
		}
	}

	return $isok;
}

function ltedit_postprocess(lt_form $fo, ltable $lto)
{
	return true;
}
?>