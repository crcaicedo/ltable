var ltform_msgTO;
var ltform_msgIST=0;
var alertxTO;
function enfocar(elobjs)
{
	document.getElementById(elobjs).focus();
}
function alertx(elobj, smsg)
{
	var ns = elobj.name;
	alert(smsg);
	alertxTO = setTimeout("enfocar('" + ns + "')", 50);
}
function lt_numen(nv)
{
    var sv = nv.toString();
    if (sv.indexOf(".") >= 0)
    {
    	sv = sv.replace(/\./g, "");
    }
    if (sv.indexOf(",") >= 0)
    {
    	sv = sv.replace(/\,/, ".");
    }
    return Number(sv);
}

function addCommas(nStr)
{
  nStr += '';
  x = nStr.split(',');
  x1 = x[0];
  x2 = x.length > 1 ? ',' + x[1] : '';
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1))
  {
    x1 = x1.replace(rgx, '$1' + '.' + '$2');
  }
  return x1 + x2;
}

function lt_numes(xv)
{
    var nv = Number(xv);
    var fv = nv.toFixed(2);
    var sv = fv.toString();
    if (sv.indexOf(".") >= 0)
    {
    	sv = sv.replace(/\./, ",");
    }
    return addCommas(sv);
}
function lt_numes_prec(xv, precision)
{
    var nv = Number(xv);
    var fv = nv.toFixed(precision);
    var sv = fv.toString();
    if (sv.indexOf(".") >= 0)
    {
    	sv = sv.replace(/\./, ",");
    }
    return addCommas(sv);
}
function lt_ipaddr(elobj)
{
	var isok = false;
	var rgx = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
	if (!rgx.test(elobj.value)) alertx(elobj, "Direccion IP incorrecta"); else isok = true;
	return isok;
}
function lt_novacio(otb)
{
	var isok = true;
	var re = /^\s{1,}$/g;
	if (!otb.readOnly && !otb.disabled)
	{
		if ((otb.value.length===0) || (otb.value===null) || ((otb.value.search(re)) > -1))
		{
			isok = false;
			alertx(otb, "El campo no debe estar en blanco");
			//otb.focus();
		}
	}
    return isok;
}
function lt_novaciox(otb)
{
	var isok = false;
	var re = /^\s{1,}$/g;
	if ((otb.value.length===0) || (otb.value===null) || ((otb.value.search(re)) > -1))
	{
		alertx(otb, "El campo no debe estar en blanco");
		//otb.focus();
	}
	else
	{
		isok = true;
	}
    return isok;
}
function lt_vacio(otb)
{
    var isok = true;
    var re = /^\s{1,}$/g;
    if ((otb.value.length===0) || (otb.value===null) || ((otb.value.search(re)) > -1))
    {
	isok = true;
    }
    else
    {
	isok = false;
    }
    return isok;
}
function lt_telefono(otb,forced)
{
    var isok = false;
    var re = /^([0-9]{4}[\s\-]?)?([0-9]{7}){1}$/;
    if (!otb.readOnly && !otb.disabled)
    {
    	if (lt_vacio(otb))
    	{
    		if (forced)
    		{
    			alertx(otb, "Telefono en blanco");
    			//otb.focus();
    		}
    		else
    		{
    			isok = true;
    		}
    	}
    	else
    	{
    		isok = re.test(otb.value);
    		if (!isok)
    		{
    			alertx(otb, "Telefono debe tener siete digitos o once digitos: 9999999 o 99999999999");
    			//otb.focus();
    		}
    	}
    }
    else
    {
    	isok = true;
    }
    return isok;
}
function lt_telefono_int(otb,forced)
{
    var isok = false;
    var re = /^((00|\+){1}[0-9]{2,3}[\s\-]?)?([0-9]{2,4}[\s\-]?)?([0-9]{6,10}){1}$/;
    if (!otb.readOnly && !otb.disabled)
    {
    	if (lt_vacio(otb))
    	{
    		if (forced)
    		{
    			alertx(otb, "Telefono en blanco");
    			//otb.focus();
    		}
    		else
    		{
    			isok = true;
    		}
    	}
    	else
    	{
    		isok = re.test(otb.value);
    		if (!isok)
    		{
    			alertx(otb, "Telefono debe tener formato correcto, nacional o internacional");
    			//otb.focus();
    		}
    	}
    }
    else
    {
    	isok = true;
    }
    return isok;
}
function lt_numero(otb)
{
	var isok = true;
	var re = /^([0-9]+)$/;
	if (!re.test(otb.value))
	{
		isok = false;
		alertx(otb, "Solo se permiten digitos numericos");
		//otb.focus();
	}
    return isok;
}
function lt_moneda(otb)
{
    var isok = true;
    var re = /^[0-9]{1,}([,]?[0-9]{0,2}){0,1}$/;
    ///var re = /^([0-9]{0,3}[\.]?)+([,]?[0-9]{0,2}){0,1}$/;
    if (!re.test(otb.value))
    {
    	isok = false;
    	alertx(otb, "Introduzca una cifra valida.\nEjemplos: 1000,00 ; 1000");
    	//otb.focus();
    }
    return isok;
}
function lt_numerico(otb,ndig,ndec,vmin,vmax)
{
    var isok = false;
    var re = new RegExp("^-?[0-9.]{1,}([,]?[0-9]{0,"+ ndec +"}){0,1}$");
    if (re.test(otb.value))
    {
    	var sval2 = otb.value.replace(/\./g, '');
    	var sval = sval2.replace(/,/,'.');
    	var valor = Number(sval);
    	if (valor >= vmin && valor <= vmax)
    	{
    		isok = true;
    	}
    	else
    	{
    		alertx(otb, "Numero (" + otb.value + ") fuera de rango.\n"+
    			"Rango: Mayor igual que " + lt_numes(vmin) + " y menor igual que " + 
    			lt_numes(vmax));
    		//otb.focus();
    	}
    }
    else
    {
    	alertx(otb, "Introduzca una cifra valida\nEjemplos: 1000,00 ; 1000");
    	//otb.focus();
    }
    return isok;
}
function lt_digitos(otb, largo)
{
    var isok = false;
    var re = new RegExp("^[0-9]{"+largo+"}$");

    isok = re.test(otb.value);
    if (!isok)
    {
    	alertx(otb, "Deben ser "+ largo + " digitos numericos.");
    	//otb.focus();
    }
    return isok;
}
function lt_digitos_var(otb, lmin, lmax)
{
    var isok = false;
    var re = new RegExp("^[0-9]{"+lmin+","+lmax+"}$");

    isok = re.test(otb.value);
    if (!isok)
    {
    	alertx(otb, "Deben ser de "+ lmin + " a " + lmax + " digitos numericos.");
    	//otb.focus();
    }
    return isok;
}
function lt_digitox(otb, largo)
{
    var isok = false;
    var re = new RegExp("^[0-9Xx]{"+largo+"}$");

    isok = re.test(otb.value);
    if (!isok)
    {
    	alertx(otb, "Deben ser "+ largo + " digitos numericos.");
    	//otb.focus();
    }
    return isok;
}
function lt_entero(otb,ndig,vmin,vmax)
{
    var isok = false;
    var re = new RegExp("^[0-9]{1,"+ndig+"}$");
   
    if (re.test(otb.value))
    {
    	var valor = Number(otb.value);
    	if (valor >= vmin && valor <= vmax)
    	{
    		isok = true;
    	}
    	else
    	{
    		alertx(otb, "Numero fuera de rango.\nRango: Mayor igual que " + vmin + " y menor igual que " + vmax);
    		//otb.focus();
    	}
    }
    else
    {
    	alertx(otb, "Introduzca solo digitos numericos, maximo "+ndig+" digitos.");
    	//otb.focus();
    }
    return isok;
}
function lt_cedula(otb)
{
	return lt_entero(otb, 9, 1, 999999999);
}
function lt_largo(otb,minimo,maximo)
{
    var isok = false;
    var re = /^\s{1,}$/g;
    if (otb.value.search(re) > -1)
    {
    	alertx(otb, "El campo no debe estar en blanco");
    }
    else
    {
        var re = new RegExp("^.{"+minimo+","+maximo+"}$");
        if (re.test(otb.value))
        {
    		isok = true;
        }
        else
        {
    		alertx(otb, "Largo del campo es invalido.\nMinimo: " + minimo + " caracteres.\nMaximo: " + maximo + " caracteres.");
    	}
    }
    return isok;
}
function lt_largo_fijo(otb,largo)
{
    var isok = false;
    var re = /^\s{1,}$/g;
    if (otb.value.search(re) > -1)
    {
    	alertx(otb, "El campo no debe estar en blanco");
    }
    else
    {
        var re = new RegExp("^.{"+largo+"}$");
        if (re.test(otb.value))
        {
    		isok = true;
        }
        else
        {
    		alertx(otb, "Largo del campo debe ser " + largo + " caracteres.");
    	}
    }
    return isok;
}
function lt_forbidden(otb)
{
    var isok = false;
    var re = new RegExp("['\"]+");
    if (lt_vacio(otb))
    {
    	isok = true;
    }
    else
    {
    	isok = !re.test(otb.value);
    	if (!isok)
    	{
    		alertx(otb, "Campo contiene caracteres invalidos como comillas simples o dobles.");
    		//otb.focus();
    	}
    }
    return isok;
}
function lt_numletra(otb)
{
    var isok = false;
    var re = /^[0-9a-zA-Z\s\.,:;_\-]+$/;
    isok = re.test(otb.value);
    if (!isok)
    {
    	alertx(otb, "Campo " + otb.name + " solo debe contener letras, numeros y signos de puntuacion.");
    	//otb.focus();
    }
    return isok;
}
function lt_rif(otb)
{
    var isok = false;
    var re = new RegExp("^[vVjJEeGg]{1}[\\-]{1}[0-9]{8}[\\-]{1}[0-9]{1}$");

    if (!re.test(otb.value))
    {
    	alertx(otb, "RIF debe estar en el formato [J|V|E|G]-99999999-9");
    	//otb.focus();
    }
    else
    {
    	isok = true;
    }
    return isok;
}
function lt_email(obj)
{
    var isok = false;
    var re = /^[0-9a-zA-Z\._]+[@]{1}[0-9a-zA-Z]+[\.]{1}[a-zA-Z]{2,4}([\.]{1}[a-zA-Z]{2})?$/;
    if (lt_vacio(obj))
    {
    	isok = true;
    }
    else
    {
    	isok = re.test(obj.value);
    	if (!isok)
    	{
    		alertx(obj, "Direccion de correo electronico incorrecta.");
    		//obj.focus();
    	}
    }
    return isok;
}
function lt_ubicacion(obj)
{
    //alert("Que ubicacion mas cutre!");
}

function ctod(elstr)
{
	this.d = Number(elstr.substr(0,2));
	this.m = Number(elstr.substr(3,2));
	this.a = Number(elstr.substr(6,4));
}

function dtoc()
{
	return sprintf("%02d/%02d/%04d", this.d, this.m, this.a);
}

function sumMonths(nMonths)
{
	var ma = this.m + nMonths;
	if (nMonths > 0)
	{
		while (ma > 12)
		{
			ma = ma - 12;
			this.a = this.a + 1;
		}
	}
	if (nMonths < 0)
	{
		while (ma < 1)
		{
			ma = ma + 12;
			this.a = this.a - 1;
		}
	}
	this.m = ma;
}

function ultimo()
{
	var maxday = 31;
	
	if (this.m == 4 || this.m == 6 || this.m == 9 || this.m == 11) maxday = 30;
	if (this.m == 2)
	{
		maxday = 28;
		if (this.a % 4 == 0)
		{
			maxday = 29;
			if (this.a % 100 == 0 && this.a % 400 != 0) maxday = 28;
		}
	}
	
    return maxday;
}

function fecha()
{
	var lafe = new Date();
	this.d = lafe.getDate();
	this.m = lafe.getMonth() + 1;
	this.a = lafe.getFullYear();
	this.ctod = ctod;
	this.dtoc = dtoc;
	this.sumMonths = sumMonths;
	this.ultimo = ultimo;
}

function lt_chkfecha(otb)
{
	var isok = false;
	var re = new RegExp("^([0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4})$");
	if (!re.test(otb.value))
	{
		alertx(otb, "Introduzca una fecha valida DD/MM/AAAA.");
	}
	else
	{
		var tmpfe = new fecha();
		tmpfe.ctod(otb.value);
		if (tmpfe.a >= 1901 && tmpfe.a <= 2030)
		{
			if (tmpfe.m >= 1 && tmpfe.m <= 12)
			{
				if (tmpfe.d >= 1 && tmpfe.d <= tmpfe.ultimo())
				{
					isok = true;
				}
				else alertx(otb, "Dia fuera de rango [1.." + tmpfe.ultimo() + 
					"] -> " + tmpfe.d);
			}
			else alertx(otb, "Mes fuera de rango [1..12] -> " + tmpfe.m);
		}
		else alertx(otb, "Agno fuera de rango [1901..2030] -> " + tmpfe.a);
	}
	return isok;
}

function lt_chkhora(otb)
{
	var isok = false;
	var re = new RegExp("^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$");
	if (!(isok = re.test(otb.value)))
	{
		alertx(otb, "Introduzca una hora valida HH:MM[:SS]");
	}
	return isok;
}

function lt_chkfecham(elobj, elobj2)
{
	if (lt_chkfecha(elobj))
	{
		var fex = new fecha();
		fex.ctod(elobj.value);
		fex.d = fex.ultimo();
		elobj2.value = fex.dtoc();
	}
}

function lt_sociedad(elobj)
{
    var i;
    var isdis = !elobj.checked;
    var isdis2;

    for (i=1; i<5; i++)
    {
    	$("activa"+i).disabled = isdis;
    	isdis2 = true;
    	if (!isdis)
    	{
    		isdis2 = !$("activa"+i).checked;
    	}

    	$("nombres"+i).disabled = isdis2;
    	$("apellidos"+i).disabled = isdis2;
    	$("ci"+i).disabled = isdis2;
    	$("rif"+i).disabled = isdis2;
    	$("sexo"+i).disabled = isdis2;
    	$("edocivil"+i).disabled = isdis2;
    	$("nacion"+i).disabled = isdis2;
    	$("pais"+i).disabled = isdis2;
    }

    return true;
}
function lt_activarsocio(elobj, socid)
{
	$("nombres" + socid).disabled = !elobj.checked;
	$("apellidos" + socid).disabled = !elobj.checked;
	$("ci" + socid).disabled = !elobj.checked;
	$("rif" + socid).disabled = !elobj.checked;
	$("sexo" + socid).disabled = !elobj.checked;
	$("edocivil" + socid).disabled = !elobj.checked;
	$("nacion" + socid).disabled = !elobj.checked;
	$("pais" + socid).disabled = !elobj.checked;
	
    return true;
}

function lt_confserial(elobj)
{
	var isok = false;
	if (lt_digitos(elobj, 6))
	{
		var serial1 = Number(elobj.value);
		var serial2 = prompt("Introduzca el serial de confirmacion: " + serial1, "");
		if (serial1 != serial2)
		{
			alertx(elobj, "Serial de confirmacion incorrecto.");
			//elobj.focus();
		}
		else
		{
			isok = true;
		}
	}
	return isok;
}

function ajaxCreate()
{
    var xmlHttp = null;
    try
    {
	// Firefox, Opera 8.0+, Safari
	xmlHttp=new XMLHttpRequest();
    }
    catch (e)
    {
	// Internet Explorer
	try
	{
	    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch (e)
	{
	    try
	    {
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	    }
	    catch (e)
	    {
		alert("Your browser does not support AJAX!");
	    }
	}
    }
    return xmlHttp;
}
function lt_parsels(resp, nomobj)
{
    var ii,kk,no,nr,sa,sb,resa;

    var cls = document.getElementById(nomobj);
    cls.length = 0;
    nr = resp.slice(resp.indexOf("{")+1,resp.indexOf("}"));
    resp = resp.slice(resp.indexOf("}")+1);
    if (nr > 0)
    {
    	resa = new Array();
    	for (ii = 0; ii < nr; ii++)
    	{
    		resa[ii] = new Array();
    		for (kk = 0; kk < 2; kk++)
    		{
    			resp = resp.slice(resp.indexOf('"')+1);
    			sa = resp.slice(0,resp.indexOf('"'));
    			resp = resp.slice(resp.indexOf('"')+1);
    			resa[ii][kk] = sa;
    		}
    	}
    	for (ii = 0; ii < nr; ii++)
    	{
    		var tmpopt = document.createElement('option');
    		tmpopt.value = resa[ii][0];
    		tmpopt.text = resa[ii][1];
    		try
    		{
    			cls.add(tmpopt, null);
    		}
    		catch (e)
    		{
    			cls.add(tmpopt);
    		}
    	}
    }
    return resp;
}
function lt_parserecord(apar,resa,nfields)
{
    var nr = 0;
    var ii,kk,sa,snr;

    snr = apar[0].slice(apar[0].indexOf("{")+1, apar[0].indexOf("}"));
    nr = Number(snr);
    apar[0] = apar[0].slice(apar[0].indexOf("}")+1);
    if (nr > 0)
    {
	for (ii = 0; ii < nr; ii++)
	{
	    resa[ii] = new Array(nfields);
	    for (kk = 0; kk < nfields; kk++)
	    {
		apar[0] = apar[0].slice(apar[0].indexOf('"')+1);
		sa = apar[0].slice(0, apar[0].indexOf('"'));
		apar[0] = apar[0].slice(apar[0].indexOf('"')+1);
		resa[ii][kk] = sa;
	    }
	}
    }
    else
    {
	if (nr == -1)
	{
	    resa[0] = new Array(1);
	    apar[0] = apar[0].slice(apar[0].indexOf('"')+1);
	    sa = apar[0].slice(0, apar[0].indexOf('"'));
	    apar[0] = apar[0].slice(apar[0].indexOf('"')+1);
	    resa[0][0] = sa;
	}
    }
    return nr;
}
function lt_vallocal_cid(elobj)
{
	var isok = false;
	if ($("nuevo").value == 1)
	{
		if (lt_novaciox(elobj))
		{
			var qq = "locales_valcid.php?local_cid="+elobj.value;
			var ajax = new Ajax.Request(qq, {asynchronous:false, method:'get'});
			var response = new Ajax.Response(ajax);
			if (ajax.success()) isok = true; else ltform_msg(response.responseJSON.msg, 10, 0);			
		}
	}
	else
	{
		isok = true;
	}
    return isok;
}
function lt_calcinicial(chkbounds)
{
    var precio = lt_numen(document.getElementById("precio").value);
    var inicial = lt_numen(document.getElementById("inicial").value);
    var inicial_porc = document.getElementById("inicial_porc").value;
    var inicial_min = precio * (inicial_porc/100);
    if (chkbounds)
    {
    	// (2009-02-25 CJ) if (inicial < inicial_min) { inicial = inicial_min; }
    	if (inicial <= 0) { inicial = inicial_min; }
    	if (inicial > precio) { inicial = inicial_min; }
    	document.getElementById("inicial").value = lt_numes(inicial);
    }
    else
    {
    	document.getElementById("inicial").value = lt_numes(inicial_min);
    }
}
function lt_calccuo_mon()
{
    var precio = lt_numen(document.getElementById("precio").value);
    var inicial = lt_numen(document.getElementById("inicial").value);
    var reserva = lt_numen(document.getElementById("reserva").value);
    var cuocant = Number(document.getElementById("cuo_cant").value);
    var giromonto = (inicial - reserva) / cuocant;

    document.getElementById("cuo_mon").value = lt_numes(giromonto);
}
function lt_calcres(bchk)
{
    var minicial = lt_numen(document.getElementById("inicial").value);
    var mreserva = lt_numen(document.getElementById("reserva").value);
    var reserva_porc = Number(document.getElementById("reserva_porc").value);
    var reserva_min = minicial * (reserva_porc/100);

    if (bchk)
    {
    	// (2009-02-25 CJ) if (mreserva < reserva_min || mreserva > minicial)
    	if (mreserva <= 0 || mreserva > minicial)
    	{
    		mreserva = reserva_min;
    	}
    	document.getElementById("reserva").value = lt_numes(mreserva);
    }
    else
    {
    	document.getElementById("reserva").value = lt_numes(reserva_min);
    }
}
function lt_localprecio(chklim)
{
    var precio,area,prxm2;
    area = lt_numen(document.getElementById("area").value);
    prxm2 = lt_numen(document.getElementById("prxm2").value);
    precio = prxm2 * area;
    document.getElementById("precio").value = lt_numes(precio);
    lt_calcinicial(chklim);
    lt_calcres(chklim);
    lt_calccuo_mon();
}
function lt_getprxm2(chkbounds)
{
    var isok = false;
    var clase = document.getElementById("clase").value;
    var q1 = "t0=locales_clases&c0=prxm2,inicial_porc,reserva_porc&k0=localclase_id&v0=" + clase;
    var xmlHttp = ajaxCreate();
    var apar = new Array();
    var resa = new Array();
    var prxm2 = 0;
    if (xmlHttp !== null)
    {
    	xmlHttp.open("GET","ltable_getaux.php?nq=1&"+q1,false);
    	xmlHttp.send(null);
    	if(xmlHttp.status == 200 || xmlHttp.status == 304)
    	{
    		apar[0] = xmlHttp.responseText;
    		lt_parserecord(apar,resa,3);
    		document.getElementById("prxm2_min").value = Number(resa[0][0]);
    		document.getElementById("inicial_porc").value = Number(resa[0][1]);
    		document.getElementById("reserva_porc").value = Number(resa[0][2]);
    		isok = true;
    	}
    }
    return isok;
}
function lt_getpry_lcnfo() // retrieve some defaults values about locales from proyectos
{
	var isok = false;
	var proyecto_id = document.getElementById("proyecto_id").value;
	var q1 = "t0=proyectos&c0=inicial_porc,reserva_porc,mora_porc,mora_lapso&k0=proyecto_id&v0=" + proyecto_id;
	var xmlHttp = ajaxCreate();
	var apar = new Array();
	var resa = new Array();
	var prxm2 = 0;
	if (xmlHttp !== null)
	{
		xmlHttp.open("GET","ltable_getaux.php?nq=1&"+q1,false);
		xmlHttp.send(null);
		if(xmlHttp.status == 200 || xmlHttp.status == 304)
		{
			apar[0] = xmlHttp.responseText;
			lt_parserecord(apar,resa,4);
			document.getElementById("inicial_porc").value = Number(resa[0][0]);
			document.getElementById("reserva_porc").value = Number(resa[0][1]);
			document.getElementById("mora_porc").value = Number(resa[0][2]);
			document.getElementById("mora_lapso").value = Number(resa[0][3]);
			isok = true;
		}
	}
    return isok;
}
function lt_getcup() // clases, ubicaciones, pisos
{
    var isok = false;
    var pryid = document.getElementById("proyecto_id").value;
    var q1 = "t0=locales_clases&c0=localclase_id,descripcion&k0=proyecto_id&v0=" + pryid;
    var q2 = "t1=locales_pisos&c1=localpiso_id,descripcion&k1=proyecto_id&v1=" + pryid;
    var q3 = "t2=locales_ubicaciones&c2=localubica_id,descripcion&k2=proyecto_id&v2=" + pryid;
    var xmlHttp = ajaxCreate();
    if (xmlHttp !== null)
    {
    	xmlHttp.open("GET","ltable_getaux.php?nq=3&"+q1+"&"+q2+"&"+q3,false);
    	xmlHttp.send(null);
    	if(xmlHttp.status == 200 || xmlHttp.status == 304)
    	{
    		var resp = xmlHttp.responseText;
    		var oldclase = document.getElementById("clase").value;
    		resp = lt_parsels(resp, "clase");
    		resp = lt_parsels(resp, "piso");
    		lt_parsels(resp, "ubicacion");
    		document.getElementById("clase").value = oldclase;
    		isok = true;
    	}
    }
    return isok;
}
function lt_calcprxm2(chkbounds)
{
    var prxm2_min = document.getElementById("prxm2_min").value;
    var prxm2 = lt_numen(document.getElementById("prxm2").value);
    var bchk = false;

//    BEGIN 20080523
//    if (chkbounds)
//    {
//	if (prxm2 < prxm2_min)
//	{
//	    prxm2 = prxm2_min;
//	}
//   }
//    else
//    {
//	prxm2 = prxm2_min;
//    }
// END 20080523
    document.getElementById("prxm2").value = lt_numes(prxm2);
    if (document.getElementById("validando").value == 1) bchk = true;
    lt_localprecio(bchk);
}
function lt_getrespry() // locales del proyecto (no reservados)
{
    var isok = false;
    var proyecto_id = document.getElementById("proyecto_id").value;
    var nuevo = document.getElementById("nuevo").value;
    var q1 = "SELECT+locales.local_id,local_cid+FROM+locales,local_estado+WHERE+proyecto_id=" + proyecto_id;
    q1 = q1 + "+AND+local_estado.local_id=locales.local_id+AND+local_estado.localstatus_id=1";
    q1 = q1 + "+AND+locales.local_id+NOT+IN+(SELECT+local_id+FROM+reservaciones)";
    var xmlHttp = ajaxCreate();
    if (xmlHttp !== null)
    {
    	if (nuevo == 0)
    	{
    		var oldlocal_id = document.getElementById("oldlocal_id").value;
    		q1 = "SELECT+local_id,local_cid+FROM+locales+WHERE+proyecto_id=" + proyecto_id + "+AND+local_id+NOT+IN+(SELECT+local_id+FROM+reservaciones WHERE local_id!=" + oldlocal_id + ")";
    	}
    	xmlHttp.open("GET","ltable_rquery.php?nq=1&q0="+q1,false);
    	xmlHttp.send(null);
    	if(xmlHttp.status == 200 || xmlHttp.status == 304)
    	{
    		var resp = xmlHttp.responseText;
    		var nuevo = document.getElementById("nuevo").value;
    		lt_parsels(resp, "local_id");
    		isok = true;
    	}
    }
    return isok;
}

function lt_getlocal() // datos del local 
{
	var isok = false;
	var local_id = Number(document.getElementById("local_id").value);
	var q1 = "t0=locales&c0=inicial,reserva,precio,cuo_mon,cuo_cant,local_cid&k0=local_id&v0=" + local_id;
	var xmlHttp = ajaxCreate();
	var apar = new Array();
	var resa = new Array();
	var prxm2 = 0;
	if (xmlHttp !== null)
	{
		xmlHttp.open("GET","ltable_getaux.php?nq=1&"+q1,false);
		xmlHttp.send(null);
		if(xmlHttp.status == 200 || xmlHttp.status == 304)
		{
			apar[0] = xmlHttp.responseText;
			nr = lt_parserecord(apar,resa,6);
			if (nr != -1)
			{
				document.getElementById("inicial").value = lt_numes(resa[0][0]);
				document.getElementById("reserva").value = lt_numes(resa[0][1]);
				document.getElementById("precio").value = lt_numes(resa[0][2]);
				document.getElementById("cuo_mon").value = lt_numes(resa[0][3]);
				document.getElementById("cuo_cant").value = Number(resa[0][4]);
				document.getElementById("local_cid").value = resa[0][5];					
			}
			else
			{
				alert("Error: " + resa[0][0]);
			}
			isok = true;
		}
	}
	return isok;
}

function lt_calcgiros3()
{
    var reserva = lt_numen(document.getElementById("reserva").value);
    var inicial = lt_numen(document.getElementById("inicial").value);
    var cuo_cant = lt_numen(document.getElementById("cuo_cant").value);
    var suma = lt_numen(document.getElementById("sumapagos").value);
    ///if (suma > reserva) reserva = suma;
    var cuobase = inicial - reserva;
    var cuomon = cuobase / cuo_cant;
    document.getElementById("cuo_mon").value = lt_numes(cuomon);
}

function lt_reschq_reset()
{
    document.getElementById("cheque_mon0").value = document.getElementById("reserva").value;
    document.getElementById("cheque_mon1").value = 0;
    document.getElementById("cheque_mon2").value = 0;
    document.getElementById("deposito_mon0").value = 0;
    document.getElementById("deposito_mon1").value = 0;
    document.getElementById("deposito_mon2").value = 0;
}

function lt_initrespry(elobj)
{
    var lsloc = document.getElementById("local_id");
    document.getElementById("oldlocal_id").value = document.getElementById("local_id").value;
    if (document.getElementById("nuevo").value == 0)
    {
	if (lt_getrespry())
	{
	    lsloc.value = document.getElementById("oldlocal_id").value;
	    lt_getlocal();
	    ///lt_calcgiros_exce();
	}
    }
    else
    {
	if (lt_getrespry())
	{
	    lt_getlocal();
	}
	lt_reschq_reset();
	document.getElementById("cliente_id").focus();
    }
    return true;
}
function lt_valrespry(elobk)
{
    var isok = false;
    if (lt_getrespry())
    {
	lt_getlocal();
	isok = true;
    }
    return isok;
}
function lt_valreslocal(elobj)
{
    var isok = false;

    if (lt_getlocal())
    {
	lt_reschq_reset();
	isok = true;
    }
    return isok;
}
function lt_valreschq(elobj,force)
{
    var isok = false;
    var re = /^[0-9]{8}$/;

    if (lt_vacio(elobj))
    {
    	if (force)
    	{
    		alertx(elobj, "Numero de cheque en blanco");
    		//elobj.focus();
    	}
    	else
    	{
    		isok = true;
    	}
    }
    else
    {
    	isok = re.test(elobj.value);
    	if (!isok)
    	{
    		alertx(elobj, "Numero de cheque debe tener exactamente 8 digitos");
    		//elobj.focus();
    	}
    }

    return isok;
}
function lt_calcgiros_exce()
{
    var reserva = lt_numen(document.getElementById("reserva").value);
    var inicial = lt_numen(document.getElementById("inicial").value);
    var cuo_cant = lt_numen(document.getElementById("cuo_cant").value);
    var mon0 = lt_numen(document.getElementById("cheque_mon0").value);
    var mon1 = lt_numen(document.getElementById("cheque_mon1").value);
    var mon2 = lt_numen(document.getElementById("cheque_mon2").value);
    var mon3 = lt_numen(document.getElementById("deposito_mon0").value);
    var mon4 = lt_numen(document.getElementById("deposito_mon1").value);
    var mon5 = lt_numen(document.getElementById("deposito_mon2").value);
    var suma = mon0 + mon1 + mon2 + mon3 + mon4 + mon5;
    ///if (suma > reserva) reserva = suma; 2008-05-21 sustituido por NC/ND
    var cuobase = inicial - reserva;
    var cuomon = cuobase / cuo_cant;
    document.getElementById("cuo_mon").value = lt_numes(cuomon);
    //alert("CUO_MON="+cuomon);
}

function lt_valresccc(elobj, chqid)
{
	var isok = false;
	var omon = document.getElementById("cheque_mon" + chqid);
	var monto = lt_numen(omon.value);
	
	if (monto > 0)
	{
		isok = lt_digitos(elobj, 20);
	}
	else
	{
		isok = true;
	}
	
	return isok;
}

function lt_valreschqmon(elobj,chqid)
{
    var isok = false;
    var re = /^[0-9]{8}$/;
    var monto = 0;
    var chq_num = document.getElementById("cheque_no"+chqid);
    var reserva = lt_numen(document.getElementById("reserva").value);
    var omon0 = document.getElementById("cheque_mon0");
    var omon1 = document.getElementById("cheque_mon1");
    var omon2 = document.getElementById("cheque_mon2");
    var omon3 = document.getElementById("deposito_mon0");
    var omon4 = document.getElementById("deposito_mon1");
    var omon5 = document.getElementById("deposito_mon2");
    var suma = 0;
    var dif = 0;

    ///if (lt_numerico(elobj,7,2,0,reserva))
    if (lt_numerico(elobj,7,2,0,9999999))
    {
    	monto = lt_numen(elobj.value);
    	if (monto > 0)
    	{
    		isok = lt_valreschq(chq_num,true);
    		if (monto < reserva)
    		{
    			if (chqid==0)
    			{
    				dif = reserva - monto;
    				//if (lt_numen(omon1.value) == 0) omon1.value = lt_numes(dif);
    			}
    			if (chqid==1)
    			{
    				dif = reserva - (lt_numen(omon0.value) + monto);
    				if (dif < 0) dif = 0;
    				//if (lt_numen(omon2.value) == 0) omon2.value = lt_numes(dif);
    			}
    			if (chqid==2)
    			{
    				dif = reserva - (lt_numen(omon0.value) + lt_numen(omon1.value) + monto);
    				if (dif < 0) dif = 0;
    				//if (lt_numen(omon3.value) == 0) omon3.value = lt_numes(dif);
    			}
    		}
    		///lt_calcgiros_exce();
    	}
    	else
    	{
    		if (lt_vacio(chq_num))
    		{
    			isok = true;
    		}
    		else
    		{
    			alertx(elobj, "Numero de cheque no esta en blanco, introduzca un monto valido para este cheque.");
    			//elobj.focus();
    		}
    	}
    }
    return isok;
}
function lt_valresdep(elobj,force)
{
    var isok = false;
    var ocheque = document.getElementById("cheque_no0");
    var re = /^[0-9]{12}$/;

    if (lt_vacio(elobj))
    {
    	if (force)
    	{
    		alertx(elobj, "Numero de deposito en blanco");
    		//elobj.focus();
    	}
    	else
    	{
    		isok = true;
    		///isok = lt_valreschq(ocheque, true);
    	}
    }
    else
    {
    	isok = re.test(elobj.value);
    	if (!isok)
    	{
    		alertx(elobj, "Numero de deposito debe tener exactamente 12 digitos");
    		//elobj.focus();
    	}
    }

    return isok;
}

function lt_valresdepmon(elobj,depid)
{
    var isok = false;
    var mon0 = lt_numen(document.getElementById("cheque_mon0").value);
    var mon1 = lt_numen(document.getElementById("cheque_mon1").value);
    var mon2 = lt_numen(document.getElementById("cheque_mon2").value);
    var mon3 = 0;
    var suma = 0;
    var reserva = lt_numen(document.getElementById("reserva").value);

    var deponom = "deposito_no" + depid;

    // 26/03/2008: permitir reserva excedente
    //if (lt_numerico(elobj,7,2,0,reserva))
    if (lt_numerico(elobj,7,2,0,9999999))
    {
    	mon3 = lt_numen(elobj.value);
    	if (mon3 > 0)
    	{
    		isok = lt_valresdep(document.getElementById(deponom), true);
    	}
    	else
    	{
    		if (lt_vacio(document.getElementById(deponom)))
    		{
    			isok = true;
    		}
    		else
    		{
    			alertx(elobj, "Numero de deposito no esta en blanco, introduzca un monto valido para este deposito.");
    			//elobj.focus();
    		}
    	}
    	if (isok)
    	{
    		var dep0 = lt_numen(document.getElementById("deposito_mon0").value);
    		var dep1 = lt_numen(document.getElementById("deposito_mon1").value);
    		var dep2 = lt_numen(document.getElementById("deposito_mon2").value);

    		suma = mon0 + mon1 + mon2 + dep0 + dep1 + dep2;
    		if (suma < reserva)
    		{
    			// 26/03/2008: Modificado para implementar apartados
    			// isok = false;
    			// elobj.focus();

    			if (depid == 2)
    			{
    				alert("Suma de los pagos es menor al monto de la reserva.\nPagos = "+suma+"\nReserva = "+reserva);
    			}
    		}
    		///lt_calcgiros_exce();
    	}
    }
    return isok;
}

function lt_initpry_lcnfo(elobj)
{
    if (document.getElementById("nuevo").value == 1)
    {
	lt_getpry_lcnfo();
    }
}
function lt_valpryid(elobj)
{
    var isok = false;
    if (lt_getcup())
    {
	if (lt_getprxm2())
	{
	    lt_calcprxm2(false);
	    isok = true;
	}
    }
    return isok;
}
function lt_initpryid(elobj)
{
    if (lt_getprxm2())
    {
	if (document.getElementById("nuevo").value == 1)    
	{
	    lt_calcprxm2(false);
	}
	else
	{
	    document.getElementById("local_cid").readOnly = true;
	}
    }
}
function lt_valprxm2(elobj)
{
    var isok = false;
    if (lt_numerico(elobj,5,2,0,999999))
    {
    	if (document.getElementById("validando").value == 1) lt_calcprxm2(true); else lt_calcprxm2(false);
    	isok = true;
    }
    return isok;
}
function lt_vallocclase(elobj)
{
    var isok = false;
    if (lt_getprxm2())
    {
	lt_calcprxm2(false);
	isok = true;
    }
    return isok;
}
function lt_valinicial(elobj)
{
    var isok = false;
    if (lt_numerico(elobj,7,2,0,99999999))
    {
	lt_calcinicial(true);
	if (document.getElementById("validando").value == 1) lt_calcres(true); else lt_calcres(false);
	lt_calccuo_mon();
	isok = true;
    }
    return isok;
}
function lt_valreserva(elobj)
{
    var isok = false;
    if (lt_numerico(elobj,7,2,0,99999999))
    {
	lt_calcres(true);
	lt_calccuo_mon();
	isok = true;
    }
    return isok;
}
function lt_valarea(elobj)
{
	var isok = false;
	if (lt_numerico(elobj,3,3,0,999))
	{
		if (document.getElementById("validando").value == 1) lt_localprecio(true); else lt_localprecio(false);
		isok = true;
	}
	return isok;
}
function lt_valcuocant(elobj)
{
    var isok = false;
    if (lt_entero(elobj,2,1,24))
    {
	lt_calccuo_mon();
	isok = true;
    }
    return isok;
}
function lt_ctod(cfecha)
{
    var tmpfe = new Array(3);
    var dt = new Date();
    tmpfe[0] = Number(cfecha.substring(0,2));
    tmpfe[1] = Number(cfecha.substring(3,2));
    tmpfe[2] = Number(cfecha.substring(6,4));
    if (tmpfe[0] < 1 || tmpfe[0] > 31)
    {
	tmpfe[0] = dt.getDate();
    }
    if (tmpfe[1] < 1 || tmpfe[1] > 12)
    {
	tmpfe[1] = dt.getMonth();
    }
    if (tmpfe[2] < 1)
    {
	tmpfe[2] = dt.getFullYear();
    }
    else
    {
	if (tmpfe[2] < 99)
	{
	    tmpfe[2] += 2000;
	}
    }
    return tmpfe;
}
function lt_contar(q1)
{
    var cuenta = 0;
    var xmlHttp = ajaxCreate();
    if (xmlHttp !== null)
    {
    	xmlHttp.open("GET","ltable_rquery.php?nq=1&q0=" + encodeURI(q1), false);
    	xmlHttp.send(null);
    	if(xmlHttp.status == 200 || xmlHttp.status == 304)
    	{
    		var apar = new Array();
    		var resa = new Array();
    		apar[0] = xmlHttp.responseText;
    		nr = lt_parserecord(apar,resa,1);
    		if (nr > 0)
    		{
    			cuenta = Number(resa[0][0]);
    		}
    		else
    		{
    			alert("Error: " + resa[0][0]);
    		}
    	}
    }
    return cuenta;
}
function lt_valclici(theobj)
{
    var isok = false;
    var qq = '';
    var cliid = document.getElementById('cliente_id').value;

    if (lt_entero(theobj,8,1,99999999))
    {
    	if (document.getElementById('nuevo') == 0)
    	{
    		qq = "SELECT COUNT(*) FROM clientes WHERE ci0=" + theobj.value;
    	}
    	else
    	{
    		qq = "SELECT COUNT(*) FROM clientes WHERE ci0=" + theobj.value + " AND cliente_id!=" + cliid;
    	}

    	if (lt_contar(qq) == 0)
    	{
    		isok = true;
    	}
    	else
    	{
    		if (confirm("Numero de cedula repetido. Debo usarlo?"))
    		{
    			isok = true;
    		}
    		else
    		{
    			setTimeout("enfocar('" + theobj.name + "')", 50);
    			theobj.value = '0';
    			theobj.select();
    		}
    	}
    }
    return isok;
}
function lt_esjuridico(theobj)
{
    var isok = true;
    var esju = false;
    if (document.getElementById("persona").value == 'j') esju = true;
    if (esju)
    {
	var nc = theobj.id;
	if (nc == 'razon' || nc == 'repre' || nc == 'reg_cargo' || nc == 'reg_tal' || nc == 'reg_tomo')
	{
	    isok = lt_numletra(theobj);
	}
	if (nc == 'rif')
	{
		isok = lt_rif(theobj);
	}
	if (nc == 'repreci')
	{
	    isok = lt_entero(theobj,8,1,99999999);
	}
	if (nc == 'reg_numero')
	{
	    isok = lt_entero(theobj,3,1,999);
	}
	if (nc == 'reg_fecha')
	{
	    isok = lt_chkfecha(theobj);
	}
    }
    return isok;
}
function lt_essociedad(theobj,socioid)
{
    var isok = true;

    if (document.getElementById("sociedad").checked)
    {
    	if (document.getElementById("activa" + socioid).checked)
    	{
    		if (theobj.id == "nombres" + socioid) isok = lt_forbidden(theobj);
    		if (theobj.id == "apellidos" + socioid) isok = lt_forbidden(theobj);
    		if (theobj.id == "ci" + socioid) isok = lt_entero(theobj,8,1,99999999);
    		if (theobj.id == "rif" + socioid) isok = lt_rif(theobj);
    	}
    }

    return isok;
}
function lt_cliperset(bflag)
{
    document.getElementById("razon").disabled = bflag;
    document.getElementById("rif").disabled = bflag;
    document.getElementById("repre").disabled = bflag;
    document.getElementById("repreci").disabled = bflag;
    document.getElementById("reg_cargo").disabled = bflag;
    document.getElementById("reg_tal").disabled = bflag;
    document.getElementById("reg_fecha").disabled = bflag;
    document.getElementById("reg_estado").disabled = bflag;
    document.getElementById("reg_numero").disabled = bflag;
    document.getElementById("reg_tomo").disabled = bflag;
}
function lt_cliper(theobj)
{
    var otb = document.getElementById("persona");
   
    if (otb.value == 'j')
    {
	lt_cliperset(false);
    }
    else
    {
	lt_cliperset(true);
    }
    return true;
}
function lt_clacc(elobj,accid)
{
//    var theobj = document.getElementById('acc' + accid);
    var desactivar = !elobj.checked;

    document.getElementById('acc_b' + accid).disabled = desactivar;
    document.getElementById('acc_tp' + accid).disabled = desactivar;
    document.getElementById('acc_t' + accid).disabled = desactivar;
    document.getElementById('acc_ut' + accid).disabled = desactivar;

    return true;
}
function lt_execfn(theobj,fnstr)
{
    var fns = fnstr.toUpperCase();
    if (fns.indexOf("U") != -1) theobj.value = theobj.value.toUpperCase();
    if (fns.indexOf("L") != -1) theobj.value = theobj.value.toLowerCase();
    if (fns.indexOf("K") != -1) theobj.select();
    return true;
}

function disableEnterKey(e)
{
    var key;
    if (window.event) key = window.event.keyCode; else key = e.which;
    if (key == 13) return false; else return true;
}
function ltrpt_up(oup,nop,tpop)
{
    ///var oup = document.getElementById("up"+nop);
    var bdis = true;
    var cn;
    bdis = !oup.checked;
    cn = oup.alt;
    document.getElementById("lp"+nop).disabled = bdis;
    document.getElementById(cn).disabled = bdis;
    if (tpop != 'l') document.getElementById("op"+nop).disabled = bdis;
}
function ltrpt_movopt(nmls1,nmls2)
{
    var ls1 = document.getElementById(nmls1);
    var ls2 = document.getElementById(nmls2);
    var ii;

    for (ii = 0; ii < ls1.length; ii++)
    {
	if (ls1.options[ii].selected)
	{
	    var tmpopt = document.createElement('option');
	    tmpopt.text = ls1.options[ii].text;
	    tmpopt.value = ls1.options[ii].value;
	    ls2.add(tmpopt, 0);
	}
    }
    for (ii = 0; ii < ls1.length; ii++)
    {
	if (ls1.options[ii].selected)
	{
	    ls1.remove(ii);
	}
    }
    if (ls1.length > 0) ls1.options[0].selected = true;
}
function ltrpt_selall(nmls2)
{
    var ii;
    var ls2 = document.getElementById(nmls2);
    var nord = 0;

    for (ii = 0; ii < ls2.length; ii++)
    {
	ls2.options[ii].selected = true;
	document.getElementById("chko" + ii).disabled = false;
    }

    document.all.ltrptordc.value = nord;
}
function ltrpt_oadd()
{
    ltrpt_movopt('ltrptodi','ltrptoes');
    ltrpt_selall('ltrptoes');
}
function ltrpt_odel()
{
    ltrpt_movopt('ltrptoes','ltrptodi');
    ltrpt_selall('ltrptoes');
}

function ltrpt_orddel(r, fln, fltit)
{
    var tbl1 = document.getElementById('rpttbldisp');
    var tbl2 = document.getElementById('rpttblord');
    var ii,oo;

    var newRow = tbl1.insertRow(1);
    var ss = "<td>" + fltit + "</td>";
    ss = ss + "<td align=\"center\">";
    ss = ss + "<input type=\"button\" value=\">>\" onclick=\"ltrpt_ordadd(this,'" + fln + "','" + fltit + "');\"></td>";
    newRow.innerHTML = ss;

    ii = r.parentNode.parentNode.rowIndex;
    tbl2.deleteRow(ii);
}

function ltrpt_ordadd(r, fln, fltit)
{
    var tbl1 = document.getElementById('rpttbldisp');
    var tbl2 = document.getElementById('rpttblord');
    var ordo = document.getElementById('ordsz');
    var ordsz = ordo.value;
    var chknm = "ordinv" + ordsz;
    var ii;

    var newRow = tbl2.insertRow(1);
    var ss = "<td align=\"center\">";
    ss = ss + "<input type=\"hidden\" name=\"ord" + ordsz + "\" value=\"" + fln + "\">";
    ss = ss + "<input type=\"button\" value=\"<<\" onclick=\"ltrpt_orddel(this,'" + fln + "','" + fltit + "');\">";
    ss = ss + "</td><td>" + fltit + "</td>";
    ss = ss + '<td align="center"><input type="checkbox" name="'+chknm+'" id="'+chknm+'"></td>';
    newRow.innerHTML = ss;

    ordsz++;
    ordo.value = ordsz;

    ii = r.parentNode.parentNode.rowIndex;
    tbl1.deleteRow(ii);
} 


function readCookie(name)
{
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++)
    {
	var c = ca[i];
	while (c.charAt(0)==' ') c = c.substring(1,c.length);
	if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}


function lt_lcinfo(elobj, prnver)
{
    var cid = elobj.value;
    if (lt_novaciox(elobj))
    {
    	var pid = $("proyecto_id").value;
    	var qq = encodeURI("local_info_do.php?cid=" + cid + "&pid=" + pid + 
    			"&prnver=" + prnver);
    	if (prnver == 0)
    	{
    		var xmlHttp = ajaxCreate();
    		if (xmlHttp !== null)
    		{
    			xmlHttp.open("GET", qq, false);
    			xmlHttp.send(null);
    			if (xmlHttp.status == 200 || xmlHttp.status == 304)
    			{
    				$("divlcinfo").innerHTML = xmlHttp.responseText;
    			}
    		}
    	}
    	else
    	{
    		window.open(qq);
    	}
    }
    
    return true;
}

function lcdispo_enable(elchk)
{
    var cual = elchk.name;
    var flag = !elchk.checked;

    if (cual == 'chkpiso') document.getElementById('lspiso').disabled = flag;
    if (cual == 'chkubica') document.getElementById('lsubica').disabled = flag;
    if (cual == 'chkarea') document.getElementById('lsarea').disabled = flag;
    if (cual == 'chkprxm2') document.getElementById('lsprxm2').disabled = flag;
}

function lt_buscar_cedula(elobj)
{
	var isok = true;
	var rr = 0;
	var validando = document.getElementById("validando").value;
	var enblk = lt_vacio(elobj);

	if (validando == 0 && !enblk)
	{
		var cedula = elobj.value;
		var q1 = "t0=clientes&c0=cliente_id&k0=ci0&v0=" + cedula;
		var xmlHttp = ajaxCreate();
		var apar = new Array();
		var resa = new Array();

		if (xmlHttp !== null)
		{
			xmlHttp.open("GET","ltable_getaux.php?nq=1&"+q1,false);
			xmlHttp.send(null);
			if(xmlHttp.status == 200 || xmlHttp.status == 304)
			{
				apar[0] = xmlHttp.responseText;
				rr = lt_parserecord(apar,resa,1)
				if (rr != -1)
				{
					if (rr != 0)
					{
						document.getElementById("cliente_id").value = Number(resa[0][0]);
					}
					else
					{
						alert("Cedula no encontrada");
					}
				}
				else
				{
					alert("Error: " + resa[0][0]);
				}
			}
		}
	}
	return isok;
}

function lt_highlightrow(rowname,ochk)
{
    var orow = document.getElementById(rowname);

    if (ochk.checked)
    {
	orow.bgColor = "#776677";
	orow.style.color = "#ffffff";
    }
    else
    {
	orow.bgColor = "#ffffff";
	orow.style.color = "#000000";
    }
}

function lt_lb_fill(olb, sqlexpr)
{
	var isok = false;
	var rr = 0, ii = 0, lc = 0;
	var apar = new Array();
	var resa = new Array();

	lc = olb.length;
	for (ii = 0; ii < lc; ii++)
	{
		olb.remove(ii);
	}
	var q1 = encodeURI(sqlexpr);

	var xmlHttp = ajaxCreate();
	if (xmlHttp !== null)
	{
		xmlHttp.open("GET","ltable_rquery.php?nq=1&q0=" + q1,false);
		xmlHttp.send(null);
		if (xmlHttp.status == 200 || xmlHttp.status == 304)
		{
			apar[0] = xmlHttp.responseText;
			rr = lt_parsels(xmlHttp.responseText, olb.name);			
		}
	}
	return isok;	
}

function reservacion_saldo(resid, elobj)
{
	var isok = false;
	var rr = 0;
	var apar = new Array();
	var resa = new Array();
	var elsaldo = 0;
	var docno = sprintf("RS%010d", resid);

	var q1 = "SELECT SUM(doc_monto) FROM cxc WHERE doc_no='" + docno + "'";
	q1 = encodeURI(q1);

	var xmlHttp = ajaxCreate();
	if (xmlHttp !== null)
	{
		xmlHttp.open("GET","ltable_rquery.php?nq=1&q0=" + q1, false);
		xmlHttp.send(null);
		if(xmlHttp.status == 200 || xmlHttp.status == 304)
		{
			apar[0] = xmlHttp.responseText;
			rr = lt_parserecord(apar,resa,1);
			if (rr != -1)
			{
				if (rr != 0)
				{
					elsaldo += Number(resa[0][0]);
					isok = true;
				}
			}
			else
			{
				alert("Error: " + resa[0][0]);
			}
		}
	}
	
	q1 = "SELECT SUM(doc_monto) FROM cxc WHERE aplicar_a='" + docno + "'";
	q1 = encodeURI(q1);

	var xmlHttp2 = ajaxCreate();
	if (xmlHttp2 !== null)
	{
		xmlHttp2.open("GET","ltable_rquery.php?nq=1&q0=" + q1, false);
		xmlHttp2.send(null);
		if(xmlHttp.status == 200 || xmlHttp.status == 304)
		{
			apar[0] = xmlHttp2.responseText;
			rr = lt_parserecord(apar,resa,1);
			if (rr != -1)
			{
				if (rr != 0)
				{
					elsaldo += Number(resa[0][0]);
					isok = true;
				}
			}
			else
			{
				alert("Error: " + resa[0][0]);
			}
		}
	}

	lt_setmonto(elobj, elsaldo);
	
	return isok;
}

function local_saldo(local_id, cliente_id, elobj)
{
	var isok = false;
	var rr = 0;
	var apar = new Array();
	var resa = new Array();

	var q1 = "SELECT SUM(doc_monto) FROM cxc WHERE local_id=" + local_id + 
		" AND cliente_id=" + cliente_id;
	q1 = encodeURI(q1);

	var xmlHttp = ajaxCreate();
	if (xmlHttp !== null)
	{
		xmlHttp.open("GET","ltable_rquery.php?nq=1&q0=" + q1, false);
		xmlHttp.send(null);
		if(xmlHttp.status == 200 || xmlHttp.status == 304)
		{
			apar[0] = xmlHttp.responseText;
			rr = lt_parserecord(apar,resa,1);
			if (rr != -1)
			{
				if (rr != 0)
				{
					lt_setmonto(elobj, Number(resa[0][0]));
					isok = true;
				}
			}
			else
			{
				alert("Error: " + resa[0][0]);
			}
		}
	}
	
	return isok;
}

function lt_setmonto(elobj, elmonto)
{
	elobj.value = lt_numes(elmonto);
	if (elmonto < 0) elobj.style.color = "#ff0000";
	else elobj.style.color = "#000000";	
}

function local_pagos(local_id, cliente_id)
{
	var elsaldo = 0;
	var rr = 0;
	var apar = new Array();
	var resa = new Array();

	var q1 = "SELECT SUM(doc_monto) FROM cxc WHERE local_id=" + local_id + 
		" AND cliente_id=" + cliente_id + 
		" AND FIND_IN_SET(doc_tipo, 'CH,DV,DP,TC,TD,TR')!=0";
	q1 = encodeURI(q1);

	var xmlHttp = ajaxCreate();
	if (xmlHttp !== null)
	{
		xmlHttp.open("GET","ltable_rquery.php?nq=1&q0=" + q1, false);
		xmlHttp.send(null);
		if(xmlHttp.status == 200 || xmlHttp.status == 304)
		{
			apar[0] = xmlHttp.responseText;
			rr = lt_parserecord(apar,resa,1);
			if (rr != -1)
			{
				if (rr != 0)
				{
					elsaldo = Number(resa[0][0]);
				}
			}
			else
			{
				alert("Error: " + resa[0][0]);
			}
		}
	}
	
	return elsaldo;
}

function xopos_abriendo()
{
	document.xoposmicr.Open("micr1");
	document.xoposmicr.ClaimDevice(1000);
	document.xoposmicr.DeviceEnabled = true;
	document.xoposmicr.DataEventEnabled = true;
}

function xopos_cerrando()
{
	document.xoposmicr.DeviceEnabled = false;
	document.xoposmicr.ReleaseDevice();
	document.xoposmicr.Close();
}

function xopos_insertar(lalinea)
{
	document.getElementById("nln").value = lalinea;
	document.xoposmicr.BeginInsertion(0);
	document.xoposmicr.EndInsertion();
}

function xopos_insertar2(elobj, lalinea)
{
	document.getElementById("nln").value = lalinea;
	document.xoposmicr.BeginInsertion(0);
	document.xoposmicr.EndInsertion();
}

function xopos_remover()
{
	document.xoposmicr.BeginRemoval(0);
}

function pagos_codcuatro(elobj, codcuatro)
{
	var nel = elobj.length;
	var ndx = -1, ii = 0;
	var cods = "0000";
	var isfound = false;
	
	for (ii = 0; ii < nel; ii++)
	{
		cods = elobj.options[ii].text.substr(1, 4);
		if (cods == codcuatro)
		{
			elobj.selectedIndex = ii;
			isfound = true;
			break;
		}
	}
	
	///if (!isfound)
	///{
	///	alertx(origen, "Codigo de banco no encontrado");
	///}
	
	return isfound;
}

function lt_tarjeta(otb)
{
    var isok = false;
    var re = new RegExp("^[0-9]{13}$");

    isok = re.test(otb.value);
    if (!isok)
    {
    	alertx(otb, "Deben ser 13 digitos numericos: LOS ULTIMOS 4 DIGITOS "+
    		"DE LA TARJETA, LUEGO LOS 6 DIGITOS DE LA APROBACION Y LUEGO LOS 3 DIGITOS DEL LOTE");
    }
    return isok;
}

function lt_highlight(elobj, elcolor)
{
	elobj.style.color = elcolor;
	elobj.style.fontWeight = "bold";
	elobj.select();
}

function lt_downlight(elobj, elcolor)
{
	elobj.style.color = elcolor;
	elobj.style.fontWeight = "";
}

function ltpry_choose(elobj)
{
	var qq = "proyecto_chooser_up.php?newpid=" + elobj.value;
    new Ajax.Request(qq,
	{
    	method: 'get',
    	onSuccess: function(request)
    	{
			document.location.reload();
    	},
    	onFailure: function(request)
    	{
    		ltform_msg(request.responseJSON.msg, 10, 0);
    	}
	});	
    return true;
}

function lttnd_choose(elobj)
{
	var qq = "tienda_chooser_up.php?newtid=" + elobj.value;
    new Ajax.Request(qq,
	{
    	method: 'get',
    	onSuccess: function(request)
    	{
			document.location.reload();
    	},
    	onFailure: function(request)
    	{
    		ltform_msg(request.responseJSON.msg, 10, 0);
    	}
	});	
    return true;
}

function rpt_lcid(elobj)
{
	var ndx = elobj.selectedIndex;
	document.getElementById('local_cid').value = elobj.options[ndx].text;
}

function rpt_lid(elobj)
{
	var lista = document.getElementById('local_id');
	var maxndx = lista.length;
	var ndx = 0;
	elobj.value = elobj.value.toUpperCase();
	for (ndx = 0; ndx < maxndx; ndx++)
	{
		if (lista.options[ndx].text == elobj.value)
		{ 
			lista.selectedIndex = ndx;
			elobj.focus();
			break;
		}
	}
}

function rpt_lidx(elobj, listaname, nextname)
{
	var isok = false;
	var lista = $(listaname);
	var maxndx = lista.length;
	var ndx = 0;
	elobj.value = elobj.value.toUpperCase();
	for (ndx = 0; ndx < maxndx; ndx++)
	{
		if (lista.options[ndx].text == elobj.value)
		{ 
			lista.selectedIndex = ndx;
			$(nextname).focus();
			isok = true;
			break;
		}
	}
	return isok;
}

function rpt_klidx(elobj, e, listaname, nextname)
{
    var key;
    var isok = true;
    if (window.event) key = window.event.keyCode; else key = e.which;
    if (key == 13)
    {
    	if (rpt_lidx(elobj, listaname, nextname))
    	{
    		$(nextname).focus();
    	}
    	else isok = false;
	}
	return isok;
}

function lt_theme_change_pp(bRe, oRe)
{
	if (bRe) document.location.reload(); else ltform_msg(oRe.msg, 10, 0);
}

function lt_theme_change(elobj)
{
	var tema = $("nvotema").value;
	var pet = "ltable_theme_up.php?nvotema=" + tema;
	lt_ajaxx(pet, lt_theme_change_pp);
}

function lt_spinner_up(sTxt, nMax, nMin, nStep, cTipo)
{
	var oTxt = document.getElementById(sTxt);
	var nVal = lt_numen(oTxt.value);
	
	nVal += nStep;
	if (nVal > nMax) nVal = nMax;
	if (nVal < nMin) nVal = nMin;
	
	if (cTipo == "n") oTxt.value = lt_numes(nVal);
	else oTxt.value = nVal;
}

function lt_spinner_down(sTxt, nMax, nMin, nStep, cTipo)
{
	var oTxt = document.getElementById(sTxt);
	var nVal = lt_numen(oTxt.value);
	
	nVal -= nStep;
	if (nVal < nMin) nVal = nMin;
	if (nVal > nMax) nVal = nMax;
	
	if (cTipo == "n") oTxt.value = lt_numes(nVal);
	else oTxt.value = nVal;
}

function favoritas_add()
{
	var qq = "favoritas_add.php?favorita_id=" + $('favorita_id').value;
	var ajax = ajaxCreate();
	if (ajax === null) return;
	ajax.open("GET", qq, false);
	ajax.send(null);
	if (ajax.status == 200 || ajax.status == 304)
	{
		ltform_msg(ajax.responseText, 2, 1);
	}
}

function favoritas_del(elid)
{
	var qq = "favoritas_del.php?favorita_id=" + elid;
	var ajax = ajaxCreate();
	if (ajax === null) return;
	ajax.open("GET", qq, false);
	ajax.send(null);
	if (ajax.status == 200 || ajax.status == 304)
	{
		ltform_msg(ajax.responseText, 2, 1);
	}
}

function ltform_msg_hide(bReload, sObj, sURL)
{
	window.clearTimeout(ltform_msgTO);
	ltform_msgIST = 0;
	$('ltform_msg_p').style.visibility = "hidden";
	$('ltform_msg').style.visibility = "hidden";
	if (bReload == 1) 
	{
	    if (sURL == null) document.location.reload(); else document.location.replace(sURL);
	}
	if (sObj != null) $(sObj).focus();
}

function ltform_msg(elhtml, nSegs, bReload, sObj, sURL)
{
	$('ltform_msg_p').innerHTML = elhtml;
	$('ltform_msg').style.visibility = "visible";
	$('ltform_msg_p').style.visibility = "visible";
	if (nSegs > 0)
	{
		if (ltform_msgIST == 1) window.clearTimeout(ltform_msgTO);
		var nTimeout = nSegs * 1000;
		if (sObj != null)
		{
			if (sURL != null)
			{
				ltform_msgIST = 1;
				ltform_msgTO = window.setTimeout('ltform_msg_hide(' + bReload + ",'" + 
					sObj + "','" + sURL + "')", nTimeout);
			}
			else
			{
				ltform_msgIST = 1;
				ltform_msgTO = window.setTimeout('ltform_msg_hide(' + bReload + ",'" + 
						sObj + "')", nTimeout);
			}
		}
		else
		{
			if (sURL != null)
			{
				ltform_msgIST = 1;
				ltform_msgTO = window.setTimeout('ltform_msg_hide(' + bReload + ",null,'" + 
					sURL + "')", nTimeout);
			}
			else
			{
				ltform_msgIST = 1;
				ltform_msgTO = window.setTimeout('ltform_msg_hide(' + bReload + ')', nTimeout);
			}
		}
	}
}

function ltform_msg_seguir(bReload, sObj, sURL)
{
	var ssobj = '', ssurl = '';
	if (sObj != null) ssobj = ",'" + sObj + "'"; else ssobj = ',null';
	if (sURL != null) ssurl = ",'" + sURL + "'"; else ssurl = ',null';
	return '<p><a href="javascript:void(0);" onclick="ltform_msg_hide(' + bReload + ssobj + ssurl + ');">Seguir</a></p>';
}

function ltform_msgs(elhtml, nSegs, bReload, sObj, sURL)
{
	ltform_msg(elhtml + ltform_msg_seguir(bReload, sObj, sURL), nSegs, bReload, sObj, sURL);
}

function ltform_wait(bShow)
{
	var existe = document.getElementById('waiticon');
	if (existe !== null)
	{
		if (bShow) existe.style.visibility = "visible";
		else existe.style.visibility = "hidden";
	}
}

function ltlog_view(tabla, valor)
{
	var lnk = encodeURI("logview.php?tabla=" + tabla + "&valor=" + valor);
	window.open(lnk, "logview"+tabla+valor, "width=300,height=100,location=no,toolbar=no,titlebar=no");
}

function ltpane_toggle(sName)
{
	var pstatus = $(sName + "_pnstatus").value == 1 ? 0: 1;
	$(sName).style.display = pstatus == 0 ? "none": "block";
	$(sName + "_pnbutt").src = pstatus == 0 ? "pop-down.png": "pop-up.png";
	$(sName + "_pnstatus").value = pstatus;
}
function PopupCenter(pageURL, title, w, h)
{
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
	var targetWin = window.open(pageURL, title, 
		'toolbar=no, location=no, directories=no, status=no, ' +
		'menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w +
		', height=' + h + ', top=' + top + ', left=' + left);
}

function hideitdo(sObj)
{
	$(sObj).style.display = 'none';
}

function ltform_msgx(sHTML, sObj, nSegs)
{
	$(sObj).innerHTML = sHTML;
	$(sObj).style.display = 'block';
	if (nSegs > 0)
	{
		var nTimeout = nSegs * 1000;
		window.setTimeout("hideitdo('" + sObj + "')", nTimeout);
	}
}

function lt_enablebycheck()
{
	var ii;
	var estado = !arguments[0].checked;
	var argc = arguments.length;
	for (ii = 1; ii < argc; ii++)
	{
		$(arguments[ii]).disabled = estado;
	}
	return true;
}

function lt_savedefault(elobj, fmid)
{
	var qq = "ltable_savedefault.php?ctrl=" + elobj.name + "&valor=" + elobj.value +
		"&fm=" + fmid;
    new Ajax.Request(qq, 
    { method: 'get', onSuccess: function(request) { isok = true; },
    	onFailure: function(request) { }});
    return true;
}

function row_delete(sTabla, rowid)
{
	var ii;
	tabla = $(sTabla);
	largo = tabla.rows.length;
	for (ii = 0; ii < largo; ii++)
	{
		if (tabla.rows[ii].id == rowid)
		{
			tabla.deleteRow(ii);
			break;
		}
	}
}

function lt_listbox_first(lto)
{
	if (lto.length > 0)
	{
		lto.selectedIndex = 0;
		lto.options[0].selected = true;
	}
}

function lt_listbox_assign(lto, valor)
{
	var ii;
	for (ii=0; ii<lto.length; ii++)
	{
		if (lto.options[ii].value == valor)
		{
			lto.selectedIndex = ii;
			lto.options[ii].selected = true;
			$("HDDN_" + lto.name).value = valor;
			break;
		}
	}
}

function lt_listbox_add(oList, sValue, sDescr, bClear, bSet)
{
	if (bClear) oList.length = 0;
	var tmpopt = document.createElement('option');
	tmpopt.value = sValue;
	tmpopt.text = sDescr;
	try
	{
		oList.add(tmpopt, null);
	}
	catch (e)
	{
		oList.add(tmpopt);
	}
	if (bSet) lt_listbox_assign(oList, sValue);
}

function lt_listbox_from_array(oList, iSizeArray, aRecords, bClear, bSet)
{
	if (bClear) oList.length = 0;
	var ndx;
	for (ndx=0;ndx<iSizeArray;ndx++)
	{
		var tmpopt = document.createElement('option');
		tmpopt.value = aRecords[ndx].v;
		tmpopt.text = aRecords[ndx].ds;
		try
		{
			oList.add(tmpopt, null);
		}
		catch (e)
		{
			oList.add(tmpopt);
		}
	}
	if (bSet && iSizeArray > 0) lt_listbox_assign(oList, aRecords[0].v);
}

function lt_listbox_from_ajax(url, nombre)
{
	var re = false;
	var qq = encodeURI(url);
	ltform_wait(true);
	$(nombre).length = 0;
	var ajax = new Ajax.Request(qq, {asynchronous:false, method:'get'});
	re = new Ajax.Response(ajax).responseJSON;
	ltform_wait(false);
	if (ajax.success())
	{
		lt_listbox_from_array($(nombre), re.lista.sz, re.lista.a, true, true);
		isok = true;
	}
	return re;
}

function lt_radio_val(nombre)
{
	return $j('input[name=' + nombre + ']:checked').val();
}

function ltup_do(sTabla, sCampo, sKey, sKeyval)
{
	var oCampo = $(sCampo);
	var isok = false;
	var sValor = oCampo.value;
	if (oCampo.type == 'checkbox') sValor = chkval(oCampo);
	if (oCampo.type == 'radio') sValor = lt_radio_val(sCampo);
	var qq = encodeURI("ltable_olib_upfield.php?tbl="+sTabla+"&fn="+sCampo+"&fv="+sValor+ "&kn="+sKey+"&kv="+sKeyval);
	//alert(qq);
	ltform_wait(true);
	var ajax = new Ajax.Request(qq, {asynchronous:false, method:'get'});
	ltform_wait(false);
	if (ajax.success())
	{
		if ($(sCampo+'_ic')!=null) $(sCampo+'_ic').style.visibility = "hidden";
		isok = true;
	}
	else
	{
		var response = new Ajax.Response(ajax);
		ltform_msg(response.responseJSON.msg, 10, 0);
	}
	return isok;
}

function ltup_enable(sCampo)
{
	if ($(sCampo + '_ic')!=null) $(sCampo + '_ic').style.visibility = "visible";
	return true;
}

function lt_sendemail(nRptid, sTxtid, sSubj, sDvid)
{
	var oAddress = document.getElementById(sTxtid);
	if (lt_novacio(oAddress)) {
		if (lt_email(oAddress)) {
			var qq = encodeURI("ltable_olib_email.php?id=" + nRptid + "&ad=" + oAddress.value + "&su=" + sSubj);
			//alert(qq);
			new Ajax.Request(qq, { method: 'get', 
				onSuccess: function(request) { alert("Mensaje enviado a: " + oAddress.value); $(sDvid).style.display='none';},
				onFailure: function(request) { alert("Error enviando mensaje");}
			});
		}
	}
}

function lt_put_default(oCtrl, sName, nForm, nTipo)
{
	var valor;
	if (nTipo == 1) valor = oCtrl.checked ? 1:0; else valor = oCtrl.value;
	var qq = encodeURI("ltable_olib_putdef.php?cn=" + sName + "&cf=" + nForm + "&cv="+ valor);
	//alert(qq);
	new Ajax.Request(qq, { method: 'get', 
		onFailure: function(request) { alert("Error guardando preferencias");}
	});
}

function chkval(nombre)
{
	return $(nombre).checked ? '1': '0';
}

function lt_ajax(url, showmsg, nombre)
{
	var re = null;
	var qq = encodeURI(url);
	if (typeof showmsg == 'undefined') var showmsg = false;
	if (typeof nombre == 'undefined') var nombre = null;
	ltform_wait(true);
	var ajax = new Ajax.Request(qq, {asynchronous:false, method:'get'});
	re = new Ajax.Response(ajax).responseJSON;
	ltform_wait(false);
	if (showmsg) ltform_msg(re.msg, 10, 0, nombre);
	return re;
}

function lt_ajaxx(url, fnCallback)
{
	var re = null;
	var qq = encodeURI(url);
	ltform_wait(true);
	var ajax = new Ajax.Request(qq, {asynchronous:false, method:'get'});
	re = new Ajax.Response(ajax).responseJSON;
	ltform_wait(false);
	return fnCallback(ajax.success(), re);
}

function lt_go(dummy, url)
{
	document.location.replace(url);
}

function obtener_mes(mes)
{
  switch (mes) 
  {
    case 1:return "Enero";
    case 2:return "Febrero";
    case 3:return "Marzo";
    case 4:return "Abril";
    case 5:return "Mayo";
    case 6:return "Junio";
    case 7:return "Julio";
    case 8:return "Agosto";
    case 9:return "Septiembre";
    case 10:return "Octubre";
    case 11:return "Noviembre";
    case 12:return "Diciembre";
    default:return "Valor no definido";
  }
}

function lt_filelink_view(archivo)
{
	var qq = encodeURI('ltable_olib_filelink_viewer.php?fn='+archivo+'&dlg=1');
	//alert(qq);
	var newDiv = $j(document.createElement('div')); 
	newDiv.load(qq).dialog({
		modal:true, width:750, height:1100, 
		title: "Ver archivo", 
		close: function(e, ui) { $j(this).remove(); } 
	});
}

function lt_file_view(tabla, campo, valor_clave, dlg)
{
	var qq = encodeURI('ltable_olib_file_viewer.php?tabla=' + tabla + '&campo=' + campo + '&valor=' + valor_clave + '&dlg=' + dlg);
	console.log(qq);
	var newDiv = $j(document.createElement('div')); 
	newDiv.load(qq).dialog({
		modal:true, width:750, height:1100, 
		title: "Ver archivo", 
		close: function(e, ui) { $j(this).remove(); } 
	});
}