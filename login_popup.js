function login_popup_chk(myurl)
{
	var us = $('user').value;
	var pw = $('passwd').value;
	var qq = encodeURI("login_popup_do.php?us=" + us + "&pw=" + pw);
	///ltform_wait(true);
	var ajax = new Ajax.Request(qq, {method: 'get', asynchronous: false});
	var re = new Ajax.Response(ajax);	
	if (ajax.success())
	{
		///ltform_wait(false);
		///login_popup_out();
		//$('loginpopupmsgdiv').innerHTML = re.responseJSON.msg;
		document.location.replace(myurl);
	}
	else
	{
		$('loginpopupmsgdiv').innerHTML = re.responseJSON.msg;
		///ltform_wait(false);
		///ltform_msg(re.responseJSON.msg, 10, 0);
	}
}

function login_popup_enter(elobj, e, myurl)
{
	var key;
	if (window.event) key = window.event.keyCode; else key = e.which;
	if (key == 13) login_popup_chk(myurl);
	return true;
}

function login_popup_in()
{
	$('loginpopupdiv').style.visibility = "visible";
	$('user').focus();
}

function login_popup_out()
{
	document.location.reload();
}
