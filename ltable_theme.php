<?php
require_once "ltable_olib.php";

if (($fo = lt_form::principal(2000)))
{
	$tema = 'default';
	if (isset($_SESSION['tema'])) $tema = $_SESSION['tema'];

	$fo->hdr("Escoger tema visual");
	
	$temaa = array(array('default','Original'),array('rosa','Rosa'),
		array('azul','Azul'), array('verde','Verde'));
	$ct = new lt_ctrl_set($fo, 'ltablethemefrm', 'ltable_theme_up.php');
	$ct->setPostProcFn('ltable_theme_change_pp');
	$ct->la('Tema', 'nvotema', 'c', $temaa, $tema);
	$ct->u('Cambiar tema');
	$ct->box_vertical();
}
?>