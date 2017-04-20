<?php
class lt_file_proc
{
	private $tmpfn = '', $fn = '', $mimetype = '', $h = FALSE;
	
	/** 
	 * 
	 * Procesar archivo CSV subido a servidor
	 * @param lt_form $fo
	 * Contexto
	 * @param string $paramname
	 * Nombre del parametro en POST
	 * @param string $callback
	 * Nombre de la funcion que procesara los registros
	 * @param array $aux
	 * Parametros adicionales a la funcion procesadora
	 * @param string $mimetypes
	 * Tipos MIME permitidos
	 * @return lt_file_upload
	 */
	public static function csv(lt_form $fo, $paramname, $callback, array $aux=array(), 
			$mimetypes='text/comma-separated-values,text/csv,text/plain')
	{
		$rv = FALSE;
		$tmpo = new self();
        $tmpo->tmpfn = $_FILES[$paramname]['tmp_name'];
        $tmpo->fn = $_FILES[$paramname]['name'];
        $tmpo->mimetype = $_FILES[$paramname]['type'];
        if (strpos($mimetypes, $tmpo->mimetype) !== false)
        {
			if (is_uploaded_file($tmpo->tmpfn))
            {
            	if (($tmpo->h = fopen($tmpo->tmpfn, 'r')) !== FALSE)
            	{
            		$hayerror = FALSE;
            		while (($line = fgetcsv($tmpo->h)) !== FALSE)
            		{
            			if (!$callback($fo, $line, $aux))
            			{
            				$hayerror = TRUE;
            				break;
            			}
            		}
            		fclose($tmpo->h);
            		@unlink($tmpo->tmpfn);
            		if (!$hayerror)
            		{ 
            			$fo->ok('Archivo procesado');
            			$rv = $tmpo;
            		}
            	}
            	else $fo->warn('No pude abrir archivo');
			}
			else $fo->warn('Archivo no subido');
        }
        else $fo->warn('Tipo de archivo incorrecto: '.$tmpo->mimetype);
        return $rv;
	}
}
?>