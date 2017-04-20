function lteditor_fledit(sTabla,sCampo) {
	var qq = encodeURI('ltable_editor_flld.php?tabla='+sTabla+'&columna='+sCampo);
	$j('#fleditoreditdiv').load(qq);
}