<?php
require_once "ltable_olib.php";

class ltable_fl
{
	public $tabla='', $n='', $t='c', $pd=0, $title='', $orden=0, $ordenv=0;
	public $dfc='', $dfn=0, $dfd=0, $dft=0, $dfh=0, $dfi=0, $dfb=0, $dfm='';
	public $ctrl_type=0, $ls_tbl='', $ls_fl_key='', $ls_fl_desc='',$ls_fl_order='', $ls_custom='', $ls_custom_new='';
	public $vcols=0, $vrows=1, $ro=0, $hidden=0, $dt_auto=0;
	public $valid_fn='', $valid_parms='', $mascara='', $funcion='', $esdato=1, $enabled=1;
	public $init_fn='', $init_parms='', $autovar=0, $postvar=0;
	public $onkey_fn='', $onkey_parms='', $isup=1, $dup=0;
	function __construct($rg)
	{
		$this->tabla = $rg->table_name;
		$this->n = $rg->column_name;
		$this->title = $rg->column_comment;
		$this->l = $rg->character_maximum_length;
		$this->vcols = $rg->character_maximum_length;
		$this->orden = $rg->ordinal_position;
		$this->ordenv = $rg->ordinal_position;
		switch ($rg->data_type)
		{
			case 'char':
			case 'varchar':
				$this->t = 'c'; break;
			case 'int':
			case 'tinyint':
			case 'smallint':
				$this->t = 'i'; break;
			case 'text':
				$this->t = 'm'; $this->l = 10; $this->vcols = 30; $this->vrows = 3; break;
			case 'datetime':
				$this->t = 't'; break;
			case 'date':
				$this->t = 'd'; $this->l = 10; $this->valid_fn = 'lt_chkfecha'; break;
			case 'double':
				$this->t = 'n'; $this->l = 7; $this->pd = 2; break;
			case 'decimal':
				$this->t = 'n'; $this->l = $rg->numeric_precision; $this->pd = $rg->numeric_scale; break;
		}
	}
	public function vs()
	{
		// tabla,n,t,l,pd,title,dfc,dfn,dfd,dft,dfh,dfi,dfb,dfm,
		// orden,ctrl_type,ls_tbl,ls_fl_key,ls_fl_desc,vcols,vrows,ro,hidden,
		// dt_auto,valid_fn,valid_parms,mascara,funcion,ordenv,esdato,enabled,
		// init_fn,init_parms,autovar,postvar,ls_fl_order,ls_custom,onkey_fn,onkey_parms,isup,ls_custom_new,dup
		return sprintf(",('%s','%s','%s',%d,%d,'%s', ".
			"'%s',%f,%d,%d,%d,%d,%d,'%s', ".
			"%d,%d,'%s','%s','%s', ".
			"%d,%d,%d,%d,%d,'%s','%s', ".
			"'%s','%s',%d,%d,%d, ".
			"'%s','%s',%d,%d,'%s','%s', ".
			"'%s','%s',%d,'%s',%d)",
			$this->tabla, $this->n, $this->t, $this->l, $this->pd, mysql_real_escape_string($this->title),
			$this->dfc, $this->dfn, $this->dfd, $this->dft, $this->dfh, $this->dfi, $this->dfb, $this->dfm,
			$this->orden, $this->ctrl_type, $this->ls_tbl, $this->ls_fl_key, $this->ls_fl_desc, 
			$this->vcols, $this->vrows, $this->ro, $this->hidden, $this->dt_auto, $this->valid_fn, $this->valid_parms, 
			$this->mascara, $this->funcion, $this->ordenv, $this->esdato, $this->enabled,
			$this->init_fn, $this->init_parms, $this->autovar, $this->postvar, $this->ls_fl_order, $this->ls_custom, 
			$this->onkey_fn, $this->onkey_parms, $this->isup, $this->ls_custom_new, $this->dup);
	}
}

$fo = new lt_form();
$para = array("db","tabla");
if (parms_isset($para,2))
{
	$db = $_REQUEST['db'];
	$tabla = $_REQUEST["tabla"];
	if ($fo->dbopen())
	{
		$fo->parc(chora());
		if ($fo->usrchk(1,1) !== USUARIO_UNAUTH)
		{
			$qa = new myquery($fo, sprintf("SELECT table_name, column_name, data_type, ".
				"character_maximum_length, column_comment, ordinal_position, numeric_precision, numeric_scale ".
				"FROM information_schema.columns WHERE table_schema='%s' AND table_name='%s'", $db, $tabla),
				"LTIMPORT-1");
			$fo->parc($qa->q);
			if ($qa->isok)
			{	
				$qb = new myquery($fo, sprintf("SELECT * FROM information_schema.columns ".
					"WHERE table_name='ltable_fl' AND table_schema='%s'", $db), "LTIMPORT-2");
				$fo->parc($qb->q);
				if ($qb->isok)
				{
					$vs = "";
					foreach ($qa->a as $cl)
					{
						$fl = new ltable_fl($cl);
						//$fo->parc(print_r($fl,true));
						$vs .= $fl->vs();
					}
					if (mysql_select_db($db))
					{
						$qc = new myquery($fo, sprintf("INSERT INTO ltable_fl VALUES %s",
							substr($vs, 1)), "LTIMPORT-3", true, true);
						if ($qc->isok) $fo->ok("Importacion completada"); else $fo->parc($qc->q);
					}
					else $fo->qerr("LTIMPORT-4");
				}
			}
		}
	}
}
$fo->show();
?>
