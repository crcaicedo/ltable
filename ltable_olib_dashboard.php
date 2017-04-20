<?php
class lt_dashboard_slot
{
	public $dashboard_id=0, $slot_id=0, $tipo=0, $contenido="", $titulo="", $parametros='';
	public $editable=1, $uid=0, $alto=0, $ancho=0;
	public $js_a=array(), $js_sz=0;
	function __construct()
	{
		$this->uid = $_SESSION["uid"];
	}
	public function show(lt_form $fo, &$jsld)
	{
		$fo->td();
		if ($this->tipo != 0)
		{
			$fo->par(3, "", "margin:5px;");
			if ($this->editable == 1)
			{
				$fo->lnk_js(sprintf("lt_dashboard_slot_clr(%d,%d);", $this->dashboard_id, $this->slot_id), 
					"&times;", "Quitar contenido de este recuadro", "text-decoration:none");
				$fo->sp();
			}
			$fo->span($this->titulo, "negrita");
			$fo->parx();
		}
		else
		{
			$fo->par(3, "", "margin:5px;");
			$fo->lnk_js(sprintf("lt_dashboard_edit(%d,%d);", $this->dashboard_id, $this->slot_id), 
				"Agregar...", "Agregar contenido a este recuadro", "text-decoration:none;");
			$fo->parx();
		}
		$ns = sprintf("dashboard_slot%d_%d", $this->dashboard_id, $this->slot_id);
		$st = sprintf("overflow:auto;width:%dpx;height:%dpx;margin:10px;", $this->ancho, $this->alto);
		$fo->div(sprintf("dashboard_decslot%d_%d", $this->dashboard_id, $this->slot_id), 0, "dashboard");
		if ($this->tipo == 0) $fo->divc("", $ns, 0, "", $st);
		if ($this->tipo == 1)
		{
			$fo->div($ns, 0, "", $st);
			$jsld .= sprintf(" lt_dashboard_plugin(%d,%d,'%s','%s');", 
					$this->dashboard_id, $this->slot_id, $this->contenido, $this->parametros);
			$fo->divx();
		}
		if ($this->tipo == 2) $fo->buf .= sprintf("<iframe src=\"%s\" scrolling=\"auto\" id=\"%s\" ".
			"style=\"%s\">Su navegador es obsoleto</iframe>", $this->contenido, $ns, $st);
		$fo->divx();
		$fo->tdx();
	}
}

class lt_dashboard
{
	private $dasboard_id=0, $slots=array(), $slots_sz=0;
	private $filas=0, $columnas=0, $alto=0, $ancho=0;
	function __construct(lt_form $fo, $dashboard_id=0)
	{
		$this->dashboard_id=$dashboard_id;
		if ($this->load($fo)) $this->show($fo);
	}
	private function load(lt_form $fo)
	{
		$isok = false;
		$qa = new myquery($fo, sprintf("SELECT filas,columnas,alto,ancho ".
			"FROM dashboard WHERE dashboard_id=%d", $this->dashboard_id),
			"DASHBOARD-1");
		if ($qa->isok)
		{
			$this->columnas = $qa->r->columnas;
			$this->filas = $qa->r->filas;
			$this->alto = $qa->r->alto;
			$this->ancho = $qa->r->ancho;

			// load slots
			$qb = new myquery($fo, sprintf("SELECT dashboard_id, slot_id, uid, tipo, titulo, contenido, ".
				"alto, ancho, editable, js_inc, parametros ".
				"FROM dashboard_det a ".
				"LEFT JOIN dashboard_plugins b ON a.dashplugin_id=b.dashplugin_id ".
				"WHERE dashboard_id=%d AND uid=%d",
				$this->dashboard_id, $_SESSION['uid']), "DASHBOARD-2");
			if ($qb->isok)
			{
				$maxslot = 0;
				foreach ($qb->a as $rg)
				{
					$this->slots[$this->slots_sz] = new lt_dashboard_slot();
					$this->slots[$this->slots_sz]->dashboard_id = $this->dashboard_id;
					$this->slots[$this->slots_sz]->slot_id = $rg->slot_id;
					$this->slots[$this->slots_sz]->tipo = $rg->tipo;
					$this->slots[$this->slots_sz]->titulo = $rg->titulo;
					$this->slots[$this->slots_sz]->contenido = $rg->contenido;
					$this->slots[$this->slots_sz]->alto = $rg->alto > 0 ? $rg->alto:$this->alto;
					$this->slots[$this->slots_sz]->ancho = $rg->ancho > 0 ? $rg->ancho:$this->ancho;
					$this->slots[$this->slots_sz]->editable = $rg->editable;
					$this->slots[$this->slots_sz]->parametros = $rg->parametros;
					if (!empty($rg->js_inc))
					{
						$this->slots[$this->slots_sz]->js_a = explode(',', $rg->js_inc);
						$this->slots[$this->slots_sz]->js_sz = count($this->slots[$this->slots_sz]->js_a);
					}
					if ($rg->slot_id > $maxslot) $maxslot = $rg->slot_id;
					$this->slots_sz++;
				}
				$this->slots[$this->slots_sz] = new lt_dashboard_slot();
				$this->slots[$this->slots_sz]->dashboard_id = $this->dashboard_id;
				$this->slots[$this->slots_sz]->slot_id = $maxslot + 1;
				$this->slots[$this->slots_sz]->alto = $rg->alto > 0 ? $rg->alto:$this->alto;
				$this->slots[$this->slots_sz]->ancho = $rg->ancho > 0 ? $rg->ancho:$this->ancho;
				$this->slots_sz++;

				$isok = true;
			}
			else $fo->err("DASHBOARD-4", "Slots no definidos");
		}
		else $fo->err("DASHBOARD-3", "Dashboard no definido");
		return $isok;
	}
	private function show(lt_form $fo)
	{
		if ($this->slots_sz <= 0) return;

		foreach ($this->slots as &$slotx)
		{
			if ($slotx->js_sz > 0)
			{
				foreach ($slotx->js_a as $js) $fo->js($js);
			}
		}

		$jsld = "function lt_dashboard_load() {";

		$fo->autotblx = false;
		$fo->autotrx = false;
		$fo->autotdx = false;
		$fo->autodivx = false;

		$fo->div(sprintf("ltdashboardedit%d", $this->dashboard_id), 0, "", "position:absolute;z-index:5;".
			"top:100px;left:300px;display:none;background:white;text-align:center;border:5px solid black; ".
			"-moz-box-shadow: 10px 10px 5px #888; -webkit-box-shadow: 10px 10px 5px #888; box-shadow: 10px 10px 5px #888;");

		$ls0 = new lt_listbox();
		$ls0->n = "ltdsedit_tipo".$this->dashboard_id;
		$ls0->rowsource_type = 1;
		$ls0->rowsource = array(array("1","Plugin"),array("2","P&aacute;gina web"));
		$ls0->valid_fn = "lt_dashboard_edit_tipo";
		$ls0->valid_parms = $this->dashboard_id;
		$ls0->assign(1);
		
		$ls1 = new lt_listbox();
		$ls1->n = "ltdsedit_plugin".$this->dashboard_id;
		$ls1->custom = sprintf("SELECT dashplugin_id,descripcion FROM dashboard_plugins ".
			"WHERE modulo_id IN (SELECT modulo_id FROM acceso WHERE uid=%d) ".
			"ORDER BY descripcion", $_SESSION['uid']);
		$ls1->fl_key = "dashplugin_id";
		$ls1->fl_desc = "descripcion";

		$tx0 = new lt_textbox();
		$tx0->n = "ltdsedit_url".$this->dashboard_id;
		$tx0->vcols = 70;
		$tx0->l = 512;
		$tx0->ro = true;
		
		$tx1 = new lt_textbox();
		$tx1->n = "ltdsedit_titulo".$this->dashboard_id;
		$tx1->vcols = 30;
		$tx1->l = 30;
		$tx1->ro = true;

		$fo->tbl(3, 0, "2%", "", "margin:5px;");
		$fo->tr();
		$fo->th("Tipo de contenido",2);
		$fo->td();
		$ls0->render($fo->buf);
		$fo->hid("ltdsedit_slot".$this->dashboard_id, 0);
		
		$fo->tr();
		$fo->th("Plugin",2);
		$fo->td();
		$ls1->render($fo->buf);

		$fo->tr();
		$fo->th("Direcci&oacute;n p&aacute;gina web (URL)",2);
		$fo->td();
		$tx0->render($fo->buf);

		$fo->tr();
		$fo->th("T&iacute;tulo",2);
		$fo->td();
		$tx1->render($fo->buf);

		$fo->tr();
		$fo->td(3,2);
		$fo->lnk_js("lt_dashboard_edit_save($this->dashboard_id)", "Agregar", 
			"Agregar el contenido especificado", "sinsub");
		$fo->sp();
		$fo->lnk_js(sprintf("\$('ltdashboardedit%d').style.display='none';", 
			$this->dashboard_id), "Descartar cambios",
			"Cerrar esta ventana sin guardar cambios", "sinsub");
		
		$fo->trx();
		$fo->tblx();

		$fo->divx();

		$fo->tbl(3,-1,"2%",'');
		$fo->tr();
		$columna = 1;
		foreach ($this->slots as $slot)
		{
			$slot->show($fo, $jsld);
			$columna++;
			if ($columna > $this->columnas)
			{
				$fo->trx();
				$columna = 1;
				$fo->tr();
			}
		}
		$fo->trx();
		$fo->tblx();
		
		$jsld .= " } ";
		$fo->jsi($jsld);
	}
}
?>
