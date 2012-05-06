<?php
class lt_form
{
	public $buf = '', $form_name = '', $usrnm = '(DESCONOCIDO)', $usrlv = 6;
	public $tropen=false, $tdopen=false, $thopen=false, $frmopen=false; 
	public $tblopen=false, $paropen=false, $divopen=false;
	public $autotrx=true, $autotdx=true, $autothx=true, $autofrmx=true;
	public $autotblx=true, $autoparx=true, $autodivx=false;
	public $bHeader = true;
	public $tbl_id = 0, $tbl_nr = array(0,0,0,0,0,0,0,0,0,0), $tbl_interhigh = array(0,0,0,0,0,0,0,0);
	public $lf = false, $toemail = false, $rpt_id=0, $eml_default = "", $eml_subject = "", $eml_start=0, $eml_stop=0;
	public $dbopened=false, $dbhandler=false;
	
	public function __construct($ismain=true, $bHeader=false)
	{
		setlocale(LC_MONETARY, "es_VE");
		date_default_timezone_set("America/Caracas");
		if ($ismain)
		{
			if (!isset($_SESSION['buggy']))
			{
				session_start();
				$_SESSION['buggy'] = rand(1,100000);
			}
		} 
		else
		{
			$this->bHeader = $bHeader;
		}

		$this->tbl_nr = array(0=>0,1=>0,2=>0,3=>0);
		$this->tbl_interhigh = array(0=>false,1=>false,2=>false,3=>false);
		
		$this->rpt_id = time();
		if ($this->toemail) $this->eml_subject = sprintf("Informacion sobre %s", $_SESSION["pnm"]);
		///$this->eml_fn = sprintf("ltemltmp%d.html", time());
	}
	public function http_header($html_content=true)
	{
		header("Expires: Mon, 27 Oct 1977 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		if ($html_content) header("Content-Type: text/html; charset=utf-8");
	}
	public function dbclose()
	{
		if ($this->dbopened)
		{
			if ($this->dbhandler !== false) @mysql_close($this->dbhandler);
			$this->dbopened = false;
		}
	}
	public function dbopen()
	{
		$isok = false;
		$this->dbclose();
		lt_global();
		$useini = true;
		if (isset($_SESSION['useini'])) $useini = $_SESSION['useini']; 
		if ($useini)
		{
			$mp = new mprs_inifile($_SESSION['inifile']);
			if ($mp->load())
			{
				$dburl = $mp->dburl;
				$dbuser = $mp->user;
				$dbpasswd = $mp->passwd;
				$dbname = $mp->dbname;
			}
			else $this->err("LTDBOPEN-1", "No pude leer configuracion");
		}
		else
		{
			$dburl = $_SESSION['dburl'];
			$dbuser = $_SESSION['dbuser'];
			$dbpasswd = $_SESSION['dbpasswd'];
			$dbname = $_SESSION['dbname'];
		}
		if (($this->dbhandler = mysql_connect($dburl, $dbuser, $dbpasswd, false, MYSQL_CLIENT_COMPRESS)) !== false)
		{
			if (mysql_select_db($dbname) !== false)
			{
				mysql_query("set collation_connection = @@collation_database");
				$this->dbopened = true;
				$isok = true;
			}
			else $this->err("LTDBOPEN-3", mysql_error());
		}
		else $fo->err("LTDBOPEN-2", mysql_error());
		return $isok;
	}
	public function show()
	{
		if ($this->toemail && $this->dbopened)
		{
			$qeml = new myquery($this, sprintf("REPLACE INTO ltable_emltmp VALUES (%d,%d,'%s')",
				$_SESSION["uid"], $this->rpt_id, mysql_real_escape_string(substr($this->buf, 0, $this->eml_start).
				substr($this->buf, $this->eml_stop))), "LTEMLSV-1", true, true);
			///@file_put_contents("/tmp/".$this->eml_fn, substr($this->buf, 0, $this->eml_start).substr($this->buf, $this->eml_stop));
		}
		$this->dbclose();
		if ($this->bHeader) $this->http_header();
		echo $this->buf;
	}
	public function write($fname)
	{
		$isok = false;
		
		if (($ff = fopen($fname, "w")) !== false)
		{
			$buf = encabezado_lynx_load() . $this->buf;
			if (fwrite($ff, $buf) !== false) $isok = true;
			fclose($ff);
		}
		
		return $isok;
	}
	public function tojson($isok, &$reto, $sgurl=LTMSG_HIDE)
	{
		if (!$isok && $sgurl!="") $this->seguir("Seguir", $sgurl);
		$reto['msg'] = $this->buf;
		$this->http_header();
		header("Content-type: application/json");
		if (!$isok) header("HTTP/1.0 419 Peticion incorrecta.");
		echo json_encode($reto);
		$this->dbclose();
	}
	function encabezado_base($tout=-1, $lapa='', $titulo=SOFTNAME, $add_css=array())
	{
		if ($this->toemail)
		{
			$this->buf .= "<html><head><style>body{margin-left:1%;margin-right:1%;color:black;background:white;".
				"font-family: Verdana,Sans Serif;font-size:10pt;}\n";
			$this->buf .= @file_get_contents("mprs_std.css")."</style>";
		}
		else
		{
			$refre = '';
			if ($tout >= 0)
			{
				if (strlen($lapa) > 0)
				$refre = sprintf("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"%d; URL=%s\">",
				$tout, $lapa);
				else $refre = sprintf("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"%d\">", $tout);
			}
			$tema = isset($_SESSION['tema']) ? $_SESSION['tema']: 'default';
			$css = "mprs.". $tema .".css";
			if (!file_exists($css)) $css = "mprs.default.css";
			$this->buf .= "<html><head>".
			"<link href=\"$css\" type=\"text/css\" rel=\"stylesheet\">".
			"<link href=\"reserv.css\" type=\"text/css\" rel=\"stylesheet\">".
			"<link href=\"ltable_rpt.css\" type=\"text/css\" rel=\"stylesheet\">".
			"<link href=\"mprs_std.css\" type=\"text/css\" rel=\"stylesheet\">";
			foreach ($add_css as $cssfn)
			$this->buf .= "<link href=\"$cssfn\" type=\"text/css\" rel=\"stylesheet\">";
			$this->buf .= $refre;
			$this->buf .= "<style>all.clsMenuItemNS</style>".
			"<style>.clsMenuItemIE{text-decoration: none; font: bold 12px Arial; " .
			"color: white; cursor: hand; z-index:100}</style>".
			"<style>.clsMenuItemIEX{text-decoration: none; font: bold 16px Arial; " .
			"color: white; cursor: hand; z-index:100}</style>".
			"<style>#MainTable A:hover {color: yellow;}</style>".
			"<script language=\"JavaScript\">".
			"var keepstatic=0; ".
			"var menucolor=\"#000000\";".
			"var submenuwidth=150;</script>".
			"<noscript><p align=center><b>HABILITE POR FAVOR EL SOPORTE JAVASCRIPT EN SU BROWSER.</b></noscript>".
			"<title>".$titulo."</title>";
		}
		$this->buf .= "<script language=\"JavaScript\" src=\"prototype.js\"></script>";
		$this->buf .= "<script language=\"JavaScript\" src=\"ltable_edit.js\"></script>";
		$this->buf .= "<script language=\"JavaScript\" src=\"cal2.js\"></script>";
		$this->buf .= "</head>";
	}
	public function encabezado_incrustado()
	{
		$this->buf .= "<html><head><style>";
		$this->buf .= @file_get_contents("mprs_std.css");
		$this->buf .= "</style></head>";
	}
	public function encabezado($pryro=false, $add_css=array())
	{
		$this->encabezado_base(-1, '', SOFTNAME, $add_css);
		$this->buf .= "<body><script language=\"JavaScript\" src=\"menu.js\"></script>";		
		menu_build($this->buf, $_SESSION['uid'], $_SESSION['pid']);		
		$this->buf .= "<script language=\"JavaScript\">showToolbar();</script>".
			"<script language=\"JavaScript\">function UpdateIt(){" .
			"if (ie&&keepstatic&&!opr6) " .
			"document.all[\"MainTable\"].style.top = " .
			"document.body.scrollTop; setTimeout(\"UpdateIt()\", 200);}".
			"UpdateIt();</script><p><br></p>".
			loguito(true, $this->usrnm, $this->usrlv, $_SESSION['pnm'], $pryro);
		$this->msg();			
	}
	public function footer()
	{
		$this->buf .= "</body></html>";
	}
	public function body($class="", $style="")
	{
		$this->buf.="<body";
		if ($class!="") $this->buf.=" class=\"$class\"";
		if ($style!="") $this->buf.=" style=\"$style\"";
		$this->buf.=">";
	}
	public function bodyx()
	{
		$this->buf .= "</body>";
	}
	public function span($caption="", $class="", $style="", $id="", $title="")
	{
		$this->buf .= "<span";
		if ($class!="") $this->buf.=" class=\"$class\"";
		if ($style!="") $this->buf.=" style=\"$style\"";
		if ($id!="") $this->buf.=" id=\"$id\"";
		if ($title!="") $this->buf.=" title=\"$title\"";
		$this->buf .= ">$caption</span>";
	}
	public function sp($nrepeats=1, $elstr="&nbsp;")
	{
		$this->buf .= str_repeat($elstr, $nrepeats);
	}
	public function tblx()
	{
		$this->buf .= "</table>";
		$this->tblopen = false;
		if ($this->lf) $this->buf .= "\n";
	}
	public function tbl($align=3, $border=0, $padd='5%', $clase='', $estilo='', $interhigh=false,
		$nombre="")
	{
		$this->tbl_id++;
		$this->tbl_nr[$this->tbl_id] = 0;
		
		$this->tbl_interhigh[$this->tbl_id] = $interhigh;
		if ($clase == "stdpg" || $clase == "stdpg4") $this->tbl_interhigh[$this->tbl_id] = true;

		if ($this->autotblx && $this->tblopen) $this->tblx();
		$this->tblopen = true;
		$this->buf .= "<table";
		if ($border != -1) $this->buf .= " border=\"$border\"";
		if ($padd != '') $this->buf .= " cellpadding=\"$padd\""; 
		if ($align == 1) $this->buf .= " align=\"left\"";
		if ($align == 2) $this->buf .= " align=\"right\"";
		if ($align == 3) $this->buf .= " align=\"center\"";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($nombre != "") $this->buf .= " name=\"$nombre\"id=\"$nombre\"";
		$this->buf .= ">";		
		if ($this->lf) $this->buf .= "\n";
	}
	public function frmx()
	{
		$this->buf .= "</form>";
		$this->form_name = '';
		$this->frmopen = false;
	}
	public function frm($form_action, $newwnd=false, $form_name="", $enctype="")
	{
		if ($this->autofrmx && $this->frmopen) $this->frmx();
		if ($newwnd) $snew = " target=\"_blank\""; else $snew = "";
		if ($enctype != "") $senc = " enctype=\"".$enctype."\""; else $senc = "";
		$this->frmopen = true;
		if ($form_name != '') $this->form_name = $form_name;
		if ($this->form_name != '')
		{
			$this->buf .= sprintf("<form id=\"%s\" name=\"%s\" action=\"%s\" " .
				"method=\"post\"%s%s>",
				$this->form_name, $this->form_name, $form_action, $snew, $senc);
		}
		else $this->buf .= "<form action=\"$form_action\" method=\"post\"$snew>";
	}
	public function hid($nombre, $valor)
	{
		$this->buf .= "<input type=\"hidden\" name=\"$nombre\" id=\"$nombre\" " .
			"value=\"$valor\">";	
	}
	public function butt($caption='', $onclickfn='', $name='', $enabled=true)
	{
		$this->buf .= "<input type=\"button\" value=\"$caption\"";
		if ($onclickfn != '') $this->buf .= sprintf(" onclick=\"%s\"", $onclickfn);
		if ($name != '') $this->buf .= sprintf(" id=\"%s\" name=\"%s\"", $name, $name);
		if (!$enabled) $this->buf .= " disabled";
		$this->buf .= ">";
	}
	public function err($ecode='', $emsg='')
	{
		$ss = sprintf("<p align=\"center\"><code><i><b>[%s]</b> Error: %s</i></code></p>", $ecode, $emsg);
		$this->buf .= $ss;
		return $ss;
	}
	public function xerr($ecode='', $emsg='')
	{
		$ss = sprintf("{-1}<p align=\"center\"><code><i><b>[%s]</b> Error: %s</i></code></p>", $ecode, $emsg);
		$this->buf .= $ss;
		return $ss;
	}
	public function xok($okmsg='')
	{
		$ss = sprintf("{00}<p align=\"center\">%s <b>OK</b></p>", $okmsg);
		$this->buf .= $ss;
		return $ss;
	}
	public function ok($okmsg='')
	{
		$this->buf .= sprintf("<p align=\"center\">%s <b>OK</b></p>", $okmsg);
	}
	public function seguir($sgmsg='Seguir', $sgurl='javascript:document.location.reload();')
	{
		$this->buf .= sprintf("<p align=\"center\"><a href=\"%s\">%s</a></p>", $sgurl, $sgmsg);
	}
	public function qerr($ecode='')
	{
		return $this->err($ecode, $_SESSION['uid'] == 1 ? mysql_error(): "Fall&oacute; consulta de datos");
	}
	public function qxerr($ecode='')
	{
		return $this->xerr($ecode, $_SESSION['uid'] == 1 ? mysql_error(): "Fall&oacute; consulta de datos");
	}
	public function sel($nombre='', $validfn='', $vparms='')
	{
		$ss = "<select";
		if ($nombre != '') $ss .= " name=\"$nombre\" id=\"$nombre\"";
		if ($validfn != '')
		{
			if ($vparms == '') $sfn = sprintf("%s(this)", $validfn);
			else $sfn = sprintf("%s(this,%s)", $validfn, $vparms);
			$sfn .= " onchange=\"$sfn\"";
		}
		$ss .= ">";
		$this->buf .= $ss;
		return $ss;
	}
	public function opt($valor='', $descrip='', $selval=null)
	{
		$ss = "<option value=\"$valor\"";
		if ($selval !== null)
		{
			if ($valor == $selval) $ss .= " selected"; 
		}
		$ss .= ">$descrip</option>";
		$this->buf .= $ss;
		return $ss;		
	}
	public function selx()
	{
		$this->buf .= "</select>";
		return "</select>";
	}
	public function txt($nombre='', $valor='', $funcion='', $validfn='', $t='c', $ro=false, $vparms='')
	{
		$tmpct = new lt_textbox();
		$tmpct->n = $nombre;
		$tmpct->v = $valor;
		$tmpct->funcion = $funcion;
		$tmpct->valid_fn = $validfn;
		$tmpct->valid_parms = $vparms;
		$tmpct->form = $this->form_name;

		$tmpct->t = $t;
		if ($t == 'n')
		{
			$tmpct->l = 10;
			$tmpct->pd = 2;
		}
		if ($t == 'i')
		{
			$tmpct->l = 10;
		}		
		if ($t == 'd')
		{
			$tmpct->l = 10;
			if ($validfn == '') $tmpct->valid_fn = 'lt_chkfecha';
		}
		$tmpct->ro = $ro;
		$tmpct->format();
		$tmpct->render($this->buf);
	}
	public function chk($nombre='', $valor=1, $validfn='', $caption='')
	{
		$tmpct = new lt_checkbox();
		$tmpct->n = $nombre;
		$tmpct->v = $valor;
		if ($valor == -1)
		{
			$tmpct->get_default(0);
			$tmpct->save_default = true;
		}
		if ($valor == -2)
		{ 
			$tmpct->get_default(1);
			$tmpct->save_default = true;
		}
		$tmpct->format();
		$tmpct->valid_fn = $validfn;
		$tmpct->save_default = true;
		$tmpct->render($this->buf);
		if ($caption != '')
		{
			$this->sp();
			$this->buf .= $caption;
		}
	}
	public function sub($caption)
	{
		$this->buf .= "<input type=\"submit\" value=\"$caption\">";	
	}
	public function trx()
	{
		if ($this->autotdx && $this->tdopen) $this->tdx();
		if ($this->autothx && $this->thopen) $this->thx();
		$this->buf .= "</tr>";
		if ($this->lf) $this->buf .= "\n";
		$this->tropen = false;
	}
	public function tr($clase='', $estilo='', $nombre="")
	{
		if ($this->autotrx && $this->tropen) $this->trx();
		$this->tropen = true;
		$this->buf .= "<tr";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($nombre != '') $this->buf .= " id=\"$nombre\"";
		$this->buf .= ">";
		$this->tbl_nr[$this->tbl_id]++;
	}
	public function tdx()
	{
		$this->buf .= "</td>";
		$this->tdopen = false;
	}
	public function td($align=0, $colspan=0, $clase='', $estilo='', $rowspan=0)
	{
		if ($this->tbl_interhigh[$this->tbl_id] && (strpos($estilo, "background") === false))
		{
			if ($this->tbl_nr[$this->tbl_id] % 2 == 0) $estilo .= "background:rgb(230,230,230);";
			else $estilo .= "background:white;";
		}
		
		if ($this->autotdx && $this->tdopen) $this->tdx();
		$this->tdopen = true;
		$this->buf .= "<td";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($colspan > 0) $this->buf .= " colspan=\"$colspan\"";
		if ($align == 1) $this->buf .= " align=\"left\"";
		if ($align == 2) $this->buf .= " align=\"right\"";
		if ($align == 3) $this->buf .= " align=\"center\"";
		if ($rowspan > 0) $this->buf .= " rowspan=\"$rowspan\"";
		$this->buf .= ">";
	}
	public function tdc($caption='', $align=0, $colspan=0, $clase='', $estilo='', $rowspan=0)
	{
		$this->td($align, $colspan, $clase, $estilo, $rowspan);
		$this->buf .= $caption;
		$this->tdx();
	}
	public function thx()
	{
		$this->buf .= "</th>";
		$this->thopen = false;
	}
	public function thh($align=0, $colspan=0, $clase='', $estilo='')
	{
		if ($this->autothx && $this->thopen) $this->thx();
		$this->thopen = true;
		$this->buf .= "<th";
		if ($align == 1) $estilo .= "text-align:left;";
		if ($align == 2) $estilo .= "text-align:right;";
		if ($align == 3) $estilo .= "text-align:center;";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($colspan > 0) $this->buf .= " colspan=\"$colspan\"";
		$this->buf .= ">";
	}
	public function th($caption='&nbsp;', $align=0, $colspan=0, $clase='', $estilo='')
	{
		$this->buf .= "<th";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($colspan > 0) $this->buf .= " colspan=\"$colspan\"";
		if ($align == 1) $this->buf .= " align=\"left\"";
		if ($align == 2) $this->buf .= " align=\"right\"";
		if ($align == 3) $this->buf .= " align=\"center\"";
		$this->buf .= ">$caption</th>";
		if ($this->lf) $this->buf .= "\n";
	}
	public function tha($caption_a, $align=0, $colspan=0, $clase='', $estilo='')
	{
		foreach ($caption_a as $caption) 
			$this->th($caption, $align, $colspan, $clase, $estilo);
	}
	public function parx()
	{
		$this->buf .= "</p>";
		$this->paropen = false;
		if ($this->lf) $this->buf .= "\n";
	}
	public function par($align=3, $clase='', $estilo='')
	{
		if ($this->autoparx && $this->paropen) $this->parx();
		$this->paropen = true;
		$this->buf .= "<p";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($align == 1) $this->buf .= " align=\"left\"";
		if ($align == 2) $this->buf .= " align=\"right\"";
		if ($align == 3) $this->buf .= " align=\"center\"";
		if ($align == 4) $this->buf .= " align=\"justify\"";
		$this->buf .= ">";
	}
	public function hr($clase='', $estilo='')
	{
		$this->buf .= "<hr";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		$this->buf .= ">";				
	}
	public function br($clase='', $estilo='')
	{
		$this->buf .= "<br";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		$this->buf .= ">";				
	}
	public function hdr($caption='', $nivel=3, $align=3, $clase='', $estilo='')
	{
		$this->buf .= "<h$nivel";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($align == 1) $this->buf .= " align=\"left\"";
		if ($align == 2) $this->buf .= " align=\"right\"";
		if ($align == 3) $this->buf .= " align=\"center\"";
		$this->buf .= ">$caption</h$nivel>";
	}
	public function lnk($url='', $caption='', $clase='', $estilo='', $target='', $title='')
	{
		$this->buf .= "<a";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($target != '') $this->buf .= " target=\"$target\"";
		if ($title != '') $this->buf .= " title=\"$title\"";
		$this->buf .= " href=\"$url\">$caption</a>";
	}
	public function ax()
	{
		$this->buf .= "</a>";
	}
	public function a($url='', $caption='', $clase='', $estilo='', $target='', $title='')
	{
		$this->buf .= "<a";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($target != '') $this->buf .= " target=\"$target\"";
		if ($title != '') $this->buf .= " title=\"$title\"";
		$this->buf .= " href=\"$url\">$caption";
	}
	public function img($src="", $clase="", $estilo="", $height=-1, $width=-1, $alt="", $id="")
	{
		$this->buf .= "<img";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($height != -1) $this->buf .= " height=\"$height\"";
		if ($width != -1) $this->buf .= " width=\"$width\"";
		if ($alt != '') $this->buf .= " alt=\"$alt\"";
		if ($id != '') $this->buf .= " id=\"$id\" name=\"$id\"";
		$this->buf .= " src=\"$src\" />";
	}
	public function parc($caption='', $align=3, $clase='', $estilo='')
	{
		$this->par($align, $clase, $estilo);
		$this->buf .= $caption;
		$this->parx();
	}
	public function panex()
	{
		$this->buf .= "</div></div>";
	}
	public function pane($name='', $title='', $isopen=false, $align=3, $clase_titulo='', 
		$estilo_titulo='border:1px solid black;', $align_contenido=0, $clase_contenido='', $estilo_contenido='')
	{
		$iconnm = $isopen ? "pop-up.png": "pop-down.png"; 
		$this->buf .= sprintf("<div name=\"%s_pntitle\" id=\"%s_pntitle\"", $name, $name);
		if ($align == 1) $this->buf .= " align=\"left\"";
		if ($align == 2) $this->buf .= " align=\"right\"";
		if ($align == 3) $this->buf .= " align=\"center\"";
		$this->buf .= ">";
		$this->hid($name."_pnstatus", $isopen ? '1':'0');
		$this->buf .= "<table";
		if ($estilo_titulo != '') $this->buf .= " style=\"$estilo_titulo\"";
		if ($clase_titulo != '') $this->buf .= " class=\"$clase_titulo\"";
		$this->buf .= ">";
		$this->buf .= "<tr><td>";
		$this->buf .= sprintf("<img id=\"%s_pnbutt\" src=\"%s\" " .
			"onclick=\"ltpane_toggle('%s');\"></img>&nbsp;%s",
			$name, $iconnm, $name, $title);
		$this->buf .= "</td></tr></table><br class=\"peque\">";

		$estilo_contenido .= $isopen ? "display:block;":"display:none;";
		$this->buf .= "<div";
		if ($name != '') $this->buf .= sprintf(" name=\"%s\" id=\"%s\"", $name, $name);
		if ($estilo_contenido != '') $this->buf .= " style=\"$estilo_contenido\"";
		if ($clase_contenido != '') $this->buf .= " class=\"$clase_contenido\"";
		if ($align_contenido == 1) $this->buf .= " align=\"left\"";
		if ($align_contenido == 2) $this->buf .= " align=\"right\"";
		if ($align_contenido == 3) $this->buf .= " align=\"center\"";
		$this->buf .= ">";
	}
	public function divx()
	{
		$this->buf .= "</div>";
		$this->divopen = false;
	}
	public function div($name='', $align=0, $clase='', $estilo='')
	{
		if ($this->autodivx && $this->divopen) $this->parx();
		$this->divopen = true;
		$this->buf .= "<div";
		if ($name != '') $this->buf .= sprintf(" name=\"%s\" id=\"%s\"", $name, $name);
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($align == 1) $this->buf .= " align=\"left\"";
		if ($align == 2) $this->buf .= " align=\"right\"";
		if ($align == 3) $this->buf .= " align=\"center\"";
		$this->buf .= ">";
	}
	public function divc($contents='', $name='', $align=3, $clase='', $estilo='')
	{
		$this->div($name, $align, $clase, $estilo);
		$this->buf .= $contents;
		$this->divx();
	}
	public function js($fn='')
	{
		if (file_exists($fn)) 
			$this->buf .= "<script language=\"JavaScript\" src=\"$fn\"></script>";
			///$this->buf .= "<script language=\"JavaScript\">".file_get_contents($fn)."</script>";
	}	
	public function jsi($src='')
	{
		$this->buf .= "<script language=\"JavaScript\">$src</script>";
	}	
	public function blank()
	{
		$this->buf = '';
	}
	public function prn()
	{
		$isok = false;
		
		$uid = $_SESSION['uid'];
		$prn = array();
		if (printer_load($uid, PRNUSO_RECIBO, $prn, $this))
		{
			$tmpfn = sprintf("tmp/ltfrm%02d%04d.html", $uid, rand(1,9999));
			@unlink($tmpfn);
			if ($this->write($tmpfn))
			{
				$cmd = sprintf("curl 'http://localhost/html2ps-2.0.43/demo/html2ps.php?" .
					"URL=http://localhost/mprs/%s&media=%s&" .
					"leftmargin=%d&rightmargin=%d&topmargin=%d&bottommargin=%d' | lpr -P %s",
					$tmpfn, $prn['mediatype'], $prn['mleft'], $prn['mright'], $prn['mtop'],
					$prn['mbottom'], $prn['cupsname']);
				system($cmd);
				$isok = true;
			}
		}
		@unlink($tmpfn);
		
		return $isok;
	}
	
	public function rpt_chk($msg, $ctrlnm, $ischk)
	{
		$this->tr();
		$this->th($msg);
		$this->td(3);
		$this->chk($ctrlnm, $ischk, '');
		$this->tdx();
	}
	
	public function submenu_uso($idopt, $uid, $pid)
	{
		$isok = false;

		$q = sprintf("SELECT modulos.submenu_id, SUM(veces) AS veces " .
			"FROM modulos " .
			"LEFT JOIN submenus_uso AS uso ON uso.submenu_id=modulos.submenu_id " .
			"AND uid=%d AND proyecto_id=%d " .
			"WHERE modulo_id=%d " .
			"GROUP BY submenu_id",
			$uid, $pid, $idopt);
		//$this->parc($q);
		if (($res = mysql_query($q)) !== false)
		{
			if (($ox = mysql_fetch_object($res)) !== false)
			{
				if ($ox->submenu_id > 0)
				{
					$qy = sprintf("REPLACE INTO submenus_uso VALUES (%d, %d, %d, %d, NOW())",
						$pid, $uid, $ox->submenu_id, $ox->veces+1);
					if (mysql_query($qy) !== false)
					{
						$isok = true;
					}
					else $this->qerr("SUBMENU-USO-2");
				}
			}
			mysql_free_result($res);
		}
		else $this->qerr("SUBMENU-USO-1");

		return $isok;	
	}
	public function acceso($modulo_id, $uid=0, $pid=0)
	{
		if ($uid == 0) $uid = $_SESSION["uid"];
		if ($pid == 0) $pid = $_SESSION["pid"];
		$nivel = USUARIO_UNAUTH;
		$qa = new myquery($this, sprintf("SELECT usertype_id FROM acceso " .
			"WHERE uid=%d AND modulo_id=%d AND proyecto_id=%d",
			$uid, $modulo_id, $pid), "LTACCESO-1");
		if ($qa->isok) $nivel = $qa->r->usertype_id;
		return $nivel;
	}
	public function usrchk($idopt, $nivelmin, $xerr=false)
	{
		$this->usrlv = USUARIO_UNAUTH;
		$this->usrnm = "(NO_AUTORIZADO)";
		$myerr = 'desconocido';
		if (isset($_SESSION["uid"]) && isset($_SESSION["pid"]))
		{			
			$uid = $_COOKIE['uid'];
			$pid = $_COOKIE['pid'];
			///$junky = $_COOKIE['junk'];
			$junky = $_SERVER['REMOTE_ADDR'];
			if (isset($_SESSION['unm'])) $this->usrnm = $_SESSION['unm'];
			
			// TODO: check kerberos ticket
			$session_tbl = "es";
			if (isset($_SESSION["session_tbl"])) $session_tbl = $_SESSION["session_tbl"];  
			$q = sprintf("SELECT abierta FROM %s WHERE uid=%d AND junky='%s' AND abierta=1",
				$session_tbl, $uid, $junky);
			if (($res = mysql_query($q)) !== false)
			{
				if (($row = mysql_fetch_row($res)) !== false)
				{
					$q2 = sprintf("SELECT usertype_id FROM acceso " .
						"WHERE (uid=%d AND modulo_id=%d AND proyecto_id=%d) ",
						$uid, $idopt, $pid, $uid, $pid);
					if (($res2 = mysql_query($q2)) !== false)
					{
						if (($row = mysql_fetch_row($res2)) !== false)
						{
							$this->usrlv = intval($row[0]);
							if ($this->usrlv > $nivelmin)
							{
								if ($xerr)
								{
									$this->xerr('USR-5', "El usuario no est&aacute; autorizado para usar este m&oacute;dulo.");
								}
								else
								{
									$this->err('USR-5', "El usuario no est&aacute; autorizado para usar este m&oacute;dulo.");
									$this->lnk("menu.php", "Volver al menu inicial");
								}
								$this->usrlv = USUARIO_UNAUTH;
							}
							else
							{
								$this->submenu_uso($idopt, $uid, $pid);
							}
						}
						else
						{ 
							$this->xerr('USR-4', "El usuario no posee privilegios de acceso para este m&oacute;dulo.");
							$this->lnk("menu.php", "Volver al menu inicial");
						}
					}
					elseif ($xerr) $this->qxerr('USR-3'); else $this->qerr('USR-3');
				}
				elseif ($xerr)
				{ 
					$this->xerr('USR-2', "Usuario no registrado en la tabla de sesiones.");
					login_popup($this);
				}
				else 
				{
					$this->err('USR-2', "Usuario no registrado en la tabla de sesiones.");
					login_popup($this);
				}
				mysql_free_result($res);
			}
			elseif ($xerr) $this->qxerr('USR-1'); else $this->qerr('USR-1');
		}
		else
		{
			if ($xerr) $this->buf .= "{-1}";
			login_popup($this);
		}
		
		return $this->usrlv;
	}
	public function menuprinc()
	{
		$this->buf .= "<p align=\"center\">Favor acceder este m&oacute;dulo desde " .
			"el <a href=\"menu.php\">menu principal</p>";
	}
	public function wait_icon()
	{
		$this->buf .= "<p style=\"text-align:center;margin:0px;\"><img src=\"wait.gif\" name=\"waiticon\" id=\"waiticon\" " .
			"style=\"visibility:hidden;\" align=\"center\"></img></p>";
	}
	public function msg()
	{
		$this->buf .= "<div name=\"ltform_msg\" id=\"ltform_msg\" " .
			"style=\"position:absolute;z-index:10;top:100px;left:300px;visibility:hidden;background:white;".
			"text-align:center;border:5px solid black;-moz-box-shadow: 10px 10px 5px #888; ".
			"-webkit-box-shadow: 10px 10px 5px #888; box-shadow: 10px 10px 5px #888;\">".
			"<div name=\"ltform_msg_p\" id=\"ltform_msg_p\" ".
			"style=\"text-align:center;margin:10px;background:white;\"></div></div>";
	}
	public function volver($tourl, $caption="Volver al formulario anterior")
	{
		$this->par();
		$this->lnk("javascript:document.location.replace('$tourl');", $caption);
		$this->parx();
	}
	public function onload($jsinitfn)
	{
		$this->buf .= sprintf("<script language=\"JavaScript\">window.onload=%s;</script>", $jsinitfn);
	}
	public function wlog($tabla, $valor, $msg, $local_id=0, $cliente_id=0)
	{
		$isok = false;
		
		$q = sprintf("INSERT INTO ltlog VALUES (0, '%s', '%s', '%s', %d, %d, '%s', NOW(), %d, %d)",
			$tabla, $valor, mysql_real_escape_string($msg), $_SESSION['uid'], $_SESSION['pid'], 
			$_SERVER['REMOTE_ADDR'], $local_id, $cliente_id);
		if (mysql_query($q) !== false)
		{
			$isok = true;
		}
		else $this->qerr("LOG-1");
		
		return $isok;
	}
	public function tojsonx($isok, &$reto, $sgurl=LTMSG_HIDE)
	{
		$reto['isok'] = 0;
		if ($isok) $reto['isok'] = 1;
		if (!$isok && $sgurl!="") $this->seguir("Seguir", $sgurl);
		$reto['msg'] = $this->buf;
		$this->http_header();
		header("Content-type: application/json");
		echo json_encode($reto);
		$this->dbclose();
	}
	public function lnk_popup($name, $url, $caption, $width=500, $height=400, $hint="")
	{
		if ($hint != "") $shint = sprintf(" title=\"%s\"", $hint); else $shint = "";
		$this->buf .= sprintf("<a href=\"javascript:void(0);\" " .
			"onclick=\"PopupCenter('%s','%s', %d, %d);\"%s>%s</a>",
			$url, $name, $width, $height, $shint, $caption);
	}
	public function lnk_js($jscode, $caption, $hint="", $style="", $tipo=0)
	{
		if ($hint != "") $shint = sprintf(" title=\"%s\"", $hint); else $shint = "";
		if ($style != "") $sstyle= sprintf(" style=\"%s\"", $style); else $sstyle = "";
		if ($tipo == 0) $this->buf .= sprintf("<a href=\"javascript:void(0);\" onclick=\"%s\"%s%s>%s</a>",
			$jscode, $shint, $sstyle, $caption);
		if ($tipo == 1) $this->buf .= sprintf("<img src=\"%s\" onclick=\"%s\"%s%s></img>",
			$caption, $jscode, $shint, $sstyle);
	}
	public function email_btt()
	{
		$nn = time();
		$divn = sprintf("ltemldiv%d", $nn);
		$txn = sprintf("ltemltxt%d", $nn);
		
		$this->eml_start = strlen($this->buf);
		$this->br("peque noprint");
		$this->div($divn, 0, "noprint", "border:1px solid black;width:500px;display:none;");
		$this->par(3, "noprint");
		$this->tbl(3,-1,"2%","stdpg4a noprint");
		$this->th("Direcci&oacute;n de correo electr&oacute;nico");
		$this->td(0, 0, "stdpg4a noprint");
		$tx0 = new lt_textbox();
		$tx0->n = $txn;
		$tx0->l = 128;
		$tx0->vcols = 32;
		$tx0->assign($this->eml_default);
		$tx0->render($this->buf);
		$this->sp();
		$this->lnk_js(sprintf("lt_sendemail(%d,'%s','%s','%s');", $this->rpt_id, $txn, $this->eml_subject, $divn), 
			"Enviar", "Enviar esta p&aacute;gina por correo electr&oacute;nico");
		$this->tblx();
		$this->lnk_js(sprintf("document.getElementById('%s').style.display='none';", $divn), "Ocultar");
		$this->parx();
		$this->divx();
		$this->br("peque noprint");
		$this->buf .= sprintf("<img src=\"email.png\" class=\"noprint\" onclick=\"document.getElementById('%s').style.display='block'\" ".
			"title=\"Enviar esta p&aacute;gina por correo electr&oacute;nico\"></img>", $divn);
		$this->eml_stop = strlen($this->buf);
	}
	public function email($addrto, $subj="")
	{
		if ($subj == "") $subj = sprintf("Sistema de ventas %s", $_SESSION['pnm']);
	
		$headers = 'MIME-Version: 1.0' . "\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
		$headers .= 'From: "Ventas OrionCorp" <carlos.caicedo@orioncorp.com.ve>' . "\n";
		$headers .= 'Reply-To: "Ventas OrionCorp" <ventas@multinmuebles.com>' . "\n";
		
		mail($addrto, $subj, $this->buf, $headers);
	}
}
?>
