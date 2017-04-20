<?php
define("LT_SPLBT_NEWWND", 1);
define("LT_SPLBT_JS", 2);
define("LT_SPLBT_SETURL", 3);
define("LT_SPLBT_REPLACEURL", 4);

define('LT_FORM_AJAX', 1);		// respuesta ajax
define('LT_FORM_MAIN', 2);		// formulario principal, sin parametros
define('LT_FORM_PROC', 3); 		// proceso de formulario html
define('LT_FORM_DIRECT', 4);	// salida directa
define('LT_FORM_NDEF', 5);		// no definido, por compatibilidad

define('LT_FORM_ACCESO_LIBRE', 999999);

define('LT_INFOBOX_HORIZONTAL', 1);
define('LT_INFOBOX_VERTICAL', 2);
define('LT_INFOBOX_NOBOX',3);

define('LT_GET', 'GET');
define('LT_POST', 'POST');
define('LT_PUT', 'PUT');
define('LT_DELETE', 'DELETE');

define('LT_MODOFF_INICIO', 0);
define('LT_MODOFF_EDITAR', 1);
define('LT_MODOFF_ACTUALIZAR', 2);
define('LT_MODOFF_BORRAR', 3);
define('LT_MODOFF_NUEVO', 4);

define('LT_VERBOSE_MUTE', 0);
define('LT_VERBOSE_ERROR', 1);
define('LT_VERBOSE_DEBUG', 2);

class lt_enrutador_item
{
	public $verbo = '', $uri = '', $formato_parametros = '', $funcion = '', $modulo_id = 0;
	function __construct($verbo, $uri, $formato_parametros, $funcion, $modulo_id)
	{
		$this->verbo = $verbo;
		$this->uri = $uri;
		$this->formato_parametros = $formato_parametros;
		$this->funcion = $funcion;
		$this->modulo_id = $modulo_id;
	}
}

class lt_enrutador
{
	public $ruta = array(), $ruta_c = 0, $verbo = '', $uri = '', $p , $fo;
	function __construct()
	{
		$this->verbo = $_SERVER['REQUEST_METHOD'];
		$this->uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO']: ''; 
	}
	public function init()
	{
		return true;
	}
	final public function agregar($verbo, $uri, $formato_parametros, $funcion, $modulo_id)
	{
		if (gettype($uri) == 'boolean') $uri = ''; ///$_SERVER['PHP_SELF'];
		$this->ruta[$verbo][$uri] = new lt_enrutador_item($verbo, $uri, $formato_parametros, $funcion, $modulo_id);
		$this->ruta_c++;		
	}
	final public function enrutar(lt_form $fo)
	{
		$isok = false;
		$this->init();
		//error_log($this->verbo);
		//error_log($this->uri);
		if (isset($this->ruta[$this->verbo][$this->uri]))
		{
			$rt = &$this->ruta[$this->verbo][$this->uri];
			$funk = $rt->funcion;
			$this->p = parametros::crear($rt->formato_parametros, false, $this->verbo);
			$this->p->escape();
			$fo->tipo = $this->p->parametro_crudo('_tform', $fo->tipo);
			if ($fo->usrchk($rt->modulo_id, 2) !== USUARIO_UNAUTH)
			{
				$this->$funk($fo, $this->p);
				$isok = true;
			}
			else error_log('Fallo chequeo de permisos');
		}
		else error_log('Ruta no encontrada');
		return $isok;
	}
	final public function from_array(array $rutas)
	{
		foreach ($rutas as $rt) $this->agregar($rt[0], $rt[1], $rt[2], $rt[3], $rt[4]);
	}
	final public function from_file($filename)
	{
		// 
	}
}


/**
 * 
 * Clase que crea una pantalla virtual para generar codigo html
 * @author carlos.caicedo
 *
 */
class lt_form
{
	/**
	 * 
	 * Atributos basicos de identificacion del form object, buf es el buffer html,
	 * @var String/int
	 */
	public $buf = '', $form_name = '', $form_id = 0, $usrnm = '(DESCONOCIDO)', $usrlv = 6;
	public $uid = 0, $pid = 0, $tienda_id = 0, $ipaddr = '';
	public $frmopen=false, $paropen=false, $divopen=false; 
	public $autofrmx=true, $autoparx=true, $autodivx=false;
	public $bHeader = true, $tabindex = 0, $ctrl=array(), $ctrl_c = 0, $have_wait = false, $lf = false;
	public $toemail = false, $rpt_id=0, $eml_default = "", $eml_subject = "", $eml_start=0, $eml_stop=0;
	public $verbose_level = LT_VERBOSE_MUTE;
	private $_have_msg = FALSE;

	private $_dbopened=false, $_dbhandler=false, $_dbname = DEFAULT_SCHEMA;

	public $isok = false, $re = array('msg'=>'','error'=>''), $seguir_url = LTMSG_HIDE;
	public $p, $form_parms='', $form_autoparms = true, $tipo = LT_FORM_NDEF, $headerless = FALSE;
	
	public $tbl_id = 0, $tbl_nr = array(0,0,0,0,0,0,0,0,0,0), $tbl_interhigh = array(0,0,0,0,0,0,0,0);
	public $tblopen=false, $tropen=false, $tdopen=false, $thopen=false;
	public $autotrx=true, $autotdx=true, $autothx=true, $autotblx=true;
	private $_tbl_autocierre = true;
	
	private $_emailed = FALSE, $_cors = FALSE, $_cors_domain = '*', $_login_byurl = FALSE;
	
	/**
	 * 
	 * Constructor de la clase
	 * @param bool $ismain
	 * (opcional) Verificar sesion e iniciarla si es necesario, por defecto true
	 * @param bool $bHeader
	 * (opcional) Enviar encabezado http al ejecutar metodo show(), por defecto true
	 */
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
		
		$this->ipaddr = $_SERVER['REMOTE_ADDR'];
		if (isset($_SESSION['uid'])) $this->uid = $_SESSION['uid'];
		if (isset($_SESSION['pid'])) $this->pid = $_SESSION['pid'];
		if (isset($_SESSION['tid'])) $this->tienda_id = $_SESSION['tid'];
		if (isset($_REQUEST['_headerless'])) $this->headerless = $_REQUEST['_headerless'];
	}
	public function __destruct()
	{
		if ($this->tipo == LT_FORM_AJAX) $this->_tojs();		// enviar respuesta ajax 
		if ($this->tipo == LT_FORM_MAIN) $this->show();			// formulario principal
		if ($this->tipo == LT_FORM_PROC) // procesar formulario
		{ 
			if (!$this->_emailed) $this->show();
		}
		
		if ($this->_login_byurl)
		{
			require_once 'login_fn.php';
			login_logout($this);
		}
		
		if (array_search($this->tipo, array(LT_FORM_MAIN, LT_FORM_PROC, LT_FORM_AJAX, LT_FORM_DIRECT)) !== FALSE) $this->dbclose();	
	}
	public function setEmailed($isEmailed)
	{
		$this->_emailed = $isEmailed;
	}
	public static function enrutar(lt_enrutador $enrutador)
	{
		$esok = false;
		$tmpf = new self();
		$tmpf->tipo = LT_FORM_PROC;
		if ($tmpf->dbopen())
		{
			if ($enrutador->enrutar($tmpf)) $esok = true;
		}
		else $tmpf->re['error'] = 'No pude conectarme a la base de datos';
		if ($esok) return $tmpf; else return false;
	}
	
	/**
	 * 
	 * Envia el encabezado http estandar, que evita el cacheo de pagina
	 * @param string $html_content
	 * (opcional) Tipo MIME a enviar, por defecto text/html
	 */
	public function http_header($html_content=true)
	{
		header("Expires: Mon, 27 Oct 1977 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		if ($html_content) header("Content-Type: text/html; charset=utf-8");
	}
	public function get_dbhandler()
	{
		return $this->_dbhandler;
	}
	/**
	 * 
	 * Cerrar conexion actual a base de datos
	 */
	public function dbclose()
	{
		if ($this->_dbopened)
		{
			if ($this->_dbhandler !== false) @mysql_close($this->_dbhandler);
			$this->_dbopened = false;
			unset($_SESSION['_dbhandler']);
		}
	}
	/**
	 * 
	 * Devuelve el ultimo error de base de datos
	 */
	public function dberror()
	{
		return mysql_error($this->_dbhandler);
	}

    /**
     * Retorna string adecuado para insertar valor en base de datos
     * @param string $str
     * @return string
     */
	public function dbe($str)
    {
        return mysql_escape_string($str);
    }

	/**
	 * 
	 * Abre una conexion a la base de datos y guarda el handler en dbhandler
	 * Toma sus valores de parametro por defecto de un archivo .conf o de las
	 * variables de sesion definidas en ltable_siteconf.php
	 * @param string $db_nombre
	 * (opcional) Nombre de la base de datos
	 * @param string $db_url
	 * (opcional) URL de conexion
	 * @param string $db_usuario
	 * (opcional) Usuario para realizar la conexion
	 * @param string $db_passwd
	 * (opcional) Password para realizar la conexion
	 * @return boolean
	 * Indica si la conexion fue exitosa
	 */
	public function dbopen($db_nombre=false, $db_url=false, $db_usuario=false, $db_passwd=false)
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
		if ($db_nombre !== false) $dbname = $db_nombre;
		if ($db_url !== false) $dburl = $db_url;
		if ($db_usuario !== false) $dbuser = $db_usuario;
		if ($db_passwd !== false) $dbpasswd = $db_passwd;
		
		if (($this->_dbhandler = mysql_connect($dburl, $dbuser, $dbpasswd, false, MYSQL_CLIENT_COMPRESS)) !== false)
		{
			if (mysql_select_db($dbname) !== false)
			{
				$this->_dbname = $dbname;
				mysql_set_charset ('utf8', $this->_dbhandler);
				mysql_query("set collation_connection = @@collation_database");
				$this->_dbopened = true;
				$_SESSION['_dbhandler'] = $this->_dbhandler;
				//error_log(print_r($_SESSION, true));
				$isok = true;
			}
			else $this->err("LTDBOPEN-3", mysql_error());
		}
		else $this->err("LTDBOPEN-2", mysql_error());
		return $isok;
	}
	
	/**
	 * 
	 * Devuelve el nombre de la base de datos en uso
	 * @return string
	 */
	function dbname()
	{
		return $this->_dbname;
	}
	/**
	 * 
	 * Envia el contenido del buffer al cliente (lo muestra) y cierra la conexion a la BD
	 */
	public function show()
	{
		if ($this->toemail && $this->_dbopened)
		{
			$qeml = new myquery($this, sprintf("REPLACE INTO ltable_emltmp VALUES (%d,%d,'%s')",
				$_SESSION["uid"], $this->rpt_id, mysql_real_escape_string(substr($this->buf, 0, $this->eml_start).
				substr($this->buf, $this->eml_stop))), "LTEMLSV-1", true, true);
			///@file_put_contents("/tmp/".$this->eml_fn, substr($this->buf, 0, $this->eml_start).substr($this->buf, $this->eml_stop));
		}
		if ($this->bHeader) $this->http_header();
		echo $this->buf;
	}
	
	function encabezado_lynx_load()
	{
		$shdr = "<html><head>";
		$fnbsa = array("mprs.default.css", "mprs_std.css", "ltable_rpt.css");
		foreach ($fnbsa as $fnbs)
		{
			$shdr .= "<style type=\"text-css\">";
			$shdr .= file_get_contents($fnbs);
			$shdr .= "</style>";
		}
		$shdr .= "</head>";
		return $shdr;
	}
	
	/**
	 * 
	 * Guarda el contenido del buffer en un archivo en el servidor
	 * @param string $fname
	 * Nombre de archivo
	 * @return boolean
	 * Indica si fue exitosa la operacion
	 */
	public function write($fname)
	{
		$isok = false;
		
		if (($ff = fopen($fname, "w")) !== false)
		{
			$buf = $this->encabezado_lynx_load() . $this->buf;
			if (fwrite($ff, $buf) !== false) $isok = true;
			fclose($ff);
		}
		
		return $isok;
	}
	/**
	 * 
	 * Envia respuesta al cliente en formato JSON. El buffer del form se guarda automaticamente en $reto['msg']
	 * y se puede acceder a traves de responseJSON.msg 
	 * @param bool $isok
	 * Indica si el procesamiento del formulario fue exitoso, y si debe ejecutarse onSuccess / OnFailure
	 * del objeto Prototype.Ajax que llamo el formulario.
	 * @param Array $reto
	 * Array asociativo de respuesta. Cada uno de sus elementos sera convertido a responseJSON.<elemento>
	 * @param string $sgurl
	 * (opcional) Codigo JS de callback en el enlace para desaparecer el popup, por defecto LTMSG_HIDE
	 */
	public function tojson($isok, &$reto, $sgurl=LTMSG_HIDE)
	{
		$this->seguir_url = $sgurl;
		$this->isok = $isok;
		$this->re = $reto;
		$this->_tojs();
	}
	/**
	 * 
	 * Indica si se permiten llamadas cross-domain en AJAX
	 * @param bool $valor
	 * TRUE si se permitiran llamadas CORS en este script
	 */
	public function setCORS($valor)
	{
		$this->_cors = $valor;
	}
	/**
	 * 
	 * Indica el dominio desde donde se permiten llamadas AJAX cross-domain
	 * @param string $dominio
	 * Dominio a permitir acceso, por defecto '*'
	 */
	public function setCORSdomain($dominio='*')
	{
		$this->_cors_domain = $dominio;
	}
	private function _tojs()
	{
		if (!$this->isok && $this->seguir_url!="") $this->seguir("Seguir", $this->seguir_url);
		$reto = $this->re;
		$reto['msg'] = mb_convert_encoding($this->buf, 'UTF-8');
		$this->http_header();
		if ($this->_cors)
		{ 
			header('Access-Control-Allow-Origin: '.$this->_cors_domain);
		}
		header("Content-type: application/json");
		if (!$this->isok) header("HTTP/1.0 419 Peticion incorrecta.");
		echo json_encode($reto);
	}
	
	private function _modulo_id($tabla, $op)
	{
		$modulo_id = 0;
		if (($tmprt = lt_registro::crear($this, 'ltable', $tabla)))
		{
			$modulo_id = $tmprt->v->form_id;
			$modulo_id += $op;
		}
		return $modulo_id;
	}
	
	public function checkLogin()
	{
		$this->_login_byurl = FALSE;
		$lgok = TRUE;
		if (isset($_REQUEST['mprs_lu']) && isset($_REQUEST['mprs_lp'])) 
		{
			$this->_login_byurl = TRUE;
			require_once 'login_fn.php';
			$lgok = login_check($this, $_REQUEST['mprs_lu'], $_REQUEST['mprs_lp']);
		}
		return $lgok;
	}
	/**
	 * 
	 * Generar lt_form para procesar formularios y peticiones Ajax
	 * @param int $modulo_id
	 * Id de modulo para verificar acceso del usuario
	 * @param string $lista_parms
	 * Lista de parametros. Si es FALSE, usa la variable $_REQUEST['_formparms']
	 * @param string $escape_parms
	 * (opcional) Aplicar mysql_real_escape_string() a los parametros. Por defecto es TRUE. 
	 * @param int $tipo
	 * (opcional) Tipo de proceso, por defecto LT_FORM_AJAX.
	 * @param int $op
	 * (opcional) Tipo de operacion a realizar, en caso de operaciones con registros
	 * @return lt_form|boolean
	 * Devuelve el nuevo formulario o FALSE si no fallo alguna verificacion
	 */
	public static function procesar($modulo_id, $lista_parms=false, $escape_parms=true, 
			$tipo=LT_FORM_AJAX, $op=LT_MODOFF_INICIO)
	{
		$esok = false;
		$tmpf = new self();
		$tmpf->tipo = $tipo;
		$parok = false;
		$noempty = false;
		if (gettype($lista_parms) == 'string') $noempty = true; 
//error_log('paso1');
		if (($tmpf->p = parametros::crear($lista_parms, $noempty)) !== false) $parok = true;
		if ($parok)
		{
//error_log('paso2');
			if ($tmpf->dbopen())
			{
//error_log('paso3');
				if (gettype($modulo_id) == 'string')
				{
					$tmpf->form_id = $tmpf->_modulo_id($modulo_id, $op);
				}
				else $tmpf->form_id = $modulo_id;
				
//error_log('paso4');
				if ($tmpf->checkLogin())
				{
					if ($tmpf->form_id == LT_FORM_ACCESO_LIBRE)
					{
						if ($tmpf->uid == 0) $tmpf->uid = USR_SYS;
						if ($tmpf->pid == 0) $tmpf->pid = 7;
						if (!isset($_SESSION['uid'])) $_SESSION['uid'] = $tmpf->uid;
						if (!isset($_SESSION['pid'])) $_SESSION['pid'] = $tmpf->pid;
						if ($escape_parms) $tmpf->p->escape();
						$esok = TRUE;
					}
					else
					{
						if ($tmpf->usrchk($tmpf->form_id, 2) !== USUARIO_UNAUTH)
						{
							if ($escape_parms) $tmpf->p->escape();
							$esok = true;
						}
						else
						{ 
							$tmpf->re['error'] = 'Acceso no permitido, modulo '.$tmpf->form_id;
							if ($tmpf->tipo == LT_FORM_DIRECT) error_log('Acceso no permitido, modulo '.$tmpf->form_id);
						}
					}
				}
				else
				{ 
					$tmpf->re['error'] = 'Error verificando usuario';
					if ($tmpf->tipo == LT_FORM_DIRECT) error_log('Error verificando usuario');
				}
			}
			else
			{ 
				$tmpf->re['error'] = 'No pude conectarme a la base de datos';
				if ($tmpf->tipo == LT_FORM_DIRECT) error_log('No pude conectarme a la base de datos');
			}
		}
		else
		{ 
			$tmpf->re['error'] = 'Parametros incorrectos';
			if ($tmpf->tipo == LT_FORM_DIRECT) error_log('Parametros incorrectos');
		}
		if ($esok) return $tmpf; else return false;
	}
	/**
	 * 
	 * LT_FORM para generar pantalla principal
	 * @param int $modulo_id
	 * Numero de modulo
	 * @param string $lista_parms
	 * (opcional) Listado con nombre y formato de parametros, por defecto FALSE, el cual 
	 * lo toma de $_REQUEST['_formparms'] 
	 * @return lt_form
	 * Retorna un LT_FORM si las validaciones son exitosas, o sino FALSE
	 */
	public static function principal($modulo_id)
	{
		if (($tmpform = lt_form::procesar($modulo_id, false, false, LT_FORM_MAIN)) !== false)
		{
			$tmpform->encabezado();
			$tmpform->wait_icon();
		}
		return $tmpform;
	}
	/**
	 * 
	 * LT_FORM para procesar formulario
	 * @param int $modulo_id
	 * Numero de modulo
	 * @param string $lista_parms
	 * (opcional) Listado con nombre y formato de parametros, por defecto FALSE, el cual 
	 * lo toma de $_REQUEST['_formparms'] 
	 * @return lt_form
	 * Retorna un LT_FORM si las validaciones son exitosas, o sino FALSE
	 */
	public static function proceso($modulo_id, $lista_parms=false)
	{
		return lt_form::procesar($modulo_id, $lista_parms, true, LT_FORM_PROC);
	}
	/**
	 * 
	 * LT_FORM para generar respuesta AJAX / JSON
	 * @param int $modulo_id
	 * Numero de modulo
	 * @param string $lista_parms
	 * (opcional) Listado con nombre y formato de parametros, por defecto FALSE, el cual 
	 * lo toma de $_REQUEST['_formparms'] 
	 * @param bool $credencial
	 * (opcional) Indica si se verifica credencial externa
	 * @return lt_form
	 * Retorna un LT_FORM si las validaciones son exitosas, o sino FALSE
	 */
	public static function respuesta($modulo_id, $lista_parms=false, $credencial_ex=false)
	{
		return lt_form::procesar($modulo_id, $lista_parms, true, LT_FORM_AJAX);
	}
	
	public static function registro_proc($tabla, $tipo)
	{
		return lt_form::procesar($tabla, FALSE, TRUE, LT_FORM_AJAX, $tipo);
	}
	
	public static function registro_update($tabla)
	{
		return lt_form::registro_proc($tabla, LT_MODOFF_ACTUALIZAR);
	}
	
	public static function registro_delete($tabla)
	{
		return lt_form::registro_proc($tabla, LT_MODOFF_BORRAR);
	}

	public static function directo($modulo_id, $lista_parms=false)
	{
		return lt_form::procesar($modulo_id, $lista_parms, true, LT_FORM_DIRECT);
	}
	/**
	 * 
	 * Genera el encabezado estandar para reportes (sin menu)
	 * @param int $tout
	 * Si es mayor que -1, la pagina sera redireccionada en $tout segundos (opcional) 
	 * @param string $url
	 * URL donde sera redireccionada la pagina (opcional)
	 * @param string $titulo
	 * Titulo de la ventana (opcional)
	 * @param variant $add_css
	 * (opcional) Array de archivos CSS adicionales
	 * @param bool $usar_jquery
	 * (opcional) Indica si se incluye libreria jQuery
	 * @param bool $con_menu
	 * (opcional) Indica si se incluye libreria TinyDropDownMenu
	 */
	function encabezado_base($tout=-1, $url='', $titulo=SOFTNAME, $add_css=false, 
			$add_js=false, $usar_jquery=true, $con_menu=false, $usar_multiselect=true)
	{
		if ($this->headerless) return true;
		
		if ($this->toemail)
		{
			$this->buf .= "<html><head><style>body{margin-left:1%;margin-right:1%;color:black;background:white;".
				"font-family: Verdana,Sans Serif;font-size:10pt;}\n";
			$this->buf .= @file_get_contents("mprs_std.css")."</style>";
		}
		else
		{
			$ruta_css = '';
			if (defined('RUTA_CSS')) $ruta_css = RUTA_CSS;
			//else error_log('No ha definido ruta CSS');
				
			$refre = '';
			if ($tout >= 0)
			{
				if (strlen($url) > 0) $refre = sprintf("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"%d; URL=%s\">", $tout, $url);
				elseif ($tout > 1) $refre = sprintf("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"%d\">", $tout);
			}
			$tema = isset($_SESSION['tema']) ? $_SESSION['tema']: 'default';
			$css = $ruta_css."mprs.". $tema .".css";
			if (!file_exists($css)) $css = $ruta_css."mprs.default.css";
			$this->buf .= "<html><head>".
			"<link href=\"{$css}\" type=\"text/css\" rel=\"stylesheet\">".
			"<link href=\"{$ruta_css}reserv.css\" type=\"text/css\" rel=\"stylesheet\">".
			"<link href=\"{$ruta_css}ltable_rpt.css\" type=\"text/css\" rel=\"stylesheet\">".
			"<link href=\"{$ruta_css}mprs_std.css\" type=\"text/css\" rel=\"stylesheet\">";
			if ($con_menu) $this->buf .= "<link href=\"{$ruta_css}tinydropdown.css\" type=\"text/css\" rel=\"stylesheet\">";
			if ($usar_jquery) $this->buf .= "<link href=\"{$ruta_css}jquery-ui.min.css\" type=\"text/css\" rel=\"stylesheet\">";
			if ($usar_multiselect)
			{
				$this->buf .= "<link href=\"{$ruta_css}jquery.multiselect.css\" type=\"text/css\" rel=\"stylesheet\">";
				$this->buf .= "<link href=\"{$ruta_css}jquery.multiselect.filter.css\" type=\"text/css\" rel=\"stylesheet\">";
			}
			if (gettype($add_css) == 'array')
			{
				foreach ($add_css as $cssfn)
				{
					$this->buf .= "<link href=\"{$ruta_css}$cssfn\" type=\"text/css\" rel=\"stylesheet\">";
				}
			}
			$this->buf .= $refre;
			$this->buf .= "<title>".$titulo."</title>";
		}
		if (defined('RUTA')) $this->buf .= sprintf("<base href=\"%s\">", RUTA);
		// TODO: quitar prototype.js y cal2.js
		$this->js("prototype.js");
		// TODO: adelgazar ltable_edit.js
		$this->js("ltable_edit.js");
		if ($con_menu) $this->js("tinydropdown.js");
		if ($usar_jquery) $this->js(array("jquery.min.js","jquery-ui.min.js","jquery_nc.js"));
		if ($usar_multiselect) $this->js(array('jquery.multiselect.min.js','jquery.multiselect.filter.min.js'));
	
		if (gettype($add_js) == 'array' || gettype($add_js) == 'string') $this->js($add_js);	
		$this->buf .= "</head>";
	}
	/**
	 * 
	 * Genera un encabezado html con los estilos css estandar del sistema incrustados en el buffer.
	 * Util cuando se generan archivos html, emails, etc.
	 */
	public function encabezado_incrustado()
	{
		$this->buf .= "<html><head><style>";
		$this->buf .= @file_get_contents("mprs_std.css");
		$this->buf .= "</style></head>";
	}
	/**
	 * 
	 * Genera encabezado estandar con todos los estilos CSS y codigo JS para correr el sistema, asi como el menu.
	 * @param bool $pryro
	 * (opcional) Indica si el formulario puede cambiar el proyecto activo, por defecto false
	 * @param array $add_css
	 * (opcional) Archivos CSS adicionales a los estandar
	 * @param bool $usar_jquery
	 * (opcional) Indica si se incluyen los archivos de jQuery
	 */
	public function encabezado($pryro=false, $add_css=false, $add_js=false, $usar_jquery=true)
	{
		$this->encabezado_base(-1, '', SOFTNAME, $add_css, $add_js, $usar_jquery, true);
		$this->menubuild($_SESSION['uid'], $_SESSION['pid']);
		$this->buf .= loguito(true, $this->usrnm, $this->usrlv, $_SESSION['pnm'], $pryro);
		$this->msg();			
	}
	public function encabezado_print()
	{
		$this->buf .= "<html><head>";
		$this->buf .= "<link href=\"ltable_print.css\" type=\"text/css\" rel=\"stylesheet\" media=\"print\">";
		$this->buf .= "</head>";
	}
	/**
	 * 
	 * Cierra el codigo HTML
	 */
	public function footer()
	{
		$this->buf .= "</body></html>";
	}
	/**
	 * 
	 * Crea el tag BODY
	 * @param string $class
	 * (opcional) Clase CSS a aplicar, por defecto ninguna
	 * @param string $style
	 * (opcional) Estilo CSS a aplicar, por defecto ninguno
	 */
	public function body($class="", $style="")
	{
		$this->buf.="<body";
		if ($class!="") $this->buf.=" class=\"$class\"";
		if ($style!="") $this->buf.=" style=\"$style\"";
		$this->buf.=">";
	}
	/**
	 * 
	 * Cierra el tag BODY
	 */
	public function bodyx()
	{
		$this->buf .= "</body>";
	}
	/**
	 * 
	 * Genera tag SPAN completo, un elemento de texto con estilo, util para mensajes/etiquetas
	 * @param string $caption
	 * (opcional) Texto a mostrar, por defecto en blanco
	 * @param string $class
	 * (opcional) Clase CSS a aplicar, por defecto ninguna
	 * @param string $style
	 * (opcional) Estilo CSS a aplicar, por defecto ninguno
	 * @param string $id
	 * (opcional) ID del elemento para acceder sus propiedades y metodos en JS
	 * @param string $title
	 * (opcional) Tooltip
	 */
	public function span($caption="", $class="", $style="", $id="", $title="")
	{
		/*$this->buf .= "<span";
		if ($class!="") $this->buf.=" class=\"$class\"";
		if ($style!="") $this->buf.=" style=\"$style\"";
		if ($id!="") $this->buf.=" id=\"$id\"";
		if ($title!="") $this->buf.=" title=\"$title\"";
		$this->buf .= ">$caption</span>";*/
		$this->spann($class, $style, $id, $title);
		$this->buf .= $caption;
		$this->spanx();
	}
	
	public function spann($class="", $style="", $id="", $title="")
	{
		$this->buf .= "<span";
		if ($class!="") $this->buf.=" class=\"$class\"";
		if ($style!="") $this->buf.=" style=\"$style\"";
		if ($id!="") $this->buf.=" id=\"$id\"";
		if ($title!="") $this->buf.=" title=\"$title\"";
		$this->buf .= ">";
	}
	
	public function spanx()
	{
		$this->buf .= "</span>";
	}
	/**
	 * 
	 * Repetir $elstr tantas veces como $nrepeats diga
	 * @param int $nrepeats cantidad de repeticion por defecto 1
	 * @param string $elstr  cadena a repetir por defecto es non-breaking-space.
	 */
	public function sp($nrepeats=1, $elstr="&nbsp;")
	{
		$this->buf .= str_repeat($elstr, $nrepeats);
	}
	
	/**
	 * 
	 * Fija las propiedades de autocierre de las tablas y sus elementos
	 * @param bool $valor
	 * TRUE para autocierre
	 */
	public function tbl_setAutoCierre($valor)
	{
		$this->_tbl_autocierre = $valor;
		$this->autotblx = $this->autotrx = $this->autotdx = $this->autothx = $valor;
	}
	public function tbl_getAutoCierre()
	{
		return $this->_tbl_autocierre;
	}
	/**
	 * Cierra tag TABLE
	 * */
	public function tblx()
	{
		$this->buf .= "</table>";
		$this->tblopen = false;
		if ($this->tbl_id > 0) $this->tbl_id--;
		if ($this->lf) $this->buf .= "\n";
	}
	/**
	 * 
	 * Genera tag TABLE. Para anidar tabla debe falsear valores de .autotblx, .autotrx y .autotdx
	 * @param int $align
	 * (opcional) Alineacion de la tabla, 0=ninguna, 1=izquierda, 2=derecha, 3=centrado (default)
	 * @param int $border
	 * (opcional) Grueso del borde. Por defecto 0. Deprecated, usar valor por defecto. 
	 * @param string $padd
	 * (opcional) Relleno (padding) entre celdas. Por defecto "5%"
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar, por defecto ninguna
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar, por defecto ninguno
	 * @param bool $interhigh
	 * (opcional) Indica si se genera sombreado interlineado para facilitar lectura, por defecto false.
	 * @param string $nombre
	 * Setea la propiedad NAME del tag.
	 */
	public function tbl($align=LT_ALIGN_CENTER, $border=LT_TABLE_BORDER_NONE, $padd=LT_TABLE_PADDING_DEFAULT, 
			$clase=LT_TABLE_CLASS_DEFAULT, $estilo='', $interhigh=false, $nombre="")
	{
		$this->tbl_id++;
		$this->tbl_nr[$this->tbl_id] = 0;
		
		$this->tbl_interhigh[$this->tbl_id] = $interhigh;
		if ($clase == "stdpg" || $clase == "stdpg4") $this->tbl_interhigh[$this->tbl_id] = true;

		if ($this->autotblx && $this->tblopen) $this->tblx();
		$this->tblopen = true;
		$this->buf .= "<table";
		if ($border != LT_TABLE_BORDER_NONE) $this->buf .= " border=\"$border\"";
		if ($padd != '') $this->buf .= " cellpadding=\"$padd\""; 
		if ($align == LT_ALIGN_LEFT) $this->buf .= " align=\"left\"";
		if ($align == LT_ALIGN_RIGHT) $this->buf .= " align=\"right\"";
		if ($align == LT_ALIGN_CENTER) $this->buf .= " align=\"center\"";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($nombre != "") $this->buf .= " name=\"$nombre\"id=\"$nombre\"";
		$this->buf .= ">";
		if ($this->lf) $this->buf .= "\n";
	}
	/**
	 * 
	 * Cierra el tag TR
	 */
	public function trx($forced=true)
	{
		if ($this->autotdx && $this->tdopen) $this->tdx();
		if ($this->autothx && $this->thopen) $this->thx();
		if ($this->tropen || $forced) $this->buf .= "</tr>";
		if ($this->lf) $this->buf .= "\n";
		$this->tropen = false;
	}
	/**
	 * 
	 * Genera tag TR (fila de tabla) abierto, se autocierra si se ejecuta otro tr()
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 * @param string $nombre
	 * (opcional) ID del elemento para referenciarlo en JS
	 */
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
	/**
	 * Cierra tag TD (celda de tabla)
	 */
	public function tdx()
	{
		$this->buf .= "</td>";
		$this->tdopen = false;
	}
	/**
	 * 
	 * Genera tag TD (celda de tabla) abierto, se autocierra con otro td() o un tr()
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param int $colspan
	 * (opcional) Indica la cantidad de celda que abarca, por defecto una sola
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 * @param int $rowspan
	 * (opcional) Indica la cantidad de filas que abarca, por defecto una sola
	 */
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
	/**
	 * 
	 * Genera tag TD (celda de tabla) completo (cerrado).
	 * @param string $caption
	 * (opcional) Texto a mostrar, se admite HTML
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param int $colspan
	 * (opcional) Indica la cantidad de celda que abarca, por defecto una sola
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 * @param int $rowspan
	 * (opcional) Indica la cantidad de filas que abarca, por defecto una sola
	 */
	public function tdc($caption='', $align=0, $colspan=0, $clase='', $estilo='', $rowspan=0)
	{
		$this->td($align, $colspan, $clase, $estilo, $rowspan);
		$this->buf .= $caption;
		$this->tdx();
	}
	/**
	 * 
	 * Cierra tag TH
	 */
	public function thx()
	{
		$this->buf .= "</th>";
		$this->thopen = false;
	}
	/**
	 * 
	 * Genera tag TH (celda resaltada) abierto.
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param int $colspan
	 * (opcional) Indica la cantidad de celda que abarca, por defecto una sola
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
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
	/**
	 * 
	 * Generar tag TH cerrado.
	 * @param String $caption texto de la cabecera
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param int $colspan
	 * (opcional) Indica la cantidad de celda que abarca, por defecto una sola
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
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
	/**
	 * 
	 * Genera multiples tag TH, util para encabezados de columnas en tablas
	 * @param array $caption_a
	 * Array de cadenas de texto a mostrar por cada TH.
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param int $colspan
	 * (opcional) Indica la cantidad de celda que abarca, por defecto una sola
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
	public function tha($caption_a, $align=0, $colspan=0, $clase='', $estilo='')
	{
		foreach ($caption_a as $caption) 
			$this->th($caption, $align, $colspan, $clase, $estilo);
	}
	/**
	 * 
	 * Cierra el tag FORM
	 */
	public function frmx()
	{
		$this->frm_parms();
		$this->buf .= "</form>";
		$this->form_name = '';
		$this->frmopen = false;
	}
	/**
	 * 
	 * Inserta un hidden de especificacion de parametros
	 */
	public function frm_parms()
	{
		if ($this->form_autoparms)
		{
			$this->form_parms = trim($this->form_parms);
			$this->buf .= sprintf("<input type=\"hidden\" name=\"_formparms\" value=\"%s\">",
					strlen($this->form_parms) > 0 ? substr($this->form_parms, 1): '');
		}
	}
	/**
	 * 
	 * Genera tag FORM, formulario de datos estandar HTML. Los valores de los input dentro del mismo
	 * son enviados a un php a traves de variables POST.
	 * @param string $form_action
	 * Archivo PHP que recibe los valores de los input del formulario al hacer submit
	 * @param bool $newwnd
	 * (opcional) Indica si se abre una nueva ventana en el browser para mostrar los resultados.
	 * @param string $form_name
	 * (opcional) ID del formulario, para referenciarlo en JS
	 * @param string $enctype
	 * (opcional) Indica el tipo de codificacion MIME a utilizar en el envio del formulario
	 * @param string $clasecss
	 * (opcional) Indica clase CSS a usar para presentacion o agrupacion
	 */
	public function frm($form_action, $newwnd=false, $form_name="", $enctype="", $clasecss='')
	{
		if ($this->autofrmx && $this->frmopen) $this->frmx();
		if ($newwnd) $snew = " target=\"_blank\""; else $snew = "";
		if ($enctype != "") $senc = " enctype=\"".$enctype."\""; else $senc = "";
		if ($clasecss != "") $scss = " class=\"".$clasecss."\""; else $scss = "";
		$this->frmopen = true;
		if ($form_name != '') $this->form_name = $form_name;
		if ($this->form_name != '')
		{
			$this->buf .= sprintf("<form id=\"%s\" name=\"%s\" action=\"%s\" " .
				"method=\"post\"%s%s%s>",
				$this->form_name, $this->form_name, $form_action, $snew, $senc, $scss);
		}
		else $this->buf .= "<form action=\"${form_action}\" method=\"post\"${snew}${scss}>";
		if ($this->form_autoparms) $this->form_parms = '';
	}
	
	public function label($para='', $class='', $style='', $form='')
	{
		$this->buf = '<label';
		if ($para != '') $this->buf .= sprintf(" for=\"%s\"", $para);
		if ($form != '') $this->buf .= sprintf(" form=\"%s\"", $form);
		if ($class != '') $this->buf .= sprintf(" class=\"%s\"", $class);
		if ($style != '') $this->buf .= sprintf(" style=\"%s\"", $style);
		$this->buf .= '>';
	}
	
	public function labelx()
	{
		$this->buf .= '</label>';
	}
	
	public function labelc($caption, $for='', $class='', $style='', $form='')
	{
		$this->label($for, $class, $style);
		$this->buf .= $caption;
		$this->labelx();
	}
	
	/**
	 * 
	 * Funcion para crear un boton de submit
	 * @param string $texto Titulo del boton
	 */
	public function sub($texto, $nombre='', $valor='', $tipo='c')
	{
		$tmpbt = new lt_button();
		$tmpbt->n = $nombre === '' ? 'boton'.mt_rand(100,200): $nombre;
		$tmpbt->tipo = LT_BUTTON_SUBMIT;
		$tmpbt->caption = $texto;
		$tmpbt->t = $tipo;
		$tmpbt->assign($valor);
		$tmpbt->render($this);
	}

	/**
	 * 
	 * Cierra tag BUTTON
	 */
	public function butx()
	{
		$this->buf .= '</button>';
	}
	/**
	 * 
	 * Abre tag BUTTON
	 * @param string $nombre
	 * Identificador del control
	 * @param string $onclickfn
	 * Codigo JS a ejecutar al hacer click
	 * @param bool $habilitado
	 * Indica si esta habilitado o no (opcional)
	 * @param string $clase
	 * Clase CSS a aplicar (opcional)
	 * @param string $estilo
	 * Estilo CSS a aplicar (opcional)
	 */
	public function but($nombre='', $onclickfn='', $habilitado=true, $clase='', $estilo='')
	{
		$this->buf .= "<button type=\"button\" ";
		if ($onclickfn != '') $this->buf .= sprintf(" onclick=\"%s\"", $onclickfn);
		if ($nombre != '') $this->buf .= sprintf(" id=\"%s\" name=\"%s\"", $nombre, $nombre);
		if ($clase != '') $this->buf .= sprintf(" class=\"%s\"", $clase);
		if ($estilo != '') $this->buf .= sprintf(" style=\"%s\"", $estilo);
		if (!$habilitado) $this->buf .= " disabled";
		$this->buf .= ">";
	}
	/**
	 * 
	 * Genera un tag BUTTON (boton), util para ejecutar codigo JS al hacer click
	 * @param string $texto
	 * (opcional) Texto a mostrar en el boton.
	 * @param string $onclickfn
	 * (opcional) Codigo JS a ejecutar al hacer click en el boton
	 * @param string $nombre
	 * (opcional) ID del control, util para referenciarlo en JS
	 * @param bool $habilitado
	 * (opcional) Indica si esta habilitado, por defecto TRUE
	 * @param string $clase
	 * Clase CSS a aplicar (opcional)
	 * @param string $estilo
	 * Estilo CSS a aplicar (opcional)
	 */
	public function butt($texto='', $onclickfn='', $nombre='', $habilitado=TRUE, $clase='', $estilo='')
	{
		$this->but($nombre, $onclickfn, $habilitado, $clase, $estilo);
		$this->buf .= $texto;
		$this->butx();
	}
	/**
	 * 
	 * Genera un tag INPUT tipo CHECKBOX, util para valores verdadero/falso.
	 * @param string $nombre
	 * (opcional) ID del elemento para referenciarlo en JS.
	 * @param string $valor
	 * (opcional) Valor inicial del control, por defecto, 1 (chequeado)
	 * @param string $validfn
	 * (opcional) Codigo JS a ejecutar cuando el control cambia de valor
	 * @param string $caption
	 * (opcional) Titulo a mostrar adjunto al checkbox. 
	 */
	public function chk($nombre='', $valor=1, $validfn='', $caption='', $no=false)
	{
		$tmpct = new lt_checkbox($this);
		$tmpct->n = $nombre;
		if ($no === false) $tmpct->no = $nombre;
		$tmpct->v = $valor;
		$tmpct->format();
		$tmpct->valid_fn = $validfn;
		///$tmpct->save_default = true;
		$tmpct->render($this);
		if ($caption != '')
		{
			$this->sp();
			$this->buf .= $caption;
		}
		if ($this->form_autoparms) $this->form_parms .= sprintf(";%s,b", $nombre);
	}
	/**
	 *
	 * Crea un tag INPUT tipo HIDDEN, util para guardar valores sin mostrarlos.
	 * @param string $nombre nombre del input
	 * ID del elemento, para referenciarlo en JS
	 * @param string $valor
	 * Valor inicial del control
	 * @param string $tipo
	 * Tipo de dato contenido en el control
	 * @param bool $htipo
	 * (opcional) Indica si crea control sombra para el tipo
	 * @param string $no
	 * (opcional) Indica el nombre de la columna relacionada
	 */
	public function hid($nombre, $valor, $tipo='c', $htipo=true, $no=false)
	{
		$tmpct = new lt_ctrl($this);
		$tmpct->n = $nombre;
		if ($no === false) $tmpct->no = $nombre;
		$tmpct->t = $tipo;
		$tmpct->htipo = $htipo;
		$tmpct->assign($valor);
		$tmpct->render($this);
	}
	
	/**
	 * 
	 * Genera un codigo de error, util para localizar errores del codigo al momento de correr o depurar.
	 * @param string $codigo
	 * Codigo de error arbitrario. Se sugiere una abreviacion del modulo y un numero, p.e. CREAFACT-1
	 * @param string $user_msg
	 * Mensaje de error, arbitario
	 */
	public function err($codigo='', $user_msg='', $log_msg=false)
	{
		$usr = ''; 
		if (isset($_SESSION['unm'])) $usr = sprintf("user:%s", $_SESSION['unm']);
		$ss = sprintf("<p align=\"center\"><code><i><b>[%s] %s</b> Error: %s</i></code></p>", 
				$codigo, $usr, $user_msg);
		$this->buf .= $ss;
		if ($log_msg !== false) error_log($codigo.':'.$usr.' msg:'.$log_msg);
		return $ss;
	}
	/**
	 * 
	 * Deprecated, no usar.
	 * @param unknown_type $ecode
	 * @param unknown_type $emsg
	 */
	public function xerr($ecode='', $emsg='')
	{
		$ss = sprintf("{-1}<p align=\"center\"><code><i><b>[%s]</b> Error: %s</i></code></p>", $ecode, $emsg);
		$this->buf .= $ss;
		return $ss;
	}
	/**
	 * 
	 * Deprecated, no usar.
	 * @param unknown_type $okmsg
	 * @return string
	 */
	public function xok($okmsg='')
	{
		$ss = sprintf("{00}<p align=\"center\">%s <b>OK</b></p>", $okmsg);
		$this->buf .= $ss;
		return $ss;
	}
	/**
	 * 
	 * Genera un mensaje de exito, util para notificar al usuario.
	 * @param string $okmsg
	 * Mensaje a mostrar, arbitrario.
	 */
	public function ok($okmsg='')
	{
		$this->buf .= sprintf("<p align=\"center\">%s <b>OK</b></p>", $okmsg);
	}
	/**
	 * 
	 * Genera un enlace JS que reemplaza la pagina actual con la URL especificada.
	 * @param string $sgmsg
	 * (opcional) Mensaje a mostrar, por defecto 'Seguir'
	 * @param string $sgurl
	 * (opcional) URL a seguir, por defecto, recarga la misma pagina
	 */
	public function seguir($sgmsg='Seguir', $sgurl='javascript:document.location.reload();')
	{
		$this->buf .= sprintf("<p align=\"center\"><a href=\"%s\">%s</a></p>", $sgurl, $sgmsg);
	}
	/**
	 * 
	 * Muestra el mensaje de error generado por mysql.  
	 * @param string $ecode
	 * Codigo de error arbitrario para identificar el query que fallo, se sugiere una cadena y un numero
	 */
	public function qerr($ecode='')
	{
		// TODO: debug flag on usuarios set session var
		$uid = 47;
		if (isset($_SESSION['uid'])) $uid = $_SESSION['uid'];
		return $this->err($ecode, $uid == 1 || $uid == 130 || $uid == 47 ? mysql_error(): 
				"Fall&oacute; consulta de datos", mysql_error());
	}
   /**
    * 
    * Cierra parrafo
    */
	public function parx()
	{
		$this->buf .= "</p>";
		$this->paropen = false;
		if ($this->lf) $this->buf .= "\n";
	}
    /**
     * 
     * Abre parrafo, util para agrupar elementos, especialmente texto
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
     */
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
	/**
	 * 
	 * Genera parrafo completo/cerrado, util para agrupar elementos, especialmente texto
	 * @param string $caption
	 * Contenido del parrafo, p.e. texto
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
	public function parc($caption='', $align=3, $clase='', $estilo='')
	{
		$this->par($align, $clase, $estilo);
		$this->buf .= $caption;
		$this->parx();
	}
	/**
	 * 
	 * Mostrar mensaje de advertencia en formulario
	 * @param string $leyenda
	 * Mensaje
	 * @param bool $log
	 * (opcional) Indica si se registra en log de errores del servidor, por defecto no
	 */
	public function warn($leyenda, $log=false)
	{
		$leyendas = $leyenda;
		$this->re['error'] = $leyenda;
		if (is_array($leyenda)) $leyendas = implode(';', $leyenda); 
		$this->parc($leyendas, 3, 'cursiva');
		if ($log || $this->tipo == LT_FORM_DIRECT) error_log($leyendas);
	}
	
	/**
	 * 
	 * Muestra mensaje de error y fija el error en respuesta JSON
	 * @param string $leyenda
	 * Mensaje de error
	 */
	public function rewarn($leyenda)
	{
		$this->warn($leyenda, TRUE);
		$this->re['error'] = $leyenda;
		$this->re['ok'] = 0;
	}
	/**
	 * 
	 * Genera tag HR (linea horizontal de separacion)
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
	public function hr($clase='', $estilo='')
	{
		$this->buf .= "<hr";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		$this->buf .= ">";				
	}
	/**
	 * 
	 * Genera tag BR (separacion horizontal)
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
	public function br($clase='', $estilo='')
	{
		$this->buf .= "<br";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		$this->buf .= ">";				
	}

	/**
	 *
	 * Abrir encabezado <Hx>
	 * @param number $nivel
	 * (opcional) Nivel de encabezado, por defecto 3
	 * @param number $align
	 * (opcional) Alineacion, por defecto LT_ALIGN_CENTER
	 * @param string $clase
	 * (opcional) Clase CSS
	 * @param string $estilo
	 * (opcional) Estilo CSS inline
	 */
	public function hdrr($nivel=3, $align=3, $clase='', $estilo='')
	{
		$this->buf .= "<h$nivel";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($align == LT_ALIGN_LEFT) $this->buf .= " align=\"left\"";
		if ($align == LT_ALIGN_RIGHT) $this->buf .= " align=\"right\"";
		if ($align == LT_ALIGN_CENTER) $this->buf .= " align=\"center\"";
		$this->buf .= ">";
	}
	
	/**
	 *
	 * Cerrar encabezado <Hx>
	 * @param number $nivel
	 * (opcional) Nivel de encabezado, por defecto 3
	 */
	public function hdrx($nivel=3)
	{
		$this->buf .= "</h$nivel>";
	}
		
	/**
	 * 
	 * Agrega tag Hx, para encabezados
	 * @param string $caption
	 * Texto a mostrar
	 * @param string $nivel
	 * Nivel de encabezado, 1=muy grande,2=grande,3=mediano (por defecto), etc.
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
	public function hdr($caption='', $nivel=3, $align=3, $clase='', $estilo='')
	{
		/*$this->buf .= "<h$nivel";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($align == 1) $this->buf .= " align=\"left\"";
		if ($align == 2) $this->buf .= " align=\"right\"";
		if ($align == 3) $this->buf .= " align=\"center\"";
		$this->buf .= ">$caption</h$nivel>";*/
		$this->hdrr($nivel, $align, $clase, $estilo);
		$this->buf .= $caption;
		$this->hdrx($nivel);
	}
	
	/**
	 * 
	 * Agrega codigo CSS inline
	 * @param string $estilo
	 * Codigo CSS
	 */
	public function style($estilo)
	{
		$this->buf .= '<style>'.$estilo.'</style>';
	}
	
	/**
	 * 
	 * Generar enlace al URL especificado
	 * @param string $url
	 * URL destino del enlace
	 * @param string $caption
	 * Texto a mostrar
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 * @param string $target
	 * Nombre de la ventana receptora, se puede usar '_blank' para nueva ventana
	 * @param string $title
	 * Tooltip, texto que se muestra a ubicarse encima del control
	 */
	public function lnk($url='', $caption='', $clase='', $estilo='', $target='', $title='', $name='')
	{
		$this->buf .= "<a";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($target != '') $this->buf .= " target=\"$target\"";
		if ($title != '') $this->buf .= " title=\"$title\"";
		if ($name != '') $this->buf .= " name=\"$name\"";
		$this->buf .= " href=\"$url\">$caption</a>";
	}
	public function marcador($nombre)
	{
		$this->lnk('', '', '', 'text-decoration:none;', '', '', $nombre);
	}
	public function ax()
	{
		$this->buf .= "</a>";
	}
	public function a($url='', $caption='', $clase='', $estilo='', $target='', $title='', $download_as='')
	{
		$this->buf .= "<a";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($target != '') $this->buf .= " target=\"$target\"";
		if ($title != '') $this->buf .= " title=\"$title\"";
		if ($download_as !== '') $this->buf .= " download=\"$download_as\"";
		$this->buf .= " href=\"$url\">$caption";
	}
	public function img($src="", $clase="", $estilo="", $height=-1, $width=-1, $alt="", $id="", $onclick='')
	{
		$this->buf .= "<img";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($height != -1) $this->buf .= " height=\"$height\"";
		if ($width != -1) $this->buf .= " width=\"$width\"";
		if ($alt != '') $this->buf .= " alt=\"$alt\"";
		if ($id != '') $this->buf .= " id=\"$id\" name=\"$id\"";
		if ($onclick != '') $this->buf .= " onclick=\"$onclick\"";
		$this->buf .= " src=\"$src\" />";
	}
	public function embed($src="", $type='', $clase="", $estilo="", $height=-1, $width=-1, $alt="", $id="", $onclick='')
	{
		$this->buf .= "<embed";
		if ($type != '') $this->buf .= " type=\"$type\"";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($height != -1) $this->buf .= " height=\"$height\"";
		if ($width != -1) $this->buf .= " width=\"$width\"";
		if ($alt != '') $this->buf .= " alt=\"$alt\"";
		if ($id != '') $this->buf .= " id=\"$id\" name=\"$id\"";
		if ($onclick != '') $this->buf .= " onclick=\"$onclick\"";
		$this->buf .= " src=\"$src\" />";
	}
    public function object($data, $type, array $params=array(), $clase="", $estilo="", $height=-1, $width=-1, $alt="", $id="", $onclick='')
    {
        $this->buf .= "<object";
        if ($type != '') $this->buf .= " type=\"$type\"";
        if ($estilo != '') $this->buf .= " style=\"$estilo\"";
        if ($clase != '') $this->buf .= " class=\"$clase\"";
        if ($height != -1) $this->buf .= " height=\"$height\"";
        if ($width != -1) $this->buf .= " width=\"$width\"";
        if ($alt != '') $this->buf .= " alt=\"$alt\"";
        if ($id != '') $this->buf .= " id=\"$id\" name=\"$id\"";
        if ($onclick != '') $this->buf .= " onclick=\"$onclick\"";
        $this->buf .= " data=\"$data\">";
        foreach ($params as $p)
        {
            $this->buf .= sprintf("<param name=\"%s\" value=\"%s\">", $p[0], $p[1]);
        }
        $this->buf .= "</object>";
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
	/**
	 * 
	 * Cerrar tag DIV
	 */
	public function divx()
	{
		$this->buf .= "</div>";
		$this->divopen = false;
	}
	/**
	 * 
	 * Generar tag DIV (contenedor) abierto. Util para agrupar y alinear elementos.
	 * @param string $name
	 * ID del elemento para referenciarlo en JS
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
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
	/**
	 * 
	 * Generar tag DIV (contenedor) cerrado. Util para agrupar y alinear elementos.
	 * @param string $contents
	 * Contenido del contenedor, p.e. texto
	 * @param string $name
	 * ID del elemento para referenciarlo en JS
	 * @param int $align
	 * (opcional) Alineacion del contenido, 0=ninguna,1=izquierda,2=derecha,3=centrado
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
	public function divc($contents='', $name='', $align=3, $clase='', $estilo='')
	{
		$this->div($name, $align, $clase, $estilo);
		$this->buf .= $contents;
		$this->divx();
	}
	/**
	 * [EN DESARROLLO] Agrega enlace a el archivo $fn con codigo .css
	 * @param string $fn
	 * Nombre del archivo CSS como un string o un array de string para multiples archivos.
	 * @param bool $ruta
	 * (opcional) Ruta a el(los) archivo(s) .css, por defecto RUTA_CSS
	 * @author victor.sanchez
	 */
	public function css($fn='', $ruta=false)
	{

		$rutaTotal = '';
		if (gettype($ruta) == 'boolean')
		{
			if (defined('RUTA_CSS')){
				$ruta = RUTA_CSS;
			}
		}else if(gettype($ruta) == 'string'){
			$ruta = RUTA_CSS.$ruta;
		}
		$rutaTotal = $_SERVER['DOCUMENT_ROOT'].$ruta;

		if (gettype($fn) == 'string')
		{
			if (file_exists($rutaTotal.$fn)){
				$this->buf .= "<link rel='stylesheet' type='text/css' href='{$ruta}{$fn}'>";
			}
		}
		if (gettype($fn) == 'array')
		{
			foreach ($fn as $fns)
			{
				if (file_exists($rutaTotal.$fns)) $this->buf .= "<link rel='stylesheet' type='text/css' href='{$ruta}{$fns}'>";
			}
		}
	}
	/**
	 * 
	 * Agrega enlace a el archivo $fn con codigo JS
	 * @param variant $fn
	 * Nombre del archivo JS como un string o un array de string para multiples archivos
	 * @param string $ruta
	 * (opcional) Ruta a el(los) archivo(s) .js, por defecto RUTA_JS
	 */
	public function js($fn='', $ruta=false)
	{

		$rutaTotal = '';
		if (gettype($ruta) == 'boolean')
		{
			if (defined('RUTA_JS')){ 
				$ruta = RUTA_JS;
			}
		}else if(gettype($ruta) == 'string'){
			$ruta = RUTA_JS.$ruta;
		}
		$rutaTotal = $_SERVER['DOCUMENT_ROOT'].$ruta;
		
		if (gettype($fn) == 'string')
		{
			if (file_exists($rutaTotal.$fn)){ 
				//$this->buf .= "<script language=\"JavaScript\" src=\"{$ruta}{$fn}\"></script>";
				$this->buf .= "<script type='text/javascript' src='{$ruta}{$fn}'></script>";
			}
		}
		if (gettype($fn) == 'array')
		{
			foreach ($fn as $fns)
			{
				//if (file_exists($rutaTotal.$fns)) $this->buf .= "<script language=\"JavaScript\" src=\"{$ruta}{$fns}\"></script>";
				if (file_exists($rutaTotal.$fns)) $this->buf .= "<script type='text/javascript' src='{$ruta}{$fns}'></script>";
			}
		}
	}
	/**
	 * 
	 * Agrega codigo JS incrustado
	 * @param String $src
	 * Codigo JS
	 */	
	public function jsi($src='')
	{
		$this->buf .= "<script type='text/javascript'>$src</script>";
	}
	/**
	 * 
	 * Eliminar todo el buffer del form
	 */
	public function blank()
	{
		$this->buf = '';
	}
	/**
	 * 
	 * Enviar a impresora. Es necesario configurar el subsistema de impresoras en servidor.
	 */
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
	/**
	 * 
	 * Registra el uso de la opcion del menu. Funcion interna, no usar.
	 * @param int $idopt
	 * @param int $uid
	 * @param int $pid
	 */
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
	/**
	 * 
	 * Devuelve el nivel de acceso de un usuario para un modulo
	 * No verifica login o sesion. Usar solo si esta iniciada sesion. 
	 * @param int $modulo_id
	 * ID del modulo
	 * @param int $uid
	 * (opcional) ID del usuario
	 * @param int $pid
	 * (opcional) ID del proyecto
	 * @return int
	 * Retorna el nivel de autorizacion, USUARIO_UNAUTH si no esta autorizado
	 */
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
	/**
	 * 
	 * Chequear si el usuario activo tiene permisos para acceder el modulo especificado.
	 * @param int $idopt
	 * ID del modulo, especificado en la tabla 'modulos'
	 * @param int $nivelmin
	 * Nivel de acceso necesario, debe ser igual o menor al especificado en tabla 'acceso'
	 * @param bool $xerr
	 * Deprecated, no usar, por defecto false
	 * @param bool $verbose
	 * Indica si muestra mensajes de error
	 */
	public function usrchk($idopt, $nivelmin, $xerr=false, $verbose=true)
	{
		if ($this->rpt_id == 0) $this->rpt_id = $idopt;
		if ($this->form_id == 0) $this->form_id = $idopt;
		$this->usrlv = USUARIO_UNAUTH;
		$this->usrnm = "(NO_AUTORIZADO)";
		$myerr = 'desconocido';
		$laurl = $_SERVER['REQUEST_URI'];
		//error_log($laurl);
		if (isset($_SESSION["uid"]) && isset($_SESSION["pid"]))
		{			
			$uid = $_SESSION['uid'];
			$pid = $_SESSION['pid'];
			//error_log('u='.$uid.','.$pid);
            if (isset($_COOKIE['junk']))
            {
                $junky = $_COOKIE['junk'];
            }
            else
            {
                error_log('Login cookie not found');
                $junky = $_SESSION['junk'];
            }
            if (isset($_SESSION['unm'])) $this->usrnm = $_SESSION['unm'];
			
			// TODO: check kerberos ticket
			$session_tbl = "es";
			if (isset($_SESSION["session_tbl"])) $session_tbl = $_SESSION["session_tbl"];  
			$q = sprintf("SELECT abierta FROM %s WHERE uid=%d AND junky='%s' AND abierta=1",
				$session_tbl, $uid, $junky);
			//error_log($q);
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
								if ($verbose)
								{
									if ($xerr) $this->xerr('USR-5', "El usuario no est&aacute; autorizado para usar este m&oacute;dulo.");
									else
									{
										$this->err('USR-5', "El usuario no est&aacute; autorizado para usar este m&oacute;dulo.");
										$this->volver("menu.php", "Volver al menu inicial");
									}
								}
								$this->usrlv = USUARIO_UNAUTH;
							}
							else
							{
								if ($verbose) $this->submenu_uso($idopt, $uid, $pid);
							}
						}
						else
						{ 
							if ($verbose)
							{
								if ($xerr) $this->xerr('USR-4', "El usuario no posee privilegios de acceso para este m&oacute;dulo.");
								else $this->err('USR-4', "El usuario no posee privilegios de acceso para este m&oacute;dulo.");
								$this->volver("menu.php", "Volver al menu inicial");
							}
						}
					}
					elseif ($verbose)
					{ 
						if ($xerr) $this->qxerr('USR-3'); else $this->qerr('USR-3');
					}
				}
				elseif ($verbose)
				{ 
					if ($xerr) $this->xerr('USR-2', "Usuario no registrado en la tabla de sesiones.");
					else $this->err('USR-2', "Usuario no registrado en la tabla de sesiones.");
					login_popup($this, $laurl);
				}
				mysql_free_result($res);
			}
			elseif ($verbose)
			{
				if ($xerr) $this->qxerr('USR-1'); else $this->qerr('USR-1');
			}
		}
		else
		{
			//error_log('Variables de sesion no creadas');
			if ($xerr) $this->buf .= "{-1}";
			login_popup($this, $laurl);
		}
		
		return $this->usrlv;
	}
	
	/**
	 * 
	 * Chequeo rapido de permisos de usuario para un modulo especifico
	 * @param int $modulo_id
	 * ID del modulo
	 * @param int $nivel
	 * Nivel de acceso, por defecto 2
	 * @param int $uid
	 * UID del usuario, por defecto el UID activo
	 * @param int $proyecto_id
	 * ID del proyecto, por defecto el proyecto activo
	 * @return boolean
	 * Indica si tiene o no permiso para el modulo
	 */
	public function modcheck($modulo_id, $nivel=2, $uid=0, $proyecto_id=0)
	{
		$isok = FALSE;
		if ($proyecto_id == 0) $proyecto_id = $this->pid;
		if ($uid == 0) $uid = $this->uid;
		if (($acc = lt_registro::crear($this, 'acceso', array($uid, $modulo_id, $proyecto_id))))
		{
			if ($acc->v->usertype_id <= $nivel) $isok = TRUE;
		}
		return $isok;
	}
	/**
	 * 
	 * Genera un enlace a menu.php
	 */
	public function menuprinc()
	{
		$this->buf .= "<p align=\"center\">Favor acceder este m&oacute;dulo desde " .
			"el <a href=\"menu.php\">menu principal</p>";
	}
	/**
	 * 
	 * Crea un GIF animado para indicar esperas en peticiones Ajax, usar conjuntamente con ltform_wait() en JS
	 */
	public function wait_icon()
	{
		if (!$this->have_wait)
		{
			$this->buf .= '<div name="waiticon" id="waiticon" style="visibility:hidden;'.
				'position:absolute;z-index:150;background:transparent;text-align:center;'.
				'left:50%;top:50%;margin-left:-100px;margin-top:-100px;">'.
				'<img src="wait.gif" name="waiticon" id="waiticon" /></div>';
		}
		$this->have_wait = true;
	}
	/**
	 * 
	 * Funcion interna, no usar.
	 */
	public function msg()
	{
		if (!$this->_have_msg)
		{
			$this->buf .= "<div name=\"ltform_msg\" id=\"ltform_msg\" " .
				"style=\"position:absolute;z-index:105;top:100px;left:300px;visibility:hidden;background:white;".
				"text-align:center;border:5px solid black;-moz-box-shadow: 10px 10px 5px #888; ".
				"-webkit-box-shadow: 10px 10px 5px #888; box-shadow: 10px 10px 5px #888;\">".
				"<div name=\"ltform_msg_p\" id=\"ltform_msg_p\" ".
				"style=\"text-align:center;margin:10px;background:white;\"></div></div>";
			$this->_have_msg = TRUE;
		}
	}
	/**
	 * 
	 * Enlace para reemplazar la pagina actual con la especificada en el URL, sin dejar historial. 
	 * @param string $tourl
	 * URL a seguir
	 * @param string $caption
	 * (opcional) Texto a mostrar
	 */
	public function volver($tourl, $caption="Volver al formulario anterior")
	{
		$this->par();
		$this->lnk_js("document.location.replace('$tourl');", $caption);
		$this->parx();
	}
	public function ir($tourl, $caption)
	{
		$this->lnk_js("document.location.replace('".$tourl."');", $caption);
	}
	/**
	 * 
	 * Especifica una funcion JS que se ejecutara al cargar la pagina 
	 * @param string $jsinitfn
	 * Nombre de la funcion
	 */
	public function onload($jsinitfn='ltform_ctrl_init')
	{
		$this->build_init();
		$this->buf .= sprintf("<script language=\"JavaScript\">window.onload=%s;</script>", $jsinitfn);
	}
	/**
	 * 
	 * Genera un registro en la tabla ltlog, funcion interna, no usar.
	 * @param string $tabla
	 * @param string $valor
	 * @param string $msg
	 * @param int $local_id
	 * @param int $cliente_id
	 */
	public function wlog($tabla, $valor, $msg, $local_id=0, $cliente_id=0)
	{
		$isok = false;
		
		$q = sprintf("INSERT INTO ltlog VALUES (0, '%s', '%s', '%s', %d, %d, '%s', NOW(), %d, %d)",
			mysql_real_escape_string($tabla), mysql_real_escape_string($valor), 
			mysql_real_escape_string($msg), $_SESSION['uid'], $_SESSION['pid'], 
			$_SERVER['REMOTE_ADDR'], $local_id, $cliente_id);
		if (mysql_query($q) !== false)
		{
			$isok = true;
		}
		else $this->qerr("LOG-1");
		
		return $isok;
	}
	/**
	 * 
	 * Genera enlace a popup. Deprecated.
	 * @param unknown_type $name
	 * @param unknown_type $url
	 * @param unknown_type $caption
	 * @param unknown_type $width
	 * @param unknown_type $height
	 * @param unknown_type $hint
	 */
	public function lnk_popup($name, $url, $caption, $width=500, $height=400, $hint="")
	{
		if ($hint != "") $shint = sprintf(" title=\"%s\"", $hint); else $shint = "";
		$pop = sprintf("PopupCenter('%s','%s', %d, %d);", $url, $name, $width, $height);
		$this->buf .= sprintf("<a href=\"javascript:void(0);\" " .
			"onclick=\"%s\"%s>%s</a>", $pop, $shint, $caption);
	}
	/**
	 * 
	 * Enlace a codigo JS, sirve para ejecutar codigo JS con clicks
	 * @param string $jscode
	 * Codigo JS a ejecutar
	 * @param string $caption
	 * Texto a mostrar. Si tipo=1 (imagen), URL de la imagen
	 * @param string $hint
	 * (opcional) Tooltipo, texto explicativo que se muestra ubicarse encima del control
	 * @param string $style
	 * (opcional) Estilo CSS a aplicar
	 * @param int $tipo
	 * Tipo de enlace, 0=enlace de texto (por defecto), 1=imagen
	 */
	public function lnk_js($jscode, $caption, $hint="", $style="", $tipo=0)
	{
		if ($hint != "") $shint = sprintf(" title=\"%s\"", $hint); else $shint = "";
		if ($style != "") $sstyle= sprintf(" style=\"%s\"", $style); else $sstyle = "";
		if ($tipo == 0) $this->buf .= sprintf("<a href=\"javascript:void(0);\" onclick=\"%s\"%s%s>%s</a>",
			$jscode, $shint, $sstyle, $caption);
		if ($tipo == 1) $this->buf .= sprintf("<img src=\"%s\" onclick=\"%s\"%s%s></img>",
			$caption, $jscode, $shint, $sstyle);
	}
	/**
	 * 
	 * Generar un boton de envio por email, util para enviar reportes y formularios.
	 */
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
		$tx0->render($this);
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
	/**
	 * 
	 * Enviar contenido del buffer a la(s) direccion(es) de email especificadas por $addrto
	 * @param string $addrto
	 * Direccion(es) de email del destinatario, si es mas de una, separar con comas 
	 * @param string $subj
	 * (opcional) Titulo (subject) del mensaje de correo
	 * @param string $from
	 * (opcional) Direccion de correo del que envia el correo
	 * @param string $reply_to
	 * (opcional) Direccion de correo indica al receptor para las respuestas
	 */
	public function email($addrto, $subj="", $from='"Sistemas OrionCorp" <orioncorp.backup@gmail.com>',
			$reply_to='')
	{
		$this->setEmailed(FALSE);
		if ($subj == "") $subj = "Notificacion del sistema MPRS";		
		require_once('Classes/PHPMailer/class.phpmailer.php');
		$mail             = new PHPMailer();
		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host       = "ssl://smtp.gmail.com"; // SMTP server
		$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
		$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
		$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
		$mail->Username   = "orioncorp.backup@gmail.com";  // GMAIL username
		$mail->Password   = "lr3492114";            // GMAIL password
		$mail->SetFrom('orioncorp.backup@gmail.com', 'OrionCorp');
		if ($reply_to!='') $mail->AddReplyTo($reply_to, '');
		$mail->Subject    = $subj;
		//$mail->AltBody  = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$mail->MsgHTML($this->buf);
		if (gettype($addrto) == 'string') $mail->AddAddress($addrto, "");
		if (gettype($addrto) == 'array')
		{ 
			foreach ($addrto as $addr) $mail->AddAddress($addr, "");
		}
		if(!$mail->Send())
		{
			$this->warn( "Mailer Error: " . $mail->ErrorInfo );
		}
		else $this->setEmailed(TRUE);
	}
	function submenubuild($uid, $pid, $menu_id, $titulo, $pista)
	{
		$sin_tooltip = $uid == 9 || $uid == 10;
		$qx = new myquery($this, sprintf("SELECT a.titulo, a.pista, a.submenu_id, url, havechildren, newwnd " .
				"FROM submenus a " .
				"LEFT JOIN modulos b ON a.submenu_id=b.submenu_id " .
				"WHERE (a.menu_id=%d) AND (parent_id=0) AND (submenu_children(a.submenu_id, %d, %d) > 0)" .
				"ORDER BY ordenv", $menu_id, $uid, $pid),'SUBMENU-1');
		//$fo->parc($qx->q);
		if ($qx->isok)
		{
			$ssel = '';
			$bf = '';
			$nitems = 0;
			if ($sin_tooltip) $bf .= sprintf("<li><a href=\"javascript:void();\" %s>%s</a>", $ssel, $titulo);
			else $bf .= sprintf("<li><a href=\"javascript:void();\" title=\"%s\"%s>%s</a>", $pista, $ssel, $titulo);
			$bf .= "<ul>";
			foreach ($qx->a as $mr)
			{
				if ($mr->havechildren == 0)
				{
					$tgt =($mr->newwnd == 1) ? " target=\"_blank\"": "";
					if ($sin_tooltip)
					{
						$bf .= sprintf("<li><a href=\"%s\"%s>%s</a></li>",
								$mr->url, $tgt, $mr->titulo);
					}
					else
					{
						$bf .= sprintf("<li><a href=\"%s\" title=\"%s\"%s>%s</a></li>",
							$mr->url, $mr->pista, $tgt, $mr->titulo);
					}
				}
				else
				{
					if ($sin_tooltip)
					{
						$bf .= sprintf("<li class=\"submenu\"><a href=\"%s\">%s &rarr;</a>",
								$mr->url, $mr->titulo);
					}
					else
					{
						$bf .= sprintf("<li class=\"submenu\"><a href=\"%s\" title=\"%s\">%s &rarr;</a>",
							$mr->url, $mr->pista, $mr->titulo);
					}
					$bf .= '<ul>';
					$qbot = new myquery($this, sprintf("SELECT a.titulo, a.pista, a.submenu_id, url, newwnd " .
							"FROM submenus a " .
							"LEFT JOIN modulos b ON a.submenu_id=b.submenu_id ".
							"LEFT JOIN acceso c ON b.modulo_id=c.modulo_id " .
							"WHERE a.parent_id=%d AND c.uid=%d AND c.proyecto_id=%d " .
							"ORDER BY ordenv,titulo", $mr->submenu_id, $uid, $pid),'SUBMENU-2');
					//error_log($qbot->q);
					if ($qbot->isok)
					{
						if ($sin_tooltip)
						{
							foreach ($qbot->a as $bt)
							{
								$tgt =($bt->newwnd == 1) ? " target=\"_blank\"": "";
								$bf .= sprintf("<li><a href=\"%s\"%s>%s</a></li>",
										$bt->url, $tgt, $bt->titulo);
								$nitems++;
							}
						}
						else
						{
							foreach ($qbot->a as $bt)
							{
								$tgt =($bt->newwnd == 1) ? " target=\"_blank\"": "";
								$bf .= sprintf("<li><a href=\"%s\" title=\"%s\"%s>%s</a></li>",
										$bt->url, $bt->pista, $tgt, $bt->titulo);
								$nitems++;
							}
						}
					}
					$bf .= '</ul></li>';
				}
			}
			$bf .= "</ul></li>";
			if ($nitems > 0) $this->buf .= $bf;
			$bf = '';
		}
	}
	function menubuild($uid, $pid)
	{
		$qmn = new myquery($this, "SELECT menu_id, titulo, pista FROM menus ORDER BY ordenv");
		//$fo->parc($qmn->q);
		if ($qmn->isok)
		{
			$this->buf .= '<div class="nav"><ul id="menu" class="menu">';
			foreach ($qmn->a as $rx)
			{
				$this->submenubuild($uid, $pid, $rx->menu_id, $rx->titulo, $rx->pista);
			}
			$this->buf .= '</ul></div>';
			$this->jsi("var dropdown=new TINY.dropdown.init(\"dropdown\", {id:'menu', active:'menuhover'});");
		}
	}
	
	/**
	 * 
	 * Crea un control para subir archivos
	 * @param string $nombre
	 * Nombre/ID del control para referenciarlo en JS
	 * @param string $titulo
	 * Titulo del boton
	 * @param string $estilo
	 * Estilo CSS (opcional)
	 * @param string $clase
	 * Clase CSS (opcional)
	 * @param string $aceptar
	 * Conjunto de tipos MIME a aceptar, separados por coma (opcional)
	 * @param string $valid_fn
	 * Funcion JS a ejecutar en OnChange() (opcional)
	 * @param string $onkey_fn
	 * Funcion JS a ejecutar en OnKey() (opcional)
	 */
	function file_upload($nombre, $titulo='', $estilo='', $clase='', $aceptar='', $valid_fn='', $onkey_fn='')
	{
		$futmp = new lt_file_upload();
		$futmp->n = $nombre;
		$futmp->title = $titulo;
		$futmp->style = $estilo;
		$futmp->css_class = $clase;
		$futmp->accept = $aceptar;
		$futmp->valid_fn = $valid_fn;
		$futmp->onkey_fn = $onkey_fn;
		$futmp->render($this);
	}
	/**
	 * 
	 * Deprecated, no usar.
	 * @param unknown_type $ecode
	 */
	/**
     *
	 * Deprecated, no usar.
	 * @param unknown_type $ecode
	 */
	public function qxerr($ecode='')
	{
		return $this->xerr($ecode, $_SESSION['uid'] == 1 ? mysql_error(): "Fall&oacute; consulta de datos");
	}
	/**
	 *
	 * Deprecated, no usar.
	 * @param unknown_type $ecode
	 */
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
	/**
	 *
	 * Deprecated, no usar.
	 * @param unknown_type $ecode
	 */
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
	/**
	 *
	 * Deprecated, no usar.
	 * @param unknown_type $ecode
	 */
	public function selx()
	{
		$this->buf .= "</select>";
		return "</select>";
	}
	/**
	 *
	 * Deprecated, no usar.
	 * @param unknown_type $ecode
	 */
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
		$tmpct->render($this);
		if ($this->form_autoparms) $this->form_parms .= sprintf(";%s,%s", $nombre, $t);
	}
	/**
	 * 
	 * Deprecated.
	 * @param unknown_type $msg
	 * @param unknown_type $ctrlnm
	 * @param unknown_type $ischk
	 */
	public function rpt_chk($msg, $ctrlnm, $ischk)
	{
		$this->tr();
		$this->th($msg);
		$this->td(3);
		$this->chk($ctrlnm, $ischk, '');
		$this->tdx();
	}
	/**
	 *
	 * Abre un tag UL para una lista sin orden.
	 * @param string $name
	 * ID del elemento para referenciarlo en JS
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar por defecto 'display:none;'
	 */
	public function ul($name='', $clase='', $estilo='')
	{
		$this->buf .= "<ul";
		if ($name != '') $this->buf .= sprintf(" name=\"%s\" id=\"%s\"", $name, $name);
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		$this->buf .= ">";
	}
	/**
	 *
	 * Cerrar tag UL
	 */
	public function ulx()
	{
		$this->buf .= "</ul>";
	}
   /**
    * Abre un tag OL para una lista ordenada.
    * @param string $name
    * ID del elemento para referenciarlo en JS
    * @param string $clase
    * (opcional) Clase CSS a aplicar
    * @param string $estilo
    * (opcional) Estilo CSS a aplicar
    */
   public function ol($name='', $clase='', $estilo='')
   {
      $this->buf .= "<ol";
      if ($name != '') $this->buf .= sprintf(" name=\"%s\" id=\"%s\"", $name, $name);
      if ($estilo != '') $this->buf .= " style=\"$estilo\"";
      if ($clase != '') $this->buf .= " class=\"$clase\"";
      $this->buf .= ">";
   }
   /**
    *
    * Cerrar tag OL
    */
   public function olx()
   {
      $this->buf .= "</ol>";
   }
   
	public function li($name='', $clase='', $estilo='')
	{
		$this->buf .= "<li";
		if ($name != '') $this->buf .= sprintf(" name=\"%s\" id=\"%s\"", $name, $name);
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		$this->buf .= ">";
	}
	/**
	 *
	 * Cerrar tag LI
	 */
	public function lix()
	{
		$this->buf .= "</li>";
	}	
	
	/**
	 * 
	 * Crea un IFRAME
	 * @param string $src
	 * URL fuente del IFRAME
	 * @param string $clase
	 * (opcional) Clase CSS
	 * @param string $estilo
	 * (opcional) Estilo inline CSS
	 * @param string $name
	 * (opcional) Nombre del IFRAME
	 * @param string $atributos_ex
	 * (opcional) Atributos extendidos
	 */
	public function iframe($src, $clase='', $estilo='', $name='', $atributos_ex='')
	{
		$this->buf .= sprintf("<iframe src=\"%s\"", $src);
		if ($clase != '') $this->buf .= " class=\"$clase\"";
		if ($estilo != '') $this->buf .= " style=\"$estilo\"";
		if ($name != '') $this->buf .= sprintf(" name=\"%s\" id=\"%s\"", $name, $name);
		if ($atributos_ex != '') $this->buf .= ' '.$atributos_ex; 
		$this->buf .= ">Sin soporte para IFRAME</iframe>";
	}
	/**
	 *
	 * Menu de botones
	 * @param array $acciones
	 * Array de tres elementos (accion, titulo, tipo de accion, id) 
	 * @param int $ancho
	 * Ancho en pixeles del menu
	 * @param string $clase
	 * Clase CSS a aplicar
	 * @param string $estilo
	 * Estilo CSS a aplicar 
	 */
	public function split_button(array &$acciones, $ancho, $clase='', $estilo='')
	{
		$accion = $acciones[0][0];
		$tipo = isset($acciones[0][2]) ? $acciones[0][2]: LT_SPLBT_JS;
		if ($tipo == LT_SPLBT_NEWWND) 
			$accion = sprintf("window.open('%s');", $accion);
		if ($tipo == LT_SPLBT_SETURL) 
			$accion = sprintf("document.location='%s';", $accion);
		if ($tipo == LT_SPLBT_REPLACEURL) 
			$accion = sprintf("document.location.replace('%s');", $accion);
		$this->buf .= '<script>$j(function() {
			$j( "#'.$acciones[0][3].'" )
			.button()
			.click(function() {
				'.$accion.'
			})
			.next()
			.button({
				text: false,
				icons: {
					primary: "ui-icon-gear" }
			})
			.click(function() {
				var menu = $j( this ).parent().next().show().position({
					my: "left top",
					at: "left bottom",
					of: this
				});
				$j( document ).one( "click", function() {
					menu.hide();
				});
				return false;
			})
			.parent()
			.buttonset()
			.next()
			.hide()
			.menu();
		});
		</script>';
		$this->buf .= sprintf("<style>.ui-menu { position: absolute; width: %dpx; }</style>", $ancho);
		$this->div('', 3);
		$this->div();
		$this->butt($acciones[0][1], '', $acciones[0][3]);
		$this->butt('Seleccione una acci&oacute;n');
		$this->divx();
		$this->ul('', $clase, "display:none;".$estilo);
		$accsz = sizeof($acciones);
		for ($ndx = 1; $ndx < $accsz; $ndx++)
		{
			$acc = &$acciones[$ndx];
			$this->li(isset($acc[3]) ? $acc[3]: '');
			if (isset($acc[2])) $acx = $acc[2]; else $acx = LT_SPLBT_JS;
			if ($acx == LT_SPLBT_NEWWND) $this->lnk($acc[0], $acc[1], '', '', '_blank');
			if ($acx == LT_SPLBT_SETURL) $this->lnk($acc[0], $acc[1]);
			if ($acx == LT_SPLBT_JS) $this->lnk_js($acc[0], $acc[1]);
			if ($acx == LT_SPLBT_REPLACEURL) 
				$this->lnk_js(sprintf("document.location.replace('%s');", $acc[0]), $acc[1]);
			$this->lix();
		}
		$this->ulx();
		$this->divx();		
	}
   /**
    *
    * Menu desplegable, boton con icono
    * @param string $id
    * ID del boton para referenciarlo en JS
    * @param array $acciones
    * Array de cuatro elementos (accion, titulo, tipo de accion, id) 
    * @param int $ancho
    * Ancho en pixeles del menu
    * @param string $clase
    * Clase CSS a aplicar
    * @param string $estilo
    * Estilo CSS a aplicar 
    */
	public function menubut_ico($id,$icono,array &$acciones, $ancho, $clase='', $estilo='')
	{
		$this->buf .= '<script>$j(function() {
		$j( "#'.$id.'" )
		.button({
		text: false,
		icons: { primary: "'.$icono.'" }
	})
	.click(function() {
	var menu = $j( this ).parent().next().show().position({
	my: "left top",
	at: "left bottom",
	of: this
	});
	$j( document ).one( "click", function() { menu.hide(); });
	return false;
	})
	.parent()
	.next()
	.hide()
	.menu();
	});
	</script>';
		$this->buf .= sprintf("<style>.ui-menu { position: absolute; width: %dpx; }</style>", $ancho);
		$this->div('', 3);
		$this->div();
		$this->butt('Seleccione una acci&oacute;n','', $id);
		$this->divx();
		$this->ul('', $clase, "display:none;".$estilo);
		$accsz = sizeof($acciones);
		for ($ndx = 0; $ndx < $accsz; $ndx++)
		{
			$acc = &$acciones[$ndx];
			$this->li(isset($acc[3]) ? $acc[3]: '');
			if (isset($acc[2])) $acx = $acc[2]; else $acx = LT_SPLBT_JS;
			if ($acx == LT_SPLBT_NEWWND) $this->lnk($acc[0], $acc[1], '', '', '_blank');
			if ($acx == LT_SPLBT_SETURL) $this->lnk($acc[0], $acc[1]);
			if ($acx == LT_SPLBT_JS) $this->lnk_js($acc[0], $acc[1]);
			if ($acx == LT_SPLBT_REPLACEURL)
				$this->lnk_js(sprintf("document.location.replace('%s');", $acc[0]), $acc[1]);
			$this->lix();
		}
		$this->ulx();
		$this->divx();
	}
	
	/**
	 * 
	 * Asignar valores del registro al set de controles del formulario
	 * @param lt_registro $r
	 * Registro de donde seran leidos los valores
	 */
	public function asignar(lt_registro $r)
	{
		foreach ($this->ctrl as &$ct)
		{
			$cn = $ct->no == '' ? $ct->n : $ct->no;
			if (isset($r->v->$cn))
			{ 
				$ct->assign($r->v($cn));
				if ($r->campos[$cn]->clave) $ct->primary_key = true;
			}
   		}
	}	
	private function build_init()
	{
		$vs = '';
		foreach ($this->ctrl as $ct)
		{
			if ($ct->init_fn !== '')
			{
				$initp = sprintf("%s%s'%s'", $ct->init_parms, $ct->init_parms == '' ? '':',', $ct->sufijo);
				$vs .= sprintf(" %s(\$('%s'),%s); ", $ct->init_fn, $ct->n, $initp);
			}
		}
		if ($vs != '') $this->jsi(sprintf("function ltform_ctrl_init(){ %s return true;}", $vs));
	}
	private function ctrl_find($nombre)
	{
		$ctx = false;
		foreach ($this->ctrl as $ct)
		{
			if ($ct->n == $nombre)
			{
				$ctx = $ct;
				break;
			}
		}
		return $ctx;
	}
	public function ajax_request(array $ctrl, $nombre, $procesador, $linea=-1, $async=true)
	{
		$vs = '';
		$fp = '';
		$sufijo = '';
		if ($linea != -1) $sufijo = sprintf("_L%d", $linea);
		foreach ($ctrl as $ct)
		{
			$op = $vs == '' ? '?':'&';
			if (gettype($ct) == 'object')
			{	
				$vs .= sprintf("+'%s%s='+\$('%s').value", $op, $ct->no, $ct->n);
				$fp .= sprintf(";%s,%s", $ct->no, $ct->t);
			}
			if (gettype($ct) == 'array')
			{	
				$vs .= sprintf("+'%s%s='+\$('%s%s').value", $op, $ct[0], $ct[0], $sufijo);
				$fp .= sprintf(";%s,%s", $ct[0], $ct[1]);
			}
			if (gettype($ct) == 'string')
			{	
				if (($ctx = $this->ctrl_find($ct)) !== false)
				{
					$vs .= sprintf("+'%s%s='+\$('%s').value", $op, $ctx->no, $ctx->n);
					$fp .= sprintf(";%s,%s", $ctx->no, $ctx->t);
				}
			}
		}
		if ($vs != '')
		{
			$w1 = $w2 = '';
			if ($this->have_wait)
			{
				$w1 = 'ltform_wait(true);';
				$w2 = 'ltform_wait(false);';
			}
			if ($linea != -1) { 
				$vs .= sprintf("+'&_linea=%d'", $linea);
				$fp .= ';_linea,i';
			}
			if ($async)
			{
				$this->jsi(sprintf("function %s%s() { ".
						"var qq=encodeURI('%s'+%s+'&_formparms=%s'); %s ".
						"new Ajax.Request(qq, { method: 'get', ".
						"onSuccess: function(request){ %s %s_pp(true,request.responseJSON,%d,'%s');}, ".
						"onFailure: function(request){ %s %s_pp(false,request.responseJSON,%d,'%s');}}); ".
						"}",
						$nombre, $sufijo, $procesador, substr($vs, 1), substr($fp, 1), $w1,
						$w2, $nombre, $linea, $sufijo, $w2, $nombre, $linea, $sufijo));
			}
		}
	}
	/**
	 * 
	 * Vuelva el contenido de una variable (debug)
	 * @param variant $var
	 */
	public function dump($var)
	{
		$this->parc(print_r($var, true));
	}
}
?>