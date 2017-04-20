<?php
class file_upload
{
	public $tipo = '', $sz = 0, $tmpfn = '', $fn = '', $isok = FALSE, $buffer = '', $file_handler = NULL;

	/**
	 *
	 * Carga en $buffer el contenido de la archivo subido al servidor
	 * @param lt_form $fo
	 * contexto
	 * @param string $name
	 * Nombre del control FILE en el FORM
	 * @param number $max_size
	 * (opcional) Tamano maximo del archivo
	 * @param array $tipos
	 * (opcional) Tipos MIME permitidos para el archivo
	 * @param bool $permanente
	 * (opcional) Indica si el archivo subido se conserva en el servidor, por defecto FALSE
	 * @param string $directorio
	 * (opcional) Indica un directorio diferente al actual en caso de que se conserve el archivo subido
	 */
	function __construct(lt_form $fo, $name, $max_size=0, array $tipos=array(), 
			$permanente=FALSE, $directorio='', $open_file=FALSE)
	{
		if (isset($_FILES[$name]))
		{
			$this->tmpfn = $_FILES[$name]['tmp_name'];
			$this->fn = $_FILES[$name]['name'];
			$this->tipo = $_FILES[$name]['type'];

			if (is_uploaded_file($this->tmpfn))
			{
				if (empty($tipos)) $tpchk = TRUE;
				else $tpchk = array_search($this->tipo, $tipos, true);
				//$fo->dump($tipos);
				if ($tpchk !== FALSE)
				{
					$checksz = TRUE;
					if ($max_size > 0)
					{
						$this->sz = filesize($this->tmpfn);
						if ($this->sz <= $max_size) $checksz = TRUE;
						else $fo->warn(sprintf("Tama&ntilde;o m&aacute;ximo: %d Kbytes, archivo %d Kbytes",
								$max_size/1024, $this->sz/1024));
					}
					if ($checksz)
					{
                        $loadok = TRUE;
					    if (!$open_file)
                        {
                            $loadok = (($this->buffer = file_get_contents($this->tmpfn)) !== FALSE);
                            //error_log(strlen($buffer));
                        }

					    if ($loadok)
						{
							if ($permanente)
							{
                                $moveok = FALSE;
                                if ($directorio != '') $directorio .= '/';
                                $dstfn = $directorio.$this->fn;
                                if (move_uploaded_file($this->tmpfn, $dstfn)) {
                                    $moveok = TRUE;
                                }
                                else $fo->warn("Error enlazando: " . $dstfn);
							}
							else $moveok = TRUE;

							if ($moveok) {
                                if ($open_file) {
                                    $ofn = $permanente ? $dstfn : $this->tmpfn;
                                    if (($this->file_handler = @fopen($ofn, 'r'))) {
                                        $this->isok = TRUE;
                                    } else $fo->warn('Error abriendo archivo subido:' . $ofn);
                                } else $this->isok = TRUE;
                            }
						}
						else $fo->warn('Error leyendo: '.$this->tmpfn, TRUE);
					}
				}
				else $fo->warn(sprintf("Tipo incorrecto: %s (%s)",
					$this->tipo, $this->tmpfn), TRUE);
			}
			//else $fo->warn('Archivo no subido: '.$this->tmpfn.', sin cambios');
		}
		else $fo->warn('Archivo no encontrado, campo:'.$name, TRUE);
	}

	public static function open(lt_form $fo, $name, $modo='r', $max_size=0, array $tipos=array(),
                                $permanente=FALSE, $directorio='')
    {
        return new self($fo, $name, $max_size, $tipos, $permanente, $directorio, TRUE);
    }

    public function read($length)
    {
        return fread($this->file_handler, $length);
    }

    public function close()
    {
        if ($this->file_handler !== NULL) {
            @fclose($this->file_handler);
            $this->file_handler = NULL;
        }
        @unlink($this->tmpfn);
    }

    function __destruct()
    {
        $this->close();
    }
}
?>