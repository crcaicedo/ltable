
function lt_chpwd0(elobj)
{
    return lt_novacio(elobj);
}

function lt_chpwd1(elobj)
{
	var isok = false;
	if (lt_numletra(elobj))
	{
		if (elobj.value.length >= 6)
		{
			isok = true;
		}
		else
		{
			alertx(elobj, 'Largo minimo es de 6 caracteres.');
		}
	}
    return isok;
}

function lt_chpwd2(elobj)
{
    var isok = false;
    var p1 = $('p1');
    if (lt_chpwd1(p1))
    {
    	if (elobj.value != p1.value) alertx(p1, 'Contrasenas no coinciden');
    	else isok = true;
    }
    return isok;
}

function lt_chpwd_valid(elobj)
{
    if (lt_chpwd0($('p0')))
    {
    	if (lt_chpwd2($('p2')))
    	{
    		if (confirm('Realmente desea cambiar su clave?'))
    		{
    			var pet = encodeURI("usuario_chpasswd_do.php?p0=" + $('p0').value +
    					'&p1=' + $('p1').value + "&p2=" + $('p2').value);
    			//alert(pet);
    			var xmlHttp = new ajaxCreate();
    			if (xmlHttp !== null)
    			{
    				xmlHttp.open("GET", pet, false);
    				xmlHttp.send(null);
    				if (xmlHttp.status == 200 || xmlHttp.status == 304)
    				{
    					ltform_msg(xmlHttp.responseText, 3, 1);
    				}
    			}
    		}
    	}
    }
    return allok;
}
