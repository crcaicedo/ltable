<?php
define('LT_CTRL_SET_AJAX', 1);
define('LT_CTRL_SET_FORM', 2);

define('LT_REGISTRO_UPDATER', 'ltable_olib_registro_up.php');
define('LT_REGISTRO_DELETER', 'ltable_olib_registro_del.php');

define('LT_FUPLOADER_T', 'multipart/form-data');

class lt_ctrl_set_item
{
	/**
	 * 
	 * @var string $t
	 * Tipo de control
	 * @var string $e
	 * Etiqueta adjunta
	 * @var int $al
	 * Alineacion visual
	 * @var object $c
	 * Control adjunto
	 * @var bool $to_update
	 * Indica si campo participa de la actualizacion
	 * @var bool $sin_celda
	 * (opcional) Indica si el campo no se renderizara en una celda individual, por defecto FALSE
	 */
	public $t, $e, $c;
	public $al, $estilo = '', $clase = '';
	private $_validate_update = true, $_to_update = true, $_sin_celda = false;
	function __construct($etiqueta, $tipo, $alineacion=LT_ALIGN_DEFAULT, $to_update=true, $sin_celda=false)
	{
		$this->t = $tipo;
		$this->e = $etiqueta;
		$this->setToUpdate($to_update);
		if ($alineacion == LT_ALIGN_DEFAULT)
			$this->al = strpos('e,b,u', $tipo) !== false ? LT_ALIGN_CENTER: LT_ALIGN_LEFT;
		else $this->al = $alineacion;
		$this->setSinCelda($sin_celda);
	}
	public function setSinCelda($valor)
	{
		$this->_sin_celda = $valor;
	}
	public function getSinCelda()
	{
		return $this->_sin_celda;
	}
	/**
	 * 
	 * Indicar si se valida al momento de actualizar
	 * @param bool $valor
	 */
	public function setValidateUpdate($valor)
	{
		$this->_validate_update = $valor;
	}
	/**
	 * 
	 * Indica si se valida al momento de actualizar
	 */
	public function getValidateUpdate()
	{
		return $this->_validate_update;
	}
	
	/**
	 * 
	 * Indica si participa de la actualizacion
	 */
	public function getToUpdate()
	{
		return $this->_to_update;
	}
	/**
	 * 
	 * Indicar si participa de la actualizacion
	 * @param unknown $valor
	 */
	public function setToUpdate($valor)
	{
		$this->_to_update = $valor;
	}
	public function setClass($class)
	{
		$this->clase = $class;
	}
	public function addClass($class)
	{
		$this->clase .= ' '.$class;
	}
	public function setStyle($style)
	{
		$this->estilo = $style;
	}
	public function addStyle($style)
	{
		$prefijo = '';
		if ($this->estilo != '')
		{
			if (substr($this->estilo, -1, 1) != ';') $prefijo = ';';
		}
		$this->estilo .= $prefijo.$style;
	}
	public function setAlignment($alignment)
    {
        $this->al = $alignment;
    }
}

class lt_ctrl_set_chkgrp
{
	public $a = array(), $sz = 0;
	function __construct(lt_form $fo, $nombre, array $chkdef, $align=LT_ALIGN_RIGHT)
	{
		$this->a = array();
		$this->sz = 0;
		foreach ($chkdef as $it)
		{
			$cn = sprintf("%s[%d]", $nombre, $this->sz);
			$this->a[$this->sz] = new lt_checkbox($fo);
			if (isset($it[1])) $this->a[$this->sz]->caption = $it[1];
			$this->a[$this->sz]->caption_align = $align;
			$this->a[$this->sz]->n = $cn;
			$this->a[$this->sz]->assign($it[0]);
			$this->sz++;
		}
	} 
}

class lt_ctrl_set
{
	public $a = array(), $sz = 0, $ndx = 0, $c, $sufijo = '', $linea = -1, $fo;
	public $etiqueta_unida = false, $tipo = LT_CTRL_SET_AJAX, $con_etiquetas = true;
	public $cv = array(), $tabla = '', $nombre_clave = '', $valor_clave = 0, $ruta = '', $sSufijo = '', $autovar_ex = FALSE;
	private $n = '', $no = '', $proc = '', $proc_del='', $async_update = true, $_newwnd=true;
	private $_nuevo = FALSE, $_info_registro = FALSE;
	private $_custom_update = false, $_custom_postproc = false, $_custom_postproc_del = false;
	private $_evento_update = '', $_evento_atrapar = false, $_tipomime='';
	private $_postproceso_fn = '', $_postproceso_del_fn = '', $_postproceso_reload = 0; 
	private $_preproceso_fn = '', $_preproceso_del_fn = '';
	private $_update_fn = '', $_update_fn_parms = '';
	private $_upbtt = '', $_style_all = '', $_style_by = array(), $_ro = false, $_tabindex = 0, $_enabled=true; 
	private $_linea_nuevas = TRUE, $_linea_campo = 'linea';
	
	// *** CREACION OBJETO ***
	
	/**
	 * 
	 * Crear un conjunto de controles 
	 * @param string $nombre
	 * (opcional) Nombre del conjunto de controles
	 * @param string $procesador
	 * (opcional) Nombre del archivo PHP que procesara la peticion de actualizacion en el servidor
	 * @param string $procesador_borrar
	 * (opcional) Nombre del archivo PHP que procesara la peticion de borrado en el servidor
	 * @param string $sufijo
	 * (opcional) Sufijo a aplicar al nombre de los campos
	 * @param int $linea
	 * (opcional) Numero de linea / detalle, por defecto -1 (no se toma en cuenta) 
	 * @param bool $con_etiquetas
	 * (opcional) Indica si se muestran etiquetas
	 * @param string $tabla
	 * (opcional) Tabla asociada
	 * @param array $valor_clave
	 * (opcional) Valor clave del registro asociado
	 * @param string $campo_linea
	 * (opcional) Campo de ordenacion en detalle, por defecto 'linea'
	 * @param bool $permitir_agregar
	 * (opcional) Permitir agregar registro, por defecto TRUE
	 */
	function __construct(lt_form $fo, $nombre='', $procesador='', $procesador_borrar='', 
			$sufijo='', $linea=-1, $con_etiquetas=true, $tabla = '', array $valor_clave=array(), 
			$campo_linea='linea', $permitir_agregar=TRUE)
	{
		if ($nombre == '') $nombre = sprintf("ctset%04d", mt_rand(1,9999));
		$this->no = $nombre;
		if ($sufijo === '' && $linea > -1) $sufijo = sprintf("_L%d", $linea);
		$this->sufijo = $sufijo;
		$this->sSufijo = sprintf("'%s'", $this->sufijo);
		$this->n = $nombre.$sufijo;
		$this->linea = $linea;
		$this->_linea_campo = $campo_linea;
		$this->_linea_nuevas = $permitir_agregar;
		$this->tabla = $tabla; 
		$this->valor_clave = $valor_clave;
		$this->proc = $procesador;
		$this->proc_del = $procesador_borrar;
		$this->con_etiquetas = $con_etiquetas;
		$this->_evento_update = sprintf("%s-update", $this->n);
		$this->_evento_atrapar = false;
		$this->setPostProcFn();
		$this->setUpdateFn();
		$this->fo = $fo;
	}
	
	/**
	 *
	 * Crear un control set tipo formulario simple
	 * @param lt_form $fo
	 * @param string $procesador
	 * Archivo PHP que procesara los datos
	 * @param string $nombre
	 * (opcional) Nombre identificador del formulario
	 * @param bool $abrir_ventana
	 * (opcional) Por defecto abre una nueva ventana del navegador
	 * @param string $tipo_mime
	 * (opcional) Tipo MIME del formulario
	 * @return lt_ctrl_set $ct
	 * Retorna un control set tipo formulario
	 */
	public static function form(lt_form $fo, $procesador, $nombre='', $abrir_ventana=true, $tipo_mime='')
	{
		$tmpct = new self($fo, $nombre, $procesador);
		$tmpct->tipo = LT_CTRL_SET_FORM;
		$tmpct->setAbrirVentana($abrir_ventana);
		$tmpct->setTipoMIME($tipo_mime);
		return $tmpct;
	}

    public static function formWithFile(lt_form $fo, $procesador, $nombre='', $abrir_ventana=true)
    {
        return lt_ctrl_set::form($fo, $procesador, $nombre, $abrir_ventana, LT_FUPLOADER_T);
    }

	public function setAbrirVentana($bValue)
	{
		$this->_newwnd = $bValue;
	}
	
	public function setTipoMIME($tipo_mime)
	{
		$this->_tipomime = $tipo_mime;
	}

    /**
     * Cargar definicion de formulario desde ltable
     */
	public function cargarLTable()
    {
        $c = new lt_condicion('tabla', '=', $this->tabla);
        if (($q = myquery::t($this->fo, 'ltable_fl', $c, FALSE, FALSE, 'ordenv')))
        {
            // tabla,n,t,l,pd,title,dfc,dfn,dfd,dft,dfh,dfi,dfb,dfm,
            //orden,ctrl_type,ls_tbl,ls_fl_key,ls_fl_desc,vcols,vrows,ro,hidden,
            //dt_auto,valid_fn,valid_parms,mascara,funcion,ordenv,esdato,enabled,
            //init_fn,init_parms,autovar,postvar,ls_fl_order,ls_custom,onkey_fn,onkey_parms,isup,ls_custom_new,dup
            foreach ($q->a as $r)
            {
                $dfvv = 'df'.$r->t;
                $dfv = $r->$dfvv;
                if ($r->hidden) $this->hidden($r->n, $r->t, $dfv);
                else
                {
                    switch ($r->ctrl_type)
                    {
                        case LTO_TEXTBOX:
                        {
                            switch ($r->t)
                            {
                                case 'd': $this->fecha($r->title, $r->n); break;
                                case 'h': $this->hora($r->title, $r->n); break;
                                case 'i': $this->entero($r->title, $r->n, 0, 999999999); break;
                                case 'n': $this->numero($r->title, $r->n, 0, (10 ^ $r->l) - 1, $r->pd); break;
                                default: $this->t($r->title, $r->n, $r->t, $r->l, $r->valid_fn, $r->valid_parms); break;
                            }
                            break;
                        }
                        case LTO_CHECKBOX: $this->chk($r->title, $r->n, $dfv); break;
                        case LTO_EDITBOX: $this->e($r->title, $r->n, $r->t, $r->vcols, $r->vrows, $r->valid_fn, $r->valid_parms); break;
                        case LTO_LISTBOX: $this->l($r->title, $r->n, $r->t, $r->ls_tbl, array($r->ls_fl_key,$r->ls_fl_desc)); break;
                        case LTO_SEPARATOR: $this->separador($r->title); break;
                    }
                    if ($r->ctrl_type != LTO_SEPARATOR) {
                        if ($r->valid_fn != '') $this->cSetValid($r->valid_fn, $r->valid_parms);
                        if ($r->onkey_fn != '') $this->cSetOnkey($r->onkey_fn, $r->onkey_parms);
                        $this->c->funcion = $r->funcion;
                        $this->c->ro = $r->ro;
                    }
                }
            }
            $this->u('Guardar');
        }
    }
	
	public function setRegistroInfo($tabla, $nombre_clave, $valor_clave, $nuevo)
	{
		$this->tabla = $tabla;
		$this->nombre_clave = $nombre_clave;
		$this->valor_clave = $valor_clave;
		$this->_nuevo = $nuevo;
		$this->_info_registro = TRUE;
	}
	
	/**
	 * 
	 * Actualizar/mostrar registro de tabla
	 * @param lt_form $fo
	 * Contexto
	 * @param string $tabla
	 * Nombre de la tabla asociada
	 * @param variant $valor_clave
	 * Valor clave del registro asociado
	 * @param string $procesador
	 * (opcional) URL para procesar actualizacion
	 * @param string $procesador_borrar
	 * (opcional) URL para procesar borrado
	 * @param string $nombre
	 * (opcional) Nombre del ctrl_set
	 * @param bool $con_etiquetas
	 * (opcional) Si se muestran etiquetas al renderizar, por defecto TRUE
	 * @param int $linea
	 * (opcional) Indica la linea en un registro tipo detalle, por defecto -1 (no es detalle)
	 * @param string $campo_linea
	 * (opcional) Campo de ordenacion en detalle, por defecto 'linea'
	 * @param bool $permitir_agregar
	 * (opcional) Permitir agregar registro, por defecto TRUE
	 * @return lt_ctrl_set
	 */
	public static function registro(lt_form $fo, $tabla, $valor_clave, $procesador=LT_REGISTRO_UPDATER, 
		$procesador_borrar='', $nombre='', $con_etiquetas=true, $linea=-1, $campo_linea='linea', 
		$permitir_agregar=TRUE)
	{
		if (gettype($procesador) == 'boolean') $procesador = LT_REGISTRO_UPDATER;
		if (gettype($procesador_borrar) == 'boolean') $procesador_borrar = LT_REGISTRO_DELETER;
		if (gettype($valor_clave) != 'array') $valor_clave = array($valor_clave);
		if (($tmpct = new self($fo, $nombre, $procesador, $procesador_borrar, '', $linea, $con_etiquetas,
			$tabla, $valor_clave, $campo_linea, $permitir_agregar)))
		{
			$tmpct->h('__tabla', 'c', $tabla);
		}
		return $tmpct;
	}
	
	// *** SETTINGS ***
	
	public function setTabIndex($valor)
	{
		$this->_tabindex = $valor;
	}
	
	public function addStyleTo($to, $css)
	{
		$this->_style_by[$to] .= $css;
	}

	public function addStyle($css)
	{
		$this->_style_all .= $css;
	}
	
	public function setStyle($css)
	{
		$this->_style_all = $css;
	}
	
	public function setStyleTo($to, $css)
	{
		$this->_style_by[$to] = $css;
	}
	
	public function setReadOnly($valor)
	{
		$this->_ro = $valor;
		// TODO: loop over controls to set readonly
	}
	
	public function setEnabled($valor)
	{
		$this->_enabled = $valor;		
		// TODO: loop over controls to set enabled
	}
	
	/**
	 * 
	 * Indica si el postproceso por defecto recarga la pagina
	 * @param bool $reload
	 */
	public function setPostProcReload($reload)
	{
		$this->_postproceso_reload = $reload;
	}
	/**
	 * 
	 * Cambia el nombre de funcion JS a ejecutar despues de retornar de una peticion ajax
	 * @param string $JSfunc
	 * (opcional) Nombre de la funcion, por defecto en blanco (accion predeterminada)
	 */
	public function setPostProcFn($JSfunc='')
	{
		if (gettype($JSfunc) == 'string' && $JSfunc != '')
		{
			$this->_custom_postproc = true;
			$this->_postproceso_fn = $JSfunc;
		}
		else
		{ 
			$this->_custom_postproc = false;
			$this->_postproceso_fn = sprintf("%s_postproc", $this->n);
		}
	}
	
	public function setPreProcFn($JSfunc='')
	{
		if (gettype($JSfunc) == 'string' && $JSfunc != '')
		{
			$this->_preproceso_fn = $JSfunc;
		}
		else
		{
			$this->_preproceso_fn = '';
		}
	}
	
	public function setPreProcDelFn($JSfunc='')
	{
		if (gettype($JSfunc) == 'string' && $JSfunc != '')
		{
			$this->_preproceso_del_fn = $JSfunc;
		}
		else
		{
			$this->_preproceso_del_fn = '';
		}
	}
	
	/**
	 * 
	 * Cambia el nombre de funcion JS a ejecutar al hacer click en boton UPDATER
	 * @param string $JSfunc
	 * (opcional) Nombre de la funcion, por defecto en blanco (accion predeterminada)
	 */
	public function setUpdateFn($JSfunc='', $sParms='')
	{
		if (gettype($JSfunc) == 'string' && $JSfunc != '')
		{
			$this->_custom_update = true;
			$this->_update_fn = $JSfunc;
			$this->_update_fn_parms = $sParms;
		}
		else
		{ 
			$this->_custom_update = false;
			$this->_update_fn = sprintf("%s_update", $this->n);
		}
	}
	
	public function enableUpdateEvent()
	{
		$this->_evento_atrapar = true;
	}
	
	public function disableUpdateEvent()
	{
		$this->_evento_atrapar = false;
	}

	
	// *** AGREGAR Y ASIGNAR CONTROLES ***
	
	private function _add($etiqueta, $tipo='', $valor=false, 
			$alineacion=LT_ALIGN_DEFAULT, $nombre='', 
			$clase_item='', $estilo_item='')
	{
		// agregar item
		$this->a[$this->sz] = new lt_ctrl_set_item($etiqueta, $tipo, $alineacion);
		$item = &$this->a[$this->sz];
		// agregar contenido/control del item
		if ($tipo == 's') $this->a[$this->sz]->c = $valor; // string
        if ($tipo == 'p') $this->a[$this->sz]->c = $valor; // separador
		if ($tipo == 't') $this->a[$this->sz]->c = new lt_textbox($this->fo);
		if ($tipo == 'e') $this->a[$this->sz]->c = new lt_editbox($this->fo);
		if ($tipo == 'l') $this->a[$this->sz]->c = new lt_listbox($this->fo);
		if ($tipo == 'b') $this->a[$this->sz]->c = new lt_button($this->fo);
		if ($tipo == 'u') $this->a[$this->sz]->c = new lt_button($this->fo);
		if ($tipo == 'h') $this->a[$this->sz]->c = new lt_ctrl($this->fo);
		if ($tipo == 'a') $this->a[$this->sz]->c = new lt_link($this->fo);
		if ($tipo == 'j') $this->a[$this->sz]->c = new lt_link($this->fo);
		if ($tipo == 'k') $this->a[$this->sz]->c = new lt_checkbox($this->fo);
		if ($tipo == 'g') $this->a[$this->sz]->c = new lt_ctrl_set_chkgrp($this->fo, $nombre, $valor);
		if ($tipo == 'f') $this->a[$this->sz]->c = new lt_file_upload($this->fo);
		if ($tipo == 'r') $this->a[$this->sz]->c = new lt_radiobutton($this->fo);
		//$this->fo->dump($this->a[$this->sz]->c);
		
		// apuntador a control del item actual
        /** @var lt_ctrl $this->c */
		$this->c = &$this->a[$this->sz]->c;
		
		// si esta asociado a tabla
		if ((array_search($tipo, array('t','e','l','k','r')) !== FALSE) && $this->_info_registro) 
			$this->c->setRegistroInfo($this->tabla, $this->nombre_clave, $this->valor_clave, $this->_nuevo);
		
		// fijar algunas propiedades por defecto
		if (strpos('s,g,p', $tipo) === false)
		{ 
			$this->c->htipo = false;
			$this->c->sufijo = $this->sufijo;
			$this->c->enabled = $this->_enabled;
			if (isset($this->_style_by[$tipo]))$this->c->style .= $this->_style_by[$tipo];
			$this->c->style .= $this->_style_all;
			if (strpos('u,b', $tipo) === false) $this->c->ro = $this->_ro;
			if ($this->_tabindex > 0)
			{
				$this->c->tabindex = $this->_tabindex;
				$this->_tabindex++;
			}
		}
		
		// estilo adicionales del item contenedor
		if ($estilo_item != '') $item->addStyle($estilo_item);
		if ($clase_item != '') $item->addClass($clase_item);

		// no incluir estos controles en la validacion del update 
		if (strpos('s,p,b,u,a,j', $tipo) !== false)
			$this->a[$this->sz]->setToUpdate(false);

		$this->ndx = $this->sz; // indice del item actual
		$this->sz++; // aumentar tamaño
	}
	
	private function _reset()
	{
		$this->a = array();
		$this->sz = 0;
	}

	/**
	 *
	 * Asignar valores a los controles a partir de los campos especificados de la tabla asociada.
	 * No esta pensada para uso directo habitual.
	 * @param array $campos
	 * Array con campos de la tabla
	 * @param stdClass $valores
	 * Objeto que contiene los valores
	 */
	public function asignarCampos(array $campos, stdClass $valores)
	{
		for ($ii = 0; $ii < $this->sz; $ii++)
		{
			if ($this->a[$ii]->getToUpdate())
			{
				$ct = &$this->a[$ii]->c;
				$cn = $ct->no == '' ? $ct->n : $ct->no;
				if (isset($valores->$cn))
				{
					$ct->assign($valores->$cn);
					if ($campos[$cn]->clave) $ct->primary_key = true;
				}
			}
		}
	}
	
	/**
	 * 
	 * Asignar valores de registro a controles del formulario
	 * @param variant $v
	 * Objeto de clase lt_registro o de clase parametros
	 */
	public function asignar($v)
	{
		if (gettype($v) == 'object')
		{
			if (get_class($v) == 'lt_registro') $this->_fromRegistro($v);
			if (get_class($v) == 'parametros') $this->_fromParametros($v);
			if (get_class($v) == 'stdClass') $this->_fromClass($v);
			if (get_class($v) == 'myquery') $this->_fromMyQuery($v);
		}
	}

	private function _fromRegistro(lt_registro $r)
	{
		//error_log('fromregistro');
		foreach ($this->a as &$o)
		{
			if (isset($o->c->n))
			{
				$cn = $o->c->no == '' ? $o->c->n : $o->c->no;
				//error_log('cn='.$cn);
				if (isset($r->v->$cn))
				{
					//error_log('cn='.$cn.' v='.$r->v->$cn);
					$o->c->assign($r->v->$cn);
					if ($r->campos[$cn]->clave) $o->c->primary_key = true;
				}
			}
		}
	}
	
	private function _fromClass(stdClass $r)
	{
		//error_log('fromclass');
		foreach ($this->a as &$o)
		{
			if (isset($o->c->n))
			{
				$cn = $o->c->no == '' ? $o->c->n : $o->c->no;
				//error_log('cn='.$cn);
				if (isset($r->$cn))
				{
					//error_log('cn='.$cn.' v='.$r->$cn);
					$o->c->assign($r->$cn);
				}
			}
		}
	}
	
	private function _fromMyQuery(myquery $q)
	{
		//error_log('frommyquery');
		foreach ($this->a as &$o)
		{
			if (isset($o->c->n))
			{
				$cn = $o->c->no == '' ? $o->c->n : $o->c->no;
				//error_log('cn='.$cn);
				if (isset($q->r->$cn))
				{
					//error_log('cn='.$cn.' v='.$q->r->$cn);
					$o->c->assign($q->r->$cn);
					if (isset($q->campos[$cn]))
					{
						if ($q->campos[$cn]->clave) $o->c->primary_key = true;
					}
				}
			}
		}
	}
	
	private function _fromParametros(parametros $p)
	{
		foreach ($this->a as &$o)
		{
			if (isset($o->c->n))
			{
				$cn = $o->c->no == '' ? $o->c->n : $o->c->no;
				if (isset($p->$cn))
				{
					$v = $p->$cn;
					$cnt = $cn.'_t';
					if ($p->$cnt == 'n') $v = nen($v);
					$o->c->assign($v);
				}
			}
		}
	}
	
	// *** SETTINGS DE CONTROLES ***
	
	/**
	 *
	 * Asignar funcion JS de validacion
	 * @param string $valid_fn
	 * Nombre de la funcion
	 * @param string $valid_parms
	 * (opcional) Parametros adicionales
	 */
	public function cSetValid($valid_fn, $valid_parms='')
	{
		// ultimo parametro del valid es el sufijo
		if ($valid_fn != '') $this->c->valid_fn = $valid_fn;
		$this->c->autocomplete = 'off';
		if ($valid_fn != '')
		{
			if ($this->sufijo != '')
			{
				$this->c->valid_parms = sprintf("%s%s'%s'", $valid_parms,
						$valid_parms == '' ? '':',', $this->sufijo);
			}
			else $this->c->valid_parms = $valid_parms;
		}
		$this->cv[$this->c->no] = &$this->c;
	}
	
	/**
	 * 
	 * Fijar valor de propiedad del control actual
	 * @param string $propiedad
	 * Nombre de la propiedad
	 * @param variant $valor
	 * Valor de la propiedad
	 */
	public function cSetProp($propiedad, $valor)
	{
		if (isset($this->c->$propiedad)) $this->c->$propiedad = $valor;
	}
	
	/**
	 * 
	 * Fijar valor de propiedad del item actual
	 * @param string $propiedad
	 * Nombre de la propiedad
	 * @param variant $valor
	 * Valor de la propiedad
	 */
	public function iSetProp($propiedad, $valor)
	{
		if (isset($this->a[$this->ndx]->$propiedad))
		{ 
			$this->a[$this->ndx]->$propiedad = $valor;
			//error_log($propiedad.'='.$valor);
		}
	}

	/**
	 *
	 * Asignar funcion JS que se ejecuta al presionar Enter
	 * @param string $onkey_fn
	 * Nombre de la funcion
	 * @param string $onkey_parms
	 * (opcional) Parametros adicionales
	 */
	public function cSetOnkey($onkey_fn, $onkey_parms='')
	{
		// ultimo parametro del onkey es el sufijo
		$this->c->onkey_fn = $onkey_fn;
		$this->c->onkey_parms = $onkey_parms;
		if (($this->c->onkey_fn != '') && ($this->sufijo != ''))
		{
			$this->c->onkey_parms = sprintf("%s%s'%s'", $this->c->onkey_parms,
					$this->c->onkey_parms == '' ? '':',', $this->sufijo);
		}
	}

	/**
	 *
	 * Asignar funcion JS que se ejecuta al levantar la tecla
	 * @param string $onkeyup_fn
	 * Nombre de la funcion
	 * @param string $onkeyup_parms
	 * (opcional) Parametros adicionales
	 */
	public function cSetOnkeyUp($onkeyup_fn, $onkeyup_parms='')
	{
		// ultimo parametro del onkey es el sufijo
		$this->c->onkeyup_fn = $onkeyup_fn;
		$this->c->onkeyup_parms = $onkeyup_parms;
		if (($this->c->onkeyup_fn != '') && ($this->sufijo != ''))
		{
			$this->c->onkeyup_parms = sprintf("%s%s'%s'", $this->c->onkeyup_parms,
					$this->c->onkeyup_parms == '' ? '':',', $this->sufijo);
		}
	}

	/**
	 *
	 * Indicar si control actual se valida al momento de actualizar
	 * @param bool $valor
	 */
	public function cValidateUpdate($valor)
	{
		$this->a[$this->ndx]->setValidateUpdate($valor);
	}

	/**
	 *
	 * Indicar si el control participa de la actualizacion
	 * @param bool $valor
	 */
	public function cToUpdate($valor)
	{
		$this->a[$this->ndx]->setToUpdate($valor);
	}

	public function cSinCelda($valor=TRUE)
	{
		$this->a[$this->ndx]->setSinCelda($valor);
	}

	public function cSetAlignment($alignment)
    {
        $this->a[$this->ndx]->setAlignment($alignment);
    }
	
	// *** CREACION DE CONTROLES ***
		
	/**
	 * 
	 * Mostrar texto
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $valor
	 * Texto a mostrar
	 * @param string $alineacion
	 * (opcional) Alineacion del texto, por defecto LT_ALIGN_DEFAULT
	 * @param string $clase
	 * (opcional) Clase CSS a aplicar
	 * @param string $estilo
	 * (opcional) Estilo CSS a aplicar
	 */
	public function s($etiqueta, $valor, $alineacion=LT_ALIGN_DEFAULT, 
			$clase='', $estilo='')
	{
		$this->_add($etiqueta, 's', $valor, $alineacion, '', $clase, $estilo);
	}
	/**
	 * 
	 * Mostrar texto
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $valor
	 * Texto a mostrar
	 * @param string $alineacion
	 * (opcional) Alineacion del texto, por defecto LT_ALIGN_DEFAULT
	 */
	public function string($etiqueta, $valor)
	{
		$this->s($etiqueta, $valor);
	}

    /**
     * Mostrar separador
     * @param string $titulo
     * Titulo a mostrar
     * @param int $alineacion
     * (opcional) Por defecto, LT_ALIGN_CENTER
     * @param string $clasecss
     * (opcional) Clase CSS
     * @param string $estilocss
     * (opcional) Estilo CSS
     */
	public function separador($titulo, $alineacion=LT_ALIGN_CENTER, $clasecss='negrita', $estilocss='font-size:10pt;')
    {
        $this->_add('-', 'p', $titulo, $alineacion, '', $clasecss, $estilocss);
    }
	/**
	 * 
	 * Control oculto (para guardar valores que no se muestran)
	 * @param string $nombre
	 * Nombre del control
	 * @param string $tipo
	 * Tipo de datos (c,i,d,h,t,n)
	 * @param variant $valor
	 * Valor del control
	 */
	public function h($nombre, $tipo, $valor, $clasecss='')
	{
		$this->_add('', 'h');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = $tipo;
		$this->c->cssclass = $clasecss;
		$this->c->assign($valor);
		$this->cv[$this->c->no] = &$this->c;
	}
	/**
	 * 
	 * Control oculto (para guardar valores que no se muestran)
	 * @param string $nombre
	 * Nombre del control
	 * @param string $tipo
	 * Tipo de datos (c,i,d,h,t,n)
	 * @param variant $valor
	 * Valor del control
	 */
	public function hidden($nombre, $tipo, $valor, $clasecss='')
	{
		$this->h($nombre, $tipo, $valor, $clasecss);
	}
	
	/**
	 * 
	 * Crear un control radio button
	 * @param string $etiqueta
	 * Etiqueta
	 * @param string $nombre
	 * Nombre interno del control
	 * @param string $tipo
	 * Tipo de datos
	 * @param array $opciones
	 * Opciones del radio button en el formato: 
	 * array(array('valor1','descripcion1'),array('valor2','descripcion2'))
	 * @param string $valid_fn
	 * (opcional) Funcion de validacion
	 * @param string $valid_parms
	 * (opcional) Parametros adicionales a la func de validacion
	 */
	public function r($etiqueta, $nombre, $tipo, array $opciones, $valid_fn='', $valid_parms='')
	{
		$this->_add($etiqueta, 'r');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = $tipo;
		$this->c->options = $opciones;
		$this->c->valid_fn = $valid_fn;
		$this->c->valid_parms = $valid_parms;
		$this->cv[$this->c->no] = &$this->c;
	}
	/**
	 * 
	 * Checkbox
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param int $valor
	 * Cero o uno (0/1)
	 */
	public function chk($etiqueta, $nombre, $valor)
	{
		$this->_add($etiqueta, 'k');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = 'l';
		$this->c->assign($valor);
		$this->cv[$this->c->no] = &$this->c;
	}
	/**
	 * 
	 * Array de checkbox
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param array $vector
	 * Array de arrays (valor,"titulo"), p.e. array(array(0,'Habilitar X'),array(1,'Habilitar Y'))
	 */
	public function chk_grp($etiqueta, $nombre, array $vector)
	{
		$this->_add($etiqueta, 'g', $vector, LT_ALIGN_DEFAULT, $nombre);
	}
	/**
	 * 
	 * Cuadro de texto (textbox)
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $tipo
	 * Tipo de datos (c,i,d,h,t,n)
	 * @param int $largo
	 * Largo del campo medido en caracteres
	 * @param string $valid_fn
	 * (opcional) Funcion JS a ejecutar cuando se cambia valor
	 * @param string $valid_parms
	 * (opcional) Parametros adicionales de la funcion JS anterior
	 */
	public function t($etiqueta, $nombre, $tipo, $largo, $valid_fn='', $valid_parms='')
	{
		$this->_add($etiqueta, 't');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = $tipo;
		$this->c->l = $largo;
		if ($tipo == 'i') $this->c->vcols = $largo; 
		if ($tipo == 'c') $this->c->vcols = $largo > 60 ? 60: $largo; 
		if ($tipo == 'n' || $this->c->pd == 0) $this->c->pd = 2; 
		if ($tipo == 'd' && $valid_fn == '') $this->c->valid_fn = 'lt_chkfecha';
		if ($tipo == 'h' && $valid_fn == '') $this->c->valid_fn = 'lt_chkhora';
		$this->cSetValid($valid_fn, $valid_parms);
	}
	/**
	 * 
	 * Cuadro de texto para editar fecha
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 */
	public function fecha($etiqueta, $nombre)
	{
		$this->t($etiqueta, $nombre, 'd', 10);
		$this->c->vcols = 10;
		$this->c->assign(lt_fecha::hoy());
	}
	/**
	 * 
	 * Cuadro de texto para editar hora
	 * @param unknown $etiqueta
	 * @param unknown $nombre
	 */
	public function hora($etiqueta, $nombre)
	{
		$this->t($etiqueta, $nombre, 'h', 8);
		$this->c->vcols = 8;
		$this->c->assign(lt_fecha::now());
	}
	/**
	 * 
	 * Cuadro de texto para editar entero
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param number $minimo
	 * (opcional) Valor minimo, por defecto 0
	 * @param number $maximo
	 * (opcional) Valor maximo, por defecto 9999
	 */
	public function entero($etiqueta, $nombre, $minimo=0, $maximo=9999)
	{
		$largomin = strlen(strval($minimo));
		$largomax = strlen(strval($maximo));
		$largo = $largomax > $largomin ? $largomax: $largomin;
		$valid_parms = sprintf("%d,%d,%d", $largo, $minimo, $maximo);
		$this->t($etiqueta, $nombre, 'i', $largo, 'lt_entero', $valid_parms);
		$this->c->assign(0);
	}
	/**
	 * 
	 * Cuadro de texto para editar numero con decimales
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param number $minimo
	 * (opcional) Valor minimo, por defecto 0
	 * @param number $maximo
	 * (opcional) Valor maximo, por defecto 9999
	 * @param number $decimales
	 * (opcional) Cantidad de decimales, por defecto 2
	 */
	public function numero($etiqueta, $nombre, $minimo=0, $maximo=9999, $decimales=2)
	{
		$largomin = strlen(strval($minimo));
		$largomax = strlen(strval($maximo));
		$largo = $largomax > $largomin ? $largomax: $largomin;
		$valid_parms = sprintf("%d,%d,%f,%f", $largo, $decimales, $minimo, $maximo);
		$this->t($etiqueta, $nombre, 'n', $largo, 'lt_numerico', $valid_parms);
		$this->c->pd = $decimales; 
	}
	/**
	 * 
	 * Cuadro de texto para editar solo digitos
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param int $largo
	 * Cantidad de digitos a leer
	 */
	public function digitos($etiqueta, $nombre, $largo)
	{
		$this->t($etiqueta, $nombre, 'i', $largo, 'lt_digitos', $largo);
	}
	/**
	 * 
	 * Cuadro de texto (textbox)
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $tipo
	 * Tipo de datos (c,i,d,h,t,n)
	 * @param int $largo
	 * Largo del campo medido en caracteres
	 * @param string $valid_fn
	 * (opcional) Funcion JS a ejecutar cuando se cambia valor
	 * @param string $valid_parms
	 * (opcional) Parametros adicionales de la funcion JS anterior
	 */
	public function textbox($etiqueta, $nombre, $tipo, $largo, $valid_fn='', $valid_parms='')
	{
		$this->t($etiqueta, $nombre, $tipo, $largo, $valid_fn, $valid_parms);
	}
	/**
	 * 
	 * Cuadro de edicion (editbox)
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $tipo
	 * Tipo de datos (c,i,d,h,t,n)
	 * @param int $ancho
	 * Ancho en medido en caracteres
	 * @param int $alto
	 * Alto medio en caracteres
	 * @param string $valid_fn
	 * (opcional) Funcion JS a ejecutar cuando se cambia valor
	 * @param string $valid_parms
	 * (opcional) Parametros adicionales de la funcion JS anterior
	 */
	public function e($etiqueta, $nombre, $tipo, $ancho, $alto, $valid_fn='', $valid_parms='')
	{
		$this->_add($etiqueta, 'e');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = $tipo;
		$this->c->l = 1024;
		$this->c->vrows = $alto;
		$this->c->vcols = $ancho; 
		$this->cSetValid($valid_fn, $valid_parms);
	}
	/**
	 * 
	 * Lista desplegable a partir de un array
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $tipo
	 * Tipo de datos (c,i,d,h,t,n)
	 * @param array $lista
	 * Lista de valores. Array de arrays (valor,descripcion), p.e. array(array(1,'Uno'),array(2,'Dos'))
	 * @param string $valor
	 * (opcional) Valor por defecto
	 * @param string $valid_fn
	 * (opcional) Funcion JS a ejecutar cuando se cambia valor
	 * @param string $valid_parms
	 * (opcional) Parametros adicionales de la funcion JS anterior
	 */
	public function la($etiqueta, $nombre, $tipo, array $lista, $valor=false, $valid_fn='', $valid_parms='')
	{
		$this->_add($etiqueta, 'l');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = $tipo;

		$this->c->rowsource_type = 1;
		$this->c->rowsource = $lista;
		if ($valor !== false) $this->c->assign($valor);
		
		$this->cSetValid($valid_fn, $valid_parms);
	}
	/**
	 * 
	 * Lista desplegable a partir de una tabla o consulta
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $tipo
	 * Tipo de datos (c,i,d,h,t,n)
	 * @param string $tabla
	 * Tabla origen de la lista
	 * @param array $campos
	 * Campos a escoger de la tabla
	 * @param string $custom
	 * (opcional) Consulta SQL personalizada
	 * @param string $valid_fn
	 * (opcional) Funcion JS a ejecutar cuando se cambia valor
	 * @param string $valid_parms
	 * (opcional) Parametros adicionales de la funcion JS anterior
	 */
	public function l($etiqueta, $nombre, $tipo, $tabla, array $campos, $custom='', $valid_fn='', $valid_parms='')
	{
		$this->_add($etiqueta, 'l');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = $tipo;
		
		$this->c->custom = $custom;
		$this->c->tbl = $tabla;
		$this->c->fl_key = $campos[0];
		$this->c->fl_desc = $campos[1];
		$this->c->fl_order = $campos[1];
		
		$this->cSetValid($valid_fn, $valid_parms);
	}
	
	/**
	 * 
	 * Lista para seleccionar mes
	 * @param string $etiqueta
	 * Etiqueta del campo
	 * @param string $nombre
	 * Nombre del campo
	 * @param bool $solovalido
	 * (opcional) No incluir item <no definido> por defecto TRUE
	 */
	public function mes($etiqueta, $nombre, $solovalido=TRUE)
	{
		$this->_add($etiqueta, 'l');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = 'i';
		
		if ($solovalido) $this->c->custom = "SELECT mes_id,nombre_es FROM meses WHERE mes_id>0 ORDER BY mes_id";
		$this->c->tbl = 'meses';
		$this->c->fl_key = 'mes_id';
		$this->c->fl_desc = 'nombre_es';
		$this->c->fl_order = 'mes_id';

		$this->c->assign(lt_fecha::hoy()->m);
	}
	
	/**
	 * 
	 * Lista para seleccionar agno
	 * @param string $etiqueta
	 * Etiqueta del campo
	 * @param string $nombre
	 * Nombre del campo
	 * @param number $minimo
	 * (opcional) Agno minimo, por defecto, inicio del proyecto
	 * @param number $maximo
	 * (opcional) Agno maximo
	 */
	public function agno($etiqueta, $nombre, $minimo=0, $maximo=0)
	{
		$this->_add($etiqueta, 'l');
		$this->c->no = $nombre;
		$this->c->n = $nombre.$this->sufijo;
		$this->c->t = 'i';
		
		if ($minimo == 0)
		{ 
			$fe = fecha_inicial_pry($this->fo, $this->fo->pid, 2);
			$minimo = $fe->a;
		}
		if ($maximo == 0) $maximo = 2100;
		$this->c->custom = sprintf("SELECT agno_id,cifra FROM agnos WHERE agno_id BETWEEN %d AND %d ORDER BY cifra",
			$minimo, $maximo);
		$this->c->tbl = 'agno_id';
		$this->c->fl_key = 'agno_id';
		$this->c->fl_desc = 'cifra';
		$this->c->fl_order = 'agno_id';
		
		$this->c->assign(lt_fecha::hoy()->a);
	}
	/**
	 * 
	 * Lista desplegable
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $tipo
	 * Tipo de datos (c,i,d,h,t,n)
	 * @param string $tabla
	 * Tabla origen de la lista
	 * @param array $campos
	 * Campos a escoger de la tabla
	 * @param string $custom
	 * (opcional) Consulta SQL personalizada
	 * @param string $valid_fn
	 * (opcional) Funcion JS a ejecutar cuando se cambia valor
	 * @param string $valid_parms
	 * (opcional) Parametros adicionales de la funcion JS anterior
	 */
	public function listbox($etiqueta, $nombre, $tipo, $tabla, array $campos, $custom='', $valid_fn='', $valid_parms='')
	{
		$this->l($etiqueta, $nombre, $tipo, $tabla, $campos, $custom, $valid_fn, $valid_parms);
	}
	/**
	 * 
	 * Enlace a una URL
	 * @param string $url
	 * URL destino
	 * @param string $caption
	 * Titulo del enlace
	 * @param string $destino
	 * (opcional) Nombre ventana destino, por defecto, nueva ventana '_blank'
	 */
	public function a($url, $caption, $destino='_blank')
	{
		$this->_add('', 'a');
		$this->c->link = $url;
		$this->c->caption = $caption;
		$this->c->destino = $destino;
	}
	/**
	 * 
	 * Enlace que ejecuta codigo JavaScript
	 * @param string $jscode
	 * Codigo JS
	 * @param string $caption
	 * Titulo del enlace
	 */
	public function js($jscode, $caption)
	{
		$this->_add('', 'j');
		$this->c->link = $jscode;
		$this->c->caption = $caption;
		$this->c->tipo = LT_LINK_JS;
		$this->c->destino = '';
	}
	/**
	 * 
	 * Boton que ejecuta funcion JS
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $onclick
	 * Funcion JS a llamar
	 * @param string $onclick_parms
	 * Parametros adicionales
	 */
	public function b($etiqueta, $nombre='', $onclick='', $onclick_parms='')
	{
		$this->_add($etiqueta, 'b');
		$this->c->n = $nombre == '' ? 'btt'.$this->ndx.$this->sufijo: $nombre;
		$this->c->caption = $etiqueta;
		$this->cSetValid($onclick, $onclick_parms);
		$this->cv[$this->c->no] = &$this->c;
	}
	/**
	 * 
	 * Boton que ejecuta funcion JS
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $onclick
	 * Funcion JS a llamar
	 */
	public function button($etiqueta, $nombre='', $onclick)
	{
		$this->b($etiqueta, $nombre, $onclick);
	}
	/**
	 * 
	 * Control para subir archivo
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $onclick
	 * (opcional) Codigo JS a ejecutar al hacer click
	 */
	public function f($etiqueta, $nombre='', $onclick='')
	{
		$this->_add($etiqueta, 'f');
		$this->c->n = $nombre == '' ? 'file'.$this->ndx.$this->sufijo: $nombre;
		$this->c->caption = $etiqueta;
		$this->c->valid_fn = $onclick;
		$this->cv[$this->c->no] = &$this->c;
	}
	/**
	 * 
	 * Control para subir archivo
	 * @param string $etiqueta
	 * Etiqueta adjunta
	 * @param string $nombre
	 * Nombre del control
	 * @param string $onclick
	 * (opcional) Codigo JS a ejecutar al hacer click
	 */
	public function file($etiqueta, $nombre='', $onclick='')
	{
		$this->f($etiqueta, $nombre, $onclick);
	}
	/**
	 * 
	 * Boton para enviar datos del formulario
	 * @param string $etiqueta
	 * (opcional) Etiqueta del boton
	 * @param string $nombre
	 * (opcional) Nombre del control
	 */
	public function u($etiqueta='Actualizar', $nombre=false)
	{
		$this->_add($etiqueta, 'u');
		$this->c->n = $nombre === false ? $this->n.'_upbtt': $nombre;
		$this->_upbtt = $this->c->n;
		$this->c->no = $this->c->n;
		$this->c->caption = $etiqueta;
		if ($this->tipo == LT_CTRL_SET_FORM)
		{
			$this->c->valid_fn = ''; 
			$this->c->tipo = LT_BUTTON_SUBMIT;
		}
		else
		{
			$this->c->valid_fn = $this->_update_fn;
			$this->c->valid_parms = $this->_update_fn_parms;
		}
		$this->cv[$this->c->no] = &$this->c;
	}
	/**
	 * 
	 * Boton para enviar datos del formulario
	 * @param string $etiqueta
	 * (opcional) Etiqueta del boton
	 * @param string $nombre
	 * (opcional) Nombre del control
	 */
	public function updater($etiqueta='Actualizar')
	{
		$this->u($etiqueta);
	}
	/**
	 * 
	 * Boton para borrar registro (solo en formularios multilinea)
	 * @param string $etiqueta
	 * (opcional) Titulo del boton
	 */
	public function delete($etiqueta='Borrar')
	{
		$this->_add($etiqueta, 'b');
		$this->c->n = $this->n.'_delbtt';
		$this->c->no = $this->c->n;
		$this->c->caption = $etiqueta;
		$this->c->valid_fn = sprintf("%s_delete", $this->n);
		$this->cv[$this->c->no] = &$this->c;
	}
	
	// *** RENDERIZACION DE ACTUALIZACION ***
	
	/**
	 * 
	 * Crear dinamicamente la validacion JS del conjunto de controles
	 */
	public function build_validate()
	{
		$vs = '';
		foreach ($this->a as &$ct)
		{
			if ($ct->getValidateUpdate() && $ct->getToUpdate() && ($ct->t != 'g') && ($ct->c->valid_fn !== ''))
			{
				$addpm = '';
				if ($ct->c->valid_parms != '') $addpm = sprintf(",%s", $ct->c->valid_parms); 
				$vs .= sprintf("if (todook) { todook=%s(\$('%s')%s,false); }\n ",
					$ct->c->valid_fn, $ct->c->n, $addpm);
			}
		}
		if ($this->_preproceso_fn != '') $vs .= sprintf("if (todook) { todook=%s(); }\n", $this->_preproceso_fn);
		$this->fo->jsi(sprintf("function %s_val() { var todook = true;\n %sreturn todook;}",
				$this->n, $vs));
	}
	
	/**
	 * 
	 * Crear dinamicamente la peticion Ajax del conjunto de controles
	 */
	private function _build_update_ajax()
	{
		$vs = '';
		$fp = '';
		foreach ($this->a as $ct)
		{
			$op = $vs == '' ? '?':'&';
			if ($ct->getToUpdate())
			{ 
				if ($ct->t == 'k') $vs .= sprintf("+'%s%s='+chkval(\$('%s')) ", $op, $ct->c->no, $ct->c->n);
				elseif ($ct->t == 'r') $vs .= sprintf("+'%s%s='+lt_radio_val('%s') ", $op, $ct->c->no, $ct->c->n);
				else $vs .= sprintf("+'%s%s='+encodeURIComponent(\$('%s').value) ", $op, $ct->c->no, $ct->c->n);
				$fp .= sprintf(";%s,%s", $ct->c->no, $ct->c->t);
			}
		}
		//error_log($vs);
		if ($vs != '')
		{
			$w1 = $w2 = '';
			if ($this->fo->have_wait)
			{
				$w1 = 'ltform_wait(true);';
				$w2 = 'ltform_wait(false);';
			}
			if ($this->async_update)
			{
				// manejador de update / ajax por defecto
				if (!$this->_custom_update)
				{
					$this->fo->jsi(sprintf("function %s() { ".
						"if (%s_val()) { ".
						"var qq='%s%s'%s+'&_formparms=%s&_tform=1'; %s ".
						"new Ajax.Request(qq, { method: 'get', ".
						"onSuccess: function(request){ %s %s(true,request.responseJSON,%d,'%s');}, ".
						"onFailure: function(request){ %s %s(false,request.responseJSON,%d,'%s');}}); ".
						"}}\n",
						$this->_update_fn, 
						$this->n, 
						$this->proc, $this->ruta, $vs, substr($fp, 1), $w1,  
						$w2, $this->_postproceso_fn, $this->linea, $this->sufijo, 
						$w2, $this->_postproceso_fn, $this->linea, $this->sufijo));
				}

				// capturar evento update
				if ($this->_evento_atrapar)
				{
					$this->fo->jsi(sprintf(
						"\$j(document).on('%s', function(event) { %s(); });\n",
						$this->_evento_update, $this->_update_fn));
				}
				
				// funcion postproceso por defecto
				if (!$this->_custom_postproc)
				{
					$this->fo->jsi(sprintf("function %s(bRe,oRe,linea,sufijo) { ".
						"var tm=3; if (!bRe) tm=15; ".
						"ltform_msg(oRe.msg, tm, %d); }\n", $this->_postproceso_fn, $this->_postproceso_reload ? 1:0));
				}
			}
		}
	}
	private function _build_update_form()
	{
		$upbtt_off = $this->_upbtt == '' ? '': sprintf("\$('%s').disabled = true; ", $this->_upbtt);
		$upbtt_on = $this->_upbtt == '' ? '': sprintf("\$('%s').disabled = false; ", $this->_upbtt);
		$this->fo->jsi(sprintf("\$j('#%s').submit(function (event) { %s ".
				"if (%s_val()) { %s return true; } ".
				"else { %s event.preventDefault(); } ".
				"}); ",
				$this->n, $upbtt_off, $this->n, $upbtt_on, $upbtt_on));
	}
	/**
	 * 
	 * No usar
	 */
	public function build_update()
	{
		$this->build_validate();
		if ($this->proc != '')
		{
			if ($this->tipo == LT_CTRL_SET_FORM) $this->_build_update_form();
			if ($this->tipo == LT_CTRL_SET_AJAX) $this->_build_update_ajax();
		}
	}
		
	private function _build_delete_form()
	{
		// TODO: delete form
	}
	private function _build_delete_ajax()
	{
		$ppdel = sprintf("%s_del_pp", $this->no);
		if ($this->_custom_postproc_del && ($this->_postproceso_del_fn != '')) 
			$ppdel = $this->_postproceso_del_fn;
		
		$vs = '';
		$fp = '';
		foreach ($this->a as $ct)
		{
			if ($ct->getToUpdate())
			{ 
				if ($ct->c->primary_key)
				{
					$op = $vs == '' ? '?':'&';
					$vs .= sprintf("+'%s%s='+\$('%s').value", $op, $ct->c->no, $ct->c->n);
					$fp .= sprintf(";%s,%s", $ct->c->no, $ct->c->t);
				}
			}
		}
		$vs .= sprintf("+'&__tabla=%s'", $this->tabla);
		$fp .= ";__tabla,c";
				
		if ($vs != '')
		{
			$w1 = $w2 = '';
			if ($this->fo->have_wait)
			{
				$w1 = 'ltform_wait(true);';
				$w2 = 'ltform_wait(false);';
			}
			$sl = '';
			if ($this->linea != -1) {
				$sl = sprintf("&_linea=%d", $this->linea);
				$fp .= ';_linea,i';
			}
			if ($this->async_update)
			{
				$predel = '';
				if ($this->_preproceso_del_fn != '') $predel = sprintf("if (todook) { todook=%s(); } ", $this->_preproceso_del_fn);
				$this->fo->jsi(sprintf("function %s_delete() { ".
						"if (confirm('Borrar este registro?')) { ".
						"var todook = true; ".
						"%s if (todook) {".
						"var qq=encodeURI('%s'+%s+'&_formparms=%s%s'); %s ".
						"new Ajax.Request(qq, { method: 'get', ".
						"onSuccess: function(request){ %s %s(true,request.responseJSON,%d,'%s');}, ".
						"onFailure: function(request){ %s %s(false,request.responseJSON,%d,'%s');}}); ".
						"}}}",
						$this->n, 
						$predel, 
						$this->proc_del, substr($vs, 1), substr($fp, 1), $sl, $w1,
						$w2, $ppdel, $this->linea, $this->sufijo,
						$w2, $ppdel, $this->linea, $this->sufijo));
			}

			// funcion postproceso por defecto
			if (!$this->_custom_postproc_del)
			{
				$this->fo->jsi(sprintf("function %s(bRe,oRe,linea,sufijo) { ".
					"var tm=3; if (!bRe) tm=15; ".
					"ltform_msg(oRe.msg, tm, 1); }\n", $ppdel));
			}
		}
	}
	
	public function setCustomDeletePostProc($bCustom, $cFuncName='')
	{
		$this->_custom_postproc_del = $bCustom;
		if ($cFuncName != '') $this->_postproceso_del_fn = $cFuncName;
	}
	/**
	 * 
	 * No usar
	 */
	public function build_delete()
	{
		if ($this->proc_del == '') return;
		if ($this->tipo == LT_CTRL_SET_FORM) $this->_build_delete_form();
		if ($this->tipo == LT_CTRL_SET_AJAX) $this->_build_delete_ajax();
	}

	private function _registro_cargar()
	{
		if ($this->tabla != '')
		{
			$nuevo = LT_DETECTAR;
			if ($this->linea != -1)
			{
				if ($this->_linea_nuevas) $nuevo = $this->linea == 0;
			}
			//error_log(print_r($this->valor_clave, TRUE));
			if (($s = lt_registro::crear($this->fo, $this->tabla, $this->valor_clave, $nuevo,
				FALSE, FALSE, $this->autovar_ex, $this->_linea_campo)))
			{
				$this->nombre_clave = $s->campo_clave();
				$this->_nuevo = $nuevo;
				//$this->_info_registro = TRUE;
				
				foreach ($s->campos as $fl)
				{
					if ($fl->clave) $this->h($fl->n, $fl->t, $fl->v);
				}
				if (!$s->nuevo)
				{
					$this->asignar($s);
					if ($this->proc_del != '') $this->delete();
				}
			}
		}
	}
	
	// *** RENDERIZACIONES VISUALES ****
	
	/**
	 * 
	 * Mostrar el ctrl_set en una tabla horizontal
	 * @param bool $completo
	 * (opcional) Indica si se muestran los titulos de las columnas, por defecto FALSE
	 * @param string $clasecss
	 * (opcional) Clase CSS a aplicar a la tabla
	 * @param int $align
	 * (opcional) Por defecto, alineado al centro, usar constantes LT_ALIGN_*
	 */
	public function box_horizontal($completo=false, $clasecss=LT_TABLE_CLASS_DEFAULT, $align=LT_ALIGN_CENTER)
	{
		$fo = &$this->fo;
		$this->_registro_cargar();
		$ncols = 0;
		
		if ($completo)
		{
			if ($this->tipo == LT_CTRL_SET_FORM) $fo->frm($this->proc, $this->_newwnd, $this->n, $this->_tipomime);
			$fo->tbl($align, -1, LT_TABLE_PADDING_DEFAULT, $clasecss);
		}
			
		if ($this->con_etiquetas)
		{
			$fo->tr();
			foreach ($this->a as $ct)
			{
				if ($ct->t == 'h' || $ct->getSinCelda()) continue;
				if (strpos('b,u,a,j', $ct->t) !== false) $fo->th('-'); else  $fo->th($ct->e);
			}
			$fo->trx();
		}
		$fo->tr();
		foreach ($this->a as $ct)
		{
			if ($ct->t != 'h')
			{
				if ($ct->t == 's' || $ct->t == 'p')
				{
					if ($ct->getSinCelda())
					{
						$fo->sp();
						$fo->span($ct->c, $ct->clase, $ct->estilo);
					} 
					else $fo->tdc($ct->c, $ct->al, 0, $ct->clase, $ct->estilo);
				}
				elseif ($ct->t == 'g')
				{
					if ($ncols > 0) $fo->tdx();
					$ncols++;
					$fo->td($ct->al, 0, $ct->clase, $ct->estilo);
					foreach ($ct->c->a as $gi)
					{
						$gi->render($fo);
						$fo->sp();
					}
				}
				else
				{
					if ($ct->getSinCelda())
					{
						$fo->sp();
						$ct->c->render($fo);
					}
					else
					{
						if ($ncols > 0) $fo->tdx();
						$ncols++;
						$fo->td($ct->al, 0, $ct->clase, $ct->estilo);
						$ct->c->render($fo);
					}
				}
			}
		}
		foreach ($this->a as $ct)
		{
			if ($ct->t == 'h') $ct->c->render($fo);
		}
		$fo->tdx();
		$fo->trx();
		
		if ($completo)
		{
			$fo->tblx();
			$this->build_update();
			$this->build_delete();
			if ($this->tipo == LT_CTRL_SET_FORM) $fo->frmx();
		}
		else
		{
			$this->build_update();
			$this->build_delete();
		}
	}
	/**
	 * 
	 * Mostrar el ctrl_set en una tabla vertical
	 * @param number $columnas
	 * (opcional) Por defecto 1 par etiqueta/control por cada fila 
	 * @param string $clasecss
	 * (opcional) Clase CSS a aplicar a la tabla
	 * @param int $align
	 * (opcional) Por defecto, alineado al centro, usar constantes LT_ALIGN_*
	 */
	public function box_vertical($columnas=1, $clasecss=LT_TABLE_CLASS_DEFAULT, $align=LT_ALIGN_CENTER)
	{
		$fo = &$this->fo;
		$this->_registro_cargar();
		
		if ($this->tipo == LT_CTRL_SET_FORM) $fo->frm($this->proc, $this->_newwnd, $this->n, $this->_tipomime);
		$fo->tbl($align, -1, LT_TABLE_PADDING_DEFAULT, $clasecss);
		$cols = 0;
		$nfilas = 0;
		$fo->tr();
		foreach ($this->a as $ct)
		{
			//$fo->dump($ct);
			if ($cols >= $columnas)
			{
				if (!$ct->getSinCelda())
				{
					if ($nfilas > 0) $fo->trx();
					$fo->tr();
					$cols = 0;
				}
			}
			if (strpos('b,u,a,j', $ct->t) !== false)
			{
				if (!$ct->getSinCelda())
				{
					$colsp = ($this->etiqueta_unida || !$this->con_etiquetas) ? 1 : 2;
					if ($cols > 0) $fo->tdx();
					$fo->td($ct->al, $colsp, $ct->clase, $ct->estilo);
					$ct->c->render($fo);
					$cols ++;
				}
				else $ct->c->render($fo);
			}
			if (strpos('s,t,l,e,k,f,r,p', $ct->t) !== false)
			{
				if ($ct->getSinCelda())
				{
					$fo->sp(); 
					if ($ct->t == 's' || $ct->t == 'p')
					{
						switch ($ct->al)
						{
							case LT_ALIGN_CENTER:
								$als = 'text-align:center'; break; 
							case LT_ALIGN_LEFT:
								$als = 'text-align:left'; break; 
							case LT_ALIGN_RIGHT:
								$als = 'text-align:right'; break; 
							default:
								$als = ''; break; 
						}
						$fo->span($ct->c, $ct->clase, $ct->estilo.$als);
					}
					else $ct->c->render($fo);
				}
				else 
				{
					if ($this->etiqueta_unida)
					{
						if ($cols > 0) $fo->tdx();
						$fo->td(0, 0, $ct->clase, 'vertical-align:top;'.$ct->estilo);
						if ($this->con_etiquetas)
						{
							$fo->span($ct->e, 'negrita');
							$fo->br();
						}
						$ct->c->render($fo);
					}
					else
					{
                        if ($ct->t == 'p') {
                            $fo->tdc($ct->c, $ct->al, 2, $ct->clase, $ct->estilo);
                        }
                        else
                        {
                            if ($this->con_etiquetas) $fo->tdc($ct->e, 0, 0, 'negrita');
                            if ($ct->t == 's') $fo->tdc($ct->c, $ct->al, 0, $ct->clase, $ct->estilo);
                            else {
                                if ($cols > 0) $fo->tdx();
                                $fo->td($ct->al, 0, $ct->clase, $ct->estilo);
                                $ct->c->render($fo);
                            }
                        }
					}
					$cols ++;
				}
			}
			if ($ct->t == 'g')
			{
				if ($this->etiqueta_unida)
				{
					$fo->td(0, 0, $ct->clase, 'vertical-align:top;'.$ct->estilo);
					if ($this->con_etiquetas)
					{
						$fo->span($ct->e, 'negrita');
						$fo->br();
					}
				}
				else
				{
					if ($this->con_etiquetas) $fo->tdc($ct->e, 0, 0, 'negrita');
					$fo->td($ct->al, 0, $ct->clase, $ct->estilo);
				}
				foreach ($ct->c->a as $gi)
				{
					$gi->render($fo);
					$fo->br();
				}
				$cols ++;
			}
			if ($ct->t == 'h') $ct->c->render($fo);
		}
		$fo->trx();
		$this->build_update();
		$fo->tblx();
		if ($this->tipo == LT_CTRL_SET_FORM) $fo->frmx();
	}
	
	public function no_box($clasecss=''){
		if ($this->tipo == LT_CTRL_SET_FORM) 
			$this->fo->frm($this->proc, $this->_newwnd, $this->n, $this->_tipomime, $clasecss);
		foreach ($this->a as $cp)
		{
			$this->fo->sp();
			$cp->c->render($this->fo);
		}
		$this->build_update();
		if ($this->tipo == LT_CTRL_SET_FORM) $this->fo->frmx();
	}
	
	/**
	 *
	 * Crea una tabla para mostrar el conjunto de controles
	 * @param int $tipo
	 * Define el layout. Por defecto LT_INFOBOX_HORIZONTAL.
	 * @param int $columnas
	 * Cantidad de columnas en caso que el layout se LT_INFOBOX_VERTICAL.
	 * @param int $align
	 * (opcional) Por defecto, alineado al centro, usar constantes LT_ALIGN_*
	 */
	public function box($tipo=LT_INFOBOX_HORIZONTAL, $columnas=1, $clasecss=LT_TABLE_CLASS_DEFAULT, $align=LT_ALIGN_CENTER)
	{
		if ($tipo == LT_INFOBOX_HORIZONTAL) $this->box_horizontal(true, $clasecss, $align);
		if ($tipo == LT_INFOBOX_VERTICAL) $this->box_vertical($columnas, $clasecss, $align);
		if ($tipo == LT_INFOBOX_NOBOX) $this->no_box();
	}
	/**
	 *
	 * Crea una tabla de controles 'string'. Sirve para encabezados informativos.
	 * @param lt_form $fo
	 * @param array $dt
	 * Vector con las etiquetas/titulos/alineacion de cada celda
	 * @param int $tipo
	 * (opcional) Tipo de layout, por defecto LT_INFOBOX_HORIZONTAL
	 * @param int $columnas
	 * Numero de columnas en caso de layout LT_INFOBOX_VERTICAL
	 */
	public static function abox(lt_form $fo, array $dt, $tipo=LT_INFOBOX_HORIZONTAL, $columnas=1, 
			$clasecss=LT_TABLE_CLASS_DEFAULT)
	{
		$tmpct = new self($fo);
		$cc = count($dt);
		for ($ii=0; $ii<$cc; $ii++) $tmpct->_add($dt[$ii][0], 's', $dt[$ii][1],
				isset($dt[$ii][2]) ? $dt[$ii][2]: LT_ALIGN_DEFAULT);
		$tmpct->box($tipo, $columnas, $clasecss);
	}
	/**
	 * 
	 * Muestra una tabla con los resultados de la consulta
	 * @param lt_form $fo
	 * Contexto
	 * @param myquery $q
	 * Objecto MYQUERY con la consulta
	 * @param string $tipo
	 * (opcional) Tipo de tabla, por defecto, LT_INFOBOX_HORIZONTAL
	 * @param number $columnas
	 * (opcional) Cantidad de pares de valores por fila en tablas verticales
	 * @param string $clasecss
	 * (opcional) Clase CSS a aplicar a la tabla
	 * @param bool $con_etiquetas
	 * (opcional) Indica si se muestran las etiquetas adjuntas a los control, por defecto TRUE
	 */
	public static function qbox(lt_form $fo, myquery $q, $tipo=LT_INFOBOX_HORIZONTAL, $columnas=1, 
		$clasecss=LT_TABLE_CLASS_DEFAULT, $con_etiquetas=true, $align=LT_ALIGN_CENTER)
	{
		// inicializar totales
		$ttc = 0;
		foreach ($q->campos_q as $fl)
		{
			$cn = $fl->n;
			if ($fl->operacion == LTFLD_CONTEO)
			{
				$tt[$cn] = 0;
				$ttc++;
			}
			if ($fl->operacion == LTFLD_SUMA)
			{
				$tt[$cn] = 0;
				$ttc++;
			}
		}
				
		if ($tipo == LT_INFOBOX_HORIZONTAL)
		{
			$fo->tbl($align, LT_TABLE_BORDER_NONE, LT_TABLE_PADDING_DEFAULT, $clasecss);
			$tmpct = new self($fo);
			for($ii = 0; $ii < $q->sz; $ii ++)
			{
				$tmpct->_reset();
				if ($con_etiquetas) $tmpct->con_etiquetas = $ii == 0;
				foreach ($q->campos_q as $fl)
				{
					$cn = $fl->n;
					$rv = $q->a[$ii]->$cn; // raw value
					if (strpos('d,t,h', $fl->t) !== false) $v = $rv->to_string(); 
					elseif ($fl->t == 'n') $v = nes($rv);
					else $v = $rv;
					$ali = $fl->align;
					if ($ali == LT_ALIGN_DEFAULT) 
						$ali = strpos('i,f,n', $fl->t) !== false ? 
						LT_ALIGN_RIGHT : LT_ALIGN_CENTER;
					if (!$fl->ocultar)
					{ 
						$tmpct->_add(strtoupper($cn), 's', $v, $ali);
						if ($fl->style != '') $tmpct->cSetProp('style', $fl->style);
						$tmpct->iSetProp('estilo', $fl->estilo);
						$tmpct->iSetProp('clase', $fl->clase);
					}
					if ($ttc > 0)
					{
						if ($fl->operacion == LTFLD_CONTEO) $tt[$cn]++;
						if ($fl->operacion == LTFLD_SUMA) $tt[$cn] += $rv;
					}
				}
				if ($q->tieneAcciones())
				{
					$tmpct->s('-', $q->acciones($q->a[$ii]));
				}
				$tmpct->box_horizontal();
			}
			
			// mostrar totales
			if ($ttc > 0)
			{
				$fo->tr();
				foreach ($q->campos_q as $fl)
				{
					$cn = $fl->n;
					if ($fl->operacion == LTFLD_CONTEO) 
						$fo->tdc($tt[$cn], 2, 0, 'negrita');
					elseif ($fl->operacion == LTFLD_SUMA) 
						$fo->tdc(nes($tt[$cn]), 2, 0, 'negrita');
					else $fo->tdc('', 0, 0, '', 'border:none;');
				}
				$fo->trx();
			}
				
			$fo->tblx();
		}
		if ($tipo == LT_INFOBOX_VERTICAL)
		{
			$tmpct = new self($fo);
			$tmpct->con_etiquetas = $con_etiquetas;
			for($ii = 0; $ii < $q->sz; $ii ++)
			{
				foreach ($q->campos_q as $fl)
				{
					$cn = $fl->n;
					if (strpos('d,t,h', $fl->t) !== false) $v = $q->a[$ii]->$cn->to_string(); 
					elseif ($fl->t == 'n') $v = nes($q->a[$ii]->$cn);
					else $v = $q->a[$ii]->$cn;
					$ali = $fl->align;
					if ($ali == LT_ALIGN_DEFAULT) $ali = strpos('i,f,n', $fl->t) !== false ? LT_ALIGN_LEFT : LT_ALIGN_CENTER; 
					if (!$fl->ocultar)
					{ 
						$tmpct->_add(strtoupper($cn), 's', $v, $ali);
						if ($fl->style != '') $tmpct->cSetProp('style', $fl->style);
						$tmpct->iSetProp('estilo', $fl->estilo);
						$tmpct->iSetProp('clase', $fl->clase);
					}
				}
			}
			$tmpct->box_vertical($columnas, $clasecss, $align);
		}
	}
	/**
	 * 
	 * Muestra el contenido de un LT_REGISTRO en una tabla horizontal
	 * @param lt_form $fo
	 * Contexto
	 * @param lt_registro $r
	 * Objeto que contiene el registro a mostrar
	 */
	public static function rbox(lt_form $fo, lt_registro $r)
	{
		$tmpct = new self($fo);
		$tmpct->con_etiquetas = true;
		foreach ($r->campos as $fl)
		{
			$v = strpos('d,t,h', $fl->t) !== false ? $fl->v->to_string() : $fl->v;
			$ali = strpos('i,f,n', $fl->t) !== false ? LT_ALIGN_LEFT : LT_ALIGN_CENTER;
			$tmpct->_add($fl->n, 's', $v, $ali);
		}
		$tmpct->box(LT_INFOBOX_HORIZONTAL);
	}	
}
?>