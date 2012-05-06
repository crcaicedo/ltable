<?php
class columna
{
	public $n = '', $t = 'c', $title = '', $esdato = false, $visible = true;
	public $fn = '', $ctrl = LISTON_NONE, $recno = 0, $v = '', $caption = '', $fv = "";
	public $align = 0, $style = "";
	
	public function assign($recno, &$row)
	{
		switch ($this->ctrl)
		{
			case LISTON_NONE: 
			case LISTON_TEXTBOX: 
				$this->v = $row[$this->n];
				$this->caption = $this->v;
				break;
			case LISTON_LINK: 
				$this->caption = $row[$this->n]; 
				$this->v = $row[$this->fv];
				break;
		}
		$this->recno = $recno;
	}
	
	public function render(&$buf)
	{
		if ($this->esdato)
		{
			$buf .= sprintf("<input type=\"hidden\" name=\"%s%d\" id=\"%s%d\" " .
				"value=\"%s\">", $this->n, $this->recno, $this->n, $this->recno, 
				$this->v);
		}
		if ($this->visible)
		{
			$salign = "";
			$sty = "border:1px solid black;font-size:8pt;";
			switch ($this->t)
			{
				case 'n': $salign = " align=\"right\""; break; 
				case 'd': $salign = " align=\"center\""; break; 
			}
			switch ($this->align)
			{
				case 1: $salign = " align=\"left\"";
				case 2: $salign = " align=\"right\"";
				case 3: $salign = " align=\"center\"";
			}
			$buf .= sprintf("<td%s style=\"%s\">", $salign, $sty);
			
			switch ($this->ctrl)
			{
				case LISTON_BUTTON:
					$buf .= sprintf("<input type=\"button\" value=\"%s\" " .
						"onclick=\"%s(%s)\">", $this->caption, $this->fn, $this->recno);
					break;
				case LISTON_LINK:
					$buf .= sprintf("<a href=\"#\" onclick=\"%s(%d,'%s')\">%s</a>",
						$this->fn, $this->recno, $this->v, $this->caption);
					break;
				case LISTON_TEXTBOX:
					$txx = new lt_textbox();
					$txx->n = $this->n.$this->recno;
					$txx->t = $this->t;
					$txx->l = 7;
					$txx->pd = 2;
					$txx->valid_fn = $this->fn;
					$txx->valid_parms = $this->recno;
					$txx->assign($this->v);
					$txx->render($buf);
					break;
				default:
					$buf .= $this->t == 'n' ? nes($this->caption):$this->caption;
					break;
			}		
			$buf .= "</td>";
		}
	}
	
	public function __construct($n='', $visible=true, $esdato=false, $fn='', $title='')
	{
		$this->n = $this->fv = $n;
		$this->esdato = $esdato;
		$this->visible = $visible;
		$this->fn = $fn;
		$this->title = $title;
	}
}

class liston
{
	public $n = '', $buf = '', $style = '';
	public $colsz = 0, $rowsz = 0;
	public $col = array();
	
	public function __construct($name='', $style='')
	{
		$this->n = $name;
		$this->style = $style;
	}
	public function addcol($n='', $visible=true, $esdato=false)
	{
		$this->col[$this->colsz++] = new columna($n, $visible, $esdato);	
	}
	public function addcols($anames)
	{
		foreach ($anames as $nm) $this->col[$this->colsz++] = new columna($nm);		
	}
	public function addtitles($atitles)
	{
		for ($ii = 0; $ii < $this->colsz; $ii++)
			$this->col[$ii]->title = $atitles[$ii];		
	}
	public function a2cols($acols)
	{
		foreach ($acols as $col)
		{
			$this->col[$this->colsz++] = new columna($col[0], $col[1], $col[2]);
		}
	}
	public function render(&$buf)
	{
		$recno = 0;
		$this->buf .= "<div";
		if ($this->n != '') $this->buf .= " name=\"$this->n\" id=\"$this->n\"";
		if ($this->style != '') $this->buf .= " style=\"$this->style\"";
		$this->buf .= ">";
		if ($this->rowsz > 0)
		{
			$this->buf .= "<table style=\"border-collapse: collapse; border: 1px solid black;\">";

			$th0 = "<th style=\"border:1px solid black;font-size:8pt;\">";
			$this->buf .= "<tr>";
			foreach ($this->col as $col)
			{
				if ($col->visible) $this->buf .= sprintf("%s%s</th>", $th0, $col->title);
			}
			$this->buf .= "</tr>";
			
			foreach ($this->row as $row)
			{
				$this->buf .= "<tr>";
				foreach ($this->col as &$colx)
				{
					$colx->assign($recno, $row);
					$colx->render($this->buf);
				}
				$recno++;
				$this->buf .= "</tr>";
			}
			$this->buf .= "</table>";
		}
		else
		{
			$this->buf .= '&laquo;Vacio&raquo;'; 
		}
		$this->buf .= "</div>";
				
		$buf .= $this->buf;
	}
	public function query($q)
	{
		$this->rowsz = 0;  
		if (($res = mysql_query($q)) !== false)
		{
			if (mysql_num_rows($res) > 0)
			{
				while (($row = mysql_fetch_assoc($res)) !== false)
				{
					$this->row[$this->rowsz++] = $row;
				}
				mysql_free_result($res);
			}
		}
		else $this->buf .= squeryerror("LISTONQRY-1");
		//else $this->buf .= "Q=$q;".squeryerror(13700);
	}
}

function casilla($fo, $caption='', $inputo, $colspan=0, $inputo2=false, $rowspan=0)
{
	$fo->td(0, $colspan, '', '', $rowspan);
	$fo->buf .= "<p class=\"casilla\">$caption</p>";
	if ($inputo2 !== false)
	{
		$autotrx_old = $fo->autotrx;
		$autotdx_old = $fo->autotdx;
		$fo->autotrx = false;
		$fo->autotdx = false;
		$fo->tbl(0, 0, "0%", "noborde");
		$fo->tr();
		$fo->td();
		$inputo->render($fo->buf);
		$fo->td();
		$inputo2->render($fo->buf);
		$fo->trx();
		$fo->tblx();
		///$fo->autotrx = true;
		///$fo->autotdx = true;
		$fo->autotrx = $autotrx_old;
		$fo->autotdx = $autotdx_old;
	}
	else $inputo->render($fo->buf);
	$fo->tdx();
}

function login_popup($fo)
{
	$fo->encabezado_base();
	$fo->js("login_popup.js");

	$fo->div("loginpopupdiv", 0, "", "position:absolute;z-index:6;top:50px;left:50px;" .
		"visibility:visible;background:white;text-align:center;border:5px solid black;");
	$fo->div("loginpopupdiv_p", 0, "", "text-align:center;margin:10px;background:white;");

	$fo->hdr("Sistema MPRS : Identificaci&oacute;n");
	$fo->wait_icon();
	
	$txt0 = new lt_textbox();
	$txt0->n = "user";
	$txt0->t = 'c';
	$txt0->l = $txt0->vcols = 32;
	
	$txt1 = new lt_textbox();
	$txt1->n = "passwd";
	$txt1->t = 'c';
	$txt1->l = $txt1->vcols = 16;
	$txt1->ctrl_type = LTO_PASSWORD;
	$txt1->funcion = "E";
	$txt1->onkey_fn = "login_popup_enter"; 
	
	$fo->tbl(3, -1, "2%", "stdpg4a");
	
	$fo->tr();
	$fo->th("Usuario",2);
	$fo->td();
	$txt0->render($fo->buf);
	
	$fo->tr();
	$fo->th("Contrase&ntilde;a",2);
	$fo->td();
	$txt1->render($fo->buf);
	
	$fo->trx();
	$fo->tblx();
	
	$fo->par(3);
	$fo->butt("Identificarse", "login_popup_chk();");
	//$fo->sp();
	//$fo->butt("Salir", "login_popup_out();");
	$fo->parx();
	
	$fo->divx();
	$fo->divx();
}

function wlog_show($fo, $tabla, $valor)
{
	$fo->div($tabla."_wlogshowdiv");
	$q = sprintf("SELECT msg, CONCAT(utes(ltlog.creado), ' ', TIME(ltlog.creado)) AS fecha, name " .
		"FROM ltlog " .
		"LEFT JOIN usuarios AS usr ON usr.uid=ltlog.uid " .
		"WHERE tabla='%s' AND valor='%s'", $tabla, $valor);
	if (($res = mysql_query($q)) !== false)
	{
		if (mysql_num_rows($res) > 0)
		{
			$fo->tbl(3, -1, "2%", "stdpg");
			$fo->tr();
			$fo->tha(array("Usuario", "Fecha", "Operaci&oacute;n"));
			while (($ox = mysql_fetch_object($res)) !== false)
			{		
				$fo->tr();
				$fo->tdc($ox->name);
				$fo->tdc($ox->fecha);
				$fo->tdc($ox->msg, 0, 0, "cursiva");
			}
			$fo->trx();
			$fo->tblx();
		}
		else $fo->parc("&laquo;No hay registro de operaciones&raquo;", 3, "nohay");		
	}
	else $fo->qerr("LTLOG-1");
	$fo->divx();
}

define("MYASSOC", 1);
define("MYOBJECT",2);
define("MYROW", 3);
class myquery
{
	public $r = false, $sz = 0, $asz = 0, $a = array(), $isok = false, $q="", $id = 0;
	function __construct(lt_form $fo, $query, $errhint="", $novacio=true, $alterdata=false, $returntype=MYOBJECT)
	{
		$this->r = false;
		$this->a = array();
		$this->asz = 0;
		$this->q = $query;
		//$fo->parc($query);
		if (($res = mysql_query($query)) !== false)
		{
			if ($alterdata)
			{ 
				$this->sz = mysql_affected_rows();
				$this->id = mysql_insert_id();
			}
			else
			{
				$this->sz = mysql_num_rows($res);
				if ($returntype==MYOBJECT)
				{
					while (($otmp = mysql_fetch_object($res)) !== false)
					{
						$this->a[$this->asz] = clone $otmp;
						$this->r = $this->a[$this->asz];
						$this->asz++;
					}
				}
				if ($returntype==MYASSOC)
				{
					while (($row = mysql_fetch_assoc($res)) !== false)
					{
						$this->a[$this->asz] = $row;
						$this->r = $this->a[$this->asz];
						$this->asz++;
					}
				}
				if ($returntype==MYROW)
				{
					while (($row = mysql_fetch_row($res)) !== false)
					{
						$this->a[$this->asz] = $row;
						$this->r = $this->a[$this->asz];
						$this->asz++;
					}
				}
			}
			if ($novacio) $this->isok = $this->sz > 0; else $this->isok = true;
			if (!$alterdata) mysql_free_result($res);
		}
		else $fo->qerr($errhint);
	}
}
?>