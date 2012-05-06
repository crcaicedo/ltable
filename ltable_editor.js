function lteditor_fledit(sTabla,sCampo)
{
	var qq = "ltable_editor_fl.php?tbl="+sTabla+"&fl="+sCampo;
	var flsel = $('flsel').value;
	if (flsel != '') $('sel'+flsel).style.background='';
	$('flsel').value = sCampo;
	$('sel'+sCampo).style.background='yellow';
	ltform_wait(true);
	new Ajax.Request(qq, 
	{
		method: 'get',
		onSuccess: function(request)
		{
			ltform_wait(false);
			$('n').value = request.responseJSON.n;
			$('t').value = request.responseJSON.t;
			$('l').value = request.responseJSON.l;
			$('pd').value = request.responseJSON.pd;
			$('title').value = request.responseJSON.title;
			$('df').value = request.responseJSON.df;
			lt_listbox_assign($('ctrl_type'), request.responseJSON.ctrl_type);
			$('ordenv').value = request.responseJSON.ordenv;
			$('vcols').value = request.responseJSON.vcols;
			$('vrows').value = request.responseJSON.vrows;
			$('enabled').checked = request.responseJSON.enabled == 1;
			$('ro').checked = request.responseJSON.ro == 1;
			$('hidden').checked = request.responseJSON.hidden == 1;
			$('esdato').checked = request.responseJSON.esdato == 1;
			$('isup').checked = request.responseJSON.isup == 1;
			$('dup').checked = request.responseJSON.dup == 1;
			$('autovar').checked = request.responseJSON.autovar == 1;
			$('postvar').checked = request.responseJSON.postvar == 1;
			lt_listbox_assign($('dt_auto'), request.responseJSON.dt_auto);
			$('mascara').value = request.responseJSON.mascara;
			$('funcion').value = request.responseJSON.funcion;
			$('valid_fn').value = request.responseJSON.valid_fn;
			$('valid_parms').value = request.responseJSON.valid_parms;
			$('init_fn').value = request.responseJSON.init_fn;
			$('init_parms').value = request.responseJSON.init_parms;
			$('onkey_fn').value = request.responseJSON.onkey_fn;
			$('onkey_parms').value = request.responseJSON.onkey_parms;
			$('ls_tbl').value = request.responseJSON.ls_tbl;
			$('ls_fl_key').value = request.responseJSON.ls_fl_key;
			$('ls_fl_desc').value = request.responseJSON.ls_fl_desc;
			$('ls_fl_order').value = request.responseJSON.ls_fl_order;
			$('ls_custom').value = request.responseJSON.ls_custom;
			$('ls_custom_new').value = request.responseJSON.ls_custom_new;
		},
		onFailure: function(request)
		{
			ltform_wait(false);
			ltform_msg(request.responseJSON.msg, 10, 0);
		}
	});
}

function lteditor_flval(oCtrl)
{
	var isok = false;
	if (oCtrl.id == 'n') isok = lt_novacio(oCtrl);
	return isok;
}

function lteditor_flsv(oCtrl)
{
	if (lteditor_flval(oCtrl))
	{
		var qq = "ltable_editor_flsv.php?tbl="+$('tabla')+"&fl="+oCtrl.id+
			"&k="+$('n').value+"&v="+oCtrl.value+"&t="+$(oCtrl.id+'__t').value;
		ltform_wait(true);
		new Ajax.Request(qq, 
		{
			method: 'get',
			onSuccess: function(request)
			{
				ltform_wait(false);
			},
			onFailure: function(request)
			{
				ltform_wait(false);
				ltform_msg(request.responseJSON.msg, 10, 0);
			}
		});		
	}
}