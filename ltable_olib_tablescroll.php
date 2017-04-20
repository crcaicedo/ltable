<?php
function lt_tablescroll_css(lt_form $fo, $width, $height)
{
	$fo->buf .= '<STYLE TYPE="text/css" MEDIA=all>'.
	'table.tblscroll {color:black;border:1px solid black;white-space:nowrap;border-collapse:collapse;}'.
	'table.tblscroll td {color:black;border:1px solid black;white-space:nowrap;}'.
	'table.tblscroll th {color:black;border:1px solid black;white-space:nowrap;}'.
	'.th{color:black;border:1px solid black;white-space:nowrap;}'.
	'A:link {COLOR: #0000EE;} A:hover {COLOR: #0000EE;} A:visited {COLOR: #0000EE;} A:hover {COLOR: #0000EE;}'.
	'.div_freezepanes_wrapper{position:relative;width:'.$width.';height:'.$height.'; '.
	'overflow:hidden;background:#fff;border-style: ridge;}'.
	'.div_verticalscroll{position: absolute;right:0px;width:18px;height:100%;background:#EAEAEA;border:1px solid #C0C0C0;}'.
	'.buttonUp{width:20px;position: absolute;top:2px;}'.
	'.buttonDn{width:20px;position: absolute;bottom:22px;}'.
	'.buttonBUp{width:20px;position: absolute;top:22px;}'.
	'.buttonBDn{width:20px;position: absolute;bottom:42px;}'.
	'.div_horizontalscroll{position: absolute;bottom:0px;width:100%;height:18px;background:#EAEAEA;border:1px solid #C0C0C0;}'.
	'.buttonRight{width:20px;position: absolute;left:0px;padding-top:2px;}'.
	'.buttonLeft{width:20px;position: absolute;right:22px;padding-top:2px;}'.
	'.buttonBRight{width:20px;position: absolute;left:22px;padding-top:2px;}'.
	'.buttonBLeft{width:20px;position: absolute;right:44px;padding-top:2px;}'.
	'</STYLE>';
}

function lt_tablescroll_begin(lt_form $fo, $idtabla, $col, $mincol, $row, $minrow, $width='100%', $height='85%', $step=10)
{
	lt_tablescroll_css($fo, $width, $height);
	$fo->autodivx = false;
	$fo->js('ltable_tablescroll.js');
	$fo->div('__'.$idtabla.'_div', 0, "div_freezepanes_wrapper");

	$fo->div('', 0, 'div_verticalscroll');
	$fo->div('', 0, '', 'height:50%;');
	$fo->img('uF035.png', 'buttonUp', '', -1, -1, '', sprintf("__%s_up", $idtabla), 
		sprintf("arriba('%s');", $idtabla));
	$fo->img('ulF035.png', 'buttonBUp', '', -1, -1, '', sprintf("__%s_bup", $idtabla), 
		sprintf("arr_largo('%s',%d);", $idtabla, $step));
	$fo->divx();
	$fo->div('', 0, '', 'height:50%;');
	$fo->img('uF036.png', 'buttonDn', '', -1, -1, '', sprintf("__%s_down", $idtabla), 
		sprintf("abajo('%s');", $idtabla));
	$fo->img('ulF036.png', 'buttonBDn', '', -1, -1, '', sprintf("__%s_bdown", $idtabla), 
		sprintf("aba_largo('%s',%d);", $idtabla, $step));
	$fo->divx();
	$fo->divx();
	
	$fo->div('', 0, 'div_horizontalscroll');
	$fo->div('', 0, '', 'float:left;width:50%;height:100%;');
	$fo->img('uF033.png', 'buttonRight', '', -1, -1, '', sprintf("__%s_right", $idtabla), 
		sprintf("derecha('%s');", $idtabla));
	$fo->img('ulF033.png', 'buttonBRight', '', -1, -1, '', sprintf("__%s_bright", $idtabla), 
		sprintf("der_largo('%s',%d);", $idtabla, $step));
	$fo->divx();
	$fo->div('', 0, '', 'float:right;width:50%;height:100%;');
	$fo->img('uF034.png', 'buttonLeft', '', -1, -1, '', sprintf("__%s_left", $idtabla), 
		sprintf("izquierda('%s');", $idtabla));
	$fo->img('ulF034.png', 'buttonBLeft', '', -1, -1, '', sprintf("__%s_bleft", $idtabla), 
		sprintf("izq_largo('%s',%d);", $idtabla, $step));
	$fo->divx();
	$fo->divx();
	
	$fo->hid('__'.$idtabla.'_c', $col);
	$fo->hid('__'.$idtabla.'_mc', $mincol);
	$fo->hid('__'.$idtabla.'_r', $row);
	$fo->hid('__'.$idtabla.'_mr', $minrow);
//  	$tx0 = new lt_textbox();
//  	$tx0->n = '__'.$idtabla.'_r';
//  	$tx0->assign($row);
//  	$tx0->render($fo->buf);
//  	$tx1 = new lt_textbox();
//  	$tx1->n = '__'.$idtabla.'_mr';
//  	$tx1->assign($minrow);
//  	$tx1->render($fo->buf);
}
function lt_tablescroll_end(lt_form $fo, $idtabla)
{
	$fo->divx();
	$fo->br();
	$fo->br();
	$fo->divx();
}
?>