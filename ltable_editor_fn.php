<?php
require_once "ltable_olib.php";

class lteditor_fl
{
	public $tabla='', $n='', $t='c', $l=10, $pd=0, $title='';
	public $dfc='', $dfn=0, $dfd='0000-00-00', $dft='0000-00-00 00:00:00', $dfh='00:00:00', $dfi=0, $dfb=0, $dfm='';
	public $orden=0, $ctrl_type=0, $ls_tbl='', $ls_fl_key='', $ls_fl_desc='', $vcols=10, $vrows=1, $ro=0, $hidden=0;
	public $dt_auto=0, $valid_fn='', $valid_parms='', $mascara='', $funcion='', $ordenv=0, $esdato=1, $enabled=1;
	public $init_fn='', $init_parms='', $autovar=0, $postvar=0, $ls_fl_order='', $ls_custom='', $onkey_fn='';
	public $onkey_parms='', $isup=1, $ls_custom_new='', $dup=0;
	public function __construct($tabla, $n, $t, $l, $title, $ctrl_type, $orden, $ordenv)
	{
		$this->tabla = $tabla;
		$this->n = $n;
		$this->t = $t;
		$this->l = $l;
		$this->title = $title;
		$this->ctrl_type = $ctrl_type;
		$this->orden = $orden;
		$this->ordenv = $ordenv;
	}
	public function vs()
	{
		return sprintf("('%s','%s','%s',%d,%d,'%s','%s',%f, ".
				"'%s','%s','%s',%d,%d,'%s',%d,%d, ".
				"'%s','%s','%s',%d,%d,%d,%d, ".
				"%d,'%s','%s','%s','%s',%d, ".
				"%d,%d,'%s','%s',%d,%d, ".
				"'%s','%s','%s','%s',%d,'%s',%d)",
				$this->tabla, $this->n, $this->t, $this->l, $this->pd, $this->title, $this->dfc, $this->dfn,
				$this->dfd, $this->dft, $this->dfh, $this->dfi, $this->dfb, $this->dfm, $this->orden, $this->ctrl_type,
				$this->ls_tbl, $this->ls_fl_key, $this->ls_fl_desc, $this->vcols, $this->vrows, $this->ro, $this->hidden,
				$this->dt_auto, $this->valid_fn, $this->valid_parms, $this->mascara, $this->funcion, $this->ordenv,
				$this->esdato, $this->enabled, $this->init_fn, $this->init_parms, $this->autovar, $this->postvar,
				$this->ls_fl_order, $this->ls_custom, $this->onkey_fn, $this->onkey_parms, $this->isup, $this->ls_custom_new,
				$this->dup);
	}
}
?>