<?php
require_once "ltable_olib.php";
require_once RUTA_LT."ltable_olib_val.php";
require_once RUTA_LT."ltable_olib_up_site.php";

$fox = new lt_form();
$para = array('tabla', 'campo', 'valor', 'nuevo','forzar_valor','cret');
if (parms_isset($para,2))
{
	$tabla = $_REQUEST['tabla'];
	$campo = $_REQUEST['campo'];
	$valor = $_REQUEST['valor'];
	$have_js = intval($_REQUEST['have_js']) == 1;
	$nuevo = intval($_REQUEST['nuevo']) == 1;
	$forzar_valor = intval($_REQUEST['forzar_valor']) == 1;
	$urlret = sprintf("ltable_olib_main.php?tabla=%s", $tabla);
    $empotrado = isset($_REQUEST['_empotrado']);
	if (isset($_REQUEST['cret'])) $urlret = $_REQUEST['cret'];

	if ($have_js)
	{
		if ($fox->dbopen())
		{
			$lto = new ltable(); 
			if ($lto->load($tabla))
			{
				$lto->nuevo = $nuevo;
				$lto->forzar_valor = $forzar_valor;
				if ($lto->nuevo) $shift = 4; else $shift = 2;
				if ($fox->usrchk($lto->form_id+$shift, $lto->form_rw) != USUARIO_UNAUTH)
				{
                    if ($empotrado) {
                        $fox->encabezado_base();
                        $fox->wait_icon();
                        $fox->msg();
                    }
                    else {
                        if (LT_FULL_HEADER) $fox->encabezado(true);
                        else $fox->encabezado_base();
                    }
					
					$lto->load_post();
					if (lto_validate($lto, $fox))
					{
						$fox->ok("Validaciones");

						$lto->values_from_text();
						if ($lto->deps($valor, 'U', $fox->buf))
						{
							$fox->ok("Dependencias");

							$allok = false;
                            myquery::start();
							if (lto_preprocess($lto, $campo, $valor, $fox))
							{
								if ($lto->update($campo, $valor))
								{
									$fox->ok("Actualizaci&oacute;n");
									if (lto_postprocess($lto, $campo, $valor, $fox))
									{
										$fox->ok("Post-proceso");
										$allok = true;
									}
									else $fox->err("LTUP-2", "Error en postproceso");
								}
								else $fox->buf .= $lto->render_error();
							}
							if ($allok)
							{
								myquery::end(TRUE);
								lto_lastprocess($lto, $campo, $valor, $fox);
							}
							else myquery::end(FALSE);
						}
						///else $fox->buf .= $lto->render_error();
					}
				}
			}
			else $fox->buf .= $lto->render_error();
		}
	}
	else $fox->err("LTUP-1", "JavaScript no habilitado en browser.");

    if (!$empotrado) {
        $fox->hr();
        $fox->par();
        $fox->lnk($urlret, "Volver al formulario principal");
        $fox->parx();
    }
}
$fox->footer();
$fox->show();
?>