function usracc_del(uidx, pidx, modx)
{
	var oajax = ajaxCreate();
	if (oajax != null)
	{
		var pet = encodeURI("usuario_acceso_del.php?uidx=" + uidx + "&pidx=" + pidx +
			"&modx=" + modx);
		//alert(pet);
		oajax.open("GET", pet, false);
		oajax.send(null);
		if (oajax.status == 200 || oajax.status == 304)
		{
			document.location.replace("usuario_acceso.php?valor=" + uidx);
		}
	}
}

function usracc_add(uidx, pidx)
{
	var qq = encodeURI("usuario_acceso_up.php?uidx=" + uidx + "&pidx=" + pidx +
		"&modulo=" + $('modulo').value + "&nivel=" + $('nivel').value);

	ltform_wait(true);
	new Ajax.Request(qq,
	{
		method: 'get',
		onSuccess: function(request)
		{
			ltform_wait(false);
			document.location.replace("usuario_acceso.php?valor=" + uidx);
		},
		onFailure: function(request)
		{
			ltform_wait(false);
			ltform_msg(request.responseJSON.msg, 10, 0);
		}
	});
	
    return true;
}
