<?php
require_once "ltable_olib.php";

class asunto
{
	public $asunto_id=0, $byid=true, $tabla = "", $key_n="", $key_v = 0;
	public $fs, $f;
	
	public function __construct($asunto_id=0, $byid=true, 
		$tabla="", $key_n="", $key_v=0)
	{
		$this->asunto_id = $asunto_id;
		$this->byid = $byid;
		$this->tabla = $tabla;
		$this->key_n = $key_n;
		$this->key_v = $key_v;
		
		$this->fs = new lt_field_set("asuntos", "asunto_id", $asunto_id);
		$this->fs->add("asunto_id", 'i', 0, $asunto_id);		
		$this->fs->add("cliente_id", 'i', 0, 0);
		$this->fs->add("asuntot_id", 'i', 0, 0);
		$this->fs->add("estatus", 'i', 6, 6);
		$this->fs->add("apert_fe", 'd', 0, time());
		$this->fs->add("apert_hr", 'h', 0, time());
		$this->fs->add("cierre_fe", 'd', 0, 0);
		$this->fs->add("cierre_hr", 'h', 0, 0);
		$this->fs->add("tabla", 'c', "", $tabla);
		$this->fs->add("key_n", 'c', "", $key_n);
		$this->fs->add("key_v", 'i', 0, $key_v);
		$this->fs->add("observ", 'm');
		$this->fs->add("crea_dt", 't', 0, 0);
		$this->fs->add("mod_dt", 't', 0, 0);
		$this->fs->add("crea_uid", 'i', 0, $_SESSION['uid']);
		$this->fs->add("mod_uid", 'i', 0, $_SESSION['uid']);
		$this->fs->add("crea_ip", 'c', "", $_SERVER['REMOTE_ADDR']);
		$this->fs->add("mod_ip", 'c', "", $_SERVER['REMOTE_ADDR']);
		
		$this->f = &$this->fs->fl;
	}
	 
	public function load($fo)
	{
		$isok = false;
		if ($this->byid) $isok = $this->fs->load($fo);
		else $isok = $this->fs->load($fo, sprintf("tabla='%s' AND key_v=%d", 
			$this->tabla, $this->key_v));
		return $isok;
	}
	
	public function save($fo)
	{
		$isok = false;

		if ($this->fs->nuevo)
		{
			$this->f['crea_dt'] = time();
			$this->f['crea_uid'] = $_SESSION['uid'];
			$this->f['crea_ip'] = $_SERVER['REMOTE_ADDR'];			
		}
		$this->f['mod_dt'] = time();
		$this->f['mod_uid'] = $_SESSION['uid'];
		$this->f['mod_ip'] = $_SERVER['REMOTE_ADDR'];
		
		if ($this->byid) $xc = ""; 
		else $xc = sprintf("tabla='%s' AND key_v=%d", $this->tabla, $this->key_v);
		if ($this->fs->save($fo, $xc))
		{
			$isok = $this->fs->save($fo, $xc, "asuntos_seg", true);
		}
		
		return $isok;
	}
	
	public function publish($fo)
	{
		// TODO: email solicitud to promotor, promotor.supervisor		
	}
	
	public function menu($fo)
	{
		if ($this->byid)
		{
			$bsurl = sprintf("javascript:crm_chst(%d,", $this->asunto_id);
		}
		else
		{
			$bsurl = sprintf("javascript:crm_chst(0,'%s','%s','%d',", $this->tabla,
				$this->key_n, $this->key_v);
		}
		$sesa = array(0=>"Indefinido", 1=>"Abierto", 2=>"Cerrado", 3=>"Delegado",
			4=>"Escalado", 5=>"Cancelado", 6=>"Indefinido"); 
		$fo->buf .= "Estado actual: <i>".$sesa[$this->f['estatus']->v]."</i><br/>";
		if ($this->f['estatus']->v == 1)
		{
			$fo->lnk($bsurl."2);", "Cerrar");
			$fo->sp();
			$fo->lnk($bsurl."3);", "Delegar");
			$fo->sp();
			$fo->lnk($bsurl."4);", "Escalar");
			$fo->sp();
			$fo->lnk($bsurl."5);", "Cancelar");			
		}
	}
}

function asuntos_lista($fo, $uid)
{
	$hoy = fecha();
	$shoy = dtoms($hoy);

	$fo->div('asuinidiv');
	
	$fo->parc("Asuntos abiertos de hoy", 3, 'titulo');
	$q = sprintf("SELECT * FROM asuntos WHERE uid=%d AND estatus=%d AND fecha='%s'",
		$uid, 1, $shoy);
	if (($res = mysql_query($q)) !== false)
	{
		// marcar ACTIVO
		if (mysql_num_rows() > 0)
		{
			$fo->tbl(3, -1, '2%', 'stdpg');
			while (($row = mysql_fetch_assoc($res)) !== false)
			{
				$fo->tr();
				$fo->tdc($row['cliente']);
				$fo->tdc($row['asunto_tipo']);
				$fo->tdc($row['observ']);

	// acciones ->(button -> pop-up):
	// historial-> fecha,hora,accion,promotor 
	// acción -> acción tomada(tipo_asunto), [momento]
	// cerrar -> estado_final_negociación(tipo_asunto), [momento]
	// postponer -> estado_actual_negociación(tipo_asunto), razones_para_postponer, 
	//    fecha reinicio, [momento]
	// delegar -> razones_delegacion, [momento] 
	//    (SI existe USUARIO.nivel(tipo_asunto) >= ESTEUSR.nivel(tipo_asunto))
	// escalar -> razones_escalar, [momento] 
	//    (SI existe USUARIO.nivel(tipo_asunto) < ESTEUSR.nivel(tipo_asunto))
				$fo->trx();
			}
			$fo->tblx();
		}
		else $fo->parc("&laquo;No hay asuntos&raquo;", 3, 'nohay');
		mysql_free_result($q);
	}
	else $fo->qerr("asuini-1");

	// otros asuntos_abiertos
	// listar by status=iniciado AND fecha!=$hoy
	
	$fo->divx();
	
	// asuntos_postpuestos($fo, $uid)
	//		abrir, cerrar, delegar, escalar 
				
	// asunto_nuevo($fo, $uid)
}
?>