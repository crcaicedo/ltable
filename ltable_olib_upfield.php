<?php
require_once "ltable_olib.php";
require_once RUTA_LT.'ltable_olib_upfield_site.php';

//error_log('LTABLE_UPFIELD');
$isok = false;
$reto = array("msg"=>"");
$para = array("tbl","fn","fv", "kn", "kv");
$fo = new lt_form();
if (parms_isset($para, 2))
{
	$tabla = $_REQUEST["tbl"];
	$fn = $_REQUEST["fn"];		// field name
	$fv = $_REQUEST["fv"];		// field value
	$kn = $_REQUEST["kn"];		// key name
	$kv = $_REQUEST["kv"];		// key value
	if ($fo->dbopen())
	{
		$lto = new ltable();
        if ($lto->load($tabla, "", false, $fn))
		{
			if ($fo->usrchk($lto->form_id + 2, $lto->form_rw) !== USUARIO_UNAUTH)
			{
				//error_log($fv);
				$lto->nuevo = 0;
				$lto->umethod = 1;
				$lto->fa[$fn]->text = $fv;
				$lto->values_from_text();
				$lto->build_update($kn, $kv, "");
				myquery::start();
				if (ltable_upfield_val($fo, $lto, $fn, $fv, $kn, $kv))
				{
					if (ltable_upfield_preprocess($fo, $lto, $fn, $fv, $kn, $kv))
					{
						if (ltable_histo($fo, $tabla, $kn, $kv, 'E'))
						{
							$quu = sprintf("UPDATE %s SET %s,modificado=NOW(),uid=%d,ipaddr='%s' WHERE %s=%s",
								$tabla, $lto->update_set, $fo->uid, $fo->ipaddr, $kn, $kv);
							//error_log($quu);
							$qa = new myquery($fo, $quu, "LTUPFL:".$tabla.':'.$kn, true, true);
							if ($qa->isok)
							{
								if (ltable_upfield_postprocess($fo, $lto, $fn, $fv, $kn, $kv)) $isok = TRUE;
                                else $fo->warn($tabla.':Error en postproceso', TRUE);
							}
						}
                        else $fo->warn($tabla.':Error en historico', TRUE);
					}
                    else $fo->warn($tabla.':Error en preproceso', TRUE);
				}
                else $fo->warn($tabla.':Error validando', TRUE);
				myquery::end($isok);
			}
			else $fo->warn($tabla.':Usuario sin permiso modificacion', TRUE);
		}
		else $fo->warn($tabla.':Error cargando definicion', TRUE);
	}
	else $fo->warn($tabla.':Error conectando base de datos', TRUE);
}
else $fo->warn('Parametros insuficientes', TRUE);
$fo->tojson($isok, $reto, LTMSG_HIDE);
?>