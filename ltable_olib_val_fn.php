<?php
function pemx($fl, $errormsg, $fo)
{
	$fo->parc(sprintf("Campo <b>%s</b> (<u>%s</u>) <i>%s</i></p>",
		$fl->title, $fl->text, $errormsg));
}

function val_largo(lt_form $fo, $texto,$minimo,$maximo,$nombre_campo, $forzar=FALSE)
{
	$isok = false;
	if (enblanco($texto))
	{
		$fo->warn("Campo <b>".$nombre_campo."</b> en blanco");
	}
	else
	{
		if (preg_match("/^.{" . $minimo . "," . $maximo . "}$/", $texto) == 1) $isok = TRUE;
		else
		{
			$fo->warn("Largo del campo <b>".$nombre_campo."</b> es invalido.\nMinimo: ".$minimo." Maximo:".$maximo);
		}
	}
	return $isok;
}

function val_telefono(lt_form $fo, $texto, $forzar=FALSE)
{
	$isok = false;
	if (enblanco($texto))
	{
		$fo->warn("Campo <b>Telefono</b> en blanco");
		//if ($forzar) $fo->warn("Campo en blanco"); else $isok = TRUE;
	}
	else              
	{
		if (preg_match("/^([0-9]{4}[\\s\\-]?)?([0-9]{7}){1}$/", $texto) == 1) $isok = TRUE;
		else
		{
			$fo->warn("Campo telefono debe tener siete u once digitos: 999999 o 99999999999");
		}
	}
	return $isok;
}

function lt_telefono($fl, $force=false, $fo)
{
	$isok = false;
	if (enblanco($fl->text))
	{
		if ($force) pemx($fl, "est&aacute; en blanco.", $fo); else $isok = true;
	}
	else
	{
		if (preg_match("/^([0-9]{4}[\\s\\-]?)?([0-9]{7}){1}$/", $fl->text) == 1) $isok = true;
		else
		{
			pemx($fl, 'debe tener siete u once digitos: 999999 o 99999999999', $fo);
		}
	}
	return $isok;
}

function lt_telefono_int($fl, $force=false, $fo)
{
	$isok = false;
	if (enblanco($fl->text))
	{
		if ($force) pemx($fl, "est&aacute; en blanco.", $fo); else $isok = true;
	}
	else
	{
		if (preg_match("/^((00|\+){1}[0-9]{2,3}[\s\-]?)?([0-9]{2,4}[\s\-]?)?([0-9]{6,10}){1}$/", $fl->text) == 1) $isok = true;
		else
		{
			pemx($fl, 'debe tener estar en formato de telefono internacional o nacional', $fo);
		}
	}
	return $isok;
}

function lt_numletra($fl, $fo)
{
	$isok = false;
	if (preg_match("/^[0-9a-zA-Z\s.,:;_\-]+$/", $fl->text) == 1)
	{
		$isok = true;
	}
	else
	{
		pemx($fl, 'debe contener solo numeros, letras y signos de puntuaci&oacute;n.', $fo);
	}
	return $isok;
}

function lt_valfecha($fl, $fo)
{
	$isok = false;
	$tmpfe = ctod($fl->text);
	if (fecha_valida($tmpfe)) $isok = true;
	else pemx($fl, 'es una fecha inv&aacute;lida.', $fo);
	
	return $isok;
}

function lt_digitos($fl, $ndig, $oblig = false, $fo)
{
	$isok = false;
	if (enblanco($fl->text))
	{
		if ($oblig) pemx($fl, 'en blanco.', $fo);
		else $isok = true;
	}
	else
	{
		if (preg_match("/^[0-9]{" . $ndig . "}$/", $fl->text) == 1)
		{
			$isok = true;
		}
		else pemx($fl, "debe contener $ndig digitos exactamente.", $fo);
	}
	return $isok;
}

function lt_entero($fl, $minval, $maxval, $fo)
{
	$isok = false;
	if (preg_match("/^[0-9]+$/", $fl->text) == 1)
	{
		$ivalor = intval($fl->text);
		if ($ivalor >= $minval && $ivalor <= $maxval)
		{
			$isok = true;
		}
		else pemx($fl, "debe ser mayor igual que $minval y menor igual que $maxval", $fo);
	}
	else pemx($fl, 'solo debe contener digitos numericos.', $fo);

	return $isok;
}

function lt_entero_warn($fo,$fl, $minval, $maxval,$campo )
{
	$isok = false;
	if (preg_match("/^[0-9]+$/", $fl) == 1)
	{
		$ivalor = intval($fl);
		if ($ivalor >= $minval && $ivalor <= $maxval)
		{
			$isok = true;
		}
		else $fo->warn("debe ser mayor igual que $minval y menor igual que $maxval");/*pemx($fl, "debe ser mayor igual que $minval y menor igual que $maxval", $fo);*/
	}
	else $fo->warn('El campo <b>'.$campo.'</b> solo debe contener digitos numericos.');/*pemx($fl, 'solo debe contener digitos numericos.', $fo);*/

	return $isok;
}

function lt_numerico($fl, $ndec, $minval, $maxval, $fo)
{
	$isok = false;
	$exp = "/^-?[0-9.]{1,}([,]?[0-9]{0,$ndec}){0,1}$/";  // FMT:ES
	//$exp = "/^-?[0-9]{1,}([.]?[0-9]{0,$ndec}){0,1}$/"; // FMT:EN
	
	if (preg_match($exp, $fl->text) == 1)
	{
		$snum = floatval(nen($fl->text));
		$minval = floor($minval);
		if ($snum <= $maxval)
		{
			if ($snum >= $minval)
			{
				$isok = true;
			}
			else pemx($fl, "Debe ser mayor igual a: <b>$minval</b> Valor actual: <b>$snum</b>", $fo);
		}
		else pemx($fl, "Debe ser menor igual a: <b>$maxval</b> Valor actual: <b>$snum</b>", $fo);
	}
	else pemx($fl, "N&uacute;mero mal escrito.", $fo);
	
	return $isok;
}

function lt_novacio($fl, $fo)
{
	$isok = !enblanco($fl->text);
	if (!$isok) pemx($fl, 'est&aacute; en blanco', $fo);
	return $isok;
}

function lt_novacio_warn($fo, $fl, $campo)
{
	if(enblanco($fl)){
		$fo->warn("Campo <b>".$campo."</b> no puede estar en blanco");
		$isok = true;
	}else $isok = false;
	
	return $isok;
}

function lt_forbidden($fl, $fo)
{
	$isok = false;
	if (enblanco($fl->text))
	{
		$isok = true;
	}
	else
	{
		$fmt = "/['\"]+/";
		if (preg_match($fmt, $fl->text) == 0)
		{
			$isok = true;
		}
		else pemx($fl, "Contiene caracteres no permitidos como comillas.", $fo);
	}
	return $isok;
}

function lt_email($campo, $fo)
{
	$isok = false;
	if (enblanco($campo->text)) $isok = true;
	else
	{
		$exp = "/^.+@.+$/";
		if (preg_match($exp, $campo->text) == 1)
		{
			$isok = true;
		}
		else
		{
			pemx($campo, 'no es v&aacute;lido.', $fo);
		}
	}
	return $isok;
}

function lt_rif($fl, $fo)
{
	$isok = false;
	if (preg_match("/^[vVjJ]{1}[\-]{1}[0-9]{8}[\-]{1}[0-9]{1}$/", $fl->text) == 1)
	{
		$isok = true;
	}
	else
	{
		pemx($fl, 'debe estar en el formato [J|V]-99999999-9', $fo);
	}
	return $isok;
}

function ltv_dummy($fo)
{
	return true;
}

function lt_cheque($fl, $oblig = false, $fo)
{
	$isok = false;

	if (enblanco($fl->text))
	{
		if ($oblig) pemx($fl, 'en blanco', $fo);
		else $isok = true;
	}
	else
	{
		if (preg_match("/^[0-9]{8}$/", $fl->text) == 1)
		{
			$isok = true;
		}
		else pemx($fl, 'debe contener 8 digitos exactamente.', $fo);
	}

	return $isok;
}

function lt_deposito($fl, $oblig = false, $fo)
{
	$isok = false;

	if (enblanco($fl->text))
	{
		if ($oblig) pemx($fl, 'en blanco', $fo);
		else $isok = true;
	}
	else
	{
		if (preg_match("/^[0-9]{12}$/", $fl->text) == 1)
		{
			$isok = true;
		}
		else pemx($fl, 'debe contener 12 digitos exactamente.', $fo);
	}

	return $isok;
}
?>