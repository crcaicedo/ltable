<?php
require_once "ltable_olib.php";
require_once "ltable_olib_del_site.php";

$fo = new lt_form();
$para = array('tabla', 'campo', 'valor', 'codsec1', 'codsec2');
if (parms_isset($para,2)) 
{
	$tabla = $_REQUEST['tabla'];
	$valor = $_REQUEST['valor'];
	$campo = $_REQUEST['campo'];
	$urlret = sprintf("ltable_olib_main.php?tabla=%s", $tabla);
	if (isset($_REQUEST['cret'])) $urlret = $_REQUEST['cret'];
	if ($fo->dbopen())
	{
		$lto = new ltable();
		if ($lto->load($tabla))
		{
			if ($fo->usrchk($lto->form_id+3, $lto->form_rw) != USUARIO_UNAUTH)
			{
				if (isset($_REQUEST['wh'])) $fo->encabezado_base();
				else $fo->encabezado(true);
				if ($_REQUEST['codsec1'] == $_REQUEST['codsec2'])
				{
					mysql_query("START TRANSACTION");
					mysql_query("SET autocommit=0");

					$isok = false;
					if (ltable_del_val($fo, $lto, $tabla, $campo, $valor))
					{    
						$q = "DELETE FROM $tabla WHERE $campo=$valor";
						if (mysql_query($q) !== false)
						{
							$isok = true;
						}
						else $fo->qerr("LTDEL-3");
					}
					else $fo->err("LTDEL-6", "Error ejecutando validaciones");
				    
					if ($isok)
					{
						mysql_query("COMMIT");
						$fo->ok("Registro borrado");
					}
					else
					{
						mysql_query("ROLLBACK");
						$fo->err("LTDEL-5", "Registro no se pudo borrar, transaccion reversada");
					}
					mysql_query("SET autocommit=1");
				}
				else $fo->err("LTDEL-4", "C&oacute;digos de seguridad no coinciden");
			}
		    
			$fo->hr();
			$fo->par();
			$fo->lnk($urlret, "Volver al formulario anterior");
			$fo->parx();
		}
	}
}
$fo->footer();
$fo->show();
?>
