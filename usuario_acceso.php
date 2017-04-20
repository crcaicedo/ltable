<?php
require_once "ltable_olib.php";

function useraccess_usrnfo(&$usra, $uid, $fo)
{
    $isok = false;
    
    $query = sprintf("SELECT uid, name, nombres, apellidos, usuarios.usertype_id, " .
    	"usertypes.descripcion as usrtipo ".
		"FROM usuarios " .
		"LEFT JOIN usertypes ON usertypes.usertype_id=usuarios.usertype_id " .
		"WHERE uid=%d", $uid);
    if (($res = mysql_query($query)) !== false)
    {
    	if (mysql_num_rows($res) > 0)
    	{
	    	if (($row = mysql_fetch_assoc($res)) !== false)
	    	{
	    		$usra = $row;
		    	$isok = true;
	    	}
    	}
    	else $fo->parc("&laquo;Informaci&oacute;n de usuario no encontrada&raquo;", 3, "nohay");
	    mysql_free_result($res);
    }
    else $fo->qerr(11600);

    return $isok;
}

function useraccess_load(&$acca, $uid, $pid, $fo)
{
    $isok = false;
    
    $ii = 0;
    $query = sprintf("SELECT modulos.descripcion as modulo, proyectos.nombre AS proyecto," .
    	"usertypes.descripcion AS nivel," .
		"acceso.uid, acceso.proyecto_id, acceso.modulo_id " .
		"FROM acceso " .
		"LEFT JOIN modulos ON modulos.modulo_id=acceso.modulo_id " .
		"LEFT JOIN proyectos ON proyectos.proyecto_id=acceso.proyecto_id " .
		"LEFT JOIN usertypes ON usertypes.usertype_id=acceso.usertype_id " .
		"WHERE acceso.uid=%d AND acceso.proyecto_id=%d " .
		"ORDER BY modulo", $uid, $pid);
    if (($res = mysql_query($query)) !== false)
    {
    	if (mysql_num_rows($res) > 0)
    	{
	    	while (($row = mysql_fetch_assoc($res)) !== false)
	    	{
		    	$acca[$ii++] = $row;
	    	}
	    	$isok = true;
    	}
	    mysql_free_result($res);
    }
    else $fo->qerr(1404);
    
    return $isok;
}

function useraccess_types(&$uta, $fo)
{
    $isok = false;
    
    $ii = 0;
    $q = "SELECT usertype_id,descripcion FROM usertypes ORDER BY usertype_id"; 
    if (($res = mysql_query($q)) !== false)
    {
	    if (mysql_num_rows($res) > 0)
	    {
		    while (($ut = mysql_fetch_assoc($res)) !== false)
		    {
			    $uta[$ii++] = $ut;
		    }
		    $isok = true; 
	    }
	    mysql_free_result($res);
    }
    else $fo->qerr(1402);
    
    return $isok;
}

function useraccess_mods(array &$mda, $uid, $pid, lt_form $fo)
{
    $isok = false;
    
    $ii = 0;
    $tpset = '1,3,4';
    if ($pid == 5 || $pid == 6) $tpset = '1,2,4';
    $q = new myquery($fo, sprintf(
    	"SELECT modulo_id, descripcion FROM modulos WHERE modulo_id NOT IN " .
    	"(SELECT modulo_id FROM acceso WHERE uid=%d AND proyecto_id=%d) ".
    	"AND FIND_IN_SET(modclase_id, '%s') " .
    	"ORDER BY descripcion", $uid, $pid, $tpset),'USRACCMOD-1',true,false,MYASSOC);
    if ($q->isok)
    {
    	$mda = $q->a;
    	$isok = true;
    }
    return $isok;
}

$fo = new lt_form();
$para = array("valor");
if (parms_isset($para, 2))
{
	$uid = $_REQUEST['valor'];
	if ($fo->dbopen())
	{
		if ($fo->usrchk(140, 0) != USUARIO_UNAUTH)
		{
			$fo->encabezado();
			$fo->js("usuario_acceso.js");
			$fo->wait_icon();
			$usra = $pra = $mda = $uta = $acca = array();
			if (useraccess_usrnfo($usra, $uid, $fo))
			{
				$fo->tbl(3,1,'2%','stdpg');
				$fo->tr();
				$fo->th("Nombre");
				$fo->th("Login name");
				$fo->th("Nivel de acceso por defecto");
				$fo->tr();
				$fo->tdc(sprintf("%s %s", $usra['nombres'], $usra['apellidos']));
				$fo->tdc($usra['name']);
				$fo->tdc($usra['usrtipo']);
				$fo->tblx();
				$fo->br('peque');
				
				$pid = $_SESSION['pid'];
				
				$fo->divc("", "msg");
				
				if (useraccess_mods($mda, $uid, $pid, $fo))
				{
					if (useraccess_types($uta, $fo))
					{
						$fo->tbl(3,1,'2%','stdpg');
						
						$fo->tr();
						$fo->tha(array("M&oacute;dulo","Nivel de acceso","-","-"));
						
						$fo->tr();								
						$fo->td();
						$fo->sel("modulo");
						foreach ($mda as $md) $fo->opt($md['modulo_id'], $md['descripcion']);
						$fo->selx();
						
						$fo->td();
						$fo->sel("nivel");
						foreach ($uta as $ut)
						$fo->opt($ut['usertype_id'], $ut['descripcion'], $usra['usertype_id']);
						$fo->selx();
						
						$fo->td();
						$fo->butt("A&ntilde;adir", "usracc_add($uid, $pid)");
						
						$fo->td();
						$fo->frm('usuario_clonar.php');
						$ls0 = new lt_listbox();
						$ls0->n = 'uid_src';
						$ls0->t = 'i';
						///$ls0->tbl = 'usuarios';
						$ls0->custom = 'SELECT a.uid,name FROM acceso a '.
							'LEFT JOIN usuarios b ON a.uid=b.uid '.
							'GROUP BY uid ORDER BY name';
						$ls0->fl_key = 'uid';
						$ls0->fl_desc = 'name';
						$ls0->render($fo->buf);
						$fo->sp();
						$fo->hid('uid_dst', $uid);
						$fo->sub('Clonar');
						$fo->frmx();
						
						$fo->trx();
						$fo->tblx();
						$fo->br("peque");
					}
				}
				
				if (useraccess_load($acca, $uid, $pid, $fo))
				{
					$fo->tbl();
					$fo->tha(array('ID', "M&oacute;dulo","Nivel de acceso","-"));
					foreach ($acca as $acc)
					{
						$fo->tr();
						$fo->tdc($acc['modulo_id'], 2);
						$fo->tdc($acc['modulo'], 3);
						$fo->tdc($acc['nivel'], 3);
						$fo->td();
						$jfn = sprintf("usracc_del(%d,%d,%d)", $uid, $pid, $acc['modulo_id']);
						$fo->butt("Borrar", $jfn);
						$fo->tdx();
						$fo->frmx();
					}
					$fo->tblx();
				}
			}
			
			$fo->hr();
			$fo->par();
			$fo->lnk("ltable_olib_main.php?tabla=usuarios", "Volver al formulario principal");
			$fo->parx();
		}
		mysql_close();
	}
}
else $fo->menuprinc();
$fo->footer();
$fo->show();
?>
