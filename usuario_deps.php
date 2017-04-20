<?php
require_once 'tienda_class.php';

function usuario_deps_del($fo, $uid)
{
    $isok = false;
    
    if (mysql_query("DELETE FROM acceso WHERE uid=".$uid)!==false)
    {
	    if (mysql_query("DELETE FROM es WHERE uid=".$uid)!==false)
	    {
		    if (mysql_query("DELETE FROM prjusr WHERE uid=".$uid)!==false)
		    {
			    if (mysql_query("DELETE FROM ltable_themes WHERE uid=".$uid)!==false)
			    {
				    $fo->ok("Dependencias d&eacute;biles borradas");
				    $isok = true;
			    }
			    else $fo->qerr("USRDPDL-4");
		    }
		    else $fo->qerr("USRDPDL-3");
	    }
	    else $fo->qerr("USRDPDL-2");
    }
    else $fo->qerr("USRDPDL-1");
    
    return $isok;
}

function usuario_deps_new(lt_form $fo, $uid, $utype)
{
    $isok = false;

    // TODO: acceso por plantilla
    
    $q1 = sprintf("REPLACE INTO prjusr VALUES (%d,7)", $uid);
    if (mysql_query($q1)!==false)
    {
    	$fo->ok("Proyecto por defecto");
    	$isok = true;
    }
    else $fo->qerr("USRDPNW-5");
    
    $q1 = sprintf("REPLACE INTO tndusr VALUES (10, %d)", $uid);
    if (mysql_query($q1)!==false)
    {
    	$fo->ok("Tienda por defecto");
    	$isok = true;
    }
    else $fo->qerr("USRDPNW-6");
    
    if (($rv = lt_registro::crear($fo, 'vendedores', 0, TRUE, FALSE, FALSE, 'uid')))
    {
    	$rv->av('tienda_id', 10);
    	$rv->av('uid', $uid);
    	$rv->av('vend_status', 1);
    	$rv->av('rol', ROL_ADMINISTRATIVO);
    	$rv->av('sysuid', $fo->uid);
    	$rv->av('comision_id1', 2);
    	$rv->av('comision_id2', 1);
    	$rv->av('comision_id3', 1);
    	if ($rv->guardar()) $fo->ok("Vendedor por defecto");
    }

    $tpa = array(2,3,6,7);
    foreach ($tpa as $tp)
    {
    	$tua[] = array(0, $fo->tienda_id, $uid, $tp, lt_fecha::now(), lt_fecha::now(), $fo->uid, $fo->ipaddr, 0);
    }
    if (myquery::i($fo, 'prefactura_tpu', $tua))
    {
    	$fo->ok("Tipos de pedido por defecto");
    	$isok = true;
    }
    else $fo->qerr("USRDPNW-6");
    
    return $isok;
}
?>