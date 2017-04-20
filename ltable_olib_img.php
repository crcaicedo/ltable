<?php
define("LT_IMG_RAW", 0);
define("LT_IMG_BASE64", 1);

/**
 * 
 * Tomando la ruta de un archivo, leer sus contenidos y prepararlos para ser insertados en la BD
 * @param String $file_name
 * @return String con la informacion del archivo lista para insertar
 */
function read_image($file_name){
	return  chunk_split(base64_encode(file_get_contents($file_name)));
}

/**
 * 
 * A partir de un binario leido mediante la funcion read_image() desplegar un img tag que muestre la img
 * @param lt_form $fo form object donde se desplegara el tag
 * @param unknown_type $bin cadena binaria a mostrar
 */
function display_image(lt_form $fo,$bin,$height = 400, $width = 500){
	$fo->img("data:image/jpeg;base64,$bin","","",$height,$width);
}

function texto_sobre_imagen($archivo, $texto, $x, $y, $fontSize=3, $tipo_mime="Content-type: image/jpeg")
{
	header($tipo_mime);
	$image = imagecreatefromjpeg($archivo);
	$color = imagecolorallocate($image, 255, 255, 255);
	imagestring($image, $fontSize, $x, $y, $texto, $color);
	imagejpeg($image);
}

function removeOLEheader($data, $tipo)
{
	$ndx = -1;

	$block = array('bmp'=>"BM", 'jpg'=>"\u00FF\u00D8\u00FF", 'png'=>"\u0089PNG\r\n\u001a\n", 
			'gif'=>"GIF8", 'tiff'=>"II*\u0000");
	if ($tipo == 'auto') $atp = array('bmp','jpg','png','gif','tiff');
	else $atp = array($tipo);
	foreach ($atp as $tp)
	{
		if (($pos = strpos($data, $block[$tp])) !== false)
		{
			$ndx = $pos;
			break;
		}
	}
	if ($ndx != -1) $data = substr($data, $pos);
	//error_log('ndx='.$ndx);
	return $data;
}

/**
 *
 * Consulta la tabla de productos y devuelve la imagen del producto
 * @param lt_form $fo
 * Formulario
 * @param int $producto_id
 * Id del producto en la tabla productos
 * @param array $buf
 * Vector donde se retornaran los datos.
 * @param int $fmt
 * Formato 0:raw, 1:base64 (opcional)
 * @param bool $joke
 * Indica si muestra una imagen aleatoria (opcional)
 */
class ltable_imagen
{
	public $archivo, $largo, $data, $tipo_mime, $file_ok = FALSE;
	function __construct($archivo, $largo, $data, $tipo_mime)
	{
		$this->archivo = $archivo;
		$this->largo = $largo;
		$this->data = $data;
		$this->tipo_mime = $tipo_mime;
	}
	public function to_http()
	{
		header("Content-type: ".$this->tipo_mime);
		header(sprintf("Content-length: %d", $this->largo));
		header("Content-Disposition: attachment; filename=".$this->archivo);
		print $this->data;
	}
	public function to_file()
	{
		$this->file_ok = file_put_contents($this->archivo, $this->data) !== FALSE;
	}
	public static function notfound_image($fmt=LT_IMG_RAW, $joke=FALSE)
	{
		$ni = $joke ? mt_rand(1, 8): 7;
		$fn = sprintf("notfound%02d.jpg", $ni);
		if (($ss = file_get_contents($fn)) !== false)
		{
			return new self($fn, strlen($ss), $fmt == LT_IMG_RAW ? $ss:
					"data:image/jpeg;base64,".chunk_split(base64_encode($ss)),
					'image/jpeg');
		}
		return false;
	}
	public static function from_query(lt_form $fo, $query, $campo, $id=0, 
			$fmt=0, $joke=false, $remove_ole=false, 
			$campo_tipo=FALSE, $campo_sz=FALSE, $archivo=FALSE)
	{
		if ($id == 0) $id = mt_rand(1, 999999);
		$qa = new myquery($fo, $query,'LTOLIB-DYNIMG-1', true, false, MYASSOC);
		if ($qa->isok)
		{
			$len = $campo_sz === FALSE ? sizeof($qa->r[$campo]): $qa->r[$campo_sz];
			$mt = $campo_tipo === FALSE ? 'image/jpeg': $qa->r[$campo_tipo];
			if ($len > 0)
			{
				if ($remove_ole)
				{
					$len -= 88;
					return new self(sprintf("%06d.bmp", $id), $len,
						$fmt == 0 ? removeOLEheader($qa->r[$campo], 'auto'):
						"data:image/bmp;base64,".
						chunk_split(base64_encode(removeOLEheader($qa->r[$campo],'auto'))),
						'image/bmp');
				}
				else
				{
					if ($archivo === FALSE)
					{
						$ext = 'jpg';
						if ($mt == 'application/pdf') $ext = '.pdf'; 
						if ($mt == 'image/png') $ext = '.png'; 
						$archivo = sprintf("tmp/%06d.%s", $id, $ext);
					}
					return new self($archivo, $len, $fmt == 0 ? $qa->r[$campo]:
						"data:".$mt.";base64,".
						chunk_split(base64_encode($qa->r[$campo])), $mt);
				}
			}
		}
		return ltable_imagen::notfound_image($fmt, $joke);
	}
}

/**
 *
 * Consulta la tabla de productos y devuelve la imagen del producto
 * @param lt_form $fo
 * Formulario
 * @param int $producto_id
 * Id del producto en la tabla productos
 * @param array $buf
 * Vector donde se retornaran los datos.
 * @param int $fmt
 * Formato 0:raw, 1:base64 (opcional)
 * @param bool $joke
 * Indica si muestra una imagen aleatoria (opcional)
 */
class producto_imagen
{
	public $archivo, $largo, $data, $tipo_mime;
	function __construct($archivo, $largo, $data, $tipo_mime)
	{
		$this->archivo = $archivo;
		$this->largo = $largo;
		$this->data = $data;
		$this->tipo_mime = $tipo_mime;
	}
	public function to_http()
	{
		header("Content-type: ".$this->tipo_mime);
		header(sprintf("Content-length: %d", $this->largo));
		header("Content-Disposition: attachment; filename=".$this->archivo);
		print $this->data;
	}

	public static function notfound(lt_form $fo, $fmt=0, $joke=false)
	{
		$ni = $joke ? mt_rand(1, 8): 7;
		$fn = sprintf("notfound%02d.jpg", $ni);
		if (($ss = file_get_contents($fn)) !== false)
		{
			return new self($fn, strlen($ss), $fmt == 0 ? $ss:
					"data:image/jpeg;base64,".chunk_split(base64_encode($ss)),
					'image/jpg');
		}
		else error_log('No pude leer '.$fn);
		
		return false;
	}
	
	/**
	 * 
	 * Cargar imagen de producto
	 * @param lt_form $fo
	 * (contexto)
	 * @param int $producto_id
	 * ID del producto
	 * @param int $fmt
	 * Formato a retornar, 1:base64, 0:binario, por defecto 1
	 * @param int $numero
	 * Indica el numero de imagen a retornar (1..6), por defecto la primera (1) 
	 * @return producto_imagen|boolean
	 * Retorna instancia de producto_imagen, o FALSE si falla
	 */
	public static function cargar(lt_form $fo, $producto_id, $fmt=0, $numero=1)
	{
		// consultar tabla de imagenes
		$jn = new lt_joins(LTJOIN_LEFT, 'a', 'productos_fichas', 'id_ficha', 'prodficha_id');
		$c = new lt_condicion('producto_id', '=', $producto_id);
		$campo = 'img'.$numero;
		$fl = new lt_campos($campo); 
		if (($qx = myquery::t($fo, 'productos', $c, $fl, $jn)))
		{
			$tmpfn = 'images/'.$qx->r->$campo;
			if (($imgdata = file_get_contents($tmpfn)))
			{
				$img = ($fmt == 0) ? $imgdata: base64_encode($imgdata);
				$sz = strlen($img);
				if ($sz > 0)
				{
					return new self(sprintf("%06d.jpg", $producto_id), strlen($img),
						$fmt == 0 ? $img : "data:image/jpg;base64,".$img,
						'image/jpg');
				}
			}
			else $fo->warn('Error cargando imagen: '.$tmpfn);
		}
		
		// fallback default image
		return self::notfound($fo, $fmt, $joke);
	}
}
?>