<?php
define('LT_FECHA_T', 0);
define('LT_FECHAHORA_T', 1);
define('LT_HORA_T', 2);

class lt_fecha
{
	public $d=0, $m=0, $a=0, $ds=0, $da=0, $timestamp=0, $hora=0, $min=0, $seg=0, $tipo = LT_FECHA_T;
	private $_semana=0;
	private function _blank()
	{
		$this->a = $this->m = $this->d = $this->ds = $this->da = 0;
		$this->hora = $this->min = $this->seg = 0;
		$this->_semana = 0;
	}

	public function setDia($dia)
	{
		if ($dia > 0)
		{
			if ($dia <= $this->ultimo())
			{
				$this->d = $dia;
				$this->_ds();
			}
		}
	}
	
	public function setMes($mes)
	{
		if ($mes > 0)
		{
			if ($mes <= 12)
			{
				$this->m = $mes;
				$this->_ds();
			}
		}
	}
	
	public function setAgno($agno)
	{
		if ($agno > 1969)
		{
			$this->a = $agno;
			$this->_ds();
		}
	}
	
	public function semana()
	{
		$this->_ds();
		return $this->_semana;
	}
	
	public function from_timestamp($timestamp)
	{
		$this->_blank();
		$this->timestamp = $timestamp;
		$tmptm = localtime($timestamp, true);
		if ($this->tipo == LT_FECHAHORA_T || $this->tipo == LT_FECHA_T)
		{
			$this->d = $tmptm['tm_mday'];
			$this->m = $tmptm['tm_mon'] + 1;
			$this->a = $tmptm['tm_year'] + 1900;
			$this->ds = $tmptm['tm_wday'] + 1;
			$this->da = $tmptm['tm_yday'] + 1;
		}
		if ($this->tipo == LT_FECHAHORA_T || $this->tipo == LT_HORA_T)
		{
			$this->hora = $tmptm['tm_hour'];
			$this->min = $tmptm['tm_min'];
			$this->seg = $tmptm['tm_sec'];
		}
	}
	
	public function from_array(array $var)
	{
		$this->_blank();
		if ($this->tipo == LT_FECHAHORA_T || $this->tipo == LT_FECHA_T)
		{
			$this->d = $var['d'];
			$this->m = $var['m'];
			$this->a = $var['a'];
		}
		if ($this->tipo == LT_FECHAHORA_T || $this->tipo == LT_HORA_T)
		{
			$this->hora = $var[0];
			$this->min = $var[1];
			$this->seg = $var[2];
		}
		$this->_ds();
	}
		
	private function _ds()
	{
		$timestamp = mktime($this->hora, $this->min, $this->seg, $this->m, $this->d, $this->a);
		$this->from_timestamp($timestamp);
		$this->_semana = date('W', $timestamp);
	}
	
	public function to_time()
	{
		$this->_ds();
		return $this->timestamp;
	}
	
	public function sumar_dias($dias)
	{
		$timestamp = mktime($this->hora, $this->min, $this->seg, $this->m, $this->d, $this->a);
		$timestamp += ($dias*86400);
		$this->from_timestamp($timestamp);
	}
	
	public function sumar_dias_en(lt_fecha $fe, $dias)
	{
		$timestamp = mktime($this->hora, $this->min, $this->seg, $this->m, $this->d, $this->a);
		$timestamp += ($dias*86400);
		$fe->from_timestamp($timestamp);
	}
	
	public function sumar_meses($meses)
	{
		$this->m += $meses;
		if ($meses > 0)
		{
			while ($this->m > 12)
			{
				$this->m -= 12;
				$this->a++;
			}
		}
		else
		{
			while ($this->m < 1)
			{
				$this->m += 12;
				$this->a--;
			}
		}
		if ($this->d > $this->ultimo()) $this->d = $this->ultimo();
		$this->_ds();
	}
	
	public function diferencia(lt_fecha $fe, $formato='d')
	{
		$diferencia = $this->timestamp - $fe->timestamp;
		if ($formato == 'd') $diferencia /= 86400;
		if ($formato == 'h') $diferencia /= 3600;
		if ($formato == 'm') $diferencia /= 60;
		return $diferencia;
	}
	
	private function _parse_hora($tmpstr)
	{
		$tmpar = explode(":", $tmpstr);
		if (isset($tmpar[0])) $this->hora = intval($tmpar[0]);
		if (isset($tmpar[1])) $this->min = intval($tmpar[1]);
		if (isset($tmpar[2])) $this->seg = intval($tmpar[2]);
	}
	
	private function _parse_fecha($tmpstr)
	{
		if (strpos($tmpstr, '/') !== false)
		{
			$tmpar = explode("/", $tmpstr);
			if (isset($tmpar[0])) $this->d = intval($tmpar[0]);
			if (isset($tmpar[1])) $this->m = intval($tmpar[1]);
			if (isset($tmpar[2])) $this->a = intval($tmpar[2]);
		}
		elseif (strpos($tmpstr, '-') !== false) $this->from_sql($tmpstr);
	}
	
	public function from_string($tmpstr)
	{
		$this->_blank();
		if ($this->tipo == LT_FECHA_T) $this->_parse_fecha($tmpstr);
		if ($this->tipo == LT_FECHAHORA_T)
		{
			$this->_parse_fecha(substr($tmpstr, 0, 10));
			$this->_parse_hora(substr($tmpstr, 11, 8));
		}
		if ($this->tipo == LT_HORA_T) $this->_parse_hora($tmpstr);
		$this->_ds();
	}
	
	/**
	 *
	 * Convertir fecha en string segun tipo
	 * @return string
	 * Fecha en forma de cadena DD/MM/AAAA [HH:MM:SS]
	 */
	public function to_string()
	{
		if ($this->tipo == LT_FECHA_T) $tmps = sprintf("%02d/%02d/%04d", $this->d, $this->m, $this->a);
		if ($this->tipo == LT_FECHAHORA_T) $tmps = sprintf("%02d/%02d/%04d %02d:%02d:%02d", 
				$this->d, $this->m, $this->a, $this->hora, $this->min, $this->seg);
		if ($this->tipo == LT_HORA_T) $tmps = sprintf("%02d:%02d:%02d", $this->hora, $this->min, $this->seg);
		return $tmps;
	}
	
	function __toString()
	{
		return $this->to_sql();
	}
	
	function from_sql($string)
	{
		$this->from_timestamp(strtotime($string));
	}
	
	/**
	 *
	 * Convertir fecha a formato AAAA-MM-DD [HH:MM:SS]
	 * @return string
	 * Fecha en formato AAAA-MM-DD [HH:MM:SS]
	 */
	public function to_sql()
	{
		$hrs = '';
		if ($this->tipo != LT_FECHA_T) $hrs = sprintf("%02d:%02d:%02d", $this->hora, $this->min, $this->seg);
		$fes = '';
		if ($this->tipo != LT_HORA_T) $fes = sprintf("%04d-%02d-%02d", $this->a, $this->m, $this->d);
		return sprintf("%s%s%s", $fes, $this->tipo == LT_FECHAHORA_T ? ' ':'', $hrs);
	}
	
	public function ampm()
	{
		$mer = 'am';
		$hora = $this->hora;
		if ($this->hora > 12)
		{ 
			$hora -= 12;
			$mer = 'pm';
		}
		return sprintf("%02d:%02d %s", $this->hora, $this->min, $mer);
	}
	
	/**
	 * 
	 * Alias de to_string()
	 * Convertir fecha en string segun tipo
	 * @return string
	 */
	public function s() { return $this->to_string(); }
	
	/**
	 * 
	 * Alias de to_sql()
	 * Convertir fecha en string para query SQL
	 * @return string
	 */
	public function sq() { return $this->to_sql(); }

	/**
	 * 
	 * Formato corto sin agno
	 * @return string
	 */
	public function ss()
	{
		$ss = $this->to_string();
		if ($this->tipo == LT_FECHA_T || $this->tipo == LT_HORA_T) $ss = substr($ss, 0, 5);
		if ($this->tipo == LT_FECHAHORA_T) $ss = substr($ss, 0, 5).' '.substr($ss, 11, 5);		
		return $ss;
	}
	
	/**
	 *
	 * Formato corto con agno
	 * @return string
	 */
	public function ssa()
	{
		$ss = $this->to_string();
		if ($this->tipo == LT_FECHA_T) $ss = substr($ss, 0, 5).'/'.substr($ss, -2, 2);
		if ($this->tipo == LT_FECHAHORA_T) $ss = substr($ss, 0, 5).' '.substr($ss, 11, 5);
		return $ss;
	}
	
	/**
	 *
	 * Convertir fecha formato AAAA-MM-DD [HH:MM:SS] a fecha
	 * @param string $string
	 * Fecha formato AAAA-MM-DD [HH:MM:SS]
	 */
	public function to_array($asociativo=true)
	{
		$mat = array();
		if ($asociativo)
		{
			if ($this->tipo == LT_FECHA_T) $mat = array('d'=>$this->d, 'm'=>$this->m, 'a'=>$this->a, 'ds'=>$this->ds);
			if ($this->tipo == LT_HORA_T) $mat = array('h'=>$this->hora, 'm'=>$this->min, 's'=>$this->seg);
		}
		else
		{
			if ($this->tipo == LT_FECHA_T) $mat = array($this->d, $this->m, $this->a, $this->ds);
			if ($this->tipo == LT_HORA_T) $mat = array($this->hora, $this->min, $this->seg);
		}
		return $mat;
	}
	
	/**
	 *
	 * Devuelve el nombre del dia de la semana
	 * @param array $tmpfe
	 * @return string
	 * Nombre del dia
	 */
	public function cdia($corto=FALSE)
	{
		if ($corto) $tmpcdia = array(0=>'','Dom','Lun','Mar','Mie','Jue','Vie','Sab');
		else $tmpcdia = array(0=>'','Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
		return $tmpcdia[ $this->ds ];
	}
	
	/**
	 *
	 * Devuelve el nombre del mes
	 * @return string
	 * Nombre del mes
	 */
	public function cmes() {
		$tmpcdia = array(0=>'','Enero','Febrero','Marzo','Abril','Mayo','Junio',
				'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
		return $tmpcdia[ $this->m ];
	}	
	
	/**
	 *
	 * Devuelve el ultimo dia del mes
	 * @return integer
	 */
	public function ultimo()
	{
		$maxday = array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
		if ($this->a % 4 == 0)
		{
			$maxday[2] = 29;
			if ($this->a % 100 == 0 && $this->a % 400 != 0) $maxday[2] = 28;
		}
		return $maxday[$this->m];
	}
	
	/**
	 * 
	 * Fija las horas, minutos y segundos a partir del $timestamp
	 * @param int $timestamp
	 */
	public function hms_from($timestamp)
	{
		$this->_blank();
		$nc = $timestamp;
		while ($nc > 0)
		{
			if ($nc >= 60)
			{ 
				$chunk = 60;
				$this->min++;
				if ($this->min > 60)
				{
					$this->hora++;
					$this->min = 0;
				}
			}
			else
			{ 
				$chunk = $nc;
				$this->seg = $chunk;
			}
			$nc -= $chunk;
		}
	}
	
	/**
	 *
	 * Comparar contra otra fecha
	 * @param lt_fecha $fecha
	 * Fecha con la que se comparara
	 * @return integer
	 * 0 si fecha1 es igual a fecha2, 1 si es mayor y -1 si es menor
	 */
	public function cmp(lt_fecha $fecha)
	{
		$horacmpi = 0;
		if ($this->a < $fecha->a) $horacmpi = -1;
		else
		{
			if ($this->a > $fecha->a) $horacmpi = 1;
			else
			{
				if ($this->m < $fecha->m) $horacmpi = -1;
				else
				{
					if ($this->m > $fecha->m) $horacmpi = 1;
					else
					{
						if ($this->d < $fecha->d) $horacmpi = -1;
						else
						{
							if ($this->d > $fecha->d) $horacmpi = 1;
						}
					}
				}
			}
		}
		return $horacmpi;
	}
	
	/**
	 *
	 * Comparar contra un agno y mes
	 * @param int $agno
	 * Agno con el que se compara
	 * @param int $agno
	 * Mes con el que se compara
	 * @return integer
	 * 0 si fecha1 es igual al $mes y $agno especificado, 1 si es mayor y -1 si es menor
	 */
	public function cmp_by_month($agno, $mes)
	{
		$horacmpi = 0;
		if ($this->a < $agno) $horacmpi = -1;
		else
		{
			if ($this->a > $agno) $horacmpi = 1;
			else
			{
				if ($this->m < $mes) $horacmpi = -1;
				else
				{
					if ($this->m > $mes) $horacmpi = 1;
				}
			}
		}
		return $horacmpi;
	}
	
	/**
	 *
	 * Rotar la fecha una cantidad de meses
	 * @param integer $meses
	 * Cantidad de meses a sumar
	 */
	public function rotar($meses, lt_fecha $destino)
	{
		$destino->d = $this->d;
		$destino->m = $this->m;
		$destino->a = $this->a;
		if ($meses > 0)
		{
			$destino->m = $this->m + $meses;
			while ($destino->m > 12)
			{
				$this->m -= 12;
				$this->a++;
			}
			$ultimo = $destino->ultimo();
			if ($this->d > $ultimo) $destino->d = $ultimo;
		}
		$destino->_ds();
	}
	
	/**
	 * 
	 * Regresa los feriados almacenados en sistema en formato aaaa-mm-dd en un arreglo de strings
	 * @param lt_form $fo
	 * @return array de las fechas en el formato aaaa-mm-dd
	 */
	public static function generarFeriados(lt_form $fo){
		$feriados = array();
		$hoy = new lt_fecha();
		$year = $hoy->a;
		//seleccionar los que siempre son la misma fecha
		$feriadosFijosCond = new lt_condicion('agno_id', '=', 0);
		$feriadosFijos = myquery::t($fo, 'feriados',$feriadosFijosCond,array('dia','mes_id'));
		foreach($feriadosFijos->a as $pos => $feriado){//TODO mejorar para usar como referencia las fechas comparadas
			array_push($feriados,$hoy->a."-".$feriado->mes_id."-".$feriado->dia);
			array_push($feriados,($hoy->a+1)."-".$feriado->mes_id."-".$feriado->dia);
			array_push($feriados,($hoy->a-1)."-".$feriado->mes_id."-".$feriado->dia);
		}
		//seleccionar los que cambian
		$feriadosMovCond = new lt_condicion('agno_id', '!=', 0);
		$feriadosMov = myquery::t($fo, 'feriados',$feriadosMovCond,array('dia','mes_id','agno_id'));
		foreach($feriadosMov->a as $pos => $feriado){
			array_push($feriados, $feriado->agno_id."-".$feriado->mes_id."-".$feriado->dia);
		}
		return $feriados;
	}
	
	/**
	 * 
	 * Funcion para calcular la cantidad de dias laborales desde la fecha representada por el objeto actual hasta la fecha especificada en $hasta
	 * @param lt_fecha $hasta
	 * @param array $feriados arreglo de strings en formato aaaa-mm-dd que representan los feriados a considerar
	 * @return int cantidad de dias laborales
	 */
	public function diasLaborales(lt_fecha $hasta,$feriados){
		// do strtotime calculations just once
		$endDate = strtotime($hasta->to_sql());
		$startDate = strtotime($this->to_sql());
		
		
		//The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
		//We add one to inlude both dates in the interval.
		$days = ($endDate - $startDate) / 86400 + 1;
		
		$no_full_weeks = floor($days / 7);
		$no_remaining_days = fmod($days, 7);
		
		//It will return 1 if it's Monday,.. ,7 for Sunday
		$the_first_day_of_week = date("N", $startDate);
		$the_last_day_of_week = date("N", $endDate);
		
		//---->The two can be equal in leap years when february has 29 days, the equal sign is added here
		//In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
		if ($the_first_day_of_week <= $the_last_day_of_week) {
			if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
			if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
		}
		else {
			// (edit by Tokes to fix an edge case where the start day was a Sunday
			// and the end day was NOT a Saturday)
		
			// the day of the week for start is later than the day of the week for end
			if ($the_first_day_of_week == 7) {
				// if the start date is a Sunday, then we definitely subtract 1 day
				$no_remaining_days--;
		
				if ($the_last_day_of_week == 6) {
					// if the end date is a Saturday, then we subtract another day
					$no_remaining_days--;
				}
			}
			else {
				// the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
				// so we skip an entire weekend and subtract 2 days
				$no_remaining_days -= 2;
			}
		}
		
		//The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
		//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
		$workingDays = $no_full_weeks * 5;
		if ($no_remaining_days > 0 )
		{
			$workingDays += $no_remaining_days;
		}
		
		//We subtract the holidays
		foreach($feriados as $feriado){
			$time_stamp=strtotime($feriado);
			//If the holiday doesn't fall in weekend
			if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
				$workingDays--;
		}
		
		return $workingDays;
	}
	
	function __construct($fuente=0, $tipo=LT_FECHA_T)
	{
		$this->tipo = $tipo;
		if (!isset($fuente))
		{ 
			$this->from_timestamp(time());
		}
		else
		{
			if (is_int($fuente)) $this->from_timestamp($fuente == 0 ? time(): $fuente);
			if (is_string($fuente)) $this->from_string($fuente);
			if (is_array($fuente)) $this->from_array($fuente);
			if (is_object($fuente) && get_class($fuente) == 'lt_fecha') $this->from_timestamp($fuente->timestamp);
		}
	}
	
	public static function fromT($timestamp, $tipo=LT_FECHA_T)
	{
		$tmpfe = new self();
		$tmpfe->from_timestamp($timestamp);
		return $tmpfe;
	}
	
	public static function fromDMA($dia=0, $mes=0, $agno=0)
	{
		$tmpfe = new self();
		if ($dia > 0) $tmpfe->d = $dia;
		if ($mes > 0) $tmpfe->m = $mes;
		if ($agno > 0) $tmpfe->a = $agno;
		$tmpfe->_ds();
		return $tmpfe;
	}
	
	public static function now()
	{
		return new lt_fecha(0, LT_FECHAHORA_T);
	}
	
	/**
	 * 
	 * Devuelve fecha al dia de hoy
	 * @return lt_fecha
	 */
	public static function hoy()
	{
		return new lt_fecha();
	}
	
	/**
	 * 
	 * Devuelve fecha al dia de ayer
	 * @return lt_fecha
	 */
	public static function ayer()
	{
		$tmpfe = new lt_fecha();
		$tmpfe->sumar_dias(-1);
		return $tmpfe;
	}
	
	/**
	 * 
	 * Devuelve fecha al primero del mes/agno especificado
	 * @param int $mes
	 * (opcional) Mes, por defecto el actual
	 * @param int $agno
	 * (opcional) Agno, por defecto el actual
	 * @return lt_fecha
	 */
	public static function al_primero($mes=0, $agno=0)
	{
		$tmpfe = new lt_fecha();
		if ($mes > 0) $tmpfe->m = $mes;
		if ($mes < 0) $tmpfe->sumar_meses($mes);
		if ($agno > 0) $tmpfe->a = $agno;
		$tmpfe->d = 1;
		$tmpfe->_ds();
		return $tmpfe;
	}
	
	/**
	 * 
	 * Devuelve fecha al ultimo del mes/agno especificado
	 * @param int $mes
	 * (opcional) Mes, por defecto el actual
	 * @param int $agno
	 * (opcional) Agno, por defecto el actual
	 * @return lt_fecha
	 */
	public static function al_ultimo($mes=0, $agno=0)
	{
		$tmpfe = new lt_fecha();
		if ($agno > 0) $tmpfe->a = $agno;
		if ($mes > 0) $tmpfe->m = $mes;
		if ($mes < 0) $tmpfe->sumar_meses($mes);
		$tmpfe->d = $tmpfe->ultimo();
		$tmpfe->_ds();
		return $tmpfe;
	}
	
	/**
	 * 
	 * Devuelve la fecha del primer dia del agno especificado
	 * @param int $agno
	 * Por defecto, 0 (agno en curso)
	 * @return lt_fecha
	 */
	public static function agno_primero($agno=0)
	{
		$tmpfe = new lt_fecha();
		if ($agno != 0) $tmpfe->a = $agno;
		$tmpfe->m = 1;
		$tmpfe->d = 1;
		$tmpfe->to_time();
		return $tmpfe;
	}

	/**
	 * 
	 * Devuelve fecha del ultimo dia del agno especificado
	 * @param int $agno
	 * Por defecto, 0 (agno en curso)
	 * @return lt_fecha
	 */
	public static function agno_ultimo($agno=0)
	{
		$tmpfe = new lt_fecha();
		if ($agno != 0) $tmpfe->a = $agno;
		$tmpfe->m = 12;
		$tmpfe->d = 31;
		$tmpfe->to_time();
		return $tmpfe;
	}

    /**
     * Devuelve fecha relativa al mes pasado
     * @param int $al_dia
     * Dia del mes pasado, 0 dia actual, -1, al ultimo, mayor a 0, dia especificado. Por defecto -1
     * @return lt_fecha
     */
	public static function mes_pasado($al_dia=-1)
    {
        $tmpfe = new lt_fecha();
        if ($al_dia == -1) $tmpfe->al_ultimo();
        elseif ($al_dia > 0) $tmpfe->setDia($al_dia);
        $tmpfe->sumar_meses(-1);
        return $tmpfe;
    }
}

/**
 *
 * Crea una fecha desde el unix timestamp especificado
 * @param integer $ttime
 * Unix timestamp
 * @return array
 * Fecha retornada en array 'd'=>dia,'m'=>mes,'a'=>agno,'ds'=>dia semana
 */
function fecha_from($ttime=0)
{
	if (is_null($ttime)) $ttime = 0;
	$tmpfe = array('d'=>0, 'm'=>0, 'a'=>0, 'ds'=>0);
	$tmptm = localtime($ttime);
	$tmpfe['d'] = $tmptm[3];
	$tmpfe['m'] = $tmptm[4] + 1;
	$tmpfe['a'] = $tmptm[5] + 1900;
	$tmpfe['ds'] = $tmptm[6] + 1;
	return $tmpfe;
}

/**
 *
 * Retorna la fecha actual del sistema
 * @return array
 * Fecha retornada en array 'd'=>dia,'m'=>mes,'a'=>agno,'ds'=>dia semana
 */
function fecha()
{
	return fecha_from(time());
}

/**
 *
 * Recalcula el dia de la semana. Funcion interna, no usar.
 * @param array $tmpfe
 * @return array
 */
function fecha_ds($tmpfe) {
	$tmpt = mktime(0, 0, 0, $tmpfe["m"], $tmpfe["d"], $tmpfe["a"]);
	$tmptm = localtime($tmpt);
	$tmpfe["d"] = $tmptm[3];
	$tmpfe["m"] = $tmptm[4] + 1;
	$tmpfe["a"] = $tmptm[5] + 1900;
	$tmpfe["ds"] = $tmptm[6] + 1;
	return $tmpfe;
}

/**
 *
 * Sumar algebraicamente dias a la fecha especificada
 * @param array $lafe
 * Fecha
 * @param integer $nday
 * Cantidad de dias a sumar o restar
 * @return array
 * Nueva fecha
 */
function fecha_sumd($lafe, $nday)
{
	$tmpfe=$lafe;
	$tmpt=mktime(0,0,0, $lafe["m"],$lafe["d"],$lafe["a"]);
	$tmpt+=($nday*86400);
	$tmptm=localtime($tmpt);
	$tmpfe["d"]=$tmptm[3];
	$tmpfe["m"]=$tmptm[4]+1;
	$tmpfe["a"]=$tmptm[5]+1900;
	$tmpfe["ds"]=$tmptm[6]+1;
	return $tmpfe;
}

/**
 *
 * Sumar algebraicamente meses a la fecha especificada
 * @param array $lafe
 * Fecha
 * @param integer $nmonths
 * Cantidad de meses a sumar o restar
 * @return array
 * Nueva fecha
 */
function fecha_summ($lafe, $nmonths)
{
	$tmpfe=$lafe;
	$tmpfe['m'] += $nmonths;
	if ($nmonths > 0)
	{
		while ($tmpfe['m'] > 12)
		{
			$tmpfe['m'] -= 12;
			$tmpfe['a']++;
		}
	}
	else
	{
		while ($tmpfe['m'] < 1)
		{
			$tmpfe['m'] += 12;
			$tmpfe['a']--;
		}
	}
	$tmpfe = fecha_ds($tmpfe);

	return $tmpfe;
}

/**
 *
 * Calcula la diferencia en dias entre dos fechas
 * @param array $fe0
 * Fecha inicial
 * @param array $fe1
 * Fecha terminal
 * @return number
 * Cantidad de dias
 */
function fecha_diff($fe0, $fe1)
{
	$t0 = mktime(0, 0, 0, $fe0['m'], $fe0['d'], $fe0['a']);
	$t1 = mktime(0, 0, 0, $fe1['m'], $fe1['d'], $fe1['a']);
	$dife = $t1 - $t0;
	return $dife / 86400;
}

/**
 *
 * Convierte una fecha en formato "DD/MM/AAAA" a fecha
 * @param string $tmpstr
 * Cadena en formato DD/MM/AAAA
 * @return array
 * Fecha
 */
function ctod($tmpstr)
{
	$tmpfe = array("d" =>0, "m" =>0, "a" =>0, "ds" =>0);
	$tmpar = explode("/", $tmpstr);
	if (isset($tmpar[0])) $tmpfe["d"] = intval($tmpar[0]);
	if (isset($tmpar[1])) $tmpfe["m"] = intval($tmpar[1]);
	if (isset($tmpar[2])) $tmpfe["a"] = intval($tmpar[2]);
	return fecha_ds($tmpfe);
}

/**
 *
 * Convierte fecha en unix timestamp
 * @param array $tmpfe
 * Fecha
 * @return number
 * Unix timestamp
 */
function fecha2time($tmpfe)
{
	return mktime(0, 0, 0, $tmpfe['m'], $tmpfe['d'], $tmpfe['a']);
}

/**
 *
 * Convertir fecha en string DD/MM/AAAA
 * @param array $tmpfe
 * @return string
 * Fecha en forma de cadena DD/MM/AAAA
 */
function dtoc($tmpfe) {
	$tmpdtoc = sprintf("%02d/%02d/%04d", $tmpfe["d"], $tmpfe["m"], $tmpfe["a"]);
	return $tmpdtoc;
}

/**
 *
 * Convertir fecha en string AAAAMMDD
 * @param unknown_type $tmpfe
 * @return string
 * Fecha en forma de cadena AAAAMMDD
 */
function dtos($tmpfe) {
	$tmpdtos = sprintf("%04d%02d%02d", $tmpfe["a"], $tmpfe["m"], $tmpfe["d"]);
	return $tmpdtos;
}

/**
 *
 * Convertir fecha formato AAAA-MM-DD a fecha
 * @param string $msqfe
 * Fecha formato AAAA-MM-DD
 * @return array
 * Fecha
 */
function mstod($msqfe)
{
	$ts = strtotime($msqfe);
	return fecha_from($ts);
}

/**
 *
 * Convertir fecha a formato AAAA-MM-DD (mysql)
 * @param array $thefe
 * @return string
 * Fecha en formato AAAA-MM-DD (mysql)
 */
function dtoms($thefe)
{
	return sprintf("%04d-%02d-%02d", $thefe['a'], $thefe['m'], $thefe['d']);
}

/**
 *
 * Devuelve el nombre del dia de la semana
 * @param array $tmpfe
 * @return string
 * Nombre del dia
 */
function cdia($tmpfe) {
	$tmpcdia = array(1=>'Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
	return $tmpcdia[ $tmpfe["ds" ] ];
}

/**
 *
 * Devuelve el nombre del mes
 * @param array $tmpfe
 * @return string
 * Nombre del mes
 */
function cmes($tmpfe) {
	$tmpcdia = array(1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio',
			'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
	return $tmpcdia[ $tmpfe['m' ] ];
}

/**
 *
 * Devuelve el nombre del mes especificado
 * @param integer $mes_n
 * Numero del mes 1..12
 * @return string
 * Nombre del mes
 */
function cnmes($mes_n) {
	$tmpcdia = array(1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio',
			'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
	return $tmpcdia[$mes_n];
}

/**
 *
 * Devuelve el ultimo dia del mes
 * @param array $tmpfe
 * @return integer
 */
function fecha_ultimo($tmpfe)
{
	$maxday = array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
	if ($tmpfe['a'] % 4 == 0)
	{
		$maxday[2] = 29;
		if ($tmpfe['a'] % 100 == 0 && $tmpfe['a'] % 400 != 0) $maxday[2] = 28;
	}
	return $maxday[$tmpfe['m']];
}

/**
 *
 * Verifica si un array fecha es valido
 * @param array $tmpfe
 * @return boolean
 */
function fecha_valida($tmpfe) {
	$isok=FALSE;
	$maxday = array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
	if ($tmpfe["m"] >= 1 && $tmpfe["m"] <= 12)
	{
		if ($tmpfe["a"] < 100) $tmpfe["a"] += 2000;
		if ($tmpfe["a"] >= 1900 && $tmpfe["a"] <= 2099)
		{
			if ($tmpfe["a"] % 4 == 0)
			{
				$maxday[2]=29;
				if ($tmpfe["a"] % 100 == 0 && $tmpfe["a"] % 400 != 0) $maxday[2]=28;
			}
			$mes = $tmpfe["m"];
			if ($tmpfe["d"] >= 1 && $tmpfe["d"] <= $maxday[$mes])
			{
				$isok=TRUE;
			}
		}
	}
	return $isok;
}

/**
 *
 * Compara dos array de fecha
 * @param Array $fecha1
 * @param Array $fecha2
 * @return integer
 * 0 si fecha1 es igual a fecha2, 1 si es mayor y -1 si es menor
 */
function fecha_cmp($fecha1, $fecha2)
{
	$horacmpi = 0;

	if ($fecha1['a'] < $fecha2['a']) $horacmpi = -1;
	else
	{
		if ($fecha1['a'] > $fecha2['a']) $horacmpi = 1;
		else
		{
			if ($fecha1['m'] < $fecha2['m']) $horacmpi = -1;
			else
			{
				if ($fecha1['m'] > $fecha2['m']) $horacmpi = 1;
				else
				{
					if ($fecha1['d'] < $fecha2['d']) $horacmpi = -1;
					else
					{
						if ($fecha1['d'] > $fecha2['d']) $horacmpi = 1;
					}
				}
			}
		}
	}

	return $horacmpi;
}

/**
 *
 * Rotar la fecha una cantidad de meses
 * @param array $lafe
 * Fecha
 * @param integer $nmeses
 * Cantidad de meses
 */
function fecha_rotar($lafe, $nmeses)
{
	$tmpfe = $lafe;
	if ($nmeses > 0)
	{
		$tmpfe['m'] = $lafe['m'] + $nmeses;
		while ($tmpfe['m'] > 12)
		{
			$tmpfe['m'] -= 12;
			$tmpfe['a']++;
		}
		$ultimo = fecha_ultimo($tmpfe);
		if ($lafe['d'] > $ultimo) $tmpfe['d'] = $ultimo;
	}
	return fecha_ds($tmpfe);
}

/**
 *
 * Convierte hora en unix timestamp
 * @param array $tmphr
 * Hora
 * @return number
 * Unix timestamp
 */
function hora2time($tmphr)
{
	return mktime($tmphr[0], $tmphr[1], $tmphr[2]);
}

/**
 *
 * Crear hora desde unix timestamp especificado
 * @param integer $tmseed
 * @return array
 * Hora en array con indices numericos 0=>H, 1=>M, 2=>S
 */
function hora_from($tmseed)
{
	$hh = array(0 => 0, 1 => 0, 2 => 0); /* h:m:s */
	$t2 = localtime($tmseed, true);
	$hh[0] = $t2['tm_hour'];
	$hh[1] = $t2['tm_min'];
	$hh[2] = $t2['tm_sec'];
	return $hh;
}

/**
 *
 * Crear hora desde unix timestamp actual del servidor
 * @param integer $tmseed
 * @return array
 * Hora en array con indices numericos 0=>H, 1=>M, 2=>S
 */
function hora()
{
	return hora_from(time());
}

/**
 *
 * Convertir cadena a array de hora
 * @param string $sh
 * Hora en formato HH:MM:SS
 * @return array
 * Hora
 */
function stoh($sh)
{
	$hh[0]=intval(substr($sh,0,2));
	$hh[1]=intval(substr($sh,2,2));
	$hh[2]=intval(substr($sh,4,2));
	return $hh;
}

/**
 *
 * Convertir hora en cadena
 * @param array $hh
 * Hora
 * @return string
 * Cadena en formato HH:MM:SS
 */
function htos($hh)
{
	$sh=sprintf("%02d%02d%02d",$hh[0],$hh[1],$hh[2]);
	return $sh;
}

/**
 *
 * Convertir cadena HH:MM:SS en hora
 * @param string $shr
 * Cadena HH:MM:SS
 * @return array
 * Hora
 */
function ctoh($shr)
{
	$hh = array(0,0,0);
	$ash = explode(":", $shr);
	$hh[0] = intval($ash[0]);
	$hh[1] = intval($ash[1]);
	$hh[2] = intval($ash[2]);
	if ($hh[0] > 23) $hh[0] = 23;
	if ($hh[1] > 59) $hh[1] = 59;
	if ($hh[2] > 59) $hh[2] = 59;
	return $hh;
}

/**
 *
 * Convertir hora en cadena HH:MM:SS
 * @param array $hh
 * Hora
 * @return string
 * Cadena HH:MM:SS
 */
function htoc($hh)
{
	$ch=sprintf("%02d:%02d:%02d",$hh[0],$hh[1],$hh[2]);
	return $ch;
}

/**
 *
 * Devuelve hora actual del sistema en una cadena HH:MM:SS
 * @return string
 */
function chora()
{
	return strftime("%H:%M:%S", time());
}

/* Obsoleta
 *
function hora_rmin($h1,$min)
{
$ha = $h1;
$ha[1] -= $min;
while ($ha[1] < 0)
{
$ha[0] -= 1;
$ha[1] += 60;
}
return $ha;
}
*/

/**
 *
 * Compara dos horas
 * @param array $h1
 * @param array $h2
 * @return number
 * 0 si h1 es igual a h2, 1 si es mayor, -1 si es menor
 */
function hora_cmp($h1, $h2)
{
	$horacmpi = 0;

	if ($h1[0] < $h2[0]) $horacmpi = -1;
	else
	{
		if ($h1[0] > $h2[0]) $horacmpi = 1;
		else
		{
			if ($h1[1] < $h2[1]) $horacmpi = -1;
			else
			{
				if ($h1[1] > $h2[1]) $horacmpi = 1;
				else
				{
					if ($h1[2] < $h2[2]) $horacmpi = -1;
					else
					{
						if ($h1[2] > $h2[2]) $horacmpi = 1;
					}
				}
			}
		}
	}

	return $horacmpi;
}

/**
 *
 * horas, minutos y segundos que contiene el timestamp $tm
 * @param int $seconds
 * Cantidad de segundos
 * @return string
 * Devuelve una cadena en el formato '99h 99m 99s'
 */
function hms2($seconds)
{
	$w = $d = $h = $m = $mes = 0;

	if ($seconds > 3600)
	{
		$h = floor($seconds / 3600);
		$seconds = $seconds % 3600;
	}
	if ($seconds > 60)
	{
		$m = floor($seconds / 60);
		$seconds = $seconds % 60;
	}

	$sw = $sd = $sh = $sm = $ss = $smes = "";
	if ($h > 0) $sh = sprintf(" %dh", $h);
	if ($m > 0) $sm = sprintf(" %dm", $m);
	if ($seconds > 0) $ss = sprintf(" %ds", $seconds);
	return sprintf("%s%s%s", $sh, $sm, $ss);
}

/**
 *
 * Devuelve las horas, minutos y segundos que contiene el timestamp $tm
 * @param int $tm
 * Timestamp origen
 * @return string
 * Devuelve una cadena en el formato '99h 99m 99s'
 */
function hms($tm)
{
	return dhms($tm);
}

/**
 *
 * Devuelve los dias, horas, minutos y segundos que contiene el timestamp $tm
 * @param int $seconds
 * Cantidad de segundos
 * @return string
 * Devuelve una cadena en el formato '99d 99h 99m 99s'
 */
function dhms($seconds)
{
	$w = $d = $h = $m = $mes = 0;

	if ($seconds > 604800)
	{
		$w = floor($seconds / 604800);
		$seconds = $seconds % 604800;
		if ($w > 3)
		{
			$mes = $w / 4;
			$w = $w % 4;
		}
	}
	if ($seconds > 86400)
	{
		$d = floor($seconds / 86400);
		$seconds = $seconds % 86400;
	}
	if ($seconds > 3600)
	{
		$h = floor($seconds / 3600);
		$seconds = $seconds % 3600;
	}
	if ($seconds > 60)
	{
		$m = floor($seconds / 60);
		$seconds = $seconds % 60;
	}

	$sw = $sd = $sh = $sm = $ss = $smes = "";
	if ($mes > 0) $smes = sprintf(" %dmes", $mes);
	if ($w > 0) $sw = sprintf(" %dsem", $w);
	if ($d > 0) $sd = sprintf(" %dd", $d);
	if ($h > 0) $sh = sprintf(" %dh", $h);
	if ($m > 0) $sm = sprintf(" %dm", $m);
	if ($seconds > 0) $ss = sprintf(" %ds", $seconds);
	return sprintf("%s%s%s%s%s%s", $smes, $sw, $sd, $sh, $sm, $ss);
}

/**
 *
 * Determina si la fecha suministrada se encuentra entre la fecha $inicio y $fin
 * @param array $fe
 * Fecha a verificar
 * @param array $inicio
 * Fecha de inicio del lapso
 * @param array $fin
 * Fecha de cierre del lapso
 * @return bool
 * Verdadero o falso si la fecha se encuentra ubicada en el lapso especificado
 */
function fecha_entre($fe, $inicio, $fin)
{
	return (fecha_cmp($fe, $inicio) >= 0) && (fecha_cmp($fe, $fin) <= 0);
}

/**
 *
 * Determina si el $mes y el $agno son mayores o menores a $fecha
 * @param array $fecha
 * Fecha de comparacion
 * @param int $mes
 * @param int $agno
 * @return int
 * Devuelve 0 si mes/agno es igual a la fecha, 1 si es mayor y -1 si es menor
 */
function fecha_cmp_pormes($fecha, $mes, $agno)
{
	$icmp = 0;
	if ($fecha['a'] < $agno) $icmp = -1;
	elseif ($fecha['a'] > $agno) $icmp = 1;
	if ($icmp == 0)
	{
		if ($fecha['m'] < $mes) $icmp = -1;
		elseif ($fecha['m'] > $mes) $icmp = 1;
	}
	return $icmp;
}
?>